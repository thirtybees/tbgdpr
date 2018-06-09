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

/**
 * Class TbRemoveRequest
 */
class TbRemoveRequest extends TbVisitorRequest
{
    const STATUS_PENDING = 1;
    const STATUS_VERIFIED = 2;

    const EXPIRE_CONST = TbGdpr::FORGOTTEN_EMAIL_EXPIRE;
    const EXPIRE_CONST_DEFAULT = TbGdpr::FORGOTTEN_EMAIL_EXPIRE_DEFAULT;

    /** @var string $email */
    public $email;
    /** @var int $status */
    public $status;
    /** @var int $id_shop */
    public $id_shop;
    /** @var string $token */
    public $token;
    /** @var string $expires */
    public $expires;
    /** @var string $date_add */
    public $date_add;
    /** @var string $date_upd */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'tbgdpr_remove_request',
        'primary' => 'id_tbgdpr_remove_request',
        'fields'  => [
            'email'    => ['type' => self::TYPE_STRING, 'validate' => 'isString',      'required' => false, 'db_type' => 'VARCHAR(255)'],
            'status'   => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedInt', 'required' => true,  'db_type' => 'INT(11) UNSIGNED'],
            'id_shop'  => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedInt', 'required' => true,  'db_type' => 'INT(11) UNSIGNED'],
            'token'    => ['type' => self::TYPE_HEX,    'validate' => 'isString',      'required' => false, 'db_type' => 'BINARY(64)'],
            'expires'  => ['type' => self::TYPE_DATE,   'validate' => 'isDate',        'required' => true,  'db_type' => 'DATETIME'],
            'date_add' => ['type' => self::TYPE_DATE,   'validate' => 'isDate',                             'db_type' => 'DATETIME'],
            'date_upd' => ['type' => self::TYPE_DATE,   'validate' => 'isDate',                             'db_type' => 'DATETIME'],
        ],
    ];
}
