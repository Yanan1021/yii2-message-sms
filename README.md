# yii2-message-sms

短信网关发送，包含云片，云通讯，云信

## Config

```php
return [
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
    ]
]
```

## 短信模板配置文件

```php
'sms_template' => [
    'constant' => [
        'signature' => '签名',
        'signature_en' => '签名',
    ],
    'templates' => [
        'Yunpian' => [
            1000 => '【{$signature}】您的验证码为 #code#',
        ],
        'Yunpian_en' => [
            1000 => '【{$signature_en}】Your verification code is #code#',
        ],
        'yupian_relevance_en' => [
            1000 => ['template_id' => 981879],
        ],
        'Yuntongxun' => [
            1000 => ['template_id' => 32619],
        ]
    ]
]
```


## message.php 包含发送的封装
$result = app\components\Message::sendSms('930978945', array(
    'code'  => 1234,
    'area'  => '886', # 区号
), 2002);
var_dump($result);
