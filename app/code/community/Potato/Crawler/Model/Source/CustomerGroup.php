<?php

class Potato_Crawler_Model_Source_CustomerGroup
{
    const GUEST_VALUE = 'guest';

    /**
     * @return mixed
     */
    static function toOptionArray()
    {
        $options = Mage::getResourceModel('customer/group_collection')
            ->loadData()
            ->toOptionArray()
        ;
        $options[0]['value'] = self::GUEST_VALUE;
        return $options;
    }
}