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

require_once __DIR__.'/vendor/autoload.php';

/**
 * Class TbGdpr
 *
 * // Translations, please leave them in this file:
 * $this->l('Please verify your email address');
 */
class TbGdpr extends Module
{
    use \TbGdprModule\Installation;
    use \TbGdprModule\Forms;
    use \TbGdprModule\AdminAjax;
    use \TbGdprModule\ReactTranslations;

    const CONSENT_FUNCTIONAL = 'functional';
    const CONSENT_ANALYTICS = 'analytics';
    const CONSENT_TRACKING = 'tracking';
    const CONSENT_TESTING = 'testing';
    const CONSENT_MARKETING = 'marketing';
    const CONSENT_RETARGETING = 'retargeting';

    const DISPLAY_POSITION = 'TBGDPR_POSITION';
    const DISPLAY_LAYOUT = 'TBGDPR_LAYOUT';
    const DISPLAY_PALETTE_BANNER = 'TBGDPR_BANNER';
    const DISPLAY_PALETTE_BANNER_TEXT = 'TBGDPR_BANNER_TEXT';
    const DISPLAY_PALETTE_BUTTON = 'TBGDPR_BUTTON';
    const DISPLAY_PALETTE_BUTTON_TEXT = 'TBGDPR_BUTTON_TEXT';
    const DISPLAY_LEARN_MORE_LINK = 'TBGDPR_LEARN_MORE';
    const DISPLAY_MESSAGE_TEXT = 'TBGDPR_MESSAGE_TEXT';
    const DISPLAY_DISMISS_TEXT = 'TBGDPR_DISMISS_TEXT';
    const DISPLAY_POLICY_TEXT = 'TBGDPR_POLICY_TEXT';

    const ANONYMOUS_ENABLED = 'TBGDPR_ANONYMOUS_ENABLED';
    const ANONYMOUS_TEXT = 'TBGDPR_ANONYMOUS_TEXT';

    const INFORMED_ENABLED = 'TBGDPR_INFORMED_ENABLED';
    const INFORMED_TEXT = 'TBGDPR_INFORMED_TEXT';

    const CORRECTED_ENABLED = 'TBGDPR_CORRECTED_ENABLED';
    const CORRECTED_TEXT = 'TBGDPR_CORRECTED_TEXT';

    const OBJECT_ENABLED = 'TBGDPR_OBJECT_ENABLED';
    const OBJECT_TEXT = 'TBGDPR_OBJECT_TEXT';
    const OBJECT_EMAIL_EXPIRE = 'TBGDPR_OE_EXPIRE';
    const OBJECT_EMAIL_EXPIRE_DEFAULT = 15;

    const NOTIFICATION_ENABLED = 'TBGDPR_NOTIFICATION_ENABLED';
    const NOTIFICATION_TEXT = 'TBGDPR_NOTIFICATION_TEXT';

    const RESTRICT_ENABLED = 'TBGDPR_RESTRICT_ENABLED';
    const RESTRICT_TEXT = 'TBGDPR_RESTRICT_TEXT';

    const DATAPORTABILITY_ENABLED = 'TBGDPR_DATAPORTABILITY_ENABLED';
    const DATAPORTABILITY_TEXT = 'TBGDPR_RESTRICT_TEXT';
    
    const FORGOTTEN_ENABLED = 'TBGDPR_FORGOTTEN_ENABLED';
    const FORGOTTEN_TEXT = 'TBGDPR_FORGOTTEN_TEXT';
    const FORGOTTEN_NEEDS_CONFIRM = 'TBGDPR_FORGOTTEN_AUTO';
    const FORGOTTEN_EMAIL_EXPIRE = 'TBGDPR_FORGOTTEN_EXPIRE';
    const FORGOTTEN_EMAIL_EXPIRE_DEFAULT = 15;

    const TAB_MAIN = 1;
    const TAB_COOKIE = 2;
    const TAB_SHOPS = 3;
    const TAB_PRODUCTS = 4;
    const TAB_CARTS = 5;
    const TAB_ORDERS = 6;

    const DEFAULT_PAGINATION_LIMIT = 50;

