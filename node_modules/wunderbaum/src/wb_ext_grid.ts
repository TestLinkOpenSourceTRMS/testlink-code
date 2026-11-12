/*!
 * Wunderbaum - ext-grid
 * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
 * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
 */
import { Wunderbaum } from "./wunderbaum";
import { WunderbaumExtension } from "./wb_extension_base";
import { DragCallbackArgType, DragObserver } from "./drag_observer";
import { ChangeType, ColumnDefinition, GridOptionsType } from "./types";
import { DEFAULT_MIN_COL_WIDTH } from "./common";
import { toBool, toPixel } from "./util";

export class GridExtension extends WunderbaumExtension<GridOptionsType> {
  protected observer: DragObserver;

  constructor(tree: Wunderbaum) {
    super(tree, "grid", {
      // throttle: 200,
    });

    this.observer = new DragObserver({
      root: window.document,
      selector: "span.wb-col-resizer-active",
      thresh: 4,
      // throttle: 400,
      dragstart: (e) => {
        const info = Wunderbaum.getEventInfo(e.startEvent);
        const colDef = info.colDef!;
        const allow =
          colDef &&
          this.tree.element.contains(e.dragElem) &&
          toBool(colDef.resizable, tree.options.columnsResizable, false);

        // this.tree.log("dragstart", colDef, e, info);

        this.tree.element.classList.toggle("wb-col-resizing", !!allow);
        info.colElem!.classList.toggle("wb-col-resizing", !!allow);

        // We start dagging, so we remember the actual width in *pixels*
        // (which may be 'auto' or '100%').
        // Since we we re-create the markup on each update, we also cannot store
        // the original event or DOM element, but only the colDef object.
        if (allow) {
          // Store initial target column infos in customData
          e.customData.colDef = colDef;
          e.customData.orgCustomWidthPx = colDef.customWidthPx;
          const curWidthPx = Number.parseInt(info.colElem!.style.width, 10);
          e.customData.orgWidthPx = curWidthPx;
          // Set custom width to current width, so that we can modify it
          colDef.customWidthPx = curWidthPx;
          // this.tree.log(
          //   `dragstart customWidthPx=${colDef.customWidthPx}`,
          //   e,
          //   info
          // );
          this.tree.update(ChangeType.colStructure);
          // this.tree.log(
          //   `dragstart 2 customWidthPx=${colDef.customWidthPx}`,
          //   e,
          //   info
          // );
        }
        return allow;
      },
      drag: (e) => {
        // TODO: throttle
        return this.handleDrag(e);
      },
      dragstop: (e) => {
        return this.handleDrag(e);
      },
    });
  }

  init() {
    super.init();
  }

  /**
   * Handles drag and sragstop events for column resizing.
   */
  protected handleDrag(e: DragCallbackArgType): void {
    const custom = e.customData;
    const colDef = <ColumnDefinition>custom.colDef!;
    // this.tree.log(`${e.type} (dx=${e.dx})`, e, info);

    if (e.type === "dragstop" || e.type === "drag") {
      this.tree.element.classList.remove("wb-col-resizing");
      // info.colElem!.classList.remove("wb-col-resizing");
      if (e.apply || e.type === "drag") {
        const minWidth = toPixel(colDef.minWidth, DEFAULT_MIN_COL_WIDTH);
        const newWidth = Math.max(minWidth, custom.orgWidthPx + e.dx);
        colDef.customWidthPx = newWidth;
        // this.tree.log(
        //   `${e.type} minWidth=${minWidth}, newWidth=${newWidth}`,
        //   colDef
        // );
      } else {
        // Drag was cancelled
        this.tree.log("Column resize cancelled", e);
        colDef.customWidthPx = custom.orgCustomWidthPx; // Restore original width or undefined
      }
      this.tree.update(ChangeType.colStructure);
    }
  }
}
