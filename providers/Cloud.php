<?php
namespace app\extensions\message\providers;

use app\extensions\message\BaseSmser;

class Cloud extends BaseSmser
{
    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * @inheritdoc
     */
    public $url = 'http://api.sms.cn/mtutf8/';

    // init
    public function initialize($content)
    {
        $this->mobile       = $content['mobile'];
        $this->message      = $content['message'];
        $this->templateData = $content['template_data'];
        $this->template_id  = $content['template_id'];
        $this->signature    = $content['signature'];
        return $this;
    }

    /**
     * 内容转换
     *
     * @inheritdoc
     */
    public function contentTransform()
    {
        $text = $this->templateData;
        foreach($this->message as $key=>$value){
            $text = str_replace('#'.$key.'#', $value, $text);
        }
        // 变量没有解析完
        if(strstr($text, '#')){
            $this->status = false;
            $this->errorMessage = '消息发送格式不正确';
        }
        $this->message = $text;

        return $this;
    }

    /**
     * 替换签名
     * @param $signature
     * @return $this
     */
    public function replaceSignature()
    {
        $this->message = str_replace('{$signature}', $this->signature, $this->message);
        $this->message = str_replace('{$signature_en}', $this->signature, $this->message);
        return $this;
    }


    /**
     * @inheritdoc
     */
    public function send()
    {
        $data = [
            'uid' => $this->username,
            'pwd' => $this->password,
            'mobile' => $this->mobile,
            'content' => $this->message,
        ];

        $this->returnMessage = $this->createCurl($this->url, $data);

        $resultArr = [];
        parse_str($this->returnMessage, $resultArr);

        $status = isset($resultArr['stat']) ? (string) $resultArr['stat'] : null;

        $this->status = $status === '100' ? true : false;

        return $this;
    }
}