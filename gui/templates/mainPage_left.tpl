{* 
 Testlink Open Source Project - http://testlink.sourceforge.net/ 
 $Id: mainPage_left.tpl,v 1.2 2007/02/27 15:36:41 franciscom Exp $     
 Purpose: smarty template - main page / site map                 
                                                                 
 rev :                                                 
      20070227 - franciscom - fixed minor presentation bug
*}
<div class="vertical_menu" style="float: left">
  {* ---------------------------------------------------------------------------------------- *}
	{*   tc management   *}
	{if $sessionProductID && $view_tc_rights == "yes"}
  	<div class="module-grey">
    <div>
    <div>
    <div>
      <h3>{lang_get s='title_test_specRRRR'}</h3>
  		<img alt="arrow" class="arrow" src="{$smarty.const.TL_THEME_IMG_DIR}/arrow_org.gif" />
  		<a href="{$launcher}?feature=editTc">
    		{if $modify_tc_rights eq "yes"}
  	      {lang_get s='href_edit_tc'}
  	   {else}
  	      {lang_get s='href_browse_tc'}
  	   {/if}
  	  </a>
      <br />
  		<img alt="arrow" class="arrow" src="{$smarty.const.TL_THEME_IMG_DIR}/arrow_org.gif" />
          <a href="{$launcher}?feature=searchTc">{lang_get s='href_search_tc'}</a>
  		 {if $modify_tc_rights eq "yes"}
  	        <br />
  			<img alt="arrow" class="arrow" src="{$smarty.const.TL_THEME_IMG_DIR}/arrow_org.gif" />
          	<a href="{$launcher}?feature=printTc">{lang_get s='href_print_tc'}</a>
  		 {/if}
      </p>
    </div>
    </div>
    </div>
    </div>
  {/if}
  {* ---------------------------------------------------------------------------------------- *}

  {* ---------------------------------------------------------------------------------------- *}
	{*   requirements   *}
	{if $sessionProductID && $opt_requirements == TRUE && $view_req_rights == "yes"}
  	<div class="module-grey">
    <div>
    <div>
    <div>
        <h3>{lang_get s='title_requirements'}</h3>
		<p>
		<img alt="arrow" class="arrow" src="{$smarty.const.TL_THEME_IMG_DIR}/arrow_org.gif" />
   		<a href="lib/req/reqSpecList.php">{lang_get s='href_req_spec'}</a>
		{if $opt_requirements == TRUE && $modify_req_rights == "yes"}
			<br />
			<img alt="arrow" class="arrow" src="{$smarty.const.TL_THEME_IMG_DIR}/arrow_org.gif" />
       		<a href="lib/general/frmWorkArea.php?feature=assignReqs">{lang_get s='href_req_assign'}</a>
       	{/if}
        </p>

    </div>
    </div>
    </div>
    </div>
  {/if}
  {* ---------------------------------------------------------------------------------------- *}


  {* ---------------------------------------------------------------------------------------- *}
	{*       keywords management                             *}
	
	{if $sessionProductID && $view_keys_rights == "yes"}
  	<div class="module-grey">
    <div>
    <div>
    <div>
        <h3>{lang_get s='title_keywords'}</h3>
		<p>
		{if $modify_keys_rights == "yes"}
			<img alt="arrow" class="arrow" src="{$smarty.const.TL_THEME_IMG_DIR}/arrow_org.gif" />
	        <a href="lib/keywords/keywordsView.php">{lang_get s='href_keywords_manage'}</a>
	        <br />
			<img alt="arrow" class="arrow" src="{$smarty.const.TL_THEME_IMG_DIR}/arrow_org.gif" />
        	<a href="{$launcher}?feature=keywordsAssign">{lang_get s='href_keywords_assign'}</a>
		{else} 		
			<img alt="arrow" class="arrow" src="{$smarty.const.TL_THEME_IMG_DIR}/arrow_org.gif" />
	        <a href="lib/keywords/keywordsView.php">{lang_get s='href_keywords_view'}</a>
	        <br />
		{/if} {* modify_keys_rights *}
    </p>

    </div>
    </div>
    </div>
    </div>
	{/if} {* view_keys_rights *}
  {* ---------------------------------------------------------------------------------------- *}

  {* ---------------------------------------------------------------------------------------- *}
	{if $sessionProductID && 
	   ($modify_product_rights == "yes" || $tproject_user_role_assignment == "yes")}
  	<div class="module-grey">
    <div>
    <div>
    <div>
    <h3>{lang_get s='title_product_mgmt'}</h3>
		<p>
	  {if $modify_product_rights == "yes"}
		    <img alt="arrow" class="arrow" src="{$smarty.const.TL_THEME_IMG_DIR}/arrow_org.gif" />
        <a href="lib/project/projectedit.php?show_create_screen">{lang_get s='href_create_product'}</a>
        <br />
		    <img alt="arrow" class="arrow" src="{$smarty.const.TL_THEME_IMG_DIR}/arrow_org.gif" />
        <a href="lib/project/projectedit.php">{lang_get s='href_edit_product'}</a>
    {/if} {* modify_product_rights *}
    
	  {if $tproject_user_role_assignment == "yes"}
        <br />
		    <img alt="arrow" class="arrow" src="{$smarty.const.TL_THEME_IMG_DIR}/arrow_org.gif" />
        <a href="lib/usermanagement/usersassign.php?feature=testproject&amp;featureID={$sessionProductID}">{lang_get s='href_assign_user_roles'}</a>
	  {/if}
    </div>
    </div>
    </div>
    </div>
	{/if}
  {* ---------------------------------------------------------------------------------------- *}


  {* ---------------------------------------------------------------------------------------- *}
	{*       user management                             *}
	{if $usermanagement_rights == "yes"}
  	<div class="module-grey">
    <div>
    <div>
    <div>
        <h3>{lang_get s='title_user_mgmt'}</h3>
		<p>
		<img alt="arrow" class="arrow" src="{$smarty.const.TL_THEME_IMG_DIR}/arrow_org.gif" />
        <a href="lib/usermanagement/usersedit.php">{lang_get s='href_user_management'}</a>
        <br />
		<img alt="arrow" class="arrow" src="{$smarty.const.TL_THEME_IMG_DIR}/arrow_org.gif" />
        <a href="lib/usermanagement/rolesview.php">{lang_get s='href_roles_management'}</a>
        </p>
    </div>
    </div>
    </div>
    </div>
	{/if}
  {* ---------------------------------------------------------------------------------------- *}


  {* ---------------------------------------------------------------------------------------- *}
	{* Custom field management                            *}
  {if $sessionProductID}
   	{if $cfield_view == "yes" || $cfield_management == "yes"}
   	    <div class="module-grey">
        <div>
        <div>
        <div>
        <h3>{lang_get s='title_cfields_mgmt'}</h3>

	      {if $cfield_management == "yes"}
  	        <p>
		        <img alt="arrow" class="arrow" src="{$smarty.const.TL_THEME_IMG_DIR}/arrow_org.gif" />
            <a href="lib/cfields/cfields_view.php">{lang_get s='href_cfields_management'}</a>
        {/if}     
        <br />
	
        {if $cfield_management == "yes"}
		        <img alt="arrow" class="arrow" src="{$smarty.const.TL_THEME_IMG_DIR}/arrow_org.gif" />
            <a href="lib/cfields/cfields_tproject_assign.php">{lang_get s='href_cfields_tproject_assign'}</a>
        {/if}
        </div>
        </div>
        </div>
        </div>
    {/if}  
  {/if}  
  {* ---------------------------------------------------------------------------------------- *}
</div>