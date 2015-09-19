<?php
/**
 * Created by PhpStorm.
 * User: Dream
 * Date: 15/9/18
 * Time: 下午2:10
 */

namespace app\extensions\message;

use yii\base\Component;

abstract class BaseSmser extends Component{

    // 发送的手机号
    public $mobile;

    // 短信签名
    public $signature;

    // 短信模板ID
    public $template_id = 0;

    // 发送的内容
    public $message;

    // 模板内容
    public $templateData;

    // 成功或失败
    public $status;

    // 错误号码
    public $code;

    // 网关发送返回的信息
    public $returnMessage;

    // 错误信息
    public $lastErrorMessage;

    /**
     * 初始化内容
     * @param $content
     * @return mixed
     */
    abstract public function initialize($content);

    /**
     * 内容转换
     * @param $template_data
     * @param $data
     * @return mixed
     */
    abstract public function contentTransform();

    /**
     * 替换签名
     * @param $signature
     * @return mixed
     */
    abstract public function replaceSignature();

    /**
     * 短信发送
     * @param $mobile
     * @param $template
     * @return boole
     */
    abstract public function send();

    /**
     * 发送请求
     * @param $url
     * @param $data
     * @return boole
     */
    protected function createCurl($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}