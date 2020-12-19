{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	issueTrackerEdit.tpl
*}

{$cfg_section=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{$url_args="lib/issuetrackers/issueTrackerEdit.php"}
{$edit_url="$basehref$url_args"}


{lang_get var='labels'
          s='warning,warning_empty_issuetracker_name,warning_empty_issuetracker_type,
             show_event_history,th_issuetracker,th_issuetracker_type,config,btn_cancel,show_hide,
             issuetracker_show_cfg_example,issuetracker_cfg_example,used_on_testproject,
             btn_check_connection,issueTracker_connection_ok,issueTracker_connection_ko,
             show_hide_config_example,show_hide_linked_to_project,issuetracker_not_used_linked'}

{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
{include file="inc_del_onclick.tpl"}
<script type="text/javascript">
/* AJAX CALL */
function displayITSCfgExample(oid,displayOID) {
	var type;
	var HTMLTxt;
  var ztr;

  type = Ext.get(oid).getValue();
  HTMLTxt = document.getElementById(displayOID).innerText;

  ztr = HTMLTxt.trim();
  if (ztr.length > 0) {
    var skip = false;
    var children = document.getElementById(displayOID).childNodes;
    for (var idx = 0; idx < children.length; idx++) {
      if( children[idx].id == 'usedBy') {
        skip = true;
        break;        
      }
    }
    if (skip == false) {
      document.getElementById(displayOID).innerHTML = '';
      return;    
    }
  }  

	Ext.Ajax.request({
		url: fRoot+'lib/ajax/getissuetrackercfgtemplate.php',
		method: 'GET',
		params: {
			type: type
		},
		success: function(result, request) {
			var obj = Ext.util.JSON.decode(result.responseText);
      // after 
      // include of jquery and upgrade of prototype
      // I've started with issues $
      document.getElementById(displayOID).innerHTML = obj['cfg'];
		},
		failure: function (result, request) {
		}
	});
	
}

/**
 *
 */
function displayITSUsedBy(displayOID) {
  var HTMLTxt;
  var ztr;

  HTMLTxt = document.getElementById(displayOID).innerText;
  ztr = HTMLTxt.trim();

  // Toogle -> remove
  if (ztr.length > 0) {
    var skip = false;
    var children = document.getElementById(displayOID).childNodes;
    for (var idx = 0; idx < children.length; idx++) {
      if( children[idx].id != 'usedBy') {
        skip = true;
        break;        
      }
    }

    if (skip == false) {
      document.getElementById(displayOID).innerHTML = '';
      return;    
    }
  }  

  var txt = '';
  txt += '<div id="usedBy">';
  {if $gui->testProjectSet != ''}
    txt += "<b>{$labels.used_on_testproject}</b>";
    {foreach key=item_id item=item_def from=$gui->testProjectSet}
      txt += '<br>' + "{$item_def.testproject_name|escape}";
    {/foreach}  
  {else}
    txt += "<b><i>{$labels.issuetracker_not_used_linked}</i></b>";
  {/if}
  txt += "<div>";
  document.getElementById(displayOID).innerHTML = txt;  

}

</script>

{include file="bootstrap.inc.tpl"}
</head>


{$cellContent = "col-sm-10"}
{$cellLabel = "col-sm-2 col-sm-2 control-label"}
{$buttonGroupLayout = "form-group"} {* Domain: form-group, groupBtn *}
{$inputClass = ""}
{$textAreaCfg.rows = #ISSUETRACKER_CFG_ROWS#}
{$textAreaCfg.cols = #ISSUETRACKER_CFG_COLS#}


{$showCheckConnAlert = false}
{if $gui->connectionStatus == 'ok'}
   {$showCheckConnAlert=true}
   {$msgConnAlert = $labels.issueTracker_connection_ok}
   {$addClassConnAlert = 'success'}
{else if $gui->connectionStatus == 'ko'}    
   {$showCheckConnAlert = true}
   {$msgConnAlert = $labels.issueTracker_connection_ko}
   {$addClassConnAlert = 'danger'}
{/if}



<body>
  {include file="aside.tpl"}  

  <div id="main-content">
    <h1 class="title big-font">{$gui->main_descr|escape}</h1>
    {if $gui->canManage != ""}
      <div style="margin: 8px;" id="8container">
      
        {include file="inc_feedback.tpl" user_feedback=$gui->user_feedback}
       
        {if $showCheckConnAlert}
          <div class="alert alert-{$addClassConnAlert}" style="width:50%;" role="alert"> {$msgConnAlert} </div>
        {/if}

        <div class="row mt">
          <div class="col-lg-12">
            <div class="form-panel">
              <form class="form-horizontal style-form" name="edit" method="post" action="{$edit_url}"">
                <div class="form-group">
                  <label for="name" class="{$cellLabel}">{$labels.th_issuetracker}</label>
                  <a href="javascript:displayITSUsedBy('cfg_example')">
                      <i class="fas fa-info-circle" title="{$labels.show_hide_linked_to_project}"></i>
                  </a>
                  <div class="{$cellContent}">
                    <input class="{$inputClass}" required type="text" name="name" id="name"  
                           size="{#ISSUETRACKER_NAME_SIZE#}" 
                           maxlength="{#ISSUETRACKER_NAME_MAXLEN#}" 
                           value="{$gui->item.name|escape}" />
                  </div> <!-- cellContent -->  
                </div> <!-- class="form-group" -->


                <div class="form-group">
                  <label for="type" class="{$cellLabel}">{$labels.th_issuetracker_type}</label>
                  <div class="{$cellContent}">
                    <select id="type" name="type">
                      {html_options options=$gui->typeDomain selected=$gui->item.type}
                    </select>
                  </div> <!-- cellContent -->  
                </div> <!-- class="form-group" -->

                <div class="form-group">
                  <label for="cfg" class="{$cellLabel}">{$labels.config}</label>
                  <a title="{$labels.show_hide_config_example}"" href="javascript:displayITSCfgExample('type','cfg_example')">
                      <i class="fas fa-eye"></i>
                  </a>
                  <div class="{$cellContent}">
                    <textarea name="cfg" id="cfg" 
                              rows="{$textAreaCfg.rows}" 
                              cols="{$textAreaCfg.cols}">{$gui->item.cfg}</textarea>
                  </div> <!-- cellContent -->  
                </div> <!-- class="form-group" -->

                <div class="form-group" style="padding-bottom:5px;marging-bottom:5px;">
                  <label class="{$cellLabel}">&nbsp;</label>
                  <div name="cfg_example" id="cfg_example" class="{$cellContent}">
                    &nbsp;
                  </div> <!-- cellContent -->        
                </div> <!-- class="form-group" -->
          
                <div class="{$buttonGroupLayout}">
                  {if $buttonGroupLayout == "form-group"}
                    <div class="col-sm-offset-2 col-sm-10">
                  {/if}  
                    <input type="hidden" name="id" id="id" value="{$gui->item.id}">
                    <input type="hidden" name="doAction" value="{$gui->operation}" />
                    <input class="btn btn-primary" type="submit" 
                           name="create" id="create" 
                           value="{$gui->submit_button_label}"
                           onclick="doAction.value='{$gui->operation}'" />
                  
                    <input class="btn btn-primary" type="submit" 
                           name="checkConnection" id="checkConnection" 
                           value="{$labels.btn_check_connection}"
                           onclick="doAction.value='checkConnection'" />
                   
                    <input class="btn btn-primary" type="button"
                           name="cancel" id="cancel"
                           value="{$labels.btn_cancel}"
                           onclick="javascript:location.href=fRoot+'lib/issuetrackers/issueTrackerView.php'" />
                  {if $buttonGroupLayout == "form-group"}
                    </div>
                  {/if}  
                </div>
              </form>
            </div> <!-- class="form-panel" -->
          </div> <!-- class="col-lg-12" -->
        </div> <!-- class="row mt" -->
      </div> <!-- id="8container" -->
    {/if}

  </div> <!-- id="main-content" -->

  {include file="supportJS.inc.tpl"}
</body>
</html>