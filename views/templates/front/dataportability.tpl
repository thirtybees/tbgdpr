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

{block name='page_content'}
  <h1 class="page-heading">{l s='Right to data portability' mod='tbgdpr'}</h1>
  <div>
    {$tbgdpr_portability nofilter}
  </div>
  {if $tbgdpr_status == 'approved'}
    <div class="alert alert-warning" role="alert">
      {l s='Your request has been processed and a CSV file has been generated.' mod='tbgdpr'}
    </div>
  {/if}
  <form method="post" style="max-width:500px;margin: 0 auto;">
    <div class="required form-group form-ok">
        <span>
          <input name="acceptexport"
                 type="checkbox"
                 value="confirmation">
          <label>{l s='I confirm to export my personal data from this shop' mod='tbgdpr'}</label>
        </span>
    </div>

    <input class="btn btn-danger"
           style="margin-right: 5px;"
           name="gdprexport"
           type="submit"
           value="{l s='Export Data' mod='tbgdpr'}">
    <input type="hidden"
           name="token"
           value="{$token|escape:'html':'UTF-8'}">

  </form>
{/block}
