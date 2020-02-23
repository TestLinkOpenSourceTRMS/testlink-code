{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource platforms.inc.tpl
*}

{lang_get var='plat_labels' 
          s='btn_add,img_title_remove_platform,warning,
             select_platforms,title_platforms'}


{lang_get s='remove_plat_msgbox_msg' var='remove_plat_msgbox_msg'}
{lang_get s='remove_plat_msgbox_title' var='remove_plat_msgbox_title'}

<script type="text/javascript">
var alert_box_title = "{$plat_labels.warning|escape:'javascript'}";
var remove_plat_msgbox_msg = '{$remove_plat_msgbox_msg|escape:'javascript'}';
var remove_plat_msgbox_title = '{$remove_plat_msgbox_title|escape:'javascript'}';

/**
 * 
 *
 */
function platform_remove_confirmation(item_id, tcplat_link_id, platform, title, msg, pFunction) 
{
  var my_msg = msg.replace('%i',platform);
  var safe_title = escapeHTML(title);
  Ext.Msg.confirm(safe_title, my_msg,
                  function(btn, text) { 
                    pFunction(btn,text,item_id, tcplat_link_id);
                  });
}


/**
 * 
 *
 */
function remove_platform(btn, text, item_id, tcplat_link_id) {

  var my_url = "{$gui->delTCVPlatformURL}";
  var dummy = my_url.replace('%1',item_id);
  var my_action = dummy.replace('%2',tcplat_link_id);

  if( btn == 'yes' ) {
    window.location=my_action;
  }
}

var pF_remove_platform = remove_platform;

</script>

<form method="post" id="platf2" name="platf2" action="{$basehref}lib/testcases/tcEdit.php">
  <input type="hidden" id="platf2_doAction" name="doAction" value="removeplatform" />
  <input type="hidden" name="tcase_id" id="tcase_id" value="{$args_tcase_id}" />
  <input type="hidden" name="tcversion_id" id="tcversion_id" value="{$args_tcversion_id}" />

  <input type="hidden" name="tproject_id" id="tproject_id_for_plat"
         value="{$gui->tproject_id}" />

  {if property_exists($gui,'tplan_id') } 
    <input type="hidden" name="tplan_id" value="{$gui->tplan_id}" />
  {/if}
  {if property_exists($gui,'show_mode') } 
    <input type="hidden" name="show_mode" value="{$gui->show_mode}" />
  {/if}


  {$itemView =
    $gsmarty_href_platformsView|replace:'%s%':$gui->tproject_id}

  <table cellpadding="0" cellspacing="0" style="font-size:100%;">
    <tr>
      <td width="35%" style="vertical-align:top;"><a href={$itemView}>{$plat_labels.title_platforms}</a>: &nbsp;
      </td>

      {* $gui->assign_platforms && *}
      {$removeEnabled = $args_edit_enabled && 
                        $args_frozen_version == "no"}

      <td style="vertical-align:top;">
          {foreach item=tcplat_link_item from=$args_platforms_map}
                {$tcplat_link_item.name|escape}
            {if $removeEnabled}
            <a href="javascript:platform_remove_confirmation({$gui->tcase_id},
                     {$tcplat_link_item.tcplat_link},
                     '{$tcplat_link_item.name|escape:'javascript'}', 
                     remove_plat_msgbox_title, remove_plat_msgbox_msg, 
                     pF_remove_platform);">
           <img src="{$tlImages.delete}" title="{$plat_labels.img_title_remove_platform}"  style="border:none" /></a>
           {/if}
            <br />
                {foreachelse}
                  {$tcView_viewer_labels.none}
            {/foreach}
      </td>      
    </tr>
    <tr>
      {$addEnabled = $args_edit_enabled}
      {if $addEnabled && null != $gui->currentVersionFreePlatforms} 
      <td>
       &nbsp;  
      <td>
        <select id="free_platforms" name="free_platforms[]"
          data-placeholder="{$plat_labels.select_platforms}"
          class="chosen-add-platforms" multiple="multiple">
          {html_options options = $gui->currentVersionFreePlatforms}
        </select>
        <input type="submit" value="{$plat_labels.btn_add}"
          onclick="doAction.value='addPlatform'">
      </td>  

      <script>
      jQuery( document ).ready(
        function() { 
          jQuery(".chosen-add-platforms").chosen({ width: "75%", allow_single_deselect: true }); 
        }
      );
      </script>  
      {/if}
    </tr>
  </table>  
</form>
