{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 


@filesource testAutomationSpec.tpl
*}

{lang_get var="labels" 
  s="user_bulk_assignment,btn_do,check_uncheck_all_checkboxes,th_id,
     btn_update_selected_tc,show_tcase_spec,
     send_mail_to_tester,platform,no_testcase_available,
     th_test_case,version"}

{include file="inc_head.tpl" openHead="yes"}
</head>

<body>
<form id='tc_exec_assignment' name='tc_exec_assignment' method='post'>
	<h1 class="title">{$gui->main_descr|escape}</h1>
	</div>

  <p>&nbsp;<p>&nbsp;<p>
  {if $gui->has_tc}
   <div class="workBack">
	  {$table_counter=0}
	  {foreach from=$gui->items item=ts key=idx name="div_drawing"}
	    {$ts_id=$ts.testsuite.id}
	    {$div_id="div_$ts_id"}
	    {if $ts_id != ''}
	      <div id="{$div_id}" style="margin-left:0px; border:1;">
        <br />
    	  {if true}
          {if $ts.testcase_qty gt 0}
	          {$table_counter=$table_counter+1}
            <table cellspacing="0" style="font-size:small;" width="100%" id="the-table-{$table_counter}" class="tableruler">
            {* -------------------------------------------------------------- *}
			      {* Heading *}
			      <thead>
			      <tr style="background-color:#059; font-weight:bold; color:white">
              <th>{$labels.th_test_case}&nbsp;{$gsmarty_gui->role_separator_open}
              	{$labels.version}{$gsmarty_gui->role_separator_close}</th>
            </tr>
			      </thead>
            {* ------------------------------------------------------- *}
            <tbody>  
             {foreach from=$ts.testcases item=tcase}
               <tr>
                 {$version = current($tcase.tcversions)}
                 <td> {$ts.testsuite.name|escape}{$tcase.name|escape}&nbsp;[{$version}]
                 </td>
               </tr>
             {/foreach}   
            </tbody>
          </table>
          {/if}
      {/if} 

      {if $gui->items_qty eq $smarty.foreach.div_drawing.iteration}
          {$next_level=0}
      {else}
          {$next_level=0}
      {/if}
    
    {/if} {* $ts_id != '' *}
    </div>
	{/foreach}
	</div>
 {/if}
  
</form>
</body>
</html>
