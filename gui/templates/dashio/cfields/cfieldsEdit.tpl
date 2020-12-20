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

                <div class="form-group">
                  <label for="name" class="{$cellLabel}">{$labels.name}</label>
                  <div class="{$cellContent}">
                    <input class="{$inputClass}" required type="text" name="name" id="name"  
                           size="{$name_size}" 
                           maxlength="{$name_maxlength}" 
                           value="{$gui->item.name|escape}" />
                  </div> <!-- cellContent -->  
                </div> <!-- class="form-group" -->

                <div class="form-group">
                  <label for="label" class="{$cellLabel}">{$labels.label}</label>
                  <div class="{$cellContent}">
                    <input class="{$inputClass}" required type="text" name="label" id="label"  
                           size="{$label_size}" 
                           maxlength="{$label_maxlength}" 
                           value="{$gui->item.label|escape}" />
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


              </form>
            </div> <!-- class="form-panel" -->
          </div> <!-- class="col-lg-12" -->
        </div> <!-- class="row mt" -->

      </div> <!-- id="8container" -->
  </div> <!-- id="main-content" -->

  {include file="supportJS.inc.tpl"}
</body>
</html>