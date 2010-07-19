{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: testCasesWithCF.tpl,v 1.8 2010/07/19 21:32:42 erikeloff Exp $

Purpose: For a test plan, list test cases with Custom Fields at Execution

rev:  
 20100719 - eloff - Use tlExtTable
 20100303 - asimon - made table ext js sortable
 
*}

{lang_get var="labels" 
          s='no_uncovered_testcases,testproject_has_no_reqspec,
             testproject_has_no_requirements,no_linked_tc_cf,generated_by_TestLink_on,
             test_case,build,th_owner,date,status'}
{include file="inc_head.tpl" openHead="yes"}
{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
  {assign var=tableID value=$matrix->tableID}
  {if $smarty.foreach.initializer.first}
    {$matrix->renderCommonGlobals()}
    {if $matrix instanceof tlExtTable}
        {include file="inc_ext_js.tpl" bResetEXTCss=1}
        {include file="inc_ext_table.tpl"}
    {/if}
  {/if}
  {$matrix->renderHeadSection()}
{/foreach}
</head>
<body>
<h1 class="title">{$gui->pageTitle|escape}</h1>
<div class="workBack" style="overflow-y: auto;">

 {include file="inc_result_tproject_tplan.tpl" 
          arg_tproject_name=$gui->tproject_name arg_tplan_name=$gui->tplan_name}	



{if $gui->warning_msg == ''}
    {if ($gui->resultSet)}
		{$matrix->renderBodySection()}

      {$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
    {else}
    	<h2>{$labels.no_linked_tc_cf}</h2>
    {/if}
{else}
    {$gui->warning_msg}
{/if}    
</div>
</body>
</html>
