/*!
 * Wunderbaum - ext-logger
 * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
 * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
 */

import { LoggerOptionsType } from "./types";
import { overrideMethod } from "./util";
import { WunderbaumExtension } from "./wb_extension_base";
import { Wunderbaum } from "./wunderbaum";

export class LoggerExtension extends WunderbaumExtension<LoggerOptionsType> {
  readonly prefix: string;
  protected ignoreEvents = new Set<string>([
    "iconBadge",
    // "enhanceTitle",
    "render",
    "discard",
  ]);

  constructor(tree: Wunderbaum) {
    super(tree, "logger", {});
    this.prefix = tree + ".ext-logger";
  }

  init() {
    const tree = this.tree;

    // this.ignoreEvents.add();

    if (tree.getOption("debugLevel") >= 4) {
      // const self = this;
      const ignoreEvents = this.ignoreEvents;
      const prefix = this.prefix;

      overrideMethod(tree, "callEvent", function (name, extra) {
        /* eslint-disable prefer-rest-params */
        if (ignoreEvents.has(name)) {
          return (<any>tree)._superApply(arguments);
        }
        const start = Date.now();
        const res = (<any>tree)._superApply(arguments);
        tree.logDebug(
          `${prefix}: callEvent('${name}') took ${Date.now() - start} ms.`,
          arguments[1]
        );
        return res;
      });
    }
  }

  onKeyEvent(data: any): boolean | undefined {
    // this.tree.logInfo("onKeyEvent", eventToString(data.event), data);
    this.tree.logDebug(`${this.prefix}: onKeyEvent()`, data);
    return;
  }
}
