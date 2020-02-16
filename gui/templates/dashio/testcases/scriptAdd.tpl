{*
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource scriptAdd.tpl
@internal revisions
@since 1.9.15

*}
{include file="inc_head.tpl"}

{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}
{lang_get var='labels' 
          s='title_script_add,link_cts_create_script,cts_project_key,script_id,expand_tree,
             collapse_tree,cts_repo_name,cts_script_path,cts_branch_name,btn_close,btn_save,
             cts_commit_id'}

<script language="JavaScript" type="text/javascript">
function highlightListItem(listItemID)
{
  var items = document.getElementsByClassName("highlightItem");
  var i;

  for (i = 0; i < items.length; i++)
  {
    items[i].style.backgroundColor='white';
  }

  document.getElementById(listItemID).style.backgroundColor='#CCDDED';
}

function hoverListItem(listItemID,outEvent)
{
  var item = document.getElementById(listItemID);
  if (item.style.backgroundColor != 'rgb(204, 221, 237)')
  {
    if (outEvent)
    {
      item.style.backgroundColor='white';
    }
    else
    {
      item.style.backgroundColor='#EEEEEE';
    }
  }
}
</script>

{function treeMenu level=0}
  <ul class="level{$level}" style="list-style-type: none;list-style-position: inside;
  padding-left: 0px;padding-right: 0px; margin-left: 10px;margin-right: 0px;
  margin-bottom: 0px;margin-top: 0px">
  {assign var=cnt value=1}
  {$fileLen=count($data)}
  {foreach from=$data key=itemName item=itemValue}
    <li id="{$itemValue[1]}{$itemName}" class="highlightItem" style="cursor: pointer;"
      onmouseover="javascript:hoverListItem('{$itemValue[1]}{$itemName}',false);"
      onmouseout="javascript:hoverListItem('{$itemValue[1]}{$itemName}',true);"
      onclick="javascript:highlightListItem('{$itemValue[1]}{$itemName}');code_path.value='{$itemValue[1]}{$itemName}'">
    {if $cnt == $fileLen}
      {$plusImg='../../../third_party/ext-js/images/default/tree/elbow-end-plus.gif'}
      {$minusImg='../../../third_party/ext-js/images/default/tree/elbow-end-minus.gif'}
      {$elbowImg='../../../third_party/ext-js/images/default/tree/elbow-end.gif'}
    {else}
      {$plusImg='../../../third_party/ext-js/images/default/tree/elbow-plus.gif'}
      {$minusImg='../../../third_party/ext-js/images/default/tree/elbow-minus.gif'}
      {$elbowImg='../../../third_party/ext-js/images/default/tree/elbow.gif'}
    {/if}
    {if $itemValue[0] == 'DIRECTORY'}
      <button type="submit" name="expandDir" style="padding: 0px 0px;border: none;"
      onclick="expand_item.value='{$itemValue[1]}{$itemName}';user_action.value='expand';">
        <img src='{$plusImg}' title="{$labels.expand_tree}" alt="{$labels.expand_tree}"/>
      </button>
      <img src='../../../third_party/ext-js/images/default/tree/folder.gif'/>
      {$itemName}</li>
    {else if is_array($itemValue[0])}
      <button type="submit" name="collapseDir" style="padding: 0px 0px;border: none;"
      onclick="collapse_item.value='{$itemValue[1]}{$itemName}';user_action.value='collapse';">
        <img src='{$minusImg}' title="{$labels.collapse_tree}" alt="{$labels.collapse_tree}"/>
      </button>
      <img src='../../../third_party/ext-js/images/default/tree/folder.gif'/>
      {$itemName}</li>
      {treeMenu data=$itemValue[0] level=$level+1}
    {else} {* FILE *}
      <img src='{$elbowImg}'/>
      <img src='../../../third_party/ext-js/images/default/tree/leaf.gif'/>
      {$itemName}</li>
    {/if}
    {assign var=cnt value=$cnt+1}
  {/foreach}
  </ul>
{/function}

<body onunload="dialog_onUnload(bug_dialog)" onload="dialog_onLoad(bug_dialog)">
<h1 class="title">
  {$gui->pageTitle|escape} 
</h1>

{include file="inc_update.tpl" user_feedback=$gui->msg}
<div class="workBack">
  <form action="lib/testcases/scriptAdd.php" method="post">
    <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}"/>
    <input type="hidden" name="tplan_id" id="tplan_id" value="{$gui->tplan_id}"/>
    <input type="hidden" name="tcversion_id" id="tcversion_id" value="{$gui->tcversion_id}"/>
    <input type="hidden" name="user_action" id="user_action" value=""/>
    <input type="hidden" name="expand_item" id="expand_item" value=""/>
    <input type="hidden" name="collapse_item" id="collapse_item" value=""/>
    {foreach from=$gui->codeTrackerMetaData.projects key=idx item=value}
      <input type="hidden" name="projectKey[{$idx}]" value="{$value}"/>
    {/foreach}
    {foreach from=$gui->codeTrackerMetaData.repos key=idx item=value}
      <input type="hidden" name="repositoryName[{$idx}]" value="{$value}"/>
    {/foreach}
    {foreach from=$gui->codeTrackerMetaData.branches key=idx item=value}
      <input type="hidden" name="branchName[{$idx}]" value="{$value}"/>
    {/foreach}
    {foreach from=$gui->codeTrackerMetaData.files key=idx item=value}
      {foreach from=$value key=subIdx item=subValue}
        {if is_array($subValue)}
          <input type="hidden" name="files[{$idx}][{$subIdx}]" value="DIRECTORY"/>
        {else}
          <input type="hidden" name="files[{$idx}][{$subIdx}]" value="{$subValue}"/>
        {/if}
      {/foreach}
    {/foreach}

    <p>
    <a style="font-weight:normal" target="_blank" href="{$gui->codeTrackerCfg->createCodeURL}">
    {$labels.link_cts_create_script}({$gui->codeTrackerCfg->VerboseID|escape})</a>
    </p>  
    {if $gui->user_action != 'create'}
      <p class="label">{$gui->codeTrackerCfg->VerboseType|escape}<br>{$labels.cts_project_key}
        <select id="project_key" name="project_key"
        onchange="user_action.value='projectSelected';
                  repository_name.value='';
                  branch_name.value='';
                  this.form.submit()">
       {html_options options=$gui->codeTrackerMetaData.projects
        selected = $gui->project_key
       }
       </select>
      </p>
      <p class="label">{$labels.cts_repo_name}
        <select id="repository_name" name="repository_name"
        onchange="user_action.value='repoSelected';
                  branch_name.value='';
                  this.form.submit()">
        <option disabled selected value></option>
       {html_options options=$gui->codeTrackerMetaData.repos
        selected = $gui->repository_name
       }
       </select>
      </p>
      <p class="label">{$labels.cts_branch_name}
        <select id="branch_name" name="branch_name"
        onchange="user_action.value='branchSelected';
        this.form.submit()">
        <option disabled selected value></option>
       {html_options options=$gui->codeTrackerMetaData.branches
        selected = $gui->branch_name
       }
       </select>
      </p>

      {if $gui->project_key != '' && $gui->repository_name != '' && $gui->branch_name != ''}
        <p class="label">{$labels.cts_commit_id}
          <input type="text" id="commit_id" name="commit_id" value="" style="width: 300px"/>
          <select style="width: 18px" onchange="commit_id.value=this.value;">
          <option selected value></option>
          {html_options options=$gui->codeTrackerMetaData.commits
           selected = $gui->commit_id
          }
          </select>
        </p>
      {/if}

      {if $gui->project_key != '' && $gui->repository_name != '' && !is_null($gui->codeTrackerMetaData.files)}
        <p class="label">{$labels.cts_script_path}
        <input type="text" id="code_path" name="code_path" value=""/></p>

        <p class="label">{$labels.script_id}</p>
        <div id="repoItems" style="background-color: white;width: 400px;height: 300px;
          overflow-x: scroll;overflow-y: scroll;margin-left: 20px;padding-left: 0px;
          border: 1px solid black">
          {treeMenu data=$gui->codeTrackerMetaData.files}
        </div>
      {/if}
    {/if}

    <div class="groupBtn">
      {if $gui->user_action != 'create'}
        <input type="submit" value="{$labels.btn_save}" 
             onclick="user_action.value='create';return dialog_onSubmit(bug_dialog)" />
      {/if}

      <input type="button" value="{$labels.btn_close}" onclick="window.close()" />
    </div>
  </form>
</div>

</body>
</html>
