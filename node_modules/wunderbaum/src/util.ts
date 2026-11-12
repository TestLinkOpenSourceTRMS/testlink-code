/*!
 * Wunderbaum - util
 * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
 * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
 */

/** @module util */

import { DebouncedFunction, debounce, throttle } from "./debounce";

export { debounce, throttle };

/** Readable names for `MouseEvent.button` */
export const MOUSE_BUTTONS: { [key: number]: string } = {
  0: "",
  1: "left",
  2: "middle",
  3: "right",
  4: "back",
  5: "forward",
};

export const MAX_INT = 9007199254740991;
const userInfo = _getUserInfo();
/**True if the client is using a macOS platform. */
export const isMac = userInfo.isMac;

const REX_HTML = /[&<>"'/]/g; // Escape those characters
const REX_TOOLTIP = /[<>"'/]/g; // Don't escape `&` in tooltips
const ENTITY_MAP: { [key: string]: string } = {
  "&": "&amp;",
  "<": "&lt;",
  ">": "&gt;",
  '"': "&quot;",
  "'": "&#39;",
  "/": "&#x2F;",
};

export type NotPromise<T> = T extends Promise<any> ? never : T;

export type FunctionType = (...args: any[]) => any;
export type EventCallbackType = (e: Event) => boolean | void;
type PromiseCallbackType = (val: any) => void;

/** A generic error that can be thrown to indicate a validation error when
 * handling the `apply` event for a node title or the `change` event for a
 * grid cell.
 */
export class ValidationError extends Error {
  constructor(message: string) {
    super(message);
    this.name = "ValidationError";
  }
}

/**
 * A ES6 Promise, that exposes the resolve()/reject() methods.
 *
 * TODO: See [Promise.withResolvers()](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Promise/withResolvers#description)
 * , a proposed standard, but not yet implemented in any browser.
 */
export class Deferred {
  private thens: PromiseCallbackType[] = [];
  private catches: PromiseCallbackType[] = [];

  private status = "";
  private resolvedValue: any;
  private rejectedError: any;

  constructor() {}

  resolve(value?: any) {
    if (this.status) {
      throw new Error("already settled");
    }
    this.status = "resolved";
    this.resolvedValue = value;
    this.thens.forEach((t) => t(value));
    this.thens = []; // Avoid memleaks.
  }
  reject(error?: any) {
    if (this.status) {
      throw new Error("already settled");
    }
    this.status = "rejected";
    this.rejectedError = error;
    this.catches.forEach((c) => c(error));
    this.catches = []; // Avoid memleaks.
  }
  then(cb: any) {
    if (status === "resolved") {
      cb(this.resolvedValue);
    } else {
      this.thens.unshift(cb);
    }
  }
  catch(cb: any) {
    if (this.status === "rejected") {
      cb(this.rejectedError);
    } else {
      this.catches.unshift(cb);
    }
  }
  promise() {
    return {
      then: this.then,
      catch: this.catch,
    };
  }
}

/**Throw an `Error` if `cond` is falsey. */
export function assert(cond: any, msg: string) {
  if (!cond) {
    msg = msg || "Assertion failed.";
    throw new Error(msg);
  }
}

function _getUserInfo() {
  const nav = navigator;
  // const ua = nav.userAgentData;
  const res = {
    isMac: /Mac/.test(nav.platform),
  };
  return res;
}

/** Run `callback` when document was loaded. */
export function documentReady(callback: () => void): void {
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", callback);
  } else {
    callback();
  }
}

/** Resolve when document was loaded. */
export function documentReadyPromise(): Promise<void> {
  return new Promise((resolve) => {
    documentReady(resolve);
  });
}

/**
 * Iterate over Object properties or array elements.
 *
 * @param obj `Object`, `Array` or null
 * @param callback called for every item.
 *  `this` also contains the item.
 *  Return `false` to stop the iteration.
 */
export function each(
  obj: any,
  callback: (index: number | string, item: any) => void | boolean
): any {
  if (obj == null) {
    // accept `null` or `undefined`
    return obj;
  }
  const length = obj.length;
  let i = 0;

  if (typeof length === "number") {
    for (; i < length; i++) {
      if (callback.call(obj[i], i, obj[i]) === false) {
        break;
      }
    }
  } else {
    for (const k in obj) {
      if (callback.call(obj[i], k, obj[k]) === false) {
        break;
      }
    }
  }
  return obj;
}

/** Shortcut for `throw new Error(msg)`. */
export function error(msg: string) {
  throw new Error(msg);
}

/** Convert `<`, `>`, `&`, `"`, `'`, and `/` to the equivalent entities. */
export function escapeHtml(s: string): string {
  return ("" + s).replace(REX_HTML, function (s) {
    return ENTITY_MAP[s];
  });
}

// export function escapeRegExp(s: string) {
//   return s.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"); // $& means the whole matched string
// }

/**Convert a regular expression string by escaping special characters (e.g. `"$"` -> `"\$"`) */
export function escapeRegex(s: string) {
  return ("" + s).replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
}

/** Convert `<`, `>`, `"`, `'`, and `/` (but not `&`) to the equivalent entities. */
export function escapeTooltip(s: string): string {
  return ("" + s).replace(REX_TOOLTIP, function (s) {
    return ENTITY_MAP[s];
  });
}

/** TODO */
export function extractHtmlText(s: string) {
  if (s.indexOf(">") >= 0) {
    error("Not implemented");
    // return $("<div/>").html(s).text();
  }
  return s;
}

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
export function getValueFromElem(elem: HTMLElement, coerce = false): any {
  const tag = elem.tagName;
  let value = null;

  if (tag === "SPAN" && elem.classList.contains("wb-col")) {
    const span = <HTMLSpanElement>elem;
    const embeddedInput = span.querySelector<HTMLElement>("input,select");

    if (embeddedInput) {
      return getValueFromElem(embeddedInput, coerce);
    }
    span.innerText = "" + value;
  } else if (tag === "INPUT") {
    const input = <HTMLInputElement>elem;
    const type = input.type;

    switch (type) {
      case "button":
      case "reset":
      case "submit":
      case "image":
        break;
      case "checkbox":
        value = input.indeterminate ? null : input.checked;
        break;
      case "date":
      case "datetime":
      case "datetime-local":
      case "month":
      case "time":
      case "week":
        value = coerce ? input.valueAsDate : input.value;
        break;
      case "number":
      case "range":
        value = input.valueAsNumber;
        break;
      case "radio":
        {
          const name = input.name;
          const checked = input.parentElement!.querySelector<HTMLInputElement>(
            `input[name="${name}"]:checked`
          );
          value = checked ? checked.value : undefined;
        }
        break;
      case "text":
      default:
        value = input.value;
    }
  } else if (tag === "SELECT") {
    const select = <HTMLSelectElement>elem;
    value = select.value;
  }
  return value;
}

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
export function setValueToElem(elem: HTMLElement, value: any): void {
  const tag = elem.tagName;

  if (tag === "SPAN" && elem.classList.contains("wb-col")) {
    const span = <HTMLSpanElement>elem;
    const embeddedInput = span.querySelector("input,select");

    if (embeddedInput) {
      return setValueToElem(<HTMLElement>embeddedInput, value);
    }
    // No embedded input: simply write as escaped html
    span.innerText = "" + value;
  } else if (tag === "INPUT") {
    const input = <HTMLInputElement>elem;
    const type = input.type;

    switch (type) {
      case "checkbox":
        // An explicit `null` value is interpreted as 'indeterminate'.
        // `undefined` is interpreted as 'unchecked'
        input.indeterminate = value === null;
        input.checked = !!value;
        break;
      case "date":
      case "month":
      case "time":
      case "week":
      case "datetime":
      case "datetime-local":
        input.valueAsDate = new Date(value);
        break;
      case "number":
      case "range":
        if (value == null) {
          input.value = value;
        } else {
          input.valueAsNumber = value;
        }
        break;
      case "radio":
        error(`Not yet implemented: ${type}`);
        // const name = input.name;
        // const checked = input.parentElement!.querySelector(
        //   `input[name="${name}"]:checked`
        // );
        // value = checked ? (<HTMLInputElement>checked).value : undefined;
        break;
      case "button":
      case "reset":
      case "submit":
      case "image":
        break;
      case "text":
      default:
        input.value = value ?? "";
    }
  } else if (tag === "SELECT") {
    const select = <HTMLSelectElement>elem;
    if (value == null) {
      select.selectedIndex = -1;
    } else {
      select.value = value;
    }
  }
}

/** Show/hide element by setting the `display` style to 'none'. */
export function setElemDisplay(
  elem: string | HTMLElement,
  flag: boolean
): void {
  const style = (<HTMLElement>elemFromSelector(elem)).style;
  if (flag) {
    if (style.display === "none") {
      style.display = "";
    }
  } else if (style.display === "") {
    style.display = "none";
  }
}

/** Create and return an unconnected `HTMLElement` from a HTML string. */
export function elemFromHtml<T = HTMLElement>(html: string): T {
  const t = document.createElement("template");
  t.innerHTML = html.trim();
  return t.content.firstElementChild as T;
}

const _IGNORE_KEYS = new Set(["Alt", "Control", "Meta", "Shift"]);

/** Return a HtmlElement from selector or cast an existing element. */
export function elemFromSelector<T = HTMLElement>(obj: string | T): T | null {
  if (!obj) {
    return null; //(null as unknown) as HTMLElement;
  }
  if (typeof obj === "string") {
    return document.querySelector(obj) as T;
  }
  return obj as T;
}

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
export function eventToString(event: Event): string {
  const key = (<KeyboardEvent>event).key;
  const et = event.type;
  const s = [];

  if ((<KeyboardEvent>event).altKey) {
    s.push("Alt");
  }
  if ((<KeyboardEvent>event).ctrlKey) {
    s.push("Control");
  }
  if ((<KeyboardEvent>event).metaKey) {
    s.push("Meta");
  }
  if ((<KeyboardEvent>event).shiftKey) {
    s.push("Shift");
  }

  if (et === "click" || et === "dblclick") {
    s.push(MOUSE_BUTTONS[(<MouseEvent>event).button] + et);
  } else if (et === "wheel") {
    s.push(et);
    // } else if (!IGNORE_KEYCODES[key]) {
    //   s.push(
    //     SPECIAL_KEYCODES[key] ||
    //     String.fromCharCode(key).toLowerCase()
    //   );
  } else if (!_IGNORE_KEYS.has(key)) {
    s.push(key);
  }
  return s.join("+");
}

/**
 * Copy allproperties from one or more source objects to a target object.
 *
 * @returns the modified target object.
 */
// TODO: use Object.assign()? --> https://stackoverflow.com/a/42740894
// TODO: support deep merge --> https://stackoverflow.com/a/42740894
export function extend(...args: any[]) {
  for (let i = 1; i < args.length; i++) {
    const arg = args[i];
    if (arg == null) {
      continue;
    }
    for (const key in arg) {
      if (Object.prototype.hasOwnProperty.call(arg, key)) {
        args[0][key] = arg[key];
      }
    }
  }
  return args[0];
}

/** Return true if `obj` is of type `array`. */
export function isArray(obj: any) {
  return Array.isArray(obj);
}

/** Return true if `obj` is of type `Object` and has no properties. */
export function isEmptyObject(obj: any) {
  return Object.keys(obj).length === 0 && obj.constructor === Object;
}

/** Return true if `obj` is of type `function`. */
export function isFunction(obj: any) {
  return typeof obj === "function";
}

/** Return true if `obj` is of type `Object`. */
export function isPlainObject(obj: any) {
  return Object.prototype.toString.call(obj) === "[object Object]";
}

/** A dummy function that does nothing ('no operation'). */
export function noop(...args: any[]): any {}

/**
 * Bind one or more event handlers directly to an [EventTarget](https://developer.mozilla.org/en-US/docs/Web/API/EventTarget).
 *
 * @param rootTarget EventTarget or selector
 * @param eventNames
 * @param handler
 */
export function onEvent(
  rootTarget: EventTarget | string,
  eventNames: string,
  handler: EventCallbackType
): void;

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
export function onEvent(
  rootTarget: EventTarget | string,
  eventNames: string,
  selector: string,
  handler: EventCallbackType
): void;

export function onEvent(
  rootTarget: EventTarget | string,
  eventNames: string,
  selectorOrHandler: string | EventCallbackType,
  handlerOrNone?: EventCallbackType
): void {
  let selector: string | null, handler: EventCallbackType;
  rootTarget = elemFromSelector<EventTarget>(rootTarget)!;
  // rootTarget = eventTargetFromSelector<EventTarget>(rootTarget)!;

  if (handlerOrNone) {
    selector = selectorOrHandler as string;
    handler = handlerOrNone!;
  } else {
    selector = "";
    handler = selectorOrHandler as EventCallbackType;
  }

  eventNames.split(" ").forEach((evn) => {
    (<EventTarget>rootTarget).addEventListener(evn, function (e) {
      if (!selector) {
        return handler!(e); // no event delegation
      } else if (e.target) {
        let elem = e.target as HTMLElement;
        if (elem.matches(selector as string)) {
          return handler!(e);
        }
        elem = elem.closest(selector) as HTMLElement;
        if (elem) {
          return handler(e);
        }
      }
    });
  });
}

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
export function overrideMethod(
  instance: any,
  methodName: string,
  handler: FunctionType,
  ctx?: any
) {
  let prevSuper: FunctionType, prevSuperApply: FunctionType;
  const self = ctx || instance;
  const prevFunc = instance[methodName];
  const _super = (...args: any[]) => {
    return prevFunc.apply(self, args);
  };
  const _superApply = (argsArray: any[]) => {
    return prevFunc.apply(self, argsArray);
  };

  const wrapper = (...args: any[]) => {
    try {
      prevSuper = self._super;
      prevSuperApply = self._superApply;
      self._super = _super;
      self._superApply = _superApply;
      return handler.apply(self, args);
    } finally {
      self._super = prevSuper;
      self._superApply = prevSuperApply;
    }
  };
  instance[methodName] = wrapper;
}

/** Run function after ms milliseconds and return a promise that resolves when done. */
export function setTimeoutPromise<T = unknown>(
  this: unknown,
  callback: (...args: any[]) => T,
  ms: number
) {
  return new Promise<T>((resolve, reject) => {
    setTimeout(() => {
      try {
        resolve(callback.apply(this));
      } catch (err) {
        reject(err);
      }
    }, ms);
  });
}

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
export async function sleep(ms: number) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

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
export function toggleCheckbox(
  element: HTMLElement | string,
  value?: boolean | null,
  tristate?: boolean
): void {
  const input = elemFromSelector(element) as HTMLInputElement;
  assert(input.type === "checkbox", `Expected a checkbox: ${input.type}`);
  tristate ??= input.classList.contains("wb-tristate") || input.indeterminate;

  if (value === undefined) {
    const curValue = input.indeterminate ? null : input.checked;
    switch (curValue) {
      case true:
        value = false;
        break;
      case false:
        value = tristate ? null : true;
        break;
      case null:
        value = true;
        break;
    }
  }
  input.indeterminate = value == null;
  input.checked = !!value;
}

/**
 * Return `opts.NAME` if opts is valid and
 *
 * @param opts dict, object, or null
 * @param name option name (use dot notation to access extension option, e.g. `filter.mode`)
 * @param defaultValue returned when `opts` is not an object, or does not have a NAME property
 */
export function getOption(
  opts: any,
  name: string,
  defaultValue: any = undefined
): any {
  let ext;

  // Lookup `name` in options dict
  if (opts && name.indexOf(".") >= 0) {
    [ext, name] = name.split(".");
    opts = opts[ext];
  }
  const value = opts ? opts[name] : null;
  // Use value from value options dict, fallback do default
  return value ?? defaultValue;
}

/** Return the next value from a list of values (rotating). @since 0.11 */
export function rotate(value: any, values: any[]): any {
  const idx = values.indexOf(value);
  return values[(idx + 1) % values.length];
}

/** Convert an Array or space-separated string to a Set. */
export function toSet(val: any): Set<string> {
  if (val instanceof Set) {
    return val;
  }
  if (typeof val === "string") {
    const set = new Set<string>();
    for (const c of val.split(" ")) {
      set.add(c.trim());
    }
    return set;
  }
  if (Array.isArray(val)) {
    return new Set<string>(val);
  }
  throw new Error("Cannot convert to Set<string>: " + val);
}

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
export function toPixel(
  ...defaults: (string | number | undefined | null)[]
): number {
  for (const d of defaults) {
    if (typeof d === "number") {
      return d;
    }
    if (typeof d === "string" && d.endsWith("px")) {
      return parseInt(d, 10);
    }
    assert(d == null, `Expected a number or string like '123px': ${d}`);
  }
  throw new Error(`Expected a string like '123px': ${defaults}`);
}

/** Cast any value to <T>. */
export function unsafeCast<T>(value: any): T {
  return value as T;
}

/** Return the the boolean value of the first non-null element.
 * Example:
 * ```js
 * const opts = { flag: true };
 * const value = util.toBool(opts.foo, opts.flag, false);  // returns true
 * ```
 */
export function toBool(
  ...boolDefaults: (boolean | undefined | null)[]
): boolean {
  for (const d of boolDefaults) {
    if (d != null) {
      return !!d;
    }
  }
  throw new Error("No default boolean value provided");
}

/**
 * Return `val` unless `val` is a number in which case we convert to boolean.
 * This is useful when a boolean value is stored as a 0/1 (e.g. in JSON) and
 * we still want to maintain string values. null and undefined are returned as
 * is. E.g. `checkbox` may be boolean or 'radio'.
 */
export function intToBool(
  val: boolean | number | string | undefined
): boolean | string | undefined {
  return typeof val === "number" ? !!val : val;
}

// /** Check if a string is contained in an Array or Set. */
// export function isAnyOf(s: string, items: Array<string>|Set<string>): boolean {
//   return Array.prototype.includes.call(items, s)
// }

// /** Check if an Array or Set has at least one matching entry. */
// export function hasAnyOf(container: Array<string>|Set<string>, items: Array<string>): boolean {
//   if (Array.isArray(container)) {
//     return container.some(v => )
//   }
//   return container.some(v => {})
//   // const container = toSet(items);
//   // const itemSet = toSet(items);
//   // Array.prototype.includes
//   // throw new Error("Cannot convert to Set<string>: " + val);
// }

/** Return a canonical string representation for an object's type (e.g. 'array', 'number', ...). */
export function type(obj: any): string {
  return Object.prototype.toString
    .call(obj)
    .replace(/^\[object (.+)\]$/, "$1")
    .toLowerCase();
}

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
export function adaptiveThrottle(
  this: unknown,
  callback: (...args: any[]) => void,
  options: object
): DebouncedFunction<(...args: any[]) => void> {
  const opts = Object.assign(
    {
      minDelay: 16,
      defaultDelay: 200,
      maxDelay: 5000,
      delayFactor: 2.0,
    },
    options
  );
  const minDelay = Math.max(16, +opts.minDelay);
  const maxDelay = +opts.maxDelay;

  let waiting = 0; // Initially, we're not waiting
  let pendingArgs: any[] | null = null;
  let pendingTimer: number | null = null;

  const throttledFn = (...args: any[]) => {
    if (waiting) {
      pendingArgs = args;
      // console.log(`adaptiveThrottle() queueing request #${waiting}...`, args);
      waiting += 1;
    } else {
      // Prevent invocations while running or blocking
      waiting = 1;
      const useArgs = args; // pendingArgs || args;
      pendingArgs = null;

      // console.log(`adaptiveThrottle() execute...`, useArgs);
      const start = Date.now();
      try {
        callback.apply(this, useArgs);
      } catch (error) {
        console.error(error); // eslint-disable-line no-console
      }
      const elap = Date.now() - start;

      const curDelay = Math.min(
        Math.max(minDelay, elap * opts.delayFactor),
        maxDelay
      );
      const useDelay = Math.max(minDelay, curDelay - elap);
      // console.log(
      //   `adaptiveThrottle() calling worker took ${elap}ms. delay = ${curDelay}ms, using ${useDelay}ms`,
      //   pendingArgs
      // );
      pendingTimer = <number>(<unknown>setTimeout(() => {
        // Unblock, and trigger pending requests if any
        // const skipped = waiting - 1;
        pendingTimer = null;
        waiting = 0; // And allow future invocations
        if (pendingArgs != null) {
          // There was another request while running or waiting
          // console.log(
          //   `adaptiveThrottle() re-trigger (missed ${skipped})...`,
          //   pendingArgs
          // );
          throttledFn.apply(this, pendingArgs);
        }
      }, useDelay));
    }
  };
  throttledFn.cancel = () => {
    if (pendingTimer) {
      clearTimeout(pendingTimer);
      pendingTimer = null;
    }
    pendingArgs = null;
    waiting = 0;
  };
  throttledFn.pending = () => {
    return !!pendingTimer;
  };
  throttledFn.flush = () => {
    throw new Error("Not implemented");
  };
  return throttledFn;
}

/**
 * MurmurHash3 implementation for strings.
 * @param key The input string to hash.
 * @param asString Optional convert result to zero-padded string of 8 characters.
 * @param seed Optional seed value.
 * @returns A 32-bit hash as a number or string.
 */
export function murmurHash3(
  key: string,
  asString = true,
  seed: number = 0
): number | string {
  let h1 = seed;
  const remainder = key.length & 3; // key.length % 4
  const bytes = key.length - remainder;
  const c1 = 0xcc9e2d51;
  const c2 = 0x1b873593;

  let i = 0;
  while (i < bytes) {
    let k1 =
      (key.charCodeAt(i) & 0xff) |
      ((key.charCodeAt(++i) & 0xff) << 8) |
      ((key.charCodeAt(++i) & 0xff) << 16) |
      ((key.charCodeAt(++i) & 0xff) << 24);
    ++i;

    k1 = Math.imul(k1, c1);
    k1 = (k1 << 15) | (k1 >>> 17);
    k1 = Math.imul(k1, c2);

    h1 ^= k1;
    h1 = (h1 << 13) | (h1 >>> 19);
    h1 = Math.imul(h1, 5) + 0xe6546b64;
  }

  let k1 = 0;
  switch (remainder) {
    case 3:
      k1 ^= (key.charCodeAt(i + 2) & 0xff) << 16;
    // fall through
    case 2:
      k1 ^= (key.charCodeAt(i + 1) & 0xff) << 8;
    // fall through
    case 1:
      k1 ^= key.charCodeAt(i) & 0xff;
      k1 = Math.imul(k1, c1);
      k1 = (k1 << 15) | (k1 >>> 17);
      k1 = Math.imul(k1, c2);
      h1 ^= k1;
  }

  h1 ^= key.length;
  h1 ^= h1 >>> 16;
  h1 = Math.imul(h1, 0x85ebca6b);
  h1 ^= h1 >>> 13;
  h1 = Math.imul(h1, 0xc2b2ae35);
  h1 ^= h1 >>> 16;

  if (asString) {
    // Convert to 8 digit hex string
    return (h1 >>> 0).toString(16).padStart(8, "0");
  }
  return h1 >>> 0; // Convert to unsigned 32-bit integer
}
