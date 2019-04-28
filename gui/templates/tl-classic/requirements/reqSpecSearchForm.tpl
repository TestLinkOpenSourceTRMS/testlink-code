{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	reqSpecSearchForm.tpl
Form for searching through requirement specifications.

@internal revisions
*}

{assign var="cfg_section" value=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels" 
          s='caption_search_form, custom_field, search_type_like,
             custom_field_value,btn_find,req_spec_document_id,log_message, 
             title_search_req_spec, reqid, reqversion, caption_search_form_req_spec,
             title, scope, coverage, status, type'}


{include file="inc_head.tpl"}
<body>

<h1 class="title">{$gui->mainCaption|escape}</h1>

<div style="margin: 1px;">
<form method="post" action="{$basehref}lib/requirements/reqSpecSearch.php" target="workframe">
	<table class="smallGrey" style="width:100%">
		<caption>{$labels.caption_search_form_req_spec}</caption>
		<tr>
			<td>{$labels.req_spec_document_id}</td>
			<td><input type="text" name="requirement_document_id" size="{#REQSPECDOCID_SIZE#}" maxlength="{#REQSPECDOCID_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{$labels.title}</td>
			<td><input type="text" name="name" size="{#REQSPECNAME_SIZE#}" maxlength="{#REQSPECNAME_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{$labels.scope}</td>
			<td><input type="text" name="scope" 
			           size="{#SCOPE_SIZE#}" maxlength="{#SCOPE_MAXLEN#}" /></td>
		</tr>
		<tr>
			<td>{$labels.type}</td>
			<td>
				<select name="reqSpecType" id="reqSpecType">
					<option value="notype">&nbsp;</option>
  					{html_options options=$gui->types}
  				</select>
  			</td>
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
		<tr>
			<td>{$labels.log_message}</td>
			<td><input type="text" name="log_message" id="log_message" 
					   size="{#LOGMSG_SIZE#}" maxlength="{#LOGMSG_MAXLEN#}" /></td>
		</tr>
	  		
		
  			      
	</table>
	
	<p style="padding-left: 20px;">
		
		<input type="submit" name="doSearch" value="{$labels.btn_find}" />
	</p>
</form>

</div>



</body>
</html>
