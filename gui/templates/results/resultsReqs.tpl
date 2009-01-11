{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsReqs.tpl,v 1.8 2009/01/11 17:10:56 franciscom Exp $
Purpose: report REQ coverage 
Author : Martin Havlat 

rev: 20090111 - franciscom - BUGID 1967
*}
{lang_get var='labels'
          s='title_result_req_testplan,no_srs_defined,req_spec,req_total_count,req_title_in_tl,testcases,
             req_title_covered,req_title_uncovered,req,req_title_not_in_tl,req_title_nottestable,none'}

{* Configure Actions *}
{assign var="reqViewAction" value="lib/requirements/reqView.php?item=requirement&requirement_id="}

{assign var="canEditTC" value=$gui->allow_edit_tc} 
{assign var="accessTestCaseAction" 
        value="lib/testcases/archiveData.php?edit=testcase&allow_edit=$canEditTC&id="}

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
      	<select name="idSRS" onchange="form.submit()">
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

  {foreach item=key from=$gui->coverageKeys} 
    <div class="workBack">
    {assign var="label_id" value=req_title_$key}
    <h2>{lang_get s=$label_id}</h2>
    {section name=row loop=$gui->coverage.$key}
    {if $smarty.section.row.first}
    <table class="simple">
    	<tr>
    		<th>{$labels.req}</th>
    		{if $key != 'not_run'}
    		  <th>{$labels.testcases}</th>
    		{/if}
    	</tr>
    {/if}
    	<tr>
    		<td><span class="bold"><a href="{$reqViewAction}{$gui->coverage.$key[row].id}">
    			  {$gui->coverage.$key[row].title|escape}</a></span></td>
    		{if $key != 'not_run'}
    		<td>{assign var=tcList value=$gui->coverage.$key[row].tcList}
    			{section name=idx loop=$tcList}
    				<a href="{$accessTestCaseAction}{$tcList[idx].tcID}">{$gui->prefixStr}{$tcList[idx].tcaseExternalID}{$gui->pieceSep}{$tcList[idx].title}</a>{$gui->pieceSep}{lang_get s=$tcList[idx].status_label}<br/>
    			{/section} 
    		</td>
    		{/if}
    	</tr>
    {if $smarty.section.row.last}
    </table>
    {/if}
    {sectionelse}
    	<p class="bold">{$labels.none}</p>
    {/section}
    </div>
  {/foreach}
{/if}

</body>
</html>
