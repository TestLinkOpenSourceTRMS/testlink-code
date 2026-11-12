/*!
 * Wunderbaum - types
 * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
 * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
 */

import { WunderbaumNode } from "./wb_node";
import { Wunderbaum } from "./wunderbaum";
import { WunderbaumOptions } from "./wb_options";

/** A value that can either be true, false, or undefined. */
export type TristateType = boolean | undefined;
/** Show/hide checkbox or display a radiobutton icon instead. */
export type CheckboxOption = boolean | "radio";
/** A value that can either be true, false, or undefined. */
export type SortOrderType = "asc" | "desc" | undefined;
/** An icon may either be
 * a string-tag that references an entry in the `iconMap` (e.g. `"folderOpen"`)),
 * an HTML string that contains a `<` and is used as-is,
 * an image URL string that contains a `.` or `/` and is rendered as `<img src='...'>`,
 * a class string such as `"bi bi-folder"`,
 * or a boolean value that indicates if the default icon should be used or hidden.
 */
export type IconOption = boolean | string;
/** Show/hide default tooltip or display a string. */
export type TooltipOption = boolean | string;

/*
 * `source` can be a url, an array of nodes, or an object with `url`, ...
 */
export interface SourceAjaxType {
  url: string;
  params?: any;
  body?: any;
  options?: RequestInit;
}
export type SourceListType = Array<WbNodeData>;
export interface SourceObjectType {
  _format?: "nested" | "flat";
  _version?: number;
  types?: NodeTypeDefinitionMap;
  columns?: ColumnDefinitionList;
  children: SourceListType;
  _keyMap?: { [key: string]: string };
  _positional?: Array<string>;
  // _typeList?: Array<string>;
  _valueMap?: { [key: string]: Array<string> };
}

/** Possible initilization for tree nodes. */
export type SourceType =
  | string
  | SourceListType
  | SourceAjaxType
  | SourceObjectType;

/** Passed to `find...()` methods. Should return true if node matches. */
export type MatcherCallback = (node: WunderbaumNode) => boolean;
/** Used for `tree.iconBadge` event. */
export type WbIconBadgeCallback = (
  e: WbIconBadgeEventType
) => WbIconBadgeEventResultType;
/**
 * Passed to `sort()` methods. Should return -1, 0, or 1.
 * @deprecated Use SortKeyCallback instead
 */
export type SortCallback = (a: WunderbaumNode, b: WunderbaumNode) => number;
/** Passed to `sort()` methods. Should return a representation that can be compared using `<`. */
export type SortKeyCallback = (node: WunderbaumNode) => string | number | any[];
/** When set as option, called when the value is needed (e.g. `colspan` type definition). */
export type BoolOptionResolver = (node: WunderbaumNode) => boolean;
/** When set as option, called when the value is needed (e.g. `icon` type definition). */
export type BoolOrStringOptionResolver = (
  node: WunderbaumNode
) => boolean | string;
/** A callback that receives a node instance and returns an arbitrary value type. */
export type NodeAnyCallback = (node: WunderbaumNode) => any;
/** A callback that receives a node instance and returns a string value. */
export type NodeStringCallback = (node: WunderbaumNode) => string;
/** A callback that receives a node instance and property name returns a value. */
export type NodePropertyGetterCallback = (
  node: WunderbaumNode,
  propName: string
) => any;
/** A callback that receives a node instance and returns an iteration modifier. */
export type NodeVisitCallback = (node: WunderbaumNode) => NodeVisitResponse;
/**
 * Returned by `NodeVisitCallback` to control iteration.
 * `false` stops iteration, `skip` skips descendants but continues.
 * All other values continue iteration.
 */
export type NodeVisitResponse = "skip" | boolean | void;
/**
 * A callback that receives a node-data dictionary and a node instance and
 * returns an iteration modifier.
 */
export type NodeToDictCallback = (
  dict: WbNodeData,
  node: WunderbaumNode
) => NodeVisitResponse;
/**
 * A callback that receives a node instance and may returnsa `false` to prevent
 * (de)selection.
 */
export type NodeSelectCallback = (node: WunderbaumNode) => boolean | void;

/** @internal */
export type DeprecationOptions = {
  since?: string;
  hint?: string;
};

/**
 * See also {@link WunderbaumNode.getOption|WunderbaumNode.getOption()}
 * to evaluate `node.NAME` setting and `tree.types[node.type].NAME`.
 */
export type DynamicBoolOption = boolean | BoolOptionResolver;
export type DynamicStringOption = string | BoolOptionResolver;
export type DynamicBoolOrStringOption =
  | boolean
  | string
  | BoolOrStringOptionResolver;
export type DynamicCheckboxOption = CheckboxOption | BoolOrStringOptionResolver;
export type DynamicIconOption = IconOption | BoolOrStringOptionResolver;
export type DynamicTooltipOption = TooltipOption | BoolOrStringOptionResolver;

