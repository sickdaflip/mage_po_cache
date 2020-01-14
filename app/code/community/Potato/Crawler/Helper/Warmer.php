<?php

class Potato_Crawler_Helper_Warmer extends Mage_Core_Helper_Data
{
    const FPC_GROUP_ID_COOKIE_NAME = 'fpc_group';
    const FPC_CURRENCY_COOKIE_NAME = 'fpc_currency';
    const FPC_STORE_COOKIE_NAME = 'fpc_store';
    const CUSTOMER_GROUP_ID_COOKIE_NAME = 'po_crawler_group';
    const CURRENCY_COOKIE_NAME = 'po_crawler_currency';
    const STORE_COOKIE_NAME = 'po_crawler_store';
    const CACHE_SPEED_INDEX = 'Potato_Crawler_Helper_Warmer::SPEED';
    const CACHE_LIFETIME = 1800;
    /**
     * Get current load average
     *
     * @return int
     */
    public function getCurrentCpuLoad()
    {
        if (self::isWin()) {
            return false;
        }

        $cores = $this->_getCpuCoresNumber();
        $currentAvg = $this->getCurrentCpuLoadAvg();
        $fullLoad = $cores + $cores/2;
        return min(100, $currentAvg * 100 / $fullLoad );
    }

    /**
     * @return int
     */
    static function getCurrentCpuLoadAvg()
    {
        if (!function_exists('sys_getloadavg')) {
            return 99999;
        }
        try {
            $load = sys_getloadavg();
            return $load[0];
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return 99999;
    }

    /**
     * @return float|int|mixed
     */
    public function getAcceptableLoadAverage()
    {
        if (self::isWin()) {
            return 1.5;
        }
        $cores = $this->_getCpuCoresNumber();
        $fullLoad = $cores + $cores/2;
        return $fullLoad * (Potato_Crawler_Helper_Config::getAcceptableCpu() / 100);
    }

    /**
     * @return bool
     */
    static function isWin()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return true;
        }
        return false;
    }

    /**
     * Get CPU cores count (for UNIX only)
     *
     * @return int|mixed
     */
    protected function _getCpuCoresNumber()
    {
        $result = array();
        $status = array();
        try {
            exec('grep -c ^processor /proc/cpuinfo 2>&1', $result, $status);
            if ($status != 0) {
                new Exception(print_r($result, true));
            }
            return $result[0];
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return 1;
    }

    /**
     * @param $urls
     * @param $time
     * @return $this
     */
    static function calculateSpeed($urls, $time)
    {
        Mage::helper('po_crawler')->log('Current Speed %s.', array(($urls / $time) * 3600));
        Mage::app()->saveCache(($urls / $time) * 3600, self::CACHE_SPEED_INDEX, array(), self::CACHE_LIFETIME);
    }

    /**
     * @return int
     */
    static function getCurrentSpeed()
    {
        return (int)Mage::app()->loadCache(self::CACHE_SPEED_INDEX);
    }
}