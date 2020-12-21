{* 
Testlink Open Source Project - http://testlink.sourceforge.net/
@filesource inc_del_onclick.tpl

*}

{include file="inc_ext_js.tpl"}
{lang_get s='Yes' var="yes_b"}
{lang_get s='No' var="no_b"}
{$body_onload = "onload=\"init_yes_no_buttons('$yes_b','$no_b');\""}
<script type="text/javascript">
/*
  function: delete_confirmation

  args: o_id: object id, id of object on with do_action() will be done.
              is not a DOM id, but an specific application id.
              IMPORTANT: defaultAction() is a function defined in this file

        o_name: name of object, used to to give user feedback.

        title: pop up title
                    
        msg: can contain a wildcard (%s), that will be replaced
             with o_name.     
  
  returns: 

*/
function delete_confirmation(o_id,o_name,title,msg,pFunction) {
	var safe_name = escapeHTML(o_name);
  var safe_title = title;
  var my_msg = msg.replace('%s',safe_name);
  if (!pFunction) {
		pFunction = defaultAction;
  }
  Ext.Msg.confirm(safe_title, my_msg,
			            function(btn, text)
			            { 
					         pFunction(btn,text,o_id);
			            });
}

/*
  function: 

  args:
  
  returns: 

*/
function init_yes_no_buttons(yes_btn,no_btn) {
  Ext.MessageBox.buttonText.yes=yes_btn;
  Ext.MessageBox.buttonText.no=no_btn;
}

/*
  function: 

  args:
  
  returns: 

*/
function defaultAction(btn, text, o_id) { 
  // IMPORTANT:
  // del_action is defined in SMARTY TEMPLATE that is using this logic.
  //
	var my_action='';
  
  if( btn == 'yes' ) {
    my_action=del_action+o_id;
	  window.location=my_action;
	}
}					

/*
  function: 

  args:
  
  returns: 

*/
function alert_message(title,msg) {
  Ext.MessageBox.alert(escapeHTML(title), escapeHTML(msg));
}

/**
 * Displays an alert message. title and message must be escaped.
 */
function alert_message_html(title,msg){
  Ext.MessageBox.alert(title, msg);
}


/**
 *
 *
 */
function escapeHTML(str) {
  return str
    .replace(/&/g, '&amp;')
    .replace(/>/g, '&gt;')
    .replace(/</g, '&lt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&apos;');
}

</script>