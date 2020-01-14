<?php

class Potato_Crawler_Model_Resource_Queue_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('po_crawler/queue');
    }

    public function sortByPriority()
    {
        return $this->setOrder('priority', self::SORT_ORDER_ASC);
    }

    public function joinPopularity()
    {
        $this->getSelect()
            ->joinLeft(
                array(
                    'popularity' => $this->getTable('po_crawler/popularity')
                ),
                'main_table.url = popularity.url',
                array('view' => 'popularity.view')
            )
            ->order(array('popularity.view DESC','main_table.priority ASC'));
        ;
        return $this;
    }
}