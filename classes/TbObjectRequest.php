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

use TbGdprModule\Exception\AlreadyRequestedException;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class TbObjectRequest
 */
class TbObjectRequest extends TbGdprObjectModel
{
    const STATUS_PENDING = 1;
    const STATUS_VERIFIED = 2;

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
        'table'     => 'tbobject_request',
        'primary'   => 'id_tbobject_request',
        'fields'    => [
            'email'        => ['type' => self::TYPE_STRING, 'validate' => 'isString',      'required' => false, 'db_type' => 'VARCHAR(255)'],
            'status'       => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedInt', 'required' => true,  'db_type' => 'INT(11) UNSIGNED'],
            'id_shop'      => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedInt', 'required' => true,  'db_type' => 'INT(11) UNSIGNED'],
            'token'        => ['type' => self::TYPE_HEX,    'validate' => 'isString',      'required' => false, 'db_type' => 'BINARY(64)'],
            'expires'      => ['type' => self::TYPE_DATE,   'validate' => 'isDate',        'required' => true,  'db_type' => 'DATETIME'],
            'date_add'     => ['type' => self::TYPE_DATE,   'validate' => 'isDate',                             'db_type' => 'DATETIME'],
            'date_upd'     => ['type' => self::TYPE_DATE,   'validate' => 'isDate',                             'db_type' => 'DATETIME'],
        ]
    ];

    /**
     * Create a new object request
     *
     * @param string   $email
     * @param int|null $idShop
     *
     * @return static
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws AlreadyRequestedException
     *
     * @since 1.0.0
     */
    public function create($email, $idShop = null)
    {
        if (!$idShop) {
            $idShop = Context::getContext()->shop->id;
        }
        if (TbGdprRequest::existsForEmail($email, TbGdprRequest::REQUEST_TYPE_OBJECT)) {
            throw new \TbGdprModule\Exception\AlreadyRequestedException('Already requested');
        }

        $objectRequest = new static();
        $objectRequest->email = $email;
        $objectRequest->id_shop = $idShop;
        $objectRequest->token = static::createToken();
        $objectRequest->status = static::STATUS_PENDING;
        $duration = Configuration::get(TbGdpr::OBJECT_EMAIL_EXPIRE);
        if (!$duration) {
            $duration = TbGdpr::OBJECT_EMAIL_EXPIRE_DEFAULT;
        }
        $objectRequest->expires = date('Y-m-d H:i:s', strtotime("+$duration mins"));

        return $objectRequest;
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @param string $email
     *
     * @return bool
     *
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    public function verify($token, $email)
    {
        if ($token === $this->token && $email === $this->email) {
            $this->status = static::STATUS_VERIFIED;
            $this->save();

            return true;
        }

        return false;
    }

    /**
     * Create a token for the verification email
     *
     * @return string
     *
     * @since 1.0.0
     */
    protected static function createToken()
    {
        return hash('sha512', random_bytes(64));
    }
}