// type WithWildcards<T> = T & { [key: string]: unknown };
/** A plain object (dictionary) that represents a node instance. */
export interface WbNodeData {
  /** Defines if the `selected` state is displayed as checkbox, radio button,
   * or hidden.
   * Defaults to {@link WunderbaumOptions.checkbox}.
   */
  checkbox?: CheckboxOption;
  /** Optional list of child nodes.
   * If `children` is an empty array, the node is considered a leaf.
   * If `lazy` is true and `children is undefined or null, the node, is
   * considered unloaded. Otherwise, the node is considered a leaf.
   */
  children?: Array<WbNodeData>;
  /** Additional classes that are added to `<div class='wb-row'>`. */
  classes?: string;
  /** Only show title in a single, merged column. */
  colspan?: boolean;
  /** Expand this node. */
  expanded?: boolean;
  /** Defaults to standard icons (doc, folder, folderOpen, ...)
   * from {@link WunderbaumOptions.iconMap}.
   * Can be overridden by {@link WunderbaumOptions.icon}.
   */
  icon?: IconOption;
  /** Tooltip for the node icon only. Defaults to {@link WunderbaumOptions.iconTooltip}. */
  iconTooltip?: TooltipOption;
  /** The node's key. Must be unique for the whole tree. Defaults to a sequence number. */
  key?: string;
  /** If true (and children are undefined or null), the node is considered lazy
   * and {@link WunderbaumOptions.lazyLoad} is called when expanded.
   */
  lazy?: boolean;
  /** Make child nodes single-select radio buttons. */
  radiogroup?: boolean;
  /** Node's reference key. Unlike {@link WunderbaumNode.key}, this value
   * may be non-unique. Nodes within the tree that share the same refKey are considered
   * clones.
   */
  refKey?: string;
  /** The node's selection status, typically displayed as a checkbox. */
  selected?: boolean;
  /** The node's status, typically displayed as merged single row.
   * @see {@link Wunderbaum.setStatus}
   */
  statusNodeType?: NodeStatusType;
  /** The node's title. Will be html escaped to prevent XSS. */
  title: string;
  /** Pass true to set node tooltip to the node's title. Defaults to {@link WunderbaumOptions.tooltip}. */
  tooltip?: TooltipOption;
  /** Inherit shared settings from the matching entry in `InitWunderbaumOptions.types`. */
  type?: string;
  /** Set to `true` to prevent selection. Defaults to {@link WunderbaumOptions.unselectable}. */
  unselectable?: boolean;
  /** @internal */
  _treeId?: string;
  /** Other data is passed to `node.data` and can be accessed via `node.data.NAME` */
  [key: string]: unknown;
}

/** A plain object (dictionary) that defines node icons. */
export interface IconMapType {
  error: string;
  loading: string;
  noData: string;
  expanderExpanded: string;
  expanderCollapsed: string;
  expanderLazy: string;
  checkChecked: string;
  checkUnchecked: string;
  checkUnknown: string;
  radioChecked: string;
  radioUnchecked: string;
  radioUnknown: string;
  folder: string;
  folderOpen: string;
  folderLazy: string;
  doc: string;
  colSortable: string;
  colSortAsc: string;
  colSortDesc: string;
  colFilter: string;
  colFilterActive: string;
  colMenu: string;
  [key: string]: string;
}
/* -----------------------------------------------------------------------------
 * EVENT CALLBACK TYPES
 * ---------------------------------------------------------------------------*/

/** Retuen value of an event handler that can return `false` to prevent the default action. */
export type WbCancelableEventResultType = false | void;

export interface WbTreeEventType {
  /** Name of the event. */
  type: string;
  /** The affected tree instance. */
  tree: Wunderbaum;
  /** Exposed utility module methods
   * (see [API docs](https://mar10.github.io/wunderbaum/api/modules/util.html)).
   */
  util: any;
  /** Originating HTML event if any (e.g. `click`). */
  event?: Event;
  // [key: string]: unknown;
}

export interface WbNodeEventType extends WbTreeEventType {
  /** The affected target node. */
  node: WunderbaumNode;
  /**
   * Contains the node's type information, i.e. `tree.types[node.type]` if
   * defined. Set to `{}` otherwise. @see {@link Wunderbaum.types}
   */
  typeInfo: NodeTypeDefinition;
}

export interface WbActivateEventType extends WbNodeEventType {
  prevNode: WunderbaumNode;
  /** The original event. */
  event: Event;
}

export interface WbChangeEventType extends WbNodeEventType {
  /** Additional information derived from the original change event. */
  info: WbEventInfo;
  /** The embedded element that fired the change event. */
  inputElem: HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement;
  /** The new value of the embedded element, depending on the input element type. */
  inputValue: any;
  /** Result of `inputElem.checkValidity()`. */
  inputValid: boolean;
}

export interface WbClickEventType extends WbTreeEventType {
  /** The original event. */
  event: MouseEvent;
  /** The clicked node if any. */
  node: WunderbaumNode;
  /** Additional information derived from the original mouse event. */
  info: WbEventInfo;
}

export interface WbDeactivateEventType extends WbNodeEventType {
  nextNode: WunderbaumNode;
  /** The original event. */
  event: Event;
}

export interface WbEditApplyEventType extends WbNodeEventType {
  /** Additional information derived from the original change event. */
  info: WbEventInfo;
  /** The input element of the node title that fired the change event. */
  inputElem: HTMLInputElement;
  /** The previous node title. */
  oldValue: string;
  /** The new node title. */
  newValue: string;
  /** Result of `inputElem.checkValidity()`. */
  inputValid: boolean;
}

export interface WbEditEditEventType extends WbNodeEventType {
  /** The input element of the node title that was just created. */
  inputElem: HTMLInputElement;
}

// export interface WbEnhanceTitleEventType extends WbNodeEventType {
//   titleSpan: HTMLSpanElement;
// }

export interface WbErrorEventType extends WbNodeEventType {
  error: any;
}

export interface WbExpandEventType extends WbNodeEventType {
  flag: boolean;
}

export interface WbFocusEventType extends WbTreeEventType {
  /** The original event. */
  event: FocusEvent;
  /** True if `focusin`, false if `focusout`. */
  flag: boolean;
}

export interface WbIconBadgeEventType extends WbNodeEventType {
  iconSpan: HTMLElement;
}

export interface WbIconBadgeEventResultType {
  /** Content of the badge `<span class='wb-badge'>` if any. */
  badge: string | number | HTMLSpanElement | null | false;
  /** Additional class name(s), separate with space. */
  badgeClass?: string;
  /** Tooltip for the badge. */
  badgeTooltip?: string;
}

