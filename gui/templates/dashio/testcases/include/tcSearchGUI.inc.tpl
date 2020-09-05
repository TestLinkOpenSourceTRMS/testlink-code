{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource tcSearchForm.tpl
Purpose: show form for search through test cases in test specification
*}
{$cfg_section=$smarty.template|basename|replace:".inc.tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels" 
          s='title_search_tcs,caption_search_form,th_tcid,th_tcversion,edited_by,status,
             th_title,summary,steps,expected_results,keyword,custom_field,created_by,jolly_hint,
             search_type_like,preconditions,filter_mode_and,test_importance,search_prefix_ignored,
             creation_date_from,creation_date_to,modification_date_from,modification_date_to,
             custom_field_value,btn_find,requirement_document_id,show_calender,clear_date,jolly'}


<div style="margin: 8px;">
<form method="post" action="{$basehref}lib/testcases/tcSearch.php">
  <input type="hidden" name="doAction" id="doAction" value="doSearch">
  <input type="hidden" name="tproject_id" id="tproject_id" value="{$gui->tproject_id}">

  <div class="container-fluid" id="{$cfg_section}">
    <div class="row">
     <td colspan="8">
     <img src="{$tlImages.info}" title =" {$labels.filter_mode_and} {$gui->search_important_notice|escape}.
                                          {$labels.search_prefix_ignored|escape}">
     </div>
    </div>
    <div class="row">
      <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.th_tcid}</label></div>
      <div class="col-sm-2 col-md-2 col-lg-2"><input type="text" name="targetTestCase" id="TCID"  
                 size="{#TC_ID_SIZE#}" maxlength="{#TC_ID_MAXLEN#}" value="{$gui->targetTestCase|escape}"/></div>

      <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.th_tcversion}</label></div>
      <div class="col-sm-2 col-md-2 col-lg-2"><input type="text" name="version"
                 size="{#VERSION_SIZE#}" maxlength="{#VERSION_MAXLEN#}" value="{$gui->tcversion|escape}" /></div>
      
      <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.th_title}</label></div>
      <div class="col-sm-2 col-md-2 col-lg-2"><input type="text" name="name" id="name" 
           value="{$gui->name|escape}"
           size="{#TCNAME_SIZE#}" maxlength="{#TCNAME_MAXLEN#}" /></div>
    </div>

    <div class="row">
    {if $gui->tprojOpt->testPriorityEnabled}
        <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.test_importance}</div>
        <div class="col-sm-2 col-md-2 col-lg-2">
          <select name="importance" id="importance">
           {html_options options=$gui->option_importance selected=$gui->importance}
          </select>
        </div>
    {/if}
      <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.status}</div>
        <div class="col-sm-2 col-md-2 col-lg-2">
          <select name="status" id="status">
           {html_options options=$gui->domainTCStatus selected=$gui->status}
          </select>
        </div>
    </div>

    <div class="row">
      <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.summary}</div>
      <div class="col-sm-2 col-md-2 col-lg-2"><input type="text" name="summary" id="summary" value="{$gui->summary|escape}"
                 size="{#SUMMARY_SIZE#}" maxlength="{#SUMMARY_MAXLEN#}" /></div>

      <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.preconditions}</label></div>
      <div class="col-sm-2 col-md-2 col-lg-2"><input type="text" name="preconditions" id="preconditions" value="{$gui->preconditions|escape}"
                 size="{#PRECONDITIONS_SIZE#}" maxlength="{#PRECONDITIONS_MAXLEN#}" /></div>

      <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.steps}</label></div>
      <div class="col-sm-2 col-md-2 col-lg-2"><input type="text" name="steps" id="steps" value="{$gui->steps|escape}"
                 size="{#STEPS_SIZE#}" maxlength="{#STEPS_MAXLEN#}" /></div>
 
      <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.expected_results}</label></div>
      <div class="col-sm-2 col-md-2 col-lg-2"><input type="text" name="expected_results" id="expected_results" value="{$gui->expected_results|escape}"
                 size="{#RESULTS_SIZE#}" maxlength="{#RESULTS_MAXLEN#}" /></div>
    </div>

    <div class="row">
      <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.created_by}</label></div>
      <div class="col-sm-2 col-md-2 col-lg-2"><input type="text" name="created_by" id="created_by" 
                 value="{$gui->created_by|escape}"
                 size="{#AUTHOR_SIZE#}" maxlength="{#TCNAME_MAXLEN#}" /></div>

      <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.edited_by}</label></div>
      <div class="col-sm-2 col-md-2 col-lg-2"><input type="text" name="edited_by" id ="edited_by" value="{$gui->edited_by|escape}"
                 size="{#AUTHOR_SIZE#}" maxlength="{#TCNAME_MAXLEN#}" /></div>
    </div>

    <div class="row">
      <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.creation_date_from}</label></div>
      <div class="col-sm-2 col-md-2 col-lg-2">
        <input type="text" name="creation_date_from" id="creation_date_from" 
               value="{$gui->creation_date_from|escape}" size="{#DATE_PICKER#}"
               onclick="showCal('creation_date_from-cal','creation_date_from','{$gsmarty_datepicker_format}');" readonly />
        
        <img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
             onclick="showCal('creation_date_from-cal','creation_date_from','{$gsmarty_datepicker_format}');" >
        <img title="{$labels.clear_date}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
               onclick="javascript:var x = document.getElementById('creation_date_from'); x.value = '';" >
        <div id="creation_date_from-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
      </div>

      <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.creation_date_to}</label></div>
      <div class="col-sm-2 col-md-2 col-lg-2">
        <input type="text" name="creation_date_to" id="creation_date_to" value="{$gui->creation_date_to|escape}" 
               size="{#DATE_PICKER#}"
               onclick="showCal('creation_date_to-cal','creation_date_to','{$gsmarty_datepicker_format}');" readonly />
        <img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
             onclick="showCal('creation_date_to-cal','creation_date_to','{$gsmarty_datepicker_format}');" >
        <img title="{$labels.clear_date}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
               onclick="javascript:var x = document.getElementById('creation_date_to'); x.value = '';" >
        <div id="creation_date_to-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
      </div>

      <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.modification_date_from}</label></div>
      <div class="col-sm-2 col-md-2 col-lg-2">
        <input type="text" name="modification_date_from" id="modification_date_from" 
        value="{$gui->modification_date_from|escape}" 
               size="{#DATE_PICKER#}"
               onclick="showCal('modification_date_from-cal','modification_date_from','{$gsmarty_datepicker_format}');" readonly />
        <img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
             onclick="showCal('modification_date_from-cal','modification_date_from','{$gsmarty_datepicker_format}');" >
        <img title="{$labels.clear_date}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
             onclick="javascript:var x = document.getElementById('modification_date_from'); x.value = '';" >
        <div id="modification_date_from-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
      </div>

      <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.modification_date_to}</label></div>
      <div class="col-sm-2 col-md-2 col-lg-2">
        <input type="text" name="modification_date_to" id="modification_date_to" 
        value="{$gui->modification_date_to|escape}" 
               size="{#DATE_PICKER#}"
               onclick="showCal('modification_date_to-cal','modification_date_to','{$gsmarty_datepicker_format}');" readonly />
        <img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
             onclick="showCal('modification_date_to-cal','modification_date_to','{$gsmarty_datepicker_format}');" >
        <img title="{$labels.clear_date}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
             onclick="javascript:var x = document.getElementById('modification_date_to'); x.value = '';" >
        <div id="modification_date_to-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
      </div>
    </div>

     <div class="row">
      <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.jolly}</label><img src="{$tlImages.info}" title="{$labels.jolly_hint}"></div>
      <div class="col-sm-2 col-md-2 col-lg-2"><input type="text" name="jolly" id="jolly" 
      value="{$gui->jolly|escape}"
                 size="{#SUMMARY_SIZE#}" maxlength="{#SUMMARY_MAXLEN#}" /></div>
     </div>
    
   
    {if $gui->filter_by.keyword}
    <div class="row">
      <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.keyword}</label></div>
      <td colspan="5"><select name="keyword_id">
          <option value="0">&nbsp;</option>
          {section name=Row loop=$gui->keywords}
          <option value="{$gui->keywords[Row]->dbID}">{$gui->keywords[Row]->name|escape}</option>
        {/section}
        </select>
      </div>
    </div>
    {/if}
    
    {if $gui->filter_by.design_scope_custom_fields}
        <div class="row">
          <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.custom_field}</label></div>
          <div class="col-sm-2 col-md-2 col-lg-2"><select name="custom_field_id" id="custom_field_id">
              <option value="0">&nbsp;</option>
              {foreach from=$gui->design_cf key=cf_id item=cf}
                <option value="{$cf_id}">{$cf.label|escape}</option>
              {/foreach}
            </select>
          </div>
          </div>
        <div class="row">
            <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.custom_field_value}</label></div>
            <div class="col-sm-2 col-md-2 col-lg-2">
            <input type="text" name="custom_field_value" 
                   size="{#CFVALUE_SIZE#}" maxlength="{#CFVALUE_MAXLEN#}"/>
          </div>
        </div>
    {/if}
    
    {if $gui->filter_by.requirement_doc_id}
        <div class="row">
            <div class="col-sm-1 col-md-1 col-lg-1"><label>{$labels.requirement_document_id}</label></div>
            <td colspan="7">
            <input type="text" name="requirement_doc_id" id="requirement_doc_id"
                   title="{$labels.search_type_like}"
                   size="{#REQ_DOCID_SIZE#}" maxlength="{#REQ_DOCID_MAXLEN#}"/>
          </div>
        </div>
    {/if}    
  </div>
  
  <p style="padding-left: 20px;">
    <input class="{#BUTTON_CLASS#}" type="submit" 
           name="doSearch" id="doSearch"
           value="{$labels.btn_find}" />
  </p>
</form>

</div>
