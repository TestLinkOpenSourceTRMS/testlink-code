{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: tcMove.tpl,v 1.4 2006/03/06 17:30:54 franciscom Exp $ 
Purpose: smarty template - move/copy test case 

20060305 - franciscom
*}

{include file="inc_head.tpl"}

<body>

<h1>{lang_get s="title_mv_cp_tc"} {$title}</h1>

<div class="workBack">
<form method="post" action="lib/testcases/tcEdit.php?testcaseID={$testcaseID}">
	<div class="groupBtn">
			<input id="submit1" type="submit" name="updateTCmove" value="{lang_get s='btn_mv'}" />
			<input id="submit2" type="submit" name="updateTCcopy" value="{lang_get s='btn_cp'}" />
			<input type="hidden" name="old_container" value="{$old_container}" />
		</div>	
	<p>{lang_get s='inst_move'}</p>
	<p>{lang_get s='choose_cat'}
		<select name="new_container">
			{html_options options=$array_container}
		</select>
	</p>
</form>
</div>

</body>
</html>