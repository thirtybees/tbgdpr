<?php
/**
 * Copyright (C) 2018 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <contact@thirtybees.com>
 * @copyright 2018 thirty bees
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

$sql = [];

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'tbgdpr_requests` (
                `id_request` INT(12) NOT NULL AUTO_INCREMENT,
                `id_customer` INT(12) NOT NULL,
                `type` VARCHAR(255) NOT NULL,
                `status` VARCHAR(255) NOT NULL,
                `comment` INT(12) NOT NULL,
                `date_added` VARCHAR(255) NOT NULL,
                `date_updated` VARCHAR(255) NOT NULL,
                PRIMARY KEY ( `id_request` )
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'tbgdpr_guest_object` (
                `id_request` INT(12) NOT NULL AUTO_INCREMENT,
                `id_guest` INT(12) NOT NULL,
                `email` VARCHAR(255) NOT NULL,
                `token` VARCHAR(255) NOT NULL,
                `status` VARCHAR(255) NOT NULL,
                `date_added` VARCHAR(255) NOT NULL,
                `date_approved` VARCHAR(255) NOT NULL,
                PRIMARY KEY ( `id_request` )
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