export interface WbInitEventType extends WbTreeEventType {
  error?: any;
}

export interface WbKeydownEventType extends WbTreeEventType {
  /** The original event. */
  event: KeyboardEvent;
  node: WunderbaumNode;
  /** Additional information derived from the original keyboard event. */
  info: WbEventInfo;
}

export interface WbModifyChildEventType extends WbNodeEventType {
  /** Type of change: 'add', 'remove', 'rename', 'move', 'data', ... */
  operation: string;
  child: WunderbaumNode;
}

export interface WbReceiveEventType extends WbNodeEventType {
  response: any;
}

export interface WbSelectEventType extends WbNodeEventType {
  flag: boolean;
}

export interface WbButtonClickEventType extends WbTreeEventType {
  info: WbEventInfo;
  /** The associated command, e.g. 'menu', 'sort', 'filter', ... */
  command: string;
}

export interface WbRenderEventType extends WbNodeEventType {
  /**
   * True if the node's markup was not yet created. In this case the render
   * event should create embedded input controls (in addition to update the
   * values according to to current node data). <br>
   * False if the node's markup was already created. In this case the render
   * event should only update the values according to to current node data.
   */
  isNew: boolean;
  /** The node's `<span class='wb-node'>` element. */
  nodeElem: HTMLSpanElement;
  /** True if the node only displays the title and is stretched over all remaining columns. */
  isColspan: boolean;
  /**
   * Array of node's `<span class='wb-col'>` elements.
   * The first element is `<span class='wb-node wb-col'>`, which contains the
   * node title and icon (`idx: 0`, id: '*'`).
   */
  allColInfosById: ColumnEventInfoMap;
  /**
   * Array of node's `<span class='wb-node'>` elements,
   * *that should be rendered by the event handler*.
   * In contrast to `allColInfosById`, the node title is not part of this array.
   * If node.isColspan() is true, this array is empty (`[]`).
   * This allows to iterate over all relevant in a simple loop:
   * ```
   * for (const col of Object.values(e.renderColInfosById)) {
   *   switch (col.id) {
   *     default:
   *       // Assumption: we named column.id === node.data.NAME
   *       col.elem.textContent = node.data[col.id];
   *       break;
   *   }
   * }
   */
  renderColInfosById: ColumnEventInfoMap;
}

/**
 * Contains the node's type information, i.e. `tree.types[node.type]` if
 * defined.
 * @see {@link Wunderbaum.types} and {@link WunderbaumNode.getOption|WunderbaumNode.getOption()}
 * to evaluate `node.NAME` setting and `tree.types[node.type].NAME`.
 */
export interface NodeTypeDefinition {
  /** En/disable checkbox for matching nodes. */
  checkbox?: CheckboxOption;
  /** Optional class names that are added to all `div.wb-row` elements of matching nodes. */
  classes?: string;
  /** Only show title and hide other columns if any. */
  colspan?: boolean;
  /** Default icon for matching nodes. */
  icon?: IconOption;
  /** Default icon tooltip for matching nodes. */
  iconTooltip?: TooltipOption;
  // and more
  [key: string]: unknown;
}

/* -----------------------------------------------------------------------------
 * DATA TYPES
 * ---------------------------------------------------------------------------*/

export type NodeTypeDefinitionMap = { [type: string]: NodeTypeDefinition };

/**
 * Column type definitions.
 * @see {@link Wunderbaum.columns}
 */
export interface ColumnDefinition {
  /** Column ID as defined in `tree.columns` definition ("*" for title column). */
  id: string;
  /** Column header (defaults to id) */
  title: string;
  /** Column header tooltip (optional) */
  tooltip?: string;
  /** Column width or weight.
   * Either an absolute pixel value (e.g. `"50px"`) or a relative weight (e.g. `1`)
   * that is used to calculate the width  inside the remaining available space.
   * Default: `"*"`, which is interpreted as `1`.
   */
  width?: string | number;
  /** Only used for columns with a relative weight.
   * Default: `4px`.
   */
  minWidth?: string | number;
  /** Allow user to resize the column.
   * @default false (or global tree option `columnsSortable`)
   * @see {@link WunderbaumOptions.columnsResizable}.
   * @since 0.10.0
   */
  resizable?: boolean;
  /** Optional custom column width when user resized by mouse drag.
   * Default: unset.
   */
  customWidthPx?: number;
  /** Display a 'filter' button in the column header. Default: false. <br>
   * Note: The actual filtering must be implemented in the `buttonClick()` event.
   * @default false (or global tree option `columnsFilterable`)
   * @since 0.11.0
   */
  filterable?: boolean;
  /** .
   * Default: inactive. <br>
   * Note: The actual filtering must be implemented in the `buttonClick()` event.
   */
  filterActive?: boolean;
  /** Display a 'sort' button in the column header. Default: false. <br>
   * Note: The actual sorting must be implemented in the `buttonClick()` event.
   * @default false (or global tree option `columnsSortable`)
   * @see {@link WunderbaumOptions.columnsSortable}.
   * @since 0.11.0
   */
  sortable?: boolean;
  /** Optional custom column sort orde when user clicked the sort icon.
   * Default: unset, e.g. not sorted. <br>
   * Note: The actual sorting must be implemented in the `buttonClick()` event.
   * @since 0.11.0
   */
  sortOrder?: SortOrderType;
  /** Display a menu icon that may open a context menu for this column.
   * Note: The actual functionality must be implemented in the `buttonClick()` event.
   * @default false (or global tree option `columnsMenu`)
   * @see {@link WunderbaumOptions.columnsMenu}.
   * @since 0.11.0
   */
  menu?: boolean;
  /** Optional class names that are added to all `span.wb-col` header AND data
   * elements of that column. Separate multiple classes with space.
   */
  classes?: string;
  /** If `headerClasses` is a set, it will be used for the header element only
   * (unlike `classes`, which is used for body and header cells).
   * Separate multiple classes with space.
   */
  headerClasses?: string;
  // /** A list of icon definitions added to the column header.
  //  */
  // headerIcons?: string;
  /** Optional HTML content that is rendered into all `span.wb-col` elements of that column.*/
  html?: string;
  /** @internal */
  _weight?: number;
  /** @internal */
  _widthPx?: number;
  /** @internal */
  _ofsPx?: number;
  // ... and more
  [key: string]: unknown;
}

