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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Trait ReactTranslations
 *
 * @package TbGdprModule
 */
trait ReactTranslations
{
    /**
     * @return array
     */
    public function getReactTranslations()
    {
        return [
            'next'        => $this->l('Next', 'ReactTranslations'),
            'previous'    => $this->l('Previous', 'ReactTranslations'),
            'loading'     => $this->l('Loading', 'ReactTranslations'),
            'noRowsFound' => $this->l('No rows found', 'ReactTranslations'),
            'page'        => $this->l('Page', 'ReactTranslations'),
            'of'          => $this->l('of', 'ReactTranslations'),
            'yes'         => $this->l('Yes', 'ReactTranslations'),
            'no'          => $this->l('No', 'ReactTranslations'),
            'dateAdded'   => $this->l('Date Added', 'ReactTranslations'),
        ];
    }
}
