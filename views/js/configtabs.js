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
(function (window) {
  function extend(a, b) {
    for (var key in b) {
      if (b.hasOwnProperty(key)) {
        a[key] = b[key];
      }
    }
    return a;
  }

  function ConfigTabs(el, options) {
    this.el = el;
    this.options = extend({}, this.options);
    extend(this.options, options);
    this._init();
  }

  ConfigTabs.prototype.options = {
    start: 0
  };

  ConfigTabs.prototype._init = function () {
    // get current index
    this.index = Number(document.URL.substring(document.URL.indexOf("#tbgdpr_tab_") + 15));
    // tabs elems
    this.tabs = [].slice.call(this.el.querySelectorAll('nav > a'));
    // content items
    this.items = [].slice.call(this.el.querySelectorAll('.content-wrap > section'));
    // set current
    this.current = -1;
    // current index
    this.options.start = (this.index != NaN ? Number(this.index) - 1 : 0);
    // show current content item
    this._show();
    // init events
    this._initEvents();
  };

  ConfigTabs.prototype._initEvents = function () {
    var self = this;
    this.tabs.forEach(function (tab, idx) {
      tab.addEventListener('click', function (ev) {
        self._show(idx);
      });
    });
  };

  ConfigTabs.prototype._show = function (idx) {
    if (this.current >= 0) {
      this.tabs[this.current].className = this.tabs[this.current].className.replace('active', '');
      this.items[this.current].className = '';
      this.items[this.current].style.display = 'none';
    }
    // change current
    this.current = idx != undefined ? idx : this.options.start >= 0 && this.options.start < this.items.length ? this.options.start : 0;
    this.tabs[this.current].className += ' active';
    this.items[this.current].className = 'content-current';
    this.items[this.current].style.display = 'block';
  };

  // add to global namespace
  window.ConfigTabs = ConfigTabs;
})(window);
