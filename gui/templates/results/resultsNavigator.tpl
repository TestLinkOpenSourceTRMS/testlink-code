{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: resultsNavigator.tpl,v 1.4 2008/05/06 06:26:11 franciscom Exp $ *}
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

<h1 class="title">{lang_get s='title_nav_results'}</h1>

<form method="get" id="resultsNavigator" onSubmit="javascript:return pre_submit();">
<div class="menu_bar">
	<span style="float: right;">{lang_get s='title_report_type'}
	<select name="format" onchange="this.form.submit();">
		    {html_options options=$arrReportTypes selected=$selectedReportType}
	</select></span>

	<span><input type="button" name="print" value="{lang_get s='btn_print'}" 
	onclick="javascript: reportPrint();" style="margin-left:5px;" /></span>
</div>

<div style="margin:3px" >
  <input type="hidden" id="called_by_me" name="called_by_me" value="1">
  <input type="hidden" id="called_url" name="called_url" value="">

  <table>
	<tr><td style="padding-right: 10px">{lang_get s='test_plan'}</td><td>
	<select name="tplan_id" onchange="pre_submit();this.form.submit()">
		{html_options options=$tplans selected=$tplan_id}
	</select><br />
	</td></tr>
	<tr><td style="padding-right: 10px"></td><td>
	</td></tr>
  </table>
	
</div>
</form>

<div style="margin:3px; padding: 15px 0px" >
{* Build href menu *}
{if $do_report.status_ok }
  {section name=Row loop=$arrData}
	<span><img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	  <a href="{$arrData[Row].href}format={$selectedReportType}&amp;tplan_id={$tplan_id}" 
	     target="workframe">{$arrData[Row].name}</a></span><br />
  {/section}
{else}
  {$do_report.msg}
{/if}
</div>



{* 20070925 *}
<script type="text/javascript">
{if $workframe != ''}
	parent.workframe.location='{$workframe}';
{/if}
</script>

</body>
</html>
