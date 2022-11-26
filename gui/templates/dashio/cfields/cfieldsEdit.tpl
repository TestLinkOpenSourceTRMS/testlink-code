{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource cfieldsEdit.tpl

Important Development note:
Input names:
            cf_show_on_design
            cf_show_on_execution
            cf_enable_on_design
            cf_enable_on_execution
            cf_show_on_testplan_design
            cf_enable_on_testplan_design


can not be changed, because there is logic on cfields_edit.php
that dependens on these names.
As you can see these names are build adding 'cf_' prefix to name
of columns present on custom fields tables.
This is done to simplify logic.

*}

{$cfg_section=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{$managerURL="lib/cfields/cfieldsEdit.php"}
{$viewAction="lib/cfields/cfieldsView.php"}

{lang_get s='warning_delete_cf' var="warning_msg" }
{lang_get s='delete' var="del_msgbox_title" }

{lang_get var="labels"
          s="btn_ok,title_cfields_mgmt,warning_is_in_use,warning,name,label,type,possible_values,
             warning_empty_cfield_name,warning_empty_cfield_label,testproject,assigned_to_testprojects,
             enable_on_design,show_on_exec,enable_on_exec,enable_on_testplan_design,
             available_on,btn_upd,btn_delete,warning_no_type_change,enable_on,
             btn_add,btn_cancel,show_on_design,show_on_testplan_design,btn_add_and_assign_to_current,"}

{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
{include file="inc_del_onclick.tpl"}

{include file="cfields/cfieldsEditJS.tpl"}

{include file="bootstrap.inc.tpl"}
</head>

{$cellContent = "col-sm-10"}
{$cellLabel = "col-sm-2 col-sm-2 control-label"}
{$buttonGroupLayout = "form-group"} {* Domain: form-group, groupBtn *}
{$inputClass = ""}
{$edit_url = "lib/cfields/cfieldsEdit.php"}
{$name_size = #CFIELD_NAME_SIZE#}
{$name_maxlength = #CFIELD_NAME_MAXLEN#}
{$label_size = #CFIELD_LABEL_SIZE#}
{$label_maxlength = #CFIELD_LABEL_MAXLEN#}
{$possible_values_size = #CFIELD_POSSIBLE_VALUES_SIZE#}
{$possible_values_maxlength = #CFIELD_POSSIBLE_VALUES_MAXLEN#}


{$possible_values_display_style="none"}
{if $gui->show_possible_values }
  {$possible_values_display_style=""}
{/if}


<body onload="configure_cf_attr('combo_cf_node_type_id',js_enable_on_cfg,js_show_on_cfg);">
  {include file="aside.tpl"}  

  <div id="main-content">
    <h1 class="title big-font">{$labels.title_cfields_mgmt}</h1>
      <div style="margin: 8px;" id="8container">
        {include file="inc_update.tpl" user_feedback=$user_feedback}

        <div class="row mt">
          <div class="col-lg-12">
            <div class="form-panel">
              <form class="form-horizontal style-form" name="cfields_edit" 
                method="post" action="{$edit_url}" onSubmit="javascript:return validateForm(this);">
                 <input type="hidden" id="hidden_id" name="cfield_id" value="{$gui->cfield.id}" />
                 <input type="hidden" id="tproject_id" name="tproject_id" value="{$gui->tproject_id}" />
                 <input type="hidden" id="tplan_id" name="tplan_id" value="{$gui->tplan_id}" />

                <div class="form-group">
                  <label for="name" class="{$cellLabel}">{$labels.name}</label>
                  <div class="{$cellContent}">
                    <input class="{$inputClass}" required type="text" name="cf_name" id="cf_name"  
                           size="{$name_size}" 
                           maxlength="{$name_maxlength}" 
                           value="{$gui->cfield.name|escape}" />
                  </div> <!-- cellContent -->  
                </div> <!-- class="form-group" -->

                <div class="form-group">
                  <label for="label" class="{$cellLabel}">{$labels.label}</label>
                  <div class="{$cellContent}">
                    <input class="{$inputClass}" required type="text" name="cf_label" id="cf_label"  
                           size="{$label_size}" 
                           maxlength="{$label_maxlength}" 
                           value="{$gui->cfield.label|escape}" />
                  </div> <!-- cellContent -->  
                </div> <!-- class="form-group" -->

                <div class="form-group">
                  <label for="available_on" class="{$cellLabel}">{$labels.available_on}</label>
                  <div class="{$cellContent}">
                    {if $gui->cfield_is_used} 
                      {* Type CAN NOT BE CHANGED *}
                      {$idx=$gui->cfield.node_type_id}
                      {$gui->cfieldCfg->cf_allowed_nodes.$idx}
                      <input type="hidden" id="combo_cf_node_type_id"
                             value={$gui->cfield.node_type_id} name="cf_node_type_id" />
                    {else}
                      <select onchange="configure_cf_attr('combo_cf_node_type_id',
                                                          js_enable_on_cfg,
                                                          js_show_on_cfg);"
                              id="combo_cf_node_type_id"
                              name="cf_node_type_id">
                      {html_options options=$gui->cfieldCfg->cf_allowed_nodes selected=$gui->cfield.node_type_id}
                      </select>
                    {/if}
                  </div> <!-- cellContent -->  
                </div> <!-- class="form-group" -->


                <div class="form-group">
                  <label for="type" class="{$cellLabel}">{$labels.type}</label>
                  <div class="{$cellContent}">
                    {if $gui->cfield_is_used}
                      {$idx=$gui->cfield.type}
                      {$gui->cfield_types.$idx}
                      <input type="hidden" id="hidden_cf_type"
                             value={$gui->cfield.type} name="cf_type" />
                    {else}
                      <select onchange="cfg_possible_values_display(js_possible_values_cfg,
                                                                    'combo_cf_type',
                                                                    'possible_values');"
                              id="combo_cf_type"
                              name="cf_type">
                      {html_options options=$gui->cfield_types selected=$gui->cfield.type}
                      </select>
                    {/if}
                  </div> <!-- cellContent -->  
                </div> <!-- class="form-group" -->


                <div id="possible_values" class="form-group" style="display:{$possible_values_display_style};">
                  <label for="cf_possible_values" class="{$cellLabel}">{$labels.possible_values}</label>
                  <div class="{$cellContent}">
                    <input class="{$inputClass}" type="text" 
                           name="cf_possible_values" id="cf_possible_values"  
                           size="{$possible_values_size}" 
                           maxlength="{$possible_values_maxlength}" 
                           value="{$gui->cfield.possible_values}" />
                  </div> <!-- cellContent -->  
                </div> <!-- class="form-group" -->

                <div class="form-group" id="container_cf_enable_on">
                  <label for="type" class="{$cellLabel}">{$labels.enable_on}</label>
                  <div class="{$cellContent}">
                    <select name="cf_enable_on" id="cf_enable_on"
                            onchange="initShowOnExec('cf_enable_on',js_show_on_cfg);">
                      {foreach item=area_cfg key=area_name from=$gui->cfieldCfg->cf_enable_on}
                        {$access_key="enable_on_$area_name"}
                        <option value={$area_name} id="option_{$area_name}" 
                          {if $area_cfg.value == 0} style="display:none;" {/if} 
                          {if $gui->cfield.$access_key} selected="selected"	{/if}>{$area_cfg.label}</option>
                      {/foreach}
                    </select>
                  </div> <!-- cellContent -->  
                </div> <!-- class="form-group" -->

                <div class="form-group" id="container_cf_show_on_execution" {$gui->cfieldCfg->cf_show_on.execution.style}>
                  <label for="type" class="{$cellLabel}">{$labels.show_on_exec}</label>
                  <div class="{$cellContent}">
                    <select id="cf_show_on_execution" name="cf_show_on_execution">
                      {html_options options=$gsmarty_option_yes_no selected=$gui->cfield.show_on_execution}
                    </select>
                  </div> <!-- cellContent -->  
                </div> <!-- class="form-group" -->


                <div class="{$buttonGroupLayout}">
                  <input type="hidden" name="do_action" id="do_action" value="" />

                  {if $buttonGroupLayout == "form-group"}
                    <div class="col-sm-offset-2 col-sm-10">
                  {/if}  
                  {if $user_action eq 'edit'  or $user_action eq 'do_update'}
                      <input class="{#BUTTON_CLASS#}" type="submit" 
                             name="do_update" value="{$labels.btn_upd}"
                             onclick="do_action.value='do_update'"/>

                      {* Allow delete , just give warning *}
                      <input class="{#BUTTON_CLASS#}" type="button" 
                             name="do_delete" id="do_delete"
                             value="{$labels.btn_delete}"
                             onclick="delete_confirmation({$gui->cfield.id},
                                     '{$gui->cfield.name|escape:'javascript'|escape}',
                                     '{$del_msgbox_title}','{$warning_msg}');">

                  {else}    
                      <input class="{#BUTTON_CLASS#}" type="submit" 
                             name="do_update" value="{$labels.btn_add}"
                             onclick="do_action.value='do_add'"/>


                      <input class="{#BUTTON_CLASS#}" type="submit" 
                             name="do_add_and_assign" id="do_add_and_assign"
                             value="{$labels.btn_add_and_assign_to_current}"
                             onclick="do_action.value='do_add_and_assign'"/>                  
                  {/if}
                  <input class="{#BUTTON_CLASS#}" type="button" 
                         name="cancel" id="cancel" 
                         value="{$labels.btn_cancel}"
                         onclick="javascript: location.href=fRoot+'lib/cfields/cfieldsView.php';" />

                  {if $buttonGroupLayout == "form-group"}
                    </div>
                  {/if}


              </form>
            </div> <!-- class="form-panel" -->
          </div> <!-- class="col-lg-12" -->
        </div> <!-- class="row mt" -->

      </div> <!-- id="8container" -->
  </div> <!-- id="main-content" -->

  {include file="supportJS.inc.tpl"}
</body>
</html>