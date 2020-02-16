{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource cfieldsEditJS.tpl
*}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'{$managerURL}'+'?do_action=do_delete&cfield_id=';
</script>

<script type="text/javascript">
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_empty_cfield_name = "{$labels.warning_empty_cfield_name|escape:'javascript'}";
var warning_empty_cfield_label = "{$labels.warning_empty_cfield_label|escape:'javascript'}";

// -------------------------------------------------------------------------------
// To manage hide/show combo logic, depending of node type
var js_enable_on_cfg = new Array();
var js_show_on_cfg = new Array();

// DOM Object ID (oid)
js_enable_on_cfg['oid_prefix'] = new Array();
js_enable_on_cfg['oid_prefix']['boolean_combobox'] = 'cf_enable_on_';
js_enable_on_cfg['oid_prefix']['container'] = 'container_cf_enable_on_';
js_enable_on_cfg['oid'] = new Array();
js_enable_on_cfg['oid']['combobox'] = 'cf_enable_on';
js_enable_on_cfg['oid']['container'] = 'container_cf_enable_on';


// will containg show (1 /0 ) info for every node type
js_enable_on_cfg['execution'] = new Array();
js_enable_on_cfg['design'] = new Array();
js_enable_on_cfg['testplan_design'] = new Array();  // BUGID 1650 (REQ)


// DOM Object ID (oid)
js_show_on_cfg['oid_prefix'] = new Array();
js_show_on_cfg['oid_prefix']['boolean_combobox'] = 'cf_show_on_';
js_show_on_cfg['oid_prefix']['container'] = 'container_cf_show_on_';

// will containg show (1 /0 ) info for every node type
js_show_on_cfg['execution'] = new Array();
js_show_on_cfg['design'] = new Array();
js_show_on_cfg['testplan_design'] = new Array();  // BUGID 1650 (REQ)

{foreach key=node_type item=cfg_def from=$gui->cfieldCfg->enable_on_cfg.execution}
  js_enable_on_cfg['execution'][{$node_type}]={$cfg_def};
{/foreach}

{foreach key=node_type item=cfg_def from=$gui->cfieldCfg->enable_on_cfg.design}
  js_enable_on_cfg['design'][{$node_type}]={$cfg_def};
{/foreach}

{foreach key=node_type item=cfg_def from=$gui->cfieldCfg->enable_on_cfg.testplan_design}
  js_enable_on_cfg['testplan_design'][{$node_type}]={$cfg_def};
{/foreach}


{foreach key=node_type item=cfg_def from=$gui->cfieldCfg->show_on_cfg.execution}
  js_show_on_cfg['execution'][{$node_type}]={$cfg_def};
{/foreach}

{foreach key=node_type item=cfg_def from=$gui->cfieldCfg->show_on_cfg.design}
  js_show_on_cfg['design'][{$node_type}]={$cfg_def};
{/foreach}

{foreach key=node_type item=cfg_def from=$gui->cfieldCfg->show_on_cfg.testplan_design}
  js_show_on_cfg['testplan_design'][{$node_type}]={$cfg_def};
{/foreach}
// -------------------------------------------------------------------------------

var js_possible_values_cfg = new Array();
{foreach key=cf_type item=cfg_def from=$gui->cfieldCfg->possible_values_cfg}
  js_possible_values_cfg[{$cf_type}]={$cfg_def};
{/foreach}


function validateForm(f)
{
  if (isWhitespace(f.cf_name.value))
  {
    alert_message(alert_box_title,warning_empty_cfield_name);
    selectField(f, 'cf_name');
    return false;
  }

  if (isWhitespace(f.cf_label.value))
  {
    alert_message(alert_box_title,warning_empty_cfield_label);
    selectField(f, 'cf_label');
    return false;
  }
  return true;
}

