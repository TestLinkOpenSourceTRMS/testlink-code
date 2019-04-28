{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource: reqSpecListTree.tpl
show requirement specifications tree menu
*}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_ext_js.tpl" bResetEXTCss=1}

{* Ext Collapsible Panel *}
<script type="text/javascript" src='gui/javascript/ext_extensions.js'></script>
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

{if $gui->ajaxTree->loader == ''}
  <script type="text/javascript">
  treeCfg = { tree_div_id:'tree_div',root_name:"",root_id:0,root_href:"",loader:"", enableDD:false, dragDropBackEndUrl:'',children:"" };
  </script>
  
  <script type="text/javascript">
  treeCfg.root_name='{$gui->ajaxTree->root_node->name|escape:'javascript'}';
  treeCfg.root_id={$gui->ajaxTree->root_node->id};
  treeCfg.root_href='{$gui->ajaxTree->root_node->href}';
  treeCfg.children={$gui->ajaxTree->children};
  </script>
  <script type="text/javascript" src='gui/javascript/execTree.js'></script>

{else}
  <script type="text/javascript">
  treeCfg = { tree_div_id:'tree_div',root_name:"",root_id:0,root_href:"",root_testlink_node_type:'',useBeforeMoveNode:false,loader:"", enableDD:false, dragDropBackEndUrl:'' };
  </script>
  
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

{include file="inc_tree_control.tpl"}

<div id="tree_div" style="overflow:auto; height:100%;border:1px solid #c3daf9;"></div>
</body>
</html>