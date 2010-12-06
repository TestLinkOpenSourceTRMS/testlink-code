{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqSpecListTree.tpl,v 1.11.2.4 2010/12/06 16:05:45 asimon83 Exp $ 
show requirement specifications tree menu

rev: 
  20101206 - asimon - BUGID 4077: Trees do not work on Internet Explorer
  20101122 - asimon - BUGID 4042: "Expand/Collapse" Button for Trees
  20101027 - asimon - BUGID 3946: reqirement specification tree size
  20100808 - asimon - first implementation of requirement filtering,
                      included filter panel template
  20080831 - franciscom - treeCfg
                             manage testlink_node_type, useBeforeMoveNode
                             
*}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_ext_js.tpl" bResetEXTCss=1}

{* Ext Collapsible Panel *}
<script type="text/javascript" src='gui/javascript/ext_extensions.js'></script>
{literal}
<script type="text/javascript">
Ext.onReady(function() {
	Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

	// collapsible panel for filters and settings
	var settingsPanel = new Ext.ux.CollapsiblePanel({
		id: 'tl_exec_filter',
		applyTo: 'settings_panel'
	});
	var filtersPanel = new Ext.ux.CollapsiblePanel({
		id: 'tl_exec_settings',
		applyTo: 'filter_panel'
	});
});
</script>
{/literal}	

{if $gui->ajaxTree->loader == ''}
	{literal}
	<script type="text/javascript">
	// BUGID 4077
		treeCfg = { tree_div_id:'tree_div',root_name:"",root_id:0,root_href:"",
		            loader:"", enableDD:false, dragDropBackEndUrl:'',children:"" };
        </script>
        {/literal}
	
	<script type="text/javascript">
		treeCfg.root_name='{$gui->ajaxTree->root_node->name|escape:'javascript'}';
		treeCfg.root_id={$gui->ajaxTree->root_node->id};
		treeCfg.root_href='{$gui->ajaxTree->root_node->href}';
		treeCfg.children={$gui->ajaxTree->children};
	</script>
	<script type="text/javascript" src='gui/javascript/execTree.js'></script>

{else}
	{literal}
	<script type="text/javascript">
		treeCfg = {tree_div_id:'tree_div',root_name:"",root_id:0,root_href:"",
		           root_testlink_node_type:'',useBeforeMoveNode:false,
		           loader:"", enableDD:false, dragDropBackEndUrl:''};
	</script>
	{/literal}
	
	<script type="text/javascript">
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
	<script type="text/javascript" src='gui/javascript/treebyloader.js'>
	</script>
{/if}

<script type="text/javascript" language="javascript">
var req_spec_manager_url = '{$gui->req_spec_manager_url}';
var req_manager_url = '{$gui->req_manager_url}';
</script>

</head>
<body>
<h1 class="title">{$gui->tree_title}</h1>

{* include file for filter panel *}
{include file='inc_filter_panel.tpl'}

{* BUGID 4042 *}
{include file="inc_tree_control.tpl"}

{* BUGID 4077 *}
<div id="tree_div" style="overflow:auto; height:100%;border:1px solid #c3daf9;"></div>
</body>
</html>