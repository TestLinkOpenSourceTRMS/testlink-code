{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	reqSearchForm.tpl
Form for requirement search.

@internal revisions

*}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels" 
          s='caption_search_form, custom_field, search_type_like,
             custom_field_value,btn_find,requirement_document_id, req_expected_coverage,
             title_search_req, reqid, reqversion, caption_search_form_req, title, scope,
             coverage, status, type, version, th_tcid, has_relation_type,
             modification_date_from,modification_date_to,creation_date_from,creation_date_to,
             show_calender,clear_date,log_message,'}


{include file="inc_head.tpl" openHead="yes" jsValidate="yes"}
{include file="inc_ext_js.tpl" bResetEXTCss=1}
</head>
<body>

<h1 class="title">{$gui->mainCaption|escape}</h1>

<div style="margin: 1px;">
<form method="post" action="{$basehref}lib/requirements/reqSearch.php" target="workframe">
	<table class="smallGrey" style="width:100%">
		<caption>{$labels.caption_search_form_req}</caption>
		<tr>
			<td>{$labels.requirement_document_id}</td>
			<td><input type="text" name="requirement_document_id" size="{#REQDOCID_SIZE#}" maxlength="{#REQDOCID_MAXLEN#}" /></td>
		</tr>
		
		<tr>
			<td>{$labels.version}</td>
			<td><input type="text" name="version" 
			           size="{#VERSION_SIZE#}" maxlength="{#VERSION_MAXLEN#}" /></td>
		</tr>
		
		<tr>
			<td>{$labels.title}</td>
			<td><input type="text" name="name" size="{#REQNAME_SIZE#}" maxlength="{#REQNAME_MAXLEN#}" /></td>
		</tr>
		
		<tr>
			<td>{$labels.scope}</td>
			<td><input type="text" name="scope" 
			           size="{#SCOPE_SIZE#}" maxlength="{#SCOPE_MAXLEN#}" /></td>
		</tr>
		
		<tr>
			<td>{$labels.status}</td>
     		<td><select name="reqStatus">
     		<option value="">&nbsp;</option>
  			{html_options options=$gui->reqStatus}
  			</select></td>
  		</tr>
		
		<tr>
			<td>{$labels.type}</td>
			<td>
				<select name="reqType" id="reqType">
					<option value="">&nbsp;</option>
  					{html_options options=$gui->types}
  				</select>
  			</td>
		</tr>
	
		{if $gui->filter_by.expected_coverage}
			<tr>
				<td>{$labels.req_expected_coverage}</td>
				<td><input type="text" name="coverage" size="{#COVERAGE_SIZE#}" maxlength="{#COVERAGE_MAXLEN#}" /></td>
			</tr>
		{/if}		
		
		{if $gui->filter_by.relation_type}
			<tr>
				<td>{$labels.has_relation_type}</td>
				<td>
					<select id="relation_type" name="relation_type" />
						<option value="">&nbsp;</option>
						{html_options options=$gui->req_relation_select.items}
					</select>
				</td>				
			</tr>
		{/if}
		
		<tr>
			<td>{$labels.creation_date_from}</td>
			<td>
				{* BUGID 3716 *}
                <input type="text" 
                       name="creation_date_from" id="creation_date_from" 
				       value="{$gui->creation_date_from}" 
				       onclick="showCal('creation_date_from-cal','creation_date_from','{$gsmarty_datepicker_format}');" 
				       readonly />
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
           	    <input type="text" 
                       name="creation_date_to" id="creation_date_to" 
				       value="{$gui->creation_date_to}" 
				       onclick="showCal('creation_date_to-cal','creation_date_to','{$gsmarty_datepicker_format}');"
				       readonly />
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
            	<input type="text" 
                       name="modification_date_from" id="modification_date_from" 
				       value="{$gui->modification_date_from}" 
				       onclick="showCal('modification_date_from-cal','modification_date_from','{$gsmarty_datepicker_format}');"
				       readonly />
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
         	    <input type="text" 
                       name="modification_date_to" id="modification_date_to" 
				       value="{$gui->modification_date_to}" 
				       onclick="showCal('modification_date_to-cal','modification_date_to','{$gsmarty_datepicker_format}');"
				       readonly />
				<img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
				     onclick="showCal('modification_date_to-cal','modification_date_to','{$gsmarty_datepicker_format}');" >
				<img title="{$labels.clear_date}" src="{$smarty.const.TL_THEME_IMG_DIR}/trash.png"
			         onclick="javascript:var x = document.getElementById('modification_date_to'); x.value = '';" >
				<div id="modification_date_to-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
		  </td>
		</tr>
		<tr>
			<td>{$labels.th_tcid}</td>
			<td><input type="text" name="tcid" value="{$gui->tcasePrefix}" 
			           size="{#TC_ID_SIZE#}" maxlength="{#TC_ID_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{$labels.log_message}</td>
			<td><input type="text" name="log_message" id="log_message" 
					   size="{#LOGMSG_SIZE#}" maxlength="{#LOGMSG_MAXLEN#}" /></td>
		</tr>
	
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
	  
		
	  		
		
  			      
	</table>
	
	<p style="padding-left: 20px;">
		
		<input type="submit" name="doSearch" value="{$labels.btn_find}" />
	</p>
</form>

</div>
</body>
</html>