    /** @var array $defaultConsents */
    public static $defaultConsents = [
        self::CONSENT_FUNCTIONAL,
        self::CONSENT_ANALYTICS,
        self::CONSENT_TRACKING,
        self::CONSENT_TESTING,
        self::CONSENT_MARKETING,
        self::CONSENT_RETARGETING,
    ];
    /** @var string $baseUrl */
    public $baseUrl;

    /**
     * TbGdpr constructor.
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->name = 'tbgdpr';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'thirty bees';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('GDPR compliance');
        $this->description = $this->l('Comply with the GDPR Guidelines');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

        $this->controllers = [
            'ajax',
            'anonymous',
            'dataportability',
            'informed',
            'notification',
            'object',
            'overview',
            'correct',
            'removedata',
            'restrict',
        ];

        if (isset(Context::getContext()->employee->id) && Context::getContext()->employee->id) {
            $this->baseUrl = $this->context->link->getAdminLink('AdminModules', true).'&'.http_build_query([
                    'configure'   => $this->name,
                    'tab_module'  => $this->tab,
                    'module_name' => $this->name,
                ]);
        }
    }

    /**
     * @return void
     */
    public function hookDisplayHeader()
    {
        $this->context->controller->addJS($this->_path.'views/js/consent-modal.js');
        $this->context->controller->addCSS($this->_path.'views/css/consent-modal.css');
        $this->context->controller->addJS($this->_path.'views/js/cookieconsent.min.js');
        $this->context->controller->addCSS($this->_path.'views/css/cookieconsent.min.css');
    }

    /**
     * @return string
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayFooter()
    {
        $this->context->smarty->assign([
            'gdprAjaxUrl'                  => $this->context->link->getModuleLink($this->name, 'ajax', [], (bool) Tools::usingSecureMode()),
            'gdprConsentModalTranslations' => json_encode($this->getModalTranslations()),
            'gdprConsentCapabilities'      => json_encode($this->getConsentCapabilities()),
            'gdprWidgetSettings'           => json_encode($this->generateWidgetSettings()),
            'gdprConsents'                 => static::$defaultConsents,
        ]);

        return $this->display(__FILE__, 'consentpopup.tpl');
    }

    /**
     * @return string
     *
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws ReflectionException
     */
    public function getContent()
    {
        if (Tools::getValue('dev') && class_exists('\\Faker\\Factory')) {
            $faker = \Faker\Factory::create();
            $data = [];
            for ($i = 0; $i < 2000; $i++) {
                $data[] = [
                    'id_customer'       => $faker->randomNumber(),
                    'id_guest'          => $faker->randomNumber(),
                    'email'             => ['type' => 'sql', 'value' => '0x'.hash('sha512', $faker->email)],
                    'status'            => $faker->numberBetween(1, 3),
                    'executed'          => (string) (int) $faker->boolean,
                    'comment'           => $faker->text,
                    'id_shop'           => $this->context->shop->id,
                    'request_type'      => TbGdprRequest::REQUEST_TYPE_REMOVE_DATA,
                    'date_add'          => $faker->date('Y-m-d H:i:s'),
                    'date_upd'          => $faker->date('Y-m-d H:i:s'),
                ];
            }
            Db::getInstance()->insert(
                TbGdprRequest::$definition['table'],
                $data
            );
        }

        $this->installEmailTemplates();
        $this->postProcess();
        $this->context->smarty->assign([
            'configLink' => $this->context->link->getAdminLink('AdminTbGdprConfigure'),
            'availableShops' => Shop::isFeatureActive()
                ? Shop::getShops(true, null, true)
                : [$this->context->shop->id => $this->context->shop->id],
            'baseUrl'        => $this->baseUrl,
            'moduleName'     => $this->name,
            'reactTranslations' => $this->getReactTranslations(),
        ]);
        foreach ([
                     'sweetalert.min.js',
                     'popover.js',
                     'back.js',
                     'dist/export-__BUILD_HASH__.bundle.min.js',
                     'dist/requests-__BUILD_HASH__.bundle.min.js',
                     'dist/translations-__BUILD_HASH__.bundle.min.js',
                 ] as $script) {
            $this->context->controller->addJS("{$this->_path}views/js/{$script}");
        }
        foreach ([
                     'back.css',
                     'popover.css',
                 ] as $style) {
            $this->context->controller->addCSS("{$this->_path}views/css/{$style}", 'screen');
        }
        $this->loadTabs();

        return $this->display(__FILE__, 'views/templates/admin/main.tpl');
    }

