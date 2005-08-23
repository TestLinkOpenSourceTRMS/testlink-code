{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: containerEdit.tpl,v 1.3 2005/08/23 20:25:55 schlundus Exp $ *}
{* Purpose: smarty template - edit test specification: containers *}
{* Note: htmlarea cannot be within tag <p> *}
{*
	20050823 - am - localized title
			lang_get('component');
			lang_get('category');
*}
{include file="inc_head.tpl"}

<body>
<div class="workBack">

<h1>{lang_get s='title_edit_level'} {$level}</h1> 

{if $level == 'category'}

	<form method="post" action="lib/testcases/containerEdit.php?data={$data[0]}" /> 
		<div style="float: right;">
			<input type="submit" name="updateCat" value="{lang_get s='btn_update_cat'}" />
		</div>
		<p>{lang_get s='cat_name'}<br />
		<input type="text" name="name" size="50" 
			alt="{lang_get s='cat_alt_name'}"
			value="{$data[1]|escape}" />
		</p>
		
		<div>{lang_get s='cat_scope'}<br />
		<textarea id="scope" name="objective" class="w99h300" alt="{lang_get s='cat_alt_scope'}">
		{$data[2]|escape|nl2br}</textarea>
		</div>
		
		<p>{lang_get s='cat_config'}<br />
			<textarea name="config" style="width: 99%; height: 100px;">{$data[3]|escape}</textarea>
		</p>
		<p>{lang_get s='cat_data'}<br />
			<textarea name="data" style="width: 99%; height: 100px;">{$data[4]|escape}</textarea>
		</p>
		<p>{lang_get s='cat_tools'}<br />
			<textarea name="tools" style="width: 99%; height: 100px;">{$data[5]|escape}</textarea>
		</p>
	</form>

{elseif $level == "component"}

	<form method="post" action="lib/testcases/containerEdit.php?data={$data[0]}" /> 
		<div style="float: right;">
			<input type="submit" name="updateCOM" value="Update" />
		</div>

		<p>{lang_get s='comp_name'}<br />
			<input type="text" name="name" alt="{lang_get s='comp_alt_name'}"
			value="{$data[1]|escape}" size="50" /></p>
		<p>{lang_get s='comp_intro'}<br />
			<textarea name="intro" style="width: 99%; height: 80px;">{$data[2]|escape}</textarea>
		</p>
		
		<div style="margin: 3px;">{lang_get s='comp_scope'}<br />
			<textarea id="scope" name="scope" class="w99h300"
				alt="{lang_get s='comp_alt_scope'}">{$data[3]|escape}
			</textarea>
		</div>
		<p>{lang_get s='comp_ref'}<br />
			<textarea name="ref" class="w99h100">{$data[4]|escape}</textarea>
		</p>
		<p>{lang_get s='comp_method'}<br />
			<textarea name="method" class="w99h100">{$data[5]|escape}</textarea>
		</p>
		<p>{lang_get s='comp_lim'}<br />
			<textarea name="lim" class="w99h100">{$data[6]|escape}</textarea>
		</p>
	</form>

{/if}

</div>

{include file="inc_htmlArea.tpl"}
<script type="text/javascript" defer="1">
   	HTMLArea.replace("scope", config);
   	document.forms[0].name.focus()
</script>

</body>
</html>