<?php

class Potato_Crawler_Model_Resource_Queue extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('po_crawler/queue', 'id');
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $write = $this->_getWriteAdapter();
        $write->truncate($this->getTable('po_crawler/queue'));
        return $this;
    }
}