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
<div class="tabs clearfix">
  <div class="sidebar navigation col-md-2">
    {if isset($tab_contents.logo)}
      <img class="tabs-logo" src="{$tab_contents.logo|escape:'htmlall':'UTF-8'}" />
    {/if}
    {assign var='tab_nr' value=1}
    {foreach from=$tab_contents.contents key=group item=tab_group}
      <nav class="list-group category-list">
        {foreach from=$tab_group key=tab_nbr item=content}
          <a class="list-group-item tbgdpr-tab{if $content@iteration == 1} tbgdpr-tab-first{/if}"
             href="#{$moduleName|escape:'htmlall':'UTF-8'}_tab_{{$tab_nr++}|intval}"
          >
            {if isset($content.icon) && $content.icon != false}
              <i class="{$content.icon|escape:"htmlall":"UTF-8"} pstab-icon"></i>
            {/if}

            {$content.name}

            {if !empty($content.badge) && $content.badge != false}
              <span class="badge-module-tabs pull-right badge">{$content.badge}</span>
            {/if}
          </a>
        {/foreach}
      </nav>
    {/foreach}
  </div>

  <div class="col-md-10 content-wrap">
    {assign var='tab_nr' value=1}
    {foreach from=$tab_contents.contents key=group item=content_group}
      {foreach from=$content_group item=content}
        <section id="section-shape-{{$tab_nr++}|intval}" style="display: none">{$content.value|escape:'UTF-8'}</section>
      {/foreach}
    {/foreach}
  </div>

</div>
<script type="text/javascript" src="{$new_base_dir|escape:'htmlall':'UTF-8'}views/js/configtabs.js"></script>
<script type="text/javascript">
  (function() {
    function getStyleRuleValue(style, selector) {
      for (var i = 0; i < document.styleSheets.length; i++) {
        var mysheet = document.styleSheets[i];
        var myrules = mysheet.cssRules ? mysheet.cssRules : mysheet.rules;
        for (var j = 0; j < myrules.length; j++) {
          if (myrules[j].selectorText && myrules[j].selectorText.toLowerCase() === selector) {
            return myrules[j].style[style];
          }
        }

      }
    }

    function addCssRule(styles, selector) {
      var sheet = document.styleSheets[0];
      if (sheet.insertRule) {
        return sheet.insertRule(
          selector + ' { ' + styles + ' }',
          sheet.cssRules.length
        );
      }
      if (sheet.addRule) return sheet.addRule(selector, styles);
    }

    function initializeDynamicStyle() {
      // Active tab
      var tabColor = getStyleRuleValue('color', '.bootstrap a');
      addCssRule('color: ' + tabColor + '!important', 'div.sidebar.navigation a.active > span.badge-module-tabs');
    }

    function init() {
      if (typeof window.ConfigTabs === 'undefined') {
        setTimeout(init, 10);
        return;
      }

      window.TbGdprModule = window.TbGdprModule | { };
      window.TbGdprModule.tabs = new window.ConfigTabs(document.querySelector('#main'));
      initializeDynamicStyle();
    }
    init();
  }());
</script>
