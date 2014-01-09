{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource printDocOptions.tpl
@internal revisions
@since 1.9.10
*}
{lang_get var="labels"
          s='doc_opt_title,doc_opt_guide,tr_td_show_as,check_uncheck_all_options,build,builds'}

{include file="inc_head.tpl" openHead="yes"}
{include file="inc_ext_js.tpl" bResetEXTCss=1}
{include file="inc_jsCheckboxes.tpl"}

{if $gui->ajaxTree->loadFromChildren}
  <script type="text/javascript">
  /* space after { and before } to signal to smarty that is JS => do not process */
  treeCfg = { tree_div_id:'tree_div',root_name:"",root_id:0,root_href:"",
              loader:"", enableDD:false, dragDropBackEndUrl:'',children:"" };
  </script>
  <script type="text/javascript">
  treeCfg.root_name = '{$gui->ajaxTree->root_node->name|escape:'javascript'}';
  treeCfg.root_id = {$gui->ajaxTree->root_node->id};
  treeCfg.root_href = '{$gui->ajaxTree->root_node->href}';
  treeCfg.children = {$gui->ajaxTree->children}
  treeCfg.cookiePrefix = '{$gui->ajaxTree->cookiePrefix}';
  </script>
  <script type="text/javascript" src='gui/javascript/execTree.js'></script>

{else}
  <script type="text/javascript">
  treeCfg = { tree_div_id:'tree_div',root_name:"",root_id:0,root_href:"",
               loader:"", enableDD:false, dragDropBackEndUrl:'' };
  </script>
  <script type="text/javascript">
  treeCfg.loader = '{$gui->ajaxTree->loader}';
  treeCfg.root_name = '{$gui->ajaxTree->root_node->name|escape:'javascript'}';
  treeCfg.root_id = {$gui->ajaxTree->root_node->id};
  treeCfg.root_href = '{$gui->ajaxTree->root_node->href}';
  treeCfg.enableDD = '{$gui->ajaxTree->dragDrop->enabled}';
  treeCfg.dragDropBackEndUrl = '{$gui->ajaxTree->dragDrop->BackEndUrl}';
  treeCfg.cookiePrefix = '{$gui->ajaxTree->cookiePrefix}';
  </script>
  <script type="text/javascript" src='gui/javascript/treebyloader.js'></script>
{/if} 

{if $gui->buildInfoSet != ''}
<script>
jQuery( document ).ready(function() {
jQuery(".chosen-select").chosen({ width: "100%" });
});
</script>
{/if}

</head>

<body>
<h1 class="title">{$gui->mainTitle} 
                  {if $gui->showHelpIcon}{include file="inc_help.tpl" helptopic="hlp_generateDocOptions" show_help_icon=true}{/if}
                </h1>

<div style="margin: 10px; {if !$gui->showOptions}display:none;{/if}" >
<form method="GET" id="printDocOptions" name="printDocOptions"
      action="lib/results/printDocument.php?type={$gui->doc_type}">

  <input type="hidden" name="docTestPlanId" value="{$docTestPlanId}" />
  <input type="hidden" name="toggle_memory" id="toggle_memory"  value="0" />


  {if $gui->buildInfoSet != ''}
   <table>
    <tr>
     <td><label for="build"> {$labels.build}</label></td>
     <td style="width:200px"> 
      <select class="chosen-select" name="build_id" id="build_id" 
              data-placeholder="{$labels.builds}">
        {foreach key=build_id item=buildObj from=$gui->buildInfoSet}
          <option value="{$build_id}">{$buildObj.name|escape}</option>
        {/foreach}
      </select>
     </td>
    </tr>
   </table>
  {/if}

  <p>{$labels.doc_opt_guide}<br /></p>
  
  <table class="smallGrey" id="optionsContainer" name="optionsContainer">
    {section name=number loop=$gui->outputOptions}
    <tr>
      <td>{$gui->outputOptions[number].description}</td>
      <td>
        <input type="checkbox" name="{$gui->outputOptions[number].value}" id="cb{$gui->outputOptions[number].value}"
        {if $gui->outputOptions[number].checked == 'y'}checked="checked"{/if}/>
      </td>
    </tr>
    {/section}
    <tr>
    {if $docType == 'testspec' || $docType == 'reqspec'}
      <td>{$labels.tr_td_show_as}</td>
      <td>
        <select id="format" name="format">
          {html_options options=$gui->outputFormat selected=$selFormat}
        </select>
      </td>
    {else}
      <td><input type="hidden" id="format" name="format" value="{$selFormat}" /></td>
    {/if}
    </tr>
    

    <tr>
     <td><input type="button" id="toogleOptions" name="toogleOptions"
                onclick='cs_all_checkbox_in_div("optionsContainer","cb","toggle_memory");'
                value="{$labels.check_uncheck_all_options}" /> </td>
    </tr>
  </table>
</form>
</div>

<div id="tree_div" style="overflow:auto; height:100%;border:1px solid #c3daf9;"></div>

</body>
</html>