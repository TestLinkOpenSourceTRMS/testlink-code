/*!
 * Wunderbaum - ext-edit
 * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
 * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
 */

import { Wunderbaum } from "./wunderbaum";
import { WunderbaumExtension } from "./wb_extension_base";
import {
  assert,
  escapeHtml,
  eventToString,
  getValueFromElem,
  isMac,
  isPlainObject,
  onEvent,
  ValidationError,
} from "./util";
import { debounce } from "./debounce";
import { WunderbaumNode } from "./wb_node";
import { EditOptionsType, InsertNodeType, WbNodeData } from "./types";

// const START_MARKER = "\uFFF7";

export class EditExtension extends WunderbaumExtension<EditOptionsType> {
  protected debouncedOnChange: (e: Event) => void;
  protected curEditNode: WunderbaumNode | null = null;
  protected relatedNode: WunderbaumNode | null = null;

  constructor(tree: Wunderbaum) {
    super(tree, "edit", {
      debounce: 100,
      minlength: 1,
      maxlength: null,
      trigger: [], //["clickActive", "F2", "macEnter"],
      trim: true,
      select: true,
      slowClickDelay: 1000, // Handle 'clickActive' only if last click is less than this old (0: always)
      validity: true, //"Please enter a title",
      // --- Events ---
      // (note: there is also the `tree.change` event.)
      beforeEdit: null,
      edit: null,
      apply: null,
    });

    this.debouncedOnChange = debounce(
      this._onChange.bind(this),
      this.getPluginOption("debounce")
    );
  }

  /*
   * Call an event handler, while marking the current node cell 'busy'.
   * Deal with returned promises and ValidationError.
   * Convert a ValidationError into a input.setCustomValidity() call and vice versa.
   */
  protected async _applyChange(
    eventName: string,
    node: WunderbaumNode,
    colElem: HTMLElement,
    inputElem: HTMLInputElement,
    extra: any
  ): Promise<any> {
    node.log(`_applyChange(${eventName})`, extra);

    colElem.classList.add("wb-busy");
    colElem.classList.remove("wb-error", "wb-invalid");

    inputElem.setCustomValidity("");

    // Call event handler either ('change' or 'edit.appy'), which may return a
    // promise or a scalar value or throw a ValidationError.
    return new Promise((resolve, reject) => {
      const res = node._callEvent(eventName, extra);
      // normalize to promise, even if a scalar value was returned and await it
      Promise.resolve(res)
        .then((res) => {
          resolve(res);
        })
        .catch((err) => {
          reject(err);
        });
    })
      .then((res) => {
        if (!inputElem.checkValidity()) {
          // Native validation failed or handler called 'inputElem.setCustomValidity()'
          node.logWarn("inputElem.checkValidity() failed: throwing....");
          throw new ValidationError(inputElem.validationMessage);
        }
        return res;
      })
      .catch((err) => {
        if (err instanceof ValidationError) {
          node.logWarn("catched ", err);
          colElem.classList.add("wb-invalid");
          if (inputElem.setCustomValidity && !inputElem.validationMessage) {
            inputElem.setCustomValidity(err.message);
          }
          if (inputElem.validationMessage) {
            inputElem.reportValidity();
          }
          // throw err;
        } else {
          node.logError(
            `Error in ${eventName} event handler (throw e.util.ValidationError instead if this was intended)`,
            err
          );
          colElem.classList.add("wb-error");
          throw err;
        }
      })
      .finally(() => {
        colElem.classList.remove("wb-busy");
      });
  }

  /*
   * Called for when a control that is embedded in a cell fires a `change` event.
   */
  protected _onChange(e: Event) {
    const info = Wunderbaum.getEventInfo(e);
    const node = info.node!;
    const colElem = <HTMLElement>info.colElem!;
    if (!node || info.colIdx === 0) {
      this.tree.log("Ignored change event for removed element or node title");
      return;
    }
    // See also WbChangeEventType
    this._applyChange("change", node, colElem, e.target as HTMLInputElement, {
      info: info,
      event: e,
      inputElem: e.target,
      inputValue: Wunderbaum.util.getValueFromElem(e.target as HTMLElement),
      inputValid: (e.target as HTMLInputElement).checkValidity(),
    });
  }

