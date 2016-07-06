<?php

$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE {$this->getTable('alexsales/transaction')} (
  `transaction_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `commit_id` INT(11) NULL,
  `type` VARCHAR(20) NULL,
  `xpos_user_id` INT(4) UNSIGNED NULL,
  `created_time` DATETIME NULL,
  `order_id` VARCHAR(20) NULL,
  `comment` VARCHAR(255) NULL,
  `points` FLOAT DEFAULT 0,
  PRIMARY KEY (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
CREATE TABLE {$this->getTable('alexsales/commit')} (
  `commit_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `xpos_user_id` INT(4) UNSIGNED NULL,
  `time` DATETIME NULL,
  `balance` FLOAT DEFAULT 0,
  `points` FLOAT,
  PRIMARY KEY (`commit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();