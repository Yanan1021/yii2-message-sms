<?php

namespace app\extensions\message\providers;

use app\extensions\message\BaseSmser;

class Yunpian extends BaseSmser
{
    /**
     * @var string
     */
    public $apikey;

    /**
     * @inheritdoc
     */
    public $url = 'http://yunpian.com/v1/sms/send.json';

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
     * 发送短信
     * @inheritdoc
     */
    public function send()
    {
        if($this->status === false) return false;

        $data = [
            'apikey' => $this->apikey,
            'mobile' => $this->mobile,
            'text' => $this->message,
        ];

        $this->returnMessage = $this->createCurl($this->url, $data);

        $json = json_decode($this->returnMessage);

        if ($json && is_object($json)) {
            $this->code = isset($json->code) ? (string) $json->code : null;
        }

        $this->status = $this->code === '0' ? true : false;

        return $this;
    }

}
