{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	execNavigator.tpl

@internal revisions
@since 1.9.4
20111031 - franciscom - TICKET 4788: Test Case Execution - 
						Give feedback when filter combination 'Result' + 'on' can not be used

@since 1.9.3
  20101206 - asimon - BUGID 4077: Trees do not work on Internet Explorer
  20101122 - asimon - BUGID 4042: "Expand/Collapse" Button for Trees
  20101101 - franciscom - openExportTestPlan() interface changes
  20101027 - asimon - BUGID 3946: reqirement specification tree size
  20101007 - franciscom - BUGID 3270 - Export Test Plan in XML Format
*}

{lang_get var="labels"
          s="filter_result,caption_nav_filter_settings,filter_owner,test_plan,filter_on,
             platform,exec_build,btn_apply_filter,build,keyword,filter_tcID,execution_type,
             include_unassigned_testcases,priority,caption_nav_filters,caption_nav_settings,
             block_filter_not_run_latest_exec"}       

{* ===================================================================== *}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_ext_js.tpl" bResetEXTCss=1}

{* includes Ext.ux.CollapsiblePanel *}
<script type="text/javascript" src='gui/javascript/ext_extensions.js'></script>

<script type="text/javascript">
var msg_block_filter_not_run_latest_exec = '{$labels.block_filter_not_run_latest_exec}';
var code_lastest_exec_method = {$gui->lastest_exec_method};
var code_not_run = '{$gui->not_run}';
</script>

{literal}
<script type="text/javascript">
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

/**
 * 
 *
 * internal revisions
 */
function openExportTestPlan(windows_title,tproject_id,tplan_id,platform_id,build_id) 
{
  args = "tproject_id=" + tproject_id + "&tplan_id=" + tplan_id + "&platform_id=" + platform_id + "&build_id=" + build_id;  
  args = args + "&exportContent=tree";
  wref = window.open(fRoot+"lib/plan/planExport.php?"+args,
	                 windows_title,"menubar=no,width=650,height=500,toolbar=no,scrollbars=yes");
  wref.focus();
}



/**
 * 
 *
 * internal revisions
 * TICKET 4788
 */
function validateForm(the_form)
{
	var filterMethod = document.getElementById('filter_result_method');
	var execStatus = document.getElementById('filter_result_result');
	var loop2do = execStatus.length;
	var idx = 0;
	var notRunFound = false;
	var status_ok = true;

	if( filterMethod.value == code_lastest_exec_method)
	{
		for(idx=0; idx<loop2do; idx++)
		{
			if(execStatus[idx].selected && execStatus[idx].value == code_not_run)
			{
				status_ok = false;
				alert(msg_block_filter_not_run_latest_exec);
				break;
			}
		}
	}
	return status_ok;
}
</script>
{/literal}


<script type="text/javascript">
	treeCfg.root_name='{$gui->ajaxTree->root_node->name|escape:'javascript'}';
	treeCfg.root_id={$gui->ajaxTree->root_node->id};
	treeCfg.root_href='{$gui->ajaxTree->root_node->href}';
	treeCfg.children={$gui->ajaxTree->children};
	treeCfg.cookiePrefix='{$gui->ajaxTree->cookiePrefix}';
</script>

<script type="text/javascript" src='gui/javascript/execTreeWithMenu.js'></script>

<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>


{* BUGID 3301 - js include file for simpler code, filter refactoring/redesign *}
{include file='inc_filter_panel_js.tpl'}

{* 
 * !!!!! IMPORTANT !!!!!
 * Above included file closes <head> tag and opens <body>, so this is not done here.
 *}

	
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":""}
{assign var="build_number" value=$control->settings.setting_build.selected}
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">{$labels.test_plan}{$tlCfg->gui_title_separator_1} {$control->args->testplan_name|escape}
{$tlCfg->gui_separator_open}{$labels.build}{$tlCfg->gui_title_separator_1}
{$control->settings.setting_build.items.$build_number|escape}{$tlCfg->gui_separator_close}</h1>


{* BUGID 3301: include file for filter panel *}
{include file='inc_filter_panel.tpl'}

{* BUGID 4042 *}
{include file="inc_tree_control.tpl"}

{* ===================================================================== *}
{* BUGID 4077 *}
<div id="tree_div" style="overflow:auto; height:100%;border:1px solid #c3daf9;"></div>

{*if $gui->src_workframe != ''}
<script type="text/javascript">
	parent.workframe.location='{$gui->src_workframe}';
</script>
{/if*}

</body>
</html>
