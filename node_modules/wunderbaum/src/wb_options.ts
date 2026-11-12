/*!
 * Wunderbaum - options
 * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
 * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
 */

import {
  ColumnDefinitionList,
  DndOptionsType,
  DynamicBoolOption,
  DynamicBoolOrStringOption,
  DynamicCheckboxOption,
  DynamicIconOption,
  EditOptionsType,
  FilterOptionsType,
  IconMapType,
  // GridOptionsType,
  // KeynavOptionsType,
  // LoggerOptionsType,
  NavModeEnum,
  NodeTypeDefinitionMap,
  SelectModeType,
  SourceType,
  TranslationsType,
  WbActivateEventType,
  WbButtonClickEventType,
  WbCancelableEventResultType,
  WbChangeEventType,
  WbClickEventType,
  WbDeactivateEventType,
  WbErrorEventType,
  WbExpandEventType,
  WbIconBadgeCallback,
  WbIconBadgeEventResultType,
  WbInitEventType,
  WbKeydownEventType,
  WbNodeEventType,
  WbReceiveEventType,
  WbRenderEventType,
  WbSelectEventType,
  WbTreeEventType,
} from "./types";

/**
 * Properties of {@link wunderbaum.Wunderbaum.options}.
 *
 * This is similar, but not identical, to the options that can be passed to the
 * constructor(@see {@link InitWunderbaumOptions}).
 */
export interface WunderbaumOptions {
  /**
   * If true, add a `wb-skeleton` class to all nodes, that will result in a
   * 'glow' effect. Typically used with initial dummy nodes, while loading the
   * real data.
   * @default false.
   */
  skeleton: boolean;
  /**
   * Translation map for some system messages.
   */
  strings: TranslationsType;
  /**
   * 0:quiet, 1:errors, 2:warnings, 3:info, 4:verbose
   * @default 3 (4 in local debug environment)
   */
  debugLevel: number;
  /**
   * Number of levels that are forced to be expanded, and have no expander icon.
   * E.g. 1 would keep all toplevel nodes expanded.
   * @default 0
   */
  minExpandLevel: number;
  /**
   * If true, allow to expand parent nodes, even if `node.children` conatains
   * an empty array (`[]`). This is the the behavior of macOS Finder, for example.
   * @default false
   */
  emptyChildListExpandable: boolean;
  // escapeTitles: boolean;
  // /**
  //  * Height of the header row div.
  //  * @default 22
  //  */
  // headerHeightPx: number;
  /**
   * Height of a node row div.
   * @default 22
   */
  rowHeightPx: number;
  /**
   * Icon font definition. May be a string (e.g. "fontawesome6" or "bootstrap")
   * or a map of `iconName: iconClass` pairs.
   * Note: the icon font must be loaded separately.
   * In order to only override some defauöt icons, use this pattern:
   * ```js
   * const tree = new mar10.Wunderbaum({
   *   ...
   *   iconMap: Object.assign(Wunderbaum.iconMaps.bootstrap, {
   *     folder: "bi bi-archive",
   *   }),
   * });
   * ```

   * @default "bootstrap"
   */
  iconMap: string | IconMapType;
  /**
   * Collapse siblings when a node is expanded.
   * @default false
   */
  autoCollapse: boolean;
  /**
   * If true, the tree will automatically adjust its height to fit the parent
   * container. This is useful when the tree is embedded in a container with
   * a fixed or calculated sized.
   * If the parent container is unsized (e.g. grows with its content, `height: auto`),
   * then we can define a `height: ...` or `max_height: ...` style on the parent.
   * To avoid a recursive resize-loop, it may be helpful to set `overflow: hidden`
   * on the parent container.
   *
   * Set this option to `false` will disable auto-resizing.
   *
   * @default: true
   */
  adjustHeight: boolean;
  /**
   * HTMLElement or selector that receives the top nodes breadcrumb.
   * @default undefined
   */
  connectTopBreadcrumb: HTMLElement | string | null;
  /**
   * @default  NavModeEnum.startRow
   */
  navigationModeOption: NavModeEnum;
  /**
   * Show/hide header (default: null)
   * null: assume false for plain tree and true for grids.
   * string: use text as header (only for plain trees)
   * true: display a header (use tree's id as text for plain trees)
   * false: do not display a header
   */
  header: boolean | string | null;
  /**
   * Show a `<progress>` element while loading data.
   * @default false.
   */
  showSpinner: boolean;
  /**
   * Generate missing keys by hashing a combination of refKey (or title) and
   * the parent key. This is useful when the source data does not contain unique
   * keys but we want stable keys for persisting the active node, selection or
   * expansion state. Note that this still assumes that the same refKey must not
   * appear twice in the same parent node.
   * @default false.
   */
  autoKeys: boolean;
  /**
   * If true, render a checkbox before the node tile to allow selection with the
   * mouse. Pass `"radio"` to render a radio button instead.
   * @default false.
   */
  checkbox: DynamicCheckboxOption;
  /** Optional callback to render icons per node. */
  icon?: DynamicIconOption;
  /** Optional callback to render a tooltip for the icon. */
  iconTooltip?: DynamicBoolOrStringOption;
  /** Optional callback to render a tooltip for the node title.
   * Pass `true` to use the node's `title` property as tooltip.
   */
  tooltip?: DynamicBoolOrStringOption;
  /** Optional callback to make a node unselectable. */
  unselectable?: DynamicBoolOption;
  // /**
  //  * @default 200
  //  */
  // updateThrottleWait?: number;
  /**
   * @default true
   */
  enabled: boolean;
  /**
   *
   * @default false
   */
  fixedCol: boolean;
  /**
   * Default value for ColumnDefinition.filterable option.
   * @default false
   * @since 0.11.0
   */
  columnsFilterable: boolean;
  /**
   * Default value for ColumnDefinition.menu option.
   * @default false
   * @since 0.11.0
   */
  columnsMenu: boolean;
  /**
   * Default value for ColumnDefinition.resizable option.
   * @default false
   * @since 0.10.0
   */
  columnsResizable?: boolean;
  /**
   * Default value for ColumnDefinition.sortable option.
   * @default false
   * @since 0.11.0
   */
  columnsSortable?: boolean;
  /**
   * Group nodes with children or of `type: 'folder'` at the top when sorting.
   * If a function is passed, it is called with the node as argument to determine
   * whether the node is a folder or not. The function should return `true` for
   * folders.
   * and should return `true` for folders.
   * @default false
   * @since 0.14.0
   */
  sortFoldersFirst?: DynamicBoolOption;

