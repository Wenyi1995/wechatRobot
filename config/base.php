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
    'weather' => [
        'chengdu' => 'http://t.weather.itboy.net/api/weather/city/101270101'
    ],
    'news' => 'http://news.qq.com/newsgn/rss_newsgn.xml'
];
