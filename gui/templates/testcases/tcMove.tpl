{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@tcMove.tpl
Purpose: smarty template - move/copy test case

*}
{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}
{lang_get var="labels"
          s="test_case,title_mv_cp_tc,inst_move,inst_copy,inst_copy_move_warning,
             copy_requirement_assignments,copy_keyword_assignments,btn_copy_ghost_zone,
             choose_container,as_first_testcase,as_last_testcase,btn_mv,btn_cp,copy_new_name"}

{include file="inc_head.tpl"}

<body>
<h1 class="title">{$labels.test_case}{$smarty.const.TITLE_SEP}{$gui->name|escape}</h1>

<div class="workBack">
<h1 class="title">{$labels.title_mv_cp_tc}</h1>
<form method="post" action="{$basehref}lib/testcases/tcEdit.php?testcase_id={$gui->testcase_id}">
  <input type="hidden" name="testcase_id" id="testcase_id" value="{$gui->testcase_id}">

  <p>
  {if $gui->move_enabled}
	  {$labels.inst_move}<br />
  {/if}
  {$labels.inst_copy}<br />
  {$labels.inst_copy_move_warning}
  </p>

	<p>{$labels.choose_container}
		<select name="new_container">
			{html_options options=$gui->array_container selected=$gui->old_container}
		</select>
  </p>
  <p>
   <label for="new_name">{$labels.copy_new_name}</label>
   <input type="text" name="testcase_name" id='testcase_name'
          size="{#TESTCASE_NAME_SIZE#}" maxlength="{#TESTCASE_NAME_MAXLEN#}"
          value ="{$gui->testcase_name}" >
  <br />
  
  <p>
   <input type="checkbox" name="keyword_assignments" id='keyword_assignments'>
     {$labels.copy_keyword_assignments}
  <br />
  <input type="checkbox" name="requirement_assignments" id='requirement_assignments'>
     {$labels.copy_requirement_assignments}
  </p>

	 
	<p><input type="radio" name="target_position"
	          value="top" {$gui->top_checked} />{$labels.as_first_testcase}
	<br /><input type="radio" name="target_position"
	          value="bottom" {$gui->bottom_checked} />{$labels.as_last_testcase}

		<div class="groupBtn">
		  {if $gui->move_enabled}
			  <input id="do_move" type="submit" name="do_move" value="{$labels.btn_mv}" />
			{/if}
			<input id="do_copy" type="submit" name="do_copy" value="{$labels.btn_cp}" />
      <input id="do_copy_ghost_zone" type="submit" name="do_copy_ghost_zone" value="{$labels.btn_copy_ghost_zone}" />
			<input type="hidden" name="old_container" value="{$gui->old_container}" />
	</div>

</form>
</div>

</body>
</html>