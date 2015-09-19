<?php

namespace app\extensions\message;

use app\extensions\message\BaseSmser;
use Yii;
use yii\base\Component;
use yii\log\Logger;

class Smser extends Component
{
    // 默认发送网关
    public $defaultProvider = null;

    public $providers = array();

    private $_providers = array();


    public function init()
    {
        foreach ($this->providers as $name => $config) {
            $config['class'] = "app\\extensions\\message\\providers\\{$name}";
            $this->_providers[$name] = Yii::createObject($config);
        }

        parent::init();
    }

    /**
     * 发送短信
     * @param $content
     * @param $provider
     * @param $abroad
     * @return mixed
     */
    public function send($content, $provider, $abroad)
    {

        $this->defaultProvider = $provider ? $provider : $this->defaultProvider;

        $this->getProvider()->lastErrorMessage = null;

        // 只有云片支持发送国际短信
        if(in_array($provider, ['Yuntongxun', 'Cloud']) && $abroad){
            return false;
        }

        $result = $this->getProvider()
                        ->initialize($content)
                        ->contentTransform()
                        ->replaceSignature()
                        ->send();

        Yii::info("Send SMS to {$content['mobile']}:" . print_r($content, true),'sms');

        return $result;

    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->getProvider()->status;
    }

    /**
     * @return mixed
     */
    public function getLastErrorMessage()
    {
        return $this->getProvider()->lastErrorMessage;
    }

    /**
     * Returns the SMS provider
     * @return BaseSMSProvider
     */
    private function getProvider()
    {
        return $this->_providers[$this->defaultProvider];
    }

}