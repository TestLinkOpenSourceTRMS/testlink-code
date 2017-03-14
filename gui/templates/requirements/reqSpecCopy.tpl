{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqSpecCopy.tpl,v 1.4 2010/10/06 10:26:22 asimon83 Exp $
@author: francisco mancardi
Purpose: copy req specification

rev :
20101006 - asimon - BUGID 3854
*}

{include file="inc_head.tpl"}
{lang_get s='container' var='parent'}
{lang_get var="labels"
          s="cont_move_first,sorry_further,title_move_cp,cont_copy_first,defined_exclam,
             cont_move_second,cont_copy_second,choose_target,
             btn_move,btn_cp,destination_top,destination_bottom"}

<body>
<h1 class="title">{$gui->main_descr}</h1>

<div class="workBack">
<h1 class="title">{$gui->action_descr}</h1>

{if $gui->containers eq ''}
	{$labels.sorry_further} {$parent} {$labels.defined_exclam}
{else}

{if $gui->array_of_msg != ''}
  <br />
  {include file="inc_msg_from_array.tpl" array_of_msg=$gui->array_of_msg arg_css_class="messages"}
  <br />
{/if}

	<form method="post" action="{$basehref}lib/requirements/reqSpecEdit.php?req_spec_id={$gui->req_spec_id}">
		<p>{$labels.choose_target} {$parent|escape}:
			<select name="containerID">
				{html_options options=$gui->containers}
			</select>
		</p>

	<p><input type="radio" name="target_position"
	          value="top" {$gui->top_checked} />{$labels.destination_top}
  	<br /><input type="radio" name="target_position"
	          value="bottom" {$gui->bottom_checked} />{$labels.destination_bottom}

		<div>
			<input type="submit" name="doActionButton" value="{$labels.btn_cp}" />
			<input type="hidden" name="doAction" value="doCopy" />
		</div>

	</form>
{/if}

{* BUGID 3854 *}
{if isset($gui->refreshTree) && $gui->refreshTree}
   {include file="inc_refreshTreeWithFilters.tpl"}
{/if}

</div>
</body>
</html>