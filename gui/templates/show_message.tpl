{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: show_message.tpl,v 1.1 2007/11/19 21:05:11 franciscom Exp $
Purpose: smarty template - delete test case in test specification

rev : 
*}

{include file="inc_head.tpl"}

<body>
<h1>{$item_type}{$smarty.const.TITLE_SEP}{$item_name|escape}</h1> 

<div class="workBack">
<h1>{$title}</h1> 
{include file="inc_update.tpl" result=$result user_feedback=$user_feedback refresh=$refresh_tree} 
</div>
</body>
</html>