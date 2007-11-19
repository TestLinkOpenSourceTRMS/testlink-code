{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: reqSpecListTree.tpl,v 1.1 2007/11/19 21:01:05 franciscom Exp $ 
show requirement specifications tree menu
*}
{include file="inc_head.tpl" jsTree="yes" openHead="yes"}
<script type="text/javascript" language="javascript">
var req_spec_manager_url = '{$req_spec_manager_url}';
var req_manager_url = '{$req_manager_url}';
</script>
</head>

<body>

<h1>{$treeHeader}</h1>
<div style="margin: 3px;">
  <form>
  	<table class="smallGrey" width="100%">
  		<tr>
  			<td>&nbsp;</td>
  	    <td><input type="button" value="{lang_get s='button_update_tree'}" style="font-size: 90%;"
  	               onClick="javascript: parent.treeframe.location.reload();" />
  	    </td>   
  	  </tr>  
    </table>
  </form>
</div>

<div class="tree" id="tree">
{if $tree eq ''}
  {lang_get s='no_tc_spec_av'}
{/if}
{$tree}
<br />
</div>
</body>
</html>