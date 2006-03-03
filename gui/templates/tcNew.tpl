{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcNew.tpl,v 1.9 2006/03/03 16:20:58 franciscom Exp $ *}
{* Purpose: smarty template - create new testcase *}
{* 20050831 - scs - change item to TestCase *}
{* 
20050829 - fm
data -> categoryID 
fckeditor
20050825 - scs - changed item to testcase 
20060106 - scs - fix bug 9
*}

{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_new_tc'}</h1>

{include file="inc_update.tpl" result=$sqlResult item="TestCase" name=$name}

<div class="workBack">

<form method="post" action="lib/testcases/tcEdit.php?containerID={$containerID}">

	<div style="float: right;">
			<input id="submit" type="submit" name="addTC" value="{lang_get s='btn_create'}" />
	</div>	

	<p>{lang_get s='tc_title'}<br />
	<input type="text" name="name" size="50" value=""
			alt="{lang_get s='alt_add_tc_name'}"/></p>
	
	<div style="width: 95%;">
	<div>{lang_get s='summary'}<br />
	{$summary}</div>
	<div>{lang_get s='steps'}<br />
	{$steps}
	<div>{lang_get s='expected_results'}<br />
	{$expected_results}</div>
	</div>

	<p><a href="lib/keywords/keywordsView.php" target="mainframe">{lang_get s='tc_keywords'}</a><br />
		<select name="keywords[]" style="width: 30%" multiple="multiple">
			{section name=oneKey loop=$keys}
				<option value="{$keys[oneKey]|escape}">{$keys[oneKey]|escape}</option>
			{/section}
		</select>
	</p>
	
</form>

</div>

{include file="inc_refreshTree.tpl"}

</body>
</html>