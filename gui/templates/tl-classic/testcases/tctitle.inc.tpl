{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@used-by inc_tcbody.tpl
*}
{$whoaim=$smarty.template|basename|replace:".inc.tpl":""}
<div class="container-fluid" id="{$whoaim}">
  <div class="row" id="title-and-icons"
       style = 
       "background:transparent url(gui/themes/default/images/white-top-bottom.gif) repeat-x 0 -1px;
        background-color: #CDDEF3;padding:    5px 3px 4px 5px;
        border:     1px solid #99bbe8;
        padding:    5px 3px 4px 5px;
        line-height:  15px;
        overflow:    hidden;
        font: bold 11px tahoma,arial,verdana,sans-serif;
        color:      #15428b;">

      {* $tcversion_id inherited  from tcView_viewer.tpl *}
      {if $inc_relations != ''}
        <img class="clickable" src="{$tlImages.relations}"
             title="{$inc_tcbody_labels.tc_has_relations}"
             onclick="document.getElementById('relations_{$tcversion_id}').scrollIntoView();">       
      {/if} 
    
      {$tco.tc_external_id}{$smarty.const.TITLE_SEP}{$tco.name|escape}
      {$smarty.const.TITLE_SEP_TYPE2}{$inc_tcbody_labels.version|escape}{$tco.version}
      <img class="clickable" src="{$tlImages.ghost_item}"
               title="{$inc_tcbody_labels.show_ghost_string}"
               onclick="showHideByDataEntity('ghostTC');">

      <img class="clickable" src="{$tlImages.activity}"
           title="{$inc_tcbody_labels.display_author_updater}"
           onclick="showHideByDataEntity('createUpdate');">
  </div>
  <div class="row" style="display:none;" data-entity="ghostTC">{$tco.ghost}<hr></div> 
  {if $inc_tcbody_author_userinfo != ''}  
    <div class="row" style="display:none;" data-entity="createUpdate">
      {$inc_tcbody_labels.title_created}&nbsp;{localize_timestamp ts=$tco.creation_ts}&nbsp;
      {$inc_tcbody_labels.by}&nbsp;{$inc_tcbody_author_userinfo->getDisplayName()|escape}
    </div>
  {/if}

  {if $tco.updater_id != ''}
    <div class="row" style="display:none;" data-entity="createUpdate">
      {$inc_tcbody_labels.title_last_mod}&nbsp;{localize_timestamp ts=$tco.modification_ts}
      &nbsp;{$inc_tcbody_labels.by}&nbsp;{$inc_tcbody_updater_userinfo->getDisplayName()|escape}
    </div>
  {/if}
  <div class="row" style="display:none;" data-entity="createUpdate"><hr></div>
</div>
