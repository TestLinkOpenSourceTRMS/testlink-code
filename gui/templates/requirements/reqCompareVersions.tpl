{*

TestLink Open Source Project - http://testlink.sourceforge.net/ 
 
Purpose: smarty template - compare requirement versions

*}

{include file="inc_head.tpl" openHead='yes'}

{lang_get var="labels"
          s="select_versions,title_compare_versions_req,version,compare,modified,modified_by,
          btn_compare_selected_versions, context, show_all"}

<link rel="stylesheet" type="text/css" href="{$basehref}third_party/diff/diff.css">

{literal}
<script type="text/javascript">
function triggerTextfield(field)
{
if (field.disabled == true) {
    field.disabled = false;
  } else {
    field.disabled = true;
  }
}
</script>
{/literal}

</head>
<body>

{if $gui->compare_selected_versions}

	<h1 class="title">{$labels.title_compare_versions_req}</h1> 
			
	<h2>{$gui->subtitle}</h2>
	
	{foreach item=diff from=$gui->diff_array}
	{assign var="diff" value=$diff}
		
		<div class="workBack" style="width:99%; overflow:auto;">	
		
		<h2>{$diff.heading}</h2>
		
		<fieldset class="x-fieldset x-form-label-left" >
		
		<legend class="legend_container" >{$diff.message}</legend>
		
		{if $diff.count > 0}
			{$diff.diff}
		{/if}
		
		</fieldset>
		</div>
		
	{/foreach}
	</div>	
{else}

	<h1 class="title">{$labels.title_compare_versions_req}</h1> 
	
	<div class="workBack" style="width:97%;">
	
	<p><input type="submit" name="compare_selected_versions" value="{$labels.btn_compare_selected_versions}" /></p>
	
	<form target="_blank" method="post" action="lib/requirements/reqCompareVersions.php" name="req_compare_versions" />
	
	<table border="0" cellspacing="0" cellpadding="2" style="font-size:small;" width="100%">
	
	    <tr style="background-color:blue;font-weight:bold;color:white">
	        <th width="10px" style="font-weight: bold; text-align: center;">{$labels.version}</td>
	        <th width="10px" style="font-weight: bold; text-align: center;">{$labels.compare}</td>
	        <th style="font-weight: bold; text-align: center;">{$labels.modified}</td>
	        <th style="font-weight: bold; text-align: center;">{$labels.modified_by}</td>
	    </tr>
	
	{foreach item=req from=$gui->req_versions}
		{assign var="req" value=$req}
	
	   <tr>
	        <td style="text-align: center;">{$req.version}</td>
	        <td style="text-align: center;"><input type="radio" name="version_left" value="{$req.version}" />
	        	<input type="radio" name="version_right" value="{$req.version}" /></td>
	        {if $req.modification_ts != "0000-00-00 00:00:00"}
	        	<td style="text-align: center;">{$req.modification_ts}</td>
	        	<td style="text-align: center;">{$req.author}</td>
	        {else}
	        	<td style="text-align: center;">{$req.creation_ts}</td>
	        	<td style="text-align: center;">{$req.author}</td>
	        {/if}
	    </tr>
	
	{/foreach}
	
	</table>
	
	<p>{$labels.context} <input type="text" name="context" id="context" maxlength="4" size="4" value="{$gui->context}" />
	<input type="checkbox" id="context_show_all" name="context_show_all" 
	onclick="triggerTextfield(this.form.context);"/> {$labels.show_all} </p>
	
	<p><input type="hidden" name="requirement_id" value="{$gui->req_id}" />
	<input type="submit" name="compare_selected_versions" value="{$labels.btn_compare_selected_versions}" /></p>
	
	</form>

	</div>

{/if}

</body>

</html>
