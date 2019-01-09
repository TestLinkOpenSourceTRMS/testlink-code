{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource attributesLinearForViewer.inc.tpl
*}

<table class="table table-striped table-bordered">
	<tbody>
    	<tr>
        	<th>
        		<span class="labelHolder" title="{$tcView_viewer_labels.onchange_save}">
              		{$tcView_viewer_labels.status}{$smarty.const.TITLE_SEP}
          		</span>
        	</th>
        	<td>
            <form style="display:inline;" id="statusForm_{$args_testcase.id}" name="statusForm_{$args_testcase.id}" method="post" action="{$basehref}lib/testcases/tcEdit.php">
                <input type="hidden" name="doAction" id="doAction" value="setStatus">
                <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
                <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
        
              	{if $edit_enabled && $args_testcase.is_open}
                  	<select name="status" id="status" onchange="document.getElementById('statusForm_{$args_testcase.id}').submit();">
                    	{html_options options=$gui->domainTCStatus selected=$args_testcase.status}
            		</select>
        		{else}
                	{$gui->domainTCStatus[$args_testcase.status]}
             	{/if}
        	</form>
        	</td>
        </tr>
        {if $session['testprojectOptions']->testPriorityEnabled}
        <tr>
        	<th>
        		<span class="labelHolder" title="{$tcView_viewer_labels.onchange_save}">
              		{$tcView_viewer_labels.importance}{$smarty.const.TITLE_SEP}
          		</span>
      		</th>
      		<td>
        		<form style="display:inline;" id="importanceForm_{$args_testcase.id}" name="importanceForm_{$args_testcase.id}" method="post" action="{$basehref}lib/testcases/tcEdit.php">
                    <input type="hidden" name="doAction" id="doAction" value="setImportance">
                    <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
                    <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
           
                    {if $edit_enabled && $args_testcase.is_open}
                    <select name="importance" onchange="document.getElementById('importanceForm_{$args_testcase.id}').submit();" >
            			{html_options options=$gsmarty_option_importance selected=$args_testcase.importance}
                    </select>
                    {else}
            			{$gsmarty_option_importance[$args_testcase.importance]}
                    {/if}
        		</form>
    		</td>
    	</tr>
        {/if}
        
        {if $session['testprojectOptions']->automationEnabled}
        <tr>
        	<th>
        		<span class="labelHolder" title="{$tcView_viewer_labels.onchange_save}">
        			{$tcView_viewer_labels.execution_type}{$smarty.const.TITLE_SEP}
        		</span>
    		</th>
    		<td>
                <form style="display:inline;" id="execTypeForm_{$args_testcase.id}" name="execTypeForm_{$args_testcase.id}" method="post" action="{$basehref}lib/testcases/tcEdit.php">
                    <input type="hidden" name="doAction" id="doAction" value="setExecutionType">
                    <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
                    <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
            		
            		
                  	{if $edit_enabled && $args_testcase.is_open}
                    	<select name="exec_type" onchange="document.getElementById('execTypeForm_{$args_testcase.id}').submit();" >
                      		{html_options options=$gui->execution_types selected=$args_testcase.execution_type}
                    	</select>
                    	<input name="changeExecTypeOnSteps" type="checkbox">{$tcView_viewer_labels.applyExecTypeChangeToAllSteps}
                  	{else}
                    	{$gui->execution_types[$args_testcase.execution_type]}
                  	{/if}
                </form>
    		</td>
    	</tr>
        {/if}
        <tr>
        	<th>
            	<span class="labelHolder" title="{$tcView_viewer_labels.estimated_execution_duration}">
        			{$tcView_viewer_labels.estimated_execution_duration_short}{$smarty.const.TITLE_SEP}
        		</span>
        	</th>
        	<td>
    			 <form style="display:inline;" id="estimatedExecDurationForm_{$args_testcase.id}" name="estimatedExecDurationForm_{$args_testcase.id}" method="post" action="{$basehref}lib/testcases/tcEdit.php">
                    <input type="hidden" name="doAction" id="doAction" value="setEstimatedExecDuration">
                    <input type="hidden" name="testcase_id" value="{$args_testcase.testcase_id}" />
                    <input type="hidden" name="tcversion_id" value="{$args_testcase.id}" />
                
            		{if $edit_enabled && $args_testcase.is_open}
                		<span>
                			<input type="text" name="estimated_execution_duration" id="estimated_execution_duration" size="{#EXEC_DURATION_SIZE#}" maxlength="{#EXEC_DURATION_MAXLEN#}"
                					title="{$tcView_viewer_labels.estimated_execution_duration}" value="{$args_testcase.estimated_exec_duration}" {$tlCfg->testcase_cfg->estimated_execution_duration->required}>
                      		<input type="submit" name="setEstimated" value="{$tcView_viewer_labels.btn_save}" class="btn btn-primary" />
                		</span>
            		{else}
                    	{$args_testcase.estimated_exec_duration}
            		{/if}
            	</form>
        	</td>
       	</tr>
   	</tbody>
</table>