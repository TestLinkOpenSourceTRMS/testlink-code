{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: newest_tcversions.tpl,v 1.1 2007/12/02 17:03:00 franciscom Exp $
Purpose: smarty template - 
*}

{include file="inc_head.tpl"}
{include file="inc_jsCheckboxes.tpl"}

{lang_get s='help' var='common_prefix'}
{assign var="text_hint" value="$common_prefix"}

<body>
{* <h1>{lang_get s='test_plan'}{$smarty.const.TITLE_SEP}{$testPlanName|escape}</h1> *}
<h1> {lang_get s='title_newest_tcversions'} 
{include file="inc_help.tpl" help="newest_tcversions" locale=$locale 
         alt="$text_hint" title="$text_hint"}


</h1>
{* onchange="pre_submit();this.form.submit()"> *}
<form method="post" id="newest_tcversions.tpl">
  <table>
  <tr>
   <td>{lang_get s='testproject'}{$smarty.const.TITLE_SEP}</td>
   <td>{$tproject_name|escape}</td>
  </tr>
  
  <tr>
    <td>{lang_get s='test_plan'}</td>
    <td>
      <select name="tplan_id" id="tplan_id" onchange="this.form.submit()">  
         {html_options options=$tplans selected=$tplan_id}
      </select>
    </td>
  </tr>
  </table>
</form>

{if $show_details }
  <div class="workBack" style="height: 380px; overflow-y: auto;">

    <table cellspacing="0" style="font-size:small;" width="100%">
      <tr style="background-color:blue;font-weight:bold;color:white">
		    <td class="tcase_id_cell">{lang_get s='th_id'}</td> 
		    <td>{lang_get s='th_test_case'}</td>
		    <td>{lang_get s='linked_version'}</td>
		    <td>{lang_get s='newest_version'}</td>
		    <td>&nbsp;</td>
      </tr>   
    
      {foreach from=$testcases item=tc}
      <tr>
        <td style="align:rigth;"> {$tc.tc_id} </td>  
        <td> {$tc.name} </td>  
        <td> {$tc.version} </td>
        <td> {$tc.newest_version} </td>
      </tr>
  	  {/foreach}
  	</table>
  </div>
{else}
	<h2>{$user_feedback}</h2>
{/if}

</body>
</html>
