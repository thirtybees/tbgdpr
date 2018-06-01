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
 * Class TbGdprRemovedataModuleFrontController
 */
class TbGdprRemovedataModuleFrontController extends ModuleFrontController
{
    /** @var bool $display_column_left */
    public $display_column_left = false;
    /** @var bool $display_column_right */
    public $display_column_right = false;
    /** @var string $table_name */
    protected $table_name = 'tbgdpr_requests';
    /** @var array $confirmations */
    public $confirmations = [];

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 1.0.0
     * @throws Adapter_Exception
     */
    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign([
            'csrf'             => Tools::getToken('removedata'),
            'confirmations'    => $this->confirmations,
            'errors'           => $this->errors,
            'tbgdpr_request'   => TbGdprRequest::getRemovalRequestForGuest($this->context->cookie->id_guest),
            'tbgdpr_forgotten' => Configuration::getInt(TbGdpr::FORGOTTEN_TEXT)[$this->context->language->id],
        ]);

        $this->setTemplate('removedata.tpl');
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
        if (Tools::isSubmit('gdpr-remove')) {
            if (!$this->isTokenValid()) {
                die('An error occured');
            }
            if (Tools::getValue('accept-gdpr-remove')) {
                if (!Validate::isLoadedObject(TbGdprRequest::getRemovalRequestForGuest($this->context->customer->id))) {
                    $request = new TbGdprRequest();
                    $request->id_customer = $this->context->customer->id;
                    $request->id_guest = $this->context->cookie->id_guest;
                    $request->email = $this->context->customer->email;

                    $request->request_type = TbGdprRequest::REQUEST_TYPE_REMOVE_DATA;
                    $request->status = Configuration::get(TbGdpr::FORGOTTEN_AUTO)
                        ? TbGdprRequest::STATUS_PENDING
                        : TbGdprRequest::STATUS_APPROVED;
                    $request->comment = '';

                    $request->add();

                    if (Configuration::get(TbGdpr::FORGOTTEN_AUTO)) {
                        $result = $request->execute();
                        if ($result) {
                            $this->confirmations[] = $this->module->l('Your personal data has been removed');
                        } else {
                            $this->errors[] = $this->module->l('An error has occurred. Please contact customer support');
                        }
                    }
                }
            } else {
//                $customerData = [];
//                $customerData['type'] = 'removal';
//                if (Configuration::get('TBGDPR_FORGOTTEN_AUTO' == 1)) {
//                    $customerData['status'] = 'pending';
//                } else {
//                    $customerData['status'] = 'removed';
//                }
//                $customerData['comment'] = '';
//                $customerData['date_updated'] = time();
//                $this->updateGdprRemovalRequest($customerData);
//
//                if (Configuration::get('TBGDPR_FORGOTTEN_AUTO' != 1)) {
//
//                    $customer = new Customer($this->context->customer->id);
//                    $result = Hook::exec('actionDeleteGdprCustomer', $customer);
//
//                    if ($result == true) {
//                        $this->confirmations[] = $this->module->l('Your personal data has been removed');
//                    } else {
//                        $this->errors[] = $this->module->l('An error has occurred. Please contact customer support');
//                    }
//                }
            }
        }

        //submit cancel removal request
        if (Tools::getValue('cancel-gdpr-remove')) {
            if (!$this->isTokenValid()) {
                die('An error occurred');
            }
            if (Tools::getValue('accept-remove')) {
//                $customerData = [];
//                $customerData['type'] = 'removal';
//                $customerData['status'] = 'canceled';
//                $customerData['comment'] = '';
//                $customerData['date_updated'] = time();
//                $this->updateGdprRemovalRequest($customerData);
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

        return strcasecmp(Tools::getToken('removedata'), Tools::getValue('csrf')) === 0;
    }
}
