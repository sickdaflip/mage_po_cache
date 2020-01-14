<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('po_crawler/queue')} (
    `id` INT ( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `store_id` SMALLINT ( 5 ) UNSIGNED NOT NULL,
    `url` VARCHAR( 255 ) NOT NULL,
    `customer_group_id` SMALLINT ( 5 ) NOT NULL,
    `useragent` VARCHAR ( 255 ) NOT NULL,
    `currency` VARCHAR ( 3 ) NOT NULL,
    `priority` INT( 10 ) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY ( `id` )
    ) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
    ALTER TABLE {$this->getTable('po_crawler/queue')} ADD UNIQUE `po_crawler_queue_unique_index`(`store_id`, `url`, `customer_group_id`, `useragent`, `currency`);
    CREATE TABLE IF NOT EXISTS {$this->getTable('po_crawler/popularity')} (
    `id` INT ( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `url` VARCHAR( 255 ) NOT NULL,
    `view` INT( 10 ) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY ( `id` )
    ) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
    CREATE TABLE IF NOT EXISTS {$this->getTable('po_crawler/counter')} (
    `id` INT ( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
    `value` INT ( 7 ) UNSIGNED NOT NULL,
    `date` DATE NOT NULL,
    PRIMARY KEY ( `id` )
    ) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
    CREATE INDEX url ON {$this->getTable('po_crawler/queue')} (url);
    CREATE INDEX url ON {$this->getTable('po_crawler/popularity')} (url);
    ALTER TABLE {$this->getTable('po_crawler/popularity')} ADD UNIQUE (url);
");
$installer->endSetup();