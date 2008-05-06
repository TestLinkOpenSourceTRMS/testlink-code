{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: newest_tcversions.tpl,v 1.5 2008/05/06 06:26:07 franciscom Exp $
Purpose: smarty template - 
rev:
    20080126 - franciscom - external tcase id
*}

{include file="inc_head.tpl"}
{include file="inc_jsCheckboxes.tpl"}

{lang_get s='help' var='common_prefix'}
{assign var="text_hint" value="$common_prefix"}

{lang_get var='labels' 
          s='testproject,test_plan,th_id,th_test_case,linked_version,newest_version' }

<body>
<h1 class="title"> {lang_get s='title_newest_tcversions'} 
{include file="inc_help.tpl" help="newest_tcversions" locale=$locale 
         inc_help_alt="$text_hint" inc_help_title="$text_hint"}


</h1>
<form method="post" id="newest_tcversions.tpl">
  <table>
  <tr>
   <td>{$labels.testproject}{$smarty.const.TITLE_SEP}</td>
   <td>{$tproject_name|escape}</td>
  </tr>
  
  <tr>
    <td>{$labels.test_plan}</td>
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
		    <td>{$labels.th_id}</td> 
		    <td>{$labels.th_test_case}</td>
		    <td>{$labels.linked_version}</td>
		    <td>{$labels.newest_version}</td>
		    <td>&nbsp;</td>
      </tr>   
    
      {foreach from=$testcases item=tc}
      <tr>
        <td style="align:rigth;" > {$tcasePrefix}{$tc.tc_external_id} </td>  
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
