<?php

class Potato_Crawler_Model_Cron_Queue
{
    /**
     * @return $this
     */
    public function process()
    {
        try {
            //add website urls to queue
            foreach (Mage::helper('po_crawler/queue')->getStores() as $store) {
                $this->addStoreUrls($store);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * Add to queue specified urls
     *
     * @param $ids
     * @return $this
     */
    public function addSpecified($ids)
    {
        if (empty($ids)) {
            return $this;
        }
        try {
            foreach (Mage::helper('po_crawler/queue')->getStores() as $store) {
                $this->addStoreUrls($store, $ids);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * Add store urls
     *
     * @param $store
     * @param null $ids
     * @return Potato_Crawler_Model_Cron_Queue
     */
    public function addStoreUrls($store, $ids=null)
    {
        if (!Potato_Crawler_Helper_Config::isEnabled($store)) {
            return $this;
        }
        //get store urls source
        $source = Potato_Crawler_Model_Source_UrlSource::getInstance($store);
        if (null !== $ids) {
            $source = new Potato_Crawler_Model_Source_Url_Database($store);
        }
        if ($source instanceof Potato_Crawler_Model_Source_Url_Database) {
            return $this->_addFromDatabase($source, $store, $ids);
        }
        return $this->_addFromSitemap($source, $store);
    }

    /**
     * Add from database source
     *
     * @param $source
     * @param $store
     * @param array $ids
     * @return $this
     */
    protected function _addFromDatabase($source, $store, $ids=array())
    {
        /** @var Potato_Crawler_Helper_Queue $helper */
        $helper = Mage::helper('po_crawler/queue');
        //sort by page priority
        foreach (Potato_Crawler_Helper_Config::getPages($store) as $pagePriority => $type) {
            if ($type == Potato_Crawler_Model_Source_PageGroup::CMS_VALUE) {
                $cmsIds = isset($ids['cms']) ? $ids['cms'] : array();
                $urls = $source->getCmsUrls($cmsIds);
            } elseif ($type == Potato_Crawler_Model_Source_PageGroup::CATEGORY_VALUE) {
                $categoryIds = isset($ids['category']) ? $ids['category'] : array();
                $urls = $source->getCategoryUrls($categoryIds);
            } else {
                $productIds = isset($ids['product']) ? $ids['product'] : array();
                $urls = $source->getProductUrls($productIds);
            }
            //sort by protocol
            foreach (Potato_Crawler_Helper_Config::getProtocol($store) as $protocolPriority => $protocol) {
                $baseUrl = Potato_Crawler_Helper_Queue::getStoreBaseUrl($store, $protocol);
                $this->_updateLock();
                foreach ($urls as $url) {
                    $helper->addUrl(htmlspecialchars($baseUrl . $url), $store, $pagePriority . $protocolPriority);
                }
            }
        }
        return $this;
    }

    /**
     * Add from sitemap
     *
     * @param $source
     * @param $store
     * @return $this
     */
    protected function _addFromSitemap($source, $store)
    {
        /** @var Potato_Crawler_Helper_Queue $helper */
        $helper = Mage::helper('po_crawler/queue');
        $urls = $source->getStoreUrls();
        foreach ($urls as $priority => $group) {
            foreach ($group as $url) {
                $helper->addUrl($url, $store, $priority);
            }
        }
        return $this;
    }

    protected function _updateLock()
    {
        file_put_contents(BP . '/shell/potato/' . Potato_Shell_Warmer_Queue::LOCK_FILE_NAME, getmypid());
    }
}