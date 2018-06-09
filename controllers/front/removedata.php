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
 * Class TbGdprRemovedataModuleFrontController
 */
class TbGdprRemovedataModuleFrontController extends ModuleFrontController
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
     * @throws Adapter_Exception
     */
    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign([
            'csrf'             => $this->generateCsrfToken(),
            'confirmations'    => $this->confirmations,
            'warnings'         => $this->warnings,
            'errors'           => $this->errors,
            'tbgdpr_request'   => TbGdprRequest::getRemovalRequestForGuest($this->context->cookie->id_guest),
            'tbgdpr_forgotten' => Configuration::getInt(TbGdpr::FORGOTTEN_TEXT)[$this->context->language->id],
        ]);

        $this->setTemplate('removedata/main.tpl');
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
        if (Tools::isSubmit('gdpr-customer-remove')) {
            $this->postProcessCustomerRemoveData();
        } elseif (Tools::isSubmit('gdpr-guest-remove')) {
            $this->postProcessGuestRemoveData();
        } elseif (Tools::isSubmit('gdpr-remove-token')) {
            $this->postProcessGuestVerification();
        }
    }

    /**
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function postProcessCustomerRemoveData()
    {
        if (!$this->verifyCsrfToken()) {
            $this->errors[] = $this->module->l('Unable to confirm request', 'removedata');
            return;
        }
        if (Tools::getValue('accept-gdpr-remove')) {
            if (!Validate::isLoadedObject(TbGdprRequest::getRequestsForGuest($this->context->customer->id))) {
                $request = new TbGdprRequest();
                $request->id_customer = $this->context->customer->id;
                $request->id_guest = $this->context->cookie->id_guest;
                $request->email = $this->context->customer->email;

                $request->request_type = TbGdprRequest::REQUEST_TYPE_REMOVE_DATA;
                $request->status = TbGdprRequest::STATUS_APPROVED;
                $request->comment = '';

                $request->add();

                $result = $request->execute();

                if ($result) {
                    $this->confirmations[] = $this->module->l('Your personal information has been removed.', 'removedata');
                } else {
                    $this->errors[] = $this->module->l('An error has occurred. Please contact customer support.', 'removedata');
                }
            }
        } else {
            $this->errors[] = $this->module->l('Please tick the box in order to confirm that you want your account to be removed.', 'removedata');
        }
    }

    /**
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function postProcessGuestRemoveData()
    {
        if (!$this->verifyCsrfToken()) {
            $this->errors[] = $this->module->l('Unable to confirm request', 'removedata');
            return;
        }
        if (Tools::getValue('accept-gdpr-remove')) {
            if (!TbRemoveRequest::existsForEmail(Tools::getValue('email'))) {
                try {
                    $request = TbRemoveRequest::create(Tools::getValue('email'));
                    if ($this->sendGuestMail($request)) {
                        $request->save();
                        $this->confirmations[] = $this->module->l('An email has been sent to verify your email address. Please click the link to verify.', 'removedata');
                    } else {
                        $this->errors[] = $this->module->l('We were unable to send the required verification email. Please try again later.');
                    }
                } catch (AlreadyRequestedException $e) {
                    $this->errors[] = sprintf(
                        $this->module->l('You have already made a request to remove. Please check your spam folder or try again in %d minutes.'),
                        (int) (Configuration::get(TbGdpr::FORGOTTEN_EMAIL_EXPIRE) ?: TbGdpr::FORGOTTEN_EMAIL_EXPIRE_DEFAULT)
                    );
                }
            }
        } else {
            $this->errors[] = $this->module->l('Please tick the box in order to confirm that you want to be removed from all direct marketing purposes', 'removedata');
        }
    }

    /**
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function postProcessGuestVerification()
    {
        $removeRequest = TbRemoveRequest::getByToken(Tools::getValue('gdpr-remove-token'));
        if (!Validate::isLoadedObject($removeRequest)) {
            $this->errors[] = $this->module->l('We were unable to remove your email address. Perhaps you have already been removed.', 'removedata');
        } else {
            $gdprRequest = new TbGdprRequest();
            $gdprRequest->id_customer = $this->context->customer->id;
            $gdprRequest->id_guest = $this->context->cookie->id_guest;
            $gdprRequest->email = $removeRequest->email;

            $gdprRequest->request_type = TbGdprRequest::REQUEST_TYPE_REMOVE_DATA;
            $gdprRequest->status = TbGdprRequest::STATUS_APPROVED;
            $gdprRequest->comment = '';

            // If someone is not logged in, the Hook actionremoveMember can not be executed via TbGdprRequest
            // execute() method. Therefore, add the request, set as executed and execute actionremoveMember
            // directly.

            $gdprRequest->executed = true;

            $gdprRequest->add();

            Hook::exec(
                'actionremoveMember',
                [
                    'customer' => $gdprRequest->id_customer,
                    'guest'    => $gdprRequest->id_guest,
                    'email'    => $gdprRequest->email,
                    'phone'    => null,
                ]
            );

            $this->confirmations[] = $this->module->l('You have been removed from all direct marketing purposes', 'removedata');
            $removeRequest->delete();
        }

        $this->setTemplate('removedata/guest-verification.tpl');
    }

    /**
     * Send a guest verification mail
     *
     * @param TbRemoveRequest $request
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    protected function sendGuestMail(TbRemoveRequest $request)
    {
        try {
            $templateVars = [
                '{gdpr_token}' => $request->token,
                '{link}'       => $this->context->link->getModuleLink($this->module->name, 'removedata', ['gdpr-remove-token' => $request->token], true),
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
