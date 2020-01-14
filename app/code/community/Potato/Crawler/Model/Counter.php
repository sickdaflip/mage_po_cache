<?php

class Potato_Crawler_Model_Counter extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('po_crawler/counter');
    }

    /**
     * @param int $value
     * @return $this
     */
    public function add($value=1)
    {
        $this->setValue($this->getValue() + $value);
        $this->save();
        return $this;
    }

    /**
     * @param $date
     * @return Mage_Core_Model_Abstract
     */
    public function loadByDate($date)
    {
        return $this->load($date, 'date');
    }
}