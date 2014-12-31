{*
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource bugAdd.tpl
@internal revisions
@since 1.9.13

*}
{include file="inc_head.tpl"}

{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}
{lang_get var='labels' 
          s='title_bug_add,link_bts_create_bug,bug_id,notes,hint_bug_notes,
             btn_close,btn_add_bug,btn_save,bug_summary,
             issueType,issuePriority,artifactVersion,artifactComponent'} 


<body onunload="dialog_onUnload(bug_dialog)" onload="dialog_onLoad(bug_dialog)">
<h1 class="title">
  {$gui->pageTitle|escape} 
  {include file="inc_help.tpl" helptopic="hlp_btsIntegration" show_help_icon=true}
</h1>

{include file="inc_update.tpl" user_feedback=$gui->msg}
<div class="workBack">
  <form action="lib/execute/bugAdd.php" method="post">
    <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">
    <input type="hidden" name="tcversion_id" id="tcversion_id" value="{$gui->tcversion_id}">
    <input type="hidden" name="user_action" id="user_action" value="">

    {if $gui->user_action == 'link' || $gui->user_action == 'add_note'}
      <p>
      <a style="font-weight:normal" target="_blank" href="{$gui->issueTrackerCfg->createIssueURL}">
      {$labels.link_bts_create_bug}({$gui->issueTrackerCfg->VerboseID|escape})</a>
      </p>  
      <p class="label">{$gui->issueTrackerCfg->VerboseType|escape} {$labels.bug_id}
        <input type="text" id="bug_id" name="bug_id" required value="{$gui->bug_id}"
               size="{#BUGID_SIZE#}" maxlength="{$gui->issueTrackerCfg->bugIDMaxLength}" 
               {if $gui->user_action == 'add_note'} readonly {/if} />
      </p>

    {/if}

    {if $gui->user_action == 'create'}
      <p class="label">{$labels.bug_summary}(*)
        <input type="text" id="bug_summary" name="bug_summary" required value="{$gui->bug_summary}"
               size="{#BUGSUMMARY_SIZE#}" maxlength="{$gui->issueTrackerCfg->bugSummaryMaxLength}" 
      </p>
    {/if}

    {if $gui->issueTrackerMetaData != ''}
      {if $gui->issueTrackerMetaData.issueTypes != ''}
        <p>
       <label for="issueType">{$labels.issueType}</label>
       {html_options name="issueType" options=$gui->issueTrackerMetaData.issueTypes.items}
      {/if}

      {if $gui->issueTrackerMetaData.priorities != ''}
        <p>
       <label for="issuePriority">{$labels.issuePriority}</label> 
       {html_options name="issuePriority" options=$gui->issueTrackerMetaData.priorities.items}
      {/if}

      {if $gui->issueTrackerMetaData.versions != ''}
        <p>
        <label for="artifactVersion">{$labels.artifactVersion}</label> 
        <select 
                {if $gui->issueTrackerMetaData.versions.isMultiSelect}
                 name="artifactVersion[]" size="2" multiple
                {else}
                 name="artifactVersion"
                {/if} 
                >
        {html_options options=$gui->issueTrackerMetaData.versions.items}
        </select>
      {/if}

      {if $gui->issueTrackerMetaData.components.items != ''}
        <p>
        <label for="artifactComponent">{$labels.artifactComponent}</label>         
         <select  
                 {if $gui->issueTrackerMetaData.components.isMultiSelect}
                 name="artifactComponent[]" size="2" multiple
                 {else}
                 name="artifactComponent"
                 {/if} 
                 >
         {html_options options=$gui->issueTrackerMetaData.components.items}
         </select>
      {/if}

    {/if}

    {if $gui->issueTrackerCfg->tlCanAddIssueNote || $gui->user_action == 'create'}
      <p class="label"><img src="{$tlImages.info}" title="{$labels.hint_bug_notes}">{$labels.notes}</p>
        <textarea id="bug_notes" name="bug_notes" 
                  rows="{#BUGNOTES_ROWS#}" cols="{#BUGNOTES_COLS#}" >{$gui->bug_notes}</textarea>
    {/if}    

    <div class="groupBtn">
     {if $gui->user_action == 'link'}
      <input type="submit" value="{$labels.btn_save}" 
             onclick="user_action.value='{$gui->user_action}';return dialog_onSubmit(bug_dialog)" />
     {/if} 

     {if $gui->user_action == 'create'}
      <input type="submit" value="{$labels.btn_save}" 
             onclick="user_action.value='doCreate';return dialog_onSubmit(bug_dialog)" />
     {/if} 

     {if $gui->user_action == 'add_note'}
      <input type="submit" value="{$labels.btn_save}" onclick="user_action.value='add_note'" />
     {/if} 


      <input type="button" value="{$labels.btn_close}" onclick="window.close()" />
    </div>
  </form>
</div>

</body>
</html>