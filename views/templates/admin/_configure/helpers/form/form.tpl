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
{extends file="helpers/form/form.tpl"}

{block name="input"}
  {if $input.type === 'textarea'}
    {include file="./module_textarea.tpl"}
  {elseif $input.type === 'csp_module'}
    {include file="./module_csp_module"}
  {elseif $input.type === 'csp_manual'}
    {include file="./module_csp_manual.tpl"}
  {elseif $input.type === 'compliant_modules'}
    {include file="./module_compliant_modules.tpl"}
  {else}
    {$smarty.block.parent}
  {/if}
{/block}
