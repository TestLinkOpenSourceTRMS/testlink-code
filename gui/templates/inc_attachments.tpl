{* Testlink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_attachments.tpl,v 1.2 2006/04/29 19:32:54 schlundus Exp $ *}
{* Purpose: smarty template - show SQL update result *}
{* INPUT: 
	
*}
{literal}
<script type="text/javascript">
{/literal}
var warning_delete_attachment = "{lang_get s='warning_delete_attachment'}";
{literal}
</script>
{/literal}

<table class="simple">
	<tr>
		<td class="bold">{lang_get s="attached_files"}:</td>
	</tr>
	{foreach from=$attachmentInfos item=info}
	<tr>
		<td><a href="lib/attachments/attachmentdownload.php?id={$info.id}" target="_blank" class="bold">{$info.title}</a> - <span class="italic">{$info.file_name} ({$info.file_size} bytes, {$info.file_type}) {localize_date d=$info.date_added}</span>
		<a href="javascript:deleteAttachment_onClick({$info.id});"><img style="border:none" alt="{lang_get s='alt_delete_build'}" src="icons/thrash.png"/></a>
		</td>
	</tr>
	{/foreach}
	<tr>
		<td colspan="2"><input type="button" value="{lang_get s='button_upload'}..." onclick="openFileUploadWindow({$id},'{$tableName}')" />&nbsp;{lang_get s="upload_file_new_file"}</td>
	</tr>
</table>