  // --- Selection ---
  /**
   * @default "multi"
   */
  selectMode: SelectModeType;

  // --- KeyNav ---
  /**
   * @default true
   */
  quicksearch: boolean;

  /**
   * Scroll Node into view on Expand Click
   * @default true
   */
  scrollIntoViewOnExpandClick: boolean;

  // --- Extensions ------------------------------------------------------------

  /** Configuration options for the drag-and-drop extension. */
  dnd: DndOptionsType;
  /** Configuration options for the edit-title extension. */
  edit: EditOptionsType;
  /** Configuration options for the node-filter extension. */
  filter: FilterOptionsType;
  // grid?: GridOptionsType;
  // keynav?: KeynavOptionsType;
  // logger?: LoggerOptionsType;

  // --- Events ----------------------------------------------------------------

  /**
   * `e.node` was activated.
   * @category Callback
   */
  activate?: (e: WbActivateEventType) => void;
  /**
   * `e.node` is about to be activated.
   * Return `false` to prevent default handling, i.e. activating the node.
   * See also `deactivate` event.
   * @category Callback
   */
  beforeActivate?: (e: WbActivateEventType) => WbCancelableEventResultType;
  /**
   * `e.node` is about to be expanded/collapsed.
   * Return `false` to prevent default handling, i.e. expanding/collapsing the node.
   * @category Callback
   */
  beforeExpand?: (e: WbExpandEventType) => WbCancelableEventResultType;
  /**
   * Return `false` to prevent default handling, i.e. (de)selecting the node.
   * @category Callback
   */
  beforeSelect?: (e: WbSelectEventType) => WbCancelableEventResultType;
  /**
   * Return `false` to prevent default handling, i.e. (de)selecting the node.
   * @category Callback
   */
  buttonClick?: (e: WbButtonClickEventType) => void;
  /**
   *
   * @category Callback
   */
  change?: (e: WbChangeEventType) => void;
  /**
   * A 'paging' status node (see {@link WunderbaumNode.addPagingNode}) was
   * clicked, or activated with Enter or Space. `e.node` is the paging node;
   * `e.node.parent` is the node whose next page should be loaded.
   * @category Callback
   */
  clickPaging?: (e: WbNodeEventType) => void;
  /**
   *
   * Return `false` to prevent default behavior, e.g. expand/collapse, (de)selection, or activation.
   * @category Callback
   */
  click?: (e: WbClickEventType) => WbCancelableEventResultType;
  /**
   * Return `false` to prevent default behavior, e.g. expand/collapse.
   * @category Callback
   */
  dblclick?: (e: WbClickEventType) => WbCancelableEventResultType;
  /**
   * `e.node` was deactivated.
   *
   * Return `false` to prevent default handling, e.g. deactivating the node
   * and activating the next.
   * See also `activate` event.
   * @category Callback
   */
  deactivate?: (e: WbDeactivateEventType) => WbCancelableEventResultType;
  /**
   * `e.node` was discarded from the viewport and its HTML markup removed.
   * @category Callback
   */
  discard?: (e: WbNodeEventType) => void;
  /**
   * `e.node` is about to be rendered. We can add a badge to the icon cell here.
   * @category Callback
   */
  iconBadge?: (e: WbIconBadgeCallback) => WbIconBadgeEventResultType;
  /**
   * An error occurred, e.g. during initialization or lazy loading.
   * @category Callback
   */
  error?: (e: WbErrorEventType) => void;
  /**
   * `e.node` was expanded (`e.flag === true`) or collapsed (`e.flag === false`)
   * @category Callback
   */
  expand?: (e: WbTreeEventType) => void;
  /**
   * The tree received or lost focus.
   * Check `e.flag` for status.
   * @category Callback
   */
  focus?: (e: WbTreeEventType) => void;
  /**
   * Fires when the tree markup was created and the initial source data was loaded.
   * Typical use cases would be activating a node, setting focus, enabling other
   * controls on the page, etc.<br>
   *  Also sent if an error occured during initialization (check `e.error` for status).
   * @category Callback
   */
  init?: (e: WbInitEventType) => void;
  /**
   * Fires when a key was pressed while the tree has focus.
   * `e.node` is set if a node is currently active.
   * Return `false` to prevent default navigation.
   * @category Callback
   */
  keydown?: (e: WbKeydownEventType) => WbCancelableEventResultType;
  /**
   * Fires when a node that was marked 'lazy', is expanded for the first time.
   * Typically we return an endpoint URL or the Promise of a fetch request that
   * provides a (potentially nested) list of child nodes.
   * @category Callback
   */
  lazyLoad?: (e: WbNodeEventType) => void;
  /**
   * Fires when data was loaded (initial request, reload, or lazy loading),
   * after the data is applied and rendered.
   * @category Callback
   */
  load?: (e: WbNodeEventType) => void;
  /**
   * @category Callback
   */
  modifyChild?: (e: WbNodeEventType) => void;
  /**
   * Fires when data was fetched (initial request, reload, or lazy loading),
   * but before the data is applied and rendered.
   * Here we can modify and adjust the received data, for example to convert an
   * external response to native Wunderbaum syntax.
   * @category Callback
   */
  receive?: (e: WbReceiveEventType) => void;
  /**
   * Fires when a node is about to be displayed.
   * The default HTML markup is already created, but not yet added to the DOM.
   * Now we can tweak the markup, create HTML elements in this node's column
   * cells, etc.
   * See also `Custom Rendering` for details.
   * @category Callback
   */
  render?: (e: WbRenderEventType) => void;
  /**
   * Same as `render(e)`, but for the status nodes, i.e. `e.node.statusNodeType`.
   * @category Callback
   */
  renderStatusNode?: (e: WbRenderEventType) => void;
  /**
   *`e.node` was selected (`e.flag === true`) or deselected (`e.flag === false`)
   * @category Callback
   */
  select?: (e: WbNodeEventType) => void;
  /**
   * Fires when the viewport content was updated, after scroling, expanding etc.
   * @category Callback
   */
  update?: (e: WbTreeEventType) => void;
}

