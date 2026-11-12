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
  protected _handler;
  protected root: EventTarget;
  protected start: {
    event: MouseEvent | TouchEvent | null;
    x: number;
    y: number;
    altKey: boolean;
    ctrlKey: boolean;
    metaKey: boolean;
    shiftKey: boolean;
  } = {
    event: null,
    x: 0,
    y: 0,
    altKey: false,
    ctrlKey: false,
    metaKey: false,
    shiftKey: false,
  };
  protected dragElem: HTMLElement | null = null;
  protected dragging: boolean = false;
  protected customData: object = {};
  // TODO: touch events
  protected events = ["mousedown", "mouseup", "mousemove", "keydown"];
  protected opts: DragObserverOptionsType;

  constructor(opts: DragObserverOptionsType) {
    if (!opts.root) {
      throw new Error("Missing `root` option.");
    }
    this.opts = Object.assign({ thresh: 5 }, opts);
    this.root = opts.root;
    this._handler = this.handleEvent.bind(this) as EventListener;
    this.events.forEach((type) => {
      this.root.addEventListener(type, this._handler);
    });
  }
  /** Unregister all event listeners. */
  disconnect() {
    this.events.forEach((type) => {
      this.root.removeEventListener(type, this._handler);
    });
  }

  public getDragElem(): HTMLElement | null {
    return this.dragElem;
  }

  public isDragging(): boolean {
    return this.dragging;
  }

  public stopDrag(cb_event?: DragCallbackArgType): void {
    if (this.dragging && this.opts.dragstop && cb_event) {
      cb_event.type = "dragstop";
      try {
        this.opts.dragstop(cb_event);
      } catch (err) {
        console.error("dragstop error", err); // eslint-disable-line no-console
      }
    }
    this.dragElem = null;
    this.dragging = false;
    this.start.event = null;
    this.customData = {};
  }

  protected handleEvent(e: MouseEvent): boolean | void {
    const type = e.type;
    const opts = this.opts;
    const cb_event: DragCallbackArgType = {
      type: e.type,
      startEvent: type === "mousedown" ? e : this.start.event!,
      event: e,
      customData: this.customData,
      dragElem: this.dragElem,
      dx: e.pageX - this.start.x,
      dy: e.pageY - this.start.y,
      apply: undefined,
    };

    // console.log("handleEvent", type, cb_event);

    switch (type) {
      case "keydown":
        this.stopDrag(cb_event);
        break;
      case "mousedown":
        if (this.dragElem) {
          this.stopDrag(cb_event);
          break;
        }
        if (opts.selector) {
          let elem = e.target as HTMLElement;
          if (elem.matches(opts.selector)) {
            this.dragElem = elem;
          } else {
            elem = elem.closest(opts.selector) as HTMLElement;
            if (elem) {
              this.dragElem = elem;
            } else {
              break; // no event delegation selector matched
            }
          }
        }
        this.start.event = e;
        this.start.x = e.pageX;
        this.start.y = e.pageY;
        this.start.altKey = e.altKey;
        this.start.ctrlKey = e.ctrlKey;
        this.start.metaKey = e.metaKey;
        this.start.shiftKey = e.shiftKey;
        break;

      case "mousemove":
        // TODO: debounce/throttle?
        // TODO: horizontal mode: ignore if dx unchanged
        if (!this.dragElem) {
          break;
        }
        if (!this.dragging) {
          if (opts.thresh) {
            const dist2 = cb_event.dx * cb_event.dx + cb_event.dy * cb_event.dy;
            if (dist2 < opts.thresh * opts.thresh) {
              break;
            }
          }
          cb_event.type = "dragstart";
          if (opts.dragstart(cb_event) === false) {
            this.stopDrag(cb_event);
            break;
          }
          this.dragging = true;
        }
        if (this.dragging && this.opts.drag) {
          cb_event.type = "drag";
          this.opts.drag(cb_event);
        }
        break;
      case "mouseup":
        if (!this.dragging) {
          this.stopDrag(cb_event);
          break;
        }
        if (e.button === 0) {
          cb_event.apply = true;
        } else {
          cb_event.apply = false;
        }
        this.stopDrag(cb_event);
        break;
    }
  }
}
