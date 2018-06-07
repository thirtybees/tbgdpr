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
{if isset($input.maxchar) && $input.maxchar}<div class="input-group">{/if}
  {assign var=use_textarea_autosize value=true}
  {if isset($input.lang) AND $input.lang}
  {foreach $languages as $language}
    <script type="text/javascript">
      (function () {
        $(document).ready(function() {
          var selector = '{if isset($input.id)}{$input.id|escape:'javascript':'UTF-8'}{else}{$input.name|escape:'javascript':'UTF-8'}{/if}_{$language.id_lang|intval}';
          if (typeof window.IntersectionObserver !== 'undefined') {
            var observer = new IntersectionObserver(function (changes) {
              changes.forEach(function (change) {
                if (change.intersectionRatio > 0) {
                  tinySetup({
                    editor_selector: '{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}_{$language.id_lang|intval}',
                  });
                }
              });
            }, {
              root: null,
              rootMargin: '0px',
              threshold: 0.5,
            });
            observer.observe(document.getElementById('{if isset($input.id)}{$input.id|escape:'javascript':'UTF-8'}{else}{$input.name|escape:'javascript':'UTF-8'}{/if}_{$language.id_lang|intval}'));
          } else {
            var interval = setInterval(function () {
              if ($('#' + selector).is(':visible')) {
                tinySetup({
                  editor_selector: '{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}_{$language.id_lang|intval}',
                });
                clearInterval(interval);
              }
            }, 200);
          }
        });
      }());
    </script>
  {if $languages|count > 1}
    <div class="form-group translatable-field lang-{$language.id_lang}"{if $language.id_lang != $defaultFormLanguage} style="display:none;"{/if}>
      <div class="col-lg-9">
        {/if}
        {if isset($input.maxchar) && $input.maxchar}
          <span id="{if isset($input.id)}{$input.id}_{$language.id_lang}{else}{$input.name}_{$language.id_lang}{/if}_counter" class="input-group-addon">
            <span class="text-count-down">{$input.maxchar|intval}</span>
          </span>
        {/if}
        <textarea{if isset($input.readonly) && $input.readonly}
          readonly="readonly"{/if}
                name="{$input.name}_{$language.id_lang}"
                id="{if isset($input.id)}{$input.id}{else}{$input.name}{/if}_{$language.id_lang}"
                class="{if isset($input.id)}{$input.id|escape:'htmlall':'UTF-8'}{else}{$input.name|escape:'htmlall':'UTF-8'}{/if}_{$language.id_lang|intval} {if isset($input.autoload_rte) && $input.autoload_rte}rte autoload_rte{else}textarea-autosize{/if}{if isset($input.class)} {$input.class}{/if}"{if isset($input.maxlength) && $input.maxlength} maxlength="{$input.maxlength|intval}"{/if}{if isset($input.maxchar) && $input.maxchar} data-maxchar="{$input.maxchar|intval}"{/if}>{$fields_value[$input.name][$language.id_lang]|escape:'html':'UTF-8'}</textarea>
        {if $languages|count > 1}
      </div>
      <div class="col-lg-2">
        <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
          {$language.iso_code}
          <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
          {foreach from=$languages item=language}
            <li>
              <a href="javascript:hideOtherLanguage({$language.id_lang});" tabindex="-1">{$language.name}</a>
            </li>
          {/foreach}
        </ul>
      </div>
    </div>
  {/if}
  {/foreach}
  {if isset($input.maxchar) && $input.maxchar}
    <script type="text/javascript">
      $(document).ready(function () {
        {foreach from=$languages item=language}
        countDown($("#{if isset($input.id)}{$input.id}_{$language.id_lang}{else}{$input.name}_{$language.id_lang}{/if}"), $("#{if isset($input.id)}{$input.id}_{$language.id_lang}{else}{$input.name}_{$language.id_lang}{/if}_counter"));
        {/foreach}
      });
    </script>
  {/if}
  {else}
  {if isset($input.maxchar) && $input.maxchar}
    <span id="{if isset($input.id)}{$input.id}_{$language.id_lang}{else}{$input.name}_{$language.id_lang}{/if}_counter" class="input-group-addon">
          <span class="text-count-down">{$input.maxchar|intval}</span>
        </span>
  {/if}
    <textarea{if isset($input.readonly) && $input.readonly} readonly="readonly"{/if} name="{$input.name}" id="{if isset($input.id)}{$input.id}{else}{$input.name}{/if}" {if isset($input.cols)}cols="{$input.cols}"{/if} {if isset($input.rows)}rows="{$input.rows}"{/if} class="{if isset($input.autoload_rte) && $input.autoload_rte}rte autoload_rte{else}textarea-autosize{/if}{if isset($input.class)} {$input.class}{/if}"{if isset($input.maxlength) && $input.maxlength} maxlength="{$input.maxlength|intval}"{/if}{if isset($input.maxchar) && $input.maxchar} data-maxchar="{$input.maxchar|intval}"{/if}>{$fields_value[$input.name]|escape:'html':'UTF-8'}</textarea>
  {if isset($input.maxchar) && $input.maxchar}
    <script type="text/javascript">
      $(document).ready(function () {
        countDown($("#{if isset($input.id)}{$input.id}{else}{$input.name}{/if}"), $("#{if isset($input.id)}{$input.id}{else}{$input.name}{/if}_counter"));
      });
    </script>
  {/if}
  {/if}
  {if isset($input.maxchar) && $input.maxchar}</div>{/if}
