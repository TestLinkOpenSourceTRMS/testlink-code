{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: show_message.tpl,v 1.2 2008/04/15 06:44:22 franciscom Exp $
Purpose: 

rev : 
*}

{include file="inc_head.tpl"}

<body>
<h1>{$gui->main_descr|escape}</h1> 

<div class="workBack">
<h1>{$gui->title}</h1> 
{include file="inc_update.tpl" result=$gui->result user_feedback=$gui->user_feedback refresh=$gui->refresh_tree} 
</div>
</body>
</html>