{* 
 Testlink Open Source Project - http://testlink.sourceforge.net/ 
 $Id: mainPageLeft.tpl,v 1.32 2010/10/30 07:49:48 franciscom Exp $     
 Purpose: smarty template - main page / site map                 
                                                                 
 rev :
    20100501 - Julian - blocks are not draggable anymore
    20100501 - franciscom - BUGID 3410: Smarty 3.0 compatibility
 	  20100309 - asimon - BUGID 3227, added link for req overview page
 	  20100106 - asimon - contribution for 2976 req/reqspec search                                    
    20090808 - franciscom - grouping rights on gui->grants
*}
{lang_get var='labels' s='title_product_mgmt,href_tproject_management,href_admin_modules,
                          href_assign_user_roles,href_cfields_management,
                          href_cfields_tproject_assign,href_keywords_manage,
                          title_user_mgmt,href_user_management,
                          href_roles_management,title_requirements,
                          href_req_spec,href_req_assign,
                          title_test_spec,href_edit_tc,href_browse_tc,href_search_tc,
                          href_search_req, href_search_req_spec,href_inventory,
                          href_platform_management, href_inventory_management,
                          href_print_tc,href_keywords_assign, href_req_overview,
                          href_print_req, title_documentation'}

{assign var="menuLayout" value=$tlCfg->gui->layoutMainPageLeft}
{assign var="display_left_block_1" value=false}
{assign var="display_left_block_2" value=false}
{assign var="display_left_block_3" value=false}
{assign var="display_left_block_4" value=false}
{assign var="display_left_block_5" value=true}
{if $gui->testprojectID && 
	    ($gui->grants.project_edit == "yes" || $gui->grants.tproject_user_role_assignment == "yes" ||
       $gui->grants.cfield_management == "yes" || $gui->grants.keywords_view == "yes")}
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


{if $gui->grants.mgt_users == "yes"}
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
                                baseCls: 'x-tl-panel',
                                bodyStyle: "background:#c8dce8;padding:3px;",
                                renderTo: {/literal}'menu_left_block_{$menuLayout.userAdministration}'{literal},
                                width:'100%'
                                });
     }
    {/literal}
    </script>

{/if}

{if $gui->testprojectID && $opt_requirements == TRUE && ($gui->grants.reqs_view == "yes" || $gui->grants.reqs_edit == "yes")}
    {assign var="display_left_block_3" value=true}

    <script type="text/javascript">
    {literal}
    function display_left_block_3()
    {
        var p3 = new Ext.Panel({
                                title: {/literal}'{$labels.title_requirements}'{literal},
                                collapsible:false,
                                collapsed: false,
                                draggable: false,
                                contentEl: 'requirements_topics',
                                baseCls: 'x-tl-panel',
                                bodyStyle: "background:#c8dce8;padding:3px;",
                                renderTo: {/literal}'menu_left_block_{$menuLayout.requirements}'{literal},
                                width:'100%'
                                });
     }
    {/literal}
    </script>
{/if}

{if $gui->testprojectID && $gui->grants.view_tc == "yes"}
    {assign var="display_left_block_4" value=true}

    <script type="text/javascript">
    {literal}
    function display_left_block_4()
    {
        var p4 = new Ext.Panel({
                                title: {/literal}'{$labels.title_test_spec}'{literal},
                                collapsible:false,
                                collapsed: false,
                                draggable: false,
                                contentEl: 'testspecification_topics',
                                baseCls: 'x-tl-panel',
                                bodyStyle: "background:#c8dce8;padding:3px;",
                                renderTo: {/literal}'menu_left_block_{$menuLayout.testSpecification}'{literal},
                                width:'100%'
                                });
     }
    {/literal}
    </script>
{/if}

    <script type="text/javascript">
    {literal}
    function display_left_block_5()
    {
        var p5 = new Ext.Panel({
                                title: {/literal}'{$labels.title_documentation}'{literal},
                                collapsible:false,
                                collapsed: false,
                                draggable: false,
                                contentEl: 'testlink_application',
                                baseCls: 'x-tl-panel',
                                bodyStyle: "background:#c8dce8;padding:3px;",
                                renderTo: {/literal}'menu_left_block_{$menuLayout.general}'{literal},
                                width:'100%'
                                });
	}
    {/literal}
    </script>

