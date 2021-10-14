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
        $holiday = HolidayService::instance()->holidayCheck(time());
        if (!$holiday) {
            $fileName = alias('@base/public/image/yinchaBase64.txt');
            $base64 = Co::readFile($fileName);
            if ($base64) {
                WechatRobotSender::instance('tee')->imgSender($base64, '1e212bd2e0e598aba19dd60536481da8');
            }
        }
    }

    /**
     * @Cron("15 29 12 * * *")
     */
    public function lunch()
    {
        $holiday = HolidayService::instance()->holidayCheck(time());
        if (!$holiday) {
            WechatRobotSender::instance('tee')->msgSender('喂！十二点几咧！做……做撚啊做！恰饭先啊！');
        }
    }

    /**
     * @Cron("05 01 09 * * *")
     */
    public function morningPush()
    {
        WechatRobotSender::instance('tee')->weatherSender();
        WechatRobotSender::instance('tee')->newsSender();
    }

    /**
     * @Cron("19 30 18 * * *")
     */
    public function offWorkSender()
    {
        $holiday = HolidayService::instance()->holidayCheck(time());
        if (!$holiday) {
            WechatRobotSender::instance('tee')->offWorkSender();
        }
    }
}
