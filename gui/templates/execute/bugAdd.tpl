{*
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource bugAdd.tpl
@internal revisions
@since 1.9.15

*}
{include file="inc_head.tpl"}

{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}
{lang_get var='labels' 
          s='title_bug_add,link_bts_create_bug,bug_id,notes,hint_bug_notes,
             btn_close,btn_add_bug,btn_save,bug_summary,
             add_link_to_tlexec,
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
    <input type="hidden" name="tplan_id" id="tplan_id" value="{$gui->tplan_id}">
    <input type="hidden" name="tcversion_id" id="tcversion_id" value="{$gui->tcversion_id}">
    <input type="hidden" name="user_action" id="user_action" value="">
    <input type="hidden" name="tcstep_id" id="tcstep_id" value="{$gui->tcstep_id}">

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

    {if $gui->user_action == 'create' || $gui->user_action == 'doCreate' }
      <p class="label">{$labels.bug_summary}(*)
        <input type="text" id="bug_summary" name="bug_summary" required value="{$gui->bug_summary}"
               size="{#BUGSUMMARY_SIZE#}" maxlength="{$gui->issueTrackerCfg->bugSummaryMaxLength}" 
      </p>

     {if $gui->issueTrackerMetaData != ''}
      <p>
      {if $gui->issueTrackerMetaData.issueTypes != ''}
       <label for="issueType">{$labels.issueType}</label>
       {html_options name="issueType" options=$gui->issueTrackerMetaData.issueTypes.items 
        selected = $gui->issueType
       }
      {/if}

      {if $gui->issueTrackerMetaData.priorities != ''}
       <label for="issuePriority">{$labels.issuePriority}</label> 
       {html_options name="issuePriority" options=$gui->issueTrackerMetaData.priorities.items
        selected = $gui->issuePriority
       }
      {/if}
      </p>

      <p> 
      {if $gui->issueTrackerMetaData.versions != ''}
        <label for="artifactVersion">{$labels.artifactVersion}</label> 
        <select class="chosen-select" data-placeholder=" " required 
                {if $gui->issueTrackerMetaData.versions.isMultiSelect}
                 name="artifactVersion[]" size="2" multiple
                {else}
                 name="artifactVersion"
                {/if} 
                >
        {html_options options=$gui->issueTrackerMetaData.versions.items
        selected = $gui->artifactVersion
        }
        </select>
      {/if}
      
      {if $gui->issueTrackerMetaData.components.items != ''}
        <label for="artifactComponent">{$labels.artifactComponent}</label>         
         <select class="chosen-select" data-placeholder=" " required 
                 {if $gui->issueTrackerMetaData.components.isMultiSelect}
                   name="artifactComponent[]" size="2" multiple
                 {else}
                   name="artifactComponent"
                 {/if} 
                 >
         {html_options options=$gui->issueTrackerMetaData.components.items
         selected = $gui->artifactComponent
         }
         </select>
      {/if}
     </p>
     {/if}  {* $gui->issueTrackerMetaData *}
    {/if}

    {if $gui->issueTrackerCfg->tlCanAddIssueNote || $gui->user_action == 'create' || $gui->user_action == 'doCreate'}
      <span class="label"><img src="{$tlImages.info}" title="{$labels.hint_bug_notes}">{$labels.notes}</span>
        <textarea id="bug_notes" name="bug_notes" 
                  rows="{#BUGNOTES_ROWS#}" cols="{#BUGNOTES_COLS#}" >{$gui->bug_notes}</textarea>
    {/if}    

    {if $gui->user_action == 'create' || $gui->user_action == 'doCreate' || $gui->user_action == 'link'}
      <br><br>
      <input type="checkbox" name="addLinkToTL"  id="addLinkToTL">
      <span class="label">{$labels.add_link_to_tlexec}</span>
    {/if}

    <div class="groupBtn">
     {if $gui->user_action == 'link'}
      <input type="submit" value="{$labels.btn_save}" 
             onclick="user_action.value='{$gui->user_action}';return dialog_onSubmit(bug_dialog)" />
     {/if} 

     {if $gui->user_action == 'create' || $gui->user_action == 'doCreate'}
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

<script>
jQuery( document ).ready(function() {
jQuery(".chosen-select").chosen({ width: "35%" });

// From https://github.com/harvesthq/chosen/issues/515
jQuery(".chosen-select").each(function(){
    //    take each select and put it as a child of the chosen container
    //    this mean it'll position any validation messages correctly
    jQuery(this).next(".chosen-container").prepend(jQuery(this).detach());

    //    apply all the styles, personally, I've added this to my stylesheet
    jQuery(this).attr("style","display:block!important; position:absolute; clip:rect(0,0,0,0)");

    //    to all of these events, trigger the chosen to open and receive focus
    jQuery(this).on("click focus keyup",function(event){
        jQuery(this).closest(".chosen-container").trigger("mousedown.chosen");
    });
});
});
</script>
</body>
</html>