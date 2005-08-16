{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcMove.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - move/copy test case *}
{* I18N: 20050528 - fm *}

{include file="inc_head.tpl"}

<body>

<h1>{lang_get s="title_mv_cp_tc"} {$title}</h1>

<div class="workBack">
<form method="post" action="lib/testcases/tcEdit.php?data={$data}">
	<div class="groupBtn">
			<input id="submit1" type="submit" name="updateTCmove" value="{lang_get s='btn_mv'}" />
			<input id="submit2" type="submit" name="updateTCcopy" value="{lang_get s='btn_cp'}" />
			<input type="hidden" name="oldCat" value="{$oldCat}" />
		</div>	
	<p>{lang_get s='inst_move'}</p>
	<p>{lang_get s='choose_cat'}
		<select name="moveCopy">
			{html_options options=$arrayCat}
		</select>
	</p>
</form>
</div>

</body>
</html>