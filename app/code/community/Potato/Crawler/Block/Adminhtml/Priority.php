<?php

class Potato_Crawler_Block_Adminhtml_Priority
    extends Mage_Adminhtml_Block_Abstract
{
    public function __construct()
    {
        $this->setTemplate('po_crawler/config/priority.phtml');
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $className = (string)$this->getElement()->getFieldConfig()->options;
        $options = $className::toOptionArray();
        return $this->_sortOptions($options);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->getElement()->getName();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->getElement()->getId();
    }

    /**
     * Sort options by priority
     *
     * @param $options
     * @return array
     */
    protected function _sortOptions($options)
    {
        if (!$values = $this->getElement()->getValue()) {
            return $options;
        }
        $valuesArray = explode(',', $values);
        $_result = array();
        foreach ($valuesArray as $value) {
            foreach ($options as $option) {
                if ($option['value'] == $value) {
                    $_result[] = $option;
                    break;
                }
            }
        }
        foreach ($options as $option) {
            if (!in_array($option['value'], $valuesArray)) {
                $_result[] = $option;
            }
        }
        return $_result;
    }

    /**
     * Check if option disabled
     *
     * @param $value
     * @return bool
     */
    public function isDisabled($value)
    {
        if ($values = $this->getElement()->getValue()) {
            $valuesArray = explode(",", $values);
            if (!in_array($value, $valuesArray)) {
                return true;
            }
        }
        return false;
    }
}