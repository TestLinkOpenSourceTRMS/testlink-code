{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcView.tpl,v 1.11 2006/03/29 14:33:32 franciscom Exp $
Purpose: smarty template - view test case in test specification

20060316 - franciscom - added action
20060303 - franciscom
*}

{include file="inc_head.tpl" openHead='yes'}
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>
</head>

<body onLoad="viewElement(document.getElementById('other_versions'),false)">

{include file="inc_update.tpl" result=$sqlResult action=$action item="test case" refresh=$refresh_tree}

<div class="workBack">


{if $testcase_curr_version eq null}
		{lang_get s='no_records_found'}
{else}
	
	{* Current active version *}
  {if $testcase_other_versions neq null}
    {assign var="my_delete_version" value="yes"}
  {/if}
  
	{foreach item=my_testcase from=$testcase_curr_version}
			{include file="tcView_viewer.tpl" my_testcase=$my_testcase 
			         can_edit=$can_edit can_move_copy="yes" 
			         can_delete_testcase=$can_delete_testcase
			         can_delete_version=$my_delete_version
			         status_quo=null
			         show_version="yes" show_title="yes"}
	{/foreach}
	
	
	{* Old active version *}
  {if $testcase_other_versions neq null}
    <span style="cursor: pointer" class="type1" onclick="viewElement(document.getElementById('other_versions'),document.getElementById('other_versions').style.display=='none')"> Other Versions </span>
    <div id="other_versions" class="workBack">
  	{foreach item=my_testcase from=$testcase_other_versions}
  	    <span style="cursor: pointer" class="type1" 
  	          onclick="viewElement(document.getElementById('{$my_testcase.version}'),document.getElementById('{$my_testcase.version}').style.display=='none')"> Version {$my_testcase.version} </span>
  	    <br><div id="{$my_testcase.version}" class="workBack">
				{include file="tcView_viewer.tpl" my_testcase=$my_testcase 
				         can_edit=$can_edit can_move_copy="no" 
   			         can_delete_testcase='no'
			           can_delete_version=$can_delete_version
			           status_quo=$status_quo
				         show_version="no" show_title="no"}
  	    </div>
  	    <br>
		{/foreach}
		</div>
  
  	{* ---------------------------------------------------------------- *}
  	{* Force the div of every old version to show closed as first state *}
  	{literal}
  	<script type="text/javascript">
  	{/literal}
  		{foreach item=my_testcase from=$testcase_other_versions}
  	  	  viewElement(document.getElementById('{$my_testcase.version}'),false);
			{/foreach}
  	{literal}
  	</script>
  	{/literal}
  	{* ---------------------------------------------------------------- *}

  
  
  {/if}
	
	
<!--
    <span style="cursor: pointer" onclick="alert('hole');viewElement(document.getElementById('other_versions'),document.getElementById('other_versions').style.display=='none')"> Other Versions </span>
<span 
 onclick="viewElement(document.getElementById('other_versions'),document.getElementById('other_versions').style.display=='none')">Dati generali</span>


    -->	
	
{/if}
</body>
</html>