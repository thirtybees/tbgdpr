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
<div class="panel col-lg-9 module-container">
  <div class="panel-heading">
    <i class="icon icon-puzzle-piece"></i> {l s='Modules that are known to be compliant' mod='tbgdpr'}
  </div>
  {foreach $input.modules as $module}
    <div class="panel module-card"
         title="{$module->displayName|escape:'html':'UTF-8'} <i class='popover-close icon icon-times' onclick='closeGdprPopover(event);'></i>"
         data-toggle="popover"
         data-content="{{include file="./module_compliant_modules_popover.tpl"}|escape:'htmlall':'UTF-8'}"
         data-html="true"
    >
      <div class="panel-body">
        <img class="img img-responsive" src="/modules/{$module->name|escape:'html':'UTF-8'}/logo.png" alt="{$module->displayName|escape:'html':'UTF-8'}">
      </div>
      <div class="panel-footer">
        <span class="small">{$module->displayName|escape:'html':'UTF-8'}</span>
      </div>
    </div>
  {foreachelse}
    <div class="alert alert-info">{l s='No modules found' mod='tbgdpr'}</div>
  {/foreach}
</div>
<script type="text/javascript">
  (function () {
    function closePopover(event) {
      var $card = $(event.target).closest('.popover').prev();
      $card.popover('hide');
    }

    function init() {
      if (typeof $ === 'undefined') {
        setTimeout(init, 100);
        return;
      }

      window.closeGdprPopover = closePopover;
      $('[data-toggle="popover"]').popover();
    }
    init();
  }());
</script>
