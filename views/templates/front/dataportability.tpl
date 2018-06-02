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
{capture name=path}
  <a href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">{l s='My account' mod='tbgdpr'}</a>
  <span class="navigation-pipe">{$navigationPipe}</span>
  <a href="{$link->getModuleLink('tbgdpr', 'overview')|escape:'html':'UTF-8'}">{l s='Privacy Tools' mod='tbgdpr'}</a>
  <span class="navigation-pipe">{$navigationPipe}</span>
  <span class="navigation_page">{l s='Right to data portability' mod='tbgdpr'}</span>
{/capture}

{include file="./misc/errors.tpl"}
{include file="./misc/warnings.tpl"}
{include file="./misc/confirmations.tpl"}

<h1 class="page-heading">{l s='Right to data portability' mod='tbgdpr'}</h1>
<div>
  {$tbgdpr_portability|escape:'htmlall':'UTF-8'}
</div>
<form method="post"
      style="max-width:500px;margin: 0 auto;"
      action="{$link->getModuleLink('tbgdpr', 'dataportability', [], true)|escape:'htmlall':'UTF-8'}">
  <input type="hidden"
         name="csrf"
         value="{$csrf|escape:'html':'UTF-8'}">

  <div class="required form-group form-ok">
    <label for="accept-gdpr-export">
      <input id="accept-gdpr-export"
             name="accept-gdpr-export"
             type="checkbox"
             value="1">
      &nbsp;{l s='I confirm to export my personal data from this shop' mod='tbgdpr'}
    </label>
  </div>

  <input class="btn btn-danger"
         style="margin-right: 5px;"
         name="gdpr-export"
         type="submit"
         value="{l s='Export Data' mod='tbgdpr'}">
</form>
