{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource	keywordsAssign.tpl
Purpose: smarty template - assign keywords to one or more test cases
*}
{include file="inc_head.tpl" openHead='yes'}
<script language="JavaScript" src="gui/javascript/OptionTransfer.js" type="text/javascript"></script>
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>

{lang_get var="labels"
          s="title_keywords,menu_manage_keywords,
          menu_assign_kw_to_tc,btn_save,keyword_assignment_empty_tsuite"}


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

{* improved feedback *}
<div class="workBack">
    <h1 class="title">{$labels.title_keywords}</h1>
    {* tabs *}
    <div class="tabMenu">
    	<span class="unselected"><a href="lib/keywords/keywordsView.php?tproject_id={$gui->tproject_id}"
    			target='mainframe'>{$labels.menu_manage_keywords}</a></span> 
    	<span class="selected">{$labels.menu_assign_kw_to_tc}</span> 
    </div>

	{if $gui->can_do} 
     {if $keyword_assignment_subtitle != ''}
      <h2>{$keyword_assignment_subtitle|escape}</h2>
     {/if}
    
    {include file="inc_update.tpl" result=$gui->sqlResult item=$gui->level action='updated'}
  
    
    {* data form *}
    <div style="margin-top: 25px;">
    	<form method="post" action="lib/keywords/keywordsAssign.php?tproject_id={$gui->tproject_id}&id={$gui->id}&edit={$gui->level}">
      {include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
	    <br />
    	<input type="submit" name="assign{$gui->level}" value="{$labels.btn_save}" />
    	</form>
    </div>
  {else}
     {if $keyword_assignment_subtitle neq ''}
      <h2> {$keyword_assignment_subtitle}</h2>
     {/if}
    {$labels.keyword_assignment_empty_tsuite}
  {/if}  
</div>
</body>
</html>