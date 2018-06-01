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

use PrestaShopException;
use TbGdprRequest;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Trait AdminAjax
 *
 * @package TbGdprModule
 */
trait AdminAjax
{
    /**
     * @throws \Adapter_Exception
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     *
     * @since 1.0.0
     */
    public function ajaxProcessApproveRemovalRequest()
    {
        $input = @json_decode(file_get_contents('php://input'), true);
        if (!isset($input['idRequest'])) {
            die(json_encode([
                'success' => false,
                'message' => $this->l('Request not found', 'AdminAjax'),
            ]));
        }

        $request = new TbGdprRequest($input['idRequest']);
        if ($request->approve()) {
            try {
                $request->execute();
            } catch (PrestaShopException $e) {
                die(json_encode([
                    'success' => false,
                    'message' => $this->l('An error occurred while updating the request', 'AdminAjax'),
                ]));
            }

            die(json_encode([
                'success' => true,
                'message' => $this->l('Request successfully updated', 'AdminAjax'),
            ]));
        }

        die(json_encode([
            'success' => false,
            'message' => $this->l('An error occurred while updating the request', 'AdminAjax'),
        ]));
    }

    /**
     * @throws \Adapter_Exception
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     *
     * @since 1.0.0
     */
    public function ajaxProcessDenyRemovalRequest()
    {
        $input = @json_decode(file_get_contents('php://input'), true);
        if (!isset($input['idRequest'])) {
            die(json_encode([
                'success' => false,
                'message' => $this->l('Request not found', 'AdminAjax'),
            ]));
        }

        $request = new TbGdprRequest($input['idRequest']);
        if ($request->deny()) {
            try {
                $request->execute();
            } catch (PrestaShopException $e) {
                die(json_encode([
                    'success' => false,
                    'message' => $this->l('An error occurred while updating the request', 'AdminAjax'),
                ]));
            }

            die(json_encode([
                'success' => true,
                'message' => $this->l('Request successfully updated', 'AdminAjax'),
            ]));
        }

        die(json_encode([
            'success' => false,
            'message' => $this->l('An error occurred while updating the request', 'AdminAjax'),
        ]));
    }
}
