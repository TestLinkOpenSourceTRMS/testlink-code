{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource inc_testsuite_viewer_rw.tpl

Warning CONTAINER* are defined in the includer template
*}
    <p>
		<div class="labelHolder">
		 <label for="name">{$labels.comp_name}</label>
		</div> 
		<div>
			<input type="text" id="name" name="container_name" alt="{lang_get s='comp_alt_name'}"
			       value="{$gui->name|escape}" 
			       size="{#CONTAINER_NAME_SIZE#}" maxlength="{#CONTAINER_NAME_MAXLEN#}"
			       />
			{include file="error_icon.tpl" field="container_name"}
       
	   </p>
    </div>
    <p>
		<div class="labelHolder">
		<label for="details">{$labels.details}</label>
		</div>
		<div>
		{$details}
		</div>