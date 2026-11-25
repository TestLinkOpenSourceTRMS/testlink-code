{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource inc_wunderbaum.tpl
Purpose: load the Wunderbaum tree library and the TestLink wrapper
         (gui/javascript/wbTree.js). This replaces the old ExtJS tree scripts
         (execTree.js / execTreeWithMenu.js / treebyloader.js).

         The page MUST define the global `treeCfg` object before including
         this file; wbTree.js reads it and initializes on DOM ready.
*}
{* guard key must be unique per template file: a bare __FILE__ here compiles to the
   same literal string in every template, so the first guarded include on the page
   (inc_ext_js.tpl) would swallow the flag and this template would render nothing. *}
{if guard_header_smarty('inc_wunderbaum.tpl')}
  {* Wunderbaum 0.15's default icons are Bootstrap Icons (`bi ...`), which this
     app does not ship. It ships Font Awesome 4.5 instead, so wbTree.js points
     the tree's iconMap at FA classes. Link that font here (guarded, once per
     page) or every expander/chevron and node icon would render as an empty
     slot. *}
  <link rel="stylesheet" type="text/css" href="{$basehref}gui/icons/font-awesome-4.5.0/css/font-awesome.min.css" />
  <link rel="stylesheet" type="text/css" href="{$basehref}node_modules/wunderbaum/dist/wunderbaum.css" />
  {* Optional tree theme ($tlCfg->tree_css): @import AFTER the library css so
     the theme's --wb-* overrides on #tree_div win over the defaults. The
     $use_tree_css flag is set by tlsmarty.inc.php (same convention as
     $use_custom_css in inc_head.tpl). The trailing ?v= cache-busts the theme
     file inside @import so a browser that already cached an older tree_css
     value (or a different theme) re-fetches it: bump the stamp whenever you
     change $tlCfg->tree_css in custom_config.inc.php. *} {if $use_tree_css}
  <style media="all" type="text/css">@import "{$tree_css}?v={$tree_css_v|default:1}";</style>
  {/if}
  {* Center the FA glyphs inside the fixed-width wb-icon/wb-expander slots
     (the font renders at line-height 1, which would top-align the glyph). *}
  <style type="text/css">
    div.wunderbaum span.wb-node i.wb-icon.fa,
    div.wunderbaum span.wb-node i.wb-expander.fa {
      line-height: var(--wb-icon-outer-height, 20px);
    }
  </style>
  {* ?v= cache-bust: these <script>s have no content hash, so a browser that
     already cached an older copy would otherwise keep running the old code
     (old iconMap, old tree behavior) after a deploy. Bump the version when the
     underlying file changes. *}
  <script type="text/javascript" src="{$basehref}node_modules/wunderbaum/dist/wunderbaum.umd.js?v=1"></script>
  <script type="text/javascript" src="{$basehref}gui/javascript/wbTree.js?v=2"></script>
{/if}
