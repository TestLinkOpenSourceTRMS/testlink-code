{* TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: resultsMoreBuilds_report.tpl,v 1.12 2005/12/05 01:46:52 havlat Exp $
Purpose: smarty template - show Test Results and Metrics
Revisions:
20051126 - scs - removed a-tags around indiv. desc.
20051204 - mht - removed obsolete print button
*}
{include file="inc_head.tpl" openHead='yes'}
<!-- added by Kevin Levy 8/27 -->

{if !$xls}
		<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>
		<script language="JavaScript">
		var bAllShown = false;
		var g_progress = null;
		var g_pCount = 0;
		progress();
		</script>
</head>
	<body onLoad="onLoad();showOrCollapseAll();showOrCollapseAll()">

		<div id="teaser">
			<h1>{lang_get s='please_wait'}</h1>
			<h1 id="progress"></h1>
		</div>	

		<div class="workBack">
			<h2>{lang_get s='query_parameters_header'}</h2> 
			{$queryParameters}
		</div>

		<div class="workBack">

			<h3>{lang_get s='overall_status'}</h3>
			{lang_get s='descr_res_totals'}
			{$summaryOfResults}
		</div>

		<div id="detailsOfReport" class="workBack">
			<h3>{lang_get s='case_return_by_query_header'}</h3>
			{lang_get s='descr_indiv'}
			<BR><BR><a href="javascript:showOrCollapseAll()">{lang_get s='show_hide_all'}</a>
			<h2 onClick="plusMinus_onClick(this);"><img class="plus" src="icons/plus.gif">{lang_get s='results_by_component_header'}</h2>
			<div class="workBack">

				{$allComponentData}
				</div>
		</div>
	</body>
</html>
{/if}

{if $xls}
	MS Excel Report - development in progress 10/18/2005 kl
{/if}
