{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource keywordsAssign.tpl
*}
{lang_get var="labels" s='keyword_assignment,keyword_assignment_empty_tsuite,
                          btn_save,assignToFilteredTestCases,
                          tcversion_executed_keyword_assignment_blocked'}


{include file="inc_head.tpl" openHead='yes'}
<script language="JavaScript" src="gui/javascript/OptionTransfer.js" type="text/javascript"></script>
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>

{if $gui->can_do} 
<script type="text/javascript" language="JavaScript">
var {$opt_cfg->js_ot_name} = new OptionTransfer("{$opt_cfg->from->name}","{$opt_cfg->to->name}");
{$opt_cfg->js_ot_name}.saveRemovedLeftOptions("{$opt_cfg->js_ot_name}_removedLeft");
{$opt_cfg->js_ot_name}.saveRemovedRightOptions("{$opt_cfg->js_ot_name}_removedRight");
{$opt_cfg->js_ot_name}.saveAddedLeftOptions("{$opt_cfg->js_ot_name}_addedLeft");
{$opt_cfg->js_ot_name}.saveAddedRightOptions("{$opt_cfg->js_ot_name}_addedRight");
{$opt_cfg->js_ot_name}.saveNewLeftOptions("{$opt_cfg->js_ot_name}_newLeft");
{$opt_cfg->js_ot_name}.saveNewRightOptions("{$opt_cfg->js_ot_name}_newRight");
</script>
{/if}
</head>

<body 
{if $gui->can_do} 
	onLoad="{$opt_cfg->js_ot_name}.init(document.forms[0])"
{/if}	
>

<div class="workBack">
 {include file="inc_update.tpl" result=$sqlResult item=$level action='updated'}

  <h1 class="title">{$labels.keyword_assignment}</h1>
  {if $gui->keyword_assignment_subtitle neq ''}
    <h2>{$gui->keyword_assignment_subtitle|escape}</h2>
  {/if}

	{if $gui->can_do} 
    <div style="margin-top: 25px;">
    	<form method="post" action="lib/keywords/keywordsAssign.php?id={$gui->id}&amp;edit={$gui->level}">
        <input type="hidden" name="form_token" id="form_token" value="{$gui->form_token}"> 

      {if $gui->level == 'testsuite'}
        {$labels.assignToFilteredTestCases}
        <input type="checkbox" name="useFilteredSet" id="useFilteredSet" value="1" 
               {if $gui->useFilteredSet} checked {/if} />
      {/if}

      {include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
	    <br />
      {if $gui->hasBeenExecuted == 0}
    	<input type="submit" name="assign{$gui->level}" id="assign{$gui->level}" value="{$labels.btn_save}" />
      {else}
        {$labels.tcversion_executed_keyword_assignment_blocked}
      {/if}
    	</form>
    </div>
  {else}
    {$labels.keyword_assignment_empty_tsuite}
  {/if}  
</div>
</body>
</html>