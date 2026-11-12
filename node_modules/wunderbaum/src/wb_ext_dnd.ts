/*!
 * Wunderbaum - ext-dnd
 * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
 * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
 */
import * as util from "./util";
import { EventCallbackType, onEvent } from "./util";
import { Wunderbaum } from "./wunderbaum";
import { WunderbaumExtension } from "./wb_extension_base";
import { WunderbaumNode } from "./wb_node";
import {
  DndOptionsType,
  DropEffectType,
  DropRegionType,
  DropRegionTypeSet,
} from "./types";
import { DebouncedFunction, throttle } from "./debounce";

const nodeMimeType = "application/x-wunderbaum-node";

export class DndExtension extends WunderbaumExtension<DndOptionsType> {
  // public dropMarkerElem?: HTMLElement;
  protected srcNode: WunderbaumNode | null = null;
  protected lastTargetNode: WunderbaumNode | null = null;
  protected lastEnterStamp = 0;
  protected lastAllowedDropRegions: DropRegionTypeSet | null = null;
  protected lastDropEffect: DropEffectType | null = null;
  protected lastDropRegion: DropRegionType | false = false;
  protected currentScrollDir: number = 0;
  // protected autoScrollThrottled: DebouncedFunction<(pageY: number) => number>;
  protected applyScrollDirThrottled: DebouncedFunction<() => void>;

  constructor(tree: Wunderbaum) {
    super(tree, "dnd", {
      autoExpandMS: 1500, // Expand nodes after n milliseconds of hovering
      // dropMarkerInsertOffsetX: -16, // Additional offset for drop-marker with hitMode = "before"/"after"
      // dropMarkerOffsetX: -24, // Absolute position offset for .fancytree-drop-marker relatively to ..fancytree-title (icon/img near a node accepting drop)
      // #1021 `document.body` is not available yet
      // dropMarkerParent: "body", // Root Container used for drop marker (could be a shadow root)
      multiSource: false, // true: Drag multiple (i.e. selected) nodes. Also a callback() is allowed
      effectAllowed: "all", // Restrict the possible cursor shapes and modifier operations (can also be set in the dragStart event)
      dropEffectDefault: "move", // Default dropEffect ('copy', 'link', or 'move') when no modifier is pressed (override in drag, dragOver).
      guessDropEffect: true, // Calculate from `effectAllowed` and modifier keys)
      preventForeignNodes: false, // Prevent dropping nodes from different Wunderbaum trees
      preventLazyParents: true, // Prevent dropping items on unloaded lazy Wunderbaum tree nodes
      preventNonNodes: false, // Prevent dropping items other than Wunderbaum tree nodes
      preventRecursion: true, // Prevent dropping nodes on own descendants
      preventSameParent: false, // Prevent dropping nodes under same direct parent
      preventVoidMoves: true, // Prevent dropping nodes 'before self', etc. (move only)
      serializeClipboardData: true, // Serialize node data to dataTransfer object
      scroll: true, // Enable auto-scrolling while dragging
      scrollSensitivity: 20, // Active top/bottom margin in pixel
      // scrollnterval: 50, // Generate event every 50 ms
      scrollSpeed: 5, // Scroll pixel per 50 ms
      // setTextTypeJson: false, // Allow dragging of nodes to different IE windows
      sourceCopyHook: null, // Optional callback passed to `toDict` on dragStart @since 2.38
      // Events (drag support)
      dragStart: null, // Callback(sourceNode, data), return true, to enable dnd drag
      drag: null, // Callback(sourceNode, data)
      dragEnd: null, // Callback(sourceNode, data)
      // Events (drop support)
      dragEnter: null, // Callback(targetNode, data), return true, to enable dnd drop
      dragOver: null, // Callback(targetNode, data)
      dragExpand: null, // Callback(targetNode, data), return false to prevent autoExpand
      drop: null, // Callback(targetNode, data)
      dragLeave: null, // Callback(targetNode, data)
    });
    this.applyScrollDirThrottled = throttle(this._applyScrollDir, 50);
  }

