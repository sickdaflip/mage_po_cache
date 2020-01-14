<?php

class Potato_Crawler_Model_Cron_Warmer
{
    protected $_curl = null;
    protected $_acceptableCpu = null;

    const WAITING_TIMEOUT = 30;

    /**
     * @return $this
     */
    public function process()
    {
        if (!Potato_Crawler_Helper_Config::isEnabled()) {
            return $this;
        }

        $this->_acceptableCpu = Mage::helper('po_crawler/warmer')->getAcceptableLoadAverage();
        Mage::helper('po_crawler')->log('Acceptable CPU load %s.', array($this->_acceptableCpu));
        /** @var Potato_Crawler_Model_Resource_Queue_Collection $collection */
        $collection = Mage::getResourceModel('po_crawler/queue_collection');
        $collection
            ->joinPopularity()
        ;
        try {
            $this->_doRequests($collection);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    protected function _updateLock()
    {
        file_put_contents(BP . '/shell/potato/' . Potato_Shell_Warmer::LOCK_FILE_NAME, getmypid());
    }

    /**
     * @param Potato_Crawler_Model_Resource_Queue_Collection $collection
     * @return $this
     */
    protected function _doRequests(Potato_Crawler_Model_Resource_Queue_Collection $collection)
    {
        $urls = array();
        $threads = 1;

        while ($item = $collection->fetchItem()) {
            if (!Potato_Crawler_Helper_Config::isEnabled()) {
                return $this;
            }

            $this->_updateLock();

            /**
             * Prepare crawler options
             */
            $options = array(
                CURLOPT_USERAGENT      => $item->getUseragent(),
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_NOBODY         => true,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_FAILONERROR    => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_COOKIE         => $this->_getCookie($item),
                CURLOPT_FOLLOWLOCATION => true
            );

            if (!Potato_Crawler_Helper_Warmer::isWin()) {
                while (!$threads = $this->_getThreadCount()) {
                    //wait while cpu overload
                    sleep(self::WAITING_TIMEOUT);
                    $this->_acceptableCpu = Mage::helper('po_crawler/warmer')->getAcceptableLoadAverage();
                }
            }
            //prepare options hash
            $hash = md5(implode(',', $options));

            if (!array_key_exists($hash, $urls)) {
                $urls[$hash] = array(
                    'urls' => array(),
                    'options' => array()
                );
            }
            $urls[$hash]['urls'][] = $item->getUrl();
            $urls[$hash]['options'] = $options;

            if (count($urls[$hash]['urls']) >= $threads) {
                //if count prepared urls = thread value -> do request
                $this->_multiRequest($urls[$hash]['urls'], $urls[$hash]['options']);
                //reset urls container and thread value
                $urls[$hash]['urls'] = array();
                $threads = $this->_getThreadCount();
            }

            try {
                //remove url from queue
                $item->delete();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        foreach ($urls as $hash => $params) {
            if (empty($params['urls'])) {
                continue;
            }
            $this->_multiRequest($params['urls'], $params['options']);
        }
        return $this;
    }

    /**
     * Do curl request and calculate warmer speed
     *
     * @param $urls
     * @param $options
     * @return $this
     */
    protected function _multiRequest($urls, $options)
    {
        $timeBefore = time();
        $this->_getCurl()->multiRequest($urls, $options);
        Mage::helper('po_crawler')->log('Urls "%s" have been requested with options %s.', array(implode(",", $urls), $options[CURLOPT_COOKIE]));
        $timeAfter = time();

        Potato_Crawler_Helper_Warmer::calculateSpeed(count($urls), max($timeAfter - $timeBefore, 1));

        try {
            Potato_Crawler_Helper_Data::increaseCounter(count($urls));
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * @return null|Varien_Http_Adapter_Curl
     */
    protected function _getCurl()
    {
        if (null === $this->_curl) {
            $this->_curl = new Varien_Http_Adapter_Curl();
        }
        return $this->_curl;
    }

    /**
     * Prepare cookie for curl request
     *
     * @param Potato_Crawler_Model_Queue $item
     * @return string
     */
    protected function _getCookie(Potato_Crawler_Model_Queue $item)
    {
        return Potato_Crawler_Helper_Warmer::CUSTOMER_GROUP_ID_COOKIE_NAME . '=' . $item->getCustomerGroupId() . ';'
            . Potato_Crawler_Helper_Warmer::FPC_GROUP_ID_COOKIE_NAME . '=' . $item->getCustomerGroupId() . ';'
            . Potato_Crawler_Helper_Warmer::FPC_CURRENCY_COOKIE_NAME . '=' . $item->getCurrency() . ';'
            . Potato_Crawler_Helper_Warmer::FPC_STORE_COOKIE_NAME . '=' . $item->getStoreId() . ';'
            . Potato_Crawler_Helper_Warmer::STORE_COOKIE_NAME  . '=' . $item->getStoreId() . ';'
            . Potato_Crawler_Helper_Warmer::CURRENCY_COOKIE_NAME  . '=' . $item->getCurrency() . ';path=/;';
    }

    /**
     * Calculate acceptable thread count
     *
     * @return float|int
     */
    protected function _getThreadCount()
    {
        if (Potato_Crawler_Helper_Warmer::isWin()) {
            //apache will crashed if $threads > 1
            return 1;
        }
        $currentAvr = Potato_Crawler_Helper_Warmer::getCurrentCpuLoadAvg();
        $thread = round($this->_acceptableCpu - $currentAvr);
        Mage::helper('po_crawler')->log('Current CPU load %s.', array($currentAvr));
        Mage::helper('po_crawler')->log('Thread %s.', array($thread));
        return $thread > 0 ? $thread : 0;
    }
}