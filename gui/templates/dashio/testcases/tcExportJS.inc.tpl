<script type="text/javascript">
var alert_box_title = "{$labels.warning|escape:'javascript'}";
var warning_empty_filename = "{$labels.warning_empty_filename|escape:'javascript'}";

function validateForm(f)
{
  if (isWhitespace(f.export_filename.value)) {
    alert_message(alert_box_title,warning_empty_filename);
    selectField(f, 'export_filename');
    return false;
  }
  return true;
}

function mirrorCheckbox(sourceOID,targetOID)
{
  var scb = document.getElementById(sourceOID);
  var tcb = document.getElementById(targetOID);

  if (scb.checked) {
    tcb.disabled = 0;
  } else {
    tcb.checked = 0;
    tcb.disabled = 1;
  }
}
</script>