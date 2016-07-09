{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcEdit_m2.tpl,v 1.2 2008/05/06 06:26:13 franciscom Exp $ *}
{* Purpose: smarty template - edit test specification: test case *}
{include file="inc_head.tpl"}

<body>

<div class="workBack" style="font-weight: bold;">
<h1 class="title">{lang_get s='title_edit_tc'} {$tc.id|escape}</h1> 
<form method="post" action="{$basehref}lib/testcases/tcEdit.php?testcaseID={$testcaseID}">

	<div style="float: right;">
		<input id="submit" type="submit" name="updateTC" value="Update" />
		<input type="hidden" name="version" value="{$tc.version}" />
	</div>	

	<p>{lang_get s='tc_title'}<br />
		<input type="text" name="name" size="40" value="{$tc.name|escape}"
			alt="{lang_get s='alt_add_tc_name'}"/>
	</p>

	<div>{lang_get s='summary'}<br />
		{$summary}
	</div>

  <table border=1 width="100%">
  <tr><td>{lang_get s='steps'}</td><td>{lang_get s='expected_results'}</td></tr>
	<tr><td>{$steps}</td><td>{$exresult}</td></tr>
	</table>
	
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

<script type="text/javascript" defer="1">
   	document.forms[0].title.focus()
</script>

</div>
</body>
</html>