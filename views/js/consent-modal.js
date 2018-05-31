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
(function () {
  function init() {
    var self = this;
    var modal = document.getElementById('tb-consent-main-modal');
    if (!modal || !window.TbGdprModule || !window.TbGdprModule.consentCapabilities) {
      setTimeout(init, 100);
      return;
    }
    var consentCapabilities = window.TbGdprModule.consentCapabilities;
    var translations = window.TbGdprModule.translations;
    var config = {
      consentLevel: null,
      callbacks: {
        onConsentChange: [],
      },
    };
    config.pallette = window.TbGdprModule.widgetSettings.palette;
    var selectors = {
      popup: {
        background: {
          'background-color': [
            '.tb-consent-modal *',
            '.tb-consent-modal',
            '.tb-consent-website-modal-title',
          ],
          color: [
            '.tb-consent-website-title span',
          ],
        },
        text: {
          'background-color': [
            '.tb-consent-website-title span',
          ],
          color: [
            '.tb-consent-modal *',
            '.tb-consent-modal',
          ],
          'outline-color': [
            '.tb-consent-checkbox + .tb-consent-checkbox-label:before',
          ],
        },
      },
      button: {
        background: {
          'background-color': [
            '.tb-consent-close-button',
            '.tb-consent-checkbox:checked + .tb-consent-checkbox-label:before',
            '#tb-consent-alert-warning',
            '#tb-consent-warning-text',
            '#tb-consent-icon-warning-svg',
          ],
        },
        text: {
          color: ['.tb-consent-close-button',
            '#tb-consent-alert-warning',
            '#tb-consent-warning-text',
            '#tb-consent-icon-warning-svg',
            '#tb-consent-icon-warning-path',
          ],
        },
      },
    };
    var doNotTrack = navigator.doNotTrack || window.doNotTrack || navigator.msDoNotTrack;
    var consentLevel = [];
    if (window.TbGdprModule.widgetSettings.consentLevels != null) {
      window.TbGdprModule.widgetSettings.consentLevels.forEach(function (key) {
        consentLevel.push(key);
      });
    } else {
      doNotTrack ? ['functional'] : Object.keys(consentCapabilities);
    }
    var originalLevel = [];
    consentLevel.forEach(function (item) {
      originalLevel.push(item);
    });

    var mainModal = document.getElementById('tb-consent-main-modal');
    var modal = document.getElementById('tb-consent-modal');
    var modalOverlay = document.getElementById('tb-consent-modal-overlay');

    var inputs = [];
    Object.keys(consentCapabilities).forEach(function (consentType) {
      inputs[consentType] = document.getElementById('tb-consent-level-' + consentType);
    });
    var warningBox = document.getElementById('tb-consent-alert-warning');
    var capabilitiesBox = document.getElementById('tb-consent-website-row');

    function updateConsentSettings() {
      var request = new XMLHttpRequest();
      request.open('POST', window.TbGdprModule.urls.ajax, true);

      request.onreadystatechange = function () {
        if (this.readyState === 4) {
          if (this.status >= 200 && this.status < 400) {
            // Success!
            var response = JSON.parse(this.responseText);
            if (response && response.success) {
              console.log(JSON.stringify(consentLevel.sort()));
              console.log(JSON.stringify(originalLevel.sort()));
              if (JSON.stringify(consentLevel.sort()) !== JSON.stringify(originalLevel.sort())) {
                location.reload();
              }
            }
          } else {
            // Error :(
          }
        }
      };

      request.setRequestHeader('Accept', 'application/json;charset=UTF-8');
      request.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
      request.send(JSON.stringify({
        consentLevels: consentLevel,
      }));
      request = null;
    }

    function allowAll() {
      consentLevel = Object.keys(consentCapabilities);
      updateConsentSettings();
    }

    function revokeConsent() {
      consentLevel = [];
      updateConsentSettings();
    }

    function openModal() {
      modal.className = modal.className.replace('tb-consent-closed', '');
      mainModal.className = mainModal.className.replace('tb-consent-closed', '');
      modalOverlay.className = modalOverlay.className.replace('tb-consent-closed', '');
    }

    function closeModal() {
      modal.className += ' tb-consent-closed';
      mainModal.className += ' tb-consent-closed';
      modalOverlay.className += ' tb-consent-closed';
    }

    function addCssRule(selector, styles) {
      var sheet = document.styleSheets[0];
      if (sheet.insertRule) {
        return sheet.insertRule(
          selector + ' {' + styles + '}',
          sheet.cssRules.length
        );
      }
      if (sheet.addRule) return sheet.addRule(selector, styles);
    }

    function initTranslations() {
      Object.keys(translations).forEach(function (key) {
        [].slice
          .call(document.querySelectorAll('#tb-consent-main-modal [data-trans="' + key + '"]'))
          .forEach(function (elem) {
            elem.innerHTML = translations[key];
          });
      });
    }

    function initCheckBoxes() {
      Object.keys(consentCapabilities).forEach(function (consentType) {
        inputs[consentType].checked = consentLevel.indexOf(consentType) > -1;
      });
    }

    function initStyles() {
      Object.keys(selectors).forEach(function (contentType) {
        var colorSetting = selectors[contentType];
        Object.keys(colorSetting).forEach(function (colorProperty) {
          var itemSelectors = colorSetting[colorProperty];
          Object.keys(itemSelectors).forEach(function (cssProperty) {
            var selectorItems = itemSelectors[cssProperty];
            selectorItems.forEach(function (selector) {
              addCssRule(
                selector,
                cssProperty + ': ' + config.pallette[contentType][colorProperty] + '!important'
              );
            });
          });
        });
      });
    }

    function checkConsentCapabilities() {
      var can = document.getElementById('tb-consent-website-can');
      var canNot = document.getElementById('tb-consent-website-cannot');
      can.innerHTML = '';
      canNot.innerHTML = '';
      if (consentLevel.indexOf('functional') < 0) {
        capabilitiesBox.style.display = 'none';
        return;
      }
      capabilitiesBox.style.display = 'block';


      Object.keys(consentCapabilities).forEach(function (consentType) {
        consentCapabilities[consentType].forEach(function (consent) {
          var li = document.createElement('LI');
          var enabled = consentLevel.indexOf(consentType) > -1;
          var target = enabled ? can : canNot;
          li.innerText = (enabled ? '✔ ' : '✘ ') + consent;
          target.appendChild(li);
        });
      });
    }

    function checkWarning() {
      if (consentLevel.indexOf('functional') < 0) {
        warningBox.style.display = 'block';
      } else {
        warningBox.style.display = 'none';
      }
    }

    function consentLevelHandler(event) {
      var target = event.target;
      if (!target) {
        return;
      }

      var level = target.id.split('-').pop();
      var index = consentLevel.indexOf(level);
      if (target.checked) {
        if (index < 0) {
          consentLevel.push(level);
        }
      } else if (index > -1) {
        consentLevel.splice(index, 1);
      }

      checkConsentCapabilities();
      checkWarning();
      if (typeof config.onConsentChange === 'function') {
        config.onConsentChange(consentLevel);
      }
    }

    Object.keys(consentCapabilities).forEach(function (consentType) {
      inputs[consentType].addEventListener('click', consentLevelHandler);
    });

    initTranslations();
    initCheckBoxes();
    initStyles();
    checkConsentCapabilities();
    checkWarning();

    window.TbGdprModule = window.TbGdprModule || {};
    window.TbGdprModule.openConsentModal = openModal;
    window.TbGdprModule.closeConsentModal = closeModal;
    window.TbGdprModule.allowAll = allowAll;
    window.TbGdprModule.revokeConsent = revokeConsent;
    window.TbGdprModule.onConsentChange = function (func) {
      config.callbacks.onConsentChange.push(func);
    };

    document.getElementById('tb-consent-close-button').addEventListener('click', function () {
      closeModal();
      updateConsentSettings();
    });
  }

  init();
}());
