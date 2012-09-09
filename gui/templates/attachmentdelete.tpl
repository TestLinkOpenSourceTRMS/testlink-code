{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource attachmentdelete.tpl

@internal revisions
@since 2.0
*}

{include file="inc_head.tpl"}
{lang_get var="adLabels" s='title_delete_attachment,btn_close'}

<body onunload="attachmentDlg_onUnload()" onload="attachmentDlg_onLoad()">

<h1 class="title">{$adLabels.title_delete_attachment}</h1>
<p class='info'>
{$gui->userFeedback}
</p>

<div class="workBack">
  <div class="groupBtn" style="text-align:right">
    <input align="right" type="button" value="{$adLabels.btn_close}" onclick="window.close()" />
  </div>
</div>

</body>
</html>