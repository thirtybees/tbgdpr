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

use Module;
use TbGdpr;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Tools
 *
 * @package TbGdprModule
 */
class Tools
{
    /**
     * @return array
     */
    public static function defaultConsentSettings()
    {
        return [
            TbGdpr::DISPLAY_POSITION            => 1,
            TbGdpr::DISPLAY_LAYOUT              => 3,
            TbGdpr::DISPLAY_PALETTE_BANNER      => '#000000',
            TbGdpr::DISPLAY_PALETTE_BANNER_TEXT => '#ffffff',
            TbGdpr::DISPLAY_PALETTE_BUTTON      => '#f1d600',
            TbGdpr::DISPLAY_PALETTE_BUTTON_TEXT => '#000000',
            TbGdpr::DISPLAY_LEARN_MORE_LINK     => [
                'en' => 'http://cookies.insites.com/',
            ],
            TbGdpr::DISPLAY_MESSAGE_TEXT        => [
                'en' => 'By using this site you agree to our privacy settings.',
            ],
            TbGdpr::DISPLAY_DISMISS_TEXT        => [
                'en' => 'Got it!',
            ],
            TbGdpr::DISPLAY_POLICY_TEXT         => [
                'en' => 'Learn more',
            ],
        ];
    }

    /**
     * Find compliant modules
     *
     * @return array
     *
     * @since 1.0.0
     */
    public static function findCompliantModules()
    {
        try {
            $modules = Module::getModulesOnDisk(true);
        } catch (\Adapter_Exception $e) {
            return [];
        } catch (\PrestaShopDatabaseException $e) {
            return [];
        } catch (\PrestaShopException $e) {
            return [];
        }
        foreach ($modules as $index => &$module) {
            try {
                $reflection = new \ReflectionClass($module->name);
                $methods = array_map(function ($item) {
                    return strtolower($item->name);
                }, $reflection->getMethods());
                $methods = array_intersect([
                    'hookactiondeletegdprcustomer',
                    'hookregistergdprconsent',
                    'actionunsubscribemember',
                    'actionsetgdprmoduledefaults',
                    'actiongetgdprmodulestatuses',
                    'actionexportgdprdata',
                    'setgdprconsent',
                    'getgdprconsent',
                ], $methods);
                if (count($methods) < 1) {
                    unset($modules[$index]);
                    continue;
                }
                $module->methods = $methods;
            } catch (\ReflectionException $e) {
                unset($modules[$index]);
                continue;
            }
        }
        usort($modules, function ($a, $b) {
            return count($a->methods) > count($b->methods);
        });

        return $modules;
    }
}
