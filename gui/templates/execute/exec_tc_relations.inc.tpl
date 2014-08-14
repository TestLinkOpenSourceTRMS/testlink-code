{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource exec_tc_relations.inc.tpl
@internal revisions
@since 1.9.12
*}
{lang_get var='rel_labels' 
          s='relation_id, relation_type_extended, relation_tcase,  relation_set_by,
             test_case,relations, new_relation, by, title_created, relation_destination_tcase'}

    {if $argsRelSet.num_relations > 0}
    <table class="simple" width="100%">
      <tr>
        <th><nobr>{$rel_labels.relation_id} / {$rel_labels.relation_type_extended}</nobr></th>
        <th colspan="1">{$rel_labels.test_case}</th>
      </tr>
      
      {foreach item=rx from=$argsRelSet.relations}
        {$rel_status=$rx.related_item.status}
        <tr>
          <td class="bold"><nobr>{$rx.id} / {$rx.type_localized|escape}</nobr></td>
          <td>
            <a href="javascript:openTCaseWindow({$rx.related_tcase.testcase_id},{$rx.related_tcase.id})">
            {$rx.related_tcase.fullExternalID|escape}:
            {$rx.related_tcase.name|escape}</a></td>
        </tr>
      {/foreach}
    </table>
    {/if}
