{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcAssignedToUser.tpl,v 1.15 2010/09/20 14:18:51 mx-julian Exp $
Purpose: smarty template - view test case in test specification
rev:
20100825 - eloff - remove redundant headers
20100731 - asimon - replaced table (changed to ExtJS format) and included some more data
20100722 - asimon - BUGID 3406 - added columns for build ID and testsuite
20100708 - Julian - BUGID 3591 - Column priority added
20100708 - franciscom - BUGID 3575
20100326 - amitkhullar - BUGID 3345
20080322 - franciscom - php errors clean up
*}

{include file="inc_head.tpl" openHead='yes' enableTableSorting="yes"}
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>

{include file="inc_ext_js.tpl"}

{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
	{assign var=tableID value=table_$idx}
	{if $smarty.foreach.initializer.first}
		{$matrix->renderCommonGlobals()}
		{include file="inc_ext_table.tpl"}
	{/if}
	{$matrix->renderHeadSection($tableID)}
{/foreach}

</head>

{assign var=this_template_dir value=$smarty.template|dirname}
{lang_get var='labels' 
          s='no_records_found,testplan,testcase,version,assigned_on,due_since,platform,goto_testspec,priority,
             high_priority,medium_priority,low_priority,build,testsuite,generated_by_TestLink_on'}

<body>
<h1 class="title">{$gui->pageTitle}</h1>
<div class="workBack">
{if $gui->warning_msg == ''}

	{if $gui->resultSet}

		{foreach from=$gui->tableSet key=idx item=matrix}
		
			<p>
			{assign var=tableID value=table_$idx}
			{$matrix->renderBodySection($tableID)}
			</p>
		
		{/foreach}
		
		<br />
		{$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
    {else}
        	{$labels.no_records_found}
    {/if}
{else}
    {$gui->warning_msg}
{/if}   
</div>
</body>
</html>