    /**
     * @return void
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 1.0.0
     */
    protected function loadTabs()
    {
        $contents = [
            'data_security' => [
                [
                    'name'  => $this->l('Data Security and Management'),
                    'icon'  => dot($this->getDataSecurityForm())->get('form.legend.icon'),
                    'value' => $this->displayDataSecurityForm(),
                    'badge' => '',
                ],
                [
                    'name'  => $this->l('All Customer Requests'),
                    'icon'  => 'icon-user',
                    'value' => $this->displayAllCustomerRequestsForm(),
                    'badge' => '',
                ],
            ],
            'anonymous' => [
                [
                    'name'  => dot($this->getRightToBeAnonymousForm())->get('form.legend.title'),
                    'icon'  => dot($this->getRightToBeAnonymousForm())->get('form.legend.icon'),
                    'value' => $this->displayRightToBeAnonymousForm(),
                    'badge' => '',
                ],
            ],
            'informed'  => [
                [
                    'name'  => dot($this->getRightToBeInformedForm())->get('form.legend.title'),
                    'icon'  => dot($this->getRightToBeInformedForm())->get('form.legend.icon'),
                    'value' => $this->displayRightToBeInformedForm(),
                    'badge' => '',
                ],
            ],
            'correct'   => [
                [
                    'name'  => dot($this->getRightToCorrectInformationForm())->get('form.legend.title'),
                    'icon'  => dot($this->getRightToCorrectInformationForm())->get('form.legend.icon'),
                    'value' => $this->displayRightToCorrectInformationForm(),
                    'badge' => '',
                ],
            ],
            'dataportability'    => [
                [
                    'name'  => dot($this->getRightToDataPortabilityForm())->get('form.legend.title'),
                    'icon'  => dot($this->getRightToDataPortabilityForm())->get('form.legend.icon'),
                    'value' => $this->displayRightToDataPortabilityForm(),
                    'badge' => '',
                ],
            ],
            'notify'    => [
                [
                    'name'  => dot($this->getRightToBeNotifiedForm())->get('form.legend.title'),
                    'icon'  => dot($this->getRightToBeNotifiedForm())->get('form.legend.icon'),
                    'value' => $this->displayRightToBeNotifiedForm(),
                    'badge' => '',
                ],
            ],
            'object'    => [
                [
                    'name'  => dot($this->getRightToObjectForm())->get('form.legend.title'),
                    'icon'  => dot($this->getRightToObjectForm())->get('form.legend.icon'),
                    'value' => $this->displayRightToObjectForm(),
                    'badge' => '',
                ],
            ],
            'removedata'    => [
                [
                    'name'  => dot($this->getRightToBeForgottenForm())->get('form.legend.title'),
                    'icon'  => dot($this->getRightToBeForgottenForm())->get('form.legend.icon'),
                    'value' => $this->displayRightToBeForgottenForm(),
                    'badge' => '',
                ],
                [
                    'name'  => dot($this->getPendingRemovalRequestsForm())->get('form.legend.title'),
                    'icon'  => dot($this->getPendingRemovalRequestsForm())->get('form.legend.icon'),
                    'value' => $this->displayPendingRemovalRequestsForm(),
                    'badge' => '',
                ],
            ],
            'restrict'  => [
                'main'          => [
                    'name'  => dot($this->getRightToRestrictProcessingForm())->get('form.legend.title'),
                    'icon'  => dot($this->getRightToRestrictProcessingForm())->get('form.legend.icon'),
                    'value' => $this->displayRightToRestrictProcessingForm(),
                    'badge' => '',
                ],
                'cookie_policy' => [
                    'name'  => $this->l('Privacy Policy'),
                    'icon'  => 'icon-list-ul',
                    'value' => $this->displayConsentModalForm(),
                    'badge' => '',
                ],
            ],
            'info'      => [
                [
                    'name'  => $this->l('About'),
                    'icon'  => 'icon-question',
                    'value' => "<div class='panel'>{$this->l('About')}</div>",
                    'badge' => (defined('_TB_VERSION_') ? 'thirty bees '._TB_VERSION_ : 'PrestaShop'._PS_VERSION_).' | v'.$this->version,
                ],
            ],
        ];

        $tabContents = [
            'contents' => $contents,
        ];

        $this->context->smarty->assign('tab_contents', $tabContents);
        $this->context->smarty->assign('ps_version', _PS_VERSION_);
        $this->context->smarty->assign('new_base_dir', $this->_path);
        $this->context->controller->addCss($this->_path.'/views/css/configtabs.css');
        $this->context->controller->addJs($this->_path.'/views/js/configtabs.js');
    }

