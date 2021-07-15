<?php

declare(strict_types=1);

namespace App\Common;


use Swlib\SaberGM;
use Swoft\Log\Helper\CLog;

class WechatRobotSender
{

    protected $group = '';

    public function __construct($group)
    {
        $this->group = $group;
    }

    protected function sender($param): bool
    {
        $configList = config('wechatRobot.webHook')[$this->group];
        $urlList = [];
        foreach ($configList as $url) {
            $urlList[] = ['uri' => $url];
        }
        $saber = SaberGM::requests($urlList, $param);
        if ($saber->count() == count($urlList)) {
            return true;
        } else {
            CLog::error($saber->serialize());
            return false;
        }
    }

    public function msgSender($msg): bool
    {
        $param = [
            'method' => 'POST',
            'json' => [
                'msgtype' => 'text',
                'text' => [
                    'content' => $msg,
                ]
            ]

        ];
        return $this->sender($param);
    }

    public function markdownSender($title, $content): bool
    {
        $markdownContent = "#### {$title}\n\n{$content}";
        $param = [
            'method' => 'POST',
            'json' => [
                'msgtype' => 'markdown',
                'markdown' => [
                    'content' => $markdownContent,
                ]
            ]

        ];

        return $this->sender($param);
    }


    public function imgSender($base64, $md5): bool
    {
        $param = [
            'method' => 'POST',
            'json' => [
                'msgtype' => 'image',
                'image' => [
                    'base64' => $base64,
                    'md5' => $md5,
                ]
            ]

        ];
        return $this->sender($param);
    }

    public function weatherSender()
    {
        $url = config('weather.chengdu');
        // "{"weatherinfo":{"city":"成都","cityid":"101270101","temp1":"16℃","temp2":"28℃","weather":"阵雨转晴","img1":"n3.gif","img2":"d0.gif","ptime":"18:00"}}"
        $result = SaberGM::get($url);
        if ($result->getStatusCode() == 200) {
            $weatherInfo = json_decode($result->getBody()->getContents(), true)['weatherinfo'];
            $msg = "今天[{$weatherInfo['city']}]天气为【{$weatherInfo['weather']}】,气温({$weatherInfo['temp1']}) -- ({$weatherInfo['temp2']})";
            return $this->msgSender($msg);
        } else {
            return false;
        }
    }

    public function newsSender()
    {
        $url = config('news');
        $result = SaberGM::get($url);
        if ($result->getStatusCode() == 200) {
            preg_match_all('|<title>(.*?)</title>\s*<link>(.*?)</link>|', $result->getBody()->getContents(), $results);
            $markdown = '';
            for ($i = 1; $i < 11; $i++) { // 取前5条新闻
                $markdown .= "{$i}. [{$results[1][$i]}]({$results[2][$i]})" . "\n\n";
            }
            return $this->markdownSender('最近这一天都发生了啥？', $markdown);
        } else {
            return false;
        }
    }
}
