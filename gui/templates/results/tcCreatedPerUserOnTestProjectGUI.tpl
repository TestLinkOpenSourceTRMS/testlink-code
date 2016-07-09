{* 
TestLink Open Source Project - http://testlink.sourceforge.net/

@filesource tcCreatedPerUserOnTestProjectGUI.tpl
@author     Bruno P. Kinoshita

Report of Test Cases created per tester

*}

{lang_get var='labels' 
          s='th_user,th_start_time,th_end_time,date,hour,submit_query,show_calender'}
{include file="inc_head.tpl"}
{include file="inc_ext_js.tpl" bResetEXTCss=1}

<body>
	<h1 class="title">{$gui->pageTitle}</h1>
    <div class="workBack">
      <form action="lib/results/tcCreatedPerUserOnTestProject.php" method="post">
        <input type="hidden" id="tproject_id" name="tproject_id" value="{$gui->tproject_id|escape}" />
        <input type="hidden" id="do_action" name="do_action" value="result" />
        <div>
          <table class="simple" style="text-align: center; margin-left: 0px;">
            <tr>
                <th width="34%">{$labels.th_user}</th>
                <th width="33%">{$labels.th_start_time}</th>
                <th width="33%">{$labels.th_end_time}</th>
            </tr>
            <tr>
            	<td align="center">
                <select name="user_id">
                  {foreach key=user item=userid from=$gui->users->items}
                    <option value="{$user}">{$userid|escape}</option>
                  {/foreach}
		  	        </select>
                </td>
                <td align="center">
                   <table border='0'>
                    <tr>
                        <td>{$labels.date}</td>
                        <td>
                            <input type="text" 
                                   name="selected_start_date" id="selected_start_date" 
                                   value="{$gui->selected_start_date|escape}" 
                                   onclick="showCal('selected_start_date-cal','selected_start_date','{$gsmarty_datepicker_format}');" 
                                   readonly="readonly" />
                            <img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
                                 onclick="showCal('selected_start_date-cal','selected_start_date','{$gsmarty_datepicker_format}');" />
                            <div id="selected_start_date-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
                        </td>
                    </tr>
                    <tr>
                        <td>{$labels.hour}</td>
                        <td align='left'>{html_select_time prefix="start_" display_minutes=false 
                                                           time=$gui->selected_start_time
                                                           display_seconds=false use_24_hours=true}
                        </td>
                    </tr>
                  </table>
                </td>
                <td align="center">
                   <table border='0'>
                       <tr>
                           <td>{$labels.date}</td>
                           <td>
                                <input type="text" 
                                       name="selected_end_date" id="selected_end_date" 
                                       value="{$gui->selected_end_date|escape}" 
                                       onclick="showCal('selected_end_date-cal','selected_end_date','{$gsmarty_datepicker_format}');" readonly />
                                <img title="{$labels.show_calender}" src="{$smarty.const.TL_THEME_IMG_DIR}/calendar.gif"
                                     onclick="showCal('selected_end_date-cal','selected_end_date','{$gsmarty_datepicker_format}');" >
                                <div id="selected_end_date-cal" style="position:absolute;width:240px;left:300px;z-index:1;"></div>
                           </td>
                       </tr>
                       <tr>
                           <td>{$labels.hour}</td>
                           <td align='left'>{html_select_time prefix="end_" display_minutes=false 
                                                          time=$gui->selected_end_time
                                                          display_seconds=false use_24_hours=true}</td>
                       </tr>
                   </table>
                </td>
            </tr>
          </table>
        </div>
        <div>
        	<input type="submit" value="{$labels.submit_query}"/>
        </div>
      </form>
    </div>
</body>
</html>