    /**
     * @return array
     *
     * @since 1.0.0
     * @throws PrestaShopException
     */
    public function getConfigFieldsValues()
    {
        return [
            static::DISPLAY_POSITION            => (int) Configuration::get(static::DISPLAY_POSITION),
            static::DISPLAY_LAYOUT              => (int) Configuration::get(static::DISPLAY_LAYOUT),
            static::DISPLAY_PALETTE_BANNER      => Configuration::get(static::DISPLAY_PALETTE_BANNER),
            static::DISPLAY_PALETTE_BANNER_TEXT => Configuration::get(static::DISPLAY_PALETTE_BANNER_TEXT),
            static::DISPLAY_PALETTE_BUTTON      => Configuration::get(static::DISPLAY_PALETTE_BUTTON),
            static::DISPLAY_PALETTE_BUTTON_TEXT => Configuration::get(static::DISPLAY_PALETTE_BUTTON_TEXT),
            static::DISPLAY_LEARN_MORE_LINK     => Configuration::get(static::DISPLAY_LEARN_MORE_LINK),
            static::ANONYMOUS_ENABLED           => (bool) Configuration::get(static::ANONYMOUS_ENABLED),
            static::ANONYMOUS_TEXT              => Configuration::getInt(static::ANONYMOUS_TEXT),
            static::INFORMED_ENABLED            => (bool) Configuration::get(static::INFORMED_ENABLED),
            static::INFORMED_TEXT               => Configuration::getInt(static::INFORMED_TEXT),
            static::CORRECTED_ENABLED           => (bool) Configuration::get(static::CORRECTED_ENABLED),
            static::CORRECTED_TEXT              => Configuration::getInt(static::CORRECTED_TEXT),
            static::NOTIFICATION_ENABLED        => (bool) Configuration::get(static::NOTIFICATION_ENABLED),
            static::NOTIFICATION_TEXT           => Configuration::getInt(static::NOTIFICATION_TEXT),
            static::OBJECT_ENABLED              => (bool) Configuration::get(static::OBJECT_ENABLED),
            static::OBJECT_TEXT                 => Configuration::getInt(static::OBJECT_TEXT),
            static::OBJECT_EMAIL_EXPIRE         => (int) (Configuration::getInt(static::OBJECT_EMAIL_EXPIRE) ?: static::OBJECT_EMAIL_EXPIRE_DEFAULT),
            static::RESTRICT_ENABLED            => (bool) Configuration::get(static::RESTRICT_ENABLED),
            static::RESTRICT_TEXT               => Configuration::getInt(static::RESTRICT_TEXT),
            static::DATAPORTABILITY_ENABLED     => (bool) Configuration::get(static::DATAPORTABILITY_ENABLED),
            static::DATAPORTABILITY_TEXT        => Configuration::getInt(static::DATAPORTABILITY_TEXT),
            static::FORGOTTEN_ENABLED           => (bool) Configuration::get(static::FORGOTTEN_ENABLED),
            static::FORGOTTEN_NEEDS_CONFIRM     => (bool) Configuration::get(static::FORGOTTEN_NEEDS_CONFIRM),
            static::FORGOTTEN_TEXT              => Configuration::getInt(static::FORGOTTEN_TEXT),
        ];
    }

