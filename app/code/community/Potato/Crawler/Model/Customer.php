<?php

class Potato_Crawler_Model_Customer extends Mage_Customer_Model_Customer
{
    /**
     * @return int
     */
    public function getGroupId()
    {
        return isset($_COOKIE[Potato_Crawler_Helper_Warmer::CUSTOMER_GROUP_ID_COOKIE_NAME]) ?
            $_COOKIE[Potato_Crawler_Helper_Warmer::CUSTOMER_GROUP_ID_COOKIE_NAME] : Mage_Customer_Model_Group::NOT_LOGGED_IN_ID
        ;
    }

    /**
     * @return $this|Mage_Core_Model_Abstract
     */
    public function save()
    {
        return $this;
    }

    /**
     * @param int  $id
     * @param null $field
     *
     * @return $this|Mage_Core_Model_Abstract
     */
    public function load($id, $field=null)
    {
        return $this;
    }

    /**
     * @return $this|Mage_Core_Model_Abstract
     */
    public function delete()
    {
        return $this;
    }
}