<div class="vertical_menu" style="float: left">
  {* ---------------------------------------------------------------------------------------- *}
  <div id='menu_left_block_1'></div><br />
  <div id='menu_left_block_2'></div><br />
  <div id="menu_left_block_3"></div><br />
  <div id="menu_left_block_4"></div><br />
  <div id="menu_left_block_5"></div><br />
  
	{if $display_left_block_1}
    <div id='testproject_topics'>
	  {if $gui->grants.project_edit == "yes"}
  		<img src="{$tlImages.bullet}" />
        <a href="lib/project/projectView.php">{$labels.href_tproject_management}</a>
    {/if}

    {* 
	  {if $gui->grants.configuration == "yes"}
        <br />
   		<img src="{$tlImages.bullet}" />
        <a href="lib/admin/modules.php">{$labels.href_admin_modules}</a>
      {/if} 
    *}
    
	  {if $gui->grants.tproject_user_role_assignment == "yes"}
        <br />
  		<img src="{$tlImages.bullet}" />
        <a href="lib/usermanagement/usersAssign.php?featureType=testproject&amp;featureID={$gui->testprojectID}">{$labels.href_assign_user_roles}</a>
	  {/if}

      {if $gui->grants.cfield_management == "yes"}
	      	<br />
	      	<img src="{$tlImages.bullet}" />
          	<a href="lib/cfields/cfieldsView.php">{$labels.href_cfields_management}</a>
			<br />
         	<img src="{$tlImages.bullet}" />
            <a href="lib/cfields/cfieldsTprojectAssign.php">{$labels.href_cfields_tproject_assign}</a>
      {/if}
	  
	  {* --- keywords management ---  *}
	  {if $gui->grants.keywords_view == "yes"}
			<br />
	  		<img src="{$tlImages.bullet}" />
	        <a href="lib/keywords/keywordsView.php">{$labels.href_keywords_manage}</a>
	  {/if} {* view_keys_rights *}
	  
 		{* --- platforms management ---  *}
		{if $gui->grants.platform_management == "yes"}
			<br />
	  		<img src="{$tlImages.bullet}" />
			<a href="lib/platforms/platformsView.php">{$labels.href_platform_management}</a>
		{/if}

 		{* --- inventory view ---  *}
		{if $gui->grants.project_inventory_view}
			<br />
	  		<img src="{$tlImages.bullet}" />
			<a href="lib/inventory/inventoryView.php">{$labels.href_inventory}</a>
		{/if}
	  
    </div>
	{/if}
  {* ---------------------------------------------------------------------------------------- *}


  {* ------------------------------------------------- *}
	{if $display_left_block_2}
    <div id='usermanagement_topics'>
  		<img src="{$tlImages.bullet}" />
        <a href="lib/usermanagement/usersView.php">{$labels.href_user_management}</a>
        <br />
  		<img src="{$tlImages.bullet}" />
        <a href="lib/usermanagement/rolesView.php">{$labels.href_roles_management}</a>
    </div>
	{/if}
  {* ------------------------------------------------- *}


  {* ---------------------------------------------------------------------------------------- *}
 	{if $display_left_block_3}
    <div id="requirements_topics" >
      {if $gui->grants.reqs_view == "yes"}
  		<img src="{$tlImages.bullet}" />
        <a href="{$gui->launcher}?feature=reqSpecMgmt">{$labels.href_req_spec}</a><br/>
        
        {* BUGID 3227 *}
        <img src="{$tlImages.bullet}" />
        <a href="lib/requirements/reqOverview.php">{$labels.href_req_overview}</a><br/>
        
        {* contribution for 2976 req/reqspec search *}
        <img src="{$tlImages.bullet}" />
        <a href="{$gui->launcher}?feature=searchReq">{$labels.href_search_req}</a><br/>
        <img src="{$tlImages.bullet}" />
        <a href="{$gui->launcher}?feature=searchReqSpec">{$labels.href_search_req_spec}</a>
        
	   	{/if}
	   	
		{if $gui->grants.reqs_edit == "yes"}
			<br />
  		<img src="{$tlImages.bullet}" />
       		<a href="lib/general/frmWorkArea.php?feature=assignReqs">{$labels.href_req_assign}</a>

  	        <br />
  		<img src="{$tlImages.bullet}" />
          	<a href="{$gui->launcher}?feature=printReqSpec">{$labels.href_print_req}</a>
  		 {/if}
    </div>
  {/if}
  {* ---------------------------------------------------------------------------------------- *}


  {* ---------------------------------------------------------------------------------------- *}
 	{if $display_left_block_4}
      <div id="testspecification_topics" >
  		<img src="{$tlImages.bullet}" />
  		<a href="{$gui->launcher}?feature=editTc">
    		{if $gui->grants.modify_tc eq "yes"}
  	      {lang_get s='href_edit_tc'}
  	   {else}
  	      {lang_get s='href_browse_tc'}
  	   {/if}
  	  </a>
      {if $gui->hasTestCases}
      <br />
  		<img src="{$tlImages.bullet}" />
          <a href="{$gui->launcher}?feature=searchTc">{$labels.href_search_tc}</a>
      {/if}    
  		
	  {* --- keywords management ---  *}
	  {if $gui->grants.keywords_view == "yes"}
	    {if $gui->grants.keywords_edit == "yes"}
	        <br />
  			<img src="{$tlImages.bullet}" />
        	<a href="{$gui->launcher}?feature=keywordsAssign">{$labels.href_keywords_assign}</a>
		  {/if}
	  {/if}
  		
  	 {if $gui->grants.modify_tc eq "yes"}
          <br />
  		  <img src="{$tlImages.bullet}" />
          <a href="{$gui->launcher}?feature=printTestSpec">{$labels.href_print_tc}</a>
  	 {/if}

	  
    </div>
  {/if}

    <div id='testlink_application'>
  		<img src="{$tlImages.bullet}" />
		<form style="display:inline;">
    	<select class="menu_combo" style="font-weight:normal;" name="docs" size="1"
            	onchange="javascript:get_docs(this.form.docs.options[this.form.docs.selectedIndex].value, 
            	'{$basehref}');" >
        	<option value="leer"> -{lang_get s='access_doc'}-</option>
        	{if $gui->docs}
            {foreach from=$gui->docs item=doc}
                <option value="{$doc}">{$doc}</option>
            {/foreach}
        	{/if}
    	</select>
		</form>
    </div>


</div>
