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
  <span class="navigation_page">{l s='Right to be forgotten' mod='tbgdpr'}</span>
{/capture}

{include file="./misc/errors.tpl"}
{include file="./misc/confirmations.tpl"}

<h1 class="page-heading">{l s='Right to be forgotten' mod='tbgdpr'}</h1>
<div>
  {$tbgdpr_forgotten|escape:'htmlall':'UTF-8'}
</div>
{if $tbgdpr_request->status === TbGdprRequest::STATUS_DENIED}
  <div class="alert alert-danger" role="alert">
    {l s='Your request has been denied. Reason:' mod='tbgdpr'}<br/>
    {$tbgdpr_comment|escape:'htmlall':'UTF-8'}
  </div>
{elseif $tbgdpr_request->status === TbGdprRequest::STATUS_PENDING}
  <div class="alert alert-warning" role="alert">
    {l s='Your request has been sent, it will be processed soon.' mod='tbgdpr'}
  </div>
{else}

{/if}
<form method="post" action="{$link->getModuleLink('tbgdpr', 'removedata', [], true)|escape:'htmlall':'UTF-8'}">
  <input type="hidden"
         name="csrf"
         value="{$csrf|escape:'html':'UTF-8'}"
  >

  <div class="required form-group form-ok">
    <label for="accept-gdpr-remove">
      <input id="accept-gdpr-remove"
             name="accept-gdpr-remove"
             type="checkbox"
             value="1"
      >
      &nbsp;{l s='I confirm that I want to have my account and personal data removed from this shop and understand that this action is irreversible' mod='tbgdpr'}
    </label>

  </div>

  {if $tbgdpr_request->status === TbGdprRequest::STATUS_PENDING}
    <input class="btn btn-danger"
           name="cancel-gdpr-remove"
           type="submit"
           value="{l s='Cancel request' mod='tbgdpr'}"
    >
  {else}
    <input class="btn btn-danger"
           name="gdpr-remove"
           type="submit"
           value="{l s='Delete Account' mod='tbgdpr'}"
    >
  {/if}
</form>