export type ColumnDefinitionList = Array<ColumnDefinition>;

/**
 * Column information (passed to the `render` event).
 */
export interface ColumnEventInfo {
  /** Column ID as defined in `tree.columns` definition ("*" for title column). */
  id: string;
  /** Column index (0: leftmost title column). */
  idx: number;
  /** The cell's `<span class='wb-col'>` element (null for plain trees). */
  elem: HTMLSpanElement | null;
  /** The value of `tree.columns[]` for the current index. */
  info: ColumnDefinition;
}

export type ColumnEventInfoMap = { [colId: string]: ColumnEventInfo };

/**
 * Additional information derived from mouse or keyboard events.
 * @see {@link Wunderbaum.getEventInfo}
 */
export interface WbEventInfo {
  /** The original HTTP Event.*/
  event: MouseEvent | KeyboardEvent;
  /** Canonical descriptive string of the event type including modifiers,
   * e.g. `Ctrl+Down`. @see {@link util.eventToString}
   */
  canonicalName: string;
  /** The tree instance. */
  tree: Wunderbaum;
  /** The affected node instance instance if any. */
  node: WunderbaumNode | null;
  /** The affected part of the node span (e.g. title, expander, ...). */
  region: NodeRegion;
  /** The definition of the affected column if any. */
  colDef?: ColumnDefinition;
  /** The index of affected column or -1. */
  colIdx: number;
  /** The column definition ID of affected column if any. */
  colId?: string;
  /** The affected column's span tag if any. */
  colElem?: HTMLSpanElement;
}

// export type WbTreeCallbackType = (e: WbTreeEventType) => any;
// export type WbNodeCallbackType = (e: WbNodeEventType) => any;
// export type WbRenderCallbackType = (e: WbRenderEventType) => void;

export type FilterModeType = null | "mark" | "dim" | "hide";
export type SelectModeType = "single" | "multi" | "hier";

export type NavigationType =
  | "down"
  | "first"
  | "firstCol"
  | "last"
  | "lastCol"
  | "left"
  | "nextMatch"
  | "pageDown"
  | "pageUp"
  | "parent"
  | "prevMatch"
  | "right"
  | "up";

export type ApplyCommandType =
  | NavigationType
  | "addChild"
  | "addSibling"
  | "collapse"
  | "collapseAll"
  | "copy"
  | "cut"
  | "edit"
  | "expand"
  | "expandAll"
  | "indent"
  | "moveDown"
  | "moveUp"
  | "outdent"
  | "paste"
  | "remove"
  | "rename"
  | "toggleSelect";

export type NodeFilterResponse = "skip" | "branch" | boolean | void;
export type NodeFilterCallback = (node: WunderbaumNode) => NodeFilterResponse;

/**
 * Possible values for {@link WunderbaumNode.update} and {@link Wunderbaum.update}.
 */
export enum ChangeType {
  /** Re-render the whole viewport, headers, and all rows. */
  any = "any",
  /** A node's title, icon, columns, or status have changed. Update the existing row markup. */
  data = "data",
  /** The `tree.columns` definition has changed beyond simple width adjustments. */
  colStructure = "colStructure",
  /** The viewport/window was resized. Adjust layout attributes for all elements. */
  resize = "resize",
  /** A node's definition has changed beyond status and data. Re-render the whole row's markup. */
  row = "row",
  /** Nodes have been added, removed, etc. Update markup. */
  structure = "structure",
  /** A node's status has changed. Update current row's classes, to reflect active, selected, ... */
  status = "status",
  /** Vertical scroll event. Update the 'top' property of all rows. */
  scroll = "scroll",
}

/** @internal */
export enum RenderFlag {
  clearMarkup = "clearMarkup",
  header = "header",
  redraw = "redraw",
  scroll = "scroll",
}

/** Possible values for {@link WunderbaumNode.setStatus}. */
export enum NodeStatusType {
  ok = "ok",
  loading = "loading",
  error = "error",
  noData = "noData",
  paging = "paging",
}

/** Define the subregion of a node, where an event occurred. */
export enum NodeRegion {
  unknown = "",
  checkbox = "checkbox",
  column = "column",
  expander = "expander",
  icon = "icon",
  prefix = "prefix",
  title = "title",
}

/** Initial navigation mode and possible transition. */
export enum NavModeEnum {
  /** Start with row mode, but allow cell-nav mode */
  startRow = "startRow",
  /** Cell-nav mode only */
  cell = "cell",
  /** Start in cell-nav mode, but allow row mode */
  startCell = "startCell",
  /** Row mode only */
  row = "row",
}

/** Translatable strings. */
export type TranslationsType = {
  /** Title of paging status nodes. @default "More..." */
  moreItems?: string;
  /** @default "Loading..." */
  loading: string;
  /** @default "Error" */
  loadError: string;
  /** @default "No data" */
  noData: string;
  /** @default " » " */
  breadcrumbDelimiter: string;
  /** @default "Found ${matches} of ${count}" */
  queryResult: string;
  /** @default "No result" */
  noMatch: string;
  /** @default "${match} of ${matches}" */
  matchIndex: string;
};
/* -----------------------------------------------------------------------------
 * METHOD OPTIONS TYPES
 * ---------------------------------------------------------------------------*/

