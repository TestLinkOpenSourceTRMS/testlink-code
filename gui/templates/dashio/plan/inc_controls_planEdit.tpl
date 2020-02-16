{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource inc_controls_planEdit.tpl
*}
{lang_get var="labels"
          s='testplan_copy_builds,testplan_copy_tcases,testplan_copy_tcases_latest,
             testplan_copy_tcases_current,testplan_copy_builds,
             testplan_copy_priorities,testplan_copy_milestones,
             testplan_copy_assigned_to,testplan_copy_user_roles,
             testplan_copy_platforms_links,testplan_copy_attachments'}

<script type="text/javascript">
function manageTestCaseRelated(checkBoxOid)
{
  var obj = document.getElementById(checkBoxOid);
  var target = ['version','priority','exec_assignment'];
  var dmode;

  // Display
  dmode = 'none';
  if(obj.checked)
  {
    dmode = '';
  }  

  var loop2do = target.length;
  for (var idx = 0; idx < loop2do; idx++) 
  {
    document.getElementById(target[idx]).style.display=dmode;
  }

  // Enable / disable + value set
  plink = document.getElementById('copy_platforms_links');
  if(obj.checked)
  {
    plink.checked = true;
    plink.disabled = true;
  }  
  else
  {
    plink.disabled = false;
  } 
} 

function manageBuildRelated(checkBoxOid)
{
  var obj = document.getElementById(checkBoxOid);
  var target = ['exec_assignment'];
  var dmode;

  dmode = 'none';
  if(obj.checked)
  {
    dmode = '';
  }  

  var loop2do = target.length;
  for (var idx = 0; idx < loop2do; idx++) 
  {
    document.getElementById(target[idx]).style.display=dmode;
  }
} 
</script>

<table style="float: left; text-align:left">
  <tr>
    <td align='left'>
      <input type="checkbox" name="copy_user_roles" checked="checked"/>{$labels.testplan_copy_user_roles}
    </td>
  </tr>
  <tr>
    <td align='left'>
      <input type="checkbox" name="copy_attachments" checked="checked"/>{$labels.testplan_copy_attachments}
    </td>
  </tr>

  <tr>
    <td align='left'>
      <input type="checkbox" name="copy_tcases" id="copy_tcases" 
             checked="checked" onclick="manageTestCaseRelated('copy_tcases');" />
      {$labels.testplan_copy_tcases}
    </td>
  </tr>
  <tr id="version">
    <td align='left'>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <input type="radio" name="tcversion_type" value="latest" />{$labels.testplan_copy_tcases_latest}
      <input type="radio" name="tcversion_type" value="current" checked="1"/>{$labels.testplan_copy_tcases_current}
    </td>
  </tr>
  <tr id="priority">
    <td align='left'>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <input type="checkbox" name="copy_priorities" checked="checked"/>{$labels.testplan_copy_priorities}
    </td>
  </tr>
  <tr>
    <td align='left'>
      <input type="checkbox" name="copy_builds" id="copy_builds" 
             checked="checked" onclick="manageBuildRelated('copy_builds');"/>
      {$labels.testplan_copy_builds}
    </td>
  </tr>
  <tr id="exec_assignment">
    <td align='left'>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <input type="checkbox" name="copy_assigned_to" checked="checked"/>{$labels.testplan_copy_assigned_to}
    </td>
  </tr>

  <tr id="platforms_links">
    <td align='left'>
      <input type="checkbox" name="copy_platforms_links" 
             id="copy_platforms_links" disabled="disabled" 
             checked="checked"/>{$labels.testplan_copy_platforms_links}
    </td>
  </tr>

  <tr>
    <td align='left'>
      <input type="checkbox" name="copy_milestones" checked="checked"/>{$labels.testplan_copy_milestones}
    </td>
  </tr>

  
  {* always copy platform links *}
  <!--
  <input type="hidden" name="copy_platforms_links" value="1"/>
  -->
</table>