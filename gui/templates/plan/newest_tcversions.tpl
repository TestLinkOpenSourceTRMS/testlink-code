{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: newest_tcversions.tpl,v 1.12 2010/01/21 22:06:18 franciscom Exp $
Purpose: smarty template - 
rev:
    20080126 - franciscom - external tcase id
*}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}

{lang_get var='labels' 
          s='testproject,test_plan,th_id,th_test_case,title_newest_tcversions,linked_version,newest_version' }

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

{if $gui->show_details }
  <div class="workBack" style="height: 380px; overflow-y: auto;">

    <table cellspacing="0" style="font-size:small;" width="100%">
      <tr style="background-color:blue;font-weight:bold;color:white">
		    {* <td>{$labels.th_id}</td>  *}
		    <td>{$labels.th_test_case}</td>
		    <td>{$labels.linked_version}</td>
		    <td>{$labels.newest_version}</td>
		    <td>&nbsp;</td>
      </tr>   
    
      {foreach from=$gui->testcases item=tc}
      <tr>
		{* <td style="align:right;"> {$gui->tcasePrefix|escape}{$tc.tc_external_id|escape} </td>  *} 
		<td> {$tc.path}{$gui->tcasePrefix|escape}{$tc.tc_external_id|escape}:{$tc.name|escape} </td>  
		<td> {$tc.version|escape} </td>
		<td> {$tc.newest_version|escape} </td>
      </tr>
  	  {/foreach}
  	</table>
  </div>
{else}
	<h2>{$gui->user_feedback}</h2>
{/if}

</body>
</html>
