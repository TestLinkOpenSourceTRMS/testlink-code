{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: containerNew.tpl,v 1.7 2006/02/27 07:59:42 franciscom Exp $
Purpose: smarty template - create containers

20060226 - franciscom - 
*}
{include file="inc_head.tpl"}

<body>
<div class="workBack">
<h1>{lang_get s='title_create'}  {$level|escape}</h1>
	
{include file="inc_update.tpl" result=$sqlResult 
                               item=$level action="add" name=$name
                               refresh="yes"}

<form method="post" action="lib/testcases/containerEdit.php?containerID={$containerID}">
	<div style="font-weight: bold;">
		<div style="float: right;">
			<input type="submit" name="add_testsuite" value="{lang_get s='btn_create_comp'}" />
		</div>	
		{include file="inc_testsuite_viewer_rw.tpl"}
   </div>
</form>
</div>

</body>
</html>