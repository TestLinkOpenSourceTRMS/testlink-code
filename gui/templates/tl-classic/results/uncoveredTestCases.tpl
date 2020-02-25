{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: uncoveredTestCases.tpl,v 1.4 2010/05/02 09:38:10 franciscom Exp $

Purpose: For a test project, list test cases that has no requirement assigned

rev: 20081109 - franciscom - BUGID 512
*}

{lang_get var="labels" 
          s='no_uncovered_testcases,testproject_has_no_reqspec,
             testproject_has_no_requirements,generated_by_TestLink_on'}
{include file="inc_head.tpl" openHead="yes"}
</head>
<body>
<h1 class="title">{$gui->pageTitle|escape}</h1>
<div class="workBack" style="overflow-y: auto;">

{assign var=doit value=1}
{if !$gui->has_reqspec}
	<h2>{$labels.testproject_has_no_reqspec}</h2>
  {assign var=doit value=0}
{/if}

{if $doit && !$gui->has_requirements}
	<h2>{$labels.testproject_has_no_requirements}</h2>
  {assign var=doit value=0}
{/if}

{if $doit}
    {if $gui->has_tc}
    {include file="inc_result_tproject_tplan.tpl" arg_tproject_name=$gui->tproject_name arg_tplan_name=''}	
    	{foreach from=$gui->items item=ts}
    		<div style="margin:0px 0px 0px {$ts.level}0px;">
        	<h3 class="testlink" style="padding:0px; margin:0px">{$ts.testsuite.name|escape}</h3> 
         {if $ts.testcase_qty gt 0}
            <table border="0" cellspacing="0" style="font-size:small;" width="100%">
            {foreach from=$ts.testcases item=tcase}
                {assign var='tcID' value=$tcase.id}
                <tr>
       			      <td>
        				    {$gui->testCasePrefix|escape}{$tcase.external_id|escape} &nbsp;
         				     <a href="javascript:openTCaseWindow({$tcID})">{$tcase.name|escape}</a>
       			      </td>
                </tr>
           {/foreach}
           </table>
           <br /> 
         {/if}  {* there are test cases to show ??? *}
        </div>
    	{/foreach}
      {$labels.generated_by_TestLink_on} {$smarty.now|date_format:$gsmarty_timestamp_format}
    {else}
    	<h2>{$labels.no_uncovered_testcases}</h2>
    {/if}
{/if}    
</div>
</body>
</html>
