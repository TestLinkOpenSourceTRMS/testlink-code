{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
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
			<h1>Please wait until the report has been fully loaded!</h1>
			<h1 id="progress"></h1>
		</div>	

		<div class="workBack">
{/if}
			<h2>Query Parameters</h2> 
			{$queryParameters}
{if !$xls}		</div>

		<div class="workBack">
{/if}
			<h3>Overall Status</h3>
			<a>Result totals for test cases which match specified "Owner", "Keyword", "Component(s)", and "Builds Selected".  "Last Status" query parameter is NOT considered.</a>
			{$summaryOfResults}
{if !$xls}		</div>

		<div id="detailsOfReport" class="workBack">
			<h3>Test Cases Returned By Query</h3>
                        <a>Individual test cases which match specified "Owner", "Keyword", "Component(s)", "Last Status" and "Builds Selected" query parameters.</a>
			<BR><BR><a href="javascript:showOrCollapseAll()">Show/Hide all</a>
			<h2 onClick="plusMinus_onClick(this);"><img class="plus" src="icons/plus.gif">Results By Component</h2>
			<div class="workBack">
{/if}
				{$allComponentData}
{if !$xls}			</div>
		</div>
	</body>
</html>
{/if}
