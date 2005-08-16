{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcEdit.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - edit test specification: test case *}
{include file="inc_head.tpl"}

<body>

<div class="workBack" style="font-weight: bold;">
<h1>{lang_get s='title_edit_tc'} {$tc[0]|escape}</h1> 

<form method="post" action="lib/testcases/tcEdit.php?data={$data}">

	<div style="float: right;">
		<input id="submit" type="submit" name="updateTC" value="Update" />
		<input type="hidden" name="version" value="{$tc[5]}" />
	</div>	

	<p>{lang_get s='tc_title'}<br />
		<input type="text" name="title" size="40" value="{$tc[1]|escape}"
			alt="{lang_get s='alt_add_tc_name'}"/>
	</p>

	<div>{lang_get s='summary'}<br />
		<textarea id="summary" name="summary" style="width: 99%; height: 150px;">{$tc[2]|escape}</textarea>
	</div>
	
	<div>{lang_get s='steps'}<br />
		<textarea id="scenario" name="steps" class="w99h300">{$tc[3]|escape}</textarea>
	</div>

	<div>{lang_get s='expected_results'}<br />
		<textarea id="exresult" name="exresult" class="w99h300">{$tc[4]|escape}</textarea>
	</div>
	
	<p><a href="lib/keywords/viewKeywords.php" target="mainframe">{lang_get s='tc_keywords'}</a><br />
		<select name="keywords[]" style="width: 30%" size="{$keySize}" multiple="multiple">
		{section name=oneKey loop=$keys}
				{if $keys[oneKey].selected == "yes"}
					<option value="{$keys[oneKey].key|escape}" selected="selected">{$keys[oneKey].key|escape}</option>
			{else}
					<option value="{$keys[oneKey].key|escape}">{$keys[oneKey].key|escape}</option>
			{/if}
		{/section}
		</select>
	</p>
</form>

{include file="inc_htmlArea.tpl"}
<script type="text/javascript" defer="1">
   	HTMLArea.replace('summary', config);
   	HTMLArea.replace('scenario', config);
   	HTMLArea.replace('exresult', config);
   	document.forms[0].title.focus()
</script>

</div>
</body>
</html>