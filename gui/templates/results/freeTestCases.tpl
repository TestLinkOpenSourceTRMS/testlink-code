{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: freeTestCases.tpl,v 1.1 2009/04/14 16:52:28 franciscom Exp $

For a test project, list FREE test cases, i.e. not assigned to a test plan.

rev: 20090414 - franciscom - BUGID 2363
*}

{lang_get var="labels" 
          s='all_testcases_has_testplan,generated_by_TestLink_on'}
{include file="inc_head.tpl" openHead="yes"}
</head>
<body>
<h1 class="title">{$gui->pageTitle|escape}</h1>
<div class="workBack" style="overflow-y: auto;">

 {include file="inc_result_tproject_tplan.tpl" 
          arg_tproject_name=$gui->tproject_name arg_tplan_name=''}	

{if $gui->warning_msg == ''}
    {if $gui->resultSet}
        <table class="simple">
        {foreach from=$gui->resultSet item=tcase}
            {assign var="tcase_id" value=$tcase.id}
           <tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">       
            <td>
        	      {foreach from=$gui->path_info[$tcase_id] item=path_part}
        	          {$path_part|escape} /
        	      {/foreach}
        	  <a href="lib/testcases/archiveData.php?edit=testcase&id={$tcase_id}">
        	  {$gui->tcasePrefix|escape}{$tcase.tc_external_id|escape}:{$tcase.name|escape}</a>
            </td>
        	  </tr>
        {/foreach}
        </table>

      {$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
    {else}
    	<h2>{$labels.all_testcases_has_testplan}</h2>
    {/if}
{else}
    {$gui->warning_msg}
{/if}    
</div>
</body>
</html>