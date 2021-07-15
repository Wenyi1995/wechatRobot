<?php

declare(strict_types=1);


namespace App\Service;

class HolidayService
{
    private $dayList = [
        '2021' => [
            "0101" => 2,
            "0102" => 1,
            "0103" => 1,
            "0211" => 1,
            "0212" => 2,
            "0213" => 2,
            "0214" => 2,
            "0215" => 1,
            "0216" => 1,
            "0217" => 1,
            "0403" => 1,
            "0404" => 2,
            "0405" => 1,
            "0501" => 2,
            "0502" => 1,
            "0503" => 1,
            "0504" => 1,
            "0505" => 1,
            "0612" => 1,
            "0613" => 1,
            "0614" => 2,
            "0919" => 1,
            "0920" => 1,
            "0921" => 2,
            "1001" => 2,
            "1002" => 2,
            "1003" => 2,
            "1004" => 1,
            "1005" => 1,
            "1006" => 1,
            "1007" => 1,
            "0207" => 0,
            "0220" => 0,
            "0425" => 0,
            "0508" => 0,
            "0918" => 0,
            "0926" => 0,
            "1009" => 0
        ],
    ];

    /**
     * 节假日查询
     * @param $time
     * @return bool
     */
    public function holidayCheck($time): bool
    {
        if (in_array(date('md', $time), $this->dayList[date('Y')])) {
            return $this->dayList[date('Y')][date('md', $time)] > 0;
        } else {
            return $this->checkWeekend($time);
        }
    }

    /**
     * 周末查询
     * @param $time
     * @return bool
     */
    public function checkWeekend($time): bool
    {
        $weekDay = date('N', $time);
        return in_array($weekDay, [6, 7]);
    }
}
