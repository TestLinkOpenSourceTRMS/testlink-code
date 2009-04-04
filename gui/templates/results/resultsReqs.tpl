{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsReqs.tpl,v 1.14 2009/04/04 18:05:16 schlundus Exp $
Purpose: report REQ coverage 
Author : Martin Havlat 

rev: 20090305 - franciscom - added test case path on displayy
     20090114 - franciscom - BUGID 1977
     20090111 - franciscom - BUGID 1967 + Refactoring
*}
{lang_get var='labels'
          s='title_result_req_testplan,no_srs_defined,req_spec,req_total_count,req_title_in_tl,testcase,
             req_without_tcase,
             req_title_covered,req_title_uncovered,req,req_title_not_in_tl,req_title_nottestable,none'}

{* Configure Actions *}
{assign var="reqViewAction" value="lib/requirements/reqView.php?item=requirement&requirement_id="}

{assign var="canEditTC" value=$gui->allow_edit_tc} 
{assign var="accessTestCaseAction" 
        value="lib/testcases/archiveData.php?show_path=1&edit=testcase&allow_edit=$canEditTC&id="}

{include file="inc_head.tpl"}
<body>
<h1 class="title">
 	{$labels.title_result_req_testplan} {$gui->reqSpecName|escape}
</h1>

<div class="workBack">
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$gui->tproject_name arg_tplan_name=$gui->tplan_name}	

{if $gui->reqSpecSet == '' }
<br />
  <div class="user_feedback">{$labels.no_srs_defined}</div>
{/if}


{if $gui->reqSpecSet != '' }
  <form method="get">
  <table class="invisible">
    <tr><td>{$labels.req_spec}
      	<select name="req_spec_id" onchange="form.submit()">
  		{html_options options=$gui->reqSpecSet selected=$gui->req_spec_id}
  	</select></td></tr>
  
    <tr><td>&nbsp;</td></tr>
    <tr><td>{$labels.req_total_count}</td><td>{$gui->metrics.expectedTotal}</td></tr>
    <tr><td>{$labels.req_title_in_tl}</td><td>{$gui->metrics.total}</td></tr>
    <tr><td>{$labels.req_title_covered}</td><td>{$gui->metrics.covered}</td></tr>
    <tr><td>{$labels.req_title_uncovered}</td><td>{$gui->metrics.total-$gui->metrics.covered}</td></tr>
    <tr><td>{$labels.req_title_not_in_tl}</td><td>{$gui->metrics.uncovered}</td></tr>
    <tr><td>{$labels.req_title_nottestable}</td><td>{$gui->metrics.notTestable}</td></tr>
    </table>
  </form>  
</div>
{* --------------------------------------------------------------------------------------------------- *}  

  {* ------------------------------------------------------------------------------------------------- *}  
  {* Display by Coverage Status *}
  {foreach item=key from=$gui->coverageKeys} 
    <div class="workBack">
    {assign var="label_id" value=req_title_$key}
    <h2>{lang_get s=$label_id}</h2>
    {section name=row loop=$gui->coverage.$key}
    {if $smarty.section.row.first}
    <table class="simple">
    	<tr>
    		<th>{$labels.req}</th>
    		<th>{$labels.testcase}</th>
    	</tr>
    {/if}
    	<tr>
    		<td><span class="bold"><a href="{$reqViewAction}{$gui->coverage.$key[row].id}">
    			  {$gui->coverage.$key[row].title|escape}</a></span></td>
    		<td>{assign var=tcList value=$gui->coverage.$key[row].tcList}
    			{section name=idx loop=$tcList}
    				<a href="{$accessTestCaseAction}{$tcList[idx].tcID}">{$tcList[idx].tcase_path|escape}{$gui->prefixStr|escape}{$tcList[idx].tcaseExternalID|escape}{$gui->pieceSep}{$tcList[idx].title|escape}</a>{$gui->pieceSep}{lang_get s=$tcList[idx].status_label}<br/>
    			{/section} 
    		</td>
    	</tr>
    {if $smarty.section.row.last}
    </table>
    {/if}
    {sectionelse}
    	<p class="bold">{$labels.none}</p>
    {/section}
    </div>
  {/foreach}
  {* ------------------------------------------------------------------------------------------------- *}  

  {* ------------------------------------------------------------------------------------------------- *}  
  {* Requierements without Test Cases *}
   <div class="workBack">
    <h2>{$labels.req_without_tcase}</h2>
    {if $gui->withoutTestCase != ''}
       <table class="simple">
       	<tr>
       		<th>{$labels.req}</th>
       	</tr>
         {foreach item=reqnotest from=$gui->withoutTestCase}
         	<tr>
         		<td><span class="bold"><a href="{$reqViewAction}{$reqnotest.id}">
         			  {$reqnotest.title|escape}</a></span></td>
         	</tr>
         {/foreach}
       </table>
    {else}
    	<p class="bold">{$labels.none}</p>
    {/if}
    </div>
  {* ------------------------------------------------------------------------------------------------- *}  
{/if}

</body>
</html>
