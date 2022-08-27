{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	platformsEdit.tpl
Purpose: smarty template - Edit a platform
*}

{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{$tproj=$gui->tproject_id}
{$manageUrl="lib/platforms/platformsEdit.php?tproject_id=$tproj"}
{$manageUrl="$basehref$manageUrl"}

{lang_get var="labels"
          s="warning,warning_empty_platform,show_event_history,
             th_platform,th_notes,btn_cancel,on_design,on_exec,platform_open_for_exec"}


{include file="inc_head.tpl" jsValidate="yes" openHead="yes"}
{include file="inc_del_onclick.tpl"}

</head>

<body>
{include file="aside.tpl"}  
<div id="main-content">

<h1 class="{#TITLE_CLASS#}">{$gui->action_descr|escape}</h1>

{include file="inc_feedback.tpl" user_feedback=$gui->user_feedback}

{if $gui->canManage ne ""}
  <div class="workBack">
  
  <div>
	{if $gui->mgt_view_events eq "yes" && $gui->platform_id > 0}
			<img style="margin-left:5px;" class="clickable" 
			     src="{$smarty.const.TL_THEME_IMG_DIR}/question.gif" 
			     onclick="showEventHistoryFor('{$gui->platform_id}','platforms')" 
			     alt="{$labels.show_event_history}" title="{$labels.show_event_history}"/>
	{/if}
  
  </div><br />

  	<form id="addPlatform" name="addPlatform" method="post" 
          action="{$manageUrl}">

  	<table class="common" style="width:50%">
  		<tr>
  			<th>{$labels.th_platform}</th>
  			<td><input type="text" name="name" id="name"
  			           size="{#PLATFORM_SIZE#}" maxlength="{#PLATFORM_MAXLEN#}"
  				         value="{$gui->name|escape}" required />
			  </td>
  		</tr>
  		<tr>
  			<th>{$labels.th_notes}</th>
  			<td>{$gui->notes}</td>
  		</tr>
      <tr><th style="background:none;">{$labels.on_design}</th>
          <td><input type="checkbox" value="1" 
                name="enable_on_design" id="enable_on_design"  
                {if $gui->enable_on_design eq 1} checked {/if} />
          </td>
      </tr>

      <tr><th style="background:none;">{$labels.on_exec}</th>
          <td><input type="checkbox" value="1" 
                name="enable_on_execution" id="enable_on_execution"  
                {if $gui->enable_on_execution eq 1} checked {/if} />
          </td>
      </tr>

      <tr><th style="background:none;">{$labels.platform_open_for_exec}</th>
          <td><input type="checkbox" value="1" 
                name="is_open" id="is_open"  
                {if $gui->is_open eq 1} checked {/if} />
          </td>
      </tr>

  	</table>
  	<div class="groupBtn">	
	  	<input type="hidden" id="doAction" name="doAction" value="" />
      <input type="hidden" id="platform_id" name="platform_id" 
             value="{$gui->platform_id}" />
	    <input type="submit" class="{#BUTTON_CLASS#}"
             id="submitButton" name="submitButton" 
             value="{$gui->submit_button_label}"
		         onclick="doAction.value='{$gui->submit_button_action}'" />

	  	<input type="button" class="{#BUTTON_CLASS#}"
             id="cancelOp" value="{$labels.btn_cancel}"
		         onclick="javascript:location.href=fRoot+'lib/platforms/platformsView.php?tproject_id={$gui->tproject_id}'" />
  	</div>
  	</form>
  </div>
{/if}
</div>
{include file="supportJS.inc.tpl"}
</body>
</html>
