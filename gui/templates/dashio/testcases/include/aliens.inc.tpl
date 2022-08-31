{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource aliens.inc.tpl
@used_by 
*}

{lang_get var='alien_labels' 
          s='btn_add,img_title_remove_alien,warning,
             id,description,aliens,
             select_aliens,createAlien,btn_create_and_link,
             alien_reportedBy,alien_handledBy,
             alien_version,alien_fixedInVersion,
             alien_statusVerbose,alien_relTypeVerbose'}


{if $args_edit_enabled}

  {lang_get s='remove_alien_msgbox_msg'   var='remove_alien_msgbox_msg'}
  {lang_get s='remove_alien_msgbox_title' var='remove_alien_msgbox_title'}

  <script type="text/javascript">
    var alert_box_title = "{$alien_labels.warning|escape:'javascript'}";
    var remove_alien_msgbox_msg = '{$remove_alien_msgbox_msg|escape:'javascript'}';
    var remove_alien_msgbox_title = '{$remove_alien_msgbox_title|escape:'javascript'}';
  </script>

  <script type="text/javascript">
    /**
    * 
    *
    */
    function alien_remove_confirmation(item_id, tcalien_link_id, alien, title, msg, pFunction) 
    {
      var my_msg = msg.replace('%i',alien);
      var safe_title = escapeHTML(title);
      Ext.Msg.confirm(safe_title, my_msg,
                      function(btn, text) { 
                        pFunction(btn,text,item_id, tcalien_link_id);
                      });
    }


    /**
    * 
    *
    */
    function remove_alien(btn, text, item_id, tcalien_link_id) 
    {
      var my_url = "{$gui->delTCVAlienURL}";
      var dummy = my_url.replace('%1',item_id);
      var my_action = dummy.replace('%2',tcalien_link_id);


      if( btn == 'yes' ) {
        window.location=my_action;
      }
    }

    var pF_remove_alien = remove_alien;
  </script>
{/if}

{$moreCol = $tlCfg->aliens->moreColumns}

<form method="post" action="{$basehref}lib/testcases/tcEdit.php">
  <input type="hidden" id="alf_doAction" name="doAction"
    value="removeAlien" />
      
  <input type="hidden" name="tcase_id" id="tcase_id" 
    value="{$args_tcase_id}" />

  <input type="hidden" name="tcversion_id" id="tcversion_id"
    value="{$args_tcversion_id}" />

  <input type="hidden" name="tproject_id" id="tproject_id"
    value="{$gui->tproject_id}" />

  {if property_exists($gui,'tplan_id') } 
    <input type="hidden" name="tplan_id" value="{$gui->tplan_id}" />
  {/if}

  {if property_exists($gui,'show_mode') } 
    <input type="hidden" name="show_mode" value="{$gui->show_mode}" />
  {/if}

  {$canWork=1}
  {$mcQty = count($moreCol)}
  {$tcolQty = $mcQty+4}
  <table class="simple" id="aliens">
    {if $args_edit_enabled}
      <br>
      {if $canWork}
        <tr>
          <th colspan="{$tcolQty}">{$alien_labels.aliens}
            &nbsp;
            <input type="text" name="free_aliens[]" 
              id="alien_ref_code"
              placeholder=""
              size="{#ALIENS_SIZE#}"
              maxlength="{#ALIENS_MAXLEN#}" 
              onclick="javascript:this.value=''" required />
                  <input type="submit" value="{$alien_labels.btn_add}"
                    onclick="doAction.value='addAlien'">
          </th>
        </tr>
      {/if}
    {/if} {* Item can be managed *}


    {* Display Existent Items *}
    {$removeEnabled = $args_edit_enabled 
                      && $gui->assign_aliens 
                      && $args_frozen_version == "no"}

    {if $args_aliens_map != null}
      {if $args_edit_enabled == 0}
        <tr>
          <th colspan="{$tcolQty}">{$alien_labels.aliens}
        </tr>
      {/if}
      <tr>
        <th class="clickable_icon"></th>
        <th>{$alien_labels.alien_relTypeVerbose}</th>
        <th>{$alien_labels.id}</th>
        <th><nobr>{$alien_labels.description}</nobr></th>
        {foreach item=ccol from=$moreCol}
          {$lbl = "alien_$ccol"}
          <th>{$alien_labels.$lbl}</th>
        {/foreach}
      </tr>
 
      {$tdPad = " 5px;"}
      {foreach item=tcalien_link_item from=$args_aliens_map}
      <tr>
        <td class="clickable_icon" 
            style="vertical-align:top;padding:{$tdPad}">
            {if $removeEnabled}
              <a href="javascript:alien_remove_confirmation(
                         {$gui->tcase_id},
                         {$tcalien_link_item.tcalien_link},
                         '{$tcalien_link_item.name|escape:'javascript'}',
                         remove_alien_msgbox_title, remove_alien_msgbox_msg, 
                         pF_remove_alien);">
              <img src="{$tlImages.delete}"
                title="{$alien_labels.img_title_remove_alien}" 
                style="border:none" /></a>
            {/if}
        </td>
        <td style="padding:{$tdPad}">
          {$tcalien_link_item.relTypeVerbose|escape}
        </td>        
        <td style="padding:{$tdPad}">
          {$tcalien_link_item.name|escape}
        </td>
        <td style="padding:{$tdPad}">
          {$tcalien_link_item.blob->summaryHTMLString|escape}
        </td>
        {foreach item=ccol from=$moreCol}
          <td style="padding:{$tdPad}">
            {$tcalien_link_item.blob->$ccol}
          </td>
        {/foreach}
      </tr>  
      {/foreach}

    {/if}
    </table>
    </form>
