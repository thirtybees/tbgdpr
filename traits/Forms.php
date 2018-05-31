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

use PrestaShopDatabaseException;
use PrestaShopException;
use SmartyException;
use ReflectionClass;
use ReflectionException;
use Tools;
use HelperForm;
use Language;
use Configuration;
use TbGdprModule\Tools as GdprTools;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Trait Forms
 *
 * @package TbGdprModule
 */
trait Forms
{
    /**
     * @return string
     *
     * @throws ReflectionException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function displayDataSecurityForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $this->fields_form = [];

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&'.http_build_query([
                'configure'   => $this->name,
                'tab_module'  => $this->tab,
                'module_name' => $this->name,
            ]);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getDataSecurityForm()]);
    }

    /**
     * @return array
     *
     * @since 1.0.0
     */
    public function getDataSecurityForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Data security & management', 'Forms'),
                    'icon'  => 'icon-list',
                ],
                'input'  => [
                    [
                        'type'  => 'compliant_modules',
                        'label' => $this->l('Compliant modules', 'Forms'),
                        'name'  => static::DISPLAY_LEARN_MORE_LINK,
                        'lang'  => true,
                        'modules' => GdprTools::findCompliantModules(),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save', 'Forms'),
                ],
            ],
        ];
    }

    /**
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 1.0.0
     * @throws SmartyException
     * @throws ReflectionException
     */
    public function displayConsentModalForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $this->fields_form = [];

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&'.http_build_query([
                'configure'   => $this->name,
                'tab_module'  => $this->tab,
                'module_name' => $this->name,
            ]);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([
            $this->getConsentModalDesignForm(),
            $this->getConsentModalCspForm(),
        ]);
    }

    /**
     * @return array
     *
     * @since 1.0.0
     */
    public function getConsentModalDesignForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Consent Modal Design', 'Forms'),
                    'icon'  => 'icon-paint-brush',
                ],
                'input'  => [
                    [
                        'type'   => 'radio',
                        'label'  => $this->l('Position', 'Forms'),
                        'name'   => static::DISPLAY_POSITION,
                        'values' => [
                            [
                                'id'    => 'banner',
                                'value' => 1,
                                'label' => $this->l('Banner bottom', 'Forms'),
                            ],
                            [
                                'id'    => 'banner_top',
                                'value' => 2,
                                'label' => $this->l('Banner top', 'Forms'),
                            ],
                            [
                                'id'    => 'banner_top_pushdown',
                                'value' => 3,
                                'label' => $this->l('Banner top (pushdown)', 'Forms'),
                            ],
                            [
                                'id'    => 'floating_left',
                                'value' => 4,
                                'label' => $this->l('Floating left', 'Forms'),
                            ],
                            [
                                'id'    => 'floating_right',
                                'value' => 5,
                                'label' => $this->l('Floating right', 'Forms'),
                            ],
                        ],
                    ],
                    [
                        'type'   => 'radio',
                        'label'  => $this->l('Layout', 'Forms'),
                        'name'   => static::DISPLAY_LAYOUT,
                        'values' => [
                            [
                                'id'    => 'block',
                                'value' => 1,
                                'label' => $this->l('Block', 'Forms'),
                            ],
                            [
                                'id'    => 'edgeless',
                                'value' => 2,
                                'label' => $this->l('Edgeless', 'Forms'),
                            ],
                            [
                                'id'    => 'classic',
                                'value' => 3,
                                'label' => $this->l('Classic', 'Forms'),
                            ],
                            [
                                'id'    => 'wire',
                                'value' => 4,
                                'label' => $this->l('Wire', 'Forms'),
                            ],
                        ],
                    ],
                    [
                        'type'  => 'color',
                        'label' => $this->l('Banner color', 'Forms'),
                        'name'  => static::DISPLAY_PALETTE_BANNER,
                    ],
                    [
                        'type'  => 'color',
                        'label' => $this->l('Banner text color', 'Forms'),
                        'name'  => static::DISPLAY_PALETTE_BANNER_TEXT,
                    ],
                    [
                        'type'  => 'color',
                        'label' => $this->l('Button color', 'Forms'),
                        'name'  => static::DISPLAY_PALETTE_BUTTON,
                    ],
                    [
                        'type'  => 'color',
                        'label' => $this->l('Button text color', 'Forms'),
                        'name'  => static::DISPLAY_PALETTE_BUTTON_TEXT,
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->l('Learn more link', 'Forms'),
                        'name'  => static::DISPLAY_LEARN_MORE_LINK,
                        'lang'  => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save', 'Forms'),
                ],
            ],
        ];
    }

    /**
     * @return array
     *
     * @since 1.0.0
     * @throws ReflectionException
     */
    public function getConsentModalCspForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Content Security Policy', 'Forms'),
                    'icon'  => 'icon-shield',
                ],
                'description' => $this->display((new ReflectionClass($this))->getFileName(), 'views/templates/admin/tabs/restrict/csp-desc.tpl'),
                'input'  => [
                    [
                        'type'   => 'content-security-policy',
                        'label'  => $this->l('Content Security Policy', 'Forms'),
                        'name'   => static::DISPLAY_POSITION,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save', 'Forms'),
                ],
            ],
        ];
    }

    /**
     * @return string
     *
     * @throws ReflectionException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function displayRightToBeAnonymousForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $this->fields_form = [];

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&'.http_build_query([
                'configure'   => $this->name,
                'tab_module'  => $this->tab,
                'module_name' => $this->name,
            ]);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getRightToBeAnonymousForm()]);
    }

    /**
     * @return array
     *
     * @throws ReflectionException
     */
    public function getRightToBeAnonymousForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Right to be anonymous', 'Forms'),
                    'icon'  => 'icon-shield',
                ],
                'description' => $this->display((new ReflectionClass($this))->getFileName(), 'views/templates/admin/tabs/anonymous/desc.tpl'),
                'input' => [
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Enable this tool'),
                        'name'    => static::ANONYMOUS_ENABLED,
                        'is_bool' => true,
                        'values'  => [
                            [
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type'         => 'textarea',
                        'label'        => $this->l('Right to be anonymous', 'Forms'),
                        'hint'         => $this->l('Content displayed on the Right to be anonymous information page', 'Forms'),
                        'name'         => static::ANONYMOUS_TEXT,
                        'required'     => false,
                        'lang'         => true,
                        'autoload_rte' => false,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save', 'Forms'),
                    'class' => 'btn btn-default pull-right',
                    'name'  => 'tbgdprsubmit',
                ],
            ],
        ];
    }

    /**
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws ReflectionException
     */
    public function displayRightToBeInformedForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $this->fields_form = [];

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&'.http_build_query([
                'configure'   => $this->name,
                'tab_module'  => $this->tab,
                'module_name' => $this->name,
            ]);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getRightToBeInformedForm()]);
    }

    /**
     * @return array
     *
     * @throws ReflectionException
     */
    public function getRightToBeInformedForm()
    {
        $reflection = new ReflectionClass($this);
        $filename = $reflection->getFileName();

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Right to be informed', 'Forms'),
                    'icon'  => 'icon-shield',
                ],
                'description' => $this->display($filename, 'views/templates/admin/tabs/informed/desc.tpl'),
                'input'  => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable this tool'),
                        'name' => static::FORGOTTEN_ENABLED,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            ]
                        ],
                    ],
                    [
                        'type'         => 'textarea',
                        'label'        => $this->l('Right to be informed', 'Forms'),
                        'hint'         => $this->l('Content displayed on the Right to be informed page', 'Forms'),
                        'name'         => static::INFORMED_TEXT,
                        'required'     => false,
                        'lang'         => true,
                        'autoload_rte' => false,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save', 'Forms'),
                    'class' => 'btn btn-default pull-right',
                    'name'  => 'tbgdprsubmit',
                ],
            ],
        ];
    }

    /**
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws ReflectionException
     */
    public function displayRightToCorrectInformationForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $this->fields_form = [];

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&'.http_build_query([
                'configure'   => $this->name,
                'tab_module'  => $this->tab,
                'module_name' => $this->name,
            ]);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getRightToCorrectInformationForm()]);
    }

    /**
     * @return array
     *
     * @throws ReflectionException
     */
    public function getRightToCorrectInformationForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Right to correct information', 'Forms'),
                    'icon'  => 'icon-shield',
                ],
                'description' => $this->display((new ReflectionClass($this))->getFileName(), 'views/templates/admin/tabs/correct/desc.tpl'),
                'input'  => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable this tool'),
                        'name' => static::CORRECTED_ENABLED,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            ]
                        ],
                    ],
                    [
                        'type'         => 'textarea',
                        'label'        => $this->l('Right to rectification', 'Forms'),
                        'hint'         => $this->l('Content displayed on the Right to rectification page', 'Forms'),
                        'name'         => static::CORRECTED_TEXT,
                        'required'     => false,
                        'lang'         => true,
                        'autoload_rte' => false,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save', 'Forms'),
                    'class' => 'btn btn-default pull-right',
                    'name'  => 'tbgdprsubmit',
                ],
            ],
        ];
    }

    /**
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws ReflectionException
     *
     * @since 1.0.0
     */
    public function displayRightToBeNotifiedForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $this->fields_form = [];

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&'.http_build_query([
                'configure'   => $this->name,
                'tab_module'  => $this->tab,
                'module_name' => $this->name,
            ]);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getRightToBeNotifiedForm()]);
    }

    /**
     * @return array
     * @throws ReflectionException
     *
     * @since 1.0.0
     */
    public function getRightToBeNotifiedForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Right to be notified', 'Forms'),
                    'icon'  => 'icon-shield',
                ],
                'description' => $this->display((new ReflectionClass($this))->getFileName(), 'views/templates/admin/tabs/notification/desc.tpl'),
                'input'  => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable this tool'),
                        'name' => static::NOTIFICATION_ENABLED,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            ]
                        ],
                    ],
                    [
                        'type'         => 'textarea',
                        'label'        => $this->l('Right to be notified', 'Forms'),
                        'hint'         => $this->l('Content displayed on the Right to be notified page', 'Forms'),
                        'name'         => static::NOTIFICATION_TEXT,
                        'required'     => false,
                        'lang'         => true,
                        'autoload_rte' => false,
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save', 'Forms'),
                    'class' => 'btn btn-default pull-right',
                    'name'  => 'tbgdprsubmit',
                ],
            ],
        ];
    }

    /**
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws ReflectionException
     *
     * @since 1.0.0
     */
    public function displayRightToObjectForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $this->fields_form = [];

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&'.http_build_query([
                'configure'   => $this->name,
                'tab_module'  => $this->tab,
                'module_name' => $this->name,
            ]);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getRightToObjectForm()]);
    }

    /**
     * @return array
     * @throws ReflectionException
     *
     * @since 1.0.0
     */
    public function getRightToObjectForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Right to object', 'Forms'),
                    'icon'  => 'icon-shield',
                ],
                'description' => $this->display((new ReflectionClass($this))->getFileName(), 'views/templates/admin/tabs/object/desc.tpl'),
                'input'  => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable this tool'),
                        'name' => static::OBJECT_ENABLED,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            ]
                        ],
                    ],
                    [
                        'type'         => 'textarea',
                        'label'        => $this->l('Right to object', 'Forms'),
                        'hint'         => $this->l('Content displayed on the Right to object page', 'Forms'),
                        'name'         => static::OBJECT_TEXT,
                        'required'     => false,
                        'lang'         => true,
                        'autoload_rte' => false,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save', 'Forms'),
                    'class' => 'btn btn-default pull-right',
                    'name'  => 'tbgdprsubmit',
                ],
            ],
        ];
    }

    /**
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws ReflectionException
     *
     * @since 1.0.0
     */
    public function displayRightToRestrictProcessingForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $this->fields_form = [];

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&'.http_build_query([
                'configure'   => $this->name,
                'tab_module'  => $this->tab,
                'module_name' => $this->name,
            ]);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getRightToRestrictProcessingForm()]);
    }

    /**
     * @return array
     * @throws ReflectionException
     *
     * @since 1.0.0
     */
    public function getRightToRestrictProcessingForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Right to restrict processing', 'Forms'),
                    'icon'  => 'icon-shield',
                ],
                'description' => $this->display((new ReflectionClass($this))->getFileName(), 'views/templates/admin/tabs/restrict/desc.tpl'),
                'input'  => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable this tool'),
                        'name' => static::RESTRICT_ENABLED,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            ]
                        ],
                    ],
                    [
                        'type'         => 'textarea',
                        'label'        => $this->l('Right to restrict processing', 'Forms'),
                        'hint'         => $this->l('Content displayed on the Right to restrict processing information page', 'Forms'),
                        'name'         => static::RESTRICT_TEXT,
                        'required'     => false,
                        'lang'         => true,
                        'autoload_rte' => false,
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save', 'Forms'),
                    'class' => 'btn btn-default pull-right',
                    'name'  => 'tbgdprsubmit',
                ],
            ],
        ];
    }

    /**
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws ReflectionException
     */
    public function displayRightToBeForgottenForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $this->fields_form = [];

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&'.http_build_query([
                'configure'   => $this->name,
                'tab_module'  => $this->tab,
                'module_name' => $this->name,
            ]);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getRightToBeForgottenForm()]);
    }

    /**
     * @return array
     * @throws ReflectionException
     *
     * @since 1.0.0
     */
    public function getRightToBeForgottenForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Right to be forgotten', 'Forms'),
                    'icon'  => 'icon-shield',
                ],
                'description' =>  $this->display((new ReflectionClass($this))->getFileName(), 'views/templates/admin/tabs/removedata/desc.tpl'),
                'input'  => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable this tool'),
                        'name' => static::FORGOTTEN_ENABLED,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            ]
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Confirm account removal'),
                        'name' => static::FORGOTTEN_AUTO,
                        'hint' => $this->l('Give permission before an account will be removed?'),
                        'desc' => $this->l('Default: Yes'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            ]
                        ],
                    ],
                    [
                        'type'         => 'textarea',
                        'label'        => $this->l('Right to be forgotten', 'Forms'),
                        'hint'         => $this->l('Content displayed on the Right to be forgotten information page', 'Forms'),
                        'name'         => static::FORGOTTEN_TEXT,
                        'required'     => false,
                        'lang'         => true,
                        'autoload_rte' => false,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save', 'Forms'),
                    'class' => 'btn btn-default pull-right',
                    'name'  => 'tbgdprsubmit',
                ],
            ],
        ];
    }

    /**
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws ReflectionException
     */
    public function displayCustomerDataForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $this->fields_form = [];

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&'.http_build_query([
                'configure'   => $this->name,
                'tab_module'  => $this->tab,
                'module_name' => $this->name,
            ]);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getRightToBeForgottenForm()]);
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function getCustomerDataForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Customer Data', 'Forms'),
                    'icon'  => 'icon-user',
                ],
                'description' => $this->display((new ReflectionClass($this))->getFileName(), 'views/templates/admin/tabs/removedata/desc.tpl'),
                'input'  => [],
                'submit' => [
                    'title' => $this->l('Save', 'Forms'),
                    'class' => 'btn btn-default pull-right',
                    'name'  => 'tbgdprsubmit',
                ],
            ],
        ];
    }

    /**
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws ReflectionException
     */
    public function displayRightToDataPortabilityForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $this->fields_form = [];

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&'.http_build_query([
                'configure'   => $this->name,
                'tab_module'  => $this->tab,
                'module_name' => $this->name,
            ]);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getRightToDataPortabilityForm()]);
    }

    /**
     * @return array
     * @throws ReflectionException
     *
     * @since 1.0.0
     */
    public function getRightToDataPortabilityForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Right to data portability', 'Forms'),
                    'icon'  => 'icon-shield',
                ],
                'description' => $this->display((new ReflectionClass($this))->getFileName(), 'views/templates/admin/tabs/dataportability/desc.tpl'),
                'input'  => [
                    [
                        'type'         => 'textarea',
                        'label'        => $this->l('Right to data portability', 'Forms'),
                        'hint'         => $this->l('Content displayed on the Right to data portablitity information page', 'Forms'),
                        'name'         => static::DATAPORTABILITY_TEXT,
                        'required'     => false,
                        'lang'         => true,
                        'autoload_rte' => false,
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save', 'Forms'),
                    'class' => 'btn btn-default pull-right',
                    'name'  => 'tbgdprsubmit',
                ],
            ],
        ];
    }
}
