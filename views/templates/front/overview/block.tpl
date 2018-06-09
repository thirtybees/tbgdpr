{*
 * 2018 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 *  @author    thirty bees <modules@thirtybees.com>
 *  @copyright 2017-2018 thirty bees
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<div class="col-lg-4 col-md-6 col-sm-6">
  <a href="{$block.link|escape:'htmlall':'UTF-8'}">
    <div class="panel gdpr-overview-panel">
      <div class="panel-heading  gdpr-overview-panel-heading">
        <i class="icon icon-{$block.icon|escape:'html':'UTF-8'} icon-4x"></i>
        <h3 class="link-item text-uppercase">
          {$block.title|escape:'htmlall':'UTF-8'}
        </h3>
      </div>
      <div class="panel-body" data-mh="1">
        <p>{$block.description|escape:'htmlall':'UTF-8'}</p>
      </div>
      <div class="panel-body">
        <div class="gdpr-overview-panel-btn-container">
          <a class="btn btn-primary"
             href="{$block.link|escape:'htmlall':'UTF-8'}"
          >
            {l s='More information' mod='tbgdpr'} <i class="icon icon-chevron-right"></i>
          </a>
        </div>
      </div>
    </div>
  </a>
</div>