/*
  function: configure_cf_attr
            depending of node type, custom fields attributes
            will be set to disable, is its value is nonsense
            for node type choosen by user.

  args :
         id_nodetype: id of html input used to choose node type
                      to which apply custom field


  returns: -

*/
function configure_cf_attr(id_nodetype,enable_on_cfg,show_on_cfg)
{
  var o_nodetype=document.getElementById(id_nodetype);
  var o_enable=new Array();
  var o_enable_container=new Array();
  var o_display=new Array();
  var o_display_container=new Array();


  var oid;
  var keys2loop=new Array();
  var idx;
  var key;
  var option_item;
  var enabled_option_counter=0;
  var style_display;
  var TCASE_NODE=3;   // Sorry MAGIC NUMBER
  
  keys2loop[0]='execution';
  keys2loop[1]='design';
  keys2loop[2]='testplan_design'; 

  style_display='';
  for(idx=0;idx < keys2loop.length; idx++)
  {
    key=keys2loop[idx];
    oid='option_' + key;
    option_item = document.getElementById(oid);

    // Dev Note:
    // Only Firefox (@20100829) is able to hide/show an option present on a HTML select.
    // IE and Chrome NOT 
    // Need to understand then if is better to remove all this code
    if( enable_on_cfg[key][o_nodetype.value] == 0 )
    {
      option_item.style.display='none';
    }
    else
    {
      option_item.style.display='';
      enabled_option_counter++;
    }
  }
  
  // Set Always to Test Spec Design that is valid for TL elements
  if( enabled_option_counter == 0 )
  {
    style_display='none';
  }
  document.getElementById(enable_on_cfg['oid']['container']).style.display=style_display;
  // responsible of BUGID 4000
  // document.getElementById(enable_on_cfg['oid']['combobox']).value='design';

  // ------------------------------------------------------------
  // Display on
  // ------------------------------------------------------------

  // exception if node type = test case && enable_on == execution
  // the display on execution combo has not to be displayed.

  for(idx=0;idx < keys2loop.length; idx++)
  {
    key=keys2loop[idx];
    oid=show_on_cfg['oid_prefix']['boolean_combobox']+key;
    
    o_display[key]=document.getElementById(oid);
    if( o_display[key] != null)
    {
      oid=show_on_cfg['oid_prefix']['container']+key;
      o_display_container[key]=document.getElementById(oid);
      
      if( show_on_cfg[key][o_nodetype.value] == 0 )
      {
        o_display[key].disabled='disabled';
        o_display_container[key].style.display='none';
        o_display[key].value=0;
      }
      else
      {
        // this logic is used to HIDE 'Display On Test Execution' combo
        if( o_nodetype.value == TCASE_NODE && key == 'execution' &&
            document.getElementById(enable_on_cfg['oid']['combobox']).value == key
        )
        {
          o_display[key].value=1;
          o_display[key].disabled='disabled';
          o_display_container[key].style.display='none';
        }
        else
        {
          o_display[key].disabled='';
          o_display_container[key].style.display='';
        }
      }
    }
  }
  // ------------------------------------------------------------
} // configure_cf_attr



/*
  function: cfg_possible_values_display
            depending of Custom Field type, Possible Values attribute
            will be displayed or not.

  args : cf_type: id of custom field type, choosen by user.

         id_possible_values_container : id of html container
                                        where input for possible values
                                        lives. Used to manage visibility.

  returns:

*/
function cfg_possible_values_display(cfg,id_cftype,id_possible_values_container)
{

  o_cftype=document.getElementById(id_cftype);
  o_container=document.getElementById(id_possible_values_container);

  if( cfg[o_cftype.value] == 0 )
  {
    o_container.style.display='none';
  }
  else
  {
    o_container.style.display='';
  }
}

/*
  function: initShowOnExec
            called every time value of 'cf_enable_on' is changed
            to initialize  show_on_ attribute.
 
  args:
  
  returns: 

*/
function initShowOnExec(id_master,show_on_cfg)
{
  var container_oid=show_on_cfg['oid_prefix']['container']+'execution';
  var combo_oid=show_on_cfg['oid_prefix']['boolean_combobox']+'execution';
  
  var o_container=document.getElementById(container_oid);
  var o_combo=document.getElementById(combo_oid);
  
  var o_master=document.getElementById(id_master);
  
  if( o_master.value == 'execution')
  {
    o_container.style.display='none';
    o_combo.value=1;
  }
  else
  {
    o_container.style.display='';
  }
}
</script>
