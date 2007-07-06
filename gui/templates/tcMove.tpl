{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: tcMove.tpl,v 1.8 2007/07/06 06:28:34 franciscom Exp $ 
Purpose: smarty template - move/copy test case 

20060316 - franciscom - html input names updated
20060305 - franciscom
*}

{include file="inc_head.tpl"}

<body>
<h1>{lang_get s="test_case"}{$smarty.const.TITLE_SEP}{$name|escape}</h1>

<div class="workBack">
<h1>{lang_get s="title_mv_cp_tc"}</h1>

<form method="post" action="lib/testcases/tcEdit.php?testcase_id={$testcase_id}">
  <p>
  {if $move_enabled }
	  {lang_get s='inst_move'}<br>
  {/if}
  {lang_get s='inst_copy'}<br>
  {lang_get s='inst_copy_move_warning'}
  </p>
  	
	<p>{lang_get s='choose_container'}
		<select name="new_container">
			{html_options options=$array_container selected=$old_container}
		</select>
	<p>
		<div class="groupBtn">
		  {if $move_enabled }
			  <input id="do_move" type="submit" name="do_move" value="{lang_get s='btn_mv'}" />
			{/if}  
			<input id="do_copy" type="submit" name="do_copy" value="{lang_get s='btn_cp'}" />
			<input type="hidden" name="old_container" value="{$old_container}" />
	</div>	

</form>
</div>

</body>
</html>