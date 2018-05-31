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

{block name='page_content'}
  <h1 class="page-heading">{l s='Right to be forgotten' mod='tbgdpr'}</h1>
  <div>
    {$tbgdpr_forgotten nofilter}
  </div>
  {if $tbgdpr_status == 'denied'}
    <div class="alert alert-danger" role="alert">
      {l s='Your request could not be processed. Reason:' mod='tbgdpr'} <br/>
      {$tbgdpr_comment nofilter}
    </div>
  {elseif $tbgdpr_status == 'pending'}
    <div class="alert alert-warning" role="alert">
      {l s='Your request has been sent, it will be processed soon.' mod='tbgdpr'}
    </div>
  {else}

  {/if}
  <form method="post" style="max-width:500px;margin: 0 auto;">
    <div class="required form-group form-ok">
        <span>
          <input name="acceptremove"
                 type="checkbox"
                 value="confirmation">
          <label>{l s='I confirm to have my account and personal data removed from this shop' mod='tbgdpr'}</label>
        </span>
    </div>

    {if $tbgdpr_status == 'denied'}
      <input class="btn btn-danger"
             style="margin-right: 5px;"
             name="gdprremove"
             type="submit"
             value="{l s='Delete Account' mod='tbgdpr'}">
      <input type="hidden"
             name="token"
             value="{$token|escape:'html':'UTF-8'}">
    {elseif $tbgdpr_status == 'pending'}
      <input class="btn btn-danger"
             name="cancelgdprremove"
             type="submit"
             value="{l s='Cancel Request' mod='tbgdpr'}">
      <input type="hidden"
             name="token"
             value="{$token|escape:'html':'UTF-8'}">
    {else}
      <input class="btn btn-danger"
             style="margin-right: 5px;"
             name="gdprremove"
             type="submit"
             value="{l s='Delete Account' mod='tbgdpr'}">
      <input type="hidden"
             name="token"
             value="{$token|escape:'html':'UTF-8'}">
    {/if}
  </form>
{/block}
