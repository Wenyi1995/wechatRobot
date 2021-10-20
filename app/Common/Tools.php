<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Common;

use DOMXPath;


/**
 * 工具类
 */
class Tools
{

    /**
     * 用class 定位 dom
     * @param $dom
     * @param $class
     * @return \DOMNodeList|false
     */
    public function getNodesByClass($dom, $class)
    {
        $finder = new DOMXPath($dom);
        return $finder->query("//*[contains(@class, '{$class}')]");
    }
}
