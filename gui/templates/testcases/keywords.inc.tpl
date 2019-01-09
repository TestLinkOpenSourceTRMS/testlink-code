{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource keywords.inc.tpl
*}

{lang_get var='kw_labels' 
          s='btn_add,img_title_remove_keyword,warning,select_keywords'}


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
  var safe_title = title.escapeHTML();
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
  var my_action = fRoot + 'lib/testcases/tcEdit.php?doAction=removeKeyword&tcase_id='
                     + item_id + '&tckw_link_id=' + tckw_link_id;
  if( btn == 'yes' ) {
    window.location=my_action;
  }
}

var pF_remove_keyword = remove_keyword;

</script>

<form method="post" id="kwf" name="kwf" action="{$basehref}lib/testcases/tcEdit.php">
    <input type="hidden" id="kwf_doAction" name="doAction" value="removeKeyword" />
    <input type="hidden" name="tcase_id" id="tcase_id" value="{$args_tcase_id}" />
    <input type="hidden" name="tcversion_id" id="tcversion_id" value="{$args_tcversion_id}" />

	{$kwView = $gsmarty_href_keywordsView|replace:'%s%':$gui->tproject_id}

	<table class="table table-striped table-bordered">
		<tbody>
    		<tr>
    			<th width="35%" style="vertical-align:top;">
    				<a href={$kwView}>{$tcView_viewer_labels.keywords}</a>: &nbsp;
    			</th>    
        	</tr>
        	<tr>
        		<td style="vertical-align:top;">
              		{foreach item=tckw_link_item from=$args_keywords_map}
                    	{$tckw_link_item.keyword|escape}
                		{if $args_edit_enabled && $gui->assign_keywords && $args_frozen_version == "no"}
                            <a href="javascript:keyword_remove_confirmation({$gui->tcase_id}, {$tckw_link_item.tckw_link}, '{$tckw_link_item.keyword|escape:'javascript'}', 
                                     remove_kw_msgbox_title, remove_kw_msgbox_msg,pF_remove_keyword);">
                           	<img src="{$tlImages.delete}" title="{$kw_labels.img_title_remove_keyword}" style="border:none" /></a>
               			{/if}
                		<br />
                	{foreachelse}
    					{$tcView_viewer_labels.none}
                	{/foreach}
          		</td> 
        	</tr>
        	<tr>
    			{if $args_edit_enabled && null != $gui->currentVersionFreeKeywords} 
    				<td>
            			<select id="free_keywords" name="free_keywords[]" data-placeholder="{$kw_labels.select_keywords}" class="chosen-add-keywords" multiple="multiple">
              				{html_options options = $gui->currentVersionFreeKeywords}
            			</select>
            			<input type="submit" value="{$kw_labels.btn_add}" onclick="doAction.value='addKeyword'" class="btn btn-primary">
          			</td>  
    
          			<script>
          				jQuery( document ).ready(
            				function() { 
              					jQuery(".chosen-add-keywords").chosen({ width: "30%", allow_single_deselect: true }); 
            				}
          				);
          			</script>  
      			{/if}
        	</tr>   	
    	</tbody>
  	</table>  
</form>