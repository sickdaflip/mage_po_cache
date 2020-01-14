<?php

class Potato_Crawler_Block_Adminhtml_System_Config_Stat extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_cpuLoad = null;
    protected $_quote = null;
    protected $_counter = null;
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->setTemplate('po_crawler/config/stat.phtml')->toHtml();
    }

    /**
     * @return float|int
     */
    public function getCpuLoadAngle()
    {
        return $this->_getPieAngle($this->getCpuLoad());
    }

    /**
     * @return float|null
     */
    public function getCpuLoad()
    {
        if (null === $this->_cpuLoad) {
            $this->_cpuLoad = round($this->helper('po_crawler/warmer')->getCurrentCpuLoad());
        }
        return $this->_cpuLoad;
    }

    /**
     * @return float|int
     */
    public function getWarmerAngle()
    {
        if (!$progress = $this->getWarmerProgress()) {
            return 2;
        }
        return $this->_getPieAngle($progress);
    }

    /**
     * @return float|int
     */
    public function getWarmerProgress()
    {
        if (!$this->_getCounterSize() && !$this->_getQuoteSize()) {
            return 100;
        }
        if (!$this->_getCounterSize()) {
            return 0;
        }
        return round($this->_getCounterSize() * 100 / ($this->_getQuoteSize() + $this->_getCounterSize()));
    }

    /**
     * @return string
     */
    public function getWarmerStat()
    {
        $total = $this->_getQuoteSize() + $this->_getCounterSize();
        return $this->_getCounterSize() . " / " . $total;
    }

    /**
     * @return null
     */
    protected function _getQuoteSize()
    {
        if (null === $this->_quote) {
            $quoteCollection = Mage::getResourceModel('po_crawler/queue_collection');
            $this->_quote = $quoteCollection->getSize();
        }
        return $this->_quote;
    }

    /**
     * @return int|null
     */
    protected function _getCounterSize()
    {
        if (null === $this->_counter) {
            $statCollection = Mage::getResourceModel('po_crawler/counter_collection');
            $statCollection->addFilterByToday();
            $this->_counter = (int)$statCollection->getFirstItem()->getValue();
        }
        return $this->_counter;
    }

    /**
     * Get Pi value for pie angle
     *
     * @param $value
     * @return float|int
     */
    protected function _getPieAngle($value)
    {
        if (!$value) {
            return 0;
        }
        return 2 * $value / 100 + 2;
    }

    /**
     * Get current warmer speed
     *
     * @return int
     */
    public function getCurrentSpeed()
    {
        return Potato_Crawler_Helper_Warmer::getCurrentSpeed();
    }
}