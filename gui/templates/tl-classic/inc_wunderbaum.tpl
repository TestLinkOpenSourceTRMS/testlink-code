{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource inc_wunderbaum.tpl
Purpose: load the Wunderbaum tree library and the TestLink wrapper
         (gui/javascript/wbTree.js). This replaces the old ExtJS tree scripts
         (execTree.js / execTreeWithMenu.js / treebyloader.js).

         The page MUST define the global `treeCfg` object before including
         this file; wbTree.js reads it and initializes on DOM ready.
*}
{if guard_header_smarty(__FILE__)}
  <link rel="stylesheet" type="text/css" href="{$basehref}node_modules/wunderbaum/dist/wunderbaum.css" />
  <script type="text/javascript" src="{$basehref}node_modules/wunderbaum/dist/wunderbaum.umd.js"></script>
  <script type="text/javascript" src="{$basehref}gui/javascript/wbTree.js"></script>
{/if}
