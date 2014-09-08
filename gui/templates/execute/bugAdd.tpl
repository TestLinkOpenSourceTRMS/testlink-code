{*
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource bugAdd.tpl
@internal revisions
@since 1.9.12

*}
{include file="inc_head.tpl"}

{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}
{lang_get var='labels' 
          s='title_bug_add,link_bts_create_bug,bug_id,notes,hint_bug_notes,
             btn_close,btn_add_bug,btn_save'} 


<body onunload="dialog_onUnload(bug_dialog)" onload="dialog_onLoad(bug_dialog)">
<h1 class="title">
  {$labels.title_bug_add} 
  {include file="inc_help.tpl" helptopic="hlp_btsIntegration" show_help_icon=true}
</h1>

{include file="inc_update.tpl" user_feedback=$gui->msg}
<div class="workBack">
  <form action="lib/execute/bugAdd.php" method="post">
    <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">
    <input type="hidden" name="user_action" id="user_action" value="">

    {if $gui->user_action == 'link'}
      <p>
      <a style="font-weight:normal" target="_blank" href="{$gui->createIssueURL}">
      {$labels.link_bts_create_bug}({$gui->issueTrackerVerboseID|escape})</a>
      </p>  
      <p class="label">{$gui->issueTrackerVerboseType|escape} {$labels.bug_id}
        <input type="text" id="bug_id" name="bug_id" required value="{$gui->bug_id}"
               size="{#BUGID_SIZE#}" maxlength="{$gui->bugIDMaxLength}"/>
      </p>

      {if $gui->tlCanAddIssueNote }
      <p class="label"><img src="{$tlImages.info}" title="{$labels.hint_bug_notes}">{$labels.notes}</p>
        <textarea id="bug_notes" name="bug_notes" rows="10" cols="60" >{$gui->bug_notes}</textarea>
      {/if}    
    {/if}
    <div class="groupBtn">
     {if $gui->user_action == 'link'}
      <input type="submit" value="{$labels.btn_save}" 
             onclick="user_action.value='link';return dialog_onSubmit(bug_dialog)" />
     {/if} 
      <input type="button" value="{$labels.btn_close}" onclick="window.close()" />
    </div>
  </form>
</div>

</body>
</html>