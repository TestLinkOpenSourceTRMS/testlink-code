{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqCreate.tpl,v 1.8 2007/01/02 13:42:06 franciscom Exp $
Purpose: smarty template - create / edit a req  
*}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{literal}
<script type="text/javascript">
{/literal}
var warning_empty_req_docid = "{lang_get s='warning_empty_reqdoc_id'}";
var warning_empty_req_title = "{lang_get s='warning_empty_req_title'}";
{literal}
function validateForm(f)
{
  if (isWhitespace(f.reqDocId.value)) 
  {
      alert(warning_empty_req_docid);
      selectField(f, 'reqDocId');
      return false;
  }
  
  if (isWhitespace(f.title.value)) 
  {
      alert(warning_empty_req_title);
      selectField(f, 'title');
      return false;
  }
  return true;
}
</script>
{/literal}
</head>


<body onload="document.forms[0].elements[0].focus()">
{config_load file="input_dimensions.conf" section="reqEdit"}

<h1>
	<img title="{lang_get s='help'}: {lang_get s='reqs'}"
	     alt="{lang_get s='help'}: {lang_get s='reqs'}" class="help" 
	     src="icons/sym_question.gif" 
	     onclick="javascript:open_popup('{$helphref}requirementsCoverage.html#req');" />
	{lang_get s='req_spec'}{$smarty.const.TITLE_SEP}{$srs_title|escape} 
</h1>



<div class="workBack">
{* show SQL result *}
{include file="inc_update.tpl" result=$sqlResult item="Requirement" name=$name action=$action}
<h1>{lang_get s='req_create'}</h1>

<form name="formReqCreate" method="post" 
      action="lib/req/reqSpecView.php?idSRS={$arrSpec[0].id}"
      onSubmit="javascript:return validateForm(this);">
  <table class="common" style="width: 90%">
  	<tr>
  		<th>{lang_get s='req_doc_id'}</th>
  		<td><input type="text" name="reqDocId" 
  		           size="{#REQ_DOCID_SIZE#}" maxlength="{#REQ_DOCID_MAXLEN#}" />
  				{include file="error_icon.tpl" field="reqDocId"}
  		</td>
  	</tr>
  	<tr>
  		<th>{lang_get s='title'}</th>
  		<td><input type="text" name="title" 
  		           size="{#REQ_TITLE_SIZE#}" maxlength="{#REQ_TITLE_MAXLEN#}" />
  		    {include file="error_icon.tpl" field="title"}
  		</td>
  	</tr>
  	<tr>
  		<th>{lang_get s='scope'}</th>
  		<td>{$scope}</td>
  	</tr>
  	<tr>
  		<th>{lang_get s='status'}</th>
  		<td><select name="reqStatus">
  			{html_options options=$selectReqStatus selected=$arrReq.status}
  		</select></td>
  	</tr>
  </table>
  
<div class="groupBtn">
<input type="hidden" name="create" value="1" />
<input type="submit" name="createReq" value="{lang_get s='btn_create'}" />
<input type="button" name="cancel" value="{lang_get s='btn_cancel'}" 
	     onclick="javascript: location.href=fRoot+'lib/req/reqSpecView.php?idSRS={$arrSpec[0].id}';" />
</div>
</form>

</div>

</div>

</body>
</html>