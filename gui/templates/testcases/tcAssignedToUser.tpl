{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcAssignedToUser.tpl,v 1.11 2010/07/22 16:32:07 asimon83 Exp $
Purpose: smarty template - view test case in test specification
rev:
20100722 - asimon - BUGID 3406 - added columns for build ID and testsuite
20100708 - Julian - BUGID 3591 - Column priority added
20100708 - franciscom - BUGID 3575
20100326 - amitkhullar - BUGID 3345
20080322 - franciscom - php errors clean up
*}

{include file="inc_head.tpl" openHead='yes' enableTableSorting="yes"}
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>

{include file="inc_ext_js.tpl" css_only=1}

</head>

{assign var=this_template_dir value=$smarty.template|dirname}
{lang_get var='labels' 
          s='no_records_found,testplan,testcase,version,assigned_on,due_since,platform,goto_testspec,priority,
             high_priority,medium_priority,low_priority,build,testsuite'}

<body>
<h1 class="title">{$gui->pageTitle}</h1>
<div class="workBack">
{if $gui->warning_msg == ''}
    {if $gui->resultSet}
        {foreach from=$gui->resultSet key=tplan_id item=tcaseSet}
           <h1 align="left">{$labels.testplan}:&nbsp;{$gui->tplanNames[$tplan_id].name|escape}</h1>
            <table class="simple sortable">
            <th align="left">{$sortHintIcon}{$labels.testsuite}</th>
            <th align="left">{$sortHintIcon}{$labels.testcase}</th>
            <th>{$sortHintIcon}{$labels.build}</th>
            <th>{$sortHintIcon}{$labels.platform}</th>
            {if $session['testprojectOptions']->testPriorityEnabled}
            	<th>{$sortHintIcon}{$labels.priority}</th>
            {/if}
            <th>{$sortHintIcon}{$labels.assigned_on}</th>
            <th>{$sortHintIcon}{$labels.due_since}</th>
            {foreach from=$tcaseSet item=tcasePlatform}
              {foreach from=$tcasePlatform item=tcase}
                {assign var="tcase_id" value=$tcase.testcase_id}
                {assign var="tcversion_id" value=$tcase.tcversion_id}
               {*
               BUGID 3575: removed alternating coloring -> no same coloring of follow up lines after sorting
               <tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">  
               *}    
               <tr bgcolor="#eeeeee">   
                <td>
                  {$tcase.tcase_full_path|escape}
                </td>
                <td>
            	  <a href="lib/testcases/archiveData.php?edit=testcase&id={$tcase_id}" title="{$labels.goto_testspec}">
            	  {$tcase.prefix|escape}{$gui->glueChar}{$tcase.tc_external_id|escape}:
            	  {$tcase.name|escape}&nbsp({$labels.version}:{$tcase.version})</a>
                </td>
                <td>
                {$tcase.build_name|escape}
                </td>
                <td>
                {$tcase.platform_name|escape}
                </td>
                {if $session['testprojectOptions']->testPriorityEnabled}
                <td>
                	{if $tcase.priority >= $tlCfg->urgencyImportance->threshold.high}
                		{$labels.high_priority}
                		{elseif $tcase.priority <= $tlCfg->urgencyImportance->threshold.low}
                				{$labels.low_priority}
                		{else}
                			{$labels.medium_priority}
               		{/if}
                </td>
                {/if}
                <td >
            	  {localize_timestamp ts=$tcase.creation_ts}
                </td>
                <td>
                 {date_diff date1=$tcase.creation_ts date2=$smarty.now interval="days"}
                </td>
            	  </tr>
              {/foreach}

            {/foreach}
            </table>
            <br>
        {/foreach}
    {else}
        	{$labels.no_records_found}
    {/if}
{else}
    {$gui->warning_msg}
{/if}   
</div>
</body>
</html>
