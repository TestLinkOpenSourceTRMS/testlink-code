{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@uses tctitle.inc.tpl
*}

{* variable $tco will be available in included templates *}
{$tco = $inc_tcbody_testcase}
{include file="testcases/tctitle.inc.tpl"} 
<!-- ------------------------------------------- -->

{if $inc_tcbody_cf.after_title neq ''}
  <div style="padding: 5px 3px 4px 5px;"> 
    <div id="cf_after_title" style="padding: 5px 3px 4px 5px;"
         class="custom_field_container">
      {$inc_tcbody_cf.after_title}
    </div>
  </div  
{/if}

<div class="mainAttrContainer"> 
  <div class="summaryCONTAINER">
    {if $inc_tcbody_cf.before_summary neq ''}
      <div id="cf_before_summary"
            class="custom_field_container">
        {$inc_tcbody_cf.before_summary}
      </div>
      <br>
    {/if}
    <div class="labelHolder">{$inc_tcbody_labels.summary}</div>
    <div>{if $inc_tcbody_editor_type == 'none'}{$tco.summary|nl2br}{else}{$tco.summary}{/if}</div>

    {if $inc_tcbody_cf.after_summary neq ''}
      <br>
      <div id="cf_after_summary"
            class="custom_field_container">
        {$inc_tcbody_cf.after_summary}
      </div>
    {/if}
  </div>

  <div class="spaceOne" style="margin-top:35px;"></div>

  <div class="preconditionsCONTAINER">
    {if $inc_tcbody_cf.before_preconditions neq ''}
      <div id="cf_before_preconditions"
            class="custom_field_container">
        {$inc_tcbody_cf.before_preconditions}
      </div>
      <br>
    {/if}
    <div class="labelHolder">{$inc_tcbody_labels.preconditions}</div>
    <div>{if $inc_tcbody_editor_type == 'none'}{$tco.preconditions|nl2br}{else}{$tco.preconditions}{/if}</div>
    {if $inc_tcbody_cf.after_summary neq ''}
      <br>
      <div id="cf_after_preconditions"
            class="custom_field_container">
        {$inc_tcbody_cf.after_preconditions}
      </div>
    {/if}
  </div>
  
  {if $inc_tcbody_cf.before_steps_results neq ''}
    <div class="CFBeforeStepsCONTAINER">
      <div class="custom_field_container">
        {$inc_tcbody_cf.before_steps_results}
      </div>
    </div>
  {/if}

</div>
<hr>
