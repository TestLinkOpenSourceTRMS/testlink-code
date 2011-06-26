{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 

@filesource	newest_tcversions.tpl
Purpose: smarty template - 
*}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}

{lang_get var='labels' 
          s='testproject,test_plan,th_id,th_test_case,title_newest_tcversions,
             linked_version,newest_version,compare'}

</head>
<body>

<h1 class="title"> {$labels.title_newest_tcversions} 
	{include file="inc_help.tpl" helptopic="hlp_planTcModified" show_help_icon=true}
</h1>

<form method="post" id="newest_tcversions.tpl">
  <table>
  <tr>
   <td>{$labels.testproject}{$smarty.const.TITLE_SEP}</td>
   <td>{$gui->tproject_name|escape}</td>
  </tr>
  
  <tr>
    <td>{$labels.test_plan}</td>
    <td>
      <select name="tplan_id" id="tplan_id" onchange="this.form.submit()">  
         {html_options options=$gui->tplans selected=$gui->tplan_id}
      </select>
    </td>
  </tr>
  </table>
</form>

{if $gui->show_details}
  <div class="workBack">

    <table class="simple_tableruler" cellspacing="0" style="font-size:small;" width="100%">
      <tr style="background-color:blue;font-weight:bold;color:white">
		    <td>{$labels.th_test_case}</td>
		    <td>{$labels.linked_version}</td>
		    <td>{$labels.newest_version}</td>
		    <td>{$labels.compare}</td>
      </tr>   
    
      {foreach from=$gui->testcases item=tc}
      <tr>
		<td> {$tc.path}{$gui->tcasePrefix|escape}{$tc.tc_external_id|escape}:{$tc.name|escape} </td>  
		<td align="center"> {$tc.version|escape} </td>
		<td align="center"> {$tc.newest_version|escape} </td>
		</td>
		<td align="center">
			<a href="lib/testcases/tcCompareVersions.php?testcase_id={$tc.tc_id}&version_left={$tc.version}&version_right={$tc.newest_version}&compare_selected_versions=1&use_html_comp=1" target="_blank">
			<img src="{$smarty.const.TL_THEME_IMG_DIR}/magnifier.png"></img></a>
		</td>
      </tr>
  	  {/foreach}
  	</table>
  </div>
{else}
	<h2>{$gui->user_feedback}</h2>
{/if}

</body>
</html>