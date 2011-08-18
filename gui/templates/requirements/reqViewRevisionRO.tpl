{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	reqViewRevisionRO.tpl
Purpose: view requirement with version + revision
         READ ONLY

@internal revisions
@since 1.9.4
20110817 - franciscom - TICKET 4702: Requirement View - display log message

*}
{lang_get var='labels' 
          s='relation_id, relation_type, relation_document, relation_status, relation_project,
             relation_set_by, relation_delete, relations, new_relation, by, title_created,
             relation_destination_doc_id, in, btn_add, img_title_delete_relation, current_req,
             no_records_found,other_versions,version,title_test_case,match_count, warning'}

{assign var=this_template_dir value=$smarty.template|dirname}
{config_load file="input_dimensions.conf"}
{assign var="my_style" value=""}
{if $gui->hilite_item_name}
    {assign var="my_style" value="background:#059; color:white; margin:0px 0px 4px 0px;padding:3px;"}
{/if}

{include file="inc_head.tpl" openHead='yes' jsValidate="yes"}
{include file="inc_ext_js.tpl"}

<script type="text/javascript">
{literal}
Ext.onReady(function(){ 
{/literal}
tip4log({$gui->item.target_id});
{literal}
});

/**
 * 
 * @since 1.9.4
 */
function tip4log(itemID)
{
	var fUrl = fRoot+'lib/ajax/getreqlog.php?item_id=';
	new Ext.ToolTip({
        target: 'tooltip-'+itemID,
        width: 500,
        autoLoad:{url: fUrl+itemID},
        dismissDelay: 0,
        trackMouse: true
    });
}
{/literal}
</script>

</head>
<body>
<h1 class="title">{$gui->main_descr|escape}</h1>
<div class="workBack">

{include file="$this_template_dir/reqViewRevisionViewer.tpl" 
         args_req=$gui->item 
         args_gui=$gui
         args_grants=$gui->grants 
         args_can_copy=false
         args_show_version=true
         args_show_title=$gui->show_title
         args_tproject_name=$gui->tproject_name
         args_reqspec_name=$gui->item['req_spec_title']
         args_cf=$gui->cfields}
</div>
</body>
</html>
