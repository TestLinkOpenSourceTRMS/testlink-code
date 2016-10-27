{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource searchForm.tpl
Purpose: show form 

@internal revisions
@since 1.9.16
*}

{$cfg_section=$smarty.template|basename|replace:".inc.tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels" s='search_items,btn_find,logical_or,logical_and'}
<div style="margin: 1px;">
<form method="post" action="{$basehref}lib/search/search.php">
  <input type="hidden" name="doAction" id="doAction" value="doSearch">
  <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">

  <table class="simple" border="1" style="width:90%">     
    <tr>
     <td style="width: 2%">{$labels.search_items|escape}</td>
     <td style="width: 10%"><input style="width: 20rem" type="text" name="target" id="target" 
                value="{$gui->target|escape}"></td>
     <td style="width: 30%">
        <fieldset style="width: 20%">
        <input type="radio" name="and_or"
               value="or" {$gui->or_checked} />{$labels.logical_or}
        <input type="radio" name="and_or"
               value="and" {$gui->and_checked} />{$labels.logical_and}
        </fieldset>
      </tr>
    <tr>
    <td style="width: 2%"></td> 
    
    <td style="width: 30%" colspan="1"> 
      TEST CASE<br>
      <input type="checkbox" name="tc_title" value="1">Title<br>
      <input type="checkbox" name="tc_summary" value="1">Summary<br>
      <input type="checkbox" name="tc_preconditions" value="1">Preconditions<br>
      <input type="checkbox" name="tc_steps" value="1">Steps<br>
      <input type="checkbox" name="tc_expected_results" value="1">Expected results<br>
      <input type="checkbox" name="tc_id" value="1">id<br>
    </td>
    <td style="width: 30%" colspan="1"> 
      TEST SUITE<br>
      <input type="checkbox" name="ts_title" value="1">Title<br>
      <input type="checkbox" name="ts_summary" value="1">Details<br>
    </td>
    
    <td style="width: 30%" colspan="1"> 
      Requirement Spec<br>
      <input type="checkbox" name="rs_title" value="1">Title<br>
      <input type="checkbox" name="rs_scope" value="1">Scope<br>
    </td>

    <td style="width: 30%" colspan="1"> 
      Requirement Spec<br>
      <input type="checkbox" name="rq_title" value="1">Title<br>
      <input type="checkbox" name="rq_scope" value="1">Scope<br>
      <input type="checkbox" name="rq_doc_id" value="1">Scope<br>
    </td>
    
    </tr>

  </table>
  
  <p style="padding-left: 20px;">
    <input type="submit" name="doSearch" value="{$labels.btn_find}" />
  </p>
</form>
</div>