/** Possible values for {@link WunderbaumNode.addChildren}. */
export interface AddChildrenOptions {
  /** Insert children before this node (or index)
   * @default undefined or null:  append as last child
   */
  before?: WunderbaumNode | number | null;
  /**
   * Set `node.expanded = true` according to tree.options.minExpandLevel.
   * This does *not* load lazy nodes.
   * @default true
   */
  applyMinExpanLevel?: boolean;
  /** (@internal Internal use, do not set! ) */
  _level?: number;
}

/** Possible values for {@link Wunderbaum.applyCommand} and {@link WunderbaumNode.applyCommand}. */
export interface ApplyCommandOptions {
  [key: string]: unknown;
}

/** Possible values for {@link Wunderbaum.expandAll} and {@link WunderbaumNode.expandAll}. */
export interface ExpandAllOptions {
  /** Expand and load lazy nodes @default false  */
  loadLazy?: boolean;
  /** Unload lazily loaded children if any (if collapsing). @default false */
  resetLazy?: boolean;
  /** Ignore tree's `minExpandLevel` option @default false */
  force?: boolean;
  /** Restrict expand level.
   * Pass 0 to make only toplevel nodes visible, 1 to expand one level deeper, etc.
   * @default unset (unlimited)
   */
  depth?: number;
  /**
   * Also collapse child nodes beyond the `depth` level.
   * Otherwise only the `depth` level is collapsed and the expand state of the
   * descendants is retained.
   * Only in combination with collapse and `depth`.
   * Expanding with `deep` option is not supported as recursion depth implied by
   * the `depth` option. However a `deep` option will be considered if
   * `collapseOthers` is set.
   * @default false
   */
  deep?: boolean;
  /**
   * Expand up to level=depth and collapse all other branches.
   * Only in combination with `flag == true`, `depth > 0`.
   * @default false
   */
  collapseOthers?: boolean;
  /** Keep active node visible @default true */
  keepActiveNodeVisible?: boolean;
}

/**
 * Possible option values for {@link Wunderbaum.filterNodes}.
 * The defaults are inherited from the tree instances ´tree.options.filter`
 * settings (see also {@link FilterOptionsType}).
 */
export interface FilterNodesOptions {
  /** Expand all branches that contain matches while filtered @default false */
  autoExpand?: boolean;
  /** Whether to implicitly match all children of matched nodes @default false */
  matchBranch?: boolean;
  /** Match single characters in order, e.g. 'fb' will match 'FooBar' @default false */
  fuzzy?: boolean;
  /**Hide expanders if all child nodes are hidden by filter @default false */
  hideExpanders?: boolean;
  /** Highlight matches by wrapping inside `<mark>` tags.
   * Does not work for filter callbacks.  @default true
   */
  highlight?: boolean;
  /** Match end nodes only @default false */
  leavesOnly?: boolean;
  /** Grayout unmatched nodes (pass 'hide' to remove instead) @default 'dim' */
  mode?: FilterModeType;
  /** Display a 'no data' status node if result is empty @default true */
  noData?: boolean | string;
}

/** Possible values for {@link Wunderbaum.getState}. */
export interface GetStateOptions {
  /** Include the active node's key (and expand its parents). @default true */
  activeKey?: boolean;
  /** Include the expanded keys. @default false */
  expandedKeys?: boolean;
  /** Include the selected keys. @default false */
  selectedKeys?: boolean;
}

/** Possible values for {@link Wunderbaum.setState}. */
export interface SetStateOptions {
  /** Recursively load lazy nodes as needed. @default false */
  expandLazy?: boolean;
}

/** Used by {@link Wunderbaum.getState} and {@link Wunderbaum.setState}. */
export interface TreeStateDefinition {
  /** List of expanded node's keys. */
  expandedKeys: Array<string> | undefined;
  /** The active node's key if any. */
  activeKey: string | null;
  /** The active column index if any. */
  activeColIdx: number | null;
  /** List of selected node's keys. */
  selectedKeys: Array<string> | undefined;
}

/** Possible values for {@link Wunderbaum.loadLazyNodes} `options` argument. */
export interface LoadLazyNodesOptions {
  /** Expand node (otherwise load, but keep collapsed). @default true */
  expand?: boolean;
  /** Force reloading even if already loaded. @default false */
  force?: boolean;
  /** Do not send events. @default false */
  noEvents?: boolean;
}

/** Possible values for {@link WunderbaumNode.makeVisible}. */
export interface MakeVisibleOptions {
  /** Do not animate expand (currently not implemented). @default false */
  noAnimation?: boolean;
  /** Scroll node into visible viewport area if required. @default true */
  scrollIntoView?: boolean;
  /** Do not send events. @default false */
  noEvents?: boolean;
}

/** Possible values for {@link WunderbaumNode.navigate}. */
export interface NavigateOptions {
  /** Activate the new node (otherwise focus only). @default true */
  activate?: boolean;
  /** Originating event (e.g. KeyboardEvent) if any. */
  event?: Event;
}

/** Possible values for {@link WunderbaumNode._render}. */
export interface RenderOptions {
  /** Which parts need update? @default ChangeType.data */
  change?: ChangeType;
  /** Where to append a new node. @default 'last' */
  after?: any;
  /** @internal. @default false */
  isNew?: boolean;
  /** @internal. @default false */
  preventScroll?: boolean;
  /** @internal. @default false */
  isDataChange?: boolean;
  /** @internal. @default false */
  top?: number;
  /** @internal. @default true */
  resizeCols?: boolean;
}

