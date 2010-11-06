{* 
Testlink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_ext_js.tpl,v 1.16 2010/11/06 14:12:52 mx-julian Exp $
Purpose: include files for:
         Ext JS Library - Copyright(c) 2006-2007, Ext JS, LLC.
         licensing@extjs.com - http://www.extjs.com/license


rev :
     20101106 - Julian - BUGID 3979 - Use grid filters for exttables
     20101022 - Julian - BUGID 3924 - Localization of ExtJS Components
     20100927 - franciscom - added new ext-js extension TableGrid.js
     20100621 - eloff - BUGID 3523 - refactor to remove smarty deprecated {php}
                                     use guard_header_smarty() instead
     20100620 - franciscom - reset.css has changed on new extjs distribution to reset-min.css
     20100614 - eloff - BUGID 3523 - prevent loading ext-js more than once
     20090730 - francisco.mancardi@gruppotesi.com
     refactored to use ext-js 3.0
     
     20071008 - franciscom - include prototype.js support
*}

{* BUGID 3924 - set ext locale according to chosen locale of user.
   default locale is "en"
   last change: 20101022
*}

{assign var="ext_lang" value="en"}
{if $smarty.session.locale == "cs_CZ"}
  {assign var="ext_lang" value="cs"}
{elseif $smarty.session.locale == "de_DE"}
  {assign var="ext_lang" value="de"}
{elseif $smarty.session.locale == "en_GB"}
  {assign var="ext_lang" value="en_GB"}
{elseif $smarty.session.locale == "en_US"}
  {assign var="ext_lang" value="en"}
{elseif $smarty.session.locale == "es_AR"}
  {assign var="ext_lang" value="es"}
{elseif $smarty.session.locale == "es_ES"}
  {assign var="ext_lang" value="es"}
{elseif $smarty.session.locale == "fi_FI"}
  {assign var="ext_lang" value="fi"}
{elseif $smarty.session.locale == "fr_FR"}
  {assign var="ext_lang" value="fr"}
{elseif $smarty.session.locale == "id_ID"}
  {assign var="ext_lang" value="id"}
{elseif $smarty.session.locale == "it_IT"}
  {assign var="ext_lang" value="it"}
{elseif $smarty.session.locale == "ja_JP"}
  {assign var="ext_lang" value="ja"}
{elseif $smarty.session.locale == "ko_KR"}
  {assign var="ext_lang" value="ko"}
{elseif $smarty.session.locale == "nl_NL"}
  {assign var="ext_lang" value="nl"}
{elseif $smarty.session.locale == "pl_PL"}
  {assign var="ext_lang" value="pl"}
{elseif $smarty.session.locale == "pt_BR"}
  {assign var="ext_lang" value="pt_BR"}
{elseif $smarty.session.locale == "ru_RU"}
  {assign var="ext_lang" value="ru"}
{elseif $smarty.session.locale == "zh_CN"}
  {assign var="ext_lang" value="zh_CN"}
{/if}


{if guard_header_smarty(__FILE__)}

  {assign var="$css_only" value="$css_only|default:0"}
  {assign var="ext_location" value=$smarty.const.TL_EXTJS_RELATIVE_PATH}
  {if isset($bResetEXTCss) && $bResetEXTCss}
  	<link rel="stylesheet" type="text/css" href="{$basehref}{$ext_location}/css/reset-min.css" />
  {/if}
  <link rel="stylesheet" type="text/css" href="{$basehref}{$ext_location}/css/ext-all.css" />
  <link rel="stylesheet" type="text/css" href="{$basehref}{$ext_location}/ux/gridfilters/css/GridFilters.css" />
  <link rel="stylesheet" type="text/css" href="{$basehref}{$ext_location}/ux/gridfilters/css/RangeMenu.css" />
  
  {if $css_only == 0}
      {*
      not useful
      <script type="text/javascript" src="{$basehref}{$ext_location}/adapter/prototype/prototype.js" language="javascript"></script>
      <script type="text/javascript" src="{$basehref}{$ext_location}/adapter/prototype/ext-prototype-adapter.js" language="javascript"></script>
      *}
      <script type="text/javascript" src="{$basehref}{$ext_location}/adapter/ext/ext-base.js" language="javascript"></script>
      <script type="text/javascript" src="{$basehref}{$ext_location}/ext-all.js" language="javascript"></script>
      
      <script type="text/javascript" src="{$basehref}{$ext_location}/ux/Reorderer.js" language="javascript"></script>
      <script type="text/javascript" src="{$basehref}{$ext_location}/ux/ToolbarReorderer.js" language="javascript"></script>
      <script type="text/javascript" src="{$basehref}{$ext_location}/ux/ToolbarDroppable.js" language="javascript"></script>
      <script type="text/javascript" src="{$basehref}{$ext_location}/ux/Exporter-all.js" language="javascript"></script>
      
      {* Grid Filters *}
      <script type="text/javascript" src="{$basehref}{$ext_location}/ux/gridfilters/menu/RangeMenu.js" language="javascript"></script>
      <script type="text/javascript" src="{$basehref}{$ext_location}/ux/gridfilters/menu/ListMenu.js" language="javascript"></script>
      
      <script type="text/javascript" src="{$basehref}{$ext_location}/ux/gridfilters/GridFilters.js" language="javascript"></script>
      <script type="text/javascript" src="{$basehref}{$ext_location}/ux/gridfilters/filter/Filter.js" language="javascript"></script>
      <script type="text/javascript" src="{$basehref}{$ext_location}/ux/gridfilters/filter/StringFilter.js" language="javascript"></script>
      <script type="text/javascript" src="{$basehref}{$ext_location}/ux/gridfilters/filter/DateFilter.js" language="javascript"></script>
      <script type="text/javascript" src="{$basehref}{$ext_location}/ux/gridfilters/filter/ListFilter.js" language="javascript"></script>
      <script type="text/javascript" src="{$basehref}{$ext_location}/ux/gridfilters/filter/NumericFilter.js" language="javascript"></script>
      <script type="text/javascript" src="{$basehref}{$ext_location}/ux/gridfilters/filter/BooleanFilter.js" language="javascript"></script>
      
      
      {* BUGID 3924 - Localization of ExtJS Components *}
      <script type="text/javascript" src="{$basehref}{$ext_location}/src/locale/ext-lang-{$ext_lang}.js" language="javascript"></script>
  
      {* 20100927 - franciscom - convert HTML table in ext-js grid *}
      <script type="text/javascript" src="{$basehref}{$ext_location}/ux/TableGrid.js" language="javascript"></script>
  {/if}

{/if}