  init() {
    super.init();

    onEvent(
      this.tree.element,
      "change", //"change input",
      ".contenteditable,input,textarea,select",
      // #61: we must not debounce the `change`, event.target may be reset to null
      // when the debounced handler is called.
      // (e) => {
      //   this.debouncedOnChange(e);
      // }
      (e) => this._onChange(e)
    );
  }

  /* Called by ext_keynav to pre-process input. */
  _preprocessKeyEvent(data: any): boolean | undefined {
    const event = data.event;
    const eventName = eventToString(event);
    const tree = this.tree;
    const trigger = this.getPluginOption("trigger");
    // const inputElem =
    //   event.target && event.target.closest("input,[contenteditable]");

    // tree.logDebug(`_preprocessKeyEvent: ${eventName}, editing:${this.isEditingTitle()}`);

    // --- Title editing: apply/discard ---
    // if (inputElem) {
    if (this.isEditingTitle()) {
      switch (eventName) {
        case "Enter":
          this._stopEditTitle(true, { event: event });
          return false;
        case "Escape":
          this._stopEditTitle(false, { event: event });
          return false;
      }
      // If the event target is an input element or `contenteditable="true"`,
      // we ignore it as navigation command
      return false;
    }
    // --- Trigger title editing
    if (tree.isRowNav() || tree.activeColIdx === 0) {
      switch (eventName) {
        case "Enter":
          if (trigger.indexOf("macEnter") >= 0 && isMac) {
            this.startEditTitle();
            return false;
          }
          break;
        case "F2":
          if (trigger.indexOf("F2") >= 0) {
            this.startEditTitle();
            return false;
          }
          break;
      }
      return true;
    }
    return true;
  }

  /** Return true if a title is currently being edited. */
  isEditingTitle(node?: WunderbaumNode): boolean {
    return node ? this.curEditNode === node : !!this.curEditNode;
  }

