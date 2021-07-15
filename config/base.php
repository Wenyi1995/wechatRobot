<?php

return [
    'name' => 'wechat robot',
    'debug' => env('SWOFT_DEBUG', 1),
    'wechatRobot' => [
        'webHook' => [
            'tee' => [
                env('MY_ROBOT_ADDR')
            ],
        ],
    ],
    'weather'=>[
        'chengdu'=>'http://www.weather.com.cn/data/cityinfo/101270101.html'
    ]
];
