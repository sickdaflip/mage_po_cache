<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('po_fpc/crawler_queue')};
    DROP TABLE IF EXISTS {$this->getTable('po_fpc/popularity')};
");
$installer->endSetup();