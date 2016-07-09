{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_testsuite_viewer_rw.tpl
*}
<p>
<div class="labelHolder"><label for="name">{lang_get s='comp_name'}</label></div> 
<div>
  <input type="text" id="name" name="container_name" title="{lang_get s='comp_alt_name'}"
         value="{$name|escape}" 
         onchange="content_modified = true"  onkeypress="content_modified = true"
         size="{#CONTAINER_NAME_SIZE#}" maxlength="{#CONTAINER_NAME_MAXLEN#}" required />
  {include file="error_icon.tpl" field="container_name"}
</div>
<p>
<div class="labelHolder"><label for="details">{lang_get s='details'}</label></div>
<div>
{$details}
</div>