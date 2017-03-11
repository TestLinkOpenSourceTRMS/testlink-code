{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource searchGUI.inc.tpl
Purpose: show form 

@since 1.9.17
*}

{$cfg_section=$smarty.template|basename|replace:".inc.tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels" s='search_items,btn_find,logical_or,logical_and,created_by,
                          edited_by,modification_date_to,modification_date_from,
                          custom_field,custom_field_value,creation_date_to,creation_date_from,keyword,type,status,req_status,reqspec_type,testcase,testsuite,title,clear_date,show_calendar,id,
                          summary,preconditions,steps,expected_results,details,tcase_wkf_status,search_words_or,search_words_and,
                          search_words_placeholder,search_words_on_attr,search_other_attr,req_type,search_created_by_ph,check_uncheck_all_checkboxes,
                          scope,requirement,req_specification,req_document_id,id'}

<div style="margin: 1px;">
<form method="post" name="fullTextSearch" id="fullTextSearch" 
      action="{$basehref}lib/search/search.php">
  <input type="hidden" name="doAction" id="doAction" value="doSearch">
  <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">
  <input type="hidden" name="caller" id="caller" value="{$gui->caller}">

  {* used as memory for the check/uncheck all checkbox javascript logic *}
  <input type="hidden" name="mmTestCaseCell" id="mmTestCaseCell"  value="1" />
  <input type="hidden" name="mmTestSuiteCell" id="mmTestSuiteCell"  value="1" />
  <input type="hidden" name="mmReqSpecCell" id="mmReqSpecCell"  value="1" />
  <input type="hidden" name="mmReqCell" id="mmReqCell"  value="1" />



  <table class="simple" style="width:99%">     
    <tr>
     <td style="width: 20%" colspan="5">
         <select name="and_or" id="and_or">
          <option value="or" {$gui->or_selected} >{$labels.search_words_or|escape}
          </option>
          <option value="and" {$gui->and_selected} >{$labels.search_words_and|escape}</option>
         </select>
         <input style="width: 20rem" type="text" name="target" id="target" 
                value="{$gui->target|escape}" 
                placeholder="{$labels.search_words_placeholder|escape}">
     </td>
    </tr>

    <tr>
    <td style="width: 2%" colspan="5">&nbsp;&nbsp;</td> 
    </tr>

    <tr>
    <td style="width: 2%" colspan="5">{$labels.search_words_on_attr|escape}</td> 
    </tr>

    <tr>
    <td style="width: 2%"></td> 
    
    <td style="width: 30%" colspan="1" id="testCaseCell"> 
      <img src="{$tlImages.toggle_all}" 
           title="{$labels.check_uncheck_all_checkboxes}"
           onclick='cs_all_checkbox_in_div("testCaseCell","tc_","mmTestCaseCell");'>
      <b>{$labels.testcase}</b><br>
      <input type="checkbox" name="tc_title" id="tc_title" value="1" {if $gui->tc_title}checked{/if}>{$labels.title}<br>
      <input type="checkbox" name="tc_summary" id="tc_summary" value="1" {if $gui->tc_summary}checked{/if}>{$labels.summary}<br>
      <input type="checkbox" name="tc_preconditions" id="tc_preconditions" 
             value="1" {if $gui->tc_preconditions}checked{/if}>{$labels.preconditions}<br>
      <input type="checkbox" name="tc_steps" id="tc_steps" 
             value="1" {if $gui->tc_steps}checked{/if}>{$labels.steps}<br>
      <input type="checkbox" name="tc_expected_results" id="tc_expected_results"
             value="1" {if $gui->tc_expected_results}checked{/if}>{$labels.expected_results}<br>
      <input type="checkbox" name="tc_id" id="tc_id" 
             value="1" {if $gui->tc_id}checked{/if}>{$labels.id}<br>
    </td>

    <td style="width: 30%" colspan="1" id="testSuiteCell"> 
      <img src="{$tlImages.toggle_all}" 
           title="{$labels.check_uncheck_all_checkboxes}"
           onclick='cs_all_checkbox_in_div("testSuiteCell","ts_","mmTestSuiteCell");'>
      <b>{$labels.testsuite}</b><br>
      <input type="checkbox" name="ts_title" id="ts_title" value="1" {if $gui->ts_title}checked{/if}>{$labels.title}<br>
      <input type="checkbox" name="ts_summary" id="ts_summary" value="1" {if $gui->ts_summary}checked{/if}>{$labels.details}<br>
    </td>
    
    <td style="width: 20%" colspan="1" id="reqSpecCell"> 
      {if $gui->reqEnabled}
      <img src="{$tlImages.toggle_all}" 
           title="{$labels.check_uncheck_all_checkboxes}"
           onclick='cs_all_checkbox_in_div("reqSpecCell","rs_","mmReqSpecCell");'>
        <b>{$labels.req_specification}</b><br>
        <input type="checkbox" name="rs_title" id="rs_title" value="1" {if $gui->rs_title}checked{/if} >{$labels.title}<br>
        <input type="checkbox" name="rs_scope" id="rs_scope" value="1" {if $gui->rs_scope}checked{/if}>{$labels.scope}<br>
      {/if}
    </td>

    <td style="width: 30%" colspan="1" id="reqCell"> 
      {if $gui->reqEnabled}
      <img src="{$tlImages.toggle_all}" 
           title="{$labels.check_uncheck_all_checkboxes}"
           onclick='cs_all_checkbox_in_div("reqCell","rq_","mmReqCell");'>
        <b>{$labels.requirement}</b><br>
        <input type="checkbox" name="rq_title" id="rq_title"  value="1" {if $gui->rq_title}checked{/if}>{$labels.title}<br>
        <input type="checkbox" name="rq_scope" id="rq_scope"  value="1" {if $gui->rq_scope}checked{/if}>{$labels.scope}<br>
        <input type="checkbox" name="rq_doc_id" id="rq_doc_id" value="1" {if $gui->rq_doc_id}checked{/if}>{$labels.req_document_id}<br>
      {/if}    
    </td>
    
    </tr>

    <tr>
    <td style="width: 2%" colspan="5">&nbsp;&nbsp;</td> 
    </tr>

    <tr>
    <td style="width: 2%" colspan="5">{$labels.search_other_attr|escape}</td> 
    </tr>

    <tr>
      <td>&nbsp;</td>
      <td>{$labels.created_by}
      <input type="text" name="created_by" id="created_by" 
                 value="{$gui->created_by|escape}"
                 size="{#AUTHOR_SIZE#}" maxlength="{#TCNAME_MAXLEN#}"/>
      <img src="{$tlImages.info_small}" title="{$labels.search_created_by_ph|escape}">

      <br>{$labels.edited_by}
      <input type="text" name="edited_by" id ="edited_by" value="{$gui->edited_by|escape}"
                 size="{#AUTHOR_SIZE#}" maxlength="{#TCNAME_MAXLEN#}" />
<img src="{$tlImages.info_small}" title="{$labels.search_created_by_ph|escape}">

      </td>
   

    {if $gui->filter_by.keyword}
      <td>{$labels.keyword}
         <select name="keyword_id">
          <option value="0">&nbsp;</option>
          {foreach from=$gui->keywords key=kw_id item=kw_name}
            <option value="{$kw_id}" {if $kw_id == $gui->keyword_id} selected {/if}>
              {$gui->keywords[$kw_id].keyword|escape}
            </option>
          {/foreach}
         </select>
      </td>
    {else}
      <td>&nbsp</td>  
    {/if}

    {if $gui->filter_by.custom_fields}
          <td colspan="2">{$labels.custom_field}
          <select name="custom_field_id">
              <option value="0">&nbsp;</option>
              {foreach from=$gui->cf key=cf_id item=cf}
                <option value="{$cf_id}" {if $cf_id == $gui->custom_field_id} selected {/if}>{$cf.label|escape}
                </option>
              {/foreach}
            </select>
          <br>
          {$labels.custom_field_value}
            <input type="text" name="custom_field_value" value="{$gui->custom_field_value}"
                   size="{#CFVALUE_SIZE#}" maxlength="{#CFVALUE_MAXLEN#}"/>
          </td>
    {/if}

   </tr>
    <tr>
    <td style="width: 2%" colspan="5">&nbsp;&nbsp;</td> 
    </tr>
   
      <tr>
      <td>&nbsp;</td>      
      <td>{$labels.creation_date_from}
        <input type="text" name="creation_date_from" id="creation_date_from" 
               value="{$gui->creation_date_from|escape}" size="{#DATE_PICKER#}"
               onclick="showCal('creation_date_from-cal','creation_date_from','{$gsmarty_datepicker_format}');" readonly />
        
        <img title="{$labels.show_calendar}" src="{$tlImages.calendar}"
             onclick="showCal('creation_date_from-cal','creation_date_from','{$gsmarty_datepicker_format}');" >

        <img title="{$labels.clear_date}" src="{$tlImages.clear}"
               onclick="javascript:var x = document.getElementById('creation_date_from'); x.value = '';" >
        <div id="creation_date_from-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>

       <br> 
      {$labels.creation_date_to}
  
        <input type="text" name="creation_date_to" id="creation_date_to" value="{$gui->creation_date_to|escape}" 
               size="{#DATE_PICKER#}"
               onclick="showCal('creation_date_to-cal','creation_date_to','{$gsmarty_datepicker_format}');" readonly />
        <img title="{$labels.show_calendar}" src="{$tlImages.calendar}"
             onclick="showCal('creation_date_to-cal','creation_date_to','{$gsmarty_datepicker_format}');" >
        <img title="{$labels.clear_date}" src="{$tlImages.clear}"
               onclick="javascript:var x = document.getElementById('creation_date_to'); x.value = '';" >
        <div id="creation_date_to-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
  
      <br>
      {$labels.modification_date_from}
      
        <input type="text" name="modification_date_from" id="modification_date_from" 
        value="{$gui->modification_date_from|escape}" 
               size="{#DATE_PICKER#}"
               onclick="showCal('modification_date_from-cal','modification_date_from','{$gsmarty_datepicker_format}');" readonly />
        <img title="{$labels.show_calendar}" src="{$tlImages.calendar}"
             onclick="showCal('modification_date_from-cal','modification_date_from','{$gsmarty_datepicker_format}');" >
        <img title="{$labels.clear_date}" src="{$tlImages.clear}"
             onclick="javascript:var x = document.getElementById('modification_date_from'); x.value = '';" >
        <div id="modification_date_from-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
      <br>

      {$labels.modification_date_to}
      
        <input type="text" name="modification_date_to" id="modification_date_to" 
        value="{$gui->modification_date_to|escape}" 
               size="{#DATE_PICKER#}"
               onclick="showCal('modification_date_to-cal','modification_date_to','{$gsmarty_datepicker_format}');" readonly />
        <img title="{$labels.show_calendar}" src="{$tlImages.calendar}"
             onclick="showCal('modification_date_to-cal','modification_date_to','{$gsmarty_datepicker_format}');" >
        <img title="{$labels.clear_date}" src="{$tlImages.clear}"
             onclick="javascript:var x = document.getElementById('modification_date_to'); x.value = '';" >
        <div id="modification_date_to-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
      </td>

      <td colspan="1">

      {$labels.tcase_wkf_status}
        <select name="tcWKFStatus" id="tcWKFStatus">
          <option value="">&nbsp;</option>
            {html_options options=$gui->tcWKFStatusDomain  selected=$gui->tcWKFStatus}
          </select>
      </td>

      <td colspan="2">
        {if $gui->reqEnabled}
          {$labels.req_type}
          <select name="reqType" id="reqType">
            <option value="">&nbsp;</option>
              {html_options options=$gui->reqTypes  selected=$gui->reqType}
            </select>

         <br>   
         {$labels.req_status}
          <select name="reqStatus">
          <option value="">&nbsp;</option>
          {html_options options=$gui->reqStatusDomain selected=$gui->reqStatus}
          </select>
        {/if}
      </td>

    </tr>

  </table>
  
  <p style="padding-left: 20px;">
    <input type="submit" name="doSearch" value="{$labels.btn_find}" />
  </p>
  {if $gui->forceSearch}
    <script type="text/javascript">document.getElementById("fullTextSearch").submit();</script>
  {/if}
</form>
</div>