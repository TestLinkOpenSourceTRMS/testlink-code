{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource reqSpecViewJS.inc.tpl
*}
<script type="text/javascript">
/* All this stuff is needed for logic contained in inc_del_onclick.tpl */
var del_action=fRoot+'{$req_module}reqSpecEdit.php?doAction=doDelete&req_spec_id=';
var log_box_title = "{$labels.commit_title|escape:'javascript'}";
var log_box_text = "{$labels.please_add_revision_log|escape:'javascript'}";


Ext.onReady(function(){ 
tip4log({$gui->req_spec.revision_id});
});
  
  
/**
 * when user put mouse over history icon, ajax call is done 
 * to get history.
 * ATTENTION:
 * seems that this get is done ONLY firts time, this means
 * that if other feature update the log, here user will be
 * continue to see the old data.
 * IMHO is not elegant, but is not a big issue.
 * 
 * @since 1.9.4
 */
function tip4log(itemID)
{
  var fUrl = fRoot+'lib/ajax/getreqspeclog.php?item_id=';
  new Ext.ToolTip({
          target: 'tooltip-'+itemID,
          width: 500,
          autoLoad:{ url: fUrl+itemID },
          dismissDelay: 0,
          trackMouse: true
      });
}
  
function freeze_req_spec(btn, text, o_id) 
{
  var my_action=fRoot+'lib/requirements/reqSpecEdit.php?doAction=doFreeze&req_spec_id=';
  if( btn == 'yes' ) 
  {
    my_action = my_action+o_id;
    window.location=my_action;
  }
}


/**
 * 
 *
 */
function ask4log(fid,tid)
{
  var target = document.getElementById(tid);
  var my_form = document.getElementById(fid);
  Ext.Msg.prompt(log_box_title, log_box_text, function(btn, text){
        if (btn == 'ok')
        {
            target.value=text;
            my_form.submit();
        }
  },this,true);    
  return false;    
} 

/**
 * Be Carefull this TRUST on existence of $gui->delAttachmentURL
 */
function jsCallDeleteFile(btn, text, o_id)
{ 
  var my_action='';
  if( btn == 'yes' )
  {
    my_action='{$gui->delAttachmentURL}'+o_id;
    window.location=my_action;
  }
}        

var pF_freeze_req_spec = freeze_req_spec;
</script>
