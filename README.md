# yii2-message-sms

短信网关发送，包含云片，云通讯，云信

默认发送网关是云片，如果短信网关因为网关限制(以下)发送失败，则换下一个网关去发送

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


## 短信网关配置文件

```php
return [
    'smser' => [
        'class' => 'app\extensions\message\Smser',
        'defaultProvider' => 'Yunpian',
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
```php
// 发送短信必须指定模板ID
$result = Message::sendSms('930978945', array(
    'code'  => 1234,
    'area'  => '886', # 区号，支持国际短信
), 1000);
var_dump($result);
```
