<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    CREATE INDEX size ON {$this->getTable('po_fpc/storage')} (size);
");
$installer->endSetup();