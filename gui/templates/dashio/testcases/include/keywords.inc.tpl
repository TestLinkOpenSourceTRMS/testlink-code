{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource keywords.inc.tpl
*}

{lang_get var='kw_labels' 
          s='btn_add,img_title_remove_keyword,warning,select_keywords,
             createKW,btn_create_and_link'}


{lang_get s='remove_kw_msgbox_msg' var='remove_kw_msgbox_msg'}
{lang_get s='remove_kw_msgbox_title' var='remove_kw_msgbox_title'}

<script type="text/javascript">
var alert_box_title = "{$kw_labels.warning|escape:'javascript'}";
var remove_kw_msgbox_msg = '{$remove_kw_msgbox_msg|escape:'javascript'}';
var remove_kw_msgbox_title = '{$remove_kw_msgbox_title|escape:'javascript'}';

/**
 * 
 *
 */
function keyword_remove_confirmation(item_id, tckw_link_id, keyword, title, msg, pFunction) 
{
  var my_msg = msg.replace('%i',keyword);
  var safe_title = escapeHTML(title);
  Ext.Msg.confirm(safe_title, my_msg,
                  function(btn, text) { 
                    pFunction(btn,text,item_id, tckw_link_id);
                  });
}


/**
 * 
 *
 */
function remove_keyword(btn, text, item_id, tckw_link_id) {

  var my_url = "{$gui->delTCVKeywordURL}";
  var dummy = my_url.replace('%1',item_id);
  var my_action = dummy.replace('%2',tckw_link_id);


  if( btn == 'yes' ) {
    window.location=my_action;
  }
}

var pF_remove_keyword = remove_keyword;

</script>

<form method="post" id="kwf" name="kwf" 
  action="{$basehref}lib/testcases/tcEdit.php">
  <input type="hidden" id="kwf_doAction" name="doAction" value="removeKeyword" />
  <input type="hidden" name="tcase_id" value="{$args_tcase_id}" />
  <input type="hidden" name="tcversion_id" value="{$args_tcversion_id}" />
  {if property_exists($gui,'tproject_id') } 
    <input type="hidden" name="tproject_id" value="{$gui->tproject_id}" />
  {/if}

  {if property_exists($gui,'tplan_id') } 
    <input type="hidden" name="tplan_id" value="{$gui->tplan_id}" />
  {/if}
  {if property_exists($gui,'show_mode') } 
    <input type="hidden" name="show_mode" value="{$gui->show_mode}" />
  {/if}


  {$kwView = "lib/keywords/keywordsView.php?tproject_id=%s%&openByKWInc=1"|replace:'%s%':$gui->tproject_id}

  {$kwAdd = "lib/keywords/keywordsEdit.php?doAction=create&tproject_id=%s%&directAccess=1"|replace:'%s%':$gui->tproject_id}

  {$kwAL = "lib/keywords/keywordsEdit.php?doAction=cfl&tproject_id=%s%&directAccess=1&tcversion_id=$args_tcversion_id"|replace:'%s%':$gui->tproject_id}

  <table cellpadding="0" cellspacing="0" style="font-size:100%;" width="30%">
    <tr>
      <td width="35%" style="vertical-align:top;">
    <a href="javascript:open_popup('{$kwView}')">{$tcView_viewer_labels.keywords}</a> &nbsp; 

      <a href="javascript:open_popup('{$kwAdd}')">
      <img src="{$tlImages.add}" title="{$kw_labels.createKW}"  style="border:none" /></a>&nbsp; 

      <a href="javascript:open_popup('{$kwAL}')">
      <img src="{$tlImages.keyword_add}" title="{$kw_labels.btn_create_and_link}"  style="border:none" /></a>&nbsp; 

      </td>


      {$removeEnabled = $args_edit_enabled && $gui->assign_keywords &&
                        $args_frozen_version == "no"}

      <td style="vertical-align:top;">
          {foreach item=tckw_link_item from=$args_keywords_map}
                {$tckw_link_item.keyword|escape}
            {if $removeEnabled}
            <a href="javascript:keyword_remove_confirmation({$gui->tcase_id},
                     {$tckw_link_item.tckw_link},
                     '{$tckw_link_item.keyword|escape:'javascript'}', 
                     remove_kw_msgbox_title, remove_kw_msgbox_msg, 
                     pF_remove_keyword);">
           <img src="{$tlImages.delete}" title="{$kw_labels.img_title_remove_keyword}"  style="border:none" /></a>
           {/if}
            <br />
                {foreachelse}
                  {$tcView_viewer_labels.none}
            {/foreach}
      </td>      
    </tr>
    <tr>
      {$addEnabled = $args_edit_enabled}
      {if $addEnabled && null != $gui->currentVersionFreeKeywords} 
      <td>
       &nbsp;  
      <td>
        <select id="free_keywords" name="free_keywords[]"
          data-placeholder="{$kw_labels.select_keywords}"
          class="chosen-add-keywords" multiple="multiple">
          {html_options options = $gui->currentVersionFreeKeywords}
        </select>
        <input class="{#BUTTON_CLASS#}" type="submit" 
               name="btnAdd" id="btnAdd"
               value="{$kw_labels.btn_add}"
               onclick="doAction.value='addKeyword'">
      </td>  

      <script>
      jQuery( document ).ready(
        function() { 
          jQuery(".chosen-add-keywords").chosen({ width: "75%", allow_single_deselect: true }); 
        }
      );
      </script>  
      {/if}
    </tr>
  </table>  
</form>
