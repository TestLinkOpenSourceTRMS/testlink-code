{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_testsuite_viewer_rw.tpl,v 1.2 2006/12/31 16:21:45 franciscom Exp $ 

20061230 - franciscom - added use of label TAG
                        Warning CONTAINER* are defined in the includer template
*}
    <p>
		<div class="labelHolder">
		 <label for="name">{lang_get s='comp_name'}</label>
		</div> 
		<div>
			<input type="text" id="name" name="name" alt="{lang_get s='comp_alt_name'}"
			       value="{$name|escape}" 
			       size="{#CONTAINER_NAME_SIZE#}" maxlength="{#CONTAINER_NAME_MAXLEN#}"
			       /></p>
    </div>
    <p>
		<div class="labelHolder">
		<label for="details">{lang_get s='details'}</label>
		</div>
		<div>
		{$details}
		</div>
