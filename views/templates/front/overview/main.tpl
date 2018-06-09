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
{capture name=path}{strip}
  <a href="{$link->getPageLink('my-account', true, [], true)|escape:'html':'UTF-8'}">{l s='My account' mod='tbgdpr'}</a>
  <span class="navigation-pipe">{$navigationPipe}</span>
  <span class="navigation_page">{l s='Privacy Tools' mod='tbgdpr'}</span>
{/strip}{/capture}

<h1 id="gdpr-overview"
    class="page-heading"
>
  {l s='Privacy Tools' mod='tbgdpr'}
</h1>

<h2 class="page-heading">{l s='Available Tools' mod='tbgdpr'}</h2>

<div class="links clearfix" data-mh="gdpr-block">
  {foreach $tbgdpr_blocks as $block}
    {include file="../overview/block.tpl"}
  {/foreach}
</div>
<h2 class="page-heading">{l s='Pending Requests' mod='tbgdpr'}</h2>
{if !empty($gdpr_requests)}
<table class="table table-responsive">
  <tbody>
    <tr>
    </tr>
  </tbody>
</table>
{else}
  <div class="alert alert-warning">{l s='No requests pending' mod='tbgdpr'}</div>
{/if}
<script type="text/javascript">
  (function initMatchHeight() {
    function throttle(callback, delay) {
      var isThrottled = false, args, context;

      function wrapper() {
        if (isThrottled) {
          args = arguments;
          context = this;
          return;
        }

        isThrottled = true;
        callback.apply(this, arguments);

        setTimeout(function () {
          isThrottled = false;
          if (args) {
            wrapper.apply(context, args);
            args = context = null;
          }
        }, delay);
      }

      return wrapper;
    }

    if (typeof window.gdprMatchHeight !== 'function') {
      return setTimeout(initMatchHeight, 100);
    }

    window.addEventListener('resize', throttle(function () {
      window.gdprMatchHeight('gdpr-block');
    }, 200));

    window.gdprMatchHeight('gdpr-block');
  }());
</script>
