{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: planUpdateTC.tpl,v 1.3 2005/11/13 19:19:31 schlundus Exp $ *}
{* Purpose: smarty template - update Test Case Suite *}
{* 20051212 - scs - Comp/cat name and tc name weren't escaped
					Un-/CheckAll Button localized
*}
{include file="inc_head.tpl" openHead="yes"}
	<script type="text/javascript" src="gui/javascript/checkboxes.js" language="javascript"></script>
</head>
<body>

<h1>{lang_get s='title_upd_mod_tc'} {$testPlanName}</h1>

{if $resultString ne ""}
<div class="info">
	{$resultString}
</div>
{/if}

<div class="workBack">
{if $changesRequired == "yes"}
	<form name="myform" method="post">
	<div style="margin-bottom: 10px;">
	<span style="float: right;">
		<input type='button' name='CheckAll' value='{lang_get s='btn_check_all'}' onclick="checkAll(document.myform);">
		<input type='button' name='UncheckAll' value='{lang_get s='btn_uncheck_all'}' onclick="uncheckAll(document.myform)";>
	</span>
	<input type='submit' name='updateSelected' value="{lang_get s='btn_upd_ck_tc'}">
	</div>

	<table class="common" width="75%">
 	<tr>                              
    {* 20050806 - fm - BUGID: SF1242462 *}
		<th>{lang_get s='th_component'}/{lang_get s='th_category'}</th>

		<th>{lang_get s='th_id_tc'}</th>        
		<th>{lang_get s='th_status'}</th>       
		<th>{lang_get s='th_spec_version'}</th> 
		<th>{lang_get s='th_suite_version'}</th>

    {* 20050806 - fm - BUGID: SF1242462 *}
		<th>{lang_get s='th_reason'}</th>

		<th>{lang_get s='th_update'}</th>       
	</tr>                             
		{section name=number loop=$arrData}
		<tr>
			<td>{$arrData[number].container|escape}</td>
			<td class="bold"
				onclick="javascript:open_top('{$basehref}lib/testcases/archiveData.php?edit=testcase&data={$arrData[number].specId}');">
				[{$arrData[number].specId}] {$arrData[number].name|escape}</td>
			<td>{$arrData[number].status}</td>
			
			{* 20050806 - fm - added attribute align*}
			<td align="center">{$arrData[number].specVersion}</td>
			<td align="center">{$arrData[number].planVersion}</td>

     {* 20050806 - fm - BUGID: SF1242462 *}
			<td>{$arrData[number].reason}</td>

			<td><input type="checkbox" name="{$arrData[number].planId}" /></td>
		<tr>
		{/section}

	</table>

	</form>
{else}
	<p class="info">{lang_get s='info_all_tc_uptodate'}</p>
{/if}	
</div>

</body>
</html>