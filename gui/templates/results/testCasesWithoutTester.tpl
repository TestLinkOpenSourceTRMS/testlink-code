{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: testCasesWithoutTester.tpl,v 1.1 2008/12/27 18:31:17 franciscom Exp $

Purpose: For a test plan, list test cases that has no tester assigned

rev: 20081227 - franciscom - BUGID 
*}

{lang_get var="labels" 
          s='no_uncovered_testcases,testproject_has_no_reqspec,
             testproject_has_no_requirements,generated_by_TestLink_on'}
{include file="inc_head.tpl" openHead="yes"}
</head>
<body>
<h1 class="title">{$gui->pageTitle|escape}</h1>
<div class="workBack" style="overflow-y: auto;">

 {include file="inc_result_tproject_tplan.tpl" 
          arg_tproject_name=$gui->tproject_name arg_tplan_name=$gui->tplan_name}	

{if $gui->warning_msg == ''}
    {if $gui->resultSet}
        <table class="simple">
        {foreach from=$gui->resultSet item=tcase}
            {assign var="tcase_id" value=$tcase.tc_id}
            {assign var="tcversion_id" value=$tcase.tcversion_id}
           <tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">       
            <td>
        	      {foreach from=$gui->path_info[$tcase_id] item=path_part}
        	          {$path_part|escape} /
        	      {/foreach}
        	  <a href="lib/testcases/archiveData.php?edit=testcase&id={$tcase_id}">
        	  {$gui->tcasePrefix}{$tcase.external_id|escape}:{$tcase.name|escape}</a>
            </td>
        	  </tr>
        {/foreach}
        </table>

      {$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
    {else}
    	<h2>{$labels.no_testcases_without_tester}</h2>
    {/if}
{else}
    {$gui->warning_msg}
{/if}    
</div>
</body>
</html>
