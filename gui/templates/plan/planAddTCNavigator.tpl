{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: planAddTCNavigator.tpl,v 1.21 2010/04/29 14:56:26 asimon83 Exp $
f
Scope: show test specification tree for Test Plan related features
		(the name of scripts is not correct; used more)

Revisions:          
  20100428 - asimon - BUGID 3301 - removed old filter/settings form/panel and replaced
                      them with new included template inc_tc_filter_panel.tpl
  20100417 - franciscom - BUGID 2498 - filter by test case spec importance
  20100410 - franciscom - BUGID 2797 - filter by test case execution type
	20080629 - franciscom - fixed missed variable bug
  20080622 - franciscom - ext js tree support
  20080429 - franciscom - keyword filter multiselect
* ------------------------------------------------------------------------ *}

{lang_get var="labels" 
          s='keywords_filter_help,btn_apply_filter,execution_type,importance,
             btn_update_menu,title_navigator,keyword,test_plan,keyword,caption_nav_filter_settings'}

{assign var="keywordsFilterDisplayStyle" value=""}
{if $gui->keywordsFilterItemQty == 0}
    {assign var="keywordsFilterDisplayStyle" value="display:none;"}
{/if}

    {include file="inc_head.tpl" openHead="yes"}
    {include file="inc_ext_js.tpl" bResetEXTCss=1}

	{* BUGID 3301 *}
	{* includes Ext.ux.CollapsiblePanel *}
	<script type="text/javascript" src='gui/javascript/ext_extensions.js'></script>
	{literal}
	<script type="text/javascript">
		treeCfg = {tree_div_id:'tree',root_name:"",root_id:0,root_href:"",
		           loader:"", enableDD:false, dragDropBackEndUrl:'',children:""};
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
	{/literal}

    {if $gui->ajaxTree->loader == '' }
        {literal}
        <script type="text/javascript">
        treeCfg = {tree_div_id:'tree',root_name:"",root_id:0,root_href:"",
                   loader:"", enableDD:false, dragDropBackEndUrl:'',children:""};
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
        treeCfg = {tree_div_id:'tree',root_name:"",root_id:0,root_href:"",
                   root_testlink_node_type:'',useBeforeMoveNode:false,
                   loader:"", enableDD:false, dragDropBackEndUrl:''};
        </script>
        {/literal}
        
        <script type="text/javascript">
	        treeCfg.loader = "{$gui->ajaxTree->loader}";
	        treeCfg.root_name = "{$gui->ajaxTree->root_node->name|escape}";
	        treeCfg.root_id = {$gui->ajaxTree->root_node->id};
	        treeCfg.root_href = "{$gui->ajaxTree->root_node->href}";
	        treeCfg.cookiePrefix = "{$gui->ajaxTree->cookiePrefix}";
	    </script>
        
        <script type="text/javascript" src="gui/javascript/treebyloader.js">
        </script>
   {/if}
{literal}
<script type="text/javascript">
function pre_submit()
{
	document.getElementById('called_url').value=parent.workframe.location;
	return true;
}
</script>
{/literal}
</head>

<body>

<h1 class="title">{$labels.title_navigator}</h1>
<div style="margin: 3px;">

{* BUGID 3301 *}
{include file='testcases/inc_tc_filter_panel.tpl'
         showSettings='yes'
         showFilters='yes'}

<div id="tree" style="overflow:auto; height:400px;border:1px solid #c3daf9;"></div>

{* 20061030 - update the right pane *}
<script type="text/javascript">
{if $gui->src_workframe != ''}
	parent.workframe.location='{$gui->src_workframe}';
{else}
	{if $gui->do_reload}
		  parent.workframe.location.reload();
	{/if}
{/if}
</script>
</body>
</html>