  init() {
    super.init();

    // Store the current scroll parent, which may be the tree
    // container, any enclosing div, or the document.
    // #761: scrollParent() always needs a container child
    // $temp = $("<span>").appendTo(this.$container);
    // this.$scrollParent = $temp.scrollParent();
    // $temp.remove();
    const tree = this.tree;
    const dndOpts = tree.options.dnd;

    // Enable drag support if dragStart() is specified:
    if (dndOpts.dragStart) {
      onEvent(
        tree.element,
        "dragstart drag dragend",
        (<EventCallbackType>this.onDragEvent).bind(this)
      );
    }
    // Enable drop support if dragEnter() is specified:
    if (dndOpts.dragEnter) {
      onEvent(
        tree.element,
        "dragenter dragover dragleave drop",
        (<EventCallbackType>this.onDropEvent).bind(this)
      );
    }
  }

  /** Cleanup classes after target node is no longer hovered. */
  protected _leaveNode(): void {
    // We remove the marker on dragenter from the previous target:
    const ltn = this.lastTargetNode;
    this.lastEnterStamp = 0;
    if (ltn) {
      ltn.setClass(
        "wb-drop-target wb-drop-over wb-drop-after wb-drop-before",
        false
      );
      this.lastTargetNode = null;
    }
  }

  /** */
  protected unifyDragover(res: any): DropRegionTypeSet | false {
    if (res === false) {
      return false;
    } else if (res instanceof Set) {
      return res.size > 0 ? res : false;
    } else if (res === true) {
      return new Set<DropRegionType>(["over", "before", "after"]);
    } else if (typeof res === "string" || util.isArray(res)) {
      res = <DropRegionTypeSet>util.toSet(res);
      return res.size > 0 ? res : false;
    }
    throw new Error("Unsupported drop region definition: " + res);
  }

  /**
   * Calculates the drop region based on the drag event and the allowed drop regions.
   */
  protected _calcDropRegion(
    e: DragEvent,
    allowed: DropRegionTypeSet | null
  ): DropRegionType | false {
    const rowHeight = this.tree.options.rowHeightPx;
    const dy = e.offsetY;

    if (!allowed) {
      return false;
    } else if (allowed.size === 3) {
      return dy < 0.25 * rowHeight
        ? "before"
        : dy > 0.75 * rowHeight
          ? "after"
          : "over";
    } else if (allowed.size === 1 && allowed.has("over")) {
      return "over";
    } else {
      // Only 'before' and 'after':
      return dy > rowHeight / 2 ? "after" : "before";
    }
    // return "over";
  }
  /**
   * Guess drop effect (copy/link/move) using opinionated conventions.
   *
   * Default: dnd.dropEffectDefault
   */
  protected _guessDropEffect(e: DragEvent): DropEffectType {
    // const nativeDropEffect = e.dataTransfer?.dropEffect;

    // if (nativeDropEffect && nativeDropEffect !== "none") {
    //   return nativeDropEffect;
    // }
    const dndOpts: DndOptionsType = this.treeOpts.dnd;
    const ea = dndOpts.effectAllowed ?? "all";
    const canCopy = ["all", "copy", "copyLink", "copyMove"].includes(ea);
    const canLink = ["all", "link", "copyLink", "linkMove"].includes(ea);
    const canMove = ["all", "move", "copyMove", "linkMove"].includes(ea);

    let res = dndOpts.dropEffectDefault!;

    if (dndOpts.guessDropEffect) {
      if (util.isMac) {
        if (e.altKey && canCopy) {
          res = "copy";
        }
        if (e.metaKey && canMove) {
          res = "move"; // command key
        }
        if (e.altKey && e.metaKey && canLink) {
          res = "link";
        }
      } else {
        if (e.ctrlKey && canCopy) {
          res = "copy";
        }
        if (e.shiftKey && canMove) {
          res = "move";
        }
        if (e.altKey && canLink) {
          res = "link";
        }
      }
    }
    return res;
  }

