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
        $customerData = $this->getGdprExportRequest();
        $token = Tools::getToken(false);

        $this->context->smarty->assign(array(
            'token' => $token,
            'confirmation' => $this->confirmation,
            'tbgdpr_status' => $customerData['status'],
            'tbgdpr_portability' => Configuration::getInt(TbGdpr::DATAPORTABILITY_TEXT)[$this->context->language->id]
        ));

        $this->setTemplate('dataportability.tpl');
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    public function postProcess()
    {
        //submit export data request
        if (Tools::isSubmit('gdprexport')) {
            $token = Tools::getValue('token');
            if (!isset($token) || $token != $this->isTokenValid()) {
                die('An error occured');
            }
            if (Tools::getIsset('acceptexport') && Tools::getValue('acceptexport') == 'confirmation') {
                if (!$this->getGdprExportRequest()) {
                    $customerData = array();
                    $customerData['id_customer'] = (int)Context::getContext()->customer->id;
                    $customerData['type'] = 'export';
                    $customerData['status'] = 'approved';
                    $customerData['comment'] = '';
                    $customerData['date_added'] = time();

                    $this->addGdprExportRequest($customerData);

                    $customer = new Customer($this->context->customer->id);
                    $result = Hook::exec('actionExportGdprData', $customer);

                    if ($result == true) {
                        $this->confirmations[] = $this->module->l('Your personal data has been exported');
                    } else {
                        $this->errors[] = $this->module->l('An error has occurred. Please contact customer support');
                    }
                }

            } else {
                $customerData = array();
                $customerData['type'] = 'export';
                $customerData['status'] = 'approved';
                $customerData['comment'] = '';
                $customerData['date_updated'] = time();
                $this->updateGdprExportRequest($customerData);

                $customer = new Customer($this->context->customer->id);
                $result = Hook::exec('actionExportGdprData', $customer);

                if ($result == true) {
                    $this->confirmations[] = $this->module->l('Your personal data has been exported');
                } else {
                    $this->errors[] = $this->module->l('An error has occurred. Please contact customer support');
                }
            }
        }
    }

    /**
     * @return mixed
     *
     * @since 1.0.0
     */
    protected function getVersionNumber()
    {
        $version = _PS_VERSION_;
        $getNumber = explode(".", $version);
        $versionNumber = $getNumber[1];

        return $versionNumber;
    }

    /**
     * @return array|bool|null|object
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    protected function getGdprExportRequest()
    {
        $customerId = $this->context->customer->id;
        $customerData = Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . pSQL($this->table_name) .
            ' WHERE type = "export" AND id_customer = ' . pSQL($customerId)
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
    protected function addGdprExportRequest($customerData)
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
     *
     * @since 1.0.0
     */
    protected function updateGdprExportRequest($customerData)
    {
        $customerId = $this->context->customer->id;
        if (!Db::getInstance()->update(
            pSQL($this->table_name),
            $customerData,
            'id_customer = ' . pSQL($customerId) . ' AND type = "export"'
        )) {
            $this->_errors[] = Tools::displayError('Error while updating request export request');
        } else {
            $this->confirmations[] = 'Your export request has been updated';
        }
    }
}
