{* 
 Testlink Open Source Project - http://testlink.sourceforge.net/ 
 @filesource  mainPageLeft.tpl
 Purpose: smarty template - main page / site map                 
                                                                 
 @internal revisions
 @since 1.9.10
*}
{lang_get var='labels' s='title_product_mgmt,href_tproject_management,href_admin_modules,
                          href_assign_user_roles,href_cfields_management,system_config,
                          href_cfields_tproject_assign,href_keywords_manage,
                          title_user_mgmt,href_user_management,
                          href_roles_management,title_requirements,
                          href_req_spec,href_req_assign,link_report_test_cases_created_per_user,
                          title_test_spec,href_edit_tc,href_browse_tc,href_search_tc,
                          href_search_req, href_search_req_spec,href_inventory,
                          href_platform_management, href_inventory_management,
                          href_print_tc,href_keywords_assign, href_req_overview,
                          href_print_req, title_documentation,href_issuetracker_management,
                          href_reqmgrsystem_management'}

{$menuLayout=$tlCfg->gui->layoutMainPageLeft}
{$display_left_block_1=false}
{$display_left_block_2=false}
{$display_left_block_3=false}
{$display_left_block_4=false}
{$display_left_block_5=true}

{if $gui->testprojectID && 
      ($gui->grants.project_edit == "yes" || $gui->grants.tproject_user_role_assignment == "yes" ||
       $gui->grants.cfield_management == "yes" || $gui->grants.keywords_view == "yes")}
    {$display_left_block_1=true}

    <script  type="text/javascript">
    function display_left_block_1()
    {
        var p1 = new Ext.Panel({
                                title: '{$labels.title_product_mgmt}',
                                collapsible:false,
                                collapsed: false,
                                draggable: false,
                                contentEl: 'testproject_topics',
                                baseCls: 'x-tl-panel',
                                bodyStyle: "background:#c8dce8;padding:3px;",
                                renderTo: 'menu_left_block_{$menuLayout.testProject}',
                                width:'100%'
                                });
     }
    </script>
{/if}


{if $gui->testprojectID && 
   ($gui->grants.cfield_management == "yes" || $gui->grants.issuetracker_management || $gui->grants.issuetracker_view)}
   {$display_left_block_2=true}

    <script  type="text/javascript">
    function display_left_block_2()
    {
      var p1 = new Ext.Panel({
                              title: '{$labels.system_config}',
                              collapsible:false,
                              collapsed: false,
                              draggable: false,
                              contentEl: 'system_topics',
                              baseCls: 'x-tl-panel',
                              bodyStyle: "background:#c8dce8;padding:3px;",
                              renderTo: 'menu_left_block_2',
                              width:'100%'
                             });
     }
    </script>
{/if}



{if $gui->testprojectID && $opt_requirements == TRUE && ($gui->grants.reqs_view == "yes" || $gui->grants.reqs_edit == "yes")}
    {$display_left_block_3=true}

    <script type="text/javascript">
    function display_left_block_3()
    {
        var p3 = new Ext.Panel({
                                title: '{$labels.title_requirements}',
                                collapsible:false,
                                collapsed: false,
                                draggable: false,
                                contentEl: 'requirements_topics',
                                baseCls: 'x-tl-panel',
                                bodyStyle: "background:#c8dce8;padding:3px;",
                                renderTo: 'menu_left_block_{$menuLayout.requirements}',
                                width:'100%'
                                });
     }
    </script>
{/if}

{if $gui->testprojectID && $gui->grants.view_tc == "yes"}
    {$display_left_block_4=true}

    <script type="text/javascript">
    function display_left_block_4()
    {
        var p4 = new Ext.Panel({
                                title: '{$labels.title_test_spec}',
                                collapsible:false,
                                collapsed: false,
                                draggable: false,
                                contentEl: 'testspecification_topics',
                                baseCls: 'x-tl-panel',
                                bodyStyle: "background:#c8dce8;padding:3px;",
                                renderTo: 'menu_left_block_{$menuLayout.testSpecification}',
                                width:'100%'
                                });
     }
   </script>
{/if}

<script type="text/javascript">
function display_left_block_5()
{
  var p5 = new Ext.Panel({
                          title: '{$labels.title_documentation}',
                          collapsible:false,
                          collapsed: false,
                          draggable: false,
                          contentEl: 'testlink_application',
                          baseCls: 'x-tl-panel',
                          bodyStyle: "background:#c8dce8;padding:3px;",
                          renderTo: 'menu_left_block_{$menuLayout.general}',
                          width:'100%'
                          });
}
</script>

<div class="vertical_menu" style="float: left">
  <div id='menu_left_block_2'></div><br />
  <div id='menu_left_block_1'></div><br />
  <div id="menu_left_block_3"></div><br />
  <div id="menu_left_block_4"></div><br />
  <div id="menu_left_block_5"></div><br />
  
  {if $display_left_block_1}
    <div id='testproject_topics'>
    {if $gui->grants.project_edit == "yes"}
      <img src="{$tlImages.bullet}" />
      <a href="lib/project/projectView.php">{$labels.href_tproject_management}</a>
      <br />
    {/if}
    
    {if $gui->grants.tproject_user_role_assignment == "yes"}
      <img src="{$tlImages.bullet}" />
      <a href="lib/usermanagement/usersAssign.php?featureType=testproject&amp;featureID={$gui->testprojectID}">{$labels.href_assign_user_roles}</a>
      <br />
    {/if}

    {if $gui->grants.cfield_management == "yes"}
      <img src="{$tlImages.bullet}" />
      <a href="lib/cfields/cfieldsTprojectAssign.php">{$labels.href_cfields_tproject_assign}</a>
      <br />
    {/if}
    
    {if $gui->grants.keywords_view == "yes"}
      <img src="{$tlImages.bullet}" />
      <a href="lib/keywords/keywordsView.php">{$labels.href_keywords_manage}</a>
      <br />
    {/if} {* view_keys_rights *}
    
    {if $gui->grants.platform_management == "yes"}
      <img src="{$tlImages.bullet}" />
      <a href="lib/platforms/platformsView.php">{$labels.href_platform_management}</a>
      <br />
    {/if}

    {if $gui->grants.project_inventory_view}
      <img src="{$tlImages.bullet}" />
      <a href="lib/inventory/inventoryView.php">{$labels.href_inventory}</a>
    {/if}
    </div>
  {/if}

  {* ------------------------------------------------- *}
  {if $display_left_block_2}
    <div id='system_topics'>
    {if $gui->grants.cfield_management == "yes"}
      <img src="{$tlImages.bullet}" />
      <a href="lib/cfields/cfieldsView.php">{$labels.href_cfields_management}</a>
      <br />
    {/if}
     
    {if $gui->grants.issuetracker_management || $gui->grants.issuetracker_view}
      <img src="{$tlImages.bullet}" />
      <a href="lib/issuetrackers/issueTrackerView.php">{$labels.href_issuetracker_management}</a>
    {/if}
    </div>
  {/if}
  {* ------------------------------------------------- *}


  {* ---------------------------------------------------------------------------------------- *}
   {if $display_left_block_3}
    <div id="requirements_topics" >
      {if $gui->grants.reqs_view == "yes"}

      <img src="{$tlImages.bullet}" />
        <a href="{$gui->launcher}?feature=reqSpecMgmt">{$labels.href_req_spec}</a><br/>
        <img src="{$tlImages.bullet}" />
        <a href="lib/requirements/reqOverview.php">{$labels.href_req_overview}</a><br/>

        <img src="{$tlImages.bullet}" />
        <a href="{$gui->launcher}?feature=searchReq">{$labels.href_search_req}</a><br/>
        <img src="{$tlImages.bullet}" />
        <a href="{$gui->launcher}?feature=searchReqSpec">{$labels.href_search_req_spec}</a>
      <br />
     {/if}
       
    {if $gui->grants.reqs_edit == "yes"}
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
      
    {if $gui->hasKeywords}  
      {if $gui->grants.keywords_view == "yes"}
        {if $gui->grants.keywords_edit == "yes"}
            <br />
          <img src="{$tlImages.bullet}" />
            <a href="{$gui->launcher}?feature=keywordsAssign">{$labels.href_keywords_assign}</a>
        {/if}
      {/if}
    {/if}
      
     {if $gui->grants.modify_tc eq "yes"}
        {* 
        <br />
        <img src="{$tlImages.bullet}" />
        <a href="{$gui->launcher}?feature=printTestSpec">{$labels.href_print_tc}</a>
        *}
        <br />
        <img src="{$tlImages.bullet}" />
        <a href="lib/results/tcCreatedPerUserOnTestProject.php?do_action=uinput&tproject_id={$gui->testprojectID}">{$labels.link_report_test_cases_created_per_user}</a>
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