  /** Don't allow void operation ('drop on self').*/
  protected _isVoidDrop(
    targetNode: WunderbaumNode,
    srcNode: WunderbaumNode | null,
    dropRegion: DropRegionType | false
  ): boolean {
    // this.tree.logDebug(
    //   `_isVoidDrop: ${srcNode} -> ${dropRegion} ${targetNode}`
    // );
    // TODO: should be checked on  move only
    if (!this.treeOpts.dnd!.preventVoidMoves || !srcNode) {
      return false;
    }
    if (
      (dropRegion === "before" && targetNode === srcNode.getNextSibling()) ||
      (dropRegion === "after" && targetNode === srcNode.getPrevSibling())
    ) {
      // this.tree.logDebug("Prevented before/after self");
      return true;
    }
    // Don't allow dropping nodes on own parent (or self)
    return srcNode === targetNode || srcNode.parent === targetNode;
  }

  /* Implement auto scrolling when drag cursor is in top/bottom area of scroll parent. */
  protected _applyScrollDir(): void {
    if (this.isDragging() && this.currentScrollDir) {
      const dndOpts = this.tree.options.dnd;
      const sp = this.tree.element; // scroll parent
      const scrollTop = sp.scrollTop;
      if (this.currentScrollDir < 0) {
        sp.scrollTop = Math.max(0, scrollTop - dndOpts.scrollSpeed!);
      } else if (this.currentScrollDir > 0) {
        sp.scrollTop = scrollTop + dndOpts.scrollSpeed!;
      }
    }
  }
  /* Implement auto scrolling when drag cursor is in top/bottom area of scroll parent. */
  protected _autoScroll(viewportY: number): number {
    const tree = this.tree;
    const dndOpts = tree.options.dnd;
    const sensitivity = dndOpts.scrollSensitivity;
    const sp = tree.element; // scroll parent
    const headerHeight = tree.headerElement.clientHeight; // May be 0
    // const height = sp.clientHeight - headerHeight;
    // const height = sp.offsetHeight + headerHeight;
    const height = sp.offsetHeight;
    const scrollTop = sp.scrollTop;

    // tree.logDebug(
    //   `autoScroll: height=${height}, scrollTop=${scrollTop}, viewportY=${viewportY}`
    // );

    this.currentScrollDir = 0;

    if (
      scrollTop > 0 &&
      viewportY > 0 &&
      viewportY <= sensitivity! + headerHeight
    ) {
      // Mouse in top 20px area: scroll up
      // sp.scrollTop = Math.max(0, scrollTop - dndOpts.scrollSpeed);
      this.currentScrollDir = -1;
    } else if (
      scrollTop < sp.scrollHeight - height &&
      viewportY >= height - sensitivity!
    ) {
      // Mouse in bottom 20px area: scroll down
      // sp.scrollTop = scrollTop + dndOpts.scrollSpeed;
      this.currentScrollDir = +1;
    }
    if (this.currentScrollDir) {
      this.applyScrollDirThrottled();
    }
    return sp.scrollTop - scrollTop;
  }

  /** Return true if a drag operation currently in progress. */
  isDragging(): boolean {
    return !!this.srcNode;
  }

