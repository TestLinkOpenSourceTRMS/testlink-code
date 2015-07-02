{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource resultsNavigator.tpl
*}
{lang_get var="labels"
          s="title_nav_results,title_report_type,btn_print,test_plan,show_inactive_tplans"}

{$cfg_section=$smarty.template|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}
{include file="inc_head.tpl" openHead="yes"}

<script type="text/javascript">
function reportPrint()
{
  parent["workframe"].focus();
  parent["workframe"].print();
}

function pre_submit()
{
 document.getElementById('called_url').value=parent.workframe.location;
 return true;
}
</script>
</head>

<body>
<h1 class="title">{$labels.title_nav_results}</h1>

<div style="margin:0px; padding:0px;">
<form method="get" id="resultsNavigator" onSubmit="javascript:return pre_submit();">
  <input type="hidden" id="called_by_me" name="called_by_me" value="1" />
  <input type="hidden" id="called_url" name="called_url" value="" />

  <div class="menu_bar">
    <span>{$labels.title_report_type}
    <select name="format" onchange="this.form.submit();">
      {html_options options=$gui->reportTypes selected=$gui->selectedReportType}
    </select>
    </span>

    <span style="margin-left:20px;"><input type="button" name="print" value="{$labels.btn_print}" 
      onclick="javascript: reportPrint();" style="margin-left:5px;" /></span>
  </div>

  <div style="margin:3px" >
    <span style="padding-right: 10px">{$labels.test_plan} 
    <select name="tplan_id" onchange="pre_submit();this.form.submit()">
      {html_options options=$gui->tplans selected=$gui->tplan_id}
    </select>
    </span>
    <br>
    <span>{$labels.show_inactive_tplans}
    <input type="checkbox" {$gui->checked_show_inactive_tplans} onclick="this.form.submit();" 
           id="show_inactive_tplans" name="show_inactive_tplans" >
    </span>
    
  </div>
</form>
</div>

<div style="margin:3px; padding: 15px 0px" >
{if $gui->do_report.status_ok}
  {foreach from=$gui->menuItems item=menu}
    <span>
      <a href="{$menu.href}format={$gui->selectedReportType}&amp;tplan_id={$gui->tplan_id}
            {if $gui->checked_show_inactive_tplans}&amp;show_inactive_tplans=1{/if}" 
       target="workframe">{$menu.name}</a>
    {if $menu.directLink != ''}{$menu.toggle}{/if}
  </span>
  <br>
  {$menu.directLinkDiv}
  {/foreach}
{else}
  {$gui->do_report.msg}
{/if}
</div>


<script type="text/javascript">
{if $gui->workframe != ''}
  parent.workframe.location='{$gui->workframe}';
{/if}
</script>

</body>
</html>