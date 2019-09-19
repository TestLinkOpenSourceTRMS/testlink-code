{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource tcTree.tpl
Purpose: smarty template - show test specification tree menu 

@internal revisions
*}
{lang_get var="labels"
          s="caption_nav_filter_settings,testsuite,do_auto_update,keywords_filter_help,
             button_update_tree,no_tc_spec_av,keyword,execution_type"}


{include file="inc_head.tpl" openHead="yes"}
{include file="inc_ext_js.tpl" bResetEXTCss=1}

{* Ext Collapsible Panel *}
<script type="text/javascript" src='gui/javascript/ext_extensions.js'></script>
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
</script>
	
{if $gui->ajaxTree->loader == ''}
  <script type="text/javascript">
  treeCfg = { tree_div_id:'tree_div',root_name:"",root_id:0,root_href:"",
              loader:"", enableDD:false, dragDropBackEndUrl:'',children:"" };
  treeCfg.root_name='{$gui->ajaxTree->root_node->name|escape:'javascript'}';
  treeCfg.root_id={$gui->ajaxTree->root_node->id};
  treeCfg.root_href='{$gui->ajaxTree->root_node->href}';
  treeCfg.children={$gui->ajaxTree->children};
  treeCfg.cookiePrefix='{$gui->ajaxTree->cookiePrefix}';
  treeCfg.enableDD='{$gui->ajaxTree->dragDrop->enabled}';
  </script>
  <script type="text/javascript" src='gui/javascript/execTree.js'></script>
    
{else}
  <script type="text/javascript">
  treeCfg = { tree_div_id:'tree_div',root_name:"",root_id:0,root_href:"",
              root_testlink_node_type:'',useBeforeMoveNode:false,
              loader:"", enableDD:false, dragDropBackEndUrl:'' };
  treeCfg.loader='{$gui->ajaxTree->loader}';
  treeCfg.root_name='{$gui->ajaxTree->root_node->name|escape}';
  treeCfg.root_id={$gui->ajaxTree->root_node->id};
  treeCfg.root_href='{$gui->ajaxTree->root_node->href}';
  treeCfg.enableDD='{$gui->ajaxTree->dragDrop->enabled}';
  treeCfg.dragDropBackEndUrl='{$gui->ajaxTree->dragDrop->BackEndUrl}';
  treeCfg.cookiePrefix='{$gui->ajaxTree->cookiePrefix}';
  treeCfg.root_testlink_node_type='{$gui->ajaxTree->root_node->testlink_node_type}';
  treeCfg.useBeforeMoveNode='{$gui->ajaxTree->dragDrop->useBeforeMoveNode}';
  </script>
  <script type="text/javascript" src='gui/javascript/treebyloader.js'></script>
{/if}


{include file='inc_filter_panel_js.tpl'}

{* 
 * !!!!! IMPORTANT !!!!!
 * Above included file closes <head> tag and opens <body>, so this is not done here.
 *}


<h1 class="title">{$gui->treeHeader}</h1>
{include file='inc_filter_panel.tpl'}
{include file="inc_tree_control.tpl"}
<div id="tree_div" style="overflow:auto; height:100%;border:1px solid #c3daf9;"></div>
</body>
</html>