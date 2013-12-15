{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource keywordsAssign.tpl
*}
{lang_get var="labels" s='keyword_assignment,keyword_assignment_empty_tsuite'}


{include file="inc_head.tpl" openHead='yes'}
<script language="JavaScript" src="gui/javascript/OptionTransfer.js" type="text/javascript"></script>
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>

{if $can_do} 
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
{if $can_do} 
	onLoad="{$opt_cfg->js_ot_name}.init(document.forms[0])"
{/if}	
>

{* improved feedback *}
<div class="workBack">
  <h1 class="title">{$labels.keyword_assignment}</h1>
  {if $keyword_assignment_subtitle neq ''}
    <h2>{$keyword_assignment_subtitle|escape}</h2>
  {/if}

	{if $can_do} 
    {include file="inc_update.tpl" result=$sqlResult item=$level action='updated'}
    <div style="margin-top: 25px;">
    	<form method="post" action="lib/keywords/keywordsAssign.php?id={$data}&amp;edit={$level}">
      {include file="opt_transfer.inc.tpl" option_transfer=$opt_cfg}
	    <br />
    	<input type="submit" name="assign{$level}" value="{lang_get s='btn_save'}" />
    	</form>
    </div>
  {else}
    {$labels.keyword_assignment_empty_tsuite}
  {/if}  
</div>
</body>
</html>