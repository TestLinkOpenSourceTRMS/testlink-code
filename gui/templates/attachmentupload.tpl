{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: attachmentupload.tpl,v 1.10 2009/01/29 20:58:22 schlundus Exp $ *}
{* Purpose: smarty template - template for attachment upload dialog 

   rev :
         20070310 - BUGID 732 

*}
{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
{include file="inc_del_onclick.tpl"}


<script type="text/javascript">
var alert_box_title = "{lang_get s='warning'}";
var warning_empty_title = "{lang_get s='enter_attachment_title'}";
</script>
<body onunload="attachmentDlg_onUnload()" onload="attachmentDlg_onLoad()">
{config_load file="input_dimensions.conf" section="attachmentupload"} {* Constant definitions *}

<h1 class="title">{lang_get s='title_upload_attachment'}</h1>
{if $bUploaded == 1}
	{lang_get s='attachment_upload_ok' var=user_feedback}
  {include file="inc_update.tpl" user_feedback=$user_feedback}
{/if}

<div class="workBack">
	<h2>{lang_get s='title_choose_local_file'}</h2>
	
	<form action="lib/attachments/attachmentupload.php" method="post" enctype="multipart/form-data" id="aForm">
		<input type="hidden" name="MAX_FILE_SIZE" value="{$import_limit}" /> {* restrict file size *}
		<p>{lang_get s='local_file'}
			<input type='hidden' value='{$id}' name='id' />
			<input type='hidden' value='{$tableName}' name='tableName' />
			<input type="file" name="uploadedFile" size="{#UPLOAD_FILENAME_SIZE#}" />
		</p>
		<p>
			{lang_get s='enter_attachment_title'}:
			<input type="text" id="title" name="title" maxlength="{#ATTACHMENT_TITLE_MAXLEN#}" 
			                                size="{#ATTACHMENT_TITLE_SIZE#}" />
		</p>
		<div class="groupBtn">
			<input type="submit" value="{lang_get s='btn_upload_file'}" onclick="return attachmentDlg_onSubmit({$gsmarty_attachments->allow_empty_title eq true})" />
			<input type="button" value="{lang_get s='btn_cancel'}" onclick="window.close()" />
		</div>
	</form>
	<p>
		{lang_get s='max_size_cvs_file'}: {$import_limit} Bytes
	</p>
	{if $msg neq ''}
		<p class="bold" style="color:red">{$msg}</p>
	{/if}
</div>

</body>
</html>