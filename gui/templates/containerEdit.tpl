{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: containerEdit.tpl,v 1.11 2006/08/07 09:42:33 franciscom Exp $
Purpose: smarty template - edit test specification: containers 

20060805 - franciscom - changes to add option transfer
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

<h1>{lang_get s='title_edit_level'} {$level}</h1> 
{if $level == 'testsuite'}
	<form method="post" action="lib/testcases/containerEdit.php?testsuiteID={$containerID}" /> 
		<div style="float: right;">
			<input type="submit" name="update_testsuite" value="{lang_get s='btn_update_cat'}" />
		</div>
   {include file="inc_testsuite_viewer_rw.tpl"}

	  {* 20060805 - franciscom *}	
		<div><a href="lib/keywords/keywordsView.php" target="mainframe">{lang_get s='tc_keywords'}</a>
  	{include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
    </div>

	</form>

{elseif $level == "component"}
	<form method="post" action="lib/testcases/containerEdit.php?componentID={$containerID}" /> 
		<div style="float: right;">
			<input type="submit" name="updateCOM" value="Update" />
		</div>

   {include file="inc_comp_viewer_rw.tpl"}
	</form>

{/if}

</div>

</body>
</html>