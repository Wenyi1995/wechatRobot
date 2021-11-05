<?php

declare(strict_types=1);


namespace App\Service;

use App\Common\Tools;
use App\Common\Traits\StaticInstance;
use Swlib\Saber\Request;
use Swlib\SaberGM;

class ChandaoService
{

    use StaticInstance;

    protected $userPhone = [];

    private $tdMap = [
        0 => 'id',
        2 => 'severity',
        4 => 'pri',
        6 => 'title',
        8 => 'active',
        10 => 'maker',
        12 => 'dealer',
    ];

    protected $cookie;
    protected $baseUrl = 'http://bug.xbwq.com.cn/index.php';
    protected $loginUrl = 'http://bug.xbwq.com.cn/index.php?m=user&f=login';
    protected $bugUrl = 'http://bug.xbwq.com.cn/index.php?m=bug&f=browse&productid=32&branch=0&browseType=unresolved&param=0';

    private $requestFlag = 0;

    public function __construct()
    {
        $this->userPhone = json_decode(env('WECHAT_USER_PHONE','{}'),true);
    }
    /**
     * 获取bug的消息
     * @return array
     */
    public function getSendInfo()
    {
        $cookie = $this->getLoginCookie();
        $response = $this->requestChandao($cookie);
        $content = $response->getBody()->getContents();
        if (strlen($content) <= 300) {
            $this->delLoginCookie();
            $cookie = $this->getLoginCookie();
            $response = $this->requestChandao($cookie);
        }
        $return = [];
        $userBugList = $this->getBugList($response->getParsedDomObject());
        foreach ($this->userPhone as $name => $phone) {
            if (isset($userBugList[$name])) {
                $userBugs = [];
                foreach ($userBugList[$name] as $bug) {
                    $userBugs[] = [
                        'title' => $bug['title'],
                        'severity' => $bug['severity'],
                        'maker' => $bug['maker'],
                    ];
                }
                $severity = array_column($userBugs, 'severity');
                array_multisort($severity, SORT_ASC, $userBugs);
                $return[$phone] = array_values($userBugs);
            }
        }
        return $return;
    }

    /**
     * 访问禅道
     * @param $cookie
     * @return \Swlib\Saber\Response
     */
    protected function requestChandao($cookie)
    {
        return SaberGM::get($this->bugUrl, [
            'iconv' => false,
            'before' => [
                function (Request $request) use ($cookie) {
                    $request->cookies->adds($cookie);
                }
            ],
        ]);
    }

    /**
     * dom文本匹配
     * @param $domObject
     * @return array
     */
    protected function getBugList($domObject)
    {
        $trs = (new Tools())->getNodesByClass($domObject, 'text-center');
        $userBugMap = [];
        foreach ($trs as $tr) {
            $tdInfo = [];
            foreach ($this->tdMap as $td => $key) {
                $tdInfo[$key] = $tr->childNodes->item($td)->nodeValue;
                $tdInfo[$key] = str_replace("[已确认]", "", $tdInfo[$key]);
                $tdInfo[$key] = str_replace("[未确认]", "", $tdInfo[$key]);
                $tdInfo[$key] = preg_replace("/\s/", "", $tdInfo[$key]);
                $tdInfo[$key] = ltrim($tdInfo[$key]);
                $tdInfo[$key] = rtrim($tdInfo[$key]);
            }
            $userBugMap[$tdInfo['dealer']][] = $tdInfo;
        }
        return $userBugMap;
    }

    /**
     * 获取登陆的cookie
     * @return \Swlib\Http\Cookies
     */
    protected function getLoginCookie()
    {
        $redis = (new RedisService())->getConnect(3);

        $cookie = $redis->get('CHANDAO_LOGIN_TOKEN');
        if ($cookie) {
            $cookie = unserialize($cookie);
        } else {
            $response = SaberGM::post($this->loginUrl, [
                'account' => env('CHANDAO_USER'),
                'password' => md5(env('CHANDAO_PASS'))
            ], [
                'iconv' => false,
            ]);
            $cookie = $response->cookies;
            $redis->set('CHANDAO_LOGIN_TOKEN', serialize($cookie), 86000);
        }

        return $cookie;
    }

    /**
     * 删除登陆cookie
     * @return int
     */
    protected function delLoginCookie()
    {
        return (new RedisService())->getConnect(3)->del('CHANDAO_LOGIN_TOKEN');
    }
}