    /**
     * @return array
     *
     * @since 1.0.0
     * @throws PrestaShopException
     */
    public function generateWidgetSettings()
    {
        $widgetSettings = []
            + $this->getWidgetPosition()
            + $this->getWidgetLayout();

        $widgetSettings['palette'] = $this->getWidgetPalette();
        $widgetSettings['geoip'] = false;
        $widgetSettings['content'] = $this->getWidgetContent();
        $widgetSettings['type'] = 'opt-in';
        $widgetSettings['tb'] = defined('_TB_VERSION_');

        $consentLevels = static::getConsentLevels();
        if (is_array($consentLevels)) {
            $consentLevels = [];
            foreach (static::getConsentLevels() as $consentLevel => $allowed) {
                if ($allowed) {
                    $consentLevels[] = $consentLevel;
                }
            }
        }
        $widgetSettings['consentLevels'] = $consentLevels;

        if ((int) Configuration::get(static::DISPLAY_LAYOUT) !== 1 && (int) Configuration::get(static::DISPLAY_LAYOUT) !== 4) {
            switch ((int) Configuration::get(static::DISPLAY_LAYOUT)) {
                case 2:
                    $theme = 'edgeless';
                    break;
                default:
                    $theme = 'classic';
                    break;
            }

            $widgetSettings['theme'] = $theme;
        }

        return $widgetSettings;
    }

    /**
     * Process settings page
     *
     * @since 1.0.0
     * @throws PrestaShopException
     * @throws ReflectionException
     */
    protected function postProcess()
    {
        if (Tools::isSubmit('submitSettings')) {
            $languageIds = Language::getLanguages(false, null, true);
            $options = array_merge(
                $this->getRightToBeAnonymousForm()['form']['input'],
                $this->getRightToBeInformedForm()['form']['input'],
                $this->getRightToCorrectInformationForm()['form']['input'],
                $this->getRightToObjectForm()['form']['input'],
                $this->getRightToBeNotifiedForm()['form']['input'],
                $this->getRightToBeForgottenForm()['form']['input'],
                $this->getRightToDataPortabilityForm()['form']['input']
            );
            foreach ($options as $option) {
                $key = $option['name'];
                if (!is_string($key) || !$key || (empty($option['lang']) && !Tools::isSubmit($key))) {
                    continue;
                }

                if (isset($option['lang']) && $option['lang']) {
                    $value = [];
                    foreach ($languageIds as $idLang) {
                        if (!Tools::isSubmit($option['name'].'_'.$idLang)) {
                            continue 2;
                        }
                        $value[$idLang] = Tools::getValue($option['name'].'_'.$idLang);
                    }
                } else {
                    $value = Tools::getValue($option['name']);
                }
                Configuration::updateValue($key, $value, $option['type'] === 'textarea');
                $values[$key] = $value;
            }
            $this->context->controller->confirmations[] = $this->l('Settings saved successfully');
        }
    }

    /**
     * @return array
     *
     * @since 1.0.0
     * @throws PrestaShopException
     */
    protected function getWidgetPosition()
    {
        switch ((int) Configuration::get(static::DISPLAY_POSITION)) {
            case 2:
                return [
                    'position' => 'top',
                    'static'   => false,
                ];
            case 3:
                return [
                    'position' => 'top',
                    'static'   => true,
                ];
            case 4:
                return [
                    'position' => 'bottom-left',
                    'static'   => false,
                ];
            case 5:
                return [
                    'position' => 'bottom-right',
                    'static'   => false,
                ];
            default:
                return [
                    'position' => 'bottom',
                    'static'   => false,
                ];

        }
    }

    /**
     * @return array
     *
     * @since 1.0.0
     * @throws PrestaShopException
     */
    protected function getWidgetLayout()
    {
        $layout = (int) Configuration::get(static::DISPLAY_LAYOUT);

        if ($layout === 1) {
            return [];
        }

        return [
            'theme' => $layout,
        ];
    }

