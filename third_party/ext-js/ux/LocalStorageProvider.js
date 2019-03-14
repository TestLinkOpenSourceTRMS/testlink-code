Ext.ns('Ext.ux');

/**
 * @class Ext.ux.LocalStorageProvider
 * State provider based on HTML5 localStorage API
 */
Ext.ux.LocalStorageProvider = Ext.extend(Ext.state.Provider, {

  keyPrefix: 'TL_',

  constructor: function(config) {
    Ext.ux.LocalStorageProvider.superclass.constructor.call(this);
  },

  /**
   * Returns key with prefix for a given key
   * @param  {String} key The key
   * @return {String}   The key with prefix
   */
  getKeyWithPrefix: function(key) {
    return this.keyPrefix + '_' + key;
  },

  /**
   * Returns the current value for a key
   * @param {String} name The key name
   * @param {Mixed} defaultValue A default value to return if the key's value is not found
   * @return {Mixed} The state data
   */
  get: function(name, defaultValue) {
    var stateValue = defaultValue;
    var value = localStorage.getItem(this.getKeyWithPrefix(name));
    if (value !== null) {
      try {
        stateValue = JSON.parse(value);
      } catch (e) {}
    }
    return stateValue;
  },

  /**
   * Clears a value from the state
   * @param {String} name The key name
   */
  clear: function(name) {
    localStorage.removeItem(this.getKeyWithPrefix(name));
    this.fireEvent("statechange", this, name, null);
    return true;
  },

  /**
   * Sets the value for a key
   * @param {String} name The key name
   * @param {Mixed} value The value to set
   */
  set: function(name, value) {
    localStorage.setItem(this.getKeyWithPrefix(name), JSON.stringify(value));
    this.fireEvent("statechange", this, name, value);
    return true;
  }
});

/**
 * Is the HTML5 localStorage API available?
 */
Ext.ux.LocalStorageProvider.isSupported = function() {
  return (typeof localStorage === "object");
};