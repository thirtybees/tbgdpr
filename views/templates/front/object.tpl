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

{block name='page_content'}
  <h1 class="page-heading">{l s='Unsubscribe' mod='tbgdpr'}</h1>
  <div>
    {$tbgdpr_object nofilter}
  </div>
  {if $status == 'pending'}
    <div class="alert alert-danger" role="alert">
      {l s='We have sent an confirmation email to your email account' mod='tbgdpr'}
    </div>
  {elseif $status == 'approved'}
    <div class="alert alert-warning" role="alert">
      {l s='Your request has been processed and you have been removed from all direct marketing.' mod='tbgdp'}
    </div>
  {/if}

  {if $logged}
    <form method="post" style="max-width:500px;margin: 0 auto;">
      <div class="required form-group form-ok">
        <label for="email" class="required">
          {l s='Email address' mod='tbgdpr'}
          <sup>*</sup>
        </label>
        <input class="is_required validate form-control" data-validate="isEmail" type="email" id="email" name="email"
               value="{$customerEmail|escape:'html':'UTF-8'}">
      </div>
      <div class="form-group form-ok">
        <label for="phone_mobile">
          {l s='Mobile phone' mod='tbgdpr'}
        </label>
        <input class="validate form-control" data-validate="isPhoneNumber" type="tel" id="phone_mobile"
               name="phone_mobile" value="{$customerMobilePhone|escape:'html':'UTF-8'}">
      </div>
      <div class="form-group">
        <span><input name="agreeobject" type="checkbox" value="confirmation"></span>
        <label>{l s='I agree to unsubscribe from all direct marketing purposes' mod='tbgdpr'}</label>
      </div>
      <div class="form-group">
        <input type="hidden" name="token" value="{$token|escape:'html':'UTF-8'}">
        <input class="btn btn-danger" style="margin-right: 5px;" name="submitcustomerobject" type="submit"
               value="{l s='Unsubscribe' mod='tbgdpr'}">
      </div>
    </form>
  {else}
    <form method="post" style="max-width:500px;margin: 0 auto;">
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
      <div class="form-group">
        <span><input name="agreeobject" type="checkbox" value="confirmation"></span>
        <label>{l s='I agree to unsubscribe from all direct marketing purposes' mod='tbgdpr'}</label>
      </div>
      <div class="form-group">
        <input type="hidden" name="token" value="{$token|escape:'html':'UTF-8'}">
        <input class="btn btn-danger"
               style="margin-right: 5px;"
               name="submitguestobject"
               type="submit"
               value="{l s='Unsubscribe' mod='tbgdpr'}"
        >
      </div>
    </form>
  {/if}

{/block}
