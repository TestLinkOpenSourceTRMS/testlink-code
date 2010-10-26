{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: tcSearchForm.tpl,v 1.21 2010/10/26 13:11:34 mx-julian Exp $
Purpose: show form for search through test cases in test specification

rev :
  20101026 - Julian - no validation for dates -> no manual input - input only via datepicker
  20101021 - asimon - BUGID 3716: replaced old separated inputs for day/month/year by ext js calendar
  20100707 - Julian - BUGID 3584: replaced cf names by cf labels
  20100609 - franciscom - BUGID 1627: Search Test Case by Date of Creation
  20100409 - franciscom - BUGID 3371 Search Test Cases based on Test Importance
  20100124 - franciscom - BUGID 3077 - search on preconditions
  20090228 - franciscom - pre-fill test case id with testcase prefix
*}
{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels" 
          s='title_search_tcs,caption_search_form,th_tcid,th_tcversion,
             th_title,summary,steps,expected_results,keyword,custom_field,
             search_type_like,preconditions,filter_mode_and,test_importance,
             creation_date_from,creation_date_to,modification_date_from,modification_date_to,
             custom_field_value,btn_find,requirement_document_id,show_calender,clear_date'}


{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_ext_js.tpl" bResetEXTCss=1}
</head>
<body>

<h1 class="title">{$gui->mainCaption|escape}</h1>
<div style="margin: 1px;">
<form method="post" action="lib/testcases/tcSearch.php" target="workframe">
	<table class="smallGrey" style="width:100%">
		<caption>{$labels.caption_search_form}</caption>
		<tr>
		 <td colspan="2"><img src="{$tlImages.info}"> {$labels.filter_mode_and} </td>
		</tr>
		<tr>
			<td>{$labels.th_tcid}</td>
			<td><input type="text" name="targetTestCase" id="TCID"  
			           size="{#TC_ID_SIZE#}" maxlength="{#TC_ID_MAXLEN#}" value="{$gui->tcasePrefix}"/></td>
		</tr>
		<tr>
			<td>{$labels.th_tcversion}</td>
			<td><input type="text" name="version"
			           size="{#VERSION_SIZE#}" maxlength="{#VERSION_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{$labels.th_title}</td>
			<td><input type="text" name="name" size="{#TCNAME_SIZE#}" maxlength="{#TCNAME_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{$labels.summary}</td>
			<td><input type="text" name="summary" 
			           size="{#SUMMARY_SIZE#}" maxlength="{#SUMMARY_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{$labels.preconditions}</td>
			<td><input type="text" name="preconditions" 
			           size="{#PRECONDITIONS_SIZE#}" maxlength="{#PRECONDITIONS_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{$labels.steps}</td>
			<td><input type="text" name="steps" 
			           size="{#STEPS_SIZE#}" maxlength="{#STEPS_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{$labels.expected_results}</td>
			<td><input type="text" name="expected_results" 
			           size="{#RESULTS_SIZE#}" maxlength="{#RESULTS_MAXLEN#}" /></td>
		</tr>

		<tr>
			<td>{$labels.creation_date_from}</td>
			<td>
				{* BUGID 3716 *}
                <input type="text" 
                       name="creation_date_from" id="creation_date_from" 
				       value="{$gui->creation_date_from}" 
				       onclick="showCal('creation_date_from-cal','creation_date_from','{$gsmarty_datepicker_format}');" readonly />
				<img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
				     onclick="showCal('creation_date_from-cal','creation_date_from','{$gsmarty_datepicker_format}');" >
				<img title="{$labels.clear_date}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
			         onclick="javascript:var x = document.getElementById('creation_date_from'); x.value = '';" >
				<div id="creation_date_from-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
		  </td>
		</tr>
		<tr>
			<td>{$labels.creation_date_to}</td>
			<td>
				{* BUGID 3716 *}
           	    <input type="text" 
                       name="creation_date_to" id="creation_date_to" 
				       value="{$gui->creation_date_to}" 
				       onclick="showCal('creation_date_to-cal','creation_date_to','{$gsmarty_datepicker_format}');" readonly />
				<img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
				     onclick="showCal('creation_date_to-cal','creation_date_to','{$gsmarty_datepicker_format}');" >
				<img title="{$labels.clear_date}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
			         onclick="javascript:var x = document.getElementById('creation_date_to'); x.value = '';" >
				<div id="creation_date_to-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
		  </td>
		</tr>		
		<tr>
			<td>{$labels.modification_date_from}</td>
			<td>
				{* BUGID 3716 *}
            	<input type="text" 
                       name="modification_date_from" id="modification_date_from" 
				       value="{$gui->modification_date_from}" 
				       onclick="showCal('modification_date_from-cal','modification_date_from','{$gsmarty_datepicker_format}');" readonly />
				<img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
				     onclick="showCal('modification_date_from-cal','modification_date_from','{$gsmarty_datepicker_format}');" >
				<img title="{$labels.clear_date}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
			         onclick="javascript:var x = document.getElementById('modification_date_from'); x.value = '';" >
				<div id="modification_date_from-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
		  </td>
		</tr>
		<tr>
			<td>{$labels.modification_date_to}</td>
			<td>
				{* BUGID 3716 *}
         	    <input type="text" 
                       name="modification_date_to" id="modification_date_to" 
				       value="{$gui->modification_date_to}" 
				       onclick="showCal('modification_date_to-cal','modification_date_to','{$gsmarty_datepicker_format}');" readonly />
				<img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
				     onclick="showCal('modification_date_to-cal','modification_date_to','{$gsmarty_datepicker_format}');" >
				<img title="{$labels.clear_date}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
			         onclick="javascript:var x = document.getElementById('modification_date_to'); x.value = '';" >
				<div id="modification_date_to-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
		  </td>
		</tr>
		
    {if $session['testprojectOptions']->testPriorityEnabled}
		  <tr>
		  	<td>{$labels.test_importance}</td>
		  	<td>
		  	<select name="importance">
      	  	{html_options options=$gui->option_importance}
	      </select>
	      </td>
		  </tr>
		{/if}
		
		
		{if $gui->filter_by.keyword}
		<tr>
			<td>{$labels.keyword}</td>
			<td><select name="keyword_id">
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
         		<td>
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
</body>
</html>
