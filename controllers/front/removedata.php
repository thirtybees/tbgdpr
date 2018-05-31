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
    /** @var string $confirmation */
    public $confirmation = '';
    /** @var bool $display_column_left */
    public $display_column_left = false;
    /** @var bool $display_column_right */
    public $display_column_right = false;
    /** @var string $table_name */
    protected $table_name = 'tbgdpr_requests';

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    public function initContent()
    {
        parent::initContent();
        $customerData = $this->getGdprRemovalRequest();
        $token = Tools::getToken(false);

        $this->context->smarty->assign([
            'token'            => $token,
            'confirmation'     => $this->confirmation,
            'tbgdpr_status'    => $customerData['status'],
            'tbgdpr_comment'   => $customerData['comment'],
            'tbgdpr_forgotten' => Configuration::getInt(TbGdpr::FORGOTTEN_TEXT)[$this->context->language->id],
        ]);

        $this->setTemplate('removedata.tpl');
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    public function postProcess()
    {
        //submit removal request
        if (Tools::isSubmit('gdprremove')) {
            $token = Tools::getValue('token');
            if (!isset($token) || $token != $this->isTokenValid()) {
                die('An error occured');
            }
            if (Tools::getIsset('acceptremove') && Tools::getValue('acceptremove') == 'confirmation') {
                if (!$this->getGdprRemovalRequest()) {
                    $customerData = [];
                    $customerData['id_customer'] = (int) Context::getContext()->customer->id;
                    $customerData['type'] = 'removal';
                    if (Configuration::get('TBGDPR_FORGOTTEN_AUTO' == 1)) {
                        $customerData['status'] = 'pending';
                    } else {
                        $customerData['status'] = 'removed';
                    }
                    $customerData['comment'] = '';
                    $customerData['date_added'] = time();

                    $this->addGdprRemovalRequest($customerData);

                    if (Configuration::get('TBGDPR_FORGOTTEN_AUTO' != 1)) {

                        $customer = new Customer($this->context->customer->id);
                        $result = Hook::exec('actionDeleteGdprCustomer', $customer);

                        if ($result == true) {
                            $this->confirmations[] = $this->module->l('Your personal data has been removed');
                        } else {
                            $this->errors[] = $this->module->l('An error has occurred. Please contact customer support');
                        }
                    }
                }
            } else {
                $customerData = [];
                $customerData['type'] = 'removal';
                if (Configuration::get('TBGDPR_FORGOTTEN_AUTO' == 1)) {
                    $customerData['status'] = 'pending';
                } else {
                    $customerData['status'] = 'removed';
                }
                $customerData['comment'] = '';
                $customerData['date_updated'] = time();
                $this->updateGdprRemovalRequest($customerData);

                if (Configuration::get('TBGDPR_FORGOTTEN_AUTO' != 1)) {

                    $customer = new Customer($this->context->customer->id);
                    $result = Hook::exec('actionDeleteGdprCustomer', $customer);

                    if ($result == true) {
                        $this->confirmations[] = $this->module->l('Your personal data has been removed');
                    } else {
                        $this->errors[] = $this->module->l('An error has occurred. Please contact customer support');
                    }
                }
            }
        }

        //submit cancel removal request
        if (Tools::isSubmit('cancelgdprremove')) {
            $token = Tools::getValue('token');
            if (!isset($token) || $token != $this->isTokenValid()) {
                die('An error occured');
            }
            if (Tools::getIsset('acceptremove') && Tools::getValue('acceptremove') == 'confirmation') {
                $customerData = [];
                $customerData['type'] = 'removal';
                $customerData['status'] = 'canceled';
                $customerData['comment'] = '';
                $customerData['date_updated'] = time();
                $this->updateGdprRemovalRequest($customerData);
            }
        }
    }

    /**
     * @return array|bool|null|object
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function getGdprRemovalRequest()
    {
        $customerId = $this->context->customer->id;
        $customerData = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            (new DbQuery())
                ->select('*')
                ->from($this->table_name)
                ->where('`type` = \'removal\' AND `id_customer` = \''.pSQL($customerId).'\'')
        );

        if (count($customerData) > 0) {
            return $customerData;
        } else {
            return false;
        }
    }

    /**
     * @param $customerData
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    protected function addGdprRemovalRequest($customerData)
    {
        if (!Db::getInstance()->insert(
            pSQL($this->table_name),
            $customerData
        )
        ) {
            $this->_errors[] = Tools::displayError('Error while adding request for removal');
        }
    }

    /**
     * @param $customerData
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function updateGdprRemovalRequest($customerData)
    {
        $customerId = $this->context->customer->id;
        if (!Db::getInstance()->update(
            pSQL($this->table_name),
            $customerData,
            'id_customer = '.pSQL($customerId).' AND type = "removal"'
        )
        ) {
            $this->_errors[] = Tools::displayError('Error while updating request for removal');
        } else {
            $this->confirmations[] = 'Your request for removal has been updated';
        }
    }
}
