{* TestLink Open Source Project - http://testlink.sourceforge.net/ 

$Author: kevinlevy $
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
	<!-- kl : I'm having a hard time getting the initial report to show up with all collapsable <div>'s to be
	in collapsed position, calling showOrCollapseAll() twice seems to help  -->

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
			<a>{lang_get s='descr_res_totals'}</a>
<!--			<a>Result totals for test cases which match specified "Owner", "Keyword", "Component(s)", and "Builds Selected".  "Last Status" query parameter is NOT considered.</a> -->
			{$summaryOfResults}
		</div>

		<div id="detailsOfReport" class="workBack">
<!--			<h3>Test Cases Returned By Query</h3> -->
			<h3>{lang_get s='case_return_by_query_header'}</h3>
<!--                    <a>Individual test cases which match specified "Owner", "Keyword", "Component(s)", "Last Status" and "Builds Selected" query parameters.</a> -->
			<a>{lang_get s='descr_indiv'}</a>
			<BR><BR><a href="javascript:showOrCollapseAll()">{lang_get s='show_hide_all'}</a>
			<h2 onClick="plusMinus_onClick(this);"><img class="plus" src="icons/plus.gif">{lang_get s='results_by_component_header'}</h2>
			<div class="workBack">

				{$allComponentData}
				</div>
		</div>

{include file="inc_print_button.tpl"}

	</body>
</html>
{/if}

{if $xls}
	MS Excel Report - development in progress 10/18/2005 kl
{/if}
