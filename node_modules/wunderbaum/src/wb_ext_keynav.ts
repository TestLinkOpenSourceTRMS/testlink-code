/*!
 * Wunderbaum - ext-keynav
 * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
 * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
 */

import { KeynavOptionsType, NavModeEnum } from "./types";
import { eventToString } from "./util";
import { Wunderbaum } from "./wunderbaum";
import { WunderbaumNode } from "./wb_node";
import { WunderbaumExtension } from "./wb_extension_base";

const QUICKSEARCH_DELAY = 500;

export class KeynavExtension extends WunderbaumExtension<KeynavOptionsType> {
  constructor(tree: Wunderbaum) {
    super(tree, "keynav", {});
  }

  protected _getEmbeddedInputElem(elem: any): HTMLInputElement | null {
    let input = null;

    if (elem && elem.type != null) {
      input = elem;
    } else {
      // ,[contenteditable]
      const ace = this.tree.getActiveColElem()?.querySelector("input,select");
      if (ace) {
        input = ace as HTMLInputElement;
      }
    }
    return input;
  }

  // /* Return the current cell's embedded input that has keyboard focus. */
  // protected _getFocusedInputElem(): HTMLInputElement | null {
  //   const ace = this.tree
  //     .getActiveColElem()
  //     ?.querySelector<HTMLInputElement>("input:focus,select:focus");
  //   return ace || null;
  // }

  /* Return true if the current cell's embedded input has keyboard focus. */
  protected _isCurInputFocused(): boolean {
    const ace = this.tree
      .getActiveColElem()
      ?.querySelector("input:focus,select:focus");
    return !!ace;
  }

  onKeyEvent(data: any): boolean | undefined {
    const event = data.event;
    const tree = this.tree;
    const opts = data.options;
    const activate = !event.ctrlKey || opts.autoActivate;
    const curInput = this._getEmbeddedInputElem(event.target);
    const inputHasFocus = curInput && this._isCurInputFocused();
    const navModeOption = opts.navigationModeOption as NavModeEnum;

    let focusNode,
      eventName = eventToString(event),
      node = data.node as WunderbaumNode,
      handled = true;

    // tree.log(`onKeyEvent: ${eventName}, curInput`, curInput);
    if (!tree.isEnabled()) {
      // tree.logDebug(`onKeyEvent ignored for disabled tree: ${eventName}`);
      return false;
    }

    // Let callback prevent default processing
    if (tree._callEvent("keydown", data) === false) {
      return false;
    }

    // Let ext-edit trigger editing
    if (tree._callMethod("edit._preprocessKeyEvent", data) === false) {
      return false;
    }

    // Set focus to active (or first node) if no other node has the focus yet
    if (!node) {
      const currentNode = tree.getFocusNode() || tree.getActiveNode();
      const firstNode = tree.getFirstChild();

      if (!currentNode && firstNode && eventName === "ArrowDown") {
        firstNode.logInfo("Keydown: activate first node.");
        firstNode.setActive();
        return;
      }

      focusNode = currentNode || firstNode;
      if (focusNode) {
        focusNode.setFocus();
        node = tree.getFocusNode()!;
        node.logInfo("Keydown: force focus on active node.");
      }
    }
    const isColspan = node.isColspan();

    // Paging nodes ("More..."): Enter and Space load the next page (row and
    // cell mode)
    if (node.isPagingNode() && (eventName === "Enter" || eventName === " ")) {
      node._clickPaging(event);
      event.preventDefault();
      return;
    }

    if (tree.isRowNav()) {
      // -----------------------------------------------------------------------
      // --- Row Mode ---
      // -----------------------------------------------------------------------
      if (inputHasFocus) {
        // If editing an embedded input control, let the control handle all
        // keys. Only Enter and Escape should apply / discard, but keep the
        // keyboard focus.
        switch (eventName) {
          case "Enter":
            curInput.blur();
            tree.setFocus();
            break;
          case "Escape":
            node._render();
            tree.setFocus();
            break;
        }
        return;
      }
      // --- Quick-Search
      // A space typed quickly continues a running quick-search term ("lab c"),
      // if a title matches. Otherwise it toggles the selection as usual.
      const continuesTerm =
        eventName === " " &&
        !!tree.lastQuicksearchTerm &&
        Date.now() - tree.lastQuicksearchTime <= QUICKSEARCH_DELAY &&
        !!tree.findNextNode(
          tree.lastQuicksearchTerm + " ",
          tree.getActiveNode()
        );
      if (
        opts.quicksearch &&
        eventName.length === 1 &&
        (/^\w$/.test(eventName) || continuesTerm) &&
        !curInput
      ) {
        // Allow to search for longer streaks if typed in quickly
        const stamp = Date.now();
        if (stamp - tree.lastQuicksearchTime > QUICKSEARCH_DELAY) {
          tree.lastQuicksearchTerm = "";
        }
        tree.lastQuicksearchTime = stamp;
        tree.lastQuicksearchTerm += eventName;
        const matchNode = tree.findNextNode(
          tree.lastQuicksearchTerm,
          tree.getActiveNode()
        );
        if (matchNode) {
          // Move the focus node too, like keyboard navigation does
          matchNode.setFocus();
          matchNode.setActive(true, { event: event });
        }
        event.preventDefault();
        return;
      }

      // Pre-Evaluate expand/collapse action for LEFT/RIGHT
      switch (eventName) {
        case "Enter":
          if (node.isActive()) {
            if (node.isExpanded()) {
              eventName = "Subtract"; // callapse
            } else if (node.isExpandable(true)) {
              eventName = "Add"; // expand
            }
          }
          break;
        case "ArrowLeft":
          if (node.expanded) {
            eventName = "Subtract"; // collapse
          }
          break;
        case "ArrowRight":
          if (!node.expanded && node.isExpandable(true)) {
            eventName = "Add"; // expand
          } else if (
            navModeOption === NavModeEnum.startCell ||
            navModeOption === NavModeEnum.startRow
          ) {
            event.preventDefault();
            tree.setCellNav();
            return false;
          }
          break;
      }

      // Standard navigation (row mode)
      switch (eventName) {
        case "+":
        case "Add":
          // case "=": // 187: '+' @ Chrome, Safari
          node.setExpanded(true);
          break;
        case "-":
        case "Subtract":
          node.setExpanded(false);
          break;
        case " ": // Space
          if (node.getOption("checkbox")) {
            node.toggleSelected();
          } else {
            node.setActive(true, { event: event });
          }
          break;
        case "Enter":
          node.setActive(true, { event: event });
          break;
        case "ArrowDown":
        case "ArrowLeft":
        case "ArrowRight":
        case "ArrowUp":
        case "Backspace":
        case "Control+End":
        case "Control+Home":
        case "Meta+ArrowDown":
        case "Meta+ArrowUp":
        case "PageDown":
        case "PageUp":
          node.navigate(eventName, { activate: activate, event: event });
          break;
        case "End":
        case "Home":
          // Row mode has no current column, so Home/End move to the first/last
          // visible row (WAI-ARIA tree grid pattern, row focus). In cell mode
          // they keep moving to the first/last column.
          node.navigate(eventName === "Home" ? "first" : "last", {
            activate: activate,
            event: event,
          });
          break;
        default:
          handled = false;
      }
    } else {
      // -----------------------------------------------------------------------
      // --- Cell Mode ---
      // -----------------------------------------------------------------------
      // // Standard navigation (cell mode)
      // if (isCellEditMode && INPUT_BREAKOUT_KEYS.has(eventName)) {
      // }
      // const curInput = this._getEmbeddedInputElem(null);
      const curInputType = curInput ? curInput.type || curInput.tagName : "";
      // const inputHasFocus = curInput && this._isCurInputFocused();
      const inputCanFocus = curInput && curInputType !== "checkbox";

      if (inputHasFocus) {
        if (eventName === "Escape") {
          node.logDebug(`Reset focused input on Escape`);
          // Discard changes and reset input validation state
          curInput.setCustomValidity("");
          node._render();
          // Keep cell-nav mode
          tree.setFocus();
          tree.setColumn(tree.activeColIdx);
          return;
          // } else if (!INPUT_BREAKOUT_KEYS.has(eventName)) {
        } else if (eventName !== "Enter") {
          if (curInput && curInput.checkValidity && !curInput.checkValidity()) {
            // Invalid input: ignore all keys except Enter and Escape
            node.logDebug(`Ignored ${eventName} inside invalid input`);
            return false;
          }
          // Let current `<input>` handle it
          node.logDebug(`Ignored ${eventName} inside focused input`);
          return;
        }
        // const curInputType = curInput.type || curInput.tagName;
        // const breakoutKeys = INPUT_KEYS[curInputType];
        // if (!breakoutKeys.includes(eventName)) {
        //   node.logDebug(`Ignored ${eventName} inside ${curInputType} input`);
        //   return;
        // }
      } else if (curInput) {
        // On a cell that has an embedded, unfocused <input>
        if (eventName.length === 1 && inputCanFocus) {
          // Typing a single char
          curInput.focus();
          curInput.value = "";
          node.logDebug(`Focus input: ${eventName}`);
          return false;
        }
      }
      if (eventName === "Tab") {
        eventName = "ArrowRight";
        handled = true;
      } else if (eventName === "Shift+Tab") {
        eventName = tree.activeColIdx > 0 ? "ArrowLeft" : "";
        handled = true;
      }

      switch (eventName) {
        case "+":
        case "Add":
          // case "=": // 187: '+' @ Chrome, Safari
          node.setExpanded(true);
          break;
        case "-":
        case "Subtract":
          node.setExpanded(false);
          break;
        case " ": // Space
          if (tree.activeColIdx === 0 && node.getOption("checkbox")) {
            node.toggleSelected();
            handled = true;
          } else if (curInput && curInputType === "checkbox") {
            curInput.click();
            // toggleCheckbox(curInput)
            // new Event("change")
            // curInput.change
            handled = true;
          }
          break;
        case "F2":
          if (curInput && !inputHasFocus && inputCanFocus) {
            curInput.focus();
            handled = true;
          }
          break;
        case "Enter":
          tree.setFocus(); // Blur prev. input if any
          if ((tree.activeColIdx === 0 || isColspan) && node.isExpandable()) {
            node.setExpanded(!node.isExpanded());
            handled = true;
          } else if (curInput && !inputHasFocus && inputCanFocus) {
            curInput.focus();
            handled = true;
          }
          break;
        case "Escape":
          tree.setFocus(); // Blur prev. input if any
          node.log(`keynav: focus tree...`);
          if (tree.isCellNav() && navModeOption !== NavModeEnum.cell) {
            node.log(`keynav: setCellNav(false)`);
            tree.setCellNav(false); // row-nav mode
            tree.setFocus(); //
            handled = true;
          }
          break;
        case "ArrowLeft":
          tree.setFocus(); // Blur prev. input if any
          if (isColspan && node.isExpanded()) {
            node.setExpanded(false);
          } else if (!isColspan && tree.activeColIdx > 0) {
            tree.setColumn(tree.activeColIdx - 1);
          } else if (navModeOption !== NavModeEnum.cell) {
            tree.setCellNav(false); // row-nav mode
          }
          handled = true;
          break;
        case "ArrowRight":
          tree.setFocus(); // Blur prev. input if any
          if (isColspan && !node.isExpanded()) {
            node.setExpanded();
          } else if (
            !isColspan &&
            tree.activeColIdx < tree.columns.length - 1
          ) {
            tree.setColumn(tree.activeColIdx + 1);
          }
          handled = true;
          break;
        case "Home": // Generated by [Fn] + ArrowLeft on Mac
          // case "Meta+ArrowLeft":
          tree.setFocus(); // Blur prev. input if any
          if (!isColspan && tree.activeColIdx > 0) {
            tree.setColumn(0);
          }
          handled = true;
          break;
        case "End": // Generated by [Fn] + ArrowRight on Mac
          // case "Meta+ArrowRight":
          tree.setFocus(); // Blur prev. input if any
          if (!isColspan && tree.activeColIdx < tree.columns.length - 1) {
            tree.setColumn(tree.columns.length - 1);
          }
          handled = true;
          break;
        case "ArrowDown":
        case "ArrowUp":
        case "Backspace":
        case "Control+End": // Generated by Control + [Fn] + ArrowRight on Mac
        case "Control+Home": // Generated by Control + [Fn] + Arrowleft on Mac
        case "Meta+ArrowDown": // [⌘] + ArrowDown on Mac
        case "Meta+ArrowUp": // [⌘] + ArrowUp on Mac
        case "PageDown": // Generated by [Fn] + ArrowDown on Mac
        case "PageUp": // Generated by [Fn] + ArrowUp on Mac
          node.navigate(eventName, { activate: activate, event: event });
          // if (isCellEditMode) {
          //   this._getEmbeddedInputElem(null, true); // set focus to input
          // }
          handled = true;
          break;
        default:
          handled = false;
      }
    }

    if (handled) {
      event.preventDefault();
    }
    return;
  }
}
