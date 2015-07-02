{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource planTCNavigator.tpl
Scope: show test plan tree for execution

@internal revisions:
@since 1.9.7
*}

{lang_get var="labels" 
          s='btn_update_menu,btn_apply_filter,keyword,keywords_filter_help,title_navigator,
             btn_bulk_update_to_latest_version,
             filter_owner,TestPlan,test_plan,caption_nav_filters,
             build,filter_tcID,filter_on,filter_result,platform, include_unassigned_testcases'}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_ext_js.tpl" bResetEXTCss=1}

{* includes Ext.ux.CollapsiblePanel *}
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

    <script type="text/javascript">
    treeCfg = { tree_div_id:'tree_div',root_name:"",root_id:0,root_href:"",
                loader:"", enableDD:false, dragDropBackEndUrl:'',children:"" };
    </script>
    
    <script type="text/javascript">
      treeCfg.root_name = '{$gui->ajaxTree->root_node->name}';
      treeCfg.root_id = {$gui->ajaxTree->root_node->id};
      treeCfg.root_href = '{$gui->ajaxTree->root_node->href}';
      treeCfg.children = {$gui->ajaxTree->children};
      treeCfg.cookiePrefix = "{$gui->ajaxTree->cookiePrefix}";
    </script>
    
    <script type="text/javascript" src='gui/javascript/execTree.js'>
    </script>

<script type="text/javascript">
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
</script>


{include file='inc_filter_panel_js.tpl'}

{* 
 * !!!!! IMPORTANT !!!!!
 * Above included file closes <head> tag and opens <body>, so this is not done here.
 *}
 
{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

<h1 class="title">{$gui->title_navigator} {$gui->additional_string|escape}</h1>

{include file='inc_filter_panel.tpl'}
{include file="inc_tree_control.tpl"}
<div id="tree_div" style="overflow:auto; height:100%;border:1px solid #c3daf9;"></div>

<script type="text/javascript">
{if $gui->src_workframe != ''}
  parent.workframe.location='{$gui->src_workframe}';
{/if}
</script>

</body>
</html>