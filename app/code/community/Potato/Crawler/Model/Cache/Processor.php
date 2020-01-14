<?php

class Potato_Crawler_Model_Cache_Processor
{
    /**
     * @param $content
     * @return mixed
     */
    public function extractContent($content)
    {
        if ($this->_isAdmin() || !Potato_Crawler_Helper_Config::usePopularity() || !empty($_POST)) {
            return $content;
        }
        try {
            $this->_updateStatistics();
        } catch (Exception $e) {
            //will be 404 page because store website doen't loaded yet
            //Mage::logException($e);
        }
        return $content;
    }

    /**
     * @return bool
     */
    protected function _isAdmin()
    {
        $adminRouteName = (string)Mage::app()->getConfig()->getNode('admin/routers/adminhtml/args/frontName');
        return strpos($this->_getCurrentUrl(), '/' . $adminRouteName) !== False;
    }

    /**
     * Collect popularity pages
     *
     * @return $this
     */
    protected function _updateStatistics()
    {
        if (isset($_COOKIE[Potato_Crawler_Helper_Warmer::STORE_COOKIE_NAME])) {
            //return if is warmer
            return $this;
        }
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        if (!class_exists('Zend_Db_Expr')) {
            include_once "Zend/Db/Expr.php";
        }
        if (!$read->showTableStatus('po_crawler_popularity')) {
            return $this;
        }
        $uri = $this->_getCurrentUrl();

        $popularityTable = Mage::getSingleton('core/resource')->getTableName('po_crawler_popularity');

        //try update table
        $result = $write->update(
            $popularityTable,
            array('view' => new Zend_Db_Expr('view + 1')),
            array(
                'url = ?' => $uri
            )
        );
        if (!$result) {
            //new entry
            $write->insert(
                $popularityTable,
                array(
                    'view' => 1,
                    'url' => $uri
                )
            );
        }
        return $this;
    }

    /**
     * Get current url
     *
     * @return mixed
     */
    protected function _getCurrentUrl()
    {
        $url = explode('?', (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        return trim($url[0], '/');
    }
}