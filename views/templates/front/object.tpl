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
  <span class="navigation_page">{l s='Unsubscribe' mod='tbgdpr'}</span>
{/capture}

{include file="./misc/errors.tpl"}
{include file="./misc/warnings.tpl"}
{include file="./misc/confirmations.tpl"}

<h1 class="page-heading">{l s='Unsubscribe' mod='tbgdpr'}</h1>

<div>
  {$tbgdpr_object nofilter}
</div>

{if $logged}
  <form method="post"

        action="{$link->getModuleLink('tbgdpr', 'object', [], true)|escape:'htmlall':'UTF-8'}">

    <input type="hidden"
           name="csrf"
           value="{$csrf|escape:'html':'UTF-8'}">
    <div class="required form-group form-ok">
      <label for="accept-gdpr-object">
        <input id="accept-gdpr-object"
               name="accept-gdpr-object"
               type="checkbox"
               value="1">
        &nbsp;{l s='I agree to unsubscribe from all direct marketing purposes' mod='tbgdpr'}
      </label>
    </div>

    <div class="form-group">
      <input class="btn btn-danger"

             name="gdpr-customer-object"
             type="submit"
             value="{l s='Unsubscribe' mod='tbgdpr'}">
    </div>

  </form>
{else}
  <form method="post"

        action="{$link->getModuleLink('tbgdpr', 'object', [], true)|escape:'htmlall':'UTF-8'}">

    <input type="hidden"
           name="csrf"
           value="{$csrf|escape:'html':'UTF-8'}">

    <div class="required form-group form-ok">
      <label for="email" class="required">
        {l s='Email address' mod='tbgdpr'}
        <sup>*</sup>
      </label>
      <input class="is_required validate form-control"
             data-validate="isEmail"
             type="email"
             id="email"
             name="email"
             value=""
              >
    </div>

    <div class="required form-group form-ok">
      <label for="accept-gdpr-object">
        <input id="accept-gdpr-object"
               name="accept-gdpr-object"
               type="checkbox"
               value="1">
        &nbsp;{l s='I agree to unsubscribe from all direct marketing purposes' mod='tbgdpr'}
      </label>
    </div>

    <div class="form-group">
      <input class="btn btn-danger"

             name="gdpr-guest-object"
             type="submit"
             value="{l s='Unsubscribe' mod='tbgdpr'}">
    </div>

  </form>
{/if}
