<?php

class Potato_Crawler_Helper_Queue extends Mage_Core_Helper_Data
{
    /**
     * Add url to queue
     *
     * @param $url
     * @param $store
     * @param int $priority
     * @return $this
     */
    public function addUrl($url, $store, $priority=0)
    {
        /** @var Magento_Db_Adapter_Pdo_Mysql $write */
        $write =  Mage::getSingleton('core/resource')->getConnection('core_write');
        $queueTable = Mage::getSingleton('core/resource')->getTableName('po_crawler_queue');
        //store priority
        $basePriority = Potato_Crawler_Helper_Config::getPriority($store) . $priority;
        //sort by customer group priority
        foreach (Potato_Crawler_Helper_Config::getCustomerGroup($store) as $groupPriority => $group) {
            //sort by currency priority
            $availableCurrencies = Mage::app()->getStore($store)->getAvailableCurrencyCodes(true);
            foreach (Potato_Crawler_Helper_Config::getCurrency($store) as $currencyPriority => $currency) {
                if (!in_array($currency, $availableCurrencies)) {
                    continue;
                }
                //sort by user agent
                foreach (Potato_Crawler_Helper_Config::getUserAgents($store) as $userAgent) {
                    //calculate priority
                    $priority = $basePriority . $groupPriority . $currencyPriority . $groupPriority;
                    $data = array(
                        'store_id'          => $store->getId(),
                        'customer_group_id' => $group,
                        'useragent'         => $userAgent['useragent'],
                        'currency'          => $currency,
                        'priority'          => (int)$priority,
                        'url'               => $url
                    );
                    try {
                        $write->insertOnDuplicate($queueTable, $data);
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                    Mage::helper('po_crawler')->log('Url "%s" has been added to the queue with %s priority .', array($url, $priority));
                }
            }
        }
        return $this;
    }

    /**
     * Get stores sorted by priority
     *
     * @return array
     */
    public function getStores()
    {
        $_result = array();
        foreach (Mage::app()->getStores() as $store) {
            if (!$store->getIsActive() || !Potato_Crawler_Helper_Config::isEnabled($store)) {
                continue;
            }
            $priority = $this->_generatePriority(Potato_Crawler_Helper_Config::getPriority($store), $_result);
            $_result[$priority] = $store;
        }
        ksort($_result);
        return $_result;
    }

    protected function _generatePriority($priority, $data)
    {
        while (array_key_exists($priority, $data)) {
            $priority++;
        }
        return $priority;
    }

    /**
     * Add url to queue by path
     *
     * @param $path
     * @param $store
     * @param int $priority
     * @return $this
     */
    public function addUrlByPath($path, $store, $priority=0)
    {
        //sort by protocol priority
        foreach (Potato_Crawler_Helper_Config::getProtocol($store) as $protocolPriority => $protocol) {
            $baseUrl = Potato_Crawler_Helper_Queue::getStoreBaseUrl($store, $protocol);
            $this->addUrl(htmlspecialchars($baseUrl . $path), $store, $priority . $protocolPriority);
        }
        return $this;
    }

    /**
     * @param $store
     * @param $protocol
     * @return string
     */
    static function getStoreBaseUrl($store, $protocol)
    {
        $baseUrl = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        if ($protocol == Potato_Crawler_Model_Source_Protocol::HTTPS_VALUE) {
            $baseUrl = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, true);
        }
        $baseUrl .= $store->getConfig(Mage_Core_Model_Store::XML_PATH_USE_REWRITES) ? '' : 'index.php/';
        if ($store->getConfig(Mage_Core_Model_Store::XML_PATH_STORE_IN_URL)) {
            $baseUrl = trim($baseUrl, '/');
            $baseUrl .= '/' . $store->getCode() . '/';
        }
        return $baseUrl;
    }

    /**
     * @param string $settings
     *
     * @return bool
     */
    static function isMatchingCronSettings($settings)
    {
        $e = preg_split('#\s+#', $settings, null, PREG_SPLIT_NO_EMPTY);
        if (sizeof($e) < 5 || sizeof($e) > 6) {
            return false;
        }
        /** @var Mage_Core_Model_Date $coreDateSingleton */
        $coreDateSingleton = Mage::getSingleton('core/date');
        $d = getdate($coreDateSingleton->timestamp(time()));
        /** @var Mage_Cron_Model_Schedule $cronScheduleModel */
        $cronScheduleModel = Mage::getModel('cron/schedule');
        $match = $cronScheduleModel->matchCronExpression($e[0], $d['minutes'])
            && $cronScheduleModel->matchCronExpression($e[1], $d['hours'])
            && $cronScheduleModel->matchCronExpression($e[2], $d['mday'])
            && $cronScheduleModel->matchCronExpression($e[3], $d['mon'])
            && $cronScheduleModel->matchCronExpression($e[4], $d['wday']);
        return $match;
    }
}