  /** Start renaming, i.e. replace the title with an embedded `<input>`. */
  startEditTitle(node?: WunderbaumNode | null) {
    node = node ?? this.tree.getActiveNode();
    const validity = this.getPluginOption("validity");
    const select = this.getPluginOption("select");

    if (!node) {
      return;
    }
    if (node.isStatusNode()) {
      node.logWarn("Cannot edit status node.");
      return;
    }
    this.tree.logDebug(`startEditTitle(node=${node})`);
    let inputHtml = node._callEvent("edit.beforeEdit");
    if (inputHtml === false) {
      node.logDebug("beforeEdit canceled operation.");
      return;
    }
    // `beforeEdit(e)` may return an input HTML string. Otherwise use a default
    // (we also treat a `true` return value as 'use default'):
    if (inputHtml === true || !inputHtml) {
      const title = escapeHtml(node.title);
      let opt = this.getPluginOption("maxlength");
      const maxlength = opt ? ` maxlength="${opt}"` : "";
      opt = this.getPluginOption("minlength");
      const minlength = opt ? ` minlength="${opt}"` : "";
      const required = opt > 0 ? " required" : "";
      inputHtml =
        `<input type=text class="wb-input-edit" tabindex=-1 value="${title}" ` +
        `autocorrect="off"${required}${minlength}${maxlength} >`;
    }
    const titleSpan = node
      .getColElem(0)!
      .querySelector(".wb-title") as HTMLSpanElement;

    titleSpan.innerHTML = inputHtml;
    const inputElem = titleSpan.firstElementChild as HTMLInputElement;
    if (validity) {
      // Permanently apply input validations (CSS and tooltip)
      inputElem.addEventListener("keydown", (e) => {
        inputElem.setCustomValidity("");
        if (!inputElem.reportValidity()) {
          node!.logWarn(`Invalid input: '${inputElem.value}'`);
        }
      });
    }
    inputElem.focus();
    if (select) {
      inputElem.select();
    }

    this.curEditNode = node;
    node._callEvent("edit.edit", {
      inputElem: inputElem,
    });
  }
  /**
   *
   * @param apply
   * @returns
   */
  stopEditTitle(apply: boolean) {
    return this._stopEditTitle(apply, {});
  }
  /*
   *
   * @param apply
   * @param opts.canKeepOpen
   */
  _stopEditTitle(apply: boolean, options: any) {
    options ??= {};
    const focusElem = document.activeElement as HTMLInputElement;
    let newValue = focusElem ? getValueFromElem(focusElem) : null;
    const node = this.curEditNode;
    const forceClose = !!options.forceClose;
    const validity = this.getPluginOption("validity");

    if (newValue && this.getPluginOption("trim")) {
      newValue = newValue.trim();
    }
    if (!node) {
      // this.tree.logDebug("stopEditTitle: not in edit mode.");
      return;
    }
    node.logDebug(`stopEditTitle(${apply})`, options, focusElem, newValue);

    if (apply && newValue !== null && newValue !== node.title) {
      const errMsg = focusElem.validationMessage;
      if (errMsg) {
        // input element's native validation failed
        throw new Error(
          `Input validation failed for "${newValue}": ${errMsg}.`
        );
      }

      const colElem = node.getColElem(0)!;

      this._applyChange("edit.apply", node, colElem, focusElem, {
        oldValue: node.title,
        newValue: newValue,
        inputElem: focusElem,
        inputValid: focusElem.checkValidity(),
      }).then((value) => {
        const errMsg = focusElem.validationMessage;
        if (validity && errMsg && value !== false) {
          // Handler called 'inputElem.setCustomValidity()' to signal error
          throw new Error(
            `Edit apply validation failed for "${newValue}": ${errMsg}.`
          );
        }
        // Discard the embedded `<input>`
        // node.logDebug("applyChange:", value, forceClose)
        if (!forceClose && value === false) {
          // Keep open
          return;
        }
        node?.setTitle(newValue);
        // NOTE: At least on Safari, this render call triggers a scroll event
        // probably because the focused input is replaced.
        this.curEditNode?._render({ preventScroll: true });
        this.curEditNode = null;
        this.relatedNode = null;
        this.tree.setFocus(); // restore focus that was in the input element
      });
      // .catch((err) => {
      //   node.logError(err);
      // });
      // Trigger 'change' event for embedded `<input>`
      // focusElem.blur();
    } else {
      // Discard the embedded `<input>`
      // NOTE: At least on Safari, this render call triggers a scroll event
      // probably because the focused input is replaced.
      this.curEditNode?._render({ preventScroll: true });
      this.curEditNode = null;
      this.relatedNode = null;
      // We discarded the <input>, so we have to acquire keyboard focus again
      this.tree.setFocus();
    }
  }
  /**
   * Create a new child or sibling node and start edit mode.
   */
  createNode(
    mode: InsertNodeType = "after",
    node?: WunderbaumNode | null,
    init?: string | WbNodeData
  ) {
    const tree = this.tree;
    node = node ?? (tree.getActiveNode() as WunderbaumNode);
    assert(node, "No node was passed, or no node is currently active.");
    // const validity = this.getPluginOption("validity");

    mode = mode || "prependChild";
    if (init == null) {
      init = { title: "" };
    } else if (typeof init === "string") {
      init = { title: init };
    } else {
      assert(isPlainObject(init), `Expected a plain object: ${init}`);
    }
    // Make sure node is expanded (and loaded) in 'child' mode
    if (
      (mode === "prependChild" || mode === "appendChild") &&
      node?.isExpandable(true)
    ) {
      node.setExpanded().then(() => {
        this.createNode(mode, node, init);
      });
      return;
    }
    const newNode = node.addNode(init, mode);
    newNode.setClass("wb-edit-new");
    this.relatedNode = node;

    // Don't filter new nodes:
    newNode.match = -1;

    newNode.makeVisible({ noAnimation: true }).then(() => {
      this.startEditTitle(newNode);
    });
  }
}
