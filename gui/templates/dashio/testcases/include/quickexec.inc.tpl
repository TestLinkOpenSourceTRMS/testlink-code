{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource quickexec.inc.tpl
@internal revisions
@since 1.9.13
*}

{lang_get var='qex_labels' 
          s='testplan_usage,platform,version,test_plan,goto_execute'}

<table class="simple">
    <theader {$addInfoDivStyle}>{$qex_labels.testplan_usage}</theader>
    <tr>
    <th>{$qex_labels.version}</th>
    <th>{$tlImages.sort_hint}{$qex_labels.test_plan}</th>
    {if $gui->platforms != null}
      <th>{$tlImages.sort_hint}{$qex_labels.platform}</th>
    {/if}
    </tr>
    {foreach from=$args_linked_versions item=link2tplan_platform}
      {foreach from=$link2tplan_platform item=link2platform key=tplan_id}
        {foreach from=$link2platform item=version_info}
          <tr>
          <td style="width:10%;text-align:center;">{$version_info.version}</td>
          <td>{$version_info.tplan_name|escape}
              <a href="{$execFeatureAction}" target="_parent" ><img class="clickable" src="{$tlImages.execute}" 
                             title="{$qex_labels.goto_execute}" /></a>
          </td>
          {if $gui->platforms != null}
            <td>
            {if $version_info.platform_id > 0}
              {$gui->platforms[$version_info.platform_id]|escape}
            {/if}          
            </td>
          {/if}
          </tr>
        {/foreach}
      {/foreach}
    {/foreach}
</table>