    /**
     * @return array
     *
     * @since 1.0.0
     * @throws PrestaShopException
     */
    protected function getWidgetPalette()
    {
        $palette = [];

        $palette['popup'] = [
            'background' => Configuration::get(static::DISPLAY_PALETTE_BANNER),
            'text'       => Configuration::get(static::DISPLAY_PALETTE_BANNER_TEXT),
        ];

        $palette['button'] = [
            'background' => Configuration::get(static::DISPLAY_PALETTE_BUTTON),
            'text'       => Configuration::get(static::DISPLAY_PALETTE_BUTTON_TEXT),
        ];

        if ((int) Configuration::get(static::DISPLAY_LAYOUT) === 4) {
            $palette['button']['background'] = Configuration::get(static::DISPLAY_PALETTE_BUTTON_TEXT);
            $palette['button']['text'] = Configuration::get(static::DISPLAY_PALETTE_BUTTON);
            $palette['button']['border'] = Configuration::get(static::DISPLAY_PALETTE_BUTTON);
        }

        return $palette;
    }

    /**
     * @param null|int $idLang
     *
     * @return array
     *
     * @since 1.0.0
     * @throws PrestaShopException
     */
    protected function getWidgetContent($idLang = null)
    {
        if (!$idLang) {
            $idLang = (int) Context::getContext()->language->id;
        }

        $content = [
            'header'  => $this->l('Cookies used on the website'),
            'message' => $this->l('This website uses cookies to ensure you get the best experience on our website.'),
            'allow'   => $this->l('Allow all cookies'),
            'deny'    => $this->l('Decline'),
            'dismiss' => $this->l('Privacy settings'),
            'link'    => $this->l('Learn more'),
        ];

        if (Configuration::get(static::DISPLAY_LEARN_MORE_LINK, $idLang)) {
            $content['href'] = Configuration::get(static::DISPLAY_LEARN_MORE_LINK, $idLang);
        }

        return $content;
    }

    /**
     * @return array
     *
     * @since 1.0.0
     */
    protected function getModalTranslations()
    {
        return [
            'yourCookieSettings'                => $this->l('Your Privacy Settings'),
            'paragraph1'                        => $this->l('Cookies are very small text files that are stored on your computer when you visit some websites.'),
            'paragraph2'                        => $this->l('We use cookies to make our website easier for you to use. You can remove any cookies already stored on your computer, but these may prevent you from using parts of our website.'),
            'save'                              => $this->l('Save'),
            'cookiePolicy'                      => $this->l('Cookie Policy'),
            'functionalPerformance'             => $this->l('Absolutely necessary'),
            'tracking'                          => $this->l('Tracking'),
            'testing'                           => $this->l('Testing'),
            'testingAndFeedback'                => $this->l('Testing & User feedback'),
            'conversionTracking'                => $this->l('Conversion tracking'),
            'analytics'                         => $this->l('Analytics'),
            'marketing'                         => $this->l('Marketing'),
            'marketingAutomation'               => $this->l('Marketing automation'),
            'retargeting'                       => $this->l('Retargeting'),
            'thisWebsiteCan'                    => $this->l('This website can'),
            'thisWebsiteCannot'                 => $this->l('This website cannot'),
            'selectCookiesYouWantToAllow'       => $this->l('Select the kinds of cookies you want to allow:'),
            'thisSiteDoesNotWorkWithoutCookies' => $this->l('This site does not work without essential cookies'),
        ];
    }

    /**
     * @return array
     *
     * @since 1.0.0
     */
    protected function getConsentCapabilities()
    {
        return [
            static::CONSENT_FUNCTIONAL  => [
                'Remember what is in your shopping basket',
                'Remember how far you are through an order',
                "Make sure you're secure when logged in to the website",
                'Make sure the website looks consistent',
            ],
            static::CONSENT_TRACKING    => [
                'Monitor how you travel through the website',
            ],
            static::CONSENT_RETARGETING => [
                'Allow you to share pages with social networks like Facebook and Twitter',
                'Send information to other websites so that advertising is more relevant to you',
            ],
            static::CONSENT_MARKETING   => [],
            static::CONSENT_TESTING     => [],
            static::CONSENT_ANALYTICS   => [],
        ];
    }

    /**
     * @return string
     * @throws PrestaShopException
     * @throws SmartyException
     *
     * @since 1.0.0
     */
    public function hookDisplayCustomerAccount()
    {
        if (static::isGdprEnabled()) {
            return $this->display(__FILE__, 'customeraccount.tpl');
        }
        
        return '';
    }

