{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsBugs.tpl,v 1.6 2005/12/01 18:19:32 schlundus Exp $
Purpose: smarty template - show Bugs Report 

20051004 - fm - added print button
20051121 - scs - added escaping of tpname
20051126 - scs - added escaping of items
20051201 - scs - removed escaping bug link
*}
{include file="inc_head.tpl"}

<body>

<h1>{$tpName|escape} {lang_get s='title_bugs_report'}</h1>
<div class="workBack">

<table class="simple" style="width: 100%; text-align: center; margin-left: 0px;">
	<tr>
		<th>{lang_get s='th_test_suite'}</th> 
		<th>{lang_get s='th_title'}</th>      
		<th>{lang_get s='th_bugs'}</th>       
	</tr>
	{section name=Row loop=$arrData}
	<tr>
		{section name=Item loop=$arrData[Row]}
			<td>
				{if $smarty.section.Item.index == 2}
					{$arrData[Row][Item]}
				{else}
					{$arrData[Row][Item]|escape}
				{/if}
			</td>
		{/section}
	</tr>
	 {/section}
</table>
</div>

{include file="inc_print_button.tpl"}


</body>
</html>