<?php

class Potato_Crawler_Model_Customer_Session extends Mage_Customer_Model_Session
{
    /**
     * @return Potato_Crawler_Model_Customer
     */
    public function getCustomer()
    {
        return Mage::getSingleton('po_crawler/customer');
    }

    /**
     * @return int|mixed|null
     */
    public function getCustomerId()
    {
        return $this->getCustomer()->getId();
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->getCustomer()->getGroupId();
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return (bool)$this->getCustomer()->getGroupId();
    }

    /**
     * @param int $customerId
     *
     * @return bool
     */
    public function checkCustomerId($customerId)
    {
        return (bool)$this->getCustomer()->getGroupId();
    }
}