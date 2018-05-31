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
    /** @var string $confirmation */
    public $confirmation = '';
    /** @var bool $display_column_left */
    public $display_column_left = false;
    /** @var bool $display_column_right */
    public $display_column_right = false;
    /** @var string $table_name */
    protected $table_name = 'tbgdpr_guest_object';

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Adapter_Exception
     */
    public function initContent()
    {
        parent::initContent();

        $getObjectRequest = Tools::getValue('link');

        if ($getObjectRequest && $this->isMD5($getObjectRequest)) {

            $getData = Db::getInstance()->getRow(
                'SELECT * FROM '._DB_PREFIX_.pSQL($this->table_name).
                ' WHERE token = '.pSQL($getObjectRequest)
            );

            $data = [];
            $data['status'] = 'approved';
            $data['date_approved'] = time();

            $this->updateGuestObjectRequest($data);

            $customer = new Customer($this->context->customer->id);
            $guest = new Guest($getData['id_guest']);

            $member = [
                'customer' => $customer,
                'guest'    => $guest,
                'email'    => $getData['email'],
                'phone'    => null,
            ];

            $result = Hook::exec('actionUnsubscribeMember', $member);

            if ($result == true) {
                $this->confirmation[] = $this->module->l('Successfully removed personal data');
            } else {
                $this->errors[] = $this->module->l('There was an error processing your request. Please contact customer support');
            }
        }

        $customer = new Customer($this->context->customer->id);
        $customerMobilePhone = Address::initialize(Address::getFirstCustomerAddressId(Context::getContext()->customer->id))->phone_mobile;
        $token = Tools::getToken(false);
        $guestId = $this->context->cookie->id_guest;
        $status = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            (new DbQuery())
                ->select('*')
                ->from(bqSQL($this->table_name))
                ->where('`guest_id` = \''.pSQL($guestId).'\'')
        );

        $this->context->smarty->assign([
            'status'              => $status['status'],
            'customerEmail'       => $customer->email,
            'token'               => $token,
            'customerMobilePhone' => $customerMobilePhone,
            'tbgdpr_object'       => Configuration::get(TbGdpr::OBJECT_TEXT)[$this->context->language->id],
        ]);

        $this->setTemplate('object.tpl');
    }

    /**
     * @throws PrestaShopException
     * @throws Adapter_Exception
     *
     * @since 1.0.0
     */
    public function postProcess()
    {
        //submit customer form
        if (Tools::isSubmit('submitcustomerobject')) {
            $token = Tools::getValue('token');
            if (!isset($token) || $token != $this->isTokenValid()) {
                die('An error occured');
            }
            if (Tools::getIsset('agreeobject') &&
                Tools::getValue('agreeobject') == 'confirmation'
            ) {
                $customer = $this->context->customer;
                $customerEmail = Tools::getValue('email');
                $customerPhone = Tools::getValue('phone');
                $guest = new Guest($this->context->cookie->id_guest);

                $member = [
                    'customer' => $customer,
                    'guest'    => $guest,
                    'email'    => $customerEmail,
                    'phone'    => $customerPhone,
                ];

                $result = Hook::exec('actionUnsubscribeMember', $member);

                if ($result == true) {
                    $this->confirmation[] = $this->module->l('Successfully removed personal data');
                } else {
                    $this->errors[] = $this->module->l('There was an error processing your request. Please contact customer support');
                }

            }
        }

        //submit guest form
        if (Tools::isSubmit('submitguestobject')) {
            $token = Tools::getValue('token');
            if (!isset($token) || $token != $this->isTokenValid()) {
                die('An error occured');
            }
            if (Tools::getIsset('agreeobject') &&
                Tools::getValue('agreeobject') == 'confirmation'
            ) {
                $guestEmail = Tools::getValue('email');

                $data = [];
                $data['id_guest'] = $this->context->cookie->id_guest;
                $data['email'] = $guestEmail;
                $data['token'] = $activation_link = md5(uniqid(rand(), true));
                $data['status'] = 'pending';
                $data['date_added'] = time();

                $this->addGuestObjectRequest($data);

                $idLang = $this->context->cookie->id_lang;
                $mailToken = md5(uniqid(rand(), true));
                $link = $this->context->link->getModuleLink($this->name, 'object').'&link='.$mailToken;

                Mail::Send($idLang,
                    'guest_object',
                    $this->l('Unsubscribe from direct marketing'),
                    [
                        '{link}' => $link,
                    ],
                    $customer->email,
                    null,
                    null,
                    null,
                    null,
                    null,
                    'modules/tbgdpr/mails/');
            }
        }
    }

    /**
     * @param $data
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    protected function addGuestObjectRequest($data)
    {
        if (!Db::getInstance()->insert(
            pSQL($this->table_name),
            $data
        )
        ) {
            $this->_errors[] = Tools::displayError('Error while adding the request to object');
        }
    }

    /**
     * @param $data
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    protected function updateGuestObjectRequest($data)
    {
        if (!Db::getInstance()->update(
            pSQL($this->table_name),
            $data
        )
        ) {
            $this->_errors[] = Tools::displayError('Error while updating request to object');
        }
    }

    /**
     * @param $str
     *
     * @return bool
     *
     * @since 1.0.0
     */
    private function isMD5($str)
    {
        for ($i = 0; $i < strlen($str); $i++) {
            if (!(($str[$i] >= 'a' && $str[$i] <= 'z') || ($str[$i] >= '0' && $str[$i] <= '9'))) {
                return false;
            }
        }

        return true;
    }
}
