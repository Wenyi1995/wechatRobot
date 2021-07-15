<?php
declare(strict_types=1);

namespace App\Common;


use Swlib\SaberGM;
use Swoft\Log\Helper\CLog;

class WechatRobotSender
{
    public function msgSender($msg, $robotGroup): bool
    {
        $configList = config('wechatRobot.webHook')[$robotGroup];
        $urlList = [];
        foreach ($configList as $url) {
            $urlList[] = ['uri' => $url];
        }
        $saber = SaberGM::requests($urlList, [
            'method' => 'POST',
            'json' => [
                'msgtype' => 'text',
                'text' => [
                    'content' => $msg,
                ]
            ]

        ]);
        if ($saber->count() == count($urlList)) {
            return true;
        } else {
            CLog::error($saber->serialize());
            return false;
        }
    }


    public function imgSender($base64, $md5, $robotGroup): bool
    {
        $configList = config('wechatRobot.webHook')[$robotGroup];
        $urlList = [];
        foreach ($configList as $url) {
            $urlList[] = ['uri' => $url];
        }
        $saber = SaberGM::requests($urlList, [
            'method' => 'POST',
            'json' => [
                'msgtype' => 'image',
                'image' => [
                    'base64' => $base64,
                    'md5' => $md5,
                ]
            ]

        ]);
        if ($saber->count() == count($urlList)) {
            var_dump($saber->serialize());
            return true;
        } else {
            CLog::error($saber->serialize());
            return false;
        }
    }

    public function weatherSender($group)
    {

        $url = config('weather.chengdu');
        // "{"weatherinfo":{"city":"成都","cityid":"101270101","temp1":"16℃","temp2":"28℃","weather":"阵雨转晴","img1":"n3.gif","img2":"d0.gif","ptime":"18:00"}}"
        $result =  SaberGM::get($url);
        if($result->getStatusCode() == 200){
            $weatherInfo = json_decode($result->getBody()->getContents(),true)['weatherinfo'];
            $msg = "今天[{$weatherInfo['city']}]天气为【{$weatherInfo['weather']}】,气温({$weatherInfo['temp1']}) -- ({$weatherInfo['temp2']})";
            return $this->msgSender($msg,$group);
        }else{
            return false;
        }
    }
}
