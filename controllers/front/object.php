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

use TbGdprModule\Exception\AlreadyRequestedException;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class TbGdprObjectModuleFrontController
 */
class TbGdprObjectModuleFrontController extends ModuleFrontController
{
    use \TbGdprModule\Csrf;

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

        $this->context->smarty->assign([
            'csrf'                => $this->generateCsrfToken(),
            'confirmations'       => $this->confirmations,
            'tbgdpr_object'       => Configuration::getInt(TbGdpr::OBJECT_TEXT)[$this->context->language->id],
        ]);

        $this->setTemplate('object/main.tpl');
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
        if (Tools::isSubmit('gdpr-customer-object')) {
            $this->postProcessCustomerObject();
        } elseif (Tools::isSubmit('gdpr-guest-object')) {
            $this->postProcessGuestObject();
        } elseif (Tools::isSubmit('gdpr-unsubscribe-token')) {
            $this->postProcessGuestVerification();
        }
    }

    /**
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function postProcessCustomerObject()
    {
        if (!$this->verifyCsrfToken()) {
            $this->errors[] = $this->module->l('Unable to confirm request', 'object');
            return;
        }
        if (Tools::getValue('accept-gdpr-object')) {
            if (!Validate::isLoadedObject(TbGdprRequest::getRequestsForGuest($this->context->customer->id))) {
                $request = new TbGdprRequest();
                $request->id_customer = $this->context->customer->id;
                $request->id_guest = $this->context->cookie->id_guest;
                $request->email = $this->context->customer->email;

                $request->request_type = TbGdprRequest::REQUEST_TYPE_OBJECT;
                $request->status = TbGdprRequest::STATUS_APPROVED;
                $request->comment = '';

                $request->add();

                $result = $request->execute();

                if ($result) {
                    $this->confirmations[] = $this->module->l('You have been removed from all direct marketing purposes', 'object');
                } else {
                    $this->errors[] = $this->module->l('An error has occurred. Please contact customer support', 'object');
                }
            }
        } else {
            $this->errors[] = $this->module->l('Please tick the box in order to confirm that you want to be removed from all direct marketing purposes', 'object');
        }
    }

    /**
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function postProcessGuestObject()
    {
        if (!$this->verifyCsrfToken()) {
            $this->errors[] = $this->module->l('Unable to confirm request', 'object');
            return;
        }
        if (Tools::getValue('accept-gdpr-object')) {
            if (!TbObjectRequest::existsForEmail(Tools::getValue('email'))) {
                try {
                    $request = TbObjectRequest::create(Tools::getValue('email'));
                    if ($this->sendGuestMail($request)) {
                        $request->save();
                        $this->confirmations[] = $this->module->l('An email has been sent to verify your email address. Please click the link to verify.', 'object');
                    } else {
                        $this->errors[] = $this->module->l('We were unable to send the required verification email. Please try again later.');
                    }
                } catch (AlreadyRequestedException $e) {
                    $this->errors[] = sprintf(
                        $this->module->l('You have already made a request to unsubscribe. Please check your spam folder or try again in %d minutes.'),
                        (int) (Configuration::get(TbGdpr::OBJECT_EMAIL_EXPIRE) ?: TbGdpr::OBJECT_EMAIL_EXPIRE_DEFAULT)
                    );
                }
            }
        } else {
            $this->errors[] = $this->module->l('Please tick the box in order to confirm that you want to be removed from all direct marketing purposes', 'object');
        }
    }

    /**
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function postProcessGuestVerification()
    {
        $objectRequest = TbObjectRequest::getByToken(Tools::getValue('gdpr-unsubscribe-token'));
        if (!Validate::isLoadedObject($objectRequest)) {
            $this->errors[] = $this->module->l('We were unable to unsubscribe your email address. Perhaps you have already been unsubscribed.', 'object');
        } else {
            $gdprRequest = new TbGdprRequest();
            $gdprRequest->id_customer = $this->context->customer->id;
            $gdprRequest->id_guest = $this->context->cookie->id_guest;
            $gdprRequest->email = $objectRequest->email;

            $gdprRequest->request_type = TbGdprRequest::REQUEST_TYPE_OBJECT;
            $gdprRequest->status = TbGdprRequest::STATUS_APPROVED;
            $gdprRequest->comment = '';

            // If someone is not logged in, the Hook actionUnsubscribeMember can not be executed via TbGdprRequest
            // execute() method. Therefore, add the request, set as executed and execute actionUnsubscribeMember
            // directly.

            $gdprRequest->executed = true;

            $gdprRequest->add();

            Hook::exec(
                'actionUnsubscribeMember',
                [
                    'customer' => $gdprRequest->id_customer,
                    'guest'    => $gdprRequest->id_guest,
                    'email'    => $gdprRequest->email,
                    'phone'    => null,
                ]
            );

            $this->confirmations[] = $this->module->l('You have been removed from all direct marketing purposes', 'object');
            $objectRequest->delete();
        }

        $this->setTemplate('object/guest-verification.tpl');
    }

    /**
     * Send a guest verification mail
     *
     * @param TbObjectRequest $request
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    protected function sendGuestMail(TbObjectRequest $request)
    {
        try {
            $templateVars = [
                '{gdpr_token}' => $request->token,
                '{link}'       => $this->context->link->getModuleLink($this->module->name, 'object', ['gdpr-unsubscribe-token' => $request->token], true),
            ];

            $mailDir = false;
            if (file_exists(_PS_THEME_DIR_."modules/{$this->module->name}/mails/en/tbgdpr_guest_verification.txt")
                && file_exists(
                    _PS_THEME_DIR_."modules/{$this->module->name}/mails/en/tbgdpr_guest_verification.html"
                )
            ) {
                $mailDir = _PS_THEME_DIR_."modules/{$this->module->name}/mails/";
            } elseif (file_exists(__DIR__."/../../mails/en/tbgdpr_guest_verification.txt")
                && file_exists(__DIR__."/../../mails/en/tbgdpr_guest_verification.html")
            ) {
                $mailDir = __DIR__.'/../../mails/';
            }

            if ($mailDir) {
                return Mail::Send(
                    $this->context->language->id,
                    'tbgdpr_guest_verification',
                    Translate::getModuleTranslation($this->module->name, 'Please verify your email address', $this->module->name),
                    $templateVars,
                    (string) $request->email,
                    null,
                    (string) Configuration::get(
                        'PS_SHOP_EMAIL',
                        null,
                        null,
                        Context::getContext()->shop->id
                    ),
                    (string) Configuration::get(
                        'PS_SHOP_NAME',
                        null,
                        null,
                        Context::getContext()->shop->id
                    ),
                    null,
                    null,
                    $mailDir,
                    false,
                    Context::getContext()->shop->id
                );
            }
        } catch (PrestaShopException $e) {
            Logger::addLog("TbGdpr module error: {$e->getMessage()}");

            return false;
        }

        return false;
    }
}