  /**
   * Handle dragstart, drag and dragend events for the source node.
   */
  protected onDragEvent(e: DragEvent) {
    const dndOpts: DndOptionsType = this.treeOpts.dnd;
    const srcNode = Wunderbaum.getNode(e);

    if (!srcNode) {
      this.tree.logWarn(`onDragEvent.${e.type}: no node`);
      return;
    }
    if (["dragstart", "dragend"].includes(e.type)) {
      this.tree.logDebug(`onDragEvent.${e.type} srcNode: ${srcNode}`, e);
    }

    // --- dragstart ---
    if (e.type === "dragstart") {
      // Set a default definition of allowed effects
      e.dataTransfer!.effectAllowed = dndOpts.effectAllowed!; //"copyMove"; // "all";
      if (srcNode.isEditingTitle()) {
        srcNode.logDebug("Prevented dragging node in edit mode.");
        e.preventDefault();
        return false;
      }
      // Let user cancel the drag operation, override effectAllowed, etc.:
      const res = srcNode._callEvent("dnd.dragStart", { event: e });
      if (!res) {
        e.preventDefault();
        return false;
      }
      const nodeData = srcNode.toDict(true, (n: any) => {
        // We don't want to reuse the key on drop:
        n._orgKey = n.key;
        delete n.key;
      });
      nodeData._treeId = srcNode.tree.id;

      if (dndOpts.serializeClipboardData) {
        if (typeof dndOpts.serializeClipboardData === "function") {
          e.dataTransfer!.setData(
            nodeMimeType,
            dndOpts.serializeClipboardData(nodeData, srcNode)
          );
        } else {
          e.dataTransfer!.setData(nodeMimeType, JSON.stringify(nodeData));
        }
      }
      // e.dataTransfer!.setData("text/html", $(node.span).html());
      if (!e.dataTransfer?.types.includes("text/plain")) {
        e.dataTransfer!.setData("text/plain", srcNode.title);
      }
      this.srcNode = srcNode;
      setTimeout(() => {
        // Decouple this call, so the CSS is applied to the node, but not to
        // the system generated drag image
        srcNode.setClass("wb-drag-source");
      }, 0);

      // --- drag ---
    } else if (e.type === "drag") {
      if (dndOpts.drag) {
        srcNode._callEvent("dnd.drag", { event: e });
      }
      // --- dragend ---
    } else if (e.type === "dragend") {
      srcNode.setClass("wb-drag-source", false);
      this.srcNode = null;
      if (this.lastTargetNode) {
        this._leaveNode();
      }
      srcNode._callEvent("dnd.dragEnd", { event: e });
    }
    return true;
  }

