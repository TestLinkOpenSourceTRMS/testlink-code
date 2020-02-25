{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource planAddTCNavigator.tpl

Scope: show test specification tree for Test Plan related features
    (the name of scripts is not correct; used more)

@internal revisions
@since 1.9.10
*}

{lang_get var="labels" 
          s='keywords_filter_help,btn_apply_filter,execution_type,importance,
             btn_update_menu,title_navigator,keyword,test_plan,keyword,caption_nav_filter_settings'}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_ext_js.tpl" bResetEXTCss=1}

{* includes Ext.ux.CollapsiblePanel *}
<script type="text/javascript" src='gui/javascript/ext_extensions.js'></script>
<script type="text/javascript">
  treeCfg = { tree_div_id:'tree_div',root_name:"",root_id:0,root_href:"",loader:"", 
              enableDD:false, dragDropBackEndUrl:"",children:"" };

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

    {if $gui->loadRightPaneAddTC}  
      EP();
    {/if}

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
  treeCfg.cookiePrefix = "{$gui->ajaxTree->cookiePrefix}";
  </script>
  <script type="text/javascript" src='gui/javascript/execTree.js'></script>
{else}
  <script type="text/javascript">
  treeCfg = { tree_div_id:'tree_div',root_name:"",root_id:0,root_href:"",
              root_testlink_node_type:'',useBeforeMoveNode:false,
              loader:"", enableDD:false, dragDropBackEndUrl:'' };

  treeCfg.loader = "{$gui->ajaxTree->loader}";
  treeCfg.root_name = '{$gui->ajaxTree->root_node->wrapOpen}' + 
                      "{$gui->ajaxTree->root_node->name|escape}" +
                      '{$gui->ajaxTree->root_node->wrapClose}';

  treeCfg.root_id = {$gui->ajaxTree->root_node->id};
  treeCfg.root_href = "{$gui->ajaxTree->root_node->href}";
  treeCfg.cookiePrefix = "{$gui->ajaxTree->cookiePrefix}";
  </script>
        
 <script type="text/javascript" src="gui/javascript/treebyloader.js"></script>
{/if}

<script type="text/javascript">
function pre_submit()
{
  document.getElementById('called_url').value=parent.workframe.location;
  return true;
}
</script>

{* js include file for simpler code, filter refactoring/redesign *}
{include file='inc_filter_panel_js.tpl'}

{* 
 * !!!!! IMPORTANT !!!!!
 * Above included file closes <head> tag and opens <body>, so this is not done here.
 *}
  
<h1 class="title">{$gui->title_navigator}</h1>
<div style="margin: 3px;">

{if $gui->loadRightPaneAddTC}
  {* MOLIII *}  
{/if}

{include file='inc_filter_panel.tpl'}
{include file="tree_control_add_tc_navigator.inc.tpl"}
<div id="tree_div" style="overflow:auto; height:100%;border:1px solid #c3daf9;"></div>

<script type="text/javascript"></script>
</body>
</html>