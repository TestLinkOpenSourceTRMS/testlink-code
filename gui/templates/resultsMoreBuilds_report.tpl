{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{include file="inc_head.tpl"}
<!-- added by Kevin Levy 8/27 -->
<head>
		<title></title>
		<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>
		<script language="JavaScript">
	var bAllShown = false;
	var g_progress = null;
	var g_pCount = 0;
	progress();
	</script>
		<style></style>
	</head>
	<!-- kl : I'm having a hard time getting the initial report to show up with all collapsable <div>'s to be
	in collapsed position, calling showOrCollapseAll() twice seems to help  -->
	<body onLoad="onLoad();showOrCollapseAll();showOrCollapseAll()">
		<div id="teaser">
			<h1>Please wait until the report has been fully loaded!</h1>
			<h1 id="progress"></h1>
		</div>	

		<div class="workBack">
			Query Parameters Used To Create This Report : <BR> 
			{$queryParameters}
		</div>

		<div class="workBack">
			Overall Results For This Test Plan : <BR>
			{$summaryOfResults}
		</div>

		<div id="detailsOfReport" class="workBack">
			<a href="javascript:showOrCollapseAll()">Show/Hide all</a>
			<h2 onClick="plusMinus_onClick(this);"><img class="plus" src="http://qa/testlink/icons/plus.gif">Results By Component</h2>
			<div class="workBack">
				{$allComponentData}
			</div>
		</div>
	</body>
</html>
