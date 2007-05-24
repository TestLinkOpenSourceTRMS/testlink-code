{* 
 Testlink Open Source Project - http://testlink.sourceforge.net/ 
 $Id: mainPage_left.tpl,v 1.8 2007/05/24 06:49:18 franciscom Exp $     
 Purpose: smarty template - main page / site map                 
                                                                 
 rev :                                                 
      20070523 - franciscom - new config constant $smarty.const.TL_ITEM_BULLET_IMG
      20070227 - franciscom - fixed minor presentation bug
*}
<div class="vertical_menu" style="float: left">
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
      <br />
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
          <a href="{$launcher}?feature=searchTc">{lang_get s='href_search_tc'}</a>
  		 {if $modify_tc_rights eq "yes"}
  	        <br />
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
          	<a href="{$launcher}?feature=printTc">{lang_get s='href_print_tc'}</a>
  		 {/if}
    {$smarty.const.MENU_ITEM_CLOSE}
  {/if}
  {* ---------------------------------------------------------------------------------------- *}

  {* ---------------------------------------------------------------------------------------- *}
	{*   requirements   *}
	{if $sessionProductID && $opt_requirements == TRUE && ($view_req_rights == "yes" || $modify_req_rights == "yes")}
    {$smarty.const.MENU_ITEM_OPEN}
        <h3>{lang_get s='title_requirements'}</h3>
        {if $view_req_rights == "yes"}
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	   		<a href="lib/req/reqSpecList.php">{lang_get s='href_req_spec'}</a>
	   	{/if}
		{if $modify_req_rights == "yes"}
			<br />
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
       		<a href="lib/general/frmWorkArea.php?feature=assignReqs">{lang_get s='href_req_assign'}</a>
       	{/if}
    {$smarty.const.MENU_ITEM_CLOSE}
  {/if}
  {* ---------------------------------------------------------------------------------------- *}


  {* ---------------------------------------------------------------------------------------- *}
	{*       keywords management                             *}
	
	{if $sessionProductID && $view_keys_rights == "yes"}
    {$smarty.const.MENU_ITEM_OPEN}
    <h3>{lang_get s='title_keywords'}</h3>
		{if $modify_keys_rights == "yes"}
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	        <a href="lib/keywords/keywordsView.php">{lang_get s='href_keywords_manage'}</a>
	        <br />
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        	<a href="{$launcher}?feature=keywordsAssign">{lang_get s='href_keywords_assign'}</a>
		{else} 		
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
	        <a href="lib/keywords/keywordsView.php">{lang_get s='href_keywords_view'}</a>
	        <br />
		{/if} {* modify_keys_rights *}
    {$smarty.const.MENU_ITEM_CLOSE}
	{/if} {* view_keys_rights *}
  {* ---------------------------------------------------------------------------------------- *}

  {* ---------------------------------------------------------------------------------------- *}
	{if $sessionProductID && 
	   ($modify_product_rights == "yes" || $tproject_user_role_assignment == "yes")}
    {$smarty.const.MENU_ITEM_OPEN}
    <h3>{lang_get s='title_product_mgmt'}</h3>
	  {if $modify_product_rights == "yes"}
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        <a href="lib/project/projectedit.php?show_create_screen">{lang_get s='href_create_product'}</a>
        <br />
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        <a href="lib/project/projectedit.php">{lang_get s='href_edit_product'}</a>
    {/if} {* modify_product_rights *}
    
	  {if $tproject_user_role_assignment == "yes"}
        <br />
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        <a href="lib/usermanagement/usersassign.php?feature=testproject&amp;featureID={$sessionProductID}">{lang_get s='href_assign_user_roles'}</a>
	  {/if}
    {$smarty.const.MENU_ITEM_CLOSE}
	{/if}
  {* ---------------------------------------------------------------------------------------- *}


  {* ---------------------------------------------------------------------------------------- *}
	{*       user management                             *}
	{if $usermanagement_rights == "yes"}
    {$smarty.const.MENU_ITEM_OPEN}
        <h3>{lang_get s='title_user_mgmt'}</h3>
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        <a href="lib/usermanagement/usersedit.php">{lang_get s='href_user_management'}</a>
        <br />
  		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
        <a href="lib/usermanagement/rolesview.php">{lang_get s='href_roles_management'}</a>
    {$smarty.const.MENU_ITEM_CLOSE}
	{/if}
  {* ---------------------------------------------------------------------------------------- *}


  {* ---------------------------------------------------------------------------------------- *}
	{* Custom field management                            *}
  {if $sessionProductID}
   	{if $cfield_management == "yes"}
    {$smarty.const.MENU_ITEM_OPEN}
        <h3>{lang_get s='title_cfields_mgmt'}</h3>
	      {if $cfield_management == "yes"}
  		      <img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
            <a href="lib/cfields/cfields_view.php">{lang_get s='href_cfields_management'}</a>
	        <br />
         		<img src="{$smarty.const.TL_ITEM_BULLET_IMG}" />
            <a href="lib/cfields/cfields_tproject_assign.php">{lang_get s='href_cfields_tproject_assign'}</a>
        {/if}
    {$smarty.const.MENU_ITEM_CLOSE}
    {/if}  
  {/if}  
  {* ---------------------------------------------------------------------------------------- *}
</div>
