{* 
 Testlink Open Source Project - http://testlink.sourceforge.net/ 
 $Id: mainPageLeft.tpl,v 1.5 2008/01/25 09:59:19 havlat Exp $     
 Purpose: smarty template - main page / site map                 
                                                                 
 rev :                                                 
      20070523 - franciscom - test case search link enabled only if session testproject
                              has test cases.
      20070523 - franciscom - new config constant $smarty.const.TL_ITEM_BULLET_IMG
      20070227 - franciscom - fixed minor presentation bug
*}
<div class="vertical_menu" style="float: left">


  {* ---------------------------------------------------------------------------------------- *}
	{if $sessionProductID}
    {$smarty.const.MENU_ITEM_OPEN}
    <h3>{lang_get s='title_product_mgmt'}</h3>

	  {if $rights_project_edit == "yes"}
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        <a href="lib/project/projectView.php">{lang_get s='href_tproject_management'}</a>
      {/if} {* modify_product_rights *}

	  {if $rights_configuration == "yes"}
        <br />
   		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        <a href="lib/admin/modules.php">{lang_get s='href_admin_modules'}</a>
      {/if} {* configuration_rights *}
    
	  {if $tproject_user_role_assignment == "yes"}
        <br />
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        <a href="lib/usermanagement/usersAssign.php?feature=testproject&amp;featureID={$sessionProductID}">{lang_get s='href_assign_user_roles'}</a>
	  {/if}

      {if $cfield_management == "yes"}
	      	<br />
	      	<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
          	<a href="lib/cfields/cfieldsView.php">{lang_get s='href_cfields_management'}</a>
			<br />
         	<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
            <a href="lib/cfields/cfieldsTprojectAssign.php">{lang_get s='href_cfields_tproject_assign'}</a>
      {/if}
      <br />
	  <img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	  <a href="lib/events/eventviewer.php">Eventviewer</a>
    {$smarty.const.MENU_ITEM_CLOSE}
	{/if}

  {* ---------------------------------------------------------------------------------------- *}
	{*       user management                             *}
	{if $usermanagement_rights == "yes"}
    {$smarty.const.MENU_ITEM_OPEN}
        <h3>{lang_get s='title_user_mgmt'}</h3>
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        <a href="lib/usermanagement/usersEdit.php">{lang_get s='href_user_management'}</a>
        <br />
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        <a href="lib/usermanagement/rolesView.php">{lang_get s='href_roles_management'}</a>
    {$smarty.const.MENU_ITEM_CLOSE}
	{/if}


  {* ---------------------------------------------------------------------------------------- *}
	{*   requirements   *}
	{if $sessionProductID && $opt_requirements == TRUE && ($rights_reqs_view == "yes" || $rights_reqs_edit == "yes")}
    {$smarty.const.MENU_ITEM_OPEN}
        <h3>{lang_get s='title_requirements'}</h3>
      {if $rights_reqs_view == "yes"}
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        <a href="{$launcher}?feature=reqSpecMgmt">{lang_get s='href_req_spec'}</a>
	   	{/if}
		{if $rights_reqs_edit == "yes"}
			<br />
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
       		<a href="lib/general/frmWorkArea.php?feature=assignReqs">{lang_get s='href_req_assign'}</a>
       	{/if}
    {$smarty.const.MENU_ITEM_CLOSE}
  {/if}


  {* ---------------------------------------------------------------------------------------- *}
	{*   tc management   *}
	{if $sessionProductID && $view_tc_rights == "yes"}
      {$smarty.const.MENU_ITEM_OPEN}
      <h3>{lang_get s='title_test_spec'}</h3>
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
          <a href="{$launcher}?feature=searchTc">{lang_get s='href_search_tc'}</a>
      {/if}    
  		 {if $modify_tc_rights eq "yes"}
  	        <br />
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
          	<a href="{$launcher}?feature=printTestSpec">{lang_get s='href_print_tc'}</a>
  		 {/if}

	  {* --- keywords management ---  *}
	  {if $rights_keywords_view == "yes"}
			<br />
	  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	        <a href="lib/keywords/keywordsView.php">{lang_get s='href_keywords_manage'}</a>
	    {if $rights_keywords_edit == "yes"}
	        <br />
  			<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        	<a href="{$launcher}?feature=keywordsAssign">{lang_get s='href_keywords_assign'}</a>
		{/if} {* modify_keys_rights *}
	  {/if} {* view_keys_rights *}

      {$smarty.const.MENU_ITEM_CLOSE}
  {/if}


</div>
