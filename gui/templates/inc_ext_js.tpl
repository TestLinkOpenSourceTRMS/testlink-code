{* 
Testlink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_ext_js.tpl,v 1.5 2008/12/10 19:37:46 schlundus Exp $
Purpose: include files for:
         Ext JS Library - Copyright(c) 2006-2007, Ext JS, LLC.
         licensing@extjs.com - http://www.extjs.com/license


rev :
     20071008 - franciscom - include prototype.js support
*}

{assign var="$css_only" value="$css_only|default:0"}
{assign var="ext_version" value="-2.0"}
{if $bResetEXTCss}
	<link rel="stylesheet" type="text/css" href="{$basehref}third_party/ext{$ext_version}/css/reset.css" />
{/if}
<link rel="stylesheet" type="text/css" href="{$basehref}third_party/ext{$ext_version}/css/ext-all.css" />

{if $css_only == 0}
<script type="text/javascript" src="{$basehref}third_party/ext{$ext_version}/adapter/prototype/prototype.js" language="javascript"></script>
<script type="text/javascript" src="{$basehref}third_party/ext{$ext_version}/adapter/prototype/ext-prototype-adapter.js" language="javascript"></script>
<script type="text/javascript" src="{$basehref}third_party/ext{$ext_version}/adapter/ext/ext-base.js" language="javascript"></script>
<script type="text/javascript" src="{$basehref}third_party/ext{$ext_version}/ext-all.js" language="javascript"></script>
{/if}