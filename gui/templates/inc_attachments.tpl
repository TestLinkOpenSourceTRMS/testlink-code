{* 
Testlink Open Source Project - http://testlink.sourceforge.net/ 
$Id: inc_attachments.tpl,v 1.8 2006/11/20 20:35:59 schlundus Exp $
Generic attachment management 

Input:
	$attachmentsInfos
	$id
	$tableName
	$show_upload_btn
	$downloadOnly

Smarty global variables:
$gsmarty_attachments

20060823 - franciscom - added warning messagge if attachment not possible
                        due to directory problems

*}
{literal}
<script type="text/javascript">
{/literal}
var warning_delete_attachment = "{lang_get s='warning_delete_attachment'}";
{literal}
</script>
{/literal}

{* -------------------------------------------------------------------------------------- *}
{* Manage missing arguments                                                               *}
{assign var="my_show_title"  value=$show_title|default:true}
{assign var="my_show_upload_btn"  value=$show_upload_btn|default:true}
{assign var="downloadOnly"  value=$downloadOnly|default:false}
{assign var="tableClassName"  value=$tableClassName|default:"simple"}
{assign var="tableStyles"  value=$tableStyles|default:"font-size:12px"}
{* -------------------------------------------------------------------------------------- *}

{if $gsmarty_attachments->enabled eq FALSE}
 	  <div class="warning_message">{lang_get s='attachment_feature_disabled'}<p>
    {$gsmarty_attachments->disabled_msg}	
    </div>
{/if}


{if $gsmarty_attachments->enabled && ($attachmentInfos neq "" || $my_show_upload_btn) }

<table class="{$tableClassName}" style="{$tableStyles}">

 	{if $my_show_title}
	<tr>
		<td class="bold">{lang_get s="attached_files"}:</td>
	</tr>
 	{/if}

	{assign var="access_label_id" value=$gsmarty_attachments->access_label_id}
		    
	{foreach from=$attachmentInfos item=info}
	
	{if $info.title eq ""}
		{if $gsmarty_attachments->action_on_display_empty_title == 'show_icon'}
			{assign var="my_link" value=$gsmarty_attachments->access_icon }
		{else}
			{assign var="my_link" value=$gsmarty_attachments->access_string}
	{/if}
	{else}
		{assign var="my_link" value=$info.title|escape}
	{/if}
  
  	<tr>
		<td><a href="lib/attachments/attachmentdownload.php?id={$info.id}" target="_blank" class="bold">
		{$my_link}</a> - <span class="italic">{$info.file_name|escape} ({$info.file_size|escape} bytes, {$info.file_type|escape}) {localize_date d=$info.date_added|escape}</span>
		{if !$downloadOnly}
		<a href="javascript:deleteAttachment_onClick({$info.id});"><img style="border:none" alt="{lang_get s='alt_delete_build'}" src="icons/thrash.png"/></a>
		{/if}
		</td>
	</tr>
	{/foreach}

  {if $my_show_upload_btn && !$downloadOnly}
  <tr>
  	<td colspan="2"><input type="button" value="{lang_get s='button_upload'}..." onclick="openFileUploadWindow({$id},'{$tableName}')" />&nbsp;{lang_get s="upload_file_new_file"}</td>
  </tr>
  {/if}
  
</table>
{/if}
