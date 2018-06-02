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
 * Class TbGdprDataportabilityModuleFrontController
 */
class TbGdprDataportabilityModuleFrontController extends ModuleFrontController
{
    /** @var bool $display_column_left */
    public $display_column_left = false;
    /** @var bool $display_column_right */
    public $display_column_right = false;
    /** @var array $confirmations */
    public $confirmations = [];
    /** @var array $warnings */
    public $warnings = [];

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign(array(
            'csrf'               => Tools::getToken('dataportability'),
            'confirmations'       => $this->confirmations,
            'tbgdpr_portability' => Configuration::getInt(TbGdpr::DATAPORTABILITY_TEXT)[$this->context->language->id]
        ));

        $this->setTemplate('dataportability.tpl');
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 1.0.0
     * @throws Adapter_Exception
     */
    public function postProcess()
    {
        //submit removal request
        if (Tools::isSubmit('gdpr-export')) {
            if (!$this->isTokenValid()) {
                $this->errors[] = $this->module->l('Unable to confirm request', 'removedata');
                return;
            }
            if (Tools::getValue('accept-gdpr-export')) {
                if (!Validate::isLoadedObject(TbGdprRequest::getRequestsForGuest($this->context->customer->id))) {
                    $request = new TbGdprRequest();
                    $request->id_customer = $this->context->customer->id;
                    $request->id_guest = $this->context->cookie->id_guest;
                    $request->email = $this->context->customer->email;

                    $request->request_type = TbGdprRequest::REQUEST_TYPE_GET_DATA;
                    $request->status = TbGdprRequest::STATUS_APPROVED;
                    $request->comment = '';

                    $request->add();

                    $result = $request->execute();

                    if ($result) {
                        $this->confirmations[] = $this->module->l('Your personal data has been exported');
                    } else {
                        $this->errors[] = $this->module->l('An error has occurred. Please contact customer support');
                    }
                }
            } else {
                $this->errors[] = $this->module->l('Please tick the box in order to confirm that you want to export your personal data', 'removedata');
            }
        }
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
    public function isTokenValid()
    {
        if (!Configuration::get('PS_TOKEN_ENABLE')) {
            return true;
        }

        return strcasecmp(Tools::getToken('dataportability'), Tools::getValue('csrf')) === 0;
    }
}
