{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_reqView.tpl,v 1.1 2005/09/05 11:38:46 havlat Exp $ *}
{* Purpose: smarty include template - show requirement *}
{* Author: Martin Havlat *}
{* Revisions: None *}

	<p><span class="bold">{lang_get s='title'}</span> &nbsp; {$arrReq.title|escape}</p>
	<p class="bold">{lang_get s='scope'}</p>
	<div>{$arrReq.scope}</div>
	<p><span class="bold">{lang_get s='status'}</span> &nbsp; {$selectReqStatus[$arrReq.status]}</p>
	<p class="bold">{lang_get s='coverage'}</p>
	<div>
		{section name=row loop=$arrReq.coverage}
			<span>{$arrReq.coverage[row].title}</span><br />
		{sectionelse}
			<span>{lang_get s='req_msg_notestcase'}</span>
		{/section}
	</div>
	<p>{lang_get s="Author"}: {$arrReq.author} [{localize_date d=$arrReq.create_date}]</p>
	{if $arrReq.id_modifier <> ''}
	<p>{lang_get s="last_edit"}: {$arrReq.modifier} [{localize_date d=$arrReq.modified_date}]</p>
	{/if}

{*** END ***}