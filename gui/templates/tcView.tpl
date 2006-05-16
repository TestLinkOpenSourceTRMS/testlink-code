{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcView.tpl,v 1.16 2006/05/16 19:35:40 schlundus Exp $
Purpose: smarty template - view test case in test specification

20060425 - franciscom - can manage multiple test cases
*}

{include file="inc_head.tpl" openHead='yes'}
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>
</head>

<body onLoad="viewElement(document.getElementById('other_versions'),false)">

{include file="inc_update.tpl" result=$sqlResult action=$action item="test case" refresh=$refresh_tree}

<div class="workBack">

{if $testcase_curr_version}
{section name=idx loop=$testcase_curr_version}
    {* Current active version *}
    {if $testcase_other_versions[idx] neq null}
        {assign var="my_delete_version" value="yes"}
    {/if}

		{include file="tcView_viewer.tpl" 
		         args_testcase=$testcase_curr_version[idx][0]
		         args_keywords_map=$keywords_map[idx] 
		         args_reqs=$arrReqs[idx] 
		         args_status_quo=$status_quo[idx]

		         args_can_edit=$can_edit 
		         args_can_move_copy="yes" 
		         args_can_delete_testcase=$can_delete_testcase
		         args_can_delete_version=$my_delete_version
		         args_show_version="yes" 
		         args_show_title="yes"}


    {* Other Versions *}
    {if $testcase_other_versions[idx] neq null}
        {assign var="vid" value=$testcase_curr_version[idx][0].id}
         
        <span style="cursor: pointer" class="type1" 
              onclick="viewElement(document.getElementById('vers_{$vid}'),document.getElementById('vers_{$vid}').style.display=='none')"> Other Versions </span>
        <div id="vers_{$vid}" class="workBack">
        
  	    {foreach item=my_testcase from=$testcase_other_versions[idx]}
  	          <span style="cursor: pointer" class="type1" 
  	                onclick="viewElement(document.getElementById('{$my_testcase.version}'),document.getElementById('{$my_testcase.version}').style.display=='none')"> Version {$my_testcase.version} </span>
  	          <br />
  	          <div id="{$my_testcase.version}" class="workBack">
				
				      {include file="tcView_viewer.tpl" 
						args_testcase=$my_testcase 
						args_keywords_map=$keywords_map[idx] 
						args_reqs=$arrReqs[idx]
						args_status_quo=$status_quo[idx]
						
						args_can_edit=$can_edit 
						args_can_move_copy="no" 
						args_can_delete_testcase='no'
						args_can_delete_version=$can_delete_version
						args_show_version="no" 
						args_show_title="no"}
  	         </div>
  	         <br />
  	         
		    {/foreach}
		    </div>
  
      	{* ---------------------------------------------------------------- *}
      	{* Force the div of every old version to show closed as first state *}
      	{literal}
      	<script type="text/javascript">
      	{/literal}
 	  	      viewElement(document.getElementById('vers_{$vid}'),false);

    	  		{foreach item=my_testcase from=$testcase_other_versions[idx]}
  	  	      viewElement(document.getElementById('{$my_testcase.version}'),false);
			      {/foreach}
      	{literal}
      	</script>
      	{/literal}
      	{* ---------------------------------------------------------------- *}
    {/if}
{/section}
{else}
	{lang_get s='no_records_found'}
{/if}

</div>
</body>
</html>