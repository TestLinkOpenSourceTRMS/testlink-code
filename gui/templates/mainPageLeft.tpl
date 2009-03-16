{* 
 Testlink Open Source Project - http://testlink.sourceforge.net/ 
 $Id: mainPageLeft.tpl,v 1.13 2009/03/16 21:02:08 schlundus Exp $     
 Purpose: smarty template - main page / site map                 
                                                                 
 rev :                                                 
      20081228 - franciscom - new feature user can choose vertical order of link groups
      20070523 - franciscom - test case search link enabled only if session testproject
                              has test cases.
      20070523 - franciscom - new config constant $smarty.const.TL_ITEM_BULLET_IMG
      20070227 - franciscom - fixed minor presentation bug
*}
{lang_get var='labels' s='title_product_mgmt,href_tproject_management,href_admin_modules,
                          href_assign_user_roles,href_cfields_management,
                          href_cfields_tproject_assign,href_keywords_manage,
                          title_user_mgmt,href_user_management,
                          href_roles_management,title_requirements,
                          href_req_spec,href_req_assign,
                          title_test_spec,href_edit_tc,href_browse_tc,href_search_tc,
                          href_print_tc,href_keywords_assign'}



{assign var="menuLayout" value=$tlCfg->gui->layoutMainPageLeft}
{assign var="display_left_block_1" value=false}
{assign var="display_left_block_2" value=false}
{assign var="display_left_block_3" value=false}
{assign var="display_left_block_4" value=false}
{if $sessionProductID && 
	    ($rights_project_edit == "yes" || $tproject_user_role_assignment == "yes" ||
       $cfield_management == "yes" || $rights_keywords_view == "yes")	}
    {assign var="display_left_block_1" value=true}

    <script  type="text/javascript">
    {literal}
    function display_left_block_1()
    {
        var p1 = new Ext.Panel({
                                title: {/literal}'{$labels.title_product_mgmt}'{literal},
                                collapsible:false,
                                collapsed: false,
                                draggable: false,
                                contentEl: 'testproject_topics',
                                baseCls: 'x-tl-panel',
                                bodyStyle: "background:#c8dce8;padding:3px;",
                                renderTo: {/literal}'menu_left_block_{$menuLayout.testProject}'{literal},
                                width:'100%'
                                });
     }
    {/literal}
    </script>
{/if}


{if $usermanagement_rights == "yes" }
    {assign var="display_left_block_2" value=true}

    <script type="text/javascript">
    {literal}
    function display_left_block_2()
    {
        var p1 = new Ext.Panel({
                                title: {/literal}'{$labels.title_user_mgmt}'{literal},
                                collapsible:false,
                                collapsed: false,
                                draggable: false,
                                contentEl: 'usermanagement_topics',
                                bodyStyle: "background:#c8dce8;padding:3px;",
                                renderTo: {/literal}'menu_left_block_{$menuLayout.userAdministration}'{literal},
                                width:'100%'
                                });
     }
    {/literal}
    </script>

{/if}

{if $sessionProductID && $opt_requirements == TRUE && ($rights_reqs_view == "yes" || $rights_reqs_edit == "yes")}
    {assign var="display_left_block_3" value=true}

    <script type="text/javascript">
    {literal}
    function display_left_block_3()
    {
        var p3 = new Ext.Panel({
                                title: {/literal}'{$labels.title_requirements}'{literal},
                                collapsible:false,
                                collapsed: false,
                                draggable: true,
                                contentEl: 'requirements_topics',
                                bodyStyle: "background:#c8dce8;padding:3px;",
                                renderTo: {/literal}'menu_left_block_{$menuLayout.requirements}'{literal},
                                width:'100%'
                                });
     }
    {/literal}
    </script>
{/if}

{if $sessionProductID && $view_tc_rights == "yes"}
    {assign var="display_left_block_4" value=true}

    <script type="text/javascript">
    {literal}
    function display_left_block_4()
    {
        var p4 = new Ext.Panel({
                                title: {/literal}'{$labels.title_test_spec}'{literal},
                                collapsible:false,
                                collapsed: false,
                                draggable: true,
                                contentEl: 'testspecification_topics',
                                bodyStyle: "background:#c8dce8;padding:3px;",
                                renderTo: {/literal}'menu_left_block_{$menuLayout.testSpecification}'{literal},
                                width:'100%'
                                });
     }
    {/literal}
    </script>

{/if}

<div class="vertical_menu" style="float: left">
  {* ---------------------------------------------------------------------------------------- *}
  <div id='menu_left_block_1'></div><br />
  <div id='menu_left_block_2'></div><br />
  <div id="menu_left_block_3"></div><br />
  <div id="menu_left_block_4"></div><br />
  
	{if $display_left_block_1 }
    <div id='testproject_topics'>
	  {if $rights_project_edit == "yes"}
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        <a href="lib/project/projectView.php">{$labels.href_tproject_management}</a>
    {/if}

    {* 
	  {if $rights_configuration == "yes"}
        <br />
   		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        <a href="lib/admin/modules.php">{$labels.href_admin_modules}</a>
      {/if} 
    *}
    
	  {if $tproject_user_role_assignment == "yes"}
        <br />
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        <a href="lib/usermanagement/usersAssign.php?feature=testproject&amp;featureID={$sessionProductID}">{$labels.href_assign_user_roles}</a>
	  {/if}

      {if $cfield_management == "yes"}
	      	<br />
	      	<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
          	<a href="lib/cfields/cfieldsView.php">{$labels.href_cfields_management}</a>
			<br />
         	<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
            <a href="lib/cfields/cfieldsTprojectAssign.php">{$labels.href_cfields_tproject_assign}</a>
      {/if}
	  
	    {* --- keywords management ---  *}
	  {if $rights_keywords_view == "yes"}
			<br />
	  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	        <a href="lib/keywords/keywordsView.php">{$labels.href_keywords_manage}</a>
	  {/if} {* view_keys_rights *}
    </div>
	{/if}
  {* ---------------------------------------------------------------------------------------- *}


  {* ------------------------------------------------- *}
	{if $display_left_block_2 }
    <div id='usermanagement_topics'>
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        <a href="lib/usermanagement/usersView.php">{$labels.href_user_management}</a>
        <br />
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        <a href="lib/usermanagement/rolesView.php">{$labels.href_roles_management}</a>
    </div>
	{/if}
  {* ------------------------------------------------- *}


  {* ---------------------------------------------------------------------------------------- *}
 	{if $display_left_block_3 }
    <div id="requirements_topics" >
      {if $rights_reqs_view == "yes"}
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        <a href="{$launcher}?feature=reqSpecMgmt">{$labels.href_req_spec}</a>
	   	{/if}
		{if $rights_reqs_edit == "yes"}
			<br />
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
       		<a href="lib/general/frmWorkArea.php?feature=assignReqs">{$labels.href_req_assign}</a>
       	{/if}
    </div>
  {/if}
  {* ---------------------------------------------------------------------------------------- *}


  {* ---------------------------------------------------------------------------------------- *}
 	{if $display_left_block_4 }
      <div id="testspecification_topics" >
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
  		<a href="{$launcher}?feature=editTc">
    		{if $modify_tc_rights eq "yes"}
  	      {lang_get s='href_edit_tc'}
  	   {else}
  	      {lang_get s='href_browse_tc'}
  	   {/if}
  	  </a>
      {if $hasTestCases}
      <br />
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
          <a href="{$launcher}?feature=searchTc">{$labels.href_search_tc}</a>
      {/if}    
  		 {if $modify_tc_rights eq "yes"}
  	        <br />
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
          	<a href="{$launcher}?feature=printTestSpec">{$labels.href_print_tc}</a>
  		 {/if}

	  {* --- keywords management ---  *}
	  {if $rights_keywords_view == "yes"}
	    {if $rights_keywords_edit == "yes"}
	        <br />
  			<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        	<a href="{$launcher}?feature=keywordsAssign">{$labels.href_keywords_assign}</a>
		  {/if}
	  {/if}
    </div>
  {/if}
</div>