declare module "debounce" {
    /*!
     * Wunderbaum - debounce.ts
     * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
     * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
     */
    type Procedure = (...args: any[]) => any;
    type DebounceOptions = {
        /** Specify invoking on the leading edge of the timeout. @default false */
        leading?: boolean;
        /** The maximum time `func` is allowed to be delayed before it's invoked.*/
        maxWait?: number;
        /**  Specify invoking on the trailing edge of the timeout. @default true */
        trailing?: boolean;
    };
    type ThrottleOptions = {
        /** Specify invoking on the leading edge of the timeout. @default true */
        leading?: boolean;
        /**  Specify invoking on the trailing edge of the timeout. @default true */
        trailing?: boolean;
    };
    export interface DebouncedFunction<F extends Procedure> {
        (this: ThisParameterType<F>, ...args: Parameters<F>): ReturnType<F>;
        cancel: () => void;
        flush: () => any;
        pending: () => boolean;
    }
    /**
     * Creates a debounced function that delays invoking `func` until after `wait`
     * milliseconds have elapsed since the last time the debounced function was
     * invoked, or until the next browser frame is drawn. The debounced function
     * comes with a `cancel` method to cancel delayed `func` invocations and a
     * `flush` method to immediately invoke them. Provide `options` to indicate
     * whether `func` should be invoked on the leading and/or trailing edge of the
     * `wait` timeout. The `func` is invoked with the last arguments provided to the
     * debounced function. Subsequent calls to the debounced function return the
     * result of the last `func` invocation.
     *
     * **Note:** If `leading` and `trailing` options are `true`, `func` is
     * invoked on the trailing edge of the timeout only if the debounced function
     * is invoked more than once during the `wait` timeout.
     *
     * If `wait` is `0` and `leading` is `false`, `func` invocation is deferred
     * until the next tick, similar to `setTimeout` with a timeout of `0`.
     *
     * If `wait` is omitted in an environment with `requestAnimationFrame`, `func`
     * invocation will be deferred until the next frame is drawn (typically about
     * 16ms).
     *
     * See [David Corbacho's article](https://css-tricks.com/debouncing-throttling-explained-examples/)
     * for details over the differences between `debounce` and `throttle`.
     *
     * @since 0.1.0
     * @category Function
     * @param {Function} func The function to debounce.
     * @param {number} [wait=0]
     *  The number of milliseconds to delay; if omitted, `requestAnimationFrame` is
     *  used (if available).
     * @param [options={}] The options object.
     * @returns {Function} Returns the new debounced function.
     * @example
     *
     * // Avoid costly calculations while the window size is in flux.
     * jQuery(window).on('resize', debounce(calculateLayout, 150))
     *
     * // Invoke `sendMail` when clicked, debouncing subsequent calls.
     * jQuery(element).on('click', debounce(sendMail, 300, {
     *   'leading': true,
     *   'trailing': false
     * }))
     *
     * // Ensure `batchLog` is invoked once after 1 second of debounced calls.
     * const debounced = debounce(batchLog, 250, { 'maxWait': 1000 })
     * const source = new EventSource('/stream')
     * jQuery(source).on('message', debounced)
     *
     * // Cancel the trailing debounced invocation.
     * jQuery(window).on('popstate', debounced.cancel)
     *
     * // Check for pending invocations.
     * const status = debounced.pending() ? "Pending..." : "Ready"
     */
    export function debounce<F extends Procedure>(func: F, wait?: number, options?: DebounceOptions): DebouncedFunction<F>;
    /**
     * Creates a throttled function that only invokes `func` at most once per
     * every `wait` milliseconds (or once per browser frame). The throttled function
     * comes with a `cancel` method to cancel delayed `func` invocations and a
     * `flush` method to immediately invoke them. Provide `options` to indicate
     * whether `func` should be invoked on the leading and/or trailing edge of the
     * `wait` timeout. The `func` is invoked with the last arguments provided to the
     * throttled function. Subsequent calls to the throttled function return the
     * result of the last `func` invocation.
     *
     * **Note:** If `leading` and `trailing` options are `true`, `func` is
     * invoked on the trailing edge of the timeout only if the throttled function
     * is invoked more than once during the `wait` timeout.
     *
     * If `wait` is `0` and `leading` is `false`, `func` invocation is deferred
     * until the next tick, similar to `setTimeout` with a timeout of `0`.
     *
     * If `wait` is omitted in an environment with `requestAnimationFrame`, `func`
     * invocation will be deferred until the next frame is drawn (typically about
     * 16ms).
     *
     * See [David Corbacho's article](https://css-tricks.com/debouncing-throttling-explained-examples/)
     * for details over the differences between `throttle` and `debounce`.
     *
     * @since 0.1.0
     * @category Function
     * @param {Function} func The function to throttle.
     * @param {number} [wait=0]
     *  The number of milliseconds to throttle invocations to; if omitted,
     *  `requestAnimationFrame` is used (if available).
     * @param [options={}] The options object.
     * @returns {Function} Returns the new throttled function.
     * @example
     *
     * // Avoid excessively updating the position while scrolling.
     * jQuery(window).on('scroll', throttle(updatePosition, 100))
     *
     * // Invoke `renewToken` when the click event is fired, but not more than once every 5 minutes.
     * const throttled = throttle(renewToken, 300000, { 'trailing': false })
     * jQuery(element).on('click', throttled)
     *
     * // Cancel the trailing throttled invocation.
     * jQuery(window).on('popstate', throttled.cancel)
     */
    export function throttle<F extends Procedure>(func: F, wait?: number, options?: ThrottleOptions): DebouncedFunction<F>;
}
declare module "util" {
    /*!
     * Wunderbaum - util
     * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
     * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
     */
    /** @module util */
    import { DebouncedFunction, debounce, throttle } from "debounce";
    export { debounce, throttle };
    /** Readable names for `MouseEvent.button` */
    export const MOUSE_BUTTONS: {
        [key: number]: string;
    };
    export const MAX_INT = 9007199254740991;
    /**True if the client is using a macOS platform. */
    export const isMac: boolean;
    export type NotPromise<T> = T extends Promise<any> ? never : T;
    export type FunctionType = (...args: any[]) => any;
    export type EventCallbackType = (e: Event) => boolean | void;
    /** A generic error that can be thrown to indicate a validation error when
     * handling the `apply` event for a node title or the `change` event for a
     * grid cell.
     */
    export class ValidationError extends Error {
        constructor(message: string);
    }
    /**
     * A ES6 Promise, that exposes the resolve()/reject() methods.
     *
     * TODO: See [Promise.withResolvers()](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Promise/withResolvers#description)
     * , a proposed standard, but not yet implemented in any browser.
     */
    export class Deferred {
        private thens;
        private catches;
        private status;
        private resolvedValue;
        private rejectedError;
        constructor();
        resolve(value?: any): void;
        reject(error?: any): void;
        then(cb: any): void;
        catch(cb: any): void;
        promise(): {
            then: (cb: any) => void;
            catch: (cb: any) => void;
        };
    }
    /**Throw an `Error` if `cond` is falsey. */
    export function assert(cond: any, msg: string): void;
    /** Run `callback` when document was loaded. */
    export function documentReady(callback: () => void): void;
    /** Resolve when document was loaded. */
    export function documentReadyPromise(): Promise<void>;
    /**
     * Iterate over Object properties or array elements.
     *
     * @param obj `Object`, `Array` or null
     * @param callback called for every item.
     *  `this` also contains the item.
     *  Return `false` to stop the iteration.
     */
    export function each(obj: any, callback: (index: number | string, item: any) => void | boolean): any;
    /** Shortcut for `throw new Error(msg)`. */
    export function error(msg: string): void;
    /** Convert `<`, `>`, `&`, `"`, `'`, and `/` to the equivalent entities. */
    export function escapeHtml(s: string): string;
    /**Convert a regular expression string by escaping special characters (e.g. `"$"` -> `"\$"`) */
    export function escapeRegex(s: string): string;
    /** Convert `<`, `>`, `"`, `'`, and `/` (but not `&`) to the equivalent entities. */
    export function escapeTooltip(s: string): string;
    /** TODO */
    export function extractHtmlText(s: string): string;
    /**
     * Read the value from an HTML input element.
     *
     * If a `<span class="wb-col">` is passed, the first child input is used.
     * Depending on the target element type, `value` is interpreted accordingly.
     * For example for a checkbox, a value of true, false, or null is returned if
     * the element is checked, unchecked, or indeterminate.
     * For datetime input control a numerical value is assumed, etc.
     *
     * Common use case: store the new user input in a `change` event handler:
     *
     * ```ts
     *   change: (e) => {
     *     const tree = e.tree;
     *     const node = e.node;
     *     // Read the value from the input control that triggered the change event:
     *     let value = tree.getValueFromElem(e.element);
     *     // and store it to the node model (assuming the column id matches the property name)
     *     node.data[e.info.colId] = value;
     *   },
     * ```
     * @param elem `<input>` or `<select>` element. Also a parent `span.wb-col` is accepted.
     * @param coerce pass true to convert date/time inputs to `Date`.
     * @returns the value
     */
    export function getValueFromElem(elem: HTMLElement, coerce?: boolean): any;
    /**
     * Set the value of an HTML input element.
     *
     * If a `<span class="wb-col">` is passed, the first child input is used.
     * Depending on the target element type, `value` is interpreted accordingly.
     * For example a checkbox is set to checked, unchecked, or indeterminate if the
     * value is truethy, falsy, or `null`.
     * For datetime input control a numerical value is assumed, etc.
     *
     * Common use case: update embedded input controls in a `render` event handler:
     *
     * ```ts
     *   render: (e) => {
     *     // e.node.log(e.type, e, e.node.data);
     *
     *     for (const col of Object.values(e.renderColInfosById)) {
     *       switch (col.id) {
     *         default:
     *           // Assumption: we named column.id === node.data.NAME
     *           util.setValueToElem(col.elem, e.node.data[col.id]);
     *           break;
     *       }
     *     }
     *   },
     * ```
     *
     * @param elem `<input>` or `<select>` element Also a parent `span.wb-col` is accepted.
     * @param value a value that matches the target element.
     */
    export function setValueToElem(elem: HTMLElement, value: any): void;
    /** Show/hide element by setting the `display` style to 'none'. */
    export function setElemDisplay(elem: string | HTMLElement, flag: boolean): void;
    /** Create and return an unconnected `HTMLElement` from a HTML string. */
    export function elemFromHtml<T = HTMLElement>(html: string): T;
    /** Return a HtmlElement from selector or cast an existing element. */
    export function elemFromSelector<T = HTMLElement>(obj: string | T): T | null;
    /**
     * Return a canonical descriptive string for a keyboard or mouse event.
     *
     * The result also contains a prefix for modifiers if any, for example
     * `"x"`, `"F2"`, `"Control+Home"`, or `"Shift+clickright"`.
     * This is especially useful in `switch` statements, to make sure that modifier
     * keys are considered and handled correctly:
     * ```ts
     *   const eventName = util.eventToString(e);
     *   switch (eventName) {
     *     case "+":
     *     case "Add":
     *       ...
     *       break;
     *     case "Enter":
     *     case "End":
     *     case "Control+End":
     *     case "Meta+ArrowDown":
     *     case "PageDown":
     *       ...
     *       break;
     *   }
     * ```
     */
    export function eventToString(event: Event): string;
    /**
     * Copy allproperties from one or more source objects to a target object.
     *
     * @returns the modified target object.
     */
    export function extend(...args: any[]): any;
    /** Return true if `obj` is of type `array`. */
    export function isArray(obj: any): obj is any[];
    /** Return true if `obj` is of type `Object` and has no properties. */
    export function isEmptyObject(obj: any): boolean;
    /** Return true if `obj` is of type `function`. */
    export function isFunction(obj: any): boolean;
    /** Return true if `obj` is of type `Object`. */
    export function isPlainObject(obj: any): boolean;
    /** A dummy function that does nothing ('no operation'). */
    export function noop(...args: any[]): any;
    /**
     * Bind one or more event handlers directly to an [EventTarget](https://developer.mozilla.org/en-US/docs/Web/API/EventTarget).
     *
     * @param rootTarget EventTarget or selector
     * @param eventNames
     * @param handler
     */
    export function onEvent(rootTarget: EventTarget | string, eventNames: string, handler: EventCallbackType): void;
    /**
     * Bind one or more event handlers using event delegation.
     *
     * E.g. handle all 'input' events for input and textarea elements of a given
     * form:
     * ```ts
     * onEvent("#form_1", "input", "input,textarea", function (e: Event) {
     *   console.log(e.type, e.target);
     * });
     * ```
     *
     * @param rootTarget EventTarget or selector
     * @param eventNames
     * @param selector
     * @param handler
     */
    export function onEvent(rootTarget: EventTarget | string, eventNames: string, selector: string, handler: EventCallbackType): void;
    /** Return a wrapped handler method, that provides `this._super` and `this._superApply`.
     *
     * ```ts
      // Implement `opts.createNode` event to add the 'draggable' attribute
      overrideMethod(ctx.options, "createNode", (event, data) => {
        // Default processing if any
        this._super.apply(this, event, data);
        // Add 'draggable' attribute
        data.node.span.draggable = true;
      });
      ```
      */
    export function overrideMethod(instance: any, methodName: string, handler: FunctionType, ctx?: any): void;
    /** Run function after ms milliseconds and return a promise that resolves when done. */
    export function setTimeoutPromise<T = unknown>(this: unknown, callback: (...args: any[]) => T, ms: number): Promise<T>;
    /**
     * Wait `ms` microseconds.
     *
     * Example:
     * ```js
     * await sleep(1000);
     * ```
     * @param ms duration
     * @returns
     */
    export function sleep(ms: number): Promise<unknown>;
    /**
     * Set or rotate checkbox status with support for tri-state.
     *
     * An initial 'indeterminate' state becomes 'checked' on the first call.
     *
     * If the input element has the class 'wb-tristate' assigned, the sequence is:<br>
     * 'indeterminate' -> 'checked' -> 'unchecked' -> 'indeterminate' -> ...<br>
     * Otherwise we toggle like <br>
     * 'checked' -> 'unchecked' -> 'checked' -> ...
     */
    export function toggleCheckbox(element: HTMLElement | string, value?: boolean | null, tristate?: boolean): void;
    /**
     * Return `opts.NAME` if opts is valid and
     *
     * @param opts dict, object, or null
     * @param name option name (use dot notation to access extension option, e.g. `filter.mode`)
     * @param defaultValue returned when `opts` is not an object, or does not have a NAME property
     */
    export function getOption(opts: any, name: string, defaultValue?: any): any;
    /** Return the next value from a list of values (rotating). @since 0.11 */
    export function rotate(value: any, values: any[]): any;
    /** Convert an Array or space-separated string to a Set. */
    export function toSet(val: any): Set<string>;
    /** Convert a pixel string to number.
     * We accept a number or a string like '123px'. If undefined, the first default
     * value that is a number or a string ending with 'px' is returned.
     *
     * Example:
     * ```js
     * let x = undefined;
     * let y = "123px";
     * const width = util.toPixel(x, y, 100);  // returns 123
     * ```
     */
    export function toPixel(...defaults: (string | number | undefined | null)[]): number;
    /** Cast any value to <T>. */
    export function unsafeCast<T>(value: any): T;
    /** Return the the boolean value of the first non-null element.
     * Example:
     * ```js
     * const opts = { flag: true };
     * const value = util.toBool(opts.foo, opts.flag, false);  // returns true
     * ```
     */
    export function toBool(...boolDefaults: (boolean | undefined | null)[]): boolean;
    /**
     * Return `val` unless `val` is a number in which case we convert to boolean.
     * This is useful when a boolean value is stored as a 0/1 (e.g. in JSON) and
     * we still want to maintain string values. null and undefined are returned as
     * is. E.g. `checkbox` may be boolean or 'radio'.
     */
    export function intToBool(val: boolean | number | string | undefined): boolean | string | undefined;
    /** Return a canonical string representation for an object's type (e.g. 'array', 'number', ...). */
    export function type(obj: any): string;
    /**
     * Return a function that can be called instead of `callback`, but guarantees
     * a limited execution rate.
     * The execution rate is calculated based on the runtime duration of the
     * previous call.
     * Example:
     * ```js
     * throttledFoo = util.adaptiveThrottle(foo.bind(this), {});
     * throttledFoo();
     * throttledFoo();
     * ```
     */
    export function adaptiveThrottle(this: unknown, callback: (...args: any[]) => void, options: object): DebouncedFunction<(...args: any[]) => void>;
    /**
     * MurmurHash3 implementation for strings.
     * @param key The input string to hash.
     * @param asString Optional convert result to zero-padded string of 8 characters.
     * @param seed Optional seed value.
     * @returns A 32-bit hash as a number or string.
     */
    export function murmurHash3(key: string, asString?: boolean, seed?: number): number | string;
}
declare module "common" {
    /*!
     * Wunderbaum - common
     * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
     * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
     */
    import { ApplyCommandType, NavigationType, SourceObjectType, IconMapType, MatcherCallback } from "types";
    import { WunderbaumNode } from "wb_node";
    export const DEFAULT_DEBUGLEVEL = 4;
    /**
     * Fixed height of a row in pixel. Must match the SCSS variable `$row-outer-height`.
     */
    export const DEFAULT_ROW_HEIGHT = 22;
    /**
     * Fixed width of node icons in pixel. Must match the SCSS variable `$icon-outer-width`.
     */
    export const ICON_WIDTH = 20;
    /**
     * Adjust the width of the title span, so overflow ellipsis work.
     * (2 x `$col-padding-x` + 3px rounding errors).
     */
    export const TITLE_SPAN_PAD_Y = 7;
    /** Render row markup for N nodes above and below the visible viewport. */
    export const RENDER_MAX_PREFETCH = 5;
    /** Skip rendering new rows when we have at least N nodes rendeed above and below the viewport. */
    export const RENDER_MIN_PREFETCH = 5;
    /** Minimum column width if not set otherwise. */
    export const DEFAULT_MIN_COL_WIDTH = 4;
    /**
     * A value for `node.type` that by convention may be used to mark a node as directory.
     * It may be used to sort 'directories' to the top.
     */
    export const NODE_TYPE_FOLDER = "folder";
    /** Regular expression to detect if a string describes an image URL (in contrast
     * to a class name). Strings are considered image urls if they contain '.' or '/'.
     * `<` is ignored, because it is probably an html tag.
     */
    export const TEST_FILE_PATH: RegExp;
    /** Regular expression to detect if a string describes an HTML element. */
    export const TEST_HTML: RegExp;
    /** Currently supported default icon maps. */
    type IconLibrary = "bootstrap" | "fontawesome6";
    /**
     * Default node icons for icon libraries
     *
     *  - 'bootstrap': {@link https://icons.getbootstrap.com}
     *  - 'fontawesome6' {@link https://fontawesome.com/icons}
     *
     */
    export const defaultIconMaps: {
        [key in IconLibrary]: IconMapType;
    };
    export const KEY_NODATA = "__not_found__";
    /** Define which keys are handled by embedded <input> control, and should
     * *not* be passed to tree navigation handler in cell-edit mode.
     */
    export const INPUT_KEYS: {
        [key: string]: Array<string>;
    };
    /** Dict keys that are evaluated by source loader (others are added to `tree.data` instead). */
    export const RESERVED_TREE_SOURCE_KEYS: Set<string>;
    /** Map `KeyEvent.key` to navigation action. */
    export const KEY_TO_NAVIGATION_MAP: {
        [key: string]: NavigationType;
    };
    /** Map `KeyEvent.key` to navigation action. */
    export const KEY_TO_COMMAND_MAP: {
        [key: string]: ApplyCommandType;
    };
    /** Return a callback that returns true if the node title matches the string
     * or regular expression.
     * @see {@link WunderbaumNode.findAll}
     */
    export function makeNodeTitleMatcher(match: string | RegExp): MatcherCallback;
    /** Return a callback that returns true if the node title starts with a string (case-insensitive). */
    export function makeNodeTitleStartMatcher(s: string): MatcherCallback;
    /** Compare two nodes by title (case-insensitive).
     * @deprecated Use `key` option instead of `cmp` in sort methods.
     */
    export function nodeTitleSorter(a: WunderbaumNode, b: WunderbaumNode): number;
    /**
     * Decompresses the source data by
     * - converting from 'flat' to 'nested' format
     * - expanding short alias names to long names (if defined in _keyMap)
     * - resolving value indexes to value strings (if defined in _valueMap)
     *
     * @param source - The source object to be decompressed.
     * @returns void
     */
    export function decompressSourceData(source: SourceObjectType): void;
}
declare module "deferred" {
    /*!
     * Wunderbaum - deferred
     * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
     * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
     */
    type PromiseCallbackType = (val: any) => void;
    type finallyCallbackType = () => void;
    /**
     * Implement a ES6 Promise, that exposes a resolve() and reject() method.
     *
     * Loosely mimics {@link https://api.jquery.com/category/deferred-object/ | jQuery.Deferred}.
     * Example:
     * ```js
     * function foo() {
     *   let dfd = new Deferred(),
     *   ...
     *   dfd.resolve('foo')
     *   ...
     *   return dfd.promise();
     * }
     * ```
     */
    export class Deferred<T> {
        private _promise;
        protected _resolve: any;
        protected _reject: any;
        constructor();
        /** Resolve the Promise. */
        resolve(value?: any): void;
        /** Reject the Promise. */
        reject(reason?: any): void;
        /** Return the native Promise instance.*/
        promise(): Promise<T>;
        /** Call Promise.then on the embedded promise instance.*/
        then(cb: PromiseCallbackType): Promise<void>;
        /** Call Promise.catch on the embedded promise instance.*/
        catch(cb: PromiseCallbackType): Promise<void | T>;
        /** Call Promise.finally on the embedded promise instance.*/
        finally(cb: finallyCallbackType): Promise<T>;
    }
}
declare module "wb_node" {
    import { Wunderbaum } from "wunderbaum";
    import { AddChildrenOptions, ApplyCommandOptions, ApplyCommandType, ChangeType, CheckboxOption, ExpandAllOptions, IconOption, InsertNodeType, MakeVisibleOptions, MatcherCallback, NavigateOptions, NavigationType, NodeAnyCallback, NodeStatusType, NodeStringCallback, NodeToDictCallback, NodeVisitCallback, NodeVisitResponse, RenderOptions, ResetOrderOptions, ScrollIntoViewOptions, SetActiveOptions, SetExpandedOptions, SetSelectedOptions, SetStatusOptions, SortByPropertyOptions, SortCallback, SortOptions, SourceType, TooltipOption, TristateType, WbNodeData } from "types";
    /**
     * A single tree node.
     *
     * **NOTE:** <br>
     * Generally you should not modify properties directly, since this may break
     * the internal bookkeeping.
     */
    export class WunderbaumNode {
        static sequence: number;
        /** Reference to owning tree. */
        tree: Wunderbaum;
        /** Parent node (null for the invisible root node `tree.root`). */
        parent: WunderbaumNode;
        /** Name of the node.
         * @see Use {@link setTitle} to modify. */
        title: string;
        /** Unique key. Passed with constructor or defaults to `SEQUENCE`.
         * @see Use {@link setKey} to modify. */
        readonly key: string;
        /** Reference key. Unlike {@link key}, a `refKey` may occur multiple
         * times within a tree (in this case we have 'clone nodes').
         * @see Use {@link setKey} to modify.
         */
        readonly refKey: string | undefined;
        /**
         * Array of child nodes (null for leaf nodes).
         * For lazy nodes, this is `null` or ùndefined` until the children are loaded
         * and leaf nodes may be `[]` (empty array).
         * @see {@link hasChildren}, {@link addChildren}, {@link lazy}.
         */
        children: WunderbaumNode[] | null;
        /** Render a checkbox or radio button @see {@link selected}. */
        checkbox?: CheckboxOption;
        /** If true, this node's children are considerd radio buttons.
         * @see {@link isRadio}.
         */
        radiogroup?: boolean;
        /** If true, (in grid mode) no cells are rendered, except for the node title.*/
        colspan?: boolean;
        /** Icon definition. */
        icon?: IconOption;
        /** Lazy loading flag.
         * @see {@link isLazy}, {@link isLoaded}, {@link isUnloaded}.
         */
        lazy?: boolean;
        /** Expansion state.
         * @see {@link isExpandable}, {@link isExpanded}, {@link setExpanded}. */
        expanded?: boolean;
        /** Selection state.
         * @see {@link isSelected}, {@link setSelected}, {@link toggleSelected}. */
        selected?: boolean;
        unselectable?: boolean;
        /** Node type (used for styling).
         * @see {@link Wunderbaum.types}.
         */
        type?: string;
        /** Tooltip definition (`true`: use node's title). */
        tooltip?: TooltipOption;
        /** Icon tooltip definition (`true`: use node's title). */
        iconTooltip?: TooltipOption;
        /** Additional classes added to `div.wb-row`.
         * @see {@link hasClass}, {@link setClass}. */
        classes: Set<string> | null;
        /** Custom data that was passed to the constructor */
        data: any;
        statusNodeType?: NodeStatusType;
        _isLoading: boolean;
        _requestId: number;
        _errorInfo: any | null;
        _partsel: boolean;
        _partload: boolean;
        /**
         * > 0 if matched (-1 to keep system nodes visible);
         * Added and removed by filter code.
         */
        match?: number;
        subMatchCount?: number;
        /** @internal */
        titleWithHighlight?: string;
        _filterAutoExpanded?: boolean;
        _rowIdx: number | undefined;
        /** Position among non-status siblings, see `_render_aria()`. @internal */
        _ariaPosInSet?: number;
        /** Number of non-status children, see `_render_aria()`. @internal */
        _ariaSetSize?: number;
        /** Render pass of `_ariaSetSize`, see `_render_aria()`. @internal */
        _ariaSetPass?: number;
        _rowElem: HTMLDivElement | undefined;
        constructor(tree: Wunderbaum, parent: WunderbaumNode, data: WbNodeData);
        /**
         * Return readable string representation for this instance.
         * @internal
         */
        toString(): string;
        /**
         * Iterate all descendant nodes depth-first, pre-order using `for ... of ...` syntax.
         * More concise, but slightly slower than {@link WunderbaumNode.visit}.
         *
         * Example:
         * ```js
         * for(const n of node) {
         *   ...
         * }
         * ```
         */
        [Symbol.iterator](): IterableIterator<WunderbaumNode>;
        /** Call event handler if defined in tree.options.
         * Example:
         * ```js
         * node._callEvent("edit.beforeEdit", {foo: 42})
         * ```
         */
        _callEvent(type: string, extra?: any): any;
        /**
         * Append a 'paging' status node ("More...") after the loaded children,
         * replacing an existing one.
         *
         * Clicking it, or pressing Enter or Space on it, fires the `clickPaging`
         * event. The handler typically fetches the next page, removes the paging
         * node, calls `addChildren()` and adds a new paging node if more items
         * remain:
         * ```js
         * clickPaging: async (e) => {
         *   const parent = e.node.parent;
         *   const page = await fetchPage(parent.key, parent.children.length - 1);
         *   e.node.remove();
         *   parent.addChildren(page.items);
         *   if (page.more) {
         *     parent.addPagingNode();
         *   }
         * },
         * ```
         * If the handler returns a promise (e.g. an `async` function), the paging
         * node shows its loading state and ignores further clicks until it settles.
         * Paging nodes are status nodes, so they are not registered by key, and
         * `sort()` keeps them at the end.
         *
         * @param node title string or node data (`statusNodeType` is forced to 'paging')
         * @returns the new paging node
         */
        addPagingNode(node?: WbNodeData | string | null, options?: AddChildrenOptions): WunderbaumNode;
        /**
         * Fire the `clickPaging` event for a paging node (click, Enter, or Space).
         * If the handler returns a promise, the node shows its loading state and
         * ignores further clicks until the promise settles.
         * @internal
         */
        _clickPaging(event: Event): void;
        /**
         * Append (or insert) a list of child nodes.
         *
         * Tip: pass `{ before: 0 }` to prepend new nodes as first children.
         *
         * @returns first child added
         */
        addChildren(nodeData: WbNodeData | WbNodeData[], options?: AddChildrenOptions): WunderbaumNode;
        /**
         * Append or prepend a node, or append a child node.
         *
         * This a convenience function that calls addChildren()
         *
         * @param nodeData node definition
         * @param [mode=child] 'before', 'after', 'firstChild', or 'child' ('over' is a synonym for 'child')
         * @returns new node
         */
        addNode(nodeData: WbNodeData, mode?: InsertNodeType): WunderbaumNode;
        /**
         * Apply a modification (or navigation) operation.
         *
         * @see {@link Wunderbaum.applyCommand}
         */
        applyCommand(cmd: ApplyCommandType, options: ApplyCommandOptions): any;
        /**
         * Collapse all expanded sibling nodes if any.
         * (Automatically called when `autoCollapse` is true.)
         */
        collapseSiblings(options?: SetExpandedOptions): any;
        /**
         * Add/remove one or more classes to `<div class='wb-row'>`.
         *
         * This also maintains `node.classes`, so the class will survive a re-render.
         *
         * @param className one or more class names. Multiple classes can be passed
         *     as space-separated string, array of strings, or set of strings.
         */
        setClass(className: string | string[] | Set<string>, flag?: boolean): void;
        /** Start editing this node's title. */
        startEditTitle(): void;
        /**
         * Call `setExpanded()` on all descendant nodes.
         *
         * @param flag true to expand, false to collapse.
         * @param options Additional options.
         * @see {@link Wunderbaum.expandAll}
         * @see {@link WunderbaumNode.setExpanded}
         */
        expandAll(flag?: boolean, options?: ExpandAllOptions): Promise<void>;
        /**
         * Find all descendant nodes that match condition (excluding self).
         *
         * If `match` is a string, search for exact node title.
         * If `match` is a RegExp expression, apply it to node.title, using
         * [RegExp.test()](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/RegExp/test).
         * If `match` is a callback, match all nodes for that the callback(node) returns true.
         *
         * Returns an empty array if no nodes were found.
         *
         * Examples:
         * ```js
         * // Match all node titles that match exactly 'Joe':
         * nodeList = node.findAll("Joe")
         * // Match all node titles that start with 'Joe' case sensitive:
         * nodeList = node.findAll(/^Joe/)
         * // Match all node titles that contain 'oe', case insensitive:
         * nodeList = node.findAll(/oe/i)
         * // Match all nodes with `data.price` >= 99:
         * nodeList = node.findAll((n) => {
         *   return n.data.price >= 99;
         * })
         * ```
         */
        findAll(match: string | RegExp | MatcherCallback): WunderbaumNode[];
        /** Return the direct child with a given key, index or null. */
        findDirectChild(ptr: number | string | WunderbaumNode): WunderbaumNode | null;
        /**
         * Find first descendant node that matches condition (excluding self) or null.
         *
         * @see {@link WunderbaumNode.findAll} for examples.
         */
        findFirst(match: string | RegExp | MatcherCallback): WunderbaumNode | null;
        /** Find a node relative to self.
         *
         * @see {@link Wunderbaum.findRelatedNode|tree.findRelatedNode()}
         */
        findRelatedNode(where: NavigationType, includeHidden?: boolean): any;
        /**
         * Iterator version of {@link WunderbaumNode.format}.
         */
        format_iter(name_cb?: NodeStringCallback, connectors?: string[]): IterableIterator<string>;
        /**
         * Return a multiline string representation of a node/subnode hierarchy.
         * Mostly useful for debugging.
         *
         * Example:
         * ```js
         * console.info(tree.getActiveNode().format((n)=>n.title));
         * ```
         * logs
         * ```
         * Books
         *  ├─ Art of War
         *  ╰─ Don Quixote
         * ```
         * @see {@link WunderbaumNode.format_iter}
         */
        format(name_cb?: NodeStringCallback, connectors?: string[]): string;
        /** Return the `<span class='wb-col'>` element with a given index or id.
         * @returns {WunderbaumNode | null}
         */
        getColElem(colIdx: number | string): HTMLSpanElement;
        /**
         * Return all nodes with the same refKey.
         *
         * @param includeSelf Include this node itself.
         * @see {@link Wunderbaum.findByRefKey}
         */
        getCloneList(includeSelf?: boolean): WunderbaumNode[];
        /** Return the first child node or null.
         * @returns {WunderbaumNode | null}
         */
        getFirstChild(): WunderbaumNode;
        /** Return the last child node or null.
         * @returns {WunderbaumNode | null}
         */
        getLastChild(): WunderbaumNode;
        /** Return node depth (starting with 1 for top level nodes). */
        getLevel(): number;
        /** Return the successive node (under the same parent) or null. */
        getNextSibling(): WunderbaumNode | null;
        /** Return the parent node (null for the system root node). */
        getParent(): WunderbaumNode | null;
        /** Return an array of all parent nodes (top-down).
         * @param includeRoot Include the invisible system root node.
         * @param includeSelf Include the node itself.
         */
        getParentList(includeRoot?: boolean, includeSelf?: boolean): any[];
        /** Return a string representing the hierarchical node path, e.g. "a/b/c".
         * @param includeSelf
         * @param part property name or callback
         * @param separator
         */
        getPath(includeSelf?: boolean, part?: keyof WunderbaumNode | NodeAnyCallback, separator?: string): string;
        /** Return the preceding node (under the same parent) or null. */
        getPrevSibling(): WunderbaumNode | null;
        /** Return true if node has children.
         * Return undefined if not sure, i.e. the node is lazy and not yet loaded.
         */
        hasChildren(): boolean;
        /** Return true if node has className set. */
        hasClass(className: string): boolean;
        /** Return true if node is the currently focused node. @since 0.9.0 */
        hasFocus(): boolean;
        /** Return true if this node is the currently active tree node. */
        isActive(): boolean;
        /** Return true if this node is a direct or indirect parent of `other`.
         * @see {@link WunderbaumNode.isParentOf}
         */
        isAncestorOf(other: WunderbaumNode): boolean;
        /** Return true if this node is a **direct** subnode of `other`.
         * @see {@link WunderbaumNode.isDescendantOf}
         */
        isChildOf(other: WunderbaumNode): boolean;
        /** Return true if this node's refKey is used by at least one other node.
         */
        isClone(): boolean;
        /** Return true if this node's title spans all columns, i.e. the node has no
         * grid cells.
         */
        isColspan(): boolean;
        /** Return true if this node is a direct or indirect subnode of `other`.
         * @see {@link WunderbaumNode.isChildOf}
         */
        isDescendantOf(other: WunderbaumNode): boolean;
        /** Return true if this node has children, i.e. the node is generally expandable.
         * If `andCollapsed` is set, we also check if this node is collapsed, i.e.
         * an expand operation is currently possible.
         */
        isExpandable(andCollapsed?: boolean): boolean;
        /** Return true if _this_ node is currently in edit-title mode.
         *
         * See {@link WunderbaumNode.startEditTitle}.
         */
        isEditingTitle(): boolean;
        /** Return true if this node is currently expanded. */
        isExpanded(): boolean;
        /** Return true if this node is the first node of its parent's children. */
        isFirstSibling(): boolean;
        /** Return true if this node is the last node of its parent's children. */
        isLastSibling(): boolean;
        /** Return true if this node is lazy (even if data was already loaded) */
        isLazy(): boolean;
        /** Return true if node is lazy and loaded. For non-lazy nodes always return true. */
        isLoaded(): boolean;
        /** Return true if node is currently loading, i.e. a GET request is pending. */
        isLoading(): boolean;
        /** Return true if this node is a temporarily generated status node of type 'paging'. */
        isPagingNode(): boolean;
        /** Return true if this node is a **direct** parent of `other`.
         * @see {@link WunderbaumNode.isAncestorOf}
         */
        isParentOf(other: WunderbaumNode): boolean;
        /** Return true if this node is partially loaded. @experimental  */
        isPartload(): boolean;
        /** Return true if this node is partially selected (tri-state). */
        isPartsel(): boolean;
        /** Return true if this node has DOM representation, i.e. is displayed in the viewport. */
        isRadio(): boolean;
        /** Return true if this node has DOM representation, i.e. is displayed in the viewport. */
        isRendered(): boolean;
        /** Return true if this node is the (invisible) system root node.
         * @see {@link WunderbaumNode.isTopLevel}
         */
        isRootNode(): boolean;
        /** Return true if this node is selected, i.e. the checkbox is set.
         * `undefined` if partly selected (tri-state), false otherwise.
         */
        isSelected(): TristateType;
        /** Return true if this node is a temporarily generated system node like
         * 'loading', 'paging', or 'error' (node.statusNodeType contains the type).
         */
        isStatusNode(): boolean;
        /** Return true if this a top level node, i.e. a direct child of the (invisible) system root node. */
        isTopLevel(): boolean;
        /** Return true if node is marked lazy but not yet loaded.
         * For non-lazy nodes always return false.
         */
        isUnloaded(): boolean;
        /** Return true if all parent nodes are expanded. Note: this does not check
         * whether the node is scrolled into the visible part of the screen or viewport.
         */
        isVisible(): boolean;
        protected _loadSourceObject(source: any, level?: number): void;
        _fetchWithOptions(source: any): Promise<any>;
        /** Download  data from the cloud, then call `.update()`. */
        load(source: SourceType): Promise<void>;
        /**
         * Load content of a lazy node.
         * If the node is already loaded, nothing happens.
         * @param [forceReload=false] If true, reload even if already loaded.
         */
        loadLazy(forceReload?: boolean): Promise<void>;
        /** Write to `console.log` with node name as prefix if opts.debugLevel >= 4.
         * @see {@link WunderbaumNode.logDebug}
         */
        log(...args: any[]): void;
        /** Write to `console.debug` with node name as prefix if opts.debugLevel >= 4
         * and browser console level includes debug/verbose messages.
         * @see {@link WunderbaumNode.log}
         */
        logDebug(...args: any[]): void;
        /** Write to `console.error` with node name as prefix if opts.debugLevel >= 1. */
        logError(...args: any[]): void;
        /** Write to `console.info` with node name as prefix if opts.debugLevel >= 3. */
        logInfo(...args: any[]): void;
        /** Write to `console.warn` with node name as prefix if opts.debugLevel >= 2. */
        logWarn(...args: any[]): void;
        /** Expand all parents and optionally scroll into visible area as neccessary.
         * Promise is resolved, when lazy loading and animations are done.
         * @param {object} [options] passed to `setExpanded()`.
         *     Defaults to {noAnimation: false, noEvents: false, scrollIntoView: true}
         */
        makeVisible(options?: MakeVisibleOptions): Promise<unknown>;
        /** Move this node to targetNode. */
        moveTo(targetNode: WunderbaumNode, mode?: InsertNodeType, map?: NodeAnyCallback): void;
        /** Set focus relative to this node and optionally activate.
         *
         * 'left' collapses the node if it is expanded, or move to the parent
         * otherwise.
         * 'right' expands the node if it is collapsed, or move to the first
         * child otherwise.
         *
         * @param where 'down', 'first', 'last', 'left', 'parent', 'right', or 'up'.
         *   (Alternatively the `event.key` that would normally trigger this move,
         *   e.g. `ArrowLeft` = 'left'.
         * @param options
         */
        navigate(where: NavigationType | string, options?: NavigateOptions): Promise<any>;
        /** Delete this node and all descendants. */
        remove(): void;
        /** Remove all descendants of this node. */
        removeChildren(): void;
        /** Remove all HTML markup from the DOM. */
        removeMarkup(): void;
        protected _getRenderInfo(): any;
        protected _createIcon(parentElem: HTMLElement, replaceChild: HTMLElement | null, showLoading: boolean): HTMLElement | null;
        /**
         * Create a whole new `<div class="wb-row">` element.
         * @see {@link WunderbaumNode._render}
         */
        protected _render_markup(opts: RenderOptions): void;
        /**
         * Render `node.title`, `.icon` into an existing row.
         *
         * @see {@link WunderbaumNode._render}
         */
        protected _render_data(opts: RenderOptions): void;
        /**
         * Update the ARIA tree grid state of a rendered row: level, position in the
         * sibling set, expansion, selection, busy state and virtual row index.
         * @internal
         */
        protected _render_aria(rowDiv: HTMLElement): void;
        /**
         * Update row classes to reflect active, focuses, etc.
         * @see {@link WunderbaumNode._render}
         */
        protected _render_status(opts: RenderOptions): void;
        _render(options?: RenderOptions): void;
        /**
         * Remove all children, collapse, and set the lazy-flag, so that the lazyLoad
         * event is triggered on next expand.
         */
        resetLazy(): void;
        /** Convert node (or whole branch) into a plain object.
         *
         * The result is compatible with node.addChildren().
         *
         * @param recursive include child nodes
         * @param callback is called for every node, in order to allow
         *     modifications.
         *     Return `false` to ignore this node or `"skip"` to include this node
         *     without its children.
         * @see {@link Wunderbaum.toDictArray}.
         */
        toDict(recursive?: boolean, callback?: NodeToDictCallback): WbNodeData;
        /** Return an option value that has a default, but may be overridden by a
         * callback or a node instance attribute.
         *
         * Evaluation sequence:
         *
         * - If `tree.options.<name>` is a callback that returns something, use that.
         * - Else if `node.<name>` is defined, use that.
         * - Else if `tree.types[<node.type>]` is a value, use that.
         * - Else if `tree.options.<name>` is a value, use that.
         * - Else use `defaultValue`.
         *
         * @param name name of the option property (on node and tree)
         * @param defaultValue return this if nothing else matched
         * {@link Wunderbaum.getOption|Wunderbaum.getOption}
         */
        getOption(name: string, defaultValue?: any): any;
        /** Make sure that this node is visible in the viewport.
         * @see {@link Wunderbaum.scrollTo|Wunderbaum.scrollTo}
         */
        scrollIntoView(options?: ScrollIntoViewOptions): Promise<void>;
        /**
         * Activate this node, deactivate previous, send events, activate column and
         * scroll into viewport.
         */
        setActive(flag?: boolean, options?: SetActiveOptions): Promise<void>;
        /**
         * Expand or collapse this node.
         */
        setExpanded(flag?: boolean, options?: SetExpandedOptions): Promise<void>;
        /**
         * Set keyboard focus here.
         * @see {@link setActive}
         */
        setFocus(flag?: boolean): void;
        /** Set a new icon path or class. */
        setIcon(icon: string): void;
        /** Change node's {@link key} and/or {@link refKey}.  */
        setKey(key: string | null, refKey: string | null): void;
        /**
         * Trigger a repaint, typically after a status or data change.
         *
         * `change` defaults to 'data', which handles modifcations of title, icon,
         * and column content. It can be reduced to 'ChangeType.status' if only
         * active/focus/selected state has changed.
         *
         * This method will eventually call  {@link WunderbaumNode._render} with
         * default options, but may be more consistent with the tree's
         * {@link Wunderbaum.update} API.
         */
        update(change?: ChangeType): void;
        /**
         * Return an array of selected nodes.
         * @param stopOnParents only return the topmost selected node (useful with selectMode 'hier')
         */
        getSelectedNodes(stopOnParents?: boolean): WunderbaumNode[];
        /**
         * Return an array of refKey values.
         *
         * RefKeys are unique identifiers for a node data, and are used to identify
         * clones.
         * If more than one node has the same refKey, it is only returned once.
         * @param selected if true, only return refKeys of selected nodes.
         */
        getRefKeys(selected?: boolean): string[];
        /** Toggle the check/uncheck state. */
        toggleSelected(options?: SetSelectedOptions): TristateType;
        /** Return true if at least on selectable descendant end-node is unselected. @internal */
        _anySelectable(): boolean;
        protected _changeSelectStatusProps(state: TristateType): boolean;
        /**
         * Fix selection status, after this node was (de)selected in `selectMode: 'hier'`.
         * This includes (de)selecting all descendants.
         */
        fixSelection3AfterClick(opts?: SetSelectedOptions): void;
        /**
         * Fix selection status for multi-hier mode.
         * Only end-nodes are considered to update the descendants branch and parents.
         * Should be called after this node has loaded new children or after
         * children have been modified using the API.
         */
        fixSelection3FromEndNodes(opts?: SetSelectedOptions): void;
        /** Modify the check/uncheck state. */
        setSelected(flag?: boolean, options?: SetSelectedOptions): TristateType;
        /** Display node status (ok, loading, error, noData) using styles and a dummy child node. */
        setStatus(status: NodeStatusType, options?: SetStatusOptions): WunderbaumNode | null;
        /** Rename this node. */
        setTitle(title: string): void;
        /** Set the node tooltip. */
        setTooltip(tooltip: TooltipOption): void;
        /**
         * Sort child list by title or custom criteria.
         * @param {function} cmp custom compare function(a, b) that returns -1, 0, or 1
         *    (defaults to sorting by title).
         * @param {boolean} deep pass true to sort all descendant nodes recursively
         * @deprecated use {@link sort}
         */
        sortChildren(cmp?: SortCallback | null, deep?: boolean): void;
        /**
         * Renumber nodes `_nativeIndex`. This is useful to allow to restore the
         * order after sorting a column.
         * This method is automatically called after loading new child nodes.
         * @since 0.11.0
         */
        resetNativeChildOrder(options?: ResetOrderOptions): void;
        /**
         * Convenience method to implement column sorting.
         * @since 0.11.0
         * @deprecated use {@link sort}
         */
        sortByProperty(options: SortByPropertyOptions): void;
        /**
         * Implement column sorting.
         * @since 0.14.0
         */
        sort(options: SortOptions): void;
        /**
         * Re-apply current sorting if any (use after lazy load).
         * Example:
         * ```js
         * load: function (e) {
         *   // Whe loading a lazy branch, apply current sort order if any
         *   e.node.resort();
         * },
         * ```
         * @since 0.14.0
         */
        resort(options?: SortOptions): void;
        /**
         * Trigger `modifyChild` event on a parent to signal that a child was modified.
         * @param {string} operation Type of change: 'add', 'remove', 'rename', 'move', 'data', ...
         */
        triggerModifyChild(operation: string, child: WunderbaumNode | null, extra?: any): void;
        /**
         * Trigger `modifyChild` event on node.parent(!).
         * @param {string} operation Type of change: 'add', 'remove', 'rename', 'move', 'data', ...
         * @param {object} [extra]
         */
        triggerModify(operation: string, extra?: any): void;
        /**
         * Call `callback(node)` for all descendant nodes in hierarchical order (depth-first, pre-order).
         *
         * Stop iteration, if fn() returns false. Skip current branch, if fn()
         * returns "skip".<br>
         * Return false if iteration was stopped.
         *
         * @param {function} callback the callback function.
         *     Return false to stop iteration, return "skip" to skip this node and
         *     its children only.
         * @see `wb_node.WunderbaumNode.IterableIterator<WunderbaumNode>`
         * @see {@link Wunderbaum.visit}.
         */
        visit(callback: NodeVisitCallback, includeSelf?: boolean): NodeVisitResponse;
        /** Call fn(node) for all parent nodes, bottom-up, including invisible system root.<br>
         * Stop iteration, if callback() returns false.<br>
         * Return false if iteration was stopped.
         *
         * @param callback the callback function. Return false to stop iteration
         */
        visitParents(callback: (node: WunderbaumNode) => boolean | void, includeSelf?: boolean): boolean;
        /**
         * Call fn(node) for all sibling nodes.<br>
         * Stop iteration, if fn() returns false.<br>
         * Return false if iteration was stopped.
         *
         * @param callback the callback function.
         *     Return false to stop iteration.
         * @param includeSelf include this node in the iteration.
         */
        visitSiblings(callback: (node: WunderbaumNode) => boolean | void, includeSelf?: boolean): boolean;
        /**
         * [ext-filter] Return true if this node is matched by current filter (or no filter is active).
         */
        isMatched(): boolean;
    }
}
declare module "wb_options" {
    /*!
     * Wunderbaum - options
     * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
     * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
     */
    import { ColumnDefinitionList, DndOptionsType, DynamicBoolOption, DynamicBoolOrStringOption, DynamicCheckboxOption, DynamicIconOption, EditOptionsType, FilterOptionsType, IconMapType, NavModeEnum, NodeTypeDefinitionMap, SelectModeType, SourceType, TranslationsType, WbActivateEventType, WbButtonClickEventType, WbCancelableEventResultType, WbChangeEventType, WbClickEventType, WbDeactivateEventType, WbErrorEventType, WbExpandEventType, WbIconBadgeCallback, WbIconBadgeEventResultType, WbInitEventType, WbKeydownEventType, WbNodeEventType, WbReceiveEventType, WbRenderEventType, WbSelectEventType, WbTreeEventType } from "types";
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
        /**
         * @default "multi"
         */
        selectMode: SelectModeType;
        /**
         * @default true
         */
        quicksearch: boolean;
        /**
         * Scroll Node into view on Expand Click
         * @default true
         */
        scrollIntoViewOnExpandClick: boolean;
        /** Configuration options for the drag-and-drop extension. */
        dnd: DndOptionsType;
        /** Configuration options for the edit-title extension. */
        edit: EditOptionsType;
        /** Configuration options for the node-filter extension. */
        filter: FilterOptionsType;
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
}
declare module "types" {
    /*!
     * Wunderbaum - types
     * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
     * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
     */
    import { WunderbaumNode } from "wb_node";
    import { Wunderbaum } from "wunderbaum";
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
        _keyMap?: {
            [key: string]: string;
        };
        _positional?: Array<string>;
        _valueMap?: {
            [key: string]: Array<string>;
        };
    }
    /** Possible initilization for tree nodes. */
    export type SourceType = string | SourceListType | SourceAjaxType | SourceObjectType;
    /** Passed to `find...()` methods. Should return true if node matches. */
    export type MatcherCallback = (node: WunderbaumNode) => boolean;
    /** Used for `tree.iconBadge` event. */
    export type WbIconBadgeCallback = (e: WbIconBadgeEventType) => WbIconBadgeEventResultType;
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
    export type BoolOrStringOptionResolver = (node: WunderbaumNode) => boolean | string;
    /** A callback that receives a node instance and returns an arbitrary value type. */
    export type NodeAnyCallback = (node: WunderbaumNode) => any;
    /** A callback that receives a node instance and returns a string value. */
    export type NodeStringCallback = (node: WunderbaumNode) => string;
    /** A callback that receives a node instance and property name returns a value. */
    export type NodePropertyGetterCallback = (node: WunderbaumNode, propName: string) => any;
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
    export type NodeToDictCallback = (dict: WbNodeData, node: WunderbaumNode) => NodeVisitResponse;
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
    export type DynamicBoolOrStringOption = boolean | string | BoolOrStringOptionResolver;
    export type DynamicCheckboxOption = CheckboxOption | BoolOrStringOptionResolver;
    export type DynamicIconOption = IconOption | BoolOrStringOptionResolver;
    export type DynamicTooltipOption = TooltipOption | BoolOrStringOptionResolver;
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
        [key: string]: unknown;
    }
    export type NodeTypeDefinitionMap = {
        [type: string]: NodeTypeDefinition;
    };
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
        /** Optional HTML content that is rendered into all `span.wb-col` elements of that column.*/
        html?: string;
        /** @internal */
        _weight?: number;
        /** @internal */
        _widthPx?: number;
        /** @internal */
        _ofsPx?: number;
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
    export type ColumnEventInfoMap = {
        [colId: string]: ColumnEventInfo;
    };
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
    export type FilterModeType = null | "mark" | "dim" | "hide";
    export type SelectModeType = "single" | "multi" | "hier";
    export type NavigationType = "down" | "first" | "firstCol" | "last" | "lastCol" | "left" | "nextMatch" | "pageDown" | "pageUp" | "parent" | "prevMatch" | "right" | "up";
    export type ApplyCommandType = NavigationType | "addChild" | "addSibling" | "collapse" | "collapseAll" | "copy" | "cut" | "edit" | "expand" | "expandAll" | "indent" | "moveDown" | "moveUp" | "outdent" | "paste" | "remove" | "rename" | "toggleSelect";
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
        scroll = "scroll"
    }
    /** @internal */
    export enum RenderFlag {
        clearMarkup = "clearMarkup",
        header = "header",
        redraw = "redraw",
        scroll = "scroll"
    }
    /** Possible values for {@link WunderbaumNode.setStatus}. */
    export enum NodeStatusType {
        ok = "ok",
        loading = "loading",
        error = "error",
        noData = "noData",
        paging = "paging"
    }
    /** Define the subregion of a node, where an event occurred. */
    export enum NodeRegion {
        unknown = "",
        checkbox = "checkbox",
        column = "column",
        expander = "expander",
        icon = "icon",
        prefix = "prefix",
        title = "title"
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
        row = "row"
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
    }
    /** Possible values for {@link WunderbaumNode.setSelected} `options` argument. */
    export interface SetSelectedOptions {
        /** Ignore restrictions, e.g. (`unselectable`). @default false */
        force?: boolean;
        /** Do not send `beforeSelect` or `select` events. @default false */
        noEvents?: boolean;
        /** Apply to all descendant nodes (only for `selectMode: 'multi'`). @default false */
        propagateDown?: boolean;
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
        /**
         * `beforeEdit(e)` may return an input HTML string. Otherwise use a default.
         * @category Callback
         */
        beforeEdit?: null | ((e: WbNodeEventType) => boolean) | string;
        /**
         *
         * @category Callback
         */
        edit?: null | ((e: WbNodeEventType & {
            inputElem: HTMLInputElement;
        }) => void);
        /**
         *
         * @category Callback
         */
        apply?: null | ((e: WbNodeEventType & {
            inputElem: HTMLInputElement;
        }) => any) | Promise<any>;
    };
    export type InsertNodeType = "before" | "after" | "prependChild" | "appendChild";
    export type DropEffectType = "none" | "copy" | "link" | "move";
    export type DropEffectAllowedType = "none" | "copy" | "copyLink" | "copyMove" | "link" | "linkMove" | "move" | "all";
    export type DropRegionType = "over" | "before" | "after";
    export type DropRegionTypeSet = Set<DropRegionType>;
    export type DropRegionTypeList = Array<DropRegionType>;
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
        serializeClipboardData?: boolean | ((nodeData: WbNodeData, node: WunderbaumNode) => string);
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
        /**
         * Pixel per event
         * @default 5
         */
        scrollSpeed?: 5;
        /**
         * Optional callback passed to `toDict` on dragStart
         * @default null
         * @category Callback
         */
        sourceCopyHook?: null;
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
        /**
         * Callback(targetNode, data), return true, to enable dnd drop
         * @default null
         * @category Callback
         */
        dragEnter?: null | ((e: DropEventType) => DropRegionType | DropRegionTypeSet | DropRegionTypeList | boolean);
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
        drop?: null | ((e: WbNodeEventType & {
            event: DragEvent;
            region: DropRegionType;
            suggestedDropMode: InsertNodeType;
            suggestedDropEffect: DropEffectType;
            sourceNode: WunderbaumNode;
            sourceNodeData: WbNodeData | null;
        }) => void);
        /**
         * Callback(targetNode, data)
         * @default null
         * @category Callback
         */
        dragLeave?: null | ((e: DropEventType) => void);
    };
    export type GridOptionsType = object;
    export type KeynavOptionsType = object;
    export type LoggerOptionsType = object;
}
declare module "wb_ext_dnd" {
    import { Wunderbaum } from "wunderbaum";
    import { WunderbaumExtension } from "wb_extension_base";
    import { WunderbaumNode } from "wb_node";
    import { DndOptionsType, DropEffectType, DropRegionType, DropRegionTypeSet } from "types";
    import { DebouncedFunction } from "debounce";
    export class DndExtension extends WunderbaumExtension<DndOptionsType> {
        protected srcNode: WunderbaumNode | null;
        protected lastTargetNode: WunderbaumNode | null;
        protected lastEnterStamp: number;
        protected lastAllowedDropRegions: DropRegionTypeSet | null;
        protected lastDropEffect: DropEffectType | null;
        protected lastDropRegion: DropRegionType | false;
        protected currentScrollDir: number;
        protected applyScrollDirThrottled: DebouncedFunction<() => void>;
        constructor(tree: Wunderbaum);
        init(): void;
        /** Cleanup classes after target node is no longer hovered. */
        protected _leaveNode(): void;
        /** */
        protected unifyDragover(res: any): DropRegionTypeSet | false;
        /**
         * Calculates the drop region based on the drag event and the allowed drop regions.
         */
        protected _calcDropRegion(e: DragEvent, allowed: DropRegionTypeSet | null): DropRegionType | false;
        /**
         * Guess drop effect (copy/link/move) using opinionated conventions.
         *
         * Default: dnd.dropEffectDefault
         */
        protected _guessDropEffect(e: DragEvent): DropEffectType;
        /** Don't allow void operation ('drop on self').*/
        protected _isVoidDrop(targetNode: WunderbaumNode, srcNode: WunderbaumNode | null, dropRegion: DropRegionType | false): boolean;
        protected _applyScrollDir(): void;
        protected _autoScroll(viewportY: number): number;
        /** Return true if a drag operation currently in progress. */
        isDragging(): boolean;
        /**
         * Handle dragstart, drag and dragend events for the source node.
         */
        protected onDragEvent(e: DragEvent): boolean;
        /**
         * Handle dragenter, dragover, dragleave, drop events.
         */
        protected onDropEvent(e: DragEvent): boolean;
    }
}
declare module "wb_ext_edit" {
    /*!
     * Wunderbaum - ext-edit
     * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
     * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
     */
    import { Wunderbaum } from "wunderbaum";
    import { WunderbaumExtension } from "wb_extension_base";
    import { WunderbaumNode } from "wb_node";
    import { EditOptionsType, InsertNodeType, WbNodeData } from "types";
    export class EditExtension extends WunderbaumExtension<EditOptionsType> {
        protected debouncedOnChange: (e: Event) => void;
        protected curEditNode: WunderbaumNode | null;
        protected relatedNode: WunderbaumNode | null;
        constructor(tree: Wunderbaum);
        protected _applyChange(eventName: string, node: WunderbaumNode, colElem: HTMLElement, inputElem: HTMLInputElement, extra: any): Promise<any>;
        protected _onChange(e: Event): void;
        init(): void;
        _preprocessKeyEvent(data: any): boolean | undefined;
        /** Return true if a title is currently being edited. */
        isEditingTitle(node?: WunderbaumNode): boolean;
        /** Start renaming, i.e. replace the title with an embedded `<input>`. */
        startEditTitle(node?: WunderbaumNode | null): void;
        /**
         *
         * @param apply
         * @returns
         */
        stopEditTitle(apply: boolean): void;
        _stopEditTitle(apply: boolean, options: any): void;
        /**
         * Create a new child or sibling node and start edit mode.
         */
        createNode(mode?: InsertNodeType, node?: WunderbaumNode | null, init?: string | WbNodeData): void;
    }
}
declare module "drag_observer" {
    /*!
     * Wunderbaum - drag_observer
     * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
     * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
     */
    export type DragCallbackArgType = {
        /** "dragstart", "drag", or "dragstop". */
        type: string;
        /** Original mousedown or touch event that triggered the dragstart event. */
        startEvent: MouseEvent | TouchEvent;
        /** Original mouse or touch event that triggered the current drag event.
         * Note that this is not the same as `startEvent`, but a mousemove in case of
         * a dragstart threshold.
         */
        event: MouseEvent | TouchEvent;
        /** Custom data that was passed to the DragObserver, typically on dragstart. */
        customData: any;
        /** Element which is currently dragged. */
        dragElem: HTMLElement | null;
        /** Relative horizontal drag distance since start. */
        dx: number;
        /** Relative vertical drag distance since start. */
        dy: number;
        /** False if drag was canceled. */
        apply?: boolean;
    };
    export type DragCallbackType = (e: DragCallbackArgType) => boolean | void;
    type DragObserverOptionsType = {
        /**Event target (typically `window.document`). */
        root: EventTarget;
        /**Event delegation selector.*/
        selector?: string;
        /**Minimum drag distance in px. */
        thresh?: number;
        /**Return `false` to cancel drag. */
        dragstart: DragCallbackType;
        drag?: DragCallbackType;
        dragstop?: DragCallbackType;
    };
    /**
     * Convert mouse- and touch events to 'dragstart', 'drag', and 'dragstop'.
     */
    export class DragObserver {
        protected _handler: any;
        protected root: EventTarget;
        protected start: {
            event: MouseEvent | TouchEvent | null;
            x: number;
            y: number;
            altKey: boolean;
            ctrlKey: boolean;
            metaKey: boolean;
            shiftKey: boolean;
        };
        protected dragElem: HTMLElement | null;
        protected dragging: boolean;
        protected customData: object;
        protected events: string[];
        protected opts: DragObserverOptionsType;
        constructor(opts: DragObserverOptionsType);
        /** Unregister all event listeners. */
        disconnect(): void;
        getDragElem(): HTMLElement | null;
        isDragging(): boolean;
        stopDrag(cb_event?: DragCallbackArgType): void;
        protected handleEvent(e: MouseEvent): boolean | void;
    }
}
declare module "wb_ext_grid" {
    /*!
     * Wunderbaum - ext-grid
     * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
     * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
     */
    import { Wunderbaum } from "wunderbaum";
    import { WunderbaumExtension } from "wb_extension_base";
    import { DragCallbackArgType, DragObserver } from "drag_observer";
    import { GridOptionsType } from "types";
    export class GridExtension extends WunderbaumExtension<GridOptionsType> {
        protected observer: DragObserver;
        constructor(tree: Wunderbaum);
        init(): void;
        /**
         * Handles drag and sragstop events for column resizing.
         */
        protected handleDrag(e: DragCallbackArgType): void;
    }
}
declare module "wb_ext_keynav" {
    /*!
     * Wunderbaum - ext-keynav
     * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
     * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
     */
    import { KeynavOptionsType } from "types";
    import { Wunderbaum } from "wunderbaum";
    import { WunderbaumExtension } from "wb_extension_base";
    export class KeynavExtension extends WunderbaumExtension<KeynavOptionsType> {
        constructor(tree: Wunderbaum);
        protected _getEmbeddedInputElem(elem: any): HTMLInputElement | null;
        protected _isCurInputFocused(): boolean;
        onKeyEvent(data: any): boolean | undefined;
    }
}
declare module "wb_ext_logger" {
    /*!
     * Wunderbaum - ext-logger
     * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
     * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
     */
    import { LoggerOptionsType } from "types";
    import { WunderbaumExtension } from "wb_extension_base";
    import { Wunderbaum } from "wunderbaum";
    export class LoggerExtension extends WunderbaumExtension<LoggerOptionsType> {
        readonly prefix: string;
        protected ignoreEvents: Set<string>;
        constructor(tree: Wunderbaum);
        init(): void;
        onKeyEvent(data: any): boolean | undefined;
    }
}
declare module "wb_extension_base" {
    import { DndExtension } from "wb_ext_dnd";
    import { EditExtension } from "wb_ext_edit";
    import { FilterExtension } from "wb_ext_filter";
    import { GridExtension } from "wb_ext_grid";
    import { KeynavExtension } from "wb_ext_keynav";
    import { LoggerExtension } from "wb_ext_logger";
    import { WunderbaumOptions } from "wb_options";
    import { Wunderbaum } from "wunderbaum";
    export type ExtensionsDict = {
        dnd: DndExtension;
        edit: EditExtension;
        filter: FilterExtension;
        grid: GridExtension;
        keynav: KeynavExtension;
        logger: LoggerExtension;
        [key: string]: WunderbaumExtension<any>;
    };
    export abstract class WunderbaumExtension<TOptions> {
        enabled: boolean;
        readonly id: string;
        readonly tree: Wunderbaum;
        readonly treeOpts: WunderbaumOptions;
        readonly extensionOpts: any;
        constructor(tree: Wunderbaum, id: string, defaults: TOptions);
        /** Called on tree (re)init after all extensions are added, but before loading.*/
        init(): void;
        getPluginOption(name: string, defaultValue?: any): any;
        setPluginOption(name: string, value: any): void;
        setEnabled(flag?: boolean): void;
        onKeyEvent(data: any): boolean | undefined;
        onRender(data: any): boolean | undefined;
    }
}
declare module "wb_ext_filter" {
    import { FilterNodesOptions, FilterOptionsType, NodeFilterCallback } from "types";
    import { Wunderbaum } from "wunderbaum";
    import { WunderbaumExtension } from "wb_extension_base";
    export class FilterExtension extends WunderbaumExtension<FilterOptionsType> {
        queryInput: HTMLInputElement | null;
        prevButton: HTMLElement | HTMLAnchorElement | null;
        nextButton: HTMLElement | HTMLAnchorElement | null;
        modeButton: HTMLButtonElement | null;
        matchInfoElem: HTMLElement | null;
        lastFilterArgs: IArguments | null;
        constructor(tree: Wunderbaum);
        init(): void;
        setPluginOption(name: string, value: any): void;
        _updatedConnectedControls(): void;
        _connectControls(): void;
        _applyFilterNoUpdate(filter: string | RegExp | NodeFilterCallback, _opts: FilterNodesOptions): number;
        _applyFilterImpl(filter: string | RegExp | NodeFilterCallback, _opts: FilterNodesOptions): number;
        /**
         * [ext-filter] Dim or hide nodes.
         */
        filterNodes(filter: string | RegExp | NodeFilterCallback, options: FilterNodesOptions): number;
        /**
         * [ext-filter] Dim or hide whole branches.
         * @deprecated Use {@link filterNodes} instead and set `options.matchBranch: true`.
         */
        filterBranches(filter: string | NodeFilterCallback, options: FilterNodesOptions): number;
        /**
         * [ext-filter] Return the number of matched nodes.
         */
        countMatches(): number;
        /**
         * [ext-filter] Re-apply current filter.
         */
        updateFilter(): void;
        /**
         * [ext-filter] Reset the filter.
         */
        clearFilter(): void;
    }
}
declare module "wunderbaum" {
    /*!
     * wunderbaum.ts
     *
     * A treegrid control.
     *
     * Copyright (c) 2021-2025, Martin Wendt (https://wwWendt.de).
     * https://github.com/mar10/wunderbaum
     *
     * Released under the MIT license.
     * @version @VERSION
     * @date @DATE
     */
    import * as util from "util";
    import { ExtensionsDict, WunderbaumExtension } from "wb_extension_base";
    import { AddChildrenOptions, ApplyCommandOptions, ApplyCommandType, ChangeType, ColumnDefinitionList, ExpandAllOptions, FilterModeType, FilterNodesOptions, IconMapType, GetStateOptions, MatcherCallback, NavigationType, NavModeEnum, NodeFilterCallback, NodeStatusType, NodeStringCallback, NodeToDictCallback, NodeTypeDefinitionMap, NodeVisitCallback, RenderFlag, ScrollToOptions, SetActiveOptions, SetColumnOptions, SetStateOptions, SetStatusOptions, SortCallback, SourceType, TreeStateDefinition, UpdateOptions, VisitRowsOptions, WbEventInfo, WbNodeData, SortOptions, DeprecationOptions, SortByPropertyOptions, ReloadOptions, LoadLazyNodesOptions } from "types";
    import { WunderbaumNode } from "wb_node";
    import { InitWunderbaumOptions, WunderbaumOptions } from "wb_options";
    import { DebouncedFunction } from "debounce";
    /**
     * A persistent plain object or array.
     *
     * See also {@link WunderbaumOptions}.
     */
    export class Wunderbaum {
        protected static sequence: number;
        protected enabled: boolean;
        /** Wunderbaum release version number "MAJOR.MINOR.PATCH". */
        static version: string;
        /** The invisible root node, that holds all visible top level nodes. */
        readonly root: WunderbaumNode;
        /** Unique tree ID as passed to constructor. Defaults to `"wb_SEQUENCE"`. */
        readonly id: string;
        /** The `div` container element that was passed to the constructor. */
        readonly element: HTMLDivElement;
        /** The `div.wb-header` element if any. */
        readonly headerElement: HTMLDivElement;
        /** The `div.wb-list-container` element that contains the `nodeListElement`. */
        readonly listContainerElement: HTMLDivElement;
        /** The `div.wb-node-list` element that contains all visible div.wb-row child elements. */
        readonly nodeListElement: HTMLDivElement;
        /** Contains additional data that was sent as response to an Ajax source load request. */
        readonly data: {
            [key: string]: any;
        };
        protected readonly _updateViewportThrottled: DebouncedFunction<() => void>;
        protected extensionList: WunderbaumExtension<any>[];
        protected extensions: ExtensionsDict;
        /** Merged options from constructor args and tree- and extension defaults. */
        options: WunderbaumOptions;
        protected keyMap: Map<string, WunderbaumNode>;
        protected refKeyMap: Map<string, Set<WunderbaumNode>>;
        protected treeRowCount: number;
        /** Number of visible header rows (0 or 1), used for `aria-rowindex`. @internal */
        _ariaHeaderRows: number;
        /** Incremented for every `_updateRows()` pass (caches ARIA set sizes). @internal */
        _ariaRenderPass: number;
        protected _disableUpdateCount: number;
        protected _disableUpdateIgnoreCount: number;
        protected _activeNode: WunderbaumNode | null;
        protected _focusNode: WunderbaumNode | null;
        protected _initialSource: SourceType | null;
        /** Currently active node if any.
         * Use {@link WunderbaumNode.setActive|setActive} to modify.
         */
        get activeNode(): WunderbaumNode;
        /** Current node hat has keyboard focus if any.
         * Use {@link WunderbaumNode.setFocus|setFocus()} to modify.
         */
        get focusNode(): WunderbaumNode;
        /** Shared properties, referenced by `node.type`. */
        types: NodeTypeDefinitionMap;
        /** List of column definitions. */
        columns: ColumnDefinitionList;
        protected _columnsById: {
            [key: string]: any;
        };
        protected resizeObserver: ResizeObserver;
        protected pendingChangeTypes: Set<RenderFlag>;
        /** A Promise that is resolved when the tree was initialized (similar to `init(e)` event). */
        readonly ready: Promise<any>;
        /** Expose some useful methods of the util.ts module as `Wunderbaum.util`. */
        static util: typeof util;
        /** A map of default iconMaps.
         * May be used as default, when passing partial icon definition maps:
         * ```js
         * const tree = new mar10.Wunderbaum({
         *   ...
         *   iconMap: Object.assign(Wunderbaum.iconMaps.bootstrap, {
         *     folder: "bi bi-archive",
         *   }),
         * });
         * ```
         */
        static iconMaps: {
            bootstrap: IconMapType;
            fontawesome6: IconMapType;
        };
        /** Expose some useful methods of the util.ts module as `tree._util`. */
        _util: typeof util;
        /** Filter options (used as defaults for calls to {@link Wunderbaum.filterNodes} ) */
        breadcrumb: HTMLElement | null;
        /** Filter options (used as defaults for calls to {@link Wunderbaum.filterNodes} ) */
        filterMode: FilterModeType;
        /** @internal Use `setColumn()`/`getActiveColElem()` to access. */
        activeColIdx: number;
        /** @internal */
        _cellNavMode: boolean;
        /** @internal */
        lastQuicksearchTime: number;
        /** @internal */
        lastQuicksearchTerm: string;
        protected lastClickTime: number;
        constructor(options: InitWunderbaumOptions);
        private _registerEventHandlers;
        /**
         * Return a Wunderbaum instance, from element, id, index, or event.
         *
         * ```js
         * getTree();         // Get first Wunderbaum instance on page
         * getTree(1);        // Get second Wunderbaum instance on page
         * getTree(event);    // Get tree for this mouse- or keyboard event
         * getTree("foo");    // Get tree for this `tree.options.id`
         * getTree("#tree");  // Get tree for first matching element selector
         * ```
         */
        static getTree(el?: Element | Event | number | string | WunderbaumNode): Wunderbaum | null;
        /**
         * Return the icon-function -> icon-definition mapping.
         * @deprecated Use {@link Wunderbaum.iconMaps}
         */
        get iconMap(): IconMapType;
        /**
         * Return a WunderbaumNode instance from element or event.
         */
        static getNode(el: Element | Event): WunderbaumNode | null;
        /**
         * Iterate all descendant nodes depth-first, pre-order using `for ... of ...` syntax.
         * More concise, but slightly slower than {@link Wunderbaum.visit}.
         *
         * Example:
         * ```js
         * for(const node of tree) {
         *   ...
         * }
         * ```
         */
        [Symbol.iterator](): IterableIterator<WunderbaumNode>;
        /** @internal */
        protected _registerExtension(extension: WunderbaumExtension<any>): void;
        /** Called on tree (re)init after markup is created, before loading. */
        protected _initExtensions(): void;
        /**
         * Calculate a *stable*, unique key for a node from its refKey (or title).
         * We also add information from the parent, because a refKey may occur multiple
         * times in a tree (but not as child of the same parent).
         * @internal
         */
        _calculateKey(data: WbNodeData, parent?: WunderbaumNode): string;
        /** Add node to tree's bookkeeping data structures. @internal */
        _registerNode(node: WunderbaumNode): void;
        /** Remove node from tree's bookkeeping data structures. @internal  */
        _unregisterNode(node: WunderbaumNode): void;
        /** Call all hook methods of all registered extensions.*/
        protected _callHook(hook: keyof WunderbaumExtension<any>, data?: any): any;
        /**
         * Call tree method or extension method if defined.
         *
         * Example:
         * ```js
         * tree._callMethod("edit.startEdit", "arg1", "arg2")
         * ```
         */
        _callMethod(name: string, ...args: any[]): any;
        /**
         * Call event handler if defined in tree or tree.EXTENSION options.
         *
         * Example:
         * ```js
         * tree._callEvent("edit.beforeEdit", {foo: 42})
         * ```
         */
        _callEvent(type: string, extra?: any): any;
        /** Return the node for  given row index. */
        protected _getNodeByRowIdx(idx: number): WunderbaumNode | null;
        /** Return the topmost visible node in the viewport.
         * @param complete If `false`, the node is considered visible if at least one
         * pixel is visible.
         */
        getTopmostVpNode(complete?: boolean): WunderbaumNode;
        /** Return the lowest visible node in the viewport. */
        getLowestVpNode(complete?: boolean): WunderbaumNode;
        /** Return preceding visible node in the viewport. */
        protected _getPrevNodeInView(node?: WunderbaumNode, ofs?: number): WunderbaumNode;
        /** Return following visible node in the viewport. */
        protected _getNextNodeInView(node?: WunderbaumNode, options?: {
            ofs?: number;
            reverse?: boolean;
            cb?: (n: WunderbaumNode) => boolean;
        }): WunderbaumNode;
        /**
         * Append (or insert) a list of toplevel nodes.
         *
         * @see {@link WunderbaumNode.addChildren}
         */
        addChildren(nodeData: any, options?: AddChildrenOptions): WunderbaumNode;
        /**
         * Apply a modification (or navigation) operation on the **tree or active node**.
         */
        applyCommand(cmd: ApplyCommandType, options?: ApplyCommandOptions): any;
        /**
         * Apply a modification (or navigation) operation on a **node**.
         * @see {@link WunderbaumNode.applyCommand}
         */
        applyCommand(cmd: ApplyCommandType, node: WunderbaumNode, options?: ApplyCommandOptions): any;
        /** Delete all nodes. */
        clear(): void;
        /**
         * Clear nodes and markup and detach events and observers.
         *
         * This method may be useful to free up resources before re-creating a tree
         * on an existing div, for example in unittest suites.
         * Note that this Wunderbaum instance becomes unusable afterwards.
         */
        destroy(): void;
        /**
         * Return `tree.option.NAME` (also resolving if this is a callback).
         *
         * See also {@link WunderbaumNode.getOption|WunderbaumNode.getOption()}
         * to evaluate `node.NAME` setting and `tree.types[node.type].NAME`.
         *
         * @param name option name (use dot notation to access extension option, e.g.
         * `filter.mode`)
         */
        getOption(name: string, defaultValue?: any): any;
        /**
         * Set tree option.
         * Use dot notation to set plugin option, e.g. "filter.mode".
         */
        setOption(name: string, value: any): void;
        /** Return true if the tree (or one of its nodes) has the input focus. */
        hasFocus(): boolean;
        /**
         * Return true if the tree displays a header. Grids have a header unless the
         * `header` option is set to `false`. Plain trees have a header if the `header`
         * option is a string or `true`.
         */
        hasHeader(): boolean;
        /** Run code, but defer rendering of viewport until done.
         *
         * ```js
         * const res = tree.runWithDeferredUpdate(() => {
         *   return someFunctionThatWouldUpdateManyNodes();
         * });
         * ```
         */
        runWithDeferredUpdate<T>(func: () => util.NotPromise<T>): T;
        /** Run code, but defer rendering of viewport until done.
         *
         * ```js
         * const res = await tree.runWithDeferredUpdate(async () => {
         *   return someAsyncFunctionThatWouldUpdateManyNodes();
         * });
         * ```
         */
        runWithDeferredUpdateAsync<T>(func: () => Promise<T>): Promise<T>;
        /** Recursively expand all expandable nodes (triggers lazy load if needed). */
        expandAll(flag?: boolean, options?: ExpandAllOptions): Promise<void>;
        /** Recursively select all nodes. */
        selectAll(flag?: boolean): import("types").TristateType;
        /** Toggle select all nodes. */
        toggleSelect(): void;
        /**
         * Return an array of selected nodes.
         * @param stopOnParents only return the topmost selected node (useful with selectMode 'hier')
         */
        getSelectedNodes(stopOnParents?: boolean): WunderbaumNode[];
        /**
         * Return an array of refKey values.
         *
         * RefKeys are unique identifiers for a node data, and are used to identify
         * clones.
         * If more than one node has the same refKey, it is only returned once.
         * @param selected if true, only return refKeys of selected nodes.
         */
        getRefKeys(selected?: boolean): string[];
        protected _selectRange(eventInfo: WbEventInfo): false | void;
        /** Return the number of nodes in the data model.
         * @param visible if true, nodes that are hidden due to collapsed parents are ignored.
         */
        count(visible?: boolean): number;
        /** Return the number of *unique* nodes in the data model, i.e. unique `node.refKey`.
         */
        countUnique(): number;
        /** @internal sanity check. */
        _check(): void;
        /**
         * Find all nodes that match condition.
         *
         * @param match title string to search for, or a
         *     callback function that returns `true` if a node is matched.
         * @see {@link WunderbaumNode.findAll}
         */
        findAll(match: string | RegExp | MatcherCallback): WunderbaumNode[];
        /**
         * Find all nodes with a given _refKey_ (aka a list of clones).
         *
         * @param refKey a `node.refKey` value to search for.
         * @returns an array of matching nodes with at least two element or `[]`
         * if nothing found.
         *
         * @see {@link WunderbaumNode.getCloneList}
         */
        findByRefKey(refKey: string): WunderbaumNode[];
        /**
         * Find first node that matches condition.
         *
         * @param match title string to search for, or a
         *     callback function that returns `true` if a node is matched.
         * @see {@link WunderbaumNode.findFirst}
         */
        findFirst(match: string | RegExp | MatcherCallback): WunderbaumNode;
        /**
         * Find first node that matches condition.
         *
         * @see {@link WunderbaumNode.findFirst}
         *
         */
        findKey(key: string): WunderbaumNode | null;
        /**
         * Find the next visible node that starts with `match`, starting at `startNode`
         * and wrap-around at the end.
         * Used by quicksearch and keyboard navigation.
         */
        findNextNode(match: string | MatcherCallback, startNode?: WunderbaumNode | null, reverse?: boolean): WunderbaumNode | null;
        /**
         * Find a node relative to another node.
         *
         * @param node
         * @param where 'down', 'first', 'last', 'left', 'parent', 'right', or 'up'.
         *   (Alternatively the keyCode that would normally trigger this move,
         *   e.g. `$.ui.keyCode.LEFT` = 'left'.
         * @param includeHidden Not yet implemented
         */
        findRelatedNode(node: WunderbaumNode, where: NavigationType, includeHidden?: boolean): any;
        /**
         * Iterator version of {@link Wunderbaum.format}.
         */
        format_iter(name_cb?: NodeStringCallback, connectors?: string[]): IterableIterator<string>;
        /**
         * Return multiline string representation of the node hierarchy.
         * Mostly useful for debugging.
         *
         * Example:
         * ```js
         * console.info(tree.format((n)=>n.title));
         * ```
         * logs
         * ```
         * Playground
         * ├─ Books
         * |   ├─ Art of War
         * |   ╰─ Don Quixote
         * ├─ Music
         * ...
         * ```
         *
         * @see {@link Wunderbaum.format_iter} and {@link WunderbaumNode.format}.
         */
        format(name_cb?: NodeStringCallback, connectors?: string[]): string;
        /**
         * Always returns null (so a tree instance behaves as `tree.root`).
         */
        get parent(): null;
        /**
         * Return a list of top-level nodes.
         */
        get children(): WunderbaumNode[];
        /**
         * Return the active cell (`span.wb-col`) of the currently active node or null.
         */
        getActiveColElem(): HTMLSpanElement;
        /**
         * Return the currently active node or null (alias for `tree.activeNode`).
         * Alias for {@link Wunderbaum.activeNode}.
         *
         * @see {@link WunderbaumNode.setActive}
         * @see {@link WunderbaumNode.isActive}
         * @see {@link Wunderbaum.activeNode}
         * @see {@link Wunderbaum.focusNode}
         */
        getActiveNode(): WunderbaumNode;
        /**
         * Return the first top level node if any (not the invisible root node).
         */
        getFirstChild(): WunderbaumNode;
        /**
         * Return the last top level node if any (not the invisible root node).
         */
        getLastChild(): WunderbaumNode;
        /**
         * Return the node that currently has keyboard focus or null.
         * Alias for {@link Wunderbaum.focusNode}.
         * @see {@link WunderbaumNode.setFocus}
         * @see {@link WunderbaumNode.hasFocus}
         * @see {@link Wunderbaum.activeNode}
         * @see {@link Wunderbaum.focusNode}
         */
        getFocusNode(): WunderbaumNode;
        /** Return a {node: WunderbaumNode, region: TYPE} object for a mouse event.
         *
         * @param {Event} event Mouse event, e.g. click, ...
         * @returns {object} Return a {node: WunderbaumNode, region: TYPE} object
         *     TYPE: 'title' | 'prefix' | 'expander' | 'checkbox' | 'icon' | undefined
         */
        static getEventInfo(event: Event): WbEventInfo;
        /**
         * Return readable string representation for this instance.
         * @internal
         */
        toString(): string;
        /** Return true if any node title or grid cell is currently beeing edited.
         *
         * See also {@link isEditingTitle}.
         */
        isEditing(): boolean;
        /** Return true if any node is currently in edit-title mode.
         *
         * See also {@link WunderbaumNode.isEditingTitle} and {@link isEditing}.
         */
        isEditingTitle(): boolean;
        /**
         * Return true if any node is currently beeing loaded, i.e. a Ajax request is pending.
         */
        isLoading(): boolean;
        /** Write to `console.log` with tree name as prefix if opts.debugLevel >= 4.
         * @see {@link logDebug}
         */
        log(...args: any[]): void;
        /** Write to `console.debug`  with tree name as prefix if opts.debugLevel >= 4.
         * and browser console level includes debug/verbose messages.
         * @see {@link log}
         */
        logDebug(...args: any[]): void;
        /** Write to `console.error` with tree name as prefix. */
        logError(...args: any[]): void;
        /** Write to `console.info`  with tree name as prefix if opts.debugLevel >= 3. */
        logInfo(...args: any[]): void;
        /** @internal */
        logTime(label: string): string;
        /** @internal */
        logTimeEnd(label: string): void;
        /** Write to `console.warn` with tree name as prefix with if opts.debugLevel >= 2. */
        logWarn(...args: any[]): void;
        /** Emit a warning for deprecated methods. @internal */
        logDeprecate(method: string, options?: DeprecationOptions): void;
        /** Reset column widths to default. @since 0.10.0 */
        resetColumns(): void;
        /**
         * Make sure that this node is vertically scrolled into the viewport.
         *
         * Nodes that are above the visible area become the top row, nodes that are
         * below the viewport become the bottom row.
         */
        scrollTo(nodeOrOpts: ScrollToOptions | WunderbaumNode): void;
        /**
         * Make sure that this node is horizontally scrolled into the viewport.
         * Called by {@link setColumn}.
         */
        protected scrollToHorz(): void;
        /**
         * Set column #colIdx to 'active'.
         *
         * This higlights the column header and -cells by adding the `wb-active`
         * class to all grid cells of the active column. <br>
         * Available in cell-nav mode only.
         *
         * If _options.edit_ is true, the embedded input element is focused, or if
         * colIdx is 0, the node title is put into edit mode.
         */
        setColumn(colIdx: number | string, options?: SetColumnOptions): void;
        _setActiveNode(node: WunderbaumNode | null): void;
        /** Set or remove keyboard focus to the tree container. */
        setActiveNode(key: string, flag?: boolean, options?: SetActiveOptions): void;
        /** Set or remove keyboard focus to the tree container. */
        setFocus(flag?: boolean): void;
        _setFocusNode(node: WunderbaumNode | null): void;
        /**
         * Point `aria-activedescendant` at the focused row, if it is rendered.
         * (Rows outside the viewport are not in the DOM; the attribute is restored
         * when the row is rendered again.)
         * @internal
         */
        _updateActiveDescendant(): void;
        /** Return the current selection/expansion/activation status. @experimental */
        getState(options?: GetStateOptions): TreeStateDefinition;
        /** Apply selection/expansion/activation status. @experimental */
        setState(state: TreeStateDefinition, options?: SetStateOptions): Promise<void>;
        /**
         * Schedule an update request to reflect a tree change.
         * The render operation is async and debounced unless the `immediate` option
         * is set.
         *
         * Use {@link WunderbaumNode.update} if only a single node has changed,
         * or {@link WunderbaumNode._render}) to pass special options.
         */
        update(change: ChangeType, options?: UpdateOptions): void;
        /**
         * Update a row to reflect a single node's modification.
         *
         * @see {@link WunderbaumNode.update}, {@link WunderbaumNode._render}
         */
        update(change: ChangeType, node: WunderbaumNode, options?: UpdateOptions): void;
        /** Disable mouse and keyboard interaction (return prev. state). */
        setEnabled(flag?: boolean): boolean;
        /** Return false if tree is disabled. */
        isEnabled(): boolean;
        /** Return true if tree has more than one column, i.e. has additional data columns. */
        isGrid(): boolean;
        /** Return true if cell-navigation mode is active. */
        isCellNav(): boolean;
        /** Return true if row-navigation mode is active. */
        isRowNav(): boolean;
        /** Set the tree's navigation mode. */
        setCellNav(flag?: boolean): void;
        /** Set the tree's navigation mode option. */
        setNavigationOption(mode: NavModeEnum, reset?: boolean): void;
        /** Display tree status (ok, loading, error, noData) using styles and a dummy root node. */
        setStatus(status: NodeStatusType, options?: SetStatusOptions): WunderbaumNode | null;
        /** Add or redefine node type definitions. */
        setTypes(types: any, replace?: boolean): void;
        /**
         * Sort nodes list by title or custom criteria.
         * @param {function} cmp custom compare function(a, b) that returns -1, 0, or 1
         *    (defaults to sorting by title).
         * @param {boolean} deep pass true to sort all descendant nodes recursively
         * @deprecated use {@link sort}
         */
        sortChildren(cmp?: SortCallback | null, deep?: boolean): void;
        /**
         * Convenience method to implement column sorting.
         * @see {@link WunderbaumNode.sortByProperty}.
         * @since 0.11.0
         * @deprecated use {@link sort}
         */
        sortByProperty(options: SortByPropertyOptions): void;
        /**
         * Sort nodes list by title or custom criteria.
         * @since 0.14.0
         */
        sort(options: SortOptions): void;
        /** Convert tree to an array of plain objects.
         *
         * @param callback is called for every node, in order to allow
         *     modifications.
         *     Return `false` to ignore this node or `"skip"` to include this node
         *     without its children.
         * @see {@link WunderbaumNode.toDict}.
         */
        toDictArray(callback?: NodeToDictCallback): Array<WbNodeData>;
        /**
         * Update column headers and column width.
         * Return true if at least one column width changed.
         */
        _updateColumnWidths(): boolean;
        /** Create/update header markup from `this.columns` definition.
         * @internal
         */
        protected _renderHeaderMarkup(): void;
        /**
         * Render pending changes that were scheduled using {@link WunderbaumNode.update} if any.
         *
         * This is hardly ever neccessary, since we normally either
         * - call `update(ChangeType.TYPE)` (async, throttled), or
         * - call `update(ChangeType.TYPE, {immediate: true})` (synchronous)
         *
         * `updatePendingModifications()` will only force immediate execution of
         * pending async changes if any.
         */
        updatePendingModifications(): void;
        /** @internal */
        _createNodeIcon(node: WunderbaumNode, showLoading: boolean, showBadge: boolean): HTMLElement | null;
        private _updateTopBreadcrumb;
        /**
         * This is the actual update method, which is wrapped inside a throttle method.
         * It calls `updateColumns()` and `_updateRows()`.
         *
         * This protected method should not be called directly but via
         * {@link WunderbaumNode.update}`, {@link Wunderbaum.update},
         * or {@link Wunderbaum.updatePendingModifications}.
         * @internal
         */
        protected _updateViewportImmediately(): void;
        protected _updateRows(options?: any): boolean;
        /**
         * Call `callback(node)` for all nodes in hierarchical order (depth-first, pre-order).
         * @see `wb_node.WunderbaumNode.IterableIterator<WunderbaumNode>`
         * @see {@link WunderbaumNode.visit}.
         *
         * @param {function} callback the callback function.
         *     Return false to stop iteration, return "skip" to skip this node and
         *     children only.
         * @returns {boolean} false, if the iterator was stopped.
         */
        visit(callback: (node: WunderbaumNode) => any): import("types").NodeVisitResponse;
        /**
         * Call callback(node) for all nodes in vertical order, top down (or bottom up).
         *
         * Note that this considers expansion state, i.e. filtered nodes and children
         * of collapsed nodes are skipped, unless `includeHidden` is set.
         *
         * Stop iteration if callback() returns false.<br>
         * Return false if iteration was stopped.
         *
         * @returns {boolean} false if iteration was canceled
         */
        visitRows(callback: NodeVisitCallback, options?: VisitRowsOptions): boolean;
        /**
         * Call fn(node) for all nodes in vertical order, bottom up.
         * @internal
         */
        protected _visitRowsUp(callback: NodeVisitCallback, options: VisitRowsOptions): boolean;
        /**
         * Reload the tree with a new source.
         *
         * Previous data is cleared. Note that also column- and type defintions may
         * be passed with the `source` object.
         * @see {@link Wunderbaum.reload} for a shortcut to reload the last ajax request
         * and restore the previous state.
         */
        load(source: SourceType): Promise<void>;
        /** Reload the tree and optionally restore state.
         * Source defaults to last ajax url if any.
         * Restoring the active node requires stable keys
         * @see {@link WunderbaumOptions.autoKeys}
         * @see {@link Wunderbaum.load}
         * @experimental
         */
        reload(options?: ReloadOptions): Promise<void>;
        /**
         * Make sure that all nodes in the given keyList are accessible.
         * This may include loading lazy parent nodes.
         * Recursively load (and optionally expand) all requested node paths.
         */
        protected _loadLazyNodes(keyList: string[], options?: LoadLazyNodesOptions): Promise<void>;
        /**
         * Disable render requests during operations that would trigger many updates.
         *
         * ```js
         * try {
         *   tree.enableUpdate(false);
         *   // ... (long running operation that would trigger many updates)
         *   foo();
         *   // ... NOTE: make sure that async operations have finished, e.g.
         *   await foo();
         * } finally {
         *   tree.enableUpdate(true);
         * }
         * ```
         */
        enableUpdate(flag: boolean): void;
        /**
         * Dim or hide unmatched nodes.
         * @param filter a string to match against node titles, or a callback function.
         * @param options filter options. Defaults to the `tree.options.filter` settings.
         * @returns the number of nodes that match the filter.
         * @example
         * ```ts
         * tree.filterNodes("foo", {mode: 'dim', fuzzy: true});
         * // or pass a callback
         * tree.filterNodes((node) => { return node.data.foo === true }, {mode: 'hide'});
         * ```
         */
        filterNodes(filter: string | RegExp | NodeFilterCallback, options: FilterNodesOptions): number;
        /**
         * Return the number of nodes that match the current filter.
         * @see {@link Wunderbaum.filterNodes}
         * @since 0.9.0
         */
        countMatches(): number;
        /**
         * Dim or hide whole branches.
         * @deprecated Use {@link filterNodes} instead and set `options.matchBranch: true`.
         */
        filterBranches(filter: string | NodeFilterCallback, options: FilterNodesOptions): number;
        /**
         * Reset the filter.
         */
        clearFilter(): void;
        /**
         * Return true if a filter is currently applied.
         */
        isFilterActive(): boolean;
        /**
         * Re-apply current filter.
         */
        updateFilter(): void;
    }
}
