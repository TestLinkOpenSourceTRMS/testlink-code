{*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: tcMove.tpl,v 1.6 2008/05/07 21:01:22 schlundus Exp $
Purpose: smarty template - move/copy test case

rev:20080104 - franciscom - added radio to choose position
                            on destination (top/bottom) container.

    20060316 - franciscom - html input names updated
    20060305 - franciscom
*}
{include file="inc_head.tpl"}

{lang_get var="labels"
          s="test_case,title_mv_cp_tc,inst_move,inst_copy,inst_copy_move_warning,
             choose_container,as_first_testcase,as_last_testcase,btn_mv,btn_cp"}
<body>
<h1 class="title">{$labels.test_case}{$smarty.const.TITLE_SEP}{$name|escape}</h1>

<div class="workBack">
<h1 class="title">{$labels.title_mv_cp_tc}</h1>

<form method="post" action="lib/testcases/tcEdit.php?testcase_id={$testcase_id}">
  <p>
  {if $move_enabled }
	  {$labels.inst_move}<br />
  {/if}
  {$labels.inst_copy}<br />
  {$labels.inst_copy_move_warning}
  </p>

	<p>{$labels.choose_container}
		<select name="new_container">
			{html_options options=$array_container selected=$old_container}
		</select>

	<p><input type="radio" name="target_position"
	          value="top" {$top_checked} />{$labels.as_first_testcase}
	<br /><input type="radio" name="target_position"
	          value="bottom" {$bottom_checked} />{$labels.as_last_testcase}

		<div class="groupBtn">
		  {if $move_enabled }
			  <input id="do_move" type="submit" name="do_move" value="{$labels.btn_mv}" />
			{/if}
			<input id="do_copy" type="submit" name="do_copy" value="{$labels.btn_cp}" />
			<input type="hidden" name="old_container" value="{$old_container}" />
	</div>

</form>
</div>

</body>
</html>
