<?php

declare(strict_types=1);


namespace App\Service;

use App\Common\Tools;
use App\Common\Traits\StaticInstance;
use Swlib\SaberGM;
use DomXPath;

class NewsService
{

    use StaticInstance;

    protected $peopleDailyChina = 'http://www.people.com.cn/rss/politics.xml';
    protected $peopleDailyWorld =  'http://www.people.com.cn/rss/world.xml';
    protected $chinaDaily = 'https://cn.chinadaily.com.cn/';
    protected $scNews = 'http://news.chengdu.cn/';

    public function getNewsXmlArray($url)
    {
        $xml = SaberGM::get($url);
        return $xml->getParsedXmlArray();
    }

    /**
     * 人民日报 国际
     * @param int $all
     * @return array
     */
    public function getPeopleDailyNews($all = 10)
    {
        $news = [];
        if ($this->peopleDailyWorld) {
            $xml = [];
//            foreach ($this->peopleDailyRss as $url) {
                $items = $this->getNewsXmlArray($this->peopleDailyWorld)['channel']['item'];
                $xml = array_merge($xml, array_slice($items, 0, $all));
//            }
            foreach ($xml as $x) {
                $news[$x['title']] = $x['link'];
            }
        }
        return $news;
    }

    /**
     * 中国日报
     * @param int $all
     * @return array
     */
    public function chinaDaily($all = 10)
    {
        $news = [];
        if ($this->chinaDaily) {
            $dom = SaberGM::get($this->chinaDaily)->getParsedDomObject();

            $nodes = (new Tools())->getNodesByClass($dom, 'right-lei');
            $node = $nodes->item(0)->childNodes->item(3)->childNodes;
            for ($i = 0; $i < $all * 2; $i += 2) {
                $linkDom = $node->item($i)->firstChild;
                if ($linkDom) {
                    $news[$linkDom->nodeValue] = 'https:' . $linkDom->getAttribute('href');
                } else {
                    break;
                }
            }
        }
        return $news;
    }

    /**
     * 中国日报四川
     * @param int $all
     * @return array
     */
    public function sichuanNews($all = 10)
    {
        $news = [];
        if ($this->scNews) {
            $dom = SaberGM::get($this->scNews)->getParsedDomObject();
            $nodes = (new Tools())->getNodesByClass($dom, 'search');
            $childNodes = $nodes->item(4)->childNodes;
            for ($i = 0; $i <= $all * 2; $i += 2) {
                $linkDom = $childNodes->item($i);
                if ($linkDom) {
                    $linkDom = $linkDom->childNodes->item(0);
                    $news[$linkDom->nodeValue] = $linkDom->getAttribute('href');
                } else {
                    break;
                }
            }
        }
        return $news;
    }


    /**
     * 每日新闻
     * @return array
     */
    public function todayNews()
    {
        return [
            'sichuan'=>$this->sichuanNews(5),
            'chinaDaily'=>$this->chinaDaily(5),
            'peopleDaily'=>$this->getPeopleDailyNews(5)
        ];
    }
}
