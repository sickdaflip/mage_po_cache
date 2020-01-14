<?php

class Potato_Crawler_Model_Resource_Counter extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('po_crawler/counter', 'id');
    }
}