{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcNew.tpl,v 1.2 2005/08/16 17:59:13 franciscom Exp $ *}
{* Purpose: smarty template - create new testcase *}
{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_new_tc'}</h1>

{include file="inc_update.tpl" result=$sqlResult item="Test case" name=$name}

<div class="workBack">

<form method="post" action="lib/testcases/tcEdit.php?data={$data}">

	<div style="float: right;">
			<input id="submit" type="submit" name="addTC" value="{lang_get s='btn_create'}" />
	</div>	

	<p>{lang_get s='tc_title'}<br />
	<input type="text" name="title" size="50" value=""
			alt="{lang_get s='alt_add_tc_name'}"/></p>
	
	<div style="width: 95%;">
	<div>{lang_get s='summary'}<br />
	<textarea id="summary" name="summary" style="width: 99%; height: 150px;"></textarea></div>
	<div>{lang_get s='steps'}<br />
	<textarea id="scenario" name="steps" style="width: 99%; height: 200px;"></textarea></div>
	<div>{lang_get s='expected_results'}<br />
	<textarea id="exresult" name="exresult" style="width: 99%; height: 170px;"></textarea></div>
	</div>
	
</form>

{include file="inc_htmlArea.tpl"} 
<script type="text/javascript" defer="1">
   	HTMLArea.replace('exresult', config);
   	HTMLArea.replace('scenario', config);
   	HTMLArea.replace('summary', config);
</script>
	

</div>

{include file="inc_refreshTree.tpl"}

</body>
</html>