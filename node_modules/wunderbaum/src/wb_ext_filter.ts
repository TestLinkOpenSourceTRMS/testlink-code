/*!
 * Wunderbaum - ext-filter
 * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
 * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
 */

import {
  assert,
  elemFromSelector,
  escapeHtml,
  escapeRegex,
  extend,
  onEvent,
} from "./util";
import {
  FilterConnectType,
  FilterNodesOptions,
  FilterOptionsType,
  NodeFilterCallback,
  NodeStatusType,
} from "./types";
import { Wunderbaum } from "./wunderbaum";
import { WunderbaumNode } from "./wb_node";
import { WunderbaumExtension } from "./wb_extension_base";
import { debounce } from "./debounce";

const START_MARKER = "\uFFF7";
const END_MARKER = "\uFFF8";
const RE_START_MARKER = new RegExp(escapeRegex(START_MARKER), "g");
const RE_END_MARTKER = new RegExp(escapeRegex(END_MARKER), "g");

export class FilterExtension extends WunderbaumExtension<FilterOptionsType> {
  public queryInput: HTMLInputElement | null = null;
  public prevButton: HTMLElement | HTMLAnchorElement | null = null;
  public nextButton: HTMLElement | HTMLAnchorElement | null = null;
  public modeButton: HTMLButtonElement | null = null;
  public matchInfoElem: HTMLElement | null = null;
  public lastFilterArgs: IArguments | null = null;

  constructor(tree: Wunderbaum) {
    super(tree, "filter", {
      autoApply: true, // Re-apply last filter if lazy data is loaded
      autoExpand: false, // Expand all branches that contain matches while filtered
      matchBranch: false, // Whether to implicitly match all children of matched nodes
      connect: null, // Element or selector of an input control for filter query strings
      fuzzy: false, // Match single characters in order, e.g. 'fb' will match 'FooBar'
      hideExpanders: false, // Hide expanders if all child nodes are hidden by filter
      highlight: true, // Highlight matches by wrapping inside <mark> tags
      leavesOnly: false, // Match end nodes only
      mode: "dim", // Grayout unmatched nodes (pass "hide" to remove unmatched node instead)
      noData: true, // Display a 'no data' status node if result is empty
    });
  }

  init() {
    super.init();
    const connect: FilterConnectType = this.getPluginOption("connect");
    if (connect) {
      this._connectControls();
    }
  }

  setPluginOption(name: string, value: any): void {
    super.setPluginOption(name, value);
    switch (name) {
      case "mode":
        this.tree.filterMode =
          value === "hide" ? "hide" : value === "mark" ? "mark" : "dim";
        this.tree.updateFilter();
        break;
    }
  }

