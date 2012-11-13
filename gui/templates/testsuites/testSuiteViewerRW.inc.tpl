{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource testSuiteViewerRW.inc.tpl

*}
<p>
<div class="labelHolder">
 <label for="name">{$labels.comp_name}</label>
</div> 
<div>
	<input type="text" id="testsuiteName" name="testsuiteName" alt="{$labels.comp_name}"
	       value="{$gui->name|escape}" 
	       size="{#CONTAINER_NAME_SIZE#}" maxlength="{#CONTAINER_NAME_MAXLEN#}" />
	{include file="error_icon.tpl" field="testsuiteName"}
 </p>
</div>
<p>
<div class="labelHolder">
<label for="details">{$labels.details}</label>
</div>
<div>
{$details}
</div>