/** Possible values for {@link WunderbaumNode.scrollIntoView} `options` argument. */
export interface ScrollIntoViewOptions {
  /** Do not animate (currently not implemented). @default false */
  noAnimation?: boolean;
  /** Do not send events. @default false */
  noEvents?: boolean;
  /** Keep this node visible at the top in any case. */
  topNode?: WunderbaumNode;
  /** Add N pixel offset at top. */
  ofsY?: number;
}

/** Possible values for {@link Wunderbaum.scrollTo} `options` argument. */
export interface ScrollToOptions extends ScrollIntoViewOptions {
  /** Which node to scroll into the viewport.*/
  node: WunderbaumNode;
}

/** Possible values for {@link WunderbaumNode.setActive} `options` argument. */
export interface SetActiveOptions {
  /** Generate (de)activate event, even if node already has this status (@default: false). */
  retrigger?: boolean;
  /** Do not generate (de)activate event  (@default: false). */
  noEvents?: boolean;
  // /** Mark node as focused node (default: true).
  //  * Combine with `focusTree: true` to set keyboard focus to tree container.
  //  */
  // focusNode?: boolean;
  /** Call `tree.setFocus()` to acquire keyboard focus (@default: false). */
  focusTree?: boolean;
  /** Optional original event that will be passed to the (de)activate handler. */
  event?: Event;
  /** Also call {@link Wunderbaum.setColumn}. */
  colIdx?: number | string;
  /**
   * Focus embedded input control of the grid cell if any (requires colIdx >= 0).
   * If colIdx is 0 or '*', the node title is put into edit mode.
   * Implies `focusTree: true`, requires `colIdx`.
   */
  edit?: boolean;
}

/** Possible values for {@link Wunderbaum.setColumn} `options` argument. */
export interface SetColumnOptions {
  /**
   * Focus embedded input control of the grid cell if any .
   * If colIdx is 0 or '*', the node title is put into edit mode.
   * @default false
   */
  edit?: boolean;
  /** Horizontically scroll into view. @default: true */
  scrollIntoView?: boolean;
}

/** Possible values for {@link WunderbaumNode.setExpanded} `options` argument. */
export interface SetExpandedOptions {
  /** Ignore {@link WunderbaumOptions}.minExpandLevel. @default false */
  force?: boolean;
  /** Immediately update viewport (async otherwise). @default false */
  immediate?: boolean;
  /** Do not animate expand (currently not implemented). @default false */
  noAnimation?: boolean;
  /** Do not send events. @default false */
  noEvents?: boolean;
  /** Unload lazily loaded children if any (if collapsing). @default false */
  resetLazy?: boolean;
  /** Scroll up to bring expanded nodes into viewport. @default false */
  scrollIntoView?: boolean;
}

/** Possible values for {@link WunderbaumNode.update} `options` argument. */
export interface UpdateOptions {
  /** Force immediate redraw instead of throttled/async mode. @default false */
  immediate?: boolean;
  // /** Remove HTML markup of all rendered nodes before redraw. @default false */
  // removeMarkup?: boolean;
}

/** Possible values for {@link WunderbaumNode.setSelected} `options` argument. */
export interface SetSelectedOptions {
  /** Ignore restrictions, e.g. (`unselectable`). @default false */
  force?: boolean;
  /** Do not send `beforeSelect` or `select` events. @default false */
  noEvents?: boolean;
  /** Apply to all descendant nodes (only for `selectMode: 'multi'`). @default false */
  propagateDown?: boolean;
  // /** Apply to all ancestor nodes. @default false */
  // propagateUp?: boolean;
  /** Called for every node. May return false to prevent action. @default null */
  callback?: NodeSelectCallback;
}

/** Possible values for {@link WunderbaumNode.setStatus} `options` argument. */
export interface SetStatusOptions {
  /** Displayed as status node title. */
  message?: string;
  /** Used as tooltip. */
  details?: string;
}

/**
 * Possible values for {@link Wunderbaum.reload} `options` argument.
 */
export interface ReloadOptions {
  /** Load this source instead. @default initial source (if loaded via ajax) */
  source?: SourceType;
  /** Reactivate currently active node if any. @default true */
  reactivate?: boolean;
}

/**
 * Possible values for {@link WunderbaumNode.resetNativeChildOrder} `options` argument.
 */
export interface ResetOrderOptions {
  /** Sort descendants recursively. @default true */
  recursive?: boolean;
  /** The name of the node property that will be renumbered.
   * @default `_nativeIndex`.
   */
  propName?: string;
}

/**
 * Possible values for {@link Wunderbaum.sort} and {@link WunderbaumNode.sort}
 * `options` argument.
 */
export interface SortOptions {
  /** The name of the node property that will be used for sorting.
   * Mandatory, unless  {@link key} or  {@link colId} are given.
   */
  propName?: string;
  /** Callback that determines a node representation for comparison.
   * @default {@link common.nodeTitleKeyGetter} */
  key?: SortKeyCallback;
  /** Callback that determines the order. @default {@link common.nodeTitleSorter}
   * @deprecated use {@link key} instead
   */
  cmp?: SortCallback;
  /** Sort order 'asc' or 'desc'.
   * @default 'asc' (or if `updateColInfo` is true, the rotated status of the
   * column definition.
   * See also {@link WunderbaumOptions.sortFoldersFirst}.
   */
  order?: SortOrderType;
  /** Sort descendants recursively. @default true */
  deep?: boolean;
  /** Sort string values case insensitive. @default false */
  caseInsensitive?: boolean;
  /**
   * Sort by this property if order is `undefined`.
   * See also {@link WunderbaumNode.resetNativeChildOrder}.
   * @default `_nativeIndex`.
   */
  nativeOrderPropName?: string;
  /**
   * Rotate sort order (asc -> desc -> none) before sorting.
   * Update the sort icons in the column header
   * Note:
   * Sorting is done in-place. There is no 'unsorted' state, but we can
   * call `setCurrentSortOrder()` to renumber the `node._sortIdx` property,
   * which will be used as sort key, when `order` is `undefined`.
   * @default false
   */
  updateColInfo?: boolean;
  /** Column ID as defined in `tree.columns` definition. Required if updateColInfo is true.*/
  colId?: string;
}

