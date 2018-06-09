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
use TbGdprModule\Tools as GdprTools;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class TbVisitorRequest
 */
class TbVisitorRequest extends TbGdprObjectModel
{
    const STATUS_PENDING = 1;
    const STATUS_VERIFIED = 2;

    const EXPIRE_CONST = TbGdpr::OBJECT_EMAIL_EXPIRE;
    const EXPIRE_CONST_DEFAULT = TbGdpr::OBJECT_EMAIL_EXPIRE_DEFAULT;

    /**
     * Create a new visitor request
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
    public static function create($email, $idShop = null)
    {
        if (!$idShop) {
            $idShop = Context::getContext()->shop->id;
        }
        if ($request = static::existsForEmail($email)) {
            throw new AlreadyRequestedException('Already requested', 0, null, $request);
        }

        $objectRequest = new static();
        $objectRequest->email = $email;
        $objectRequest->id_shop = $idShop;
        $objectRequest->token = static::createToken();
        $objectRequest->status = static::STATUS_PENDING;
        $duration = Configuration::get(static::EXPIRE_CONST);
        if (!$duration) {
            $duration = static::EXPIRE_CONST_DEFAULT;
        }
        $objectRequest->expires = date('Y-m-d H:i:s', strtotime("+$duration mins"));

        return $objectRequest;
    }

    /**
     * Auto add id_shop
     *
     * @param bool $autoDate
     * @param bool $nullValues
     *
     * @return bool|void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    public function add($autoDate = true, $nullValues = false)
    {
        if (!$this->id_shop) {
            $this->id_shop = Context::getContext()->shop->id;
        }
        if (!$this->token) {
            $this->token = static::createToken();
        }

        parent::add($autoDate, $nullValues);
    }

    /**
     * Delete this visitor request
     *
     * @return bool
     *
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    public function delete()
    {
        static::flushExpired();

        return parent::delete();
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
        if (strcasecmp($token, $this->token) && $email === $this->email) {
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

    /**
     * Check if there is a request for the given email address
     * If it already exists the request object will be returned
     *
     * @param string $email
     *
     * @return false|static
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    public static function existsForEmail($email)
    {
        static::flushExpired();
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            (new DbQuery())
                ->select('*')
                ->from(bqSQL(static::$definition['table']))
                ->where('`email` = \''.pSQL($email).'\'')
        );
        if (!is_array($row) || empty($row)) {
            return false;
        }

        $request = new static();
        $request->hydrate($row);

        return $request;
    }

    /**
     * Get visitor request by unsubscribe token
     *
     * @param string $token
     *
     * @return false|static
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    public static function getByToken($token)
    {
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            (new DbQuery())
                ->select('*, HEX(`token`)')
                ->from(bqSQL(static::$definition['table']))
                ->where('`token` = 0x'.pSQL(GdprTools::sanitizeHex($token)))
        );
        if (!is_array($row) || empty($row)) {
            return false;
        }

        $request = new static();
        $request->hydrate($row);

        return $request;
    }

    /**
     * Flush expired requests
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    public static function flushExpired()
    {
        $ttl = (int) (Configuration::get(static::EXPIRE_CONST) ?: static::EXPIRE_CONST_DEFAULT);
        // Check for expired entries as well
        Db::getInstance(_PS_USE_SQL_SLAVE_)->delete(
            bqSQL(static::$definition['table']),
            '`date_add` < \''.date('Y-m-d H:i:s', strtotime("-$ttl mins")).'\''
        );
    }
}
