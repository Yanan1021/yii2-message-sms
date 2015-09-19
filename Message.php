<?php
/**
 * Created by PhpStorm.
 * User: Dream
 * Date: 15/8/13
 * Time: 下午2:14
 */
namespace app\components;

use app\modules\user\models\MsgPushSms;
use Yii;
use app\components\Helper;

class Message
{
    public static $provider;

    public static $template_id;

    public static $mobile;

    public static $data = false;

    public static $send_failure_queue = ['Yuntongxun','Cloud'];

    /**
     * 检查短信格式
     * @param $mobile
     * @param $data
     * @param $template_id
     * @return array
     */
    private static function checkMessageFormat($mobile, $data, $template_id, $abroad)
    {
        if(!empty($data) && is_array($data)) {
            if (Helper::isMobile($mobile)) {
                $sms_template = Yii::$app->params['sms_template'];
                $provider = !$abroad ? 'Yunpian' : 'Yunpian_en';
                if(isset($sms_template['templates'][$provider][$template_id])) {
                    return array('state'=> 1);
                }else{
                    return array('state'=> 0, 'msg' => '找不到短信模板ID');
                }
            } else {
                return array('state'=> 0, 'msg' => '手机号码格式错误');
            }
        }else{
            return array('state'=> 0, 'msg' => 'data数据不能为空');
        }
    }

    /**
     * 替换特殊字符
     *
     * @param $data
     * @return array
     */
    public static function smsStrReplace($data)
    {
        if(!empty($data) && is_array($data)){
            foreach($data as $key=>$value){
                $str = str_replace('【', '', $value);
                $data[$key] = str_replace('】', '', $str);
            }
        }
        return $data;
    }


    /**
     * 发送短信
     * @param $mobile
     * @param $data
     * @param $template_id
     * @return bool|array
     */
    public static function sendSms($mobile, $data, $template_id)
    {
        $result = false;

        $provider = self::$provider ? self::$provider : Yii::$app->components['smser']['defaultProvider'];

        self::$template_id = self::$template_id ? self::$template_id : $template_id;

        self::$data = self::$data ? self::$data : $data;

        $data = self::smsStrReplace(self::$data);

        self::$mobile = self::$mobile ? self::$mobile : $mobile;

        // 发送国际短信加上区号, 每次发送只能接受一个手机号
        $abroad = false;
        if (isset($data['area'])) {
            if ($data['area'] != '86') {
                $mobile = '+' . $data['area'] . self::$mobile;
                $abroad = true;
            }
        }

        $result = self::checkMessageFormat($mobile, $data, self::$template_id, $abroad);

        if($result['state'] == 1){

            $sms_config = Yii::$app->params['sms_template'];

            $signature = $sms_config['constant']['signature'];

            $template_data = $sms_config['templates']['Yunpian'][self::$template_id];

            if($provider == 'Yuntongxun'){
                // 获取云通讯对应的模板ID
                $template_id = $sms_config['templates']['Yuntongxun'][self::$template_id]['template_id'];
            }

            $content = array(
                'mobile'        => $mobile,
                'message'       => $data,
                'signature'     => $signature,
                'template_id'   => $template_id,
                'template_data' => $template_data,
            );

            $object = Yii::$app->smser->send($content, $provider, $abroad);

            if(is_object($object)){
                self::lastSave($object, $provider);

                $result = $object->status;

                if($result === false){
                    /*
                     *  云片返回规则：http://www.yunpian.com/4.0/api/recode.html
                     *
                     *  17 : 24小时内同一手机号发送次数超过限制
                     *  9  : 同一手机号5分钟内重复提交相同的内容超过3次
                     *  8  : 同一手机号30秒内重复提交相同的内容
                     *
                     *  云通讯返回规则： http://docs.yuntongxun.com/index.php/%E9%94%99%E8%AF%AF%E4%BB%A3%E7%A0%81
                     *
                     *  160021 : 【短信】相同的内容发给同一手机一天中只能发一次
                     *  160022 : 【短信】对同一个手机一天发送的短信超过限制次数
                     * */
                    $yuntongxun_code = [17, 9, 8, 160021, 160022];
                    if(in_array($object->code, $yuntongxun_code)){
                        $result = self::repeatSendSms($mobile, $data, self::$template_id);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * 网关发送失败重复发送
     * @param $mobile
     * @param $data
     * @param $template_id
     */
    public static function repeatSendSms($mobile, $data, $template_id)
    {
        if(!empty(self::$send_failure_queue)){
            self::$provider = array_shift(self::$send_failure_queue);
            if(!self::sendSms($mobile, $data, $template_id)){
                return self::repeatSendSms($mobile, $data, $template_id);
            }
        }
        return true;
    }

    /**
      * 短信发送记录
      * @param $object
      * @param $provider
      * @return bool
      */
    public static function lastSave($object, $provider)
    {
        $MsgPushSms = new MsgPushSms();
        $MsgPushSms->provider   = $provider;
        $MsgPushSms->template_id= $object->template_id;
        $MsgPushSms->mobile     = $object->mobile;
        $MsgPushSms->content    = self::decodeUnicode(json_encode($object->message));
        $MsgPushSms->send_time  = date('Y-m-d H:i:s');
        $MsgPushSms->return     = self::decodeUnicode(json_encode($object->returnMessage));
        $MsgPushSms->state      = $object->status ? 1 : 2;
        $MsgPushSms->create_time= date('Y-m-d H:i:s');
        return $MsgPushSms->save();
    }

    public static function decodeUnicode($unicodeStr)
    {
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', create_function( '$matches', 'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");' ), $unicodeStr);
    }
}