  /**
   * Handle dragenter, dragover, dragleave, drop events.
   */
  protected onDropEvent(e: DragEvent) {
    // const isLink = event.dataTransfer.types.includes("text/uri-list");
    const srcNode = this.srcNode;
    const srcTree = srcNode ? srcNode.tree : null;
    const targetNode = Wunderbaum.getNode(e)!;
    const dndOpts: DndOptionsType = this.treeOpts.dnd;
    const dt = e.dataTransfer!;
    const dropRegion = this._calcDropRegion(e, this.lastAllowedDropRegions);

    /** Helper to log a message if predicate is false. */
    const _t = (pred: any, msg: string) => {
      if (pred) {
        this.tree.log(`Prevented drop operation (${msg}).`);
      }
      return pred;
    };
    if (!targetNode) {
      this._leaveNode();
      e.preventDefault(); // Don't open file in browser when dropped in empty area
      return;
    }
    if (["drop"].includes(e.type)) {
      this.tree.logDebug(
        `onDropEvent.${e.type} targetNode: ${targetNode}, ea: ${dt?.effectAllowed}, ` +
          `de: ${dt?.dropEffect}, cy: ${e.offsetY}, r: ${dropRegion}, srcNode: ${srcNode}`,
        e
      );
    }

    // --- dragenter ---
    if (e.type === "dragenter") {
      // this.tree.logWarn(` onDropEvent.${e.type} targetNode: ${targetNode}`, e);
      this.lastAllowedDropRegions = null;
      // `dragleave` is not reliable with event delegation, so we generate it
      // from dragenter:
      if (this.lastTargetNode && this.lastTargetNode !== targetNode) {
        this._leaveNode();
      }
      this.lastTargetNode = targetNode;
      this.lastEnterStamp = Date.now();

      if (
        // Don't drop on status node:
        _t(targetNode.isStatusNode(), "is status node") ||
        // Prevent dropping nodes from different Wunderbaum trees:
        _t(
          dndOpts.preventForeignNodes && targetNode.tree !== srcTree,
          "preventForeignNodes"
        ) ||
        // Prevent dropping items on unloaded lazy Wunderbaum tree nodes:
        _t(
          dndOpts.preventLazyParents && !targetNode.isLoaded(),
          "preventLazyParents"
        ) ||
        // Prevent dropping items other than Wunderbaum tree nodes:
        _t(dndOpts.preventNonNodes && !srcNode, "preventNonNodes") ||
        // Prevent dropping nodes on own descendants:
        _t(
          dndOpts.preventRecursion && srcNode?.isAncestorOf(targetNode),
          "preventRecursion"
        ) ||
        // Prevent dropping nodes under same direct parent:
        _t(
          dndOpts.preventSameParent &&
            srcNode &&
            targetNode.parent === srcNode.parent,
          "preventSameParent"
        ) ||
        // Don't allow void operation ('drop on self'): TODO: should be checked on  move only
        _t(
          dndOpts.preventVoidMoves && targetNode === srcNode,
          "preventVoidMoves"
        )
      ) {
        dt.dropEffect = "none";
        // this.tree.log("Prevented drop operation");
        return true; // Prevent drop operation
      }

      // User may return a set of regions (or `false` to prevent drop)
      // Figure out a drop effect (copy/link/move) using opinated conventions.
      dt.dropEffect = this._guessDropEffect(e) || "none";
      let regionSet = targetNode._callEvent("dnd.dragEnter", {
        event: e,
        sourceNode: srcNode,
      });
      //
      regionSet = this.unifyDragover(regionSet);
      if (!regionSet) {
        dt.dropEffect = "none";
        return true; // Prevent drop operation
      }
      this.lastAllowedDropRegions = regionSet;
      this.lastDropEffect = dt.dropEffect;

      const region = this._calcDropRegion(e, this.lastAllowedDropRegions);
      targetNode.setClass("wb-drop-target");
      targetNode.setClass("wb-drop-over", region === "over");
      targetNode.setClass("wb-drop-before", region === "before");
      targetNode.setClass("wb-drop-after", region === "after");

      e.preventDefault(); // Allow drop (Drop operation is denied by default)
      return false;

      // --- dragover ---
    } else if (e.type === "dragover") {
      const viewportY = e.clientY - this.tree.element.offsetTop;
      this._autoScroll(viewportY);

      dt.dropEffect = this._guessDropEffect(e) || "none";

      targetNode._callEvent("dnd.dragOver", { event: e, sourceNode: srcNode });

      const region = this._calcDropRegion(e, this.lastAllowedDropRegions);

      this.lastDropRegion = region;
      this.lastDropEffect = dt.dropEffect;

      if (
        dndOpts.autoExpandMS! > 0 &&
        targetNode.isExpandable(true) &&
        !targetNode._isLoading &&
        Date.now() - this.lastEnterStamp > dndOpts.autoExpandMS! &&
        targetNode._callEvent("dnd.dragExpand", {
          event: e,
          sourceNode: srcNode,
        }) !== false
      ) {
        targetNode.setExpanded();
      }

      if (!region || this._isVoidDrop(targetNode, srcNode, region)) {
        return; // We already rejected in dragenter
      }
      targetNode.setClass("wb-drop-over", region === "over");
      targetNode.setClass("wb-drop-before", region === "before");
      targetNode.setClass("wb-drop-after", region === "after");

      e.preventDefault(); // Allow drop (Drop operation is denied by default)
      return false;

      // --- dragleave ---
    } else if (e.type === "dragleave") {
      // NOTE: we cannot trust this event, since it is always fired,
      // Instead we remove the marker on dragenter
      targetNode._callEvent("dnd.dragLeave", { event: e, sourceNode: srcNode });

      // --- drop ---
    } else if (e.type === "drop") {
      e.stopPropagation(); // prevent browser from opening links?
      e.preventDefault(); // #69 prevent iOS browser from opening links

      this._leaveNode();

      const region = this.lastDropRegion;
      let nodeData = e.dataTransfer?.getData(nodeMimeType);
      nodeData = nodeData ? JSON.parse(nodeData) : null;
      const srcNode = this.srcNode;
      const lastDropEffect = this.lastDropEffect;

      /* Before v0.14.0, we decoupled `_callEvent` like so:
           Decouple this call, because drop actions may prevent the dragend
           event from being fired on some browsers.
           setTimeout(() => {...}, 10);
        however this made e.dataTransfer.items inaccessible */
      targetNode._callEvent("dnd.drop", {
        event: e,
        region: region,
        suggestedDropMode: region === "over" ? "appendChild" : region,
        suggestedDropEffect: lastDropEffect,
        sourceNode: srcNode,
        sourceNodeData: nodeData,
        dataTransfer: e.dataTransfer,
      });
    }
    return false;
  }
}
