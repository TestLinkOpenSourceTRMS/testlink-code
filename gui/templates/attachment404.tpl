{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource attachment404.tpl

@internal revisions
@since 2.0
*}
{include file="inc_head.tpl"}
{lang_get var="anfLabels" s='title_downloading_attachment,error_attachment_not_found,btn_close'}

<body>
<h1 class="title">{$anfLabels.title_downloading_attachment}</h1>
<p class='info'>{$anfLabels.error_attachment_not_found}</p>

<div class="workBack">
  <div class="groupBtn" style="text-align:right">
	  <input align="right" type="button" value="{$anfLabels.btn_close}" onclick="window.close()" />
  </div>
</div>

</body>
</html>