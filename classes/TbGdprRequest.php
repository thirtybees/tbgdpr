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
 * Class TbGdprRequest
 */
class TbGdprRequest extends TbGdprObjectModel
{
    const REQUEST_TYPE_GET_DATA = 1;
    const REQUEST_TYPE_REMOVE_DATA = 2;
    
    const STATUS_PENDING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_DENIED = 3;

    /** @var int $id_customer */
    public $id_customer;
    /** @var int $id_guest */
    public $id_guest;
    /** @var string $email */
    public $email;
    /** @var int $request_type */
    public $request_type;
    /** @var int $status */
    public $status;
    /** @var bool $executed */
    public $executed;
    /** @var string $comment */
    public $comment;
    /** @var int $id_shop */
    public $id_shop;
    /** @var string $data_add */
    public $data_add;
    /** @var string $data_upd */
    public $data_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'     => 'tbgdpr_request',
        'primary'   => 'id_tbgdpr_request',
        'fields'    => [
            'id_customer'  => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedInt',  'required' => false, 'db_type' => 'INT(11) UNSIGNED'],
            'id_guest'     => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedInt',  'required' => false, 'db_type' => 'INT(11) UNSIGNED'],
            'email'        => ['type' => self::TYPE_HEX, 'validate' => 'isString', 'required' => false, 'db_type' => 'BINARY(64)'],
            'request_type' => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedInt',  'required' => true,  'db_type' => 'INT(11) UNSIGNED'],
            'status'       => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedInt',  'required' => true,  'db_type' => 'INT(11) UNSIGNED'],
            'executed'     => ['type' => self::TYPE_BOOL,   'validate' => 'isBool',         'required' => false, 'db_type' => 'TINYINT(1) UNSIGNED'],
            'comment'      => ['type' => self::TYPE_STRING, 'validate' => 'isString',       'required' => false, 'db_type' => 'TEXT'],
            'id_shop'      => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedInt',  'required' => true,  'db_type' => 'INT(11) UNSIGNED'],
            'date_add'     => ['type' => self::TYPE_DATE,   'validate' => 'isDate',                              'db_type' => 'DATETIME'],
            'date_upd'     => ['type' => self::TYPE_DATE,   'validate' => 'isDate',                              'db_type' => 'DATETIME'],
        ]
    ];

    /**
     * Execute the approved request (only works if status = approved)
     *
     * @param bool $force Force executing this action
     *
     * @return bool|array Hook output or status
     *
     * @throws PrestaShopException
     * @since 1.0.0
     */
    public function execute($force = false)
    {
        if (!$this->status === static::STATUS_APPROVED) {
            return false;
        }
        if (!$this->executed && !$force) {
            return true;
        }

        $result = true;
        switch ($this->request_type) {
            case static::REQUEST_TYPE_REMOVE_DATA:
                $customer = new Customer($this->context->customer->id);
                $result = Hook::exec('actionDeleteGdprCustomer', $customer);
                break;
        }

        $this->executed = true;
        $this->save();

        return $result;
    }

    /**
     * Add request
     *
     * @param bool $autoDate
     * @param bool $nullValues
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    public function add($autoDate = true, $nullValues = false)
    {
        if (Validate::isEmail($this->email)) {
            $this->email = hash('sha256', $this->email);
        }

        if (!$this->id_shop) {
            $this->id_shop = Context::getContext()->shop->id;
        }

        return parent::add($autoDate, $nullValues);
    }

    /**
     * Get requests for Guest ID
     *
     * @param int $idGuest
     *
     * @return TbGdprRequest[]
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    public static function getRequestsForGuest($idGuest)
    {
        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('*')
                ->from(bqSQL(static::$definition['table']))
                ->where('`id_guest` = '.(int) $idGuest.' '.Shop::addSqlRestriction())
        );
        if (!is_array($results)) {
            return [];
        }

        $requests = [];
        foreach ($results as $result) {
            $request = new static();
            $request->hydrate($result);
            $requests[] = $request;
        }

        return $requests;
    }

    /**
     * @param int $idGuest
     *
     * @return TbGdprRequest
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    public static function getRemovalRequestForGuest($idGuest)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            (new DbQuery())
                ->select('*')
                ->from(bqSQL(static::$definition['table']))
                ->where('`id_guest` = '.(int) $idGuest.' '.Shop::addSqlRestriction())
                ->where('`request_type` = '.(int) static::REQUEST_TYPE_GET_DATA)
        );

        $request = new static();
        if (is_array($result)) {
            $request->hydrate($result);
        }

        return $request;
    }
}
