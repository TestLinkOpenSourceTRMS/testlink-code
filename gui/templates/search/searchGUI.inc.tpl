{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource searchForm.tpl
Purpose: show form 

@internal revisions
@since 1.9.16
*}

{$cfg_section=$smarty.template|basename|replace:".inc.tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels" s='search_items,btn_find,logical_or,logical_and,created_by,
                          edited_by,modification_date_to,modification_date_from,
                          custom_field,custom_field_value,creation_date_to,creation_date_from,keyword,type,status'}
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
      <input type="checkbox" name="tc_title" value="1" {if $gui->tc_title}checked{/if}>Title<br>
      <input type="checkbox" name="tc_summary" value="1" {if $gui->tc_summary}checked{/if}>Summary<br>
      <input type="checkbox" name="tc_preconditions" value="1" {if $gui->tc_preconditions}checked{/if}>Preconditions<br>
      <input type="checkbox" name="tc_steps" value="1" {if $gui->tc_steps}checked{/if}>Steps<br>
      <input type="checkbox" name="tc_expected_results" value="1" {if $gui->tc_expected_results}checked{/if}>Expected results<br>
      <input type="checkbox" name="tc_id" value="1" {if $gui->tc_id}checked{/if}>id<br>
    </td>

    <td style="width: 30%" colspan="1"> 
      TEST SUITE<br>
      <input type="checkbox" name="ts_title" value="1" {if $gui->ts_title}checked{/if}>Title<br>
      <input type="checkbox" name="ts_summary" value="1" {if $gui->ts_summary}checked{/if}>Details<br>
    </td>
    
    <td style="width: 30%" colspan="1"> 
      Requirement Spec<br>
      <input type="checkbox" name="rs_title" value="1" {if $gui->rs_title}checked{/if} >Title<br>
      <input type="checkbox" name="rs_scope" value="1" {if $gui->rs_scope}checked{/if}>Scope<br>
    </td>

    <td style="width: 30%" colspan="1"> 
      Requirement<br>
      <input type="checkbox" name="rq_title" value="1" {if $gui->rq_title}checked{/if}>Title<br>
      <input type="checkbox" name="rq_scope" value="1" {if $gui->rq_scope}checked{/if}>Scope<br>
      <input type="checkbox" name="rq_doc_id" value="1" {if $gui->rq_doc_id}checked{/if}>Req Doc ID<br>
    </td>
    
    </tr>

    <tr>
      <td>&nbsp;</td>
      <td>{$labels.created_by}
      <input type="text" name="created_by" id="created_by" 
                 value="{$gui->created_by|escape}"
                 size="{#AUTHOR_SIZE#}" maxlength="{#TCNAME_MAXLEN#}" />

      <br>{$labels.edited_by}
      <input type="text" name="edited_by" id ="edited_by" value="{$gui->edited_by|escape}"
                 size="{#AUTHOR_SIZE#}" maxlength="{#TCNAME_MAXLEN#}" /></td>
   

    {if $gui->filter_by.keyword}
      <td>{$labels.keyword}
         <select name="keyword_id">
          <option value="0">&nbsp;</option>
          {section name=Row loop=$gui->keywords}
          <option value="{$gui->keywords[Row]->dbID}">{$gui->keywords[Row]->name|escape}</option>
        {/section}
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
      <td>&nbsp;</td>      
      <td>{$labels.creation_date_from}
        <input type="text" name="creation_date_from" id="creation_date_from" 
               value="{$gui->creation_date_from|escape}" size="{#DATE_PICKER#}"
               onclick="showCal('creation_date_from-cal','creation_date_from','{$gsmarty_datepicker_format}');" readonly />
        
        <img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
             onclick="showCal('creation_date_from-cal','creation_date_from','{$gsmarty_datepicker_format}');" >
        <img title="{$labels.clear_date}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
               onclick="javascript:var x = document.getElementById('creation_date_from'); x.value = '';" >
        <div id="creation_date_from-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>

       <br> 
      {$labels.creation_date_to}
  
        <input type="text" name="creation_date_to" id="creation_date_to" value="{$gui->creation_date_to|escape}" 
               size="{#DATE_PICKER#}"
               onclick="showCal('creation_date_to-cal','creation_date_to','{$gsmarty_datepicker_format}');" readonly />
        <img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
             onclick="showCal('creation_date_to-cal','creation_date_to','{$gsmarty_datepicker_format}');" >
        <img title="{$labels.clear_date}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
               onclick="javascript:var x = document.getElementById('creation_date_to'); x.value = '';" >
        <div id="creation_date_to-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
  
      <br>
      {$labels.modification_date_from}
      
        <input type="text" name="modification_date_from" id="modification_date_from" 
        value="{$gui->modification_date_from|escape}" 
               size="{#DATE_PICKER#}"
               onclick="showCal('modification_date_from-cal','modification_date_from','{$gsmarty_datepicker_format}');" readonly />
        <img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
             onclick="showCal('modification_date_from-cal','modification_date_from','{$gsmarty_datepicker_format}');" >
        <img title="{$labels.clear_date}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
             onclick="javascript:var x = document.getElementById('modification_date_from'); x.value = '';" >
        <div id="modification_date_from-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
      <br>

      {$labels.modification_date_to}
      
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

      <td>
      {$labels.type}
        <select name="rType" id="rType">
          <option value="">&nbsp;</option>
            {html_options options=$gui->rtypes  selected=$gui->rType}
          </select>

       <br>   
       {$labels.status}
        <select name="reqStatus">
        <option value="">&nbsp;</option>
        {html_options options=$gui->reqStatusDomain selected=$gui->reqStatus}
        </select>
      </td>

    </tr>

  </table>
  
  <p style="padding-left: 20px;">
    <input type="hidden" name="caller" value="searchGui" />
    <input type="submit" name="doSearch" value="{$labels.btn_find}" />
  </p>
</form>
</div>