{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: tcTree.tpl,v 1.6 2007/02/19 07:30:20 franciscom Exp $ *}
{* Purpose: smarty template - show test specification tree menu *}
{*
	20070217 - franciscom - added test suite filter
                          changes to form method to allow automatic refresh
                          without browser warning
*}
{include file="inc_head.tpl" jsTree="yes"}

<body>

<h1>{$treeHeader}</h1>


<div style="margin: 3px;">

{if $draw_filter}
  <form method="get" onchange="document.tree.style.display = 'hidden';">
    <input type="hidden" name="feature" value={$smarty.get.feature}>
  	<table class="smallGrey" width="100%">
    		<caption>
    			{lang_get s='caption_nav_filter_settings'}
    		</caption>
    		<tr>
    			<td>{lang_get s='testsuite'}</td>
    			<td>
    			{html_options name="tsuites_to_show" options=$tsuites_combo selected=$tsuite_choice}
    			</td>
    		</tr>

  		<tr>
  			<td>&nbsp;</td>
  			<td><input type="submit" name="refresh_view" 
  			           value="{lang_get s='button_update_tree'}" style="font-size: 90%;" /></td>
  		</tr>
  	</table>
  </form>

{else}
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
{/if}		
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