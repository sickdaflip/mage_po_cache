<?php

require_once __DIR__ . '/../abstract.php';
class Potato_Shell_Warmer extends Mage_Shell_Abstract
{
    const LOCK_FILE_NAME = 'warmer.lock';

    public function run()
    {
        if ($this->_isLocked()) {
            return false;
        }
        ini_set('max_execution_time', -1);
        try {
            Mage::getModel('po_crawler/cron_warmer')->process();
        } catch (Exception $e) {
            Mage::logException($e);
        }
        $this->_removeLock();
    }

    protected function _isLocked()
    {
        if (file_exists(__DIR__ . '/' . self::LOCK_FILE_NAME)) {
            $diff = time() - filemtime(__DIR__ . '/' . self::LOCK_FILE_NAME);
            if ($diff < 900) {
                return true;
            }
        }
        file_put_contents(__DIR__ . '/' . self::LOCK_FILE_NAME, getmypid());
        return false;
    }

    protected function _removeLock()
    {
        @unlink(__DIR__ . '/' . self::LOCK_FILE_NAME);
    }
}
$shell = new Potato_Shell_Warmer();
$shell->run();