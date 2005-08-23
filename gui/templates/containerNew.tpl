{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: containerNew.tpl,v 1.3 2005/08/23 20:25:55 schlundus Exp $ *}
{* Purpose: smarty template - create containers *}
{* I18N: 20050528 - fm *}
{*
	20050821 - am - changed p-tags to div-tags because there arent alloed for htmlarea

	lang_get('component');
	lang_get('category');
*}
{include file="inc_head.tpl"}

<body>
<div class="workBack">

<h1>{lang_get s='title_create'} {$level|escape}</h1>
	
{include file="inc_update.tpl" result=$sqlResult item=$level action="add" name=$name
	refresh="yes"}
	
{if $level eq "category"}
	<form method="post" action="lib/testcases/containerEdit.php?data={$data}">
		<div class="bold">
			<div style="float: right;">
				<input type="submit" name="addCAT" value="{lang_get s='btn_create_cat'}" />
			</div>	
			<p>{lang_get s='cat_name'}<br />
				<input type="text" name="name" size="50" 
				alt="{lang_get s='cat_alt_name'}"></textarea>
			</p>
			<div>{lang_get s='cat_scope'}<br />
				<textarea id="scope" name="objective" class="w99h300"
				alt="{lang_get s='cat_alt_scope'}"></textarea>
			</div>
			<p>{lang_get s='cat_config'}<br />
				<textarea name="config" class="w99h300"></textarea>
			</p>
			<p>{lang_get s='cat_data'}<br />
				<textarea name="testdata" class="w99h300"></textarea>
			</p>
			<p>{lang_get s='cat_tools'}<br />
				<textarea name="tools" class="w99h300"></textarea>
			</p>
		</div>
	</form>

{elseif $level == 'component'}
	
	<form method="post" action="lib/testcases/containerEdit.php?component={$data}">
		<div style="font-weight: bold;">
			<div style="float: right;">
				<input type="submit" name="addCOM" value="{lang_get s='btn_create_comp'}" />
			</div>	
			<p>{lang_get s='comp_name'}<br />
				<input type="text" name="name" size="50" 
				alt="{lang_get s='comp_alt_name'}"/></p>
			<p>{lang_get s='comp_intro'}<br />
				<textarea name="intro" style="width: 99%; height: 80px;"></textarea></p>
			<div>{lang_get s='comp_scope'}<br />
				<textarea id="scope" name="scope" class="w99h300"
				alt="{lang_get s='comp_alt_scope'}"></textarea>
			</div>
			<p>{lang_get s='comp_ref'}<br />
				<textarea name="ref" class="w99h100"></textarea>
			</p>
			<p>{lang_get s='comp_method'}<br />
				<textarea name="method" class="w99h100"></textarea>
			</p>
			<p>{lang_get s='comp_lim'}<br />
				<textarea name="lim" class="w99h100"></textarea>
			</p>
		</div>
	</form>
	
{/if}	

</div>

{include file="inc_htmlArea.tpl"}
<script type="text/javascript" defer="1">
   	HTMLArea.replace('scope', config);
   	document.forms[0].name.focus()
</script>

</body>
</html>