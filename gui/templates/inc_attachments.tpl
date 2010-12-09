{*
Testlink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_attachments.tpl,v 1.25.4.1 2010/12/09 14:04:44 mx-julian Exp $
Generic attachment management

Input:
	$attach_attachmentsInfos
	$attach_id
	$attach_tableName
	$attach_show_upload_btn
	$attach_downloadOnly
  $attach_tableClassName
  $attach_inheritStyle
  $attach_tableStyles

	

Smarty global variables:
$gsmarty_attachments

20100713 - asimon - added missing ">" to opening "<a>"-tag (syntax error)
20100501 - franciscom - BUGID 3410: Smarty 3.0 compatibility
20080701 - franciscom - removed "none" label when there are no attachments has no value.
20080425 - franciscom -
20070826 - franciscom - added inheritStyle
20070307 - franciscom - BUGID 722

*}
{lang_get s='warning_delete_attachment' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

{literal}
<script type="text/javascript">
{/literal}
var warning_delete_attachment = "{lang_get s='warning_delete_attachment'}";
{if isset($attach_loadOnCancelURL)}
 	var attachment_reloadOnCancelURL = '{$attach_loadOnCancelURL}';
{/if} 
{literal}
</script>
{/literal}

{if $gsmarty_attachments->enabled eq FALSE}
 	  <div class="messages">{lang_get s='attachment_feature_disabled'}<p>
    {$gsmarty_attachments->disabled_msg}
    </div>
{/if}
{include file="inc_del_onclick.tpl"}
{if $gsmarty_attachments->enabled && ($attach_attachmentInfos != "" || $attach_show_upload_btn)}

<table class="{$attach_tableClassName}" {if $attach_inheritStyle == 0} style="{$attach_tableStyles}" {/if}>

 	{if $attach_show_title}
	<tr>
		<td class="bold">{lang_get s="attached_files"}{$tlCfg->gui_title_separator_1}</td>
	</tr>
 	{/if}

	{foreach from=$attach_attachmentInfos item=info}
		{if $info.title eq ""}
			{if $gsmarty_attachments->action_on_display_empty_title == 'show_icon'}
				{assign var="my_link" value=$gsmarty_attachments->access_icon}
			{else}
				{assign var="my_link" value=$gsmarty_attachments->access_string}
		{/if}
		{else}
			{assign var="my_link" value=$info.title|escape}
		{/if}

	  	<tr>
			<td style="vertical-align:middle;"><a href="lib/attachments/attachmentdownload.php?id={$info.id}" target="_blank" class="bold">
			{$my_link}</a> - <span class="italic">{$info.file_name|escape} ({$info.file_size|escape} bytes, {$info.file_type|escape}) {localize_date d=$info.date_added|escape}</span>
				{if !$attach_downloadOnly}
				<a href="javascript:delete_confirmation({$info.id},'{$info.file_name|escape:'javascript'|escape}',
					                                '{$del_msgbox_title|escape:'javascript'|escape}','{$warning_msg|escape:'javascript'|escape}',deleteAttachment_onClick);">
					<img style="border:none;" alt="{lang_get s='alt_delete_attachment'}"
				                         title="{lang_get s='alt_delete_attachment'}"
				                         src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png" /></a>
				{/if}
			</td>
		</tr>
	{/foreach}

  {if $attach_show_upload_btn && !$attach_downloadOnly}
  <tr>
  	<td colspan="2">
  	<input type="button" value="{lang_get s='upload_file_new_file'}" 
  	       onclick="openFileUploadWindow({$attach_id},'{$attach_tableName}')" /></td>
  </tr>
  {/if}

</table>
{/if}