/**
 * Possible values for {@link WunderbaumNode.sortByProperty} `options` argument.
 * @deprecated
 */
export type SortByPropertyOptions = SortOptions;

/** Options passed to {@link Wunderbaum.visitRows}. */
export interface VisitRowsOptions {
  /** Skip filtered nodes and children of collapsed nodes. @default false */
  includeHidden?: boolean;
  /** Return the start node as first result. @default true */
  includeSelf?: boolean;
  /** Traverse in opposite direction, i.e. bottom up. @default false */
  reverse?: boolean;
  /** Start traversal at this node @default first (topmost) tree node */
  start?: WunderbaumNode | null;
  /** Wrap around at last node and continue at the top,
   * until the start node is reached again @default false */
  wrap?: boolean;
}

/* -----------------------------------------------------------------------------
 * wb_ext_filter
 * ---------------------------------------------------------------------------*/

/**
 * Passed as tree option.filer.connect to configure automatic integration of
 * filter UI controls. @experimental
 */
export interface FilterConnectType {
  inputElem: string | HTMLInputElement | null;
  modeButton?: string | HTMLButtonElement | null;
  nextButton?: string | HTMLButtonElement | HTMLAnchorElement | null;
  prevButton?: string | HTMLButtonElement | HTMLAnchorElement | null;
  matchInfoElem?: string | HTMLElement | null;
}

/**
 * Passed as tree options to configure default filtering behavior.
 *
 * @see {@link Wunderbaum.filterNodes}
 * @see {@link FilterNodesOptions}
 */
export type FilterOptionsType = {
  /**
   * Element or selector of input controls and buttons for filter query strings.
   * @experimental
   * @since 0.13
   * @default null
   */
  connect?: null | FilterConnectType;
  /**
   * Re-apply last filter if lazy data is loaded
   * @default true
   */
  autoApply?: boolean;
} & FilterNodesOptions;

/* -----------------------------------------------------------------------------
 * wb_ext_edit
 * ---------------------------------------------------------------------------*/
/**
 * Note: <br>
 * This options are used for renaming node titles. <br>
 * There is also the `tree.change` event to handle modifying node data from
 * input controls that are embedded in grid cells.
 */
export type EditOptionsType = {
  /**
   * Used to debounce the `change` event handler for grid cells [ms].
   * @default 100
   */
  debounce?: number;
  /**
   * Minimum number of characters required for node title input field.
   * @default 1
   */
  minlength?: number;
  /**
   * Maximum number of characters allowed for node title input field.
   * @default null;
   */
  maxlength?: null | number;
  /**
   * Array of strings to determine which user input should trigger edit mode.
   * E.g. `["clickActive", "F2", "macEnter"]`: <br>
   * 'clickActive': single click on active node title <br>
   * 'F2': press F2 key <br>
   * 'macEnter': press Enter (on macOS only) <br>
   * Pass an empty array to disable edit mode.
   * @default []
   */
  trigger?: string[];
  /**
   * Trim whitespace before saving a node title.
   * @default true
   */
  trim?: boolean;
  /**
   * Select all text of a node title, so it can be overwritten by typing.
   * @default true
   */
  select?: boolean;
  /**
   * Handle 'clickActive' only if last click is less than this ms old (0: always)
   * @default 1000
   */
  slowClickDelay?: number;
  /**
   * Permanently apply node title input validations (CSS and tooltip) on keydown.
   * @default true
   */
  validity?: boolean;

  // --- Events ---

  /**
   * `beforeEdit(e)` may return an input HTML string. Otherwise use a default.
   * @category Callback
   */
  beforeEdit?: null | ((e: WbNodeEventType) => boolean) | string;
  /**
   *
   * @category Callback
   */
  edit?:
    | null
    | ((e: WbNodeEventType & { inputElem: HTMLInputElement }) => void);
  /**
   *
   * @category Callback
   */
  apply?:
    | null
    | ((e: WbNodeEventType & { inputElem: HTMLInputElement }) => any)
    | Promise<any>;
};

/* -----------------------------------------------------------------------------
 * wb_ext_dnd
 * ---------------------------------------------------------------------------*/

export type InsertNodeType =
  | "before"
  | "after"
  | "prependChild"
  | "appendChild";

export type DropEffectType = "none" | "copy" | "link" | "move";
export type DropEffectAllowedType =
  | "none"
  | "copy"
  | "copyLink"
  | "copyMove"
  | "link"
  | "linkMove"
  | "move"
  | "all";

export type DropRegionType = "over" | "before" | "after";
export type DropRegionTypeSet = Set<DropRegionType>;
export type DropRegionTypeList = Array<DropRegionType>;
// type AllowedDropRegionType =
//   | "after"
//   | "afterBefore"
//   // | "afterBeforeOver" // == all == true
//   | "afterOver"
//   | "all" // == true
//   | "before"
//   | "beforeOver"
//   | "none" // == false == "" == null
//   | "over"; // == "child"

export interface DragEventType extends WbNodeEventType {
  /** The original event. */
  event: DragEvent;
  /** The source node. */
  node: WunderbaumNode;
}

