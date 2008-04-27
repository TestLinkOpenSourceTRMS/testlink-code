{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcView.tpl,v 1.7 2008/04/27 17:35:23 franciscom Exp $
Purpose: smarty template - view test case in test specification
rev: 20080322 - franciscom - php errors clean up
*}

{include file="inc_head.tpl" openHead='yes'}
<script language="JavaScript" src="gui/javascript/expandAndCollapseFunctions.js" type="text/javascript"></script>

{if $smarty.const.USE_EXT_JS_LIBRARY}
  {include file="inc_ext_js.tpl" css_only=1}
{/if}

</head>

{lang_get var='labels' 
          s='no_records_found,other_versions,version,title_test_case'}

<body onLoad="viewElement(document.getElementById('other_versions'),false)">
<h1>{$labels.title_test_case}{$tlCfg->gui->title_sep_1} {$gui->tc_current_version[0][0].name|escape} </h1>

<div class="workBack">
{include file="inc_update.tpl" user_feedback=$user_feedback refresh=$refresh_tree}

{assign var=this_template_dir value=$smarty.template|dirname}

{if $gui->tc_current_version}
{section name=idx loop=$gui->tc_current_version}
    {* Current active version *}
    {if $testcase_other_versions[idx] neq null}
        {assign var="my_delete_version" value="yes"}
    {else}
        {assign var="my_delete_version" value="no"}
    {/if}

    {* added args_cf *}
		{include file="$this_template_dir/tcView_viewer.tpl" 
		         args_testcase=$gui->tc_current_version[idx][0]
		         args_keywords_map=$keywords_map[idx] 
		         args_reqs=$arrReqs[idx] 
		         args_status_quo=$status_quo[idx]

		         args_can_edit=$can_edit 
		         args_can_move_copy="yes" 
		         args_can_delete_testcase=$can_delete_testcase
		         args_can_delete_version=$my_delete_version
		         args_show_version="yes" 
		         args_show_title="yes"
		         
		         args_activate_deactivate_name='activate'
		         args_activate_deactivate='bnt_activate'
		         args_cf=$cf[idx] 
		         args_tcase_cfg=$tcase_cfg
		         args_users=$users

		         args_tproject_name=$tprojectName
		         args_tsuite_name=$parentTestSuiteName
		         }
		
		{assign var="tcID" value=$gui->tc_current_version[idx][0].testcase_id}
		{assign var="bDownloadOnly" value=false}
		{if $can_edit neq 'yes'}
			{assign var="bDownloadOnly" value=true}
		{/if}
		{include file="inc_attachments.tpl" id=$tcID  tableName="nodes_hierarchy"
		         attachmentInfos=$attachments[$tcID]  downloadOnly=$bDownloadOnly}

    {* Other Versions *}
    {if $testcase_other_versions[idx] neq null}
        {assign var="vid" value=$gui->tc_current_version[idx][0].id}
        {assign var="div_id" value=vers_$vid}
        {assign var="memstatus_id" value=mem_$div_id}
  
        {include file="inc_show_hide_mgmt.tpl" 
                 args_container_title=$labels.other_versions
                 args_container_id=$div_id
                 args_container_view_status_id=$memstatus_id}
               
        <div id="vers_{$vid}" class="workBack">
        
  	    {foreach item=my_testcase from=$testcase_other_versions[idx]}

            {assign var="version_num" value=$my_testcase.version}
            {assign var="title" value="$labels.version}
            {assign var="title" value="$title $version_num"}
            
            {assign var="div_id" value=v_$vid}
            {assign var="sep" value="_"}
            {assign var="div_id" value=$div_id$sep$version_num}
            {assign var="memstatus_id" value=mem_$div_id}
           
            {include file="inc_show_hide_mgmt.tpl" 
                     args_container_title=$title
                     args_container_id=$div_id
                     args_container_view_status_id=$memstatus_id}
 
  	          <div id="{$div_id}" class="workBack">
				      {include file="$this_template_dir/tcView_viewer.tpl" 
                       args_testcase=$my_testcase 
                       args_keywords_map=$keywords_map[idx] 
                       args_reqs=$arrReqs[idx]
                       args_status_quo=$status_quo[idx]
                       			
                       args_can_edit=$can_edit 
                       args_can_move_copy="no" 
                       args_can_delete_testcase='no'
                       args_can_delete_version=$can_delete_version
                       args_show_version="no" 
                       args_show_title="no"
                       args_users=$users
                       args_cf=$cf[idx] 
                       }
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
  	  	      viewElement(document.getElementById('v_{$vid}_{$my_testcase.version}'),false);
			      {/foreach}
      	{literal}
      	</script>
      	{/literal}
      	{* ---------------------------------------------------------------- *}
    {/if}
{/section}
{else}
	{$labels.no_records_found}
{/if}

</div>
</body>
</html>
