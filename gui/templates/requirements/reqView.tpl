{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: reqView.tpl,v 1.1 2007/11/19 21:01:05 franciscom Exp $
*}
{include file="inc_head.tpl" openHead="yes"}
</head>

<body>
<div class="workBack">
	<p><span class="bold">{lang_get s='title'}</span> &nbsp; {$req.title|escape}</p>
	<p class="bold">{lang_get s='scope'}</p>
	<div>{$req.scope}</div>
	<p><span class="bold">{lang_get s='status'}</span> &nbsp; {$selectReqStatus[$req.status]}</p>
	<p class="bold">{lang_get s='coverage'}
	<div>
		{section name=row loop=$req.coverage}
			<span>{$req.coverage[row].name}</span><br />
		{sectionelse}
			<span>{lang_get s='req_msg_notestcase'}</span>
		{/section}
	</div>
    </p>
  {if $cf != ''}
    {$cf}
  {/if}
	<p>{lang_get s="Author"}: {$req.author} [{localize_date d=$req.creation_ts}]</p>
	{if $req.modifier <> ''}
	<p>{lang_get s="last_edit"}: {$req.modifier} [{localize_date d=$req.modification_ts}]</p>
	{/if}



  {* ----------------------------------------------------------------------------------------- *}
  <div class="groupBtn">
    <form id="req" name="req" action="{$smarty.const.REQ_MODULE}reqEdit.php" method="post">
    	<input type="hidden" name="requirement_id" value="{$req_id}" />
    	<input type="hidden" name="do_action" value="" />
    	
    	{if $modify_req_rights == "yes"}
    	<input type="submit" name="edit_req" 
    	       value="{lang_get s='btn_edit'}" 
    	       onclick="do_action.value='edit'"/>
    	
    	
    	<input type="button" name="delete_req" value="{lang_get s='btn_delete'}"
    	       onclick="delete_confirmation({$req_spec.id},
 					                                 '{$req_spec.title|escape:'javascript'}',
 					                                 '{$warning_msg}');"	/>
    	{/if}
    </form>
  </div>
</div>
</body>