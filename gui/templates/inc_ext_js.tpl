{* 
Testlink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_ext_js.tpl,v 1.7 2009/07/30 17:22:25 franciscom Exp $
Purpose: include files for:
         Ext JS Library - Copyright(c) 2006-2007, Ext JS, LLC.
         licensing@extjs.com - http://www.extjs.com/license


rev :
     20090730 - francisco.mancardi@gruppotesi.com
     refactored to use ext-js 3.0
     
     20071008 - franciscom - include prototype.js support
*}

{assign var="$css_only" value="$css_only|default:0"}
{assign var="ext_location" value=$smarty.const.TL_EXTJS_RELATIVE_PATH}
{if isset($bResetEXTCss) && $bResetEXTCss}
	<link rel="stylesheet" type="text/css" href="{$basehref}{$ext_location}/css/reset.css" />
{/if}
<link rel="stylesheet" type="text/css" href="{$basehref}{$ext_location}/css/ext-all.css" />

{if $css_only == 0}
{*
not useful
<script type="text/javascript" src="{$basehref}{$ext_location}/adapter/prototype/prototype.js" language="javascript"></script>
<script type="text/javascript" src="{$basehref}{$ext_location}/adapter/prototype/ext-prototype-adapter.js" language="javascript"></script>
*}
<script type="text/javascript" src="{$basehref}{$ext_location}/adapter/ext/ext-base.js" language="javascript"></script>
<script type="text/javascript" src="{$basehref}{$ext_location}/ext-all.js" language="javascript"></script>
{/if}