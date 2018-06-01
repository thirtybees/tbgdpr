<div class="table-responsive" style="min-height: 200px">
  <table class="table">
    <thead>
      <tr>
        <th><span class="title_box">{l s='ID' mod='tbgdpr'}</span></th>
        <th><span class="title_box">{l s='Customer / Visitor' mod='tbgdpr'}</span></th>
        <th><span class="title_box">{l s='Last update' mod='tbgdpr'}</span></th>
        <th><span class="title_box">{l s='Status'}</span></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      {foreach $input.requests as $request}
        <tr style="min-height: 200px">
          <td><p>{$request->id|intval}</p></td>
          <td>{$request->customer->firstname|escape:'htmlall':'UTF-8'} {$request->customer->lastname|escape:'htmlall':'UTF-8'}</td>
          <td>{$request->date_add|date_format:'Y-m-d H:i:s'}</td>
          <td>{if $request->status == TbGdprRequest::STATUS_PENDING}{l s='Pending' mod='tbgdpr'}{elseif $request->status == TbGdprRequest::STATUS_APPROVED}{l s='Approved' mod='tbgdpr'}{else}{l s='Denied' mod='tbgdpr'}{/if}</td>
          <td class="text-right">
            <div class="btn-group-action">
              <div class="btn-group pull-right">
                <a {if $request->status != TbGdprRequest::STATUS_PENDING}disabled="disabled"{/if}
                   title="{l s='Approve' mod='tbgdpr'}"
                   class="btn btn-default"
                   onclick="approveRemovalRequest({$request->id|intval});"
                   data-approve-request="{$request->id|intval}"
                >
                  <i class="icon-check"></i> {l s='Approve' mod='tbgdpr'}
                </a>
                <button {if $request->status != TbGdprRequest::STATUS_PENDING}disabled="disabled"{/if}
                        class="btn btn-default dropdown-toggle"
                        data-toggle="dropdown"
                        data-deny-request="{$request->id|intval}"
                >
                  <i class="icon-caret-down"></i>&nbsp;
                </button>
                <ul class="dropdown-menu" style="cursor: pointer;">
                  <li disabled="disabled">
                    <a disabled="disabled"
                       title="{l s='Deny' mod='tbgdpr'}"
                       onclick="denyRemovalRequest({$request->id|intval});"
                    >
                      <i class="icon-times"></i> {l s='Deny' mod='tbgdpr'}</a>
                  </li>
                </ul>
              </div>
            </div>
          </td>
        </tr>
      {/foreach}
    </tbody>
  </table>
</div>
