/*!
 * Wunderbaum - wb_extension_base
 * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
 * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
 */

import * as util from "./util";
import { DndExtension } from "./wb_ext_dnd";
import { EditExtension } from "./wb_ext_edit";
import { FilterExtension } from "./wb_ext_filter";
import { GridExtension } from "./wb_ext_grid";
import { KeynavExtension } from "./wb_ext_keynav";
import { LoggerExtension } from "./wb_ext_logger";
import { WunderbaumOptions } from "./wb_options";
import { Wunderbaum } from "./wunderbaum";

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
  public enabled = true;
  readonly id: string;
  readonly tree: Wunderbaum;
  readonly treeOpts: WunderbaumOptions;
  readonly extensionOpts: any;

  constructor(tree: Wunderbaum, id: string, defaults: TOptions) {
    this.tree = tree;
    this.id = id;
    this.treeOpts = tree.options;

    const opts = tree.options as any;

    if ((<any>this.treeOpts)[id] === undefined) {
      opts[id] = this.extensionOpts = util.extend({}, defaults);
    } else {
      // TODO: do we break existing object instance references here?
      this.extensionOpts = util.extend({}, defaults, opts[id]);
      opts[id] = this.extensionOpts;
    }
    this.enabled = this.getPluginOption("enabled", true);
  }

  /** Called on tree (re)init after all extensions are added, but before loading.*/
  init() {
    this.tree.element.classList.add("wb-ext-" + this.id);
  }

  // protected callEvent(type: string, extra?: any): any {
  //   let func = this.extensionOpts[type];
  //   if (func) {
  //     return func.call(
  //       this.tree,
  //       util.extend(
  //         {
  //           event: this.id + "." + type,
  //         },
  //         extra
  //       )
  //     );
  //   }
  // }

  getPluginOption(name: string, defaultValue?: any): any {
    return this.extensionOpts[name] ?? defaultValue;
  }

  setPluginOption(name: string, value: any): void {
    this.extensionOpts[name] = value;
  }

  setEnabled(flag = true) {
    return this.setPluginOption("enabled", !!flag);
    // this.enabled = !!flag;
  }

  onKeyEvent(data: any): boolean | undefined {
    return;
  }

  onRender(data: any): boolean | undefined {
    return;
  }
}
