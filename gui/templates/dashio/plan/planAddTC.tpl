{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource planAddTC.tpl
generate a list of TC for adding to Test Plan 
*}

{$cfg_section=$smarty.template|basename|replace:".tpl":"" }
{config_load file="input_dimensions.conf" section=$cfg_section}

{lang_get var="labels" 
          s='note_keyword_filter, check_uncheck_all_for_remove,
             th_id,th_test_case,version,execution_order,th_platform,
             no_testcase_available,btn_save_custom_fields,send_mail_to_tester,
             inactive_testcase,btn_save_exec_order,info_added_on_date,
             executed_can_not_be_removed,added_on_date,btn_save_platform,
             check_uncheck_all_checkboxes,removal_tc,show_tcase_spec,
             tester_assignment_on_add,adding_tc,check_uncheck_all_tc,for,
             build_to_assign_on_add,importance,execution,design,execution_history,
             warning_remove_executed,th_status,selected_cb,deselected_cb'}

{* prefix for checkbox named , ADD and ReMove *}   
{$add_cb="achecked_tc"} 
{$rm_cb="remove_checked_tc"}


{include file="inc_head.tpl" openHead="yes"}
{include file="inc_jsCheckboxes.tpl"}

{include file="inc_ext_js.tpl"}
{include file="plan/planAddTCJS.inc.tpl"}
{include file="bootstrap.inc.tpl"}

<script src="{$basehref}third_party/pace/pace.js"></script> 
<link href="{$basehref}third_party/pace/themes/blue/pace-theme-loading-bar.css" rel="stylesheet" />
<script src="{$basehref}third_party/bootbox/bootbox.all.min.js"></script>

</head>
<body class="fixedheader">
<form name="addTcForm" id="addTcForm" method="post" 
      onSubmit="javascript:return checkDelete(js_remove_executed_counter);">

   <div class="workBack" id="header-wrap">
	  	<h1 class="{#TITLE_CLASS#}">{$gui->pageTitle|escape}
	  	{include file="inc_help.tpl" helptopic="hlp_planAddTC" show_help_icon=true}
	  	</h1>

	    {if $gui->has_tc}
	  	  {include file="inc_update.tpl" result=$sqlResult}
        
	  	  	
		{* user assignments per build *}
		{* show this only if a build exists to which we can assign users *}
		{if $gui->build.count && $gui->canAssignExecTask}
		
		<div class="groupBtn">
				{$labels.tester_assignment_on_add}
				<select name="testerID"
				        id="testerID">
					{html_options options=$gui->testers selected=$gui->testerID}
				</select>
				
				{$labels.build_to_assign_on_add}
				<select name="build_id" id="build_id">
				{html_options options=$gui->build.items 
				              selected=$gui->build.selected}
				</select>
		
				<input type="checkbox" name="send_mail" id="send_mail" {$gui->send_mail_checked}/>
				{$labels.send_mail_to_tester}
			
		</div>

		{/if} {* if $gui->build.count *}
		{* --------------------------------------------------------------- *}
		
	  	  
	  <div class="groupBtn">
			<div style="float: left; margin-right: 2em">
				{$labels.check_uncheck_all_tc}
				{if $gui->usePlatforms}
				<select id="select_platform">
					{html_options options=$gui->bulk_platforms}
				</select>
				{else}
				<input type="hidden" id="select_platform" value="0">
				{/if}
				{$labels.for}
				{if $gui->full_control}
				<button class="{#BUTTON_CLASS#}"
                onclick="cs_all_checkbox_in_div_with_platform('addTcForm', '{$add_cb}', jQuery('#select_platform').val()); return false;">{$labels.adding_tc}</button>
				{/if}
				<button class="{#BUTTON_CLASS#}"
               onclick="cs_all_checkbox_in_div_with_platform('addTcForm', '{$rm_cb}', jQuery('#select_platform').val()); return false;">{$labels.removal_tc}</button>
			</div>
	  	  	<input type="hidden" name="doAction" id="doAction" value="default" />
	  	  	<input class="{#BUTTON_CLASS#}" type="submit" 
                 name="doAddRemove" id="doAddRemove"
                 value="{$gui->buttonValue}"
	  	  		     onclick="doAction.value=this.name" />
	  	  	{if $gui->full_control eq 1}
	  	  	  <input class="{#BUTTON_CLASS#}" type="submit" 
                   name="doReorder" id="doReorder"
                   value="{$labels.btn_save_exec_order}"
	  	  		       onclick="doAction.value=this.name" />
        
	  	  		{if $gui->drawSaveCFieldsButton}
	  	  		  <input class="{#BUTTON_CLASS#}" type="submit" 
                     name="doSaveCustomFields" 
                     id="doSaveCustomFields" 
                     value="{$labels.btn_save_custom_fields}"
	  	  			       onclick="doAction.value=this.name" />
	  	  		{/if}
	  	  		{if $gui->drawSavePlatformsButton}
	  	  		  <input type="submit" name="doSavePlatforms" value="{$labels.btn_save_platform}"
	  	  			       onclick="doAction.value=this.name" />
	  	  		{/if}
	  	  	{/if}
	  	  </div>
      {else}
	    <div class="info">{$labels.no_testcase_available}</div>
	  	{/if}  

    </div> <!-- header-wrap -->

{if $gui->has_tc}
  <div class="workBack" id="workback">
    <div style="margin-left: 20px; font-size: smaller;">
  	{$gui->status_feeback|escape}<br />
    {if $gui->keywords_filter_feedback != '' }
  			<br />{$labels.note_keyword_filter}: {$gui->keywords_filter_feedback|escape}</p>
  	{/if}
    </div>

    {* ======================================== *}
    {* Loop over Test Suites to draw test cases *}
  	{$item_number=0}
  	{foreach name="tSuiteLoop" from=$gui->items item=ts}
  		{$item_number=$item_number+1}
  		{$ts_id=$ts.testsuite.id}
  		{$div_id="div_$ts_id"}
  	  {strip}
  	  
  	  {* Title and clickable images to control toogle *}
  		<div id="{$div_id}"  style="margin:0px 0px 0px {$ts.level}0px;">
        <h2 class="testlink">{$ts.testsuite.name|escape}</h2> 
        {if $item_number == 1}
          <hr />
        {/if} {* $item_number == 1 *}
     
        {* used as memory for the check/uncheck all checkbox javascript logic *}
        <input type="hidden" name="add_value_{$ts_id}"  id="add_value_{$ts_id}"  value="0" />
        <input type="hidden" name="rm_value_{$ts_id}"  id="rm_value_{$ts_id}"  value="0" />
              
       {* ------------------------------------------------------------------------- *}      
       {if ($gui->full_control && $ts.testcase_qty gt 0) || $ts.linked_testcase_qty gt 0}
          <table cellspacing="0" border="0" style="font-size:small;" width="100%">
            <tr style="background-color:blue;font-weight:bold;color:white">
  			     <td width="5" align="center">
                {if $gui->full_control}
  			          <img class="clickable" src="{$tlImages.toggle_all}"
  			               onclick='cs_all_checkbox_in_div("{$div_id}","{$add_cb}","add_value_{$ts_id}");'
                       title="{$labels.check_uncheck_all_checkboxes}" />
      			    {else}
      			     &nbsp;
  		    	    {/if}
  			     </td>
  
             {if $gui->usePlatforms} <td>{$labels.th_platform}</td> {/if}
  			     <td>{$labels.th_test_case}</td>
  			     <td>{$labels.version}</td>
             <td>{$labels.th_status}</td>
  			     {if $gui->priorityEnabled} <td>{$labels.importance}</td> {/if}
             		<td align="center">
   				      <img src="{$tlImages.execution_order}" title="{$labels.execution_order}" />
  				   	</td>

             {if $ts.linked_testcase_qty gt 0}
  				      <td>&nbsp;</td>
  				      <td>
  				      <img class="clickable" src="{$tlImages.disconnect}" 
                     onclick='cs_all_checkbox_in_div("{$div_id}","{$rm_cb}","rm_value_{$ts_id}");'
                     title="{$labels.check_uncheck_all_for_remove}" />
  				      </td>
  				      <td align="center">
    				      <img src="{$tlImages.date}" title="{$labels.added_on_date}" />
  				      </td>
             {/if}
            </tr>   
            
  			    {foreach name="tCaseLoop" from=$ts.testcases item=tcase}
      			  {$is_active=0}
              	{$linked_version_id=$tcase.linked_version_id}
              {$tcID=$tcase.id}
  				    {if $linked_version_id != 0}
                {if $tcase.tcversions_active_status[$linked_version_id] eq 1}             
                    {$is_active=1}
                {/if}
              {else}
                {if $tcase.tcversions_qty != 0}
                	{$is_active=1}
                {/if}
              {/if}      
              
              {* ------------------------------------------------------------- *}
              {if $is_active || $linked_version_id != 0}  
     				    {if $gui->full_control || $linked_version_id != 0}
     					    {$drawPlatformChecks=0}
                  {if $gui->usePlatforms}
                    {* Feature id is indexed by platform id then 0 => has no platform assigned *}
                    {if !isset($tcase.feature_id[0])}
                      {$drawPlatformChecks=1}
                    {/if}
                  {/if}
     				  
     				      <tr{if $linked_version_id != 0 && $drawPlatformChecks == 0} style="{$smarty.const.TL_STYLE_FOR_ADDED_TC}"{/if}>
      			    	  <td width="20">
                    {* ------------------------------------------------------- *} 
                    {* Draw check box left to test case name - the old way when platforms feature does not exist *}
      			        {if !$gui->usePlatforms  || $drawPlatformChecks == 0}
      				        {if $gui->full_control}
  	      				        {if $is_active == 0 || $linked_version_id != 0}
  	      				           &nbsp;&nbsp;
  	      				        {else}
  	      				           <input type="checkbox" name="{$add_cb}[{$tcID}][0]" id="{$add_cb}{$tcID}[0]" value="{$tcID}" /> 
  	      				        {/if}
  	      				        <input type="hidden" name="a_tcid[{$tcID}]" value="{$tcID}" />
      				        {else}
  								      &nbsp;&nbsp;
      				        {/if}
      				      {/if}  
                    {* ------------------------------------------------------- *} 
      			      	</td>
      			      	
                    {if $gui->usePlatforms}
                    	<td>
                    	    {if $drawPlatformChecks}
                    	      &nbsp;
                    		  {else}
           				          <select name="feature2fix[{$tcase.feature_id[0]}][{$linked_version_id}]">
           					                {html_options options=$gui->platformsForHtmlOptions selected=0}
  						              </select>
                    		  {/if}  
                    	</td>
                    {/if}
      			     
      			        <td>
							<img class="clickable" src="{$tlImages.history_small}"
							     onclick="javascript:openExecHistoryWindow({$tcase.id});"
							     title="{$labels.execution_history}" />
							<img class="clickable" src="{$smarty.const.TL_THEME_IMG_DIR}/edit_icon.png"
							     onclick="javascript:openTCaseWindow({$tcase.id});"
							     title="{$labels.design}" />
							<span id="tooltip-{$tcID}">
                <img src="{$tlImages.summary_small}">&nbsp;
								{$gui->testCasePrefix|escape}{$tcase.external_id}{$gsmarty_gui->title_separator_1}{$tcase.name|escape}
							</span>
      			        </td>
                  	<td>
                  	{if $gui->priorityEnabled}
                  		<script type="text/javascript">
                  		{* To be able to update importance when selecting 
                         another test case version we need to transform 
                         smarty arrays to javascript array *}

                      js_tcase_importance[{$tcID}] = new Array();
                      js_tcase_wkfstatus[{$tcID}] = new Array();

                  		{foreach key=version item=value from=$tcase.importance}
                  			js_tcase_importance[{$tcID}][{$version}] = {$value};
                  		{/foreach}
                      {foreach key=version item=value from=$tcase.status}
                        js_tcase_wkfstatus[{$tcID}][{$version}] = {$value};
                      {/foreach}
                      </script>
           				    
                      <select name="tcversion_for_tcid[{$tcID}]" 
           				      onchange="updTCAttr({$tcID},this.options[this.selectedIndex].value);"
           				      {if $linked_version_id != 0} disabled{/if}>
           				        {html_options options=$tcase.tcversions selected=$linked_version_id}
           				    </select>
                  	</td>


                    {if $linked_version_id != 0} 
                        {$importance=$tcase.importance.$linked_version_id}
                        {$wkf=$tcase.status.$linked_version_id}
                    {else}
                      {* 
                        if no test case version is linked -> 
                        set attr to first option from select box. 
                        Only way to get first element of an array is 
                        this loop afaik 
                      *}
                      {foreach name="oneLoop" from=$tcase.importance 
                               key=key item=item}
                        {if $smarty.foreach.oneLoop.first}
                          {$firstElement=$key}
                        {/if}
                      {/foreach}
                      {$importance=$tcase.importance.$firstElement}

                      {foreach name="oneLoop" from=$tcase.status 
                               key=key item=item}
                        {if $smarty.foreach.oneLoop.first}
                          {$firstElement=$key}
                        {/if}
                      {/foreach}
                      {$wkf=$tcase.status.$firstElement}
                    {/if}

                    <td id="wkfstatus_{$tcID}" style="width:15%">
                      {$gsmarty_option_wkfstatus.$wkf}
                    </td>

      			        <td id="importance_{$tcID}" style="width:7%">
      			          {$gsmarty_option_importance.$importance}
      			        </td>

           			{else}
           				    <select name="tcversion_for_tcid[{$tcID}]"{if $linked_version_id != 0} disabled{/if}>
           				            {html_options options=$tcase.tcversions selected=$linked_version_id}
           				    </select>
           			{/if}
                  	<td style="text-align:center;">
                    		<input name="exec_order[{$tcID}]" {$gui->exec_order_input_disabled}
                               style="text-align:right;" size="{#EXECUTION_ORDER_SIZE#}" maxlength="{#EXECUTION_ORDER_MAXLEN#}" 
                               value="{$tcase.execution_order}" />
                    		{if $linked_version_id != 0}  
                      	  <input type="hidden" name="linked_version[{$tcID}]" value="{$linked_version_id}" />
                      	  <input type="hidden" name="linked_exec_order[{$tcID}]"  value="{$tcase.execution_order}" />
                    		{/if}
                  	</td>
                  
                  {* ---------------------------------------------------------------------------------------------------------- *}      
                  {if $ts.linked_testcase_qty gt 0 && $drawPlatformChecks==0}
            			  <td>&nbsp;</td>
            			  
            			  <td>
            			    {$show_remove_check=0}
            			    {$executed=0}
         				      {if !is_null($tcase.executed) && $tcase.executed[0] == 'yes'}
            			    	{$executed=1}
            			    {/if}
            			    
            			  	{if $linked_version_id}
            			  		{$show_remove_check=1}
         				        {if !is_null($tcase.executed) && $tcase.executed[0] == 'yes'}
         				          {$show_remove_check=$gui->can_remove_executed_testcases}
            			  	  {/if}      
                   		{/if} 
            	   			{if $show_remove_check}
            					<input type='checkbox' name='{$rm_cb}[{$tcID}][0]' id='{$rm_cb}{$tcID}[0]' 
            					       value='{$linked_version_id}' 
            						   {if $executed}	
            						   	 onclick="updateRemoveExecCounter('{$rm_cb}{$tcID}[0]')"
            						   {/if}	
            					/>
  				   			{else}
            		    		&nbsp;
            	   			{/if}
            	   
                   			{if !is_null($tcase.executed) && $tcase.executed[0] eq 'yes'}&nbsp;&nbsp;&nbsp;
   				                  <img src="{$tlImages.executed}" title="{$gui->warning_msg->executed}" />
                      		{/if}
                      		{if $is_active eq 0}&nbsp;&nbsp;&nbsp;{$labels.inactive_testcase}{/if}
            			  </td>
            			  
            			  <td align="center" title="{$labels.info_added_on_date}">
            			  	{if !is_null($tcase.linked_ts) && $tcase.linked_ts[0] != ''}{localize_date d=$tcase.linked_ts[0]}{else}&nbsp;{/if}  
            			  </td>
                  {/if}
                  {* ---------------------------------------------------------------------------------------------------------- *}      
                  
                </tr>
                {* This piece will be used ONLY when platforms are not used or not assigned yet *}
  			        {if isset($tcase.custom_fields[0])}
        			    <input type='hidden' name='linked_with_cf[{$tcase.feature_id[0]}]' value='{$tcase.feature_id[0]}' />
                  <tr><td colspan="9">{$tcase.custom_fields[0]}</td></tr>
                {/if}
                
              {/if}
              
              
              {* ============================================================ *} 
              {* === Draw Platform related information === *}
              {if $gui->usePlatforms && $drawPlatformChecks}
                {foreach from=$gui->platforms item=platform}
                  <tr {if isset($tcase.feature_id[$platform.id])}	
                      style="{$smarty.const.TL_STYLE_FOR_ADDED_TC}" {/if} >
                  	<td>
      				    {if $gui->full_control}
  	      		        {if $is_active == 0 || isset($tcase.feature_id[$platform.id])}
  	      		      	  &nbsp;&nbsp;
  	      		        {else}
  	      		      	  <input type="checkbox"  name="{$add_cb}[{$tcID}][{$platform.id}]" id="{$add_cb}{$tcID}" value="{$tcID}" /> 
  						         {/if}
  	      		        <input type="hidden" name="a_tcid[{$tcID}][{$platform.id}]" value="{$tcID}" />
  					         {else}
  						         &nbsp;&nbsp;
      				       {/if}
      			        </td>
      			        <td>{$platform.name|escape}</td>
  				          <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
  				          {if $gui->priorityEnabled} <td>&nbsp;</td> {/if}

                    {* it's not possible to remove an inactive tc version from a testplan with platforms *}
                      {if isset($tcase.feature_id[$platform.id])}
  	      			      <td>&nbsp;</td>
                      <td>&nbsp;</td>
  	   				        <td>
  	      			    	{* added isset() on next section to avoid warning on event log *}
							        {* can_remove_executed doesn't work when Platforms are used *}	
            			    {$show_remove_check=0}
            			  	{if $linked_version_id}
            			  		{$show_remove_check=1}
         				        {if !is_null($tcase.executed) && isset($tcase.executed[$platform.id]) && $tcase.executed[$platform.id] eq 'yes'}
         				          	{$show_remove_check=$gui->can_remove_executed_testcases}
            			  	  	{/if}      
                   			{/if} 
            	   			{if $show_remove_check}
  	   				            <input type='checkbox' name='{$rm_cb}[{$tcID}][{$platform.id}]' id='{$rm_cb}{$tcID}[{$platform.id}]'
  	      			  		           value='{$linked_version_id}' />
  				   			{else}
            		    		&nbsp;&nbsp;
            	   			{/if}
                        	{if !is_null($tcase.executed) && isset($tcase.executed[$platform.id]) && $tcase.executed[$platform.id] eq 'yes'}&nbsp;&nbsp;&nbsp;
   				                  <img src="{$tlImages.executed}" title="{$gui->warning_msg->executed}" />
                        	{/if}

                            {* TICKET 5294: it is not possible to remove an inactive tc version from a testplan with platforms *}
                            {* display "inactive" label when testcase has no active version *}
                            {if $is_active eq 0}&nbsp;&nbsp;&nbsp;{$labels.inactive_testcase}{/if}
  	                  </td>
  	                  <td align="center" title="{$labels.info_added_on_date}">{localize_date d=$tcase.linked_ts[$platform.id]}</td>
                    {/if}
                  </tr>
  			          {if isset($tcase.custom_fields[$platform.id])}
                    <tr>
                      <td colspan="8">
                        <input type='hidden' name='linked_with_cf[{$tcase.feature_id}]' value='{$tcase.feature_id}' />
                        {$tcase.custom_fields[$platform.id]}
                      </td>
                    </tr>
                  {/if}
                  
                {/foreach}
               	<tr><td colspan="10"><hr/></td></tr>
              {/if}             
              {* ================================================================================================================ *} 
              
              
              
             {/if} {* if $is_active || $linked_version_id ne 0 *}
    	      {/foreach}
          </table>
          <br />
       {/if}  {* there are test cases to show ??? *}
      {/strip}
      </div>
  	{/foreach}
  </div>
{/if}
</form>

{if $gui->refreshTree}
	{include file="inc_refreshTreeWithFilters.tpl"}
{/if}

</body>
</html>