  _updatedConnectedControls() {
    const filterActive = this.tree.filterMode !== null;
    const activeNode = this.tree.getActiveNode();
    const matchCount = filterActive ? this.countMatches() : 0;
    const strings = this.treeOpts.strings;
    let matchIdx: string | number = "?";

    if (this.matchInfoElem) {
      if (filterActive) {
        let info;
        if (matchCount === 0) {
          info = strings.noMatch;
        } else if (activeNode && activeNode.match! >= 1) {
          matchIdx = activeNode.match ?? "?";
          info = strings.matchIndex;
        } else {
          info = strings.queryResult;
        }
        info = info
          .replace("${count}", this.tree.count().toLocaleString())
          .replace("${match}", "" + matchIdx)
          .replace("${matches}", matchCount.toLocaleString());
        this.matchInfoElem.textContent = info;
      } else {
        this.matchInfoElem.textContent = "";
      }
    }

    if (this.nextButton instanceof HTMLButtonElement) {
      this.nextButton.disabled = !matchCount;
    }
    if (this.prevButton instanceof HTMLButtonElement) {
      this.prevButton.disabled = !matchCount;
    }
    if (this.modeButton) {
      this.modeButton.disabled = !filterActive;
      this.modeButton.classList.toggle(
        "wb-filter-hide",
        this.tree.filterMode === "hide"
      );
    }
  }
  _connectControls() {
    const tree = this.tree;
    const connect: FilterConnectType = this.getPluginOption("connect");
    if (!connect) {
      return;
    }
    this.queryInput = elemFromSelector(connect.inputElem);
    if (!this.queryInput) {
      throw new Error(`Invalid 'filter.connect' option: ${connect.inputElem}.`);
    }
    this.prevButton = elemFromSelector(connect.prevButton!);
    this.nextButton = elemFromSelector(connect.nextButton!);
    this.modeButton = elemFromSelector(connect.modeButton!);
    this.matchInfoElem = elemFromSelector(connect.matchInfoElem!);
    if (this.prevButton) {
      onEvent(this.prevButton, "click", () => {
        tree.findRelatedNode(
          tree.getActiveNode() || tree.getFirstChild()!,
          "prevMatch"
        );
        this._updatedConnectedControls();
      });
    }
    if (this.nextButton) {
      onEvent(this.nextButton, "click", () => {
        tree.findRelatedNode(
          tree.getActiveNode() || tree.getFirstChild()!,
          "nextMatch"
        );
        this._updatedConnectedControls();
      });
    }
    if (this.modeButton) {
      onEvent(this.modeButton, "click", (e) => {
        if (!this.tree.filterMode) {
          return;
        }
        this.setPluginOption(
          "mode",
          tree.filterMode === "dim" ? "hide" : "dim"
        );
      });
    }
    onEvent(
      this.queryInput,
      "input",
      debounce((e) => {
        this.filterNodes(this.queryInput!.value.trim(), {});
      }, 700)
    );
    this._updatedConnectedControls();
  }

  _applyFilterNoUpdate(
    filter: string | RegExp | NodeFilterCallback,
    _opts: FilterNodesOptions
  ): number {
    return this.tree.runWithDeferredUpdate(() => {
      return this._applyFilterImpl(filter, _opts);
    });
  }

