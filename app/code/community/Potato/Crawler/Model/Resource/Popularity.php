<?php

class Potato_Crawler_Model_Resource_Popularity extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('po_crawler/popularity', 'id');
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $write = $this->_getWriteAdapter();
        $write->truncate($this->getTable('po_crawler/popularity'));
        return $this;
    }
}