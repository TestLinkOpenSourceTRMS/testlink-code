{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: planTCNavigator.tpl,v 1.32.2.3 2010/12/06 15:49:22 asimon83 Exp $
Scope: show test plan tree for execution

Revisions : 
    20101206 - asimon - BUGID 4077: Trees do not work on Internet Explorer
    20101122 - asimon - BUGID 4042: "Expand/Collapse" Button for Trees
	20101027 - asimon - BUGID 3946: reqirement specification tree size
    20100708 - aismon - BUGDI 3406 - removed functionality and labels from 3049
	20100428 - asimon - BUGID 3301 - removed old filter/settings form/panel and replaced
	                    them with new included template inc_tc_filter_panel.tpl
	20100412 - asimon - BUGID 3379, changed displaying of some filters
	20100302 - asimon - BUGID 3049, added button in filter frame
	20100218 - asimon - BUGID 3049, changed root_href
	20100202 - asimon - BUGID 2455, BUGID 3026, changed filtering 
	                    panel is now ext collapsible panel
	20081223 - franciscom - advanced/simple filters
	20080311 - franciscom - BUGID 1427
 ---------------------------------------------------------------------- *}

{lang_get var="labels" 
          s='btn_update_menu,btn_apply_filter,keyword,keywords_filter_help,title_navigator,
             btn_bulk_update_to_latest_version,
             filter_owner,TestPlan,test_plan,caption_nav_filters,
             build,filter_tcID,filter_on,filter_result,platform, include_unassigned_testcases'}

    {include file="inc_head.tpl" openHead="yes"}
    {include file="inc_ext_js.tpl" bResetEXTCss=1}

	{* includes Ext.ux.CollapsiblePanel *}
	<script type="text/javascript" src='gui/javascript/ext_extensions.js'></script>
	{* BUGID 3301 *}
	{literal}
	<script type="text/javascript">
	// BUGID 4077
		  treeCfg = { tree_div_id:'tree_div',root_name:"",root_id:0,root_href:"",
		              loader:"", enableDD:false, dragDropBackEndUrl:'',children:"" };
		  Ext.onReady(function() {
			Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
	
			// Use a collapsible panel for filter settings
			// and place a help icon in ther header
			var settingsPanel = new Ext.ux.CollapsiblePanel({
				id: 'tl_exec_filter',
				applyTo: 'settings_panel',
				tools: [{
					id: 'help',
					handler: function(event, toolEl, panel) {
						show_help(help_localized_text);
					}
				}]
			});
			var filtersPanel = new Ext.ux.CollapsiblePanel({
				id: 'tl_exec_settings',
				applyTo: 'filter_panel'
			});
		});
	</script>

    <script type="text/javascript">
    // BUGID 4077
    treeCfg = { tree_div_id:'tree_div',root_name:"",root_id:0,root_href:"",
                loader:"", enableDD:false, dragDropBackEndUrl:'',children:"" };
    </script>
    {/literal}
    
    <script type="text/javascript">
	    treeCfg.root_name = '{$gui->ajaxTree->root_node->name}';
	    treeCfg.root_id = {$gui->ajaxTree->root_node->id};
	    // BUGID 3049
	    // treeCfg.root_href = "javascript:PL({$gui->tPlanID})";
	    // BUGID 3406
	    treeCfg.root_href = '{$gui->ajaxTree->root_node->href}';
	    treeCfg.children = {$gui->ajaxTree->children};
	    treeCfg.cookiePrefix = "{$gui->ajaxTree->cookiePrefix}";
    </script>
    
    <script type="text/javascript" src='gui/javascript/execTree.js'>
    </script>

<script type="text/javascript">
{literal}
function pre_submit()
{
	document.getElementById('called_url').value = parent.workframe.location;
	return true;
}

/*
  function: update2latest
  args :
  returns:
*/
function update2latest(id)
{
	var action_url = fRoot+'/'+menuUrl+"?doAction=doBulkUpdateToLatest&level=testplan&id="+id+args;
	parent.workframe.location = action_url;
}

// BUGID 3406
///**
// * open page to unassign all testcases in workframe
// *
// * @param id Testplan ID
// */
//function goToUnassignPage(id)
//{
//	var action_url = fRoot + 'lib/testcases/containerEdit.php?doAction=doUnassignFromPlan&tplan_id=' + id;
//	parent.workframe.location = action_url;
//}

{/literal}
</script>


{* BUGID 3301 - js include file for simpler code, filter refactoring/redesign *}
{include file='inc_filter_panel_js.tpl'}

{* 
 * !!!!! IMPORTANT !!!!!
 * Above included file closes <head> tag and opens <body>, so this is not done here.
 *}

	
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">{$labels.title_navigator} {$labels.TestPlan} {$gui->additional_string|escape}</h1>

{*
{assign var="keywordsFilterDisplayStyle" value=""}
{if $gui->keywordsFilterItemQty == 0}
    {assign var="keywordsFilterDisplayStyle" value="display:none;"}
{/if}
*}

{* BUGID 3301: include file for filter panel *}
{include file='inc_filter_panel.tpl'}

{* BUGID 4042 *}
{include file="inc_tree_control.tpl"}

{* BUGID 4077 *}
<div id="tree_div" style="overflow:auto; height:100%;border:1px solid #c3daf9;"></div>

<script type="text/javascript">
{if $gui->src_workframe != ''}
	parent.workframe.location='{$gui->src_workframe}';
{/if}
</script>

</body>
</html>