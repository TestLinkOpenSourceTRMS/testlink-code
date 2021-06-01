{*
TestLink Open Source Project - http://testlink.sourceforge.net/ 

@filesource planView.tpl

@internal development hint:
some smarty and javascript variables are created on the inc_*.tpl files.

*}

{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{* Configure Actions *}
{$managerURL="lib/plan/planEdit.php"}
{$editAction="$managerURL?do_action=edit&amp;tplan_id="}
{$deleteAction="$managerURL?do_action=do_delete&tplan_id="}
{$createAction="$managerURL?do_action=create"}
{$exportAction="lib/plan/planExport.php?tplan_id="}
{$importAction="lib/plan/planImport.php?tplan_id="}
{$assignRolesAction="lib/usermanagement/usersAssign.php?featureType=testplan&featureID="}
{$gotoExecuteAction="lib/general/frmWorkArea.php?feature=executeTest&tplan_id="}

{include file="plan/planView.labels.tpl"}

{lang_get s='warning_delete_testplan' var="warning_msg"}
{lang_get s='delete' var="del_msgbox_title"}

{include file="inc_head.tpl" openHead="yes" enableTableSorting="yes"}
{include file="inc_del_onclick.tpl"}

<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'{$deleteAction}';
</script>

{if $tlCfg->gui->planView->pagination->enabled}
  {$menuLen = $tlCfg->gui->planView->pagination->length}
  {include file="DataTables.inc.tpl"}

<script>
$(document).ready(function() {

    // 20210530 
    // stateSave: true produces weird behaivour when using filter on individual columns
    var pimpedTable = $('#item_view').DataTable( {
        orderCellsTop: true,
        fixedHeader: true,
        lengthMenu: [{$menuLen}],
        // https://datatables.net/reference/option/dom
        "dom": 'lrtip'
    } );


    // Setup - add a text input to each footer cell
    // Clone & append the whole header row
    // clone(false) -> is the solution to avoid sort action when clicking 
    $('#item_view thead tr').clone(false).prop("id","column_filters").appendTo( '#item_view thead' );
    $('#item_view thead tr:eq(1) th').each( function (idx) {
        if (typeof  $(this).data('draw-filter') != 'undefined') {
          // var title = $(this).text();
          var title = '';
          var dst = $(this).data('draw-filter');
          switch (dst) {
            case 'regexp':
              title += "regexp";
            break;

            default:
            break;
          }

          var html = '<input type="text" data-search-type="%dst%" placeholder="Filter %title%" />';
          $(this).html(html.replace('%dst%',dst).replace('%title%',title));
              
          $( 'input', this ).on( 'keyup change', function () {
              var use_regexp = false;
              var use_smartsearch = true;
              if ($(this).data('search-type') == "regexp") {
                use_regexp = true;
                use_smartsearch = false;
              }

              if ( pimpedTable.column(idx).search() !== this.value ) {
                  pimpedTable.column(idx)
                             .search( this.value, use_regexp, use_smartsearch )
                             .draw();
              }
          } );        
        } else {
          $(this).html( '' );
        }
    } );
 
} );
</script>
{/if}

{include file="bootstrap.inc.tpl"}
</head>

<body {$body_onload} class="testlink">

<h1 class="title">{$gui->main_descr|escape}</h1>
{if $gui->user_feedback ne ""}
  <div>
    <p class="info">{$gui->user_feedback}</p>
  </div>
{/if}

<div class="page-content">
{if $gui->grants->testplan_create && $gui->tproject_id > 0
    && is_array($gui->tplans)
    && count($gui->tplans) > $tlCfg->gui->planView->itemQtyForTopButton}
   <div class="page-content">
     <form method="post" action="{$createAction}" name="topCreateForm">
       <input type="submit" name="create_testplan_top" value="{$labels.btn_testplan_create}" />
     </form>
   </div>
{/if}

<div id="testplan_management_list">
{if $gui->tproject_id <= 0}
  {$labels.error_no_testprojects_present}
{elseif $gui->tplans eq ''}
  {$labels.testplan_txt_empty_list}
{else}
  <form method="post" id="testPlanView" name="testPlanView" action="{$managerURL}">
    <input type="hidden" name="do_action" id="do_action" value="">
    <input type="hidden" name="tplan_id" id="tplan_id" value="">




  <table id='item_view' class="table table-bordered sortable">
    <thead class="thead-dark">
    <tr>
      <th data-draw-filter="smartsearch">{$tlImages.toggle_api_info}{$tlImages.sort_hint}{$labels.testplan_th_name}</th>       
      <th data-draw-filter="smartsearch" class="{$noSortableColumnClass}">{$labels.testplan_th_notes}</th>

      {if $gui->cfieldsColumns != null}
        {foreach item=cflbl from=$gui->cfieldsColumns}
         <th data-draw-filter="regexp" title="{$cflbl}">{$cflbl}</th>
        {/foreach}
      {/if}

      <th data-draw-filter="smartsearch" title="{$labels.testcase_number_help}">{$tlImages.sort_hint}{$labels.testcase_qty}</th>
      <th data-draw-filter="smartsearch" title="{$labels.build_number_help}">{$tlImages.sort_hint}{$labels.build_qty}</th>
      {if $gui->drawPlatformQtyColumn}
        <th data-draw-filter="regex" title="{$labels.platform_number_help}">{$tlImages.sort_hint}{$labels.platform_qty}</th>
      {/if} 
      <th class="{$noSortableColumnClass}">{$labels.testplan_th_active}</th>
      <th class="{$noSortableColumnClass}">{$labels.public}</th>
      <th class="{$noSortableColumnClass}">&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    {foreach item=testplan from=$gui->tplans}
      <tr data-qa-tplan-name="{$testplan.name|escape}">
        <td><a href="{$editAction}{$testplan.id}"> 
               {$testplan.name|escape}
               <span class="api_info" style='display:none'>{$tlCfg->api->id_format|replace:"%s":$testplan.id}</span>
             
               {if $gsmarty_gui->show_icon_edit}
                   <img title="{$labels.testplan_alt_edit_tp}"  alt="{$labels.testplan_alt_edit_tp}" 
                        src="{$tlImages.edit}"/>
               {/if}  
            </a>
        </td>
   	    <td>{if $gui->editorType == 'none'}{$testplan.notes|nl2br}{else}{$testplan.notes}{/if}</td>
       
        {if $gui->cfieldsColumns != null}
          {foreach item=cflbl from=$gui->cfieldsColumns}
             <td data-sort="{$testplan[$cflbl]['data-order']}">{$testplan[$cflbl]['value']|escape}</td>
          {/foreach}
        {/if}

        <td align="right" style="width:8%;">
          {$testplan.tcase_qty}
        </td>
        <td align="right" style="width:6%;">
          {$testplan.build_qty}
        </td>
        {if $gui->drawPlatformQtyColumn}
          <td align="right" style="width:10%;">
            {$testplan.platform_qty}
          </td>
        {/if} 

        <td class="clickable_icon" data-qa-active="{$testplan.active}">
          {if $testplan.active==1} 
              <input type="image" style="border:none" 
                     title="{$labels.active_click_to_change}" alt="{$labels.active_click_to_change}" 
                     onClick = "do_action.value='setInactive';tplan_id.value={$testplan.id};"
                     src="{$tlImages.on}"/>
            {else}
              <input type="image" style="border:none" 
                   title="{$labels.inactive_click_to_change}" alt="{$labels.inactive_click_to_change}" 
                   onClick = "do_action.value='setActive';tplan_id.value={$testplan.id};"
                   src="{$tlImages.off}"/>
            {/if}
        </td>
        <td class="clickable_icon" data-qa-is_public="{$testplan.is_public}">
          {if $testplan.is_public eq 1} 
              <img style="border:none" title="{$labels.public}"  alt="{$labels.public}" src="{$tlImages.checked}"/>
            {else}
              &nbsp;        
            {/if}
        </td>
        <td style="width:8%;">
            <img style="border:none;cursor: pointer;" 
                 alt="{$labels.testplan_alt_delete_tp}"
               title="{$labels.testplan_alt_delete_tp}" 
               onclick="delete_confirmation({$testplan.id},'{$testplan.name|escape:'javascript'|escape}',
                                            '{$del_msgbox_title}','{$warning_msg}');"
               src="{$tlImages.delete}"/>
            <a href="{$exportAction}{$testplan.id}"> 
            <img style="border:none;cursor: pointer;" alt="{$labels.export_testplan_links}" 
                 title="{$labels.export_testplan_links}" src="{$tlImages.export}"/>
            </a>     
            <a href="{$importAction}{$testplan.id}"> 
            <img style="border:none;cursor: pointer;" alt="{$labels.import_testplan_links}" 
                 title="{$labels.import_testplan_links}"  src="{$tlImages.import}"/>
            </a>     

            {if $testplan.rights.testplan_user_role_assignment}
              <a href="{$assignRolesAction}{$testplan.id}"> 
              <img style="border:none;cursor: pointer;" alt="{$labels.assign_roles}" 
                   title="{$labels.assign_roles}"  src="{$tlImages.user}"/>
              </a>     
            {/if}
            <a href="{$gotoExecuteAction}{$testplan.id}"> 
            <img style="border:none;cursor: pointer;" alt="{$labels.execution}" 
                 title="{$labels.execution}"  src="{$tlImages.execution}"/>
            </a>     
        </td>
      </tr>
    {/foreach}
    </tbody>
  </table>
</form>

{/if}
</div>

 {if $gui->grants->testplan_create && $gui->tproject_id > 0}
 <div class="groupBtn">
    <form method="post" action="{$createAction}" name="bottomCreateForm">
      <input type="submit" name="create_testplan" value="{$labels.btn_testplan_create}" />
    </form>
  </div>
 {/if}
</div>

</body>
</html>