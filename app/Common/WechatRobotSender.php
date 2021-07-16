<?php

declare(strict_types=1);

namespace App\Common;

use App\Service\NewsService;
use Swlib\SaberGM;
use Throwable;
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
        $markdownContent = "### {$title}\n\n{$content}";
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
        try {
            $news = (new NewsService())->todayNews();
            $markdown = '';
            $titleArr = [
                'sichuan' => '本地新闻',
                'chinaDaily' => '全国新闻',
                'peopleDaily' => '国际新闻',
            ];
            $i = 1;
            foreach ($news as $title => $newArr) {
                if (count($newArr) > 0) {
                    if (isset($titleArr[$title])) {
                        $markdown .= "#### {$titleArr[$title]}\n\n";
                        $i = 1;
                    }
                    foreach ($newArr as $t => $l) {
                        $markdown .= "{$i}. [{$t}]({$l})" . "\n\n";
                        $i++;
                    }
                }
            }

            if ($markdown) {
                return $this->markdownSender('过去这一天都发生了啥？', $markdown);
            } else {
                return false;
            }
        }catch (Throwable $exception){
            CLog::error($exception->getMessage());
            CLog::error($exception->getFile());
            return false;
        }
    }


}
