{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource keywordsAssign.tpl
*}
{$cfg_section = $smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

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

{if $gui->caller == ''}
  {include file="aside.tpl"}
{/if}

{if $gui->caller == ''}
<div id="main-content">
{/if}

<div class="workBack">
 {include file="inc_update.tpl" result=$sqlResult item=$gui->level action='updated'}

  <h1 class="{#TITLE_CLASS#}">{$labels.keyword_assignment}</h1>
  {if $gui->keyword_assignment_subtitle neq ''}
    <h2>{$gui->keyword_assignment_subtitle|escape}</h2>
  {/if}

	{if $gui->can_do} 
    <div style="margin-top: 25px;">
    	<form method="post" action="lib/keywords/keywordsAssign.php?id={$gui->id}&edit={$gui->level}">
        <input type="hidden" name="form_token" id="form_token" value="{$gui->form_token}"> 

        <input type="hidden" name="caller" id="caller" value="MYSELF"> 
        <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}"> 
        <input type="hidden" name="tplan_id" id="tplan_id" value="{$gui->tplan_id}"> 

        {$drawButton = 0}
        {if $gui->level == 'testsuite'}
          {$labels.assignToFilteredTestCases}
          <input type="checkbox" name="useFilteredSet" id="useFilteredSet" value="1" 
                {if $gui->useFilteredSet} checked {/if} />
          {$drawButton = 1}
        {else}
         {if $gui->hasBeenExecuted == 0 || $gui->canAddRemoveKWFromExecuted == 1}
            {$drawButton = 1} 
          {/if}
        {/if}

        {include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
        <br />

        {if $drawButton}
          <input class="{#BUTTON_CLASS#}" type="submit" 
                name="assign{$gui->level}" id="assign{$gui->level}"
                value="{$labels.btn_save}" />
        {else}
          {$labels.tcversion_executed_keyword_assignment_blocked}
        {/if}

    	</form>
    </div>
  {else}
    {$labels.keyword_assignment_empty_tsuite}
  {/if}  
</div>

{if $gui->caller == ''}
</div>
{/if}
{include file="supportJS.inc.tpl"}
</body>
</html>