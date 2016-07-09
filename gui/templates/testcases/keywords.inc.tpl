{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource keywords.inc.tpl
@internal revisions
@since 1.9.13
*}

{lang_get var='kw_labels' 
          s='in, btn_add, img_title_remove_keyword,no_records_found,
             title_test_case,match_count,warning,keywords,none,
             commit_title,current_direct_link,current_testcase,test_case,
             specific_direct_link,req_does_not_exist,actions'}


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
function keyword_remove_confirmation(item_id, keyword_id, keyword, title, msg, pFunction) 
{
  var my_msg = msg.replace('%i',keyword);
  var safe_title = title.escapeHTML();
  Ext.Msg.confirm(safe_title, my_msg,
                  function(btn, text) { 
                    pFunction(btn,text,item_id, keyword_id);
                  });
}


/**
 * 
 *
 */
function remove_keyword(btn, text, item_id, keyword_id) 
{
  var my_action = fRoot + 'lib/testcases/tcEdit.php?doAction=removeKeyword&tcase_id='
                     + item_id + '&keyword_id=' + keyword_id;
  if( btn == 'yes' ) 
  {
    window.location=my_action;
  }
}

var pF_remove_keyword = remove_keyword;

</script>
<form method="post" id="kwf" name="kwf" action="{$basehref}lib/testcases/tcEdit.php">
  <input type="hidden" id="kwf_doAction" name="doAction" value="removeKeyword" />
  <input type="hidden" name="tcase_id" id="tcase_id" value="{$gui->tcase_id}" />
  <input type="hidden" name="tcversion_id" id="tcversion_id" value="{$gui->tcversion_id}" />

  {$kwView = $gsmarty_href_keywordsView|replace:'%s%':$gui->tproject_id}

  <table cellpadding="0" cellspacing="0" style="font-size:100%;">
    <tr>
      <td width="35%" style="vertical-align:top;"><a href={$kwView}>{$tcView_viewer_labels.keywords}</a>: &nbsp;
      </td>
      <td style="vertical-align:top;">
          {foreach item=keyword_item from=$args_keywords_map}
                {$keyword_item.keyword|escape}
            {if $edit_enabled}
            <a href="javascript:keyword_remove_confirmation({$gui->tcase_id}, {$keyword_item.keyword_id},
                                                             '{$keyword_item.keyword|escape:'javascript'}', 
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
  </table>  
</form>
