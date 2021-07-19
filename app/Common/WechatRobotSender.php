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
        // {"message":"success感谢又拍云(upyun.com)提供CDN赞助","status":200,"date":"20210719","time":"2021-07-19 09:16:32","cityInfo":{"city":"成都市","citykey":"101270101","parent":"四川","updateTime":"07:16"},"data":{"shidu":"80%","pm25":13.0,"pm10":23.0,"quality":"优","wendu":"26","ganmao":"各类人群可自由活动","forecast":[{"date":"19","high":"高温 33℃","low":"低温 23℃","ymd":"2021-07-19","week":"星期一","sunrise":"06:14","sunset":"20:06","aqi":21,"fx":"东南风","fl":"1级","type":"阵雨","notice":"阵雨来袭，出门记得带伞"}],"yesterday":{"date":"18","high":"高温 32℃","low":"低温 23℃","ymd":"2021-07-18","week":"星期日","sunrise":"06:13","sunset":"20:07","aqi":28,"fx":"西南风","fl":"1级","type":"小雨","notice":"雨虽小，注意保暖别感冒"}}}
        $result = SaberGM::get($url);
        if ($result->getStatusCode() == 200) {
            $weatherInfo = json_decode($result->getBody()->getContents(), true);
            if ($weatherInfo['status']) {
                $cityInfo = $weatherInfo['cityInfo'];
                $nowInfo = $weatherInfo['data'];
                $todayInfo = $nowInfo['forecast'][0];
                $markdown = "气温：{$todayInfo['low']} -- {$todayInfo['high']} \n\n";//"high":"高温 33℃","low":"低温 23℃"
                $markdown .= "空气质量：{$nowInfo['quality']} 空气质量指数：{$todayInfo['aqi']}  pm2.5:{$nowInfo['pm25']} \n\n";//"shidu":"80%","pm25":13.0,"pm10":23.0,"quality":"优"
                $markdown .= "风向：{$todayInfo['fx']}   风力:{$todayInfo['fl']}  湿度：{$nowInfo['shidu']} ";//"fx":"东南风","fl":"1级",
                return $this->markdownSender("今天[{$cityInfo['city']}]的天气为[{$todayInfo['type']}]", $markdown);
            }
                CLog::error($weatherInfo['message']);
        }
            return false;
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
        } catch (Throwable $exception) {
            CLog::error($exception->getMessage());
            CLog::error($exception->getFile());
            return false;
        }
    }


}
