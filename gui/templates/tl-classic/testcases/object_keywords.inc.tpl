{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource object_keywords.inc.tpl
*}

{lang_get var='kw_labels' 
          s='btn_add,btn_add_to_testsuites_deep,
            keywords,img_title_remove_keyword,warning,none,select_keywords'}


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
function keyword_remove_confirmation(item_id, kw_link_id, keyword, title, msg, pFunction, project_id) {
  var my_msg = msg.replace('%i',keyword);
  var safe_title = escapeHTML(title);
  Ext.Msg.confirm(safe_title, my_msg,
                  function(btn, text) { 
                    pFunction(btn,text,item_id,kw_link_id,project_id);
                  });
}


/**
 * 
 *
 */
function remove_keyword(btn, text, item_id, kw_link_id, project_id) {
  var my_action = fRoot + 'lib/testcases/containerEdit.php?doAction=removeKeyword&item_id='
                     + item_id + '&kw_link_id=' + kw_link_id + '&tproject_id=' + project_id;
  if( btn == 'yes' ) {
    window.location=my_action;
  }
}

var pF_remove_keyword = remove_keyword;

</script>

<form method="post" id="kwf" name="kwf" action="{$basehref}lib/testcases/containerEdit.php">
  <input type="hidden" id="kwf_doAction" name="doAction" value="removeKeyword" />
  <input type="hidden" name="item_id" id="item_id" value="{$args_item_id}" />
  <input type="hidden" name="containerType" id="containerType" 
    value="{$gui->level}" />
  <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}" />


  {$kwView = $gsmarty_href_keywordsView|replace:'%s%':$gui->tproject_id}

  <table cellpadding="0" cellspacing="0" style="font-size:100%;">
    <tr>
      <td width="15%" style="vertical-align:top;"><a href={$kwView}>{$kw_labels.keywords}</a>: &nbsp;
      </td>
      <td style="vertical-align:top;">
          {foreach item=kw_link_item from=$args_keywords_map}
                {$kw_link_item.keyword|escape}
            {if $args_edit_enabled && $gui->assign_keywords }
            <a href="javascript:keyword_remove_confirmation({$gui->item_id},
                     {$kw_link_item.kw_link},
                     '{$kw_link_item.keyword|escape:'javascript'}', 
                     remove_kw_msgbox_title, remove_kw_msgbox_msg, 
                     pF_remove_keyword,{$gui->tproject_id});">
           <img src="{$tlImages.delete}" title="{$kw_labels.img_title_remove_keyword}"  style="border:none" /></a>
           {/if}
            <br />
                {foreachelse}
                  {$kw_labels.none}
            {/foreach}
      </td>      
    </tr>
    <tr>
      <td>
       &nbsp;
      </td> 
    </tr>
    <tr>
      {if $args_edit_enabled && null != $gui->freeKeywords} 
      <td>
       &nbsp;
      </td> 
      <td>
        <select id="free_keywords" name="free_keywords[]"
          data-placeholder="{$kw_labels.select_keywords}"
          class="chosen-add-keywords" multiple="multiple">
          {html_options options = $gui->freeKeywords}
        </select>
        <br>
        <input type="submit" value="{$kw_labels.btn_add}"
          onclick="doAction.value='addKeyword'">
        <input type="submit" value="{$kw_labels.btn_add_to_testsuites_deep}"
          onclick="doAction.value='addKeywordTSDeep'">

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