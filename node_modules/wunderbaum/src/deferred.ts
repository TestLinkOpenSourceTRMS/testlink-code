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
  private _promise: Promise<T>;
  protected _resolve: any;
  protected _reject: any;

  constructor() {
    this._promise = new Promise<T>((resolve, reject) => {
      this._resolve = resolve;
      this._reject = reject;
    });
  }
  /** Resolve the Promise. */
  resolve(value?: any) {
    this._resolve(value);
  }
  /** Reject the Promise. */
  reject(reason?: any) {
    this._reject(reason);
  }
  /** Return the native Promise instance.*/
  promise() {
    return this._promise;
  }
  /** Call Promise.then on the embedded promise instance.*/
  then(cb: PromiseCallbackType) {
    return this._promise.then(cb);
  }
  /** Call Promise.catch on the embedded promise instance.*/
  catch(cb: PromiseCallbackType) {
    return this._promise.catch(cb);
  }
  /** Call Promise.finally on the embedded promise instance.*/
  finally(cb: finallyCallbackType) {
    return this._promise.finally(cb);
  }
}
