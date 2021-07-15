<?php

declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Task\Crontab;

use App\Common\WechatRobotSender;
use App\Service\HolidayService;
use Swoft\Co;
use Swoft\Crontab\Annotaion\Mapping\Cron;
use Swoft\Crontab\Annotaion\Mapping\Scheduled;

/**
 * Class CronTask
 *
 * @since 2.0
 *
 * @Scheduled()
 */
class CronTask
{

    /**
     * @Cron("15 3 15 * * *")
     */
    public function tee()
    {
        $holiday = (new HolidayService())->holidayCheck(time());
        if (!$holiday) {
            $fileName =  alias('@base/public/image/yinchaBase64.txt');
            $base64 =  Co::readFile($fileName);
            if($base64) {
                (new WechatRobotSender('tee'))->imgSender($base64, '1e212bd2e0e598aba19dd60536481da8');
            }
        }
    }

    /**
     * @Cron("15 29 12 * * *")
     */
    public function lunch()
    {
        $holiday = (new HolidayService())->holidayCheck(time());
        if (!$holiday) {
            (new WechatRobotSender('tee'))->msgSender('喂！十二点几咧！做……做撚啊做！恰饭先啊！');
        }
    }

    /**
     * @Cron("30 30 08 * * *")
     */
    public function weather()
    {
        $holiday = (new HolidayService())->holidayCheck(time());
        if (!$holiday) {
            (new WechatRobotSender('tee'))->weatherSender();
        }
    }

    /**
     * @Cron("05 01 09 * * *")
     */
    public function news()
    {
        (new WechatRobotSender('tee'))->newsSender();
    }
}
