{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource  keywordsImport.tpl

Purpose: smarty template - keyword import initial page 
*}

{lang_get var='lbl'
          s='title_keyword_import,file_type,btn_upload_file,
             view_file_format_doc,keywords_file,btn_cancel'}

{include file="inc_head.tpl"}

<body>
<h1 class="title">{$gui->main_descr|escape}</h1>

<div class="workBack">
<h1 class="title">{$lbl.title_keyword_import}</h1>

<form method="post" enctype="multipart/form-data" action="{$SCRIPT_NAME}">
  <table>
  <tr>
    <td>{$lbl.file_type}</td>
    <td>
      <select name="importType">
        {html_options options=$gui->importTypes selected=$gui->import_type_selected}
      </select>
        <a href={$basehref}{$smarty.const.PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT}>{$lbl.view_file_format_doc}</a>

    </td>
  </tr>
  <tr>
    <td>
      {$lbl.keywords_file}
    </td>
    <td>
      <input type="hidden" name="MAX_FILE_SIZE" value="{$gui->importLimit}" /> {* restrict file size *}
      <input type="file" name="uploadedFile" size="30" />
    </td>
  </tr>
  <tr>
    <td colspan="2">
      {$gui->fileSizeLimitMsg}
    </td>
  </tr>
  </table>
  <br/>
  <div class="groupBtn">
    <input type="hidden" name="tproject_id" value="{$gui->tproject_id}" />
    <input type="submit" name="UploadFile" value="{$lbl.btn_upload_file}" />
    <input type="button" name="cancel" value="{$lbl.btn_cancel}" 
      onclick="javascript: location.href=fRoot+'{$gui->viewUrl}';" />
  </div>
</form>

{if $gui->msg != ''}
  <script type="text/javascript">
  alert("{$gui->msg}");
  </script>
{/if}  
</div>

</body>
</html>