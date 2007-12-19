{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: resultsReqs.tpl,v 1.3 2007/12/19 20:27:19 schlundus Exp $
Purpose: report REQ coverage 
Author : Martin Havlat 

*}
{include file="inc_head.tpl"}

<body>

<h1>
 {lang_get s='help' var='common_prefix'}
 {lang_get s='req_spec' var="xx_alt"}
 {assign var="text_hint" value="$common_prefix: $xx_alt"}
 {include file="inc_help.tpl" help="requirementsCoverage" locale=$locale 
          alt="$text_hint" title="$text_hint"  style="float: right;"}
 {lang_get s='title_result_req_testplan'} {$arrReqSpec[$selectedReqSpec]|escape}
</h1>

<div class="workBack">
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$tproject_name arg_tplan_name=$tplan_name}	

{if $arrReqSpec == '' }
<br />
  <div class="user_feedback">{lang_get s='no_srs_defined'}</div>
{/if}


{if $arrReqSpec != '' }
  {*
   <div>
   <form method="get">
  	 <select name="idSRS" onchange="form.submit()">
  		{html_options options=$arrReqSpec selected=$selectedReqSpec}
   	</select>
   </form>
   </div>
  *} 
  
  {* METRICS *}
  <form method="get">
  <table class="invisible">

    <tr><td>{lang_get s='req_spec'}
      	<select name="idSRS" onchange="form.submit()">
  		{html_options options=$arrReqSpec selected=$selectedReqSpec}
  	</select></td></tr>
  
    <tr><td>&nbsp;</td></tr>
    <tr><td>{lang_get s='req_total_count'}</td><td>{$arrMetrics.expectedTotal}</td></tr>
    <tr><td>{lang_get s='req_title_in_tl'}</td><td>{$arrMetrics.total}</td></tr>
    <tr><td>{lang_get s='req_title_covered'}</td><td>{$arrMetrics.covered}</td></tr>
    <tr><td>{lang_get s='req_title_uncovered'}</td><td>{$arrMetrics.total-$arrMetrics.covered}</td></tr>
    <tr><td>{lang_get s='req_title_not_in_tl'}</td><td>{$arrMetrics.uncovered}</td></tr>
    <tr><td>{lang_get s='req_title_nottestable'}</td><td>{$arrMetrics.notTestable}</td></tr>
    </table>
  </form>  
</div>
{* --------------------------------------------------------------------------------------------------- *}  
  
  <div class="workBack">
  <h2>{lang_get s='req_title_passed'}</h2>
  
  {section name=row loop=$arrCoverage.passed}
  {if $smarty.section.row.first}
  <table class="simple">
  	<tr>
  		<th>{lang_get s="req"}</th>
  		<th>{lang_get s="testcases"}</th>
  	</tr>
  {/if}
  	<tr>
  		<td><span class="bold"><a href="lib/req/reqSpecView.php?editReq={$arrCoverage.passed[row].id}&idSRS={$selectedReqSpec}">
  			{$arrCoverage.passed[row].title|escape}</a></span></td>
  		<td>{assign var=tcList value=$arrCoverage.passed[row].tcList}
  			{section name=idx loop=$tcList}
  				<a href="lib/testcases/archiveData.php?id={$tcList[idx].tcID|escape}&amp;edit=testcase&allow_edit={$allow_edit_tc}">{$tcList[idx].tcID}</a> {$tcList[idx].title} <br/>
  			{/section} 
  		</td>
  	</tr>
  {if $smarty.section.row.last}
  </table>
  {/if}
  {sectionelse}
  	<p class="bold">{lang_get s='none'}</p>
  {/section}
  </div>
  
  
  
  <div class="workBack">
  <h2>{lang_get s='req_title_failed'}</h2>
  
  {section name=row loop=$arrCoverage.failed}
  {if $smarty.section.row.first}
  <table class="simple">
  	<tr>
  		<th>{lang_get s="req"}</th>
  		<th>{lang_get s="testcases"}</th>
  	</tr>
  {/if}
  	<tr>
  		<td><span class="bold"><a href="lib/req/reqSpecView.php?editReq={$arrCoverage.failed[row].id}&idSRS={$selectedReqSpec}">
  			{$arrCoverage.failed[row].title|escape}</a></span></td>
  		<td>{assign var=tcList value=$arrCoverage.failed[row].tcList}
  			{section name=idx loop=$tcList}
  				<a href="lib/testcases/archiveData.php?id={$tcList[idx].tcID|escape}&amp;edit=testcase&allow_edit={$allow_edit_tc}">{$tcList[idx].tcID}</a> {$tcList[idx].title} <br/>
  			{/section} 
  		</td>
  	</tr>
  {if $smarty.section.row.last}
  </table>
  {/if}
  {sectionelse}
  	<p class="bold">{lang_get s='none'}</p>
  {/section}
  </div>
  
  
  
  <div class="workBack">
  <h2>{lang_get s='req_title_blocked'}</h2>
  
  {section name=row loop=$arrCoverage.blocked}
  {if $smarty.section.row.first}
  <table class="simple">
  	<tr>
  		<th>{lang_get s="req"}</th>
  		<th>{lang_get s="testcases"}</th>
  	</tr>
  {/if}
  	<tr>
  		<td><span class="bold"><a href="lib/req/reqSpecView.php?editReq={$arrCoverage.blocked[row].id}&idSRS={$selectedReqSpec}">
  			{$arrCoverage.blocked[row].title|escape}</a></span></td>
  		<td>{assign var=tcList value=$arrCoverage.blocked[row].tcList}
  			{section name=idx loop=$tcList}
  				<a href="lib/testcases/archiveData.php?id={$tcList[idx].tcID|escape}&amp;edit=testcase&allow_edit={$allow_edit_tc}">{$tcList[idx].tcID}</a> {$tcList[idx].title} <br/>
  			{/section} 
  		</td>
  	</tr>
  {if $smarty.section.row.last}
  </table>
  {/if}
  {sectionelse}
  	<p class="bold">{lang_get s='none'}</p>
  {/section}
  </div>
  
  
  
  <div class="workBack">
  <h2>{lang_get s='req_title_notrun'}</h2>
  
  {section name=row loop=$arrCoverage.not_run}
  {if $smarty.section.row.first}
  <table class="simple">
  	<tr>
  		<th>{lang_get s="req"}</th>
  		<th>{lang_get s="testcases"}</th>
  	</tr>
  {/if}
  	<tr>
  		<td><span class="bold"><a href="lib/req/reqSpecView.php?editReq={$arrCoverage.not_run[row].id}&idSRS={$selectedReqSpec}">
  			{$arrCoverage.not_run[row].title|escape}</a></span></td>
  		<td>{assign var=tcList value=$arrCoverage.not_run[row].tcList}
  			{section name=idx loop=$tcList}
  				<a href="lib/testcases/archiveData.php?id={$tcList[idx].tcID|escape}&amp;edit=testcase&allow_edit={$allow_edit_tc}">{$tcList[idx].tcID}</a> {$tcList[idx].title} <br/>
  			{/section} 
  		</td>
  	</tr>
  {if $smarty.section.row.last}
  </table>
  {/if}
  {sectionelse}
  	<p class="bold">{lang_get s='none'}</p>
  {/section}
  </div>
{/if}

</body>
</html>
