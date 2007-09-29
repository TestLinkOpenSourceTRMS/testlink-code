{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsNavigator.tpl,v 1.17 2007/09/29 16:57:43 franciscom Exp $ *}
{* Purpose: smarty template - show Test Results and Metrics *}
{* Rev :
        20070929 - franciscom - 
        20070113 - franciscom - use of smarty config file
*}
{include file="inc_head.tpl" openHead="yes"}

{literal}
<script type="text/javascript">
function reportPrint(){
	parent["workframe"].focus();
	parent["workframe"].print();
}

function pre_submit()
{
 document.getElementById('called_url').value=parent.workframe.location;
 return true;
}
</script>
{/literal}
</head>


</head>
<body>
{assign var="cfg_section" value=$smarty.template|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1>{$title|escape}</h1>

<div class="groupBtn">
	<input type="button" name="print" value="{lang_get s='btn_print'}" 
	onclick="javascript: reportPrint();" style="margin-left:2px;" />
</div>

<p>
{* Build href menu *}
{if $do_report.status_ok }
  <a href="lib/results/{$arrDataB[Row].href}?build={$selectedBuild}&amp;report_type={$selectedReportType|escape}" 
	   target="workframe">{$arrDataB[Row].name}</a><br />

  {section name=Row loop=$arrData}
	  <a href="lib/results/{$arrData[Row].href}{$selectedReportType}&amp;build={$selectedBuild}&amp;tplan_id={$tplan_id}" 
	     target="workframe">{$arrData[Row].name}</a><br />
  {/section}
{else}
  {$do_report.msg}
{/if}
</p>
</div>

<div>
<form method="get" id="resultsNavigator" onSubmit="javascript:return pre_submit();">
  <input type="hidden" id="called_by_me" name="called_by_me" value="1">
  <input type="hidden" id="called_url" name="called_url" value="">

	<table>
	<tr><td>
	  {lang_get s='test_plan'}
	</td></tr>
	<tr>
	  <td>
	  <select name="tplan_id" onchange="pre_submit();this.form.submit()">
		{html_options options=$tplans selected=$tplan_id}
	  </select>
	 </td>
	</tr>
	
	{if $arrBuilds != '' }
	<tr>
	  <td>{lang_get s='title_active_build'}</td>
  </tr>
	<tr>
	  <td><select name="build" onchange="pre_submit();this.form.submit()">
		    {html_options options=$arrBuilds selected=$selectedBuild}
	     </select>
	  </td>
	</tr>
	
	<tr>
	  <td>{lang_get s='title_report_type'}</td>
	</tr>
	<tr>
	  <td><select name="report_type" onchange="this.form.submit();">
		    {html_options options=$arrReportTypes selected=$selectedReportType}
	      </select>
	  </td>
	</tr>
	{/if}
	
	
	<!--
	<tr>
		<td>
		{lang_get s="note_email_sent_t"}
		</td>
	</tr>
	-->
	</table>
</form>
</div>

{* 20070925 *}
<script type="text/javascript">
{if $workframe != ''}
	parent.workframe.location='{$workframe}';
{/if}
</script>

</body>
</html>
