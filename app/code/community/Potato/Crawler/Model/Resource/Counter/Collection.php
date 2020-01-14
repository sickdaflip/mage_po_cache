<?php

class Potato_Crawler_Model_Resource_Counter_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('po_crawler/counter');
    }

    public function addFilterByToday()
    {
        $date = Mage::getModel('core/date')->date('Y-m-d');
        return $this->addFieldToFilter('date', $date);
    }
}