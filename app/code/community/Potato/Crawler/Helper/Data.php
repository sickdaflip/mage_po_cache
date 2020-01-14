<?php

class Potato_Crawler_Helper_Data extends Mage_Core_Helper_Data
{
    const LOG_FILE_NAME = 'po_crawler.log';

    /**
     * @param int $value
     * @return Potato_Crawler_Model_Stat
     */
    static function increaseCounter($value)
    {
        $date = Mage::getModel('core/date')->date('Y-m-d');
        /** @var Potato_Crawler_Model_Stat $stat */
        $stat = Mage::getModel('po_crawler/counter')->loadByDate(
            $date
        );
        $stat
            ->setDate($date)
            ->add($value)
        ;
        return $stat;
    }

    public function log($message, $params=array())
    {
        if (Potato_Crawler_Helper_Config::canDebug()) {
            Mage::log(vsprintf($this->__($message), $params), 1, self::LOG_FILE_NAME, true);
        }
        return $this;
    }
}