<?php

namespace app\extensions\message\providers;

use app\extensions\message\BaseSmser;

class Yuntongxun extends BaseSmser
{
    /**
     * @var string
     */
    public $accountSid;
    
    /**
     * @var string
     */
    public $accountToken;
    
    /**
     * @var string
     */
    public $appId;
    
    /**
     * @var string
     */
    public $serverIp;
    
    /**
     * @var string
     */
    public $serverPort;
    
    /**
     * @var string
     */
    public $softVersion;
    
    /**
     * @var string|null
     */
    private $_batch;

    /**
     * @var string
     */
    private $dataType = 'json';

    /**
     * @var string
     */
    private $url;

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
        $row = [];
        preg_match_all('/\#(.*?)\#/', $this->templateData, $result);
        if(!empty($result[1]) && is_array($result[1])){
            $msg_content_key = $result[1];
            foreach ($msg_content_key as $key => $value) {
                foreach ($this->message as $k => $val) {
                    if($value == $k){
                        array_push($row, $val);
                        break;
                    }
                }
            }
        }
        $this->message = $row;
        return $this;
    }

    /**
     * 替换签名
     * @param $signature
     * @return $this
     */
    public function replaceSignature()
    {
        return $this;
    }


    /**
     * @inheritdoc
     */
    public function send()
    {
        $body = json_encode([
            'to' => $this->mobile,
            'templateId' => $this->template_id,
            'appId' => $this->appId,
            'datas' => array_values($this->message)
        ]);
        $sig = strtoupper(md5($this->accountSid . $this->accountToken . $this->getBatch()));
        $this->url = "https://{$this->serverIp}:{$this->serverPort}/{$this->softVersion}/Accounts/{$this->accountSid}/SMS/TemplateSMS?sig={$sig}";
        $authen = base64_encode($this->accountSid . ':' . $this->getBatch());
        $header = ["Accept:application/{$this->dataType}", "Content-Type:application/{$this->dataType};charset=utf-8", "Authorization:{$authen}"];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $this->returnMessage = curl_exec($ch);
        curl_close($ch);

        if (empty($this->returnMessage)) {
            $this->state = '172001';
            $this->message = '网络错误';
        } else {
            $json = json_decode($this->returnMessage);
            if ($json && is_object($json)) {
                $this->code = isset($json->statusCode) ? (string) $json->statusCode : null;
                $result = isset($json->statusCode) ? (string) $json->statusCode : null;
            }
        }

        $this->status = $result === '000000' ? true : false;

        return $this;
    }

    /**
     * 时间戳
     * 
     * @return string
     */
    public function getBatch()
    {
        if ($this->_batch === null) {
            $this->_batch = date('YmdHis');
        }
        
        return $this->_batch;
    }
}
