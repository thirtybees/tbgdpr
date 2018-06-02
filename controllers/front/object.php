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
 * Class TbGdprObjectModuleFrontController
 */
class TbGdprObjectModuleFrontController extends ModuleFrontController
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

        $customer = new Customer($this->context->customer->id);
        $customerMobilePhone = Address::initialize(Address::getFirstCustomerAddressId(Context::getContext()->customer->id))->phone_mobile;

        $this->context->smarty->assign(array(
            'csrf'                => Tools::getToken('object'),
            'confirmations'       => $this->confirmations,
            'tbgdpr_object'       => Configuration::getInt(TbGdpr::OBJECT_TEXT)[$this->context->language->id],
            'customerEmail'       => $customer->email,
            'customerMobilePhone' => $customerMobilePhone
        ));

        $this->setTemplate('object.tpl');
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
        // submit customer object to direct marketing
        if (Tools::isSubmit('gdpr-customer-object')) {
            if (!$this->isTokenValid()) {
                $this->errors[] = $this->module->l('Unable to confirm request', 'object');
                return;
            }
            if (Tools::getValue('accept-gdpr-object')) {
                if (!Validate::isLoadedObject(TbGdprRequest::getRequestsForGuest($this->context->customer->id))) {
                    $request = new TbGdprRequest();
                    $request->id_customer = $this->context->customer->id;
                    $request->id_guest = $this->context->cookie->id_guest;
                    $request->email = Tools::getValue('email');

                    $request->request_type = TbGdprRequest::REQUEST_TYPE_OBJECT;
                    $request->status = TbGdprRequest::STATUS_APPROVED;
                    $request->comment = '';

                    $request->add();

                    $result = $request->execute();

                    if ($result) {
                        $this->confirmations[] = $this->module->l('You have been removed from all direct marketing purposes');
                    } else {
                        $this->errors[] = $this->module->l('An error has occurred. Please contact customer support');
                    }
                }
            } else {
                $this->errors[] = $this->module->l('Please tick the box in order to confirm that you want to be removed from all direct marketing purposes', 'object');
            }
        }
        // submit guest object to direct marketing
        if (Tools::isSubmit('gdpr-guest-object')) {
            if (!$this->isTokenValid()) {
                $this->errors[] = $this->module->l('Unable to confirm request', 'object');
                return;
            }
            if (Tools::getValue('accept-gdpr-object')) {
                if (!Validate::isLoadedObject(TbGdprRequest::getRequestsForGuest($this->context->customer->id))) {
                    $request = new TbGdprRequest();
                    $request->id_customer = $this->context->customer->id;
                    $request->id_guest = $this->context->cookie->id_guest;
                    $request->email = Tools::getValue('email');

                    $request->request_type = TbGdprRequest::REQUEST_TYPE_OBJECT;
                    $request->status = TbGdprRequest::STATUS_APPROVED;
                    $request->comment = '';

                    // If someone is not logged in, the Hook actionUnsubscribeMember can not be executed via TbGdprRequest
                    // execute() method. Therefore, add the request, set as executed and execute actionUnsubscribeMember
                    // directly.

                    $request->executed = 1;

                    $request->add();

                    $result = Hook::exec('actionUnsubscribeMember', ['customer' => $request->id_customer, 'guest' => $request->id_guest, 'email' => $request->email, 'phone' => null]);

                    if ($result) {
                        $this->confirmations[] = $this->module->l('You have been removed from all direct marketing purposes');
                    } else {
                        $this->errors[] = $this->module->l('An error has occurred. Please contact customer support');
                    }
                }
            } else {
                $this->errors[] = $this->module->l('Please tick the box in order to confirm that you want to be removed from all direct marketing purposes', 'object');
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

        return strcasecmp(Tools::getToken('object'), Tools::getValue('csrf')) === 0;
    }
}