  _applyFilterImpl(
    filter: string | RegExp | NodeFilterCallback,
    _opts: FilterNodesOptions
  ): number {
    let //temp,
      count = 0;
    const start = Date.now();
    const tree = this.tree;
    const treeOpts = tree.options;
    const prevAutoCollapse = treeOpts.autoCollapse;
    // Use default options from `tree.options.filter`, but allow to override them
    const opts: FilterOptionsType = extend({}, treeOpts.filter, _opts);
    const hideMode = opts.mode === "hide";
    const matchBranch = !!opts.matchBranch;
    const leavesOnly = !!opts.leavesOnly && !matchBranch;

    let filterRegExp: RegExp;
    let highlightRegExp: RegExp;

    // Default to 'match title substring (case insensitive)'
    if (typeof filter === "string" || filter instanceof RegExp) {
      if (filter === "") {
        tree.logInfo(
          "Passing an empty string as a filter is handled as clearFilter()."
        );
        this.clearFilter();
        return 0;
      }
      if (opts.fuzzy) {
        assert(typeof filter === "string", "fuzzy filter must be a string");
        // See https://codereview.stackexchange.com/questions/23899/faster-javascript-fuzzy-string-matching-function/23905#23905
        // and http://www.quora.com/How-is-the-fuzzy-search-algorithm-in-Sublime-Text-designed
        // and http://www.dustindiaz.com/autocomplete-fuzzy-matching
        const matchReString = (<string>filter)
          .split("")
          // Escaping the `filter` will not work because,
          // it gets further split into individual characters. So,
          // escape each character after splitting
          .map(escapeRegex)
          .reduce(function (a, b) {
            // create capture groups for parts that comes before
            // the character
            return a + "([^" + b + "]*)" + b;
          }, "");
        filterRegExp = new RegExp(matchReString, "i");
        // highlightRegExp = new RegExp(escapeRegex(filter), "gi");
      } else if (filter instanceof RegExp) {
        filterRegExp = filter;
        highlightRegExp = filter;
      } else {
        const matchReString = escapeRegex(filter); // make sure a '.' is treated literally
        filterRegExp = new RegExp(matchReString, "i");
        highlightRegExp = new RegExp(matchReString, "gi");
      }
      tree.logDebug(`Filtering nodes by '${filterRegExp}'`);
      // const re = new RegExp(match, "i");
      // const reHighlight = new RegExp(escapeRegex(filter), "gi");
      filter = (node: WunderbaumNode) => {
        if (!node.title) {
          return false;
        }
        // let text = escapeTitles ? node.title : extractHtmlText(node.title);
        const text = node.title;
        // `.match` instead of `.test` to get the capture groups
        // const res = text.match(filterRegExp);
        const res = filterRegExp.exec(text);

        if (res && opts.highlight) {
          let highlightString: string;

          if (opts.fuzzy) {
            highlightString = _markFuzzyMatchedChars(text, res, true);
          } else {
            // #740: we must not apply the marks to escaped entity names, e.g. `&quot;`
            // Use some exotic characters to mark matches:
            highlightString = text.replace(highlightRegExp, function (s) {
              return START_MARKER + s + END_MARKER;
            });
          }
          // now we can escape the title...
          node.titleWithHighlight = escapeHtml(highlightString)
            // ... and finally insert the desired `<mark>` tags
            .replace(RE_START_MARKER, "<mark>")
            .replace(RE_END_MARTKER, "</mark>");
        }
        return !!res;
      };
    }

    tree.filterMode = opts.mode ?? "dim";
    // eslint-disable-next-line prefer-rest-params
    this.lastFilterArgs = arguments;

    tree.element.classList.toggle("wb-ext-filter-hide", !!hideMode);
    tree.element.classList.toggle("wb-ext-filter-dim", opts.mode === "dim");
    tree.element.classList.toggle(
      "wb-ext-filter-hide-expanders",
      !!opts.hideExpanders
    );
    // Reset current filter
    tree.root.subMatchCount = 0;
    tree.visit((node) => {
      delete node.match;
      delete node.titleWithHighlight;
      node.subMatchCount = 0;
    });
    tree.setStatus(NodeStatusType.ok);

    // Adjust node.hide, .match, and .subMatchCount properties
    treeOpts.autoCollapse = false; // #528

    tree.visit((node) => {
      if (leavesOnly && node.children != null) {
        return;
      }
      let res = (<NodeFilterCallback>filter)(node);

      if (res === "skip") {
        node.visit(function (c) {
          c.match = undefined;
        }, true);
        return "skip";
      }

      let matchedByBranch = false;
      if ((matchBranch || res === "branch") && node.parent.match) {
        res = true;
        matchedByBranch = true;
      }

      if (res) {
        count++;
        node.match = count;
        node.visitParents((p) => {
          if (p !== node) {
            p.subMatchCount! += 1;
          }
          // Expand match (unless this is no real match, but only a node in a matched branch)
          if (opts.autoExpand && !matchedByBranch && !p.expanded) {
            p.setExpanded(true, {
              noAnimation: true,
              noEvents: true,
            });
            p._filterAutoExpanded = true;
          }
        }, true);
      }
    });
    treeOpts.autoCollapse = prevAutoCollapse;

    if (count === 0 && opts.noData && hideMode) {
      if (typeof opts.noData === "string") {
        tree.root.setStatus(NodeStatusType.noData, { message: opts.noData });
      } else {
        tree.root.setStatus(NodeStatusType.noData);
      }
    }
    // Redraw whole tree
    tree.logDebug(
      `Filter '${filter}' found ${count} nodes in ${Date.now() - start} ms.`
    );
    this._updatedConnectedControls();

    return count;
  }

  /**
   * [ext-filter] Dim or hide nodes.
   */
  filterNodes(
    filter: string | RegExp | NodeFilterCallback,
    options: FilterNodesOptions
  ): number {
    return this._applyFilterNoUpdate(filter, options);
  }

