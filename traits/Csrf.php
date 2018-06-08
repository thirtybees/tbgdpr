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

namespace TbGdprModule;

use Configuration;
use PrestaShopException;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Trait Csrf
 *
 * @package TbGdprModule
 */
trait Csrf
{
    /**
     * Generate a CSRF token
     *
     * @return string
     *
     * @since 1.0.0
     */
    protected function generateCsrfToken()
    {
        return Tools::getToken(get_class());
    }

    /**
     * Checks if token is valid.
     *
     * @return bool
     *
     * @since   1.0.0
     *
     * @throws PrestaShopException
     */
    public function verifyCsrfToken()
    {
        if (!Configuration::get('PS_TOKEN_ENABLE')) {
            return true;
        }

        return strcasecmp(Tools::getToken(get_class()), Tools::getValue('csrf')) === 0;
    }
}
