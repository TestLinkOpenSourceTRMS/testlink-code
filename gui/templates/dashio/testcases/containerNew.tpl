{* 
TestLink Open Source Project - http://testlink.sourceforge.net/

Purpose: smarty template - create containers

@filesource containerNew.tpl
*}

{$myJS = $smarty.template|basename|replace:".tpl":"JS.inc.tpl"}
{config_load file="input_dimensions.conf" section="containerEdit"}

{$inc = "testcases/include"}
{$action="lib/testcases/containerEdit.php?containerID="}
{$action="$basehref$action{$gui->containerID}"}

{lang_get var="labels"
          s="warning_empty_testsuite_name,title_create,tc_keywords,
             warning,btn_create_testsuite,btn_save,cancel,warning_unsaved"}

{include file="inc_head.tpl" openHead='yes' jsValidate="yes"}
{include file="inc_del_onclick.tpl"}
{include file="{$inc}/$myJS"}

</head>

<body onLoad="{$opt_cfg->js_ot_name}.init(document.forms[0]);focusInputField('name')">

<h1 class="{#TITLE_CLASS#}">{$parent_info.description}{$smarty.const.TITLE_SEP}{$parent_info.name|escape}</h1>

<div class="workBack">
<h1 class="title">{$labels.title_create} {lang_get s=$level}</h1>
{include file="inc_update.tpl" result=$sqlResult 
                               user_feedback=$user_feedback
                               item=$level action="add" name=$name
                               refresh=$gui->refreshTree}


<form method="post" action="{$action}" name="container_new" id="container_new"
      onSubmit="javascript:return validateForm(this);">

  <div style="font-weight: bold;">
    <div>
      <input type="hidden" name="containerType" id="containerType" value="{$gui->containerType}"/>
      <input type="hidden" name="tplan_id" id="tplan_id" value="{$gui->tplan_id}"/>
      <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}"/>

      {if $gui->containerType == "testsuite"}
        <input type="hidden" name="parent_tsuite_id" id="parent_tsuite_id" value="{$gui->containerID}"/>
      {/if}

      <input type="hidden" name="add_testsuite" id="add_testsuite" />
      <input class="{#BUTTON_CLASS#}" type="submit" 
             name="add_testsuite_button" id="add_testsuite_button"
             value="{$labels.btn_save}"
             onclick="show_modified_warning = false;" />
  
      <input class="{#BUTTON_CLASS#}" type="button" 
             name="go_back" id="go_back"
             value="{$labels.cancel}" 
             onclick="show_modified_warning=false; 
                     javascript: {if isset($gui->cancelActionJS)}{$gui->cancelActionJS} {else} history.back() {/if};"/>
    </div>  
    {include file="{$inc}/tsuiteViewerRW.inc.tpl"}

   {* Custom fields *}
   {if $cf neq ""}
     <br />
     <div id="cfields_design_time" class="custom_field_container">
     {$cf}
     </div>
   {/if}
   
     <br />
   <div>
   {$kwView = $gsmarty_href_keywordsView|replace:'%s%':$gui->tproject_id}
   <a href={$kwView}>{$labels.tc_keywords}</a>
   {include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
   </div>
   <br />
  <div>
    <input class="{#BUTTON_CLASS#}" type="submit" 
           name="add_testsuite_button" 
           value="{$labels.btn_save}" 
           onclick="show_modified_warning = false;" />

    <input class="{#BUTTON_CLASS#}" type="button" 
           name="go_back" id="go_back"
           value="{$labels.cancel}" 
           onclick="show_modified_warning=false; 
                    javascript: {if isset($gui->cancelActionJS)}{$gui->cancelActionJS} {else} history.back() {/if};"/>
  </div>  

</div>
</form>
</body>
</html>