export interface DropEventType extends WbNodeEventType {
  /** The original event. */
  event: DragEvent;
  /** The target node. */
  node: WunderbaumNode;
  /** The source node if any. */
  sourceNode: WunderbaumNode;
  /** The DataTransfer object. */
  dataTransfer: DataTransfer;
}

export type DndOptionsType = {
  /**
   * Expand nodes after n milliseconds of hovering
   * @default 1500
   */
  autoExpandMS?: 1500;
  // /**
  //  * Additional offset for drop-marker with hitMode = "before"/"after"
  //  * @default
  //  */
  // dropMarkerInsertOffsetX: -16;
  // /**
  //  * Absolute position offset for .fancytree-drop-marker relatively to ..fancytree-title (icon/img near a node accepting drop)
  //  * @default
  //  */
  // dropMarkerOffsetX: -24;
  // /**
  //  * Root Container used for drop marker (could be a shadow root)
  //  * (#1021 `document.body` is not available yet)
  //  * @default
  //  */
  // dropMarkerParent: "body";
  /**
   * true: Drag multiple (i.e. selected) nodes. Also a callback() is allowed
   * @default false
   */
  multiSource?: false;
  /**
   * Restrict the possible cursor shapes and modifier operations
   * (can also be set in the dragStart event)
   * @default "all"
   */
  effectAllowed?: DropEffectAllowedType;
  /**
   * Default dropEffect ('copy', 'link', or 'move') when no modifier is pressed.
   * Overidable in the dragEnter or dragOver event.
   * @default "move"
   */
  dropEffectDefault?: DropEffectType;
  /**
   * Use opinionated heuristics to determine the dropEffect ('copy', 'link', or 'move')
   * based on `effectAllowed`, `dropEffectDefault`, and modifier keys.
   * This is recalculated before each dragEnter and dragOver event and can be
   * overridden there.
   *
   * @default true
   */
  guessDropEffect: boolean;
  /**
   * Prevent dropping nodes from different Wunderbaum trees
   * @default false
   */
  preventForeignNodes?: boolean;
  /**
   * Prevent dropping items on unloaded lazy Wunderbaum tree nodes
   * @default true
   */
  preventLazyParents?: boolean;
  /**
   * Prevent dropping items other than Wunderbaum tree nodes
   * @default false
   */
  preventNonNodes?: boolean;
  /**
   * Prevent dropping nodes on own descendants
   * @default true
   */
  preventRecursion?: boolean;
  /**
   * Prevent dropping nodes under same direct parent
   * @default false
   */
  preventSameParent?: boolean;
  /**
   * Prevent dropping nodes 'before self', etc. (move only)
   * @default true
   */
  preventVoidMoves?: boolean;
  /**
   * Serialize Node Data to datatransfer object
   * @default true
   */
  serializeClipboardData?:
    | boolean
    | ((nodeData: WbNodeData, node: WunderbaumNode) => string);
  /**
   * Enable auto-scrolling while dragging
   * @default true
   */
  scroll?: boolean;
  /**
   * Active top/bottom margin in pixel
   * @default 20
   */
  scrollSensitivity?: 20;
  // /**
  //  * Scroll events every N microseconds
  //  * @default 50
  //  */
  // scrollnterval: 50;
  /**
   * Pixel per event
   * @default 5
   */
  scrollSpeed?: 5;
  // /**
  //  * Allow dragging of nodes to different IE windows
  //  * @default false
  //  */
  // setTextTypeJson: boolean;
  /**
   * Optional callback passed to `toDict` on dragStart
   * @default null
   * @category Callback
   */
  sourceCopyHook?: null;
  // Events (drag support)
  /**
   * Callback(sourceNode, data), return true, to enable dnd drag
   * @default null
   * @category Callback
   */
  dragStart?: null | ((e: DragEventType) => boolean);
  /**
   * Callback(sourceNode, data)
   * @default null
   * @category Callback
   */
  drag?: null | ((e: DragEventType) => void);
  /**
   * Callback(sourceNode, data)
   * @default null
   * @category Callback
   */
  dragEnd?: null | ((e: DragEventType) => void);
  // Events (drop support)
  /**
   * Callback(targetNode, data), return true, to enable dnd drop
   * @default null
   * @category Callback
   */
  dragEnter?:
    | null
    | ((
        e: DropEventType
      ) => DropRegionType | DropRegionTypeSet | DropRegionTypeList | boolean);
  /**
   * Callback(targetNode, data)
   * @default null
   * @category Callback
   */
  dragOver?: null | ((e: DropEventType) => void);
  /**
   * Callback(targetNode, data), return false to prevent autoExpand
   * @default null
   * @category Callback
   */
  dragExpand?: null | ((e: DropEventType) => boolean);
  /**
   * Callback(targetNode, data)
   * @default null
   * @category Callback
   */
  drop?:
    | null
    | ((
        e: WbNodeEventType & {
          event: DragEvent;
          region: DropRegionType;
          suggestedDropMode: InsertNodeType;
          suggestedDropEffect: DropEffectType;
          sourceNode: WunderbaumNode;
          sourceNodeData: WbNodeData | null;
        }
      ) => void);
  /**
   * Callback(targetNode, data)
   * @default null
   * @category Callback
   */
  dragLeave?: null | ((e: DropEventType) => void);
};

/* -----------------------------------------------------------------------------
 * wb_ext_grid
 * ---------------------------------------------------------------------------*/
export type GridOptionsType = object;

/* -----------------------------------------------------------------------------
 * wb_ext_keynav
 * ---------------------------------------------------------------------------*/
export type KeynavOptionsType = object;

/* -----------------------------------------------------------------------------
 * wb_ext_loger
 * ---------------------------------------------------------------------------*/
export type LoggerOptionsType = object;
