{lang_get var='labels' 
          s='assign_table_header_fieldvals,assign_table_header_users,btn_cancel,btn_ok'}

<!DOCTYPE html>
{$selectSize = 10}
{if count($gui->users) lt 10}
{$selectSize = count($gui->users)}
{/if}
<html>
  <head>
    <link rel="stylesheet" href="../../gui/themes/default/css/testlink.css"/>
  </head>
  <body>
    <div class="workBack">
      <form id="assignmentForm" method="post" action="notificationAssignmentConfig.php" >
        <input hidden type="text" name="fieldName" value="{$gui->fieldName}"/>
        <table class="simple_tableruler">
          <th>{$labels.assign_table_header_fieldvals}</th><th>{$labels.assign_table_header_users}</th>
          {foreach key=fieldValNr item=fieldVal from=$gui->fieldVals}
            <tr>
              <td>{$fieldVal}</td>
              <td>
                <select name="select_{$fieldVal}[]" multiple="multiple" size="{$selectSize}">
                  {$addSelectedUser = false}
                  {foreach key=userIndex item=userName from=$gui->users}
                    {foreach item=activeUser from=$gui->fieldAssignments[$gui->fieldName][$fieldVal]}
                      {if strcmp($activeUser,$userName) === 0}
                        {$addSelectedUser = true}
                        {break}
                      {/if}
                    {/foreach}
                    {if $addSelectedUser}
                      <option	selected="selected" id={$userIndex}>{$userName}</option>
                      {$addSelectedUser = false}
                    {else}
                      <option	id={$userIndex}>{$userName}</option>
                    {/if}
                  {/foreach}
                </select>
              </td>
            </tr>
          {/foreach}
        </table>
        <input id="assignmentFormSubmit" name="submit" type="submit" value="{$labels.btn_ok}" />
        <input id="assignmentFormCancel" name="cancel" type="submit" value="{$labels.btn_cancel}" />
      </form>
    </div>
  </body>
</html>