/**
 * Available options for {@link wunderbaum.Wunderbaum}.
 *
 * Options are passed to the constructor as plain object:
 *
 * ```js
 * const tree = new mar10.Wunderbaum({
 *   id: "demo",
 *   element: document.getElementById("demo-tree"),
 *   source: "url/of/data/request",
 *   ...
 * });
 * ```
 *
 * Event handlers are also passed as callbacks
 *
 * ```js
 * const tree = new mar10.Wunderbaum({
 *   ...
 *   init: (e) => {
 *     console.log(`Tree ${e.tree} was initialized and loaded.`)
 *   },
 *   activate: (e) => {
 *     console.log(`Node ${e.node} was activated.`)
 *   },
 *   ...
 * });
 * ```
 *
 * Most of the properties are optional and have resonable default.
 * They are then available as {@link Wunderbaum.options} property and can be
 * changed at runtime. <br>
 * Only the `element` option is mandatory.
 *
 * Note that some options passed here, are *not* available as {@link Wunderbaum.options}.
 * They are moved to the `tree` instance instead:
 * - `tree.element`
 * - `tree.id`
 * - `tree.columns`
 * - `tree.types`
 * - ...
 *
 * Some options are only used during initialization and are not stored in the
 * tree instance:
 * - `source`
 *
 */
export interface InitWunderbaumOptions extends Partial<WunderbaumOptions> {
  /**
   * The target `div` element (or selector) that shall become a Wunderbaum.
   */
  element: string | HTMLDivElement;
  /**
   * The identifier of this tree. Used to reference the instance, especially
   * when multiple trees are present (e.g. `tree = mar10.Wunderbaum.getTree("demo")`).
   *
   * @default `"wb_" + COUNTER`.
   */
  id?: string;
  /**
   * A list of maps that define column headers. If this option is set,
   * Wunderbaum becomes a treegrid control instead of a plain tree.
   * Column definitions can be passed as tree option, or be part of a `source`
   * response.
   * @default `[]` meaning this is a plain tree.
   */
  columns?: ColumnDefinitionList;
  /**
   * Define shared attributes for multiple nodes of the same type.
   * This allows for more compact data models. Type definitions can be passed
   * as tree option, or be part of a `source` response.
   *
   * @default `{}`.
   */
  types?: NodeTypeDefinitionMap;
  /**
   * Define the initial tree data. Typically a URL of an endpoint that serves
   * a JSON formatted structure, but also a callback, Promise, or static data
   * is allowed.
   *
   * @default `[]`.
   */
  source?: SourceType;
}