    /**
     * @return void
     *
     * @since 1.0.0
     */
    public function hookHeader()
    {
        $this->context->controller->addCSS(($this->_path).'views/css/front.css');
    }

    /**
     * Grab the accepted consent levels from the cookie
     *
     * @param bool $forceArray
     *
     * @return array|bool|mixed
     *
     * @since 1.0.0
     */
    public static function getConsentLevels($forceArray = true)
    {
        if (!$context = Context::getContext()) {
            return false;
        }
        if (!$cookie = $context->cookie) {
            return false;
        }


        if (is_string($cookie->consentLevels)) {
            $consentLevels = json_decode($cookie->consentLevels, true);
            $consents = [];
            foreach (static::$defaultConsents as $defaultConsent) {
                $consents[$defaultConsent] = in_array($defaultConsent, $consentLevels);
            }

            return $consents;
        } elseif (!$forceArray) {
            return null;
        }

        return [];
    }

    /**
     * Do we have consent for the cookies that are absolutely necessary?
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public static function hasFunctionalConsent()
    {
        $levels = static::getConsentLevels();

        return is_array($levels) && !empty($levels[static::CONSENT_FUNCTIONAL]);
    }

    /**
     * Do we have tracking consent?
     * 
     * @return bool
     *
     * @since 1.0.0
     */
    public static function hasTrackingConsent()
    {
        $levels = static::getConsentLevels();

        return is_array($levels) && !empty($levels[static::CONSENT_TRACKING]);
    }

    /**
     * Do we have targeting consent?
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public static function hasTargetingConsent()
    {
        $levels = static::getConsentLevels();

        return is_array($levels) && !empty($levels[static::CONSENT_RETARGETING]);
    }

    /**
     * Run Cookie and CSP checks for the FrontController::__destruct override (where all the magic happens)
     *
     * @since 1.0.0
     */
    public static function runChecks()
    {
        $consentLevels = static::getConsentLevels();
        if (is_array($consentLevels) && empty($consentLevels[static::CONSENT_FUNCTIONAL])) {
            header_remove('Set-Cookie');
            header('Content-Security-Policy: upgrade-insecure-requests; default-src \'self\'; script-src \'unsafe-inline\' \'self\' \'unsafe-eval\'; style-src \'self\' \'unsafe-inline\'; img-src \'self\' data:; child-src \'self\'');
                foreach ($_COOKIE as $key => $value) {
                    if (substr($key, 0, 11) === 'thirtybees-' || substr($key, 0, 11) === 'PrestaShop-') {
                    try {
                        static::removeCookie($key);
                    } catch (Exception $e) {
                    }
                }
            }
        }
    }

    /**
     * Remove cookie
     *
     * @param string $key
     *
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    protected static function removeCookie($key)
    {
        $path = trim((Context::getContext()->shop->physical_uri), '/\\').'/';
        if ($path{0} != '/') {
            $path = '/'.$path;
        }
        $path = rawurlencode($path);
        $path = str_replace('%2F', '/', $path);
        $path = str_replace('%7E', '~', $path);
        if (DIRECTORY_SEPARATOR === '\\') {
            $path = Tools::strtolower($path);
        }

        $domain = '.'.Tools::getHttpHost(false, false);
        unset($_COOKIE[$key]);
        setcookie($key, null, time() - 3600, $path, $domain, false, true);
        setcookie($key, null, time() - 3600, $path, $domain, true, true);
    }

    /**
     * Is GDPR enabled?
     *
     * @return bool
     *
     * @throws PrestaShopException
     *
     * @since 1.0.0
     */
    protected static function isGdprEnabled()
    {
        return (bool) array_sum(array_values(Configuration::getMultiple([
            static::ANONYMOUS_ENABLED,
            static::CORRECTED_ENABLED,
            static::DATAPORTABILITY_ENABLED,
            static::NOTIFICATION_ENABLED,
            static::OBJECT_ENABLED,
            static::RESTRICT_ENABLED,
            static::INFORMED_ENABLED,
            static::FORGOTTEN_ENABLED,
        ])));
    }
}
