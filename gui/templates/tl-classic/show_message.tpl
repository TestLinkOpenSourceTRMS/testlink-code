{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: show_message.tpl,v 1.4 2010/09/08 09:52:57 asimon83 Exp $
Purpose: 

rev :
     20100908 - asimon - BUGID 3757: tree always refreshed when deleting requirements
*}

{include file="inc_head.tpl"}

<body>
<h1 class="title">{$gui->main_descr|escape}</h1> 

<div class="workBack">
<h1 class="title">{$gui->title}</h1>
{* BUGID 3757: misspelled variable caused tree to always be refreshed when deleting requirements *}
{include file="inc_update.tpl" result=$gui->result user_feedback=$gui->user_feedback refresh=$gui->refreshTree} 
</div>
</body>
</html>