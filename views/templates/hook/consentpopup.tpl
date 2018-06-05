{*
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
*}
<aside class="tb-consent-modal-overlay tb-consent-closed" id="tb-consent-modal-overlay"></aside>
<aside id="tb-consent-main-modal" class="tb-consent-reset-this tb-consent-closed">
  <div id="tb-consent-modal" class="tb-consent-modal tb-consent-closed">
    <button class="tb-consent-close-button" id="tb-consent-close-button" data-trans="save"></button>
    <div class="tb-consent-modal-guts">
      <span class="tb-consent-modal-title" data-trans="yourCookieSettings"></span>
      <span data-trans="paragraph1"></span>
      <span data-trans="paragraph2"></span>
      <span class="tb-consent-modal-subtitle" data-trans="selectCookiesYouWantToAllow"></span>
      <ul>
        <li>
          <div {if !in_array(TbGdpr::CONSENT_FUNCTIONAL, $gdprConsents)}style="display: none"{/if}>
            <input id="tb-consent-level-functional"
                   class="tb-consent-checkbox"
                   name="tb-consent-level"
                   type="checkbox"
                   data-consent-type="functional"
                   data-no-uniform="true"
            >
            <label for="tb-consent-level-functional"
                   class="tb-consent-checkbox-label"
                   data-trans="functionalPerformance"
            />
          </div>
          <div {if !in_array(TbGdpr::CONSENT_ANALYTICS, $gdprConsents)}style="display: none"{/if}>
            <input id="tb-consent-level-analytics"
                   class="tb-consent-checkbox"
                   name="tb-consent-level"
                   type="checkbox"
                   data-consent-type="analytics"
                   data-no-uniform="true"
            >
            <label for="tb-consent-level-analytics"
                   class="tb-consent-checkbox-label"
                   data-trans="analytics"
            />
          </div>
          <div {if !in_array(TbGdpr::CONSENT_TESTING, $gdprConsents)}style="display: none"{/if}>
            <input id="tb-consent-level-testing"
                   class="tb-consent-checkbox"
                   name="tb-consent-level"
                   type="checkbox"
                   data-consent-type="testing"
                   data-no-uniform="true"
            >
            <label for="tb-consent-level-testing"
                   class="tb-consent-checkbox-label"
                   data-trans="testingAndFeedback"
            />
          </div>
          <div {if !in_array(TbGdpr::CONSENT_MARKETING, $gdprConsents)}style="display: none"{/if}>
            <input id="tb-consent-level-marketing"
                   class="tb-consent-checkbox"
                   name="tb-consent-level"
                   type="checkbox"
                   data-consent-type="marketing"
                   data-no-uniform="true"
            >
            <label for="tb-consent-level-marketing"
                   class="tb-consent-checkbox-label"
                   data-trans="marketingAutomation"
            />
          </div>
          <div {if !in_array(TbGdpr::CONSENT_TRACKING, $gdprConsents)}style="display: none"{/if}>
            <input id="tb-consent-level-tracking"
                   class="tb-consent-checkbox"
                   name="tb-consent-level"
                   type="checkbox"
                   data-consent-type="tracing"
                   data-no-uniform="true"
            >
            <label for="tb-consent-level-tracking"
                   class="tb-consent-checkbox-label"
                   data-trans="conversionTracking"
            />
          </div>
          <div {if !in_array(TbGdpr::CONSENT_RETARGETING, $gdprConsents)}style="display: none"{/if}>
            <input id="tb-consent-level-retargeting"
                   class="tb-consent-checkbox"
                   name="cc-consent-level"
                   type="checkbox"
                   data-consent-type="retargeting"
                   data-no-uniform="true"
            >
            <label for="tb-consent-level-retargeting"
                   class="tb-consent-checkbox-label"
                   data-trans="retargeting"
            />
          </div>
        </li>
      </ul>
      <div id="tb-consent-alert-warning">
        <svg xmlns="http://www.w3.org/2000/svg"
             viewBox="-2 -256 1810 1810"
             id="tb-consent-icon-warning-svg"
             version="1.1"
             width="100%"
             height="100%"
        >
          <g
                  transform="matrix(1,0,0,-1,7.5932254,1333.7966)"
                  id="tb-consent-icon-warning-g"
          >
            <path
                    d="m 1024,161 v 190 q 0,14 -9.5,23.5 Q 1005,384 992,384 H 800 q -13,0 -22.5,-9.5 Q 768,365 768,351 V 161 q 0,-14 9.5,-23.5 Q 787,128 800,128 h 192 q 13,0 22.5,9.5 9.5,9.5 9.5,23.5 z m -2,374 18,459 q 0,12 -10,19 -13,11 -24,11 H 786 q -11,0 -24,-11 -10,-7 -10,-21 l 17,-457 q 0,-10 10,-16.5 10,-6.5 24,-6.5 h 185 q 14,0 23.5,6.5 9.5,6.5 10.5,16.5 z m -14,934 768,-1408 q 35,-63 -2,-126 -17,-29 -46.5,-46 -29.5,-17 -63.5,-17 H 128 q -34,0 -63.5,17 Q 35,-94 18,-65 -19,-2 16,61 l 768,1408 q 17,31 47,49 30,18 65,18 35,0 65,-18 30,-18 47,-49 z"
                    id="tb-consent-icon-warning-path"

            />
          </g>
        </svg><span id="tb-consent-warning-text" data-trans="thisSiteDoesNotWorkWithoutCookies"></span>
      </div>
      <div id="tb-consent-website-row" class="tb-consent-website-row">
        <div class="tb-consent-website-capabilities">
          <div class="tb-consent-website-title">
            <span data-trans="thisWebsiteCan"></span>
          </div>
          <ul id="tb-consent-website-can" class="tb-consent-website-list"></ul>
        </div>
        <div class="tb-consent-website-capabilities">
          <div class="tb-consent-website-title">
            <span data-trans="thisWebsiteCannot"></span>
          </div>
          <ul id="tb-consent-website-cannot" class="tb-consent-website-list"></ul>
        </div>
      </div>
    </div>
  </div>
</aside>
<script type="text/javascript">
  (function () {
    window.TbGdprModule = window.TbGdprModule || { };
    window.TbGdprModule.urls = window.TbGdprModule.urls || { };
    window.TbGdprModule.urls.ajax = '{$gdprAjaxUrl|escape:'javascript':'UTF-8'}';
    window.TbGdprModule.consentCapabilities = {$gdprConsentCapabilities};
    window.TbGdprModule.translations = {$gdprConsentModalTranslations};
    window.TbGdprModule.widgetSettings = {$gdprWidgetSettings};

    window.addEventListener('load', function() {
      // If cookie consent is undefined by then, it might have been blocked by e.g. Ghostery
      if (typeof window.cookieconsent !== 'undefined') {
        window.cookieconsent.initialise({$gdprWidgetSettings});
      }
    });

    function restoreCheckboxes() {
      var seconds = 10.0;
      if (typeof $ === 'undefined'
        || typeof $.uniform !== 'object'
        || ![].slice.call(document.querySelectorAll('[data-no-uniform]')).length
      ) {
        if ((seconds -= 0.100) > 0) {
          setTimeout(restoreCheckboxes, 100);
          return;
        }
      }
      seconds = 10;
      var interval = setInterval(function () {
        if (seconds-- > 0) {
          $.uniform.restore('[data-no-uniform]');
        } else {
          clearInterval(interval);
        }
      }, 1000);
    }
    restoreCheckboxes();
  }());
</script>
