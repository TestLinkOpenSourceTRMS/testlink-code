{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource tcSearchForm.tpl
Purpose: show form for search through test cases in test specification

@internal revisions
@since 1.9.13

*}
{$cfg_section=$smarty.template|basename|replace:".inc.tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels" 
          s='title_search_tcs,caption_search_form,th_tcid,th_tcversion,edited_by,status,
             th_title,summary,steps,expected_results,keyword,custom_field,created_by,jolly_hint,
             search_type_like,preconditions,filter_mode_and,test_importance,search_prefix_ignored,
             creation_date_from,creation_date_to,modification_date_from,modification_date_to,
             custom_field_value,btn_find,requirement_document_id,show_calender,clear_date,jolly'}


<div style="margin: 1px;">
<form method="post" action="{$basehref}lib/testcases/tcSearch.php">
  <input type="hidden" name="doAction" id="doAction" value="doSearch">
  <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">
  <table class="simple" style="width:100%">
    <tr>
     <td colspan="8">
     <img src="{$tlImages.info}" title =" {$labels.filter_mode_and} {$gui->search_important_notice|escape}.
                                          {$labels.search_prefix_ignored|escape}">
     </td>
    </tr>
    <tr>
      <td>{$labels.th_tcid}</td>
      <td><input type="text" name="targetTestCase" id="TCID"  
                 size="{#TC_ID_SIZE#}" maxlength="{#TC_ID_MAXLEN#}" value="{$gui->targetTestCase|escape}"/></td>

      <td>{$labels.th_tcversion}</td>
      <td><input type="text" name="version"
                 size="{#VERSION_SIZE#}" maxlength="{#VERSION_MAXLEN#}" value="{$gui->tcversion|escape}" /></td>
      
      <td>{$labels.th_title}</td>
      <td><input type="text" name="name" id="name" 
           value="{$gui->name|escape}"
           size="{#TCNAME_SIZE#}" maxlength="{#TCNAME_MAXLEN#}" /></td>
    </tr>

    <tr>
    {if $session['testprojectOptions']->testPriorityEnabled}
        <td>{$labels.test_importance}</td>
        <td>
          <select name="importance">
           {html_options options=$gui->option_importance selected=$gui->importance}
          </select>
        </td>
    {/if}
      <td>{$labels.status}</td>
        <td>
          <select name="status">
           {html_options options=$gui->domainTCStatus selected=$gui->status}
          </select>
        </td>
    </tr>

    <tr>
      <td>{$labels.summary}</td>
      <td><input type="text" name="summary" id="summary" value="{$gui->summary|escape}"
                 size="{#SUMMARY_SIZE#}" maxlength="{#SUMMARY_MAXLEN#}" /></td>

      <td>{$labels.preconditions}</td>
      <td><input type="text" name="preconditions" id="preconditions" value="{$gui->preconditions|escape}"
                 size="{#PRECONDITIONS_SIZE#}" maxlength="{#PRECONDITIONS_MAXLEN#}" /></td>

      <td>{$labels.steps}</td>
      <td><input type="text" name="steps" id="steps" value="{$gui->steps|escape}"
                 size="{#STEPS_SIZE#}" maxlength="{#STEPS_MAXLEN#}" /></td>
 
      <td>{$labels.expected_results}</td>
      <td><input type="text" name="expected_results" id="expected_results" value="{$gui->expected_results|escape}"
                 size="{#RESULTS_SIZE#}" maxlength="{#RESULTS_MAXLEN#}" /></td>
    </tr>

    <tr>
      <td>{$labels.created_by}</td>
      <td><input type="text" name="created_by" id="created_by" 
                 value="{$gui->created_by|escape}"
                 size="{#AUTHOR_SIZE#}" maxlength="{#TCNAME_MAXLEN#}" /></td>

      <td>{$labels.edited_by}</td>
      <td><input type="text" name="edited_by" id ="edited_by" value="{$gui->edited_by|escape}"
                 size="{#AUTHOR_SIZE#}" maxlength="{#TCNAME_MAXLEN#}" /></td>
    </tr>

    <tr>
      <td>{$labels.creation_date_from}</td>
      <td>
        <input type="text" name="creation_date_from" id="creation_date_from" 
               value="{$gui->creation_date_from|escape}" size="{#DATE_PICKER#}"
               onclick="showCal('creation_date_from-cal','creation_date_from','{$gsmarty_datepicker_format}');" readonly />
        
        <img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
             onclick="showCal('creation_date_from-cal','creation_date_from','{$gsmarty_datepicker_format}');" >
        <img title="{$labels.clear_date}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
               onclick="javascript:var x = document.getElementById('creation_date_from'); x.value = '';" >
        <div id="creation_date_from-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
      </td>

      <td>{$labels.creation_date_to}</td>
      <td>
        <input type="text" name="creation_date_to" id="creation_date_to" value="{$gui->creation_date_to|escape}" 
               size="{#DATE_PICKER#}"
               onclick="showCal('creation_date_to-cal','creation_date_to','{$gsmarty_datepicker_format}');" readonly />
        <img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
             onclick="showCal('creation_date_to-cal','creation_date_to','{$gsmarty_datepicker_format}');" >
        <img title="{$labels.clear_date}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
               onclick="javascript:var x = document.getElementById('creation_date_to'); x.value = '';" >
        <div id="creation_date_to-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
      </td>

      <td>{$labels.modification_date_from}</td>
      <td>
        <input type="text" name="modification_date_from" id="modification_date_from" 
        value="{$gui->modification_date_from|escape}" 
               size="{#DATE_PICKER#}"
               onclick="showCal('modification_date_from-cal','modification_date_from','{$gsmarty_datepicker_format}');" readonly />
        <img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
             onclick="showCal('modification_date_from-cal','modification_date_from','{$gsmarty_datepicker_format}');" >
        <img title="{$labels.clear_date}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
             onclick="javascript:var x = document.getElementById('modification_date_from'); x.value = '';" >
        <div id="modification_date_from-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
      </td>

      <td>{$labels.modification_date_to}</td>
      <td>
        <input type="text" name="modification_date_to" id="modification_date_to" 
        value="{$gui->modification_date_to|escape}" 
               size="{#DATE_PICKER#}"
               onclick="showCal('modification_date_to-cal','modification_date_to','{$gsmarty_datepicker_format}');" readonly />
        <img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
             onclick="showCal('modification_date_to-cal','modification_date_to','{$gsmarty_datepicker_format}');" >
        <img title="{$labels.clear_date}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
             onclick="javascript:var x = document.getElementById('modification_date_to'); x.value = '';" >
        <div id="modification_date_to-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
      </td>
    </tr>

     <tr>
      <td>{$labels.jolly}<img src="{$tlImages.info}" title="{$labels.jolly_hint}"></td>
      <td><input type="text" name="jolly" id="jolly" 
      value="{$gui->jolly|escape}"
                 size="{#SUMMARY_SIZE#}" maxlength="{#SUMMARY_MAXLEN#}" /></td>
     </tr>
    
   
    {if $gui->filter_by.keyword}
    <tr>
      <td>{$labels.keyword}</td>
      <td colspan="5"><select name="keyword_id">
          <option value="0">&nbsp;</option>
          {section name=Row loop=$gui->keywords}
          <option value="{$gui->keywords[Row]->dbID}">{$gui->keywords[Row]->name|escape}</option>
        {/section}
        </select>
      </td>
    </tr>
    {/if}
    
    {if $gui->filter_by.design_scope_custom_fields}
        <tr>
          <td>{$labels.custom_field}</td>
          <td><select name="custom_field_id">
              <option value="0">&nbsp;</option>
              {foreach from=$gui->design_cf key=cf_id item=cf}
                <option value="{$cf_id}">{$cf.label|escape}</option>
              {/foreach}
            </select>
          </td>
          </tr>
        <tr>
            <td>{$labels.custom_field_value}</td>
            <td>
            <input type="text" name="custom_field_value" 
                   size="{#CFVALUE_SIZE#}" maxlength="{#CFVALUE_MAXLEN#}"/>
          </td>
        </tr>
    {/if}
    
    {if $gui->filter_by.requirement_doc_id}
        <tr>
            <td>{$labels.requirement_document_id}</td>
            <td colspan="7">
            <input type="text" name="requirement_doc_id" id="requirement_doc_id"
                   title="{$labels.search_type_like}"
                   size="{#REQ_DOCID_SIZE#}" maxlength="{#REQ_DOCID_MAXLEN#}"/>
          </td>
        </tr>
    {/if}    
  </table>
  
  <p style="padding-left: 20px;">
    <input type="submit" name="doSearch" value="{$labels.btn_find}" />
  </p>
</form>

</div>
