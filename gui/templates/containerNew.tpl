{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: containerNew.tpl,v 1.9 2006/08/29 19:41:36 schlundus Exp $
Purpose: smarty template - create containers

20060804 - franciscom - changes to add option transfer
*}
{include file="inc_head.tpl" openHead='yes'}
<script language="JavaScript" src="gui/javascript/OptionTransfer.js" type="text/javascript"></script>
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
<div class="workBack">
<h1>{lang_get s='title_create'}  {lang_get s=$level}</h1>
	
{include file="inc_update.tpl" result=$sqlResult 
                               item=$level action="add" name=$name
                               refresh="yes"}

<form method="post" action="lib/testcases/containerEdit.php?containerID={$containerID}">
	<div style="font-weight: bold;">
		<div style="float: right;">
			<input type="submit" name="add_testsuite" value="{lang_get s='btn_create_comp'}" />
		</div>	
		{include file="inc_testsuite_viewer_rw.tpl"}

	  {* 20060804 - franciscom *}	
		<div><a href="lib/keywords/keywordsView.php" target="mainframe">{lang_get s='tc_keywords'}</a>
  	{include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
    </div>

   </div>
</form>
</div>
</body>
</html>