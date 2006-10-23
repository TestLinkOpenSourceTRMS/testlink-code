{* 
	TestLink Open Source Project - http://testlink.sourceforge.net/
	$Id: containerDelete.tpl,v 1.8 2006/10/23 20:11:28 schlundus Exp $ 
	Purpose: smarty template - delete containers in test specification
*}
{include file="inc_head.tpl"}

<body>
<div class="workBack">
{include file="inc_title.tpl" title="Delete $level $objectName"}
{include file="inc_update.tpl" result=$sqlResult item=$level refresh="yes"}

{if $sqlResult == ''}

  <br />
  {section name=idx loop=$warning}
  		{lang_get s='test_case'} {$warning[idx]} {lang_get s=$link_msg[idx]}<br>
  {/section}

  
	<h2>{lang_get s='delete_notice'}</h2>

	<p>{lang_get s='question_del'} {$level|escape}?</p>

	<form method="post" 
	      action="lib/testcases/containerEdit.php?sure=yes&amp;objectID={$objectID|escape}">
		<input type="submit" name="delete_testsuite" value="{lang_get s='btn_yes_del_comp'}" />
	</form>
{/if}

{if $refreshTree}
   {include file="inc_refreshTree.tpl"}
{/if}

</div>
</body>
</html>