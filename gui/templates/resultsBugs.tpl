{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsBugs.tpl,v 1.8 2006/10/14 21:14:31 schlundus Exp $
Purpose: smarty template - show Bugs Report 

20051004 - fm - added print button
20051121 - scs - added escaping of tpname
20051126 - scs - added escaping of items
20051201 - scs - removed escaping bug link
20051204 - mht - removed obsolete print button
*}
{include file="inc_head.tpl"}

<body>

<h1>{$tpName|escape} {lang_get s='title_bugs_report'}</h1>
<div class="workBack">
<table class="simple" style="width: 100%; text-align: left; margin-left: 0px;">
	<tr>
		<th>{lang_get s='th_test_suite'}</th> 
		<th>{lang_get s='th_tc_title'}</th>      
		<th>{lang_get s='th_execution_ts'}</th>       
		<th>{lang_get s='th_bugs'}</th>       
	</tr>
	{section name=Row loop=$arrData}
	<tr>
			<td class="bold" colspan="4">
				{$arrData[Row].name}
			</td>
	</tr>
	{assign var=tcInfo value=$arrData[Row].tcInfo}
	{foreach key=tcID item=tc from=$tcInfo}
	<tr>
		<td colspan="4"><hr/></td>
	</tr>
	<tr>
		<td>&nbsp;</td><td class="italic" >{$tc.tcName}</td>
	</tr>	
		{assign var=execInfo value=$tc.executions}
		{foreach key=ts item=exec from=$execInfo}
		<tr>
			<td colspan="2">&nbsp;</td><td>{$ts}</td>
			<td>
				{foreach key=k item=bug from=$exec}
					{$bug}<br />
				{/foreach}
			</td>
		</tr>		
		{/foreach}
	{/foreach}
	<tr>
		<td colspan="4"><hr/></td>
	</tr>
	{/section}
</table>
</div>
</body>
</html>