{* 
Testlink Open Source Project - http://testlink.sourceforge.net/
@filesource	inc_del_onclick.tpl

*}
{include file="inc_ext_js.tpl"}
{lang_get s='Yes' var="yes_b"}
{lang_get s='No' var="no_b"}
{$body_onload="onload=\"init_yes_no_buttons('$yes_b','$no_b');\""}
<script type="text/javascript">
 
/*
  function: action_confirmation

  IMPORTANT/CRITIC: do_action() is a function defined in this file

  args: o_id: can be 
  			  a) object id, id of object on with do_action() will be done.
                 is not a DOM id, but an specific application id.

			  b) can be an string valid to be used as part of URL 
			     this have been added in order to simplify this function 
			     interface
			     Example
			     1766&tproject_id=67
			     
			     1766 -> req spec id TL DBID on what we want to act
			     &tproject_id=67 -> additional info we want be available on
			     					script called to fullfil user request
			     
        o_name: name of object, used to to give user feedback.

        title: pop up title
                    
        msg: can contain a wildcard (%s), that will be replaced
             with o_name.     
  
  returns: 

*/
function action_confirmation(o_id,o_name,title,msg,pFunction)
{
  var safe_name = o_name.escapeHTML();
  var safe_title = title;
  var my_msg = msg.replace('%s',safe_name);
  if (!pFunction)
  {
		pFunction = do_action;
  }
  
  // alert(o_id);
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
function init_yes_no_buttons(yes_btn,no_btn)
{
  Ext.MessageBox.buttonText.yes=yes_btn;
  Ext.MessageBox.buttonText.no=no_btn;
}

/*
  function: 

  args:
  
  returns: 

*/
function do_action(btn, text, o_id)
{ 
  // IMPORTANT:
  // target_action is defined in SMARTY TEMPLATE that is using this logic.
  //
  var my_action='';
  if( btn == 'yes' )
  {
	my_action=target_action+o_id;
	window.location=my_action;
  }
}					
</script>