<?php

class Potato_Crawler_Model_Resource_Queue_Popularity extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('po_crawler/popularity');
    }

    public function sortByView()
    {
        return $this->setOrder('view');
    }
}