  /**
   * [ext-filter] Dim or hide whole branches.
   * @deprecated Use {@link filterNodes} instead and set `options.matchBranch: true`.
   */
  filterBranches(
    filter: string | NodeFilterCallback,
    options: FilterNodesOptions
  ) {
    assert(
      options.matchBranch === undefined,
      "filterBranches() is deprecated."
    );
    this.tree.logDeprecate("filterBranches()", {
      since: "0.9.0",
      hint: "Use `filterNodes` instead and set `options.matchBranch: true`",
    });
    options.matchBranch = true;
    return this._applyFilterNoUpdate(filter, options);
  }

  /**
   * [ext-filter] Return the number of matched nodes.
   */
  countMatches(): number {
    let n = 0;
    this.tree.visit((node) => {
      if (node.match && !node.statusNodeType) {
        n++;
      }
    });
    return n;
  }

  /**
   * [ext-filter] Re-apply current filter.
   */
  updateFilter() {
    const tree = this.tree;
    if (
      tree.filterMode &&
      this.lastFilterArgs &&
      tree.options.filter?.autoApply
    ) {
      // eslint-disable-next-line prefer-spread
      this._applyFilterNoUpdate.apply(this, <any>this.lastFilterArgs);
    } else {
      tree.logWarn("updateFilter(): no filter active.");
    }
    this._updatedConnectedControls();
  }

  /**
   * [ext-filter] Reset the filter.
   */
  clearFilter() {
    const tree = this.tree;
    tree.enableUpdate(false);

    tree.setStatus(NodeStatusType.ok);
    // we also counted root node's subMatchCount
    delete tree.root.match;
    delete tree.root.subMatchCount;

    tree.visit((node) => {
      delete node.match;
      delete node.subMatchCount;
      delete node.titleWithHighlight;
      if (node._filterAutoExpanded && node.expanded) {
        node.setExpanded(false, {
          noAnimation: true,
          noEvents: true,
        });
      }
      delete node._filterAutoExpanded;
    });
    tree.filterMode = null;
    this.lastFilterArgs = null;
    tree.element.classList.remove(
      // "wb-ext-filter",
      "wb-ext-filter-dim",
      "wb-ext-filter-hide"
    );
    this._updatedConnectedControls();

    tree.enableUpdate(true);
  }
}

/**
 * @description Marks the matching characters of `text` either by `mark` or
 * by exotic*Chars (if `escapeTitles` is `true`) based on `matches`
 * which is an array of matching groups.
 * @param {string} text
 * @param {RegExpMatchArray} matches
 */
function _markFuzzyMatchedChars(
  text: string,
  matches: RegExpMatchArray,
  escapeTitles = true
) {
  const matchingIndices = [];
  // get the indices of matched characters (Iterate through `RegExpMatchArray`)
  for (
    let _matchingArrIdx = 1;
    _matchingArrIdx < matches.length;
    _matchingArrIdx++
  ) {
    const _mIdx: number =
      // get matching char index by cumulatively adding
      // the matched group length
      matches[_matchingArrIdx].length +
      (_matchingArrIdx === 1 ? 0 : 1) +
      (matchingIndices[matchingIndices.length - 1] || 0);
    matchingIndices.push(_mIdx);
  }
  // Map each `text` char to its position and store in `textPoses`.
  const textPoses = text.split("");
  if (escapeTitles) {
    // If escaping the title, then wrap the matching char within exotic chars
    matchingIndices.forEach(function (v) {
      textPoses[v] = START_MARKER + textPoses[v] + END_MARKER;
    });
  } else {
    // Otherwise, Wrap the matching chars within `mark`.
    matchingIndices.forEach(function (v) {
      textPoses[v] = "<mark>" + textPoses[v] + "</mark>";
    });
  }
  // Join back the modified `textPoses` to create final highlight markup.
  return textPoses.join("");
}
