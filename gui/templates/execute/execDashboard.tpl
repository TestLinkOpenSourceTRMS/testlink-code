{*
TestLink Open Source Project - http://testlink.sourceforge.net/

@filesource	execDashboard.tpl
@internal revisions
@since 1.9.10
*}
{$title_sep=$smarty.const.TITLE_SEP}
{$title_sep_type3=$smarty.const.TITLE_SEP_TYPE3}
{lang_get var='labels'
          s='build_is_closed,test_cases_cannot_be_executed,build,builds_notes,testplan,
             test_plan_notes,platform,platform_description'}

{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl" popup='yes' openHead='yes'}
{if #ROUND_EXEC_HISTORY# || #ROUND_TC_TITLE# || #ROUND_TC_SPEC#}
  {$round_enabled=1}
  <script language="JavaScript" src="{$basehref}gui/niftycube/niftycube.js" type="text/javascript"></script>
{/if}
</head>
<body>

<h1 class="title">
{$gui->pageTitlePrefix}  
{$labels.testplan} {$gui->testplan_name|escape} {$title_sep_type3} {$labels.build} {$gui->build_name|escape}
{if $gui->platform_info.name != ""}
  {$title_sep_type3}{$labels.platform}{$title_sep}{$gui->platform_info.name|escape}
{/if}
</h1>
<div id="main_content" class="workBack">
  {if $gui->build_is_open == 0}
    <div class="messages" style="align:center;">
    {$labels.build_is_closed}<br />
    {$labels.test_cases_cannot_be_executed}
    </div>
    <br />
  {/if}

  <div style="color: rgb(21, 66, 139);font-weight: bold;font-size: 11px;font-family: tahoma,arial,verdana,sans-serif;">
  {$labels.testplan} {$gui->testplan_name|escape}
  </div>
  <div id="testplan_notes" class="exec_additional_info">
  {if $gui->testPlanEditorType == 'none'}{$gui->testplan_notes|nl2br}{else}{$gui->testplan_notes}{/if}
  {if $gui->testplan_cfields neq ''} <div id="cfields_testplan" class="custom_field_container">{$gui->testplan_cfields}</div>{/if}
  </div>

  {if $gui->platform_info.id > 0}
    <div style="color: rgb(21, 66, 139);font-weight: bold;font-size: 11px;font-family: tahoma,arial,verdana,sans-serif;">
    {$labels.platform} {$gui->platform_info.name|escape}
    </div>
    <div id="platform_notes" class="exec_additional_info">
	{if $gui->platformEditorType == 'none'}{$gui->platform_info.notes|nl2br}{else}{$gui->platform_info.notes}{/if}
    </div>
  {/if}

  <div style="color: rgb(21, 66, 139);font-weight: bold;font-size: 11px;font-family: tahoma,arial,verdana,sans-serif;">
  {$labels.build} {$gui->build_name|escape}
  </div>
  <div id="build_notes" class="exec_additional_info">
  {if $gui->buildEditorType == 'none'}{$gui->build_notes|nl2br}{else}{$gui->build_notes}{/if}
  {if $gui->build_cfields != ''} <div id="cfields_build" class="custom_field_container">{$gui->build_cfields}</div>{/if}
  </div>
</div>
</body>
</html>