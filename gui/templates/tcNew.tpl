{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcNew.tpl,v 1.13 2006/06/30 18:41:25 schlundus Exp $ *}
{* Purpose: smarty template - create new testcase *}
{* 20050831 - scs - change item to TestCase *}
{* 
20050829 - fm
data -> categoryID 
fckeditor
20050825 - scs - changed item to testcase 
20060106 - scs - fix bug 9
20060425 - franciscom - added new interface for keywords

*}

{include file="inc_head.tpl" openHead='yes'}
<script language="JavaScript" src="gui/javascript/OptionTransfer.js" type="text/javascript"></script>
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>

<script language="JavaScript" type="text/javascript">
var {$opt_cfg->js_ot_name} = new OptionTransfer("{$opt_cfg->from->name}","{$opt_cfg->to->name}");
{$opt_cfg->js_ot_name}.saveRemovedLeftOptions("{$opt_cfg->js_ot_name}_removedLeft");
{$opt_cfg->js_ot_name}.saveRemovedRightOptions("{$opt_cfg->js_ot_name}_removedRight");
{$opt_cfg->js_ot_name}.saveAddedLeftOptions("{$opt_cfg->js_ot_name}_addedLeft");
{$opt_cfg->js_ot_name}.saveAddedRightOptions("{$opt_cfg->js_ot_name}_addedRight");
{$opt_cfg->js_ot_name}.saveNewLeftOptions("{$opt_cfg->js_ot_name}_newLeft");
{$opt_cfg->js_ot_name}.saveNewRightOptions("{$opt_cfg->js_ot_name}_newRight");
</script>
</head>

<body onLoad="{$opt_cfg->js_ot_name}.init(document.forms[0])">


<h1>{lang_get s='title_new_tc'}</h1>

{include file="inc_update.tpl" result=$sqlResult item="TestCase" name=$name}

<div class="workBack">

<form method="post" action="lib/testcases/tcEdit.php?containerID={$containerID}">

	<div style="float: right;">
			<input id="do_create" type="submit" name="do_create" value="{lang_get s='btn_create'}" />
	</div>	

	<p>{lang_get s='tc_title'}<br />
	<input type="text" name="name" size="50" value=""
			alt="{lang_get s='alt_add_tc_name'}"/></p>
	
	<div>{lang_get s='summary'}<br />
	{$summary}</div>
	<div>{lang_get s='steps'}<br />
	{$steps}
	<div>{lang_get s='expected_results'}<br />
	{$expected_results}</div>
	</div>

	<div><a href="lib/keywords/keywordsView.php" target="mainframe">{lang_get s='tc_keywords'}</a>
	{include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
	</div>
	
</form>

</div>

{if $sqlResult eq 'ok'}
	{include file="inc_refreshTree.tpl"}
{/if}

</body>
</html>