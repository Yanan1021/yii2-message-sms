# yii2-message-sms

短信网关配置文件:

'smser' => [
    'class' => 'app\extensions\message\Smser',
    'defaultProvider' => 'Yunpian',// 'Yunpian',
    'providers' =>  [
        'Yunpian'=> [
            'apikey'=>'XXX', // 云片apikey
        ],
        'Yuntongxun' => [
            'accountSid' => 'XXX',
            'accountToken' => 'XXX',
            'appId' => 'XXX',
            'serverIp' => 'app.cloopen.com',
            'serverPort' => 8883,
            'softVersion' => '2013-12-26',
        ],
        'Cloud' => [      // 云信
            'username' => 'XXX',
            // 加密地址：http://www.sms.cn/password/
            'password' => 'XXX',
        ],
    ],
],

