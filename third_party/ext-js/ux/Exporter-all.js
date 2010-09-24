/**
*
*  Base64 encode / decode
*  http://www.webtoolkit.info/
*
**/

var Base64 = (function() {

    // private property
    var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

    // private method for UTF-8 encoding
    function utf8Encode(string) {
        string = string.replace(/\r\n/g,"\n");
        var utftext = "";
        for (var n = 0; n < string.length; n++) {
            var c = string.charCodeAt(n);
            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }
        }
        return utftext;
    }

    // public method for encoding
    return {
        //This was the original line, which tries to use Firefox's built in Base64 encoder, but this kept throwing exceptions....
        // encode : (typeof btoa == 'function') ? function(input) { return btoa(input); } : function (input) {
        
        
        encode : function (input) {
            var output = "";
            var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
            var i = 0;
            input = utf8Encode(input);
            while (i < input.length) {
                chr1 = input.charCodeAt(i++);
                chr2 = input.charCodeAt(i++);
                chr3 = input.charCodeAt(i++);
                enc1 = chr1 >> 2;
                enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                enc4 = chr3 & 63;
                if (isNaN(chr2)) {
                    enc3 = enc4 = 64;
                } else if (isNaN(chr3)) {
                    enc4 = 64;
                }
                output = output +
                keyStr.charAt(enc1) + keyStr.charAt(enc2) +
                keyStr.charAt(enc3) + keyStr.charAt(enc4);
            }
            return output;
        }
    };
})();

/**
 * @class Ext.ux.Exporter
 * @author Ed Spencer (http://edspencer.net)
 * Class providing a common way of downloading data in .xls or .csv format
 */
Ext.ux.Exporter = function() {
  return {
    /**
     * Exports a grid, using the .xls formatter by default
     * @param {Ext.grid.GridPanel} grid The grid to export from
     * @param {Object} config Optional config settings for the formatter
     */
    exportGrid: function(grid, formatter, config) {
      config = config || {};
      formatter = formatter || new Ext.ux.Exporter.ExcelFormatter();
      
      Ext.applyIf(config, {
        title  : grid.title,
        columns: grid.getColumnModel().config
      });
      
      return Base64.encode(formatter.format(grid.store, config));
    },
    
    exportStore: function(store, formatter, config) {
       config = config || {};
       formatter = formatter || new Ext.ux.Exporter.ExcelFormatter();

       Ext.applyIf(config, {
         columns: config.store.fields.items
       });
       
       return Base64.encode(formatter.format(store, config));
    },
    
    exportTree: function(tree, formatter, config) {
      config    = config || {};
      formatter = formatter || new Ext.ux.Exporter.ExcelFormatter();
      
      var store = tree.store || config.store;

      Ext.applyIf(config, {
        title: tree.title
      });
      
      return Base64.encode(formatter.format(store, config));
    }
  };
}();

/**
 * @class Ext.ux.Exporter.Button
 * @extends Ext.Button
 * @author Nige White, with modifications from Ed Spencer
 * Specialised Button class that allows downloading of data via data: urls.
 * Internally, this is just a link.
 * Pass it either an Ext.Component subclass with a 'store' property, or just a store:
 * new Ext.ux.Exporter.Button({component: someGrid});
 * new Ext.ux.Exporter.Button({store: someStore});
 * @cfg {Ext.Component} component The component the store is bound to
 * @cfg {Ext.data.Store} store The store to export (alternatively, pass a component with a store property)
 */
Ext.ux.Exporter.Button = Ext.extend(Ext.Button, {
  constructor: function(config) {
    config = config || {};
    
    Ext.applyIf(config, {
      exportFunction: 'exportGrid',
      disabled      : false,
      text          : 'Download',
      cls           : 'download'
    });

    if (config.store == undefined && config.component != undefined) {
      Ext.applyIf(config, {
        store: config.component.store
      });
    } else {
      Ext.applyIf(config, {
        component: {
          store: config.store
        }
      });
    }
    
    Ext.ux.Exporter.Button.superclass.constructor.call(this, config);
    
    if (this.store && Ext.isFunction(this.store.on)) {
      var setLink = function() {
        this.getEl().child('a', true).href = 'data:application/vnd.ms-excel;base64,' + Ext.ux.Exporter[config.exportFunction](this.component, null, config);
        
        this.enable();
      };
      
      if (this.el) {
        setLink.call(this);
      } else {
        this.on('render', setLink, this);
      }
      
      this.store.on('load', setLink, this);
    }
  },
  
  template: new Ext.Template(
    '<table border="0" cellpadding="0" cellspacing="0" class="x-btn-wrap"><tbody><tr>',
    '<td class="x-btn-left"><i> </i></td><td class="x-btn-center"><a class="x-btn-text" href="{1}" target="{2}">{0}</a></td><td class="x-btn-right"><i> </i></td>',
    "</tr></tbody></table>"),

    onRender:   function(ct, position){
      var btn, targs = [this.text || ' ', this.href, this.target || "_self"];
      if (position){
        btn = this.template.insertBefore(position, targs, true);
      }else{
        btn = this.template.append(ct, targs, true);
      }
      var btnEl = btn.child("a:first");
      this.btnEl = btnEl;
      btnEl.on('focus', this.onFocus, this);
      btnEl.on('blur', this.onBlur, this);

      this.initButtonEl(btn, btnEl);
      Ext.ButtonToggleMgr.register(this);
    },

    onClick : function(e){
      if (e.button != 0) return;
      
      if (!this.disabled){
        this.fireEvent("click", this, e);
        
        if (this.handler) this.handler.call(this.scope || this, this, e);
      }
    }
});

Ext.reg('exportbutton', Ext.ux.Exporter.Button);

/**
 * @class Ext.ux.Exporter.Formatter
 * @author Ed Spencer (http://edspencer.net)
 * @cfg {Ext.data.Store} store The store to export
 */
Ext.ux.Exporter.Formatter = function(config) {
  config = config || {};
        
  Ext.applyIf(config, {
    
  });
};

Ext.ux.Exporter.Formatter.prototype = {
  /**
   * Performs the actual formatting. This must be overridden by a subclass
   */
  format: Ext.emptyFn
};

/**
 * @class Ext.ux.Exporter.ExcelFormatter
 * @extends Ext.ux.Exporter.Formatter
 * Specialised Format class for outputting .xls files
 */
Ext.ux.Exporter.ExcelFormatter = Ext.extend(Ext.ux.Exporter.Formatter, {
  
  format: function(store, config) {
    var workbook = new Ext.ux.Exporter.ExcelFormatter.Workbook(config);
    workbook.addWorksheet(store, config || {});
    
    return workbook.render();
  }
});

/**
 * @class Ext.ux.Exporter.ExcelFormatter.Workbook
 * @extends Object
 * Represents an Excel workbook
 */
Ext.ux.Exporter.ExcelFormatter.Workbook = Ext.extend(Object, {
  
  constructor: function(config) {
    config = config || {};
    
    Ext.apply(this, config, {
      /**
       * @property title
       * @type String
       * The title of the workbook (defaults to "Workbook")
       */
      title: "Workbook",
      
      /**
       * @property worksheets
       * @type Array
       * The array of worksheets inside this workbook
       */
      worksheets: [],
      
      /**
       * @property compileWorksheets
       * @type Array
       * Array of all rendered Worksheets
       */
      compiledWorksheets: [],
      
      /**
       * @property cellBorderColor
       * @type String
       * The colour of border to use for each Cell
       */
      cellBorderColor: "#e4e4e4",
      
      /**
       * @property styles
       * @type Array
       * The array of Ext.ux.Exporter.ExcelFormatter.Style objects attached to this workbook
       */
      styles: [],
      
      /**
       * @property compiledStyles
       * @type Array
       * Array of all rendered Ext.ux.Exporter.ExcelFormatter.Style objects for this workbook
       */
      compiledStyles: [],
      
      /**
       * @property hasDefaultStyle
       * @type Boolean
       * True to add the default styling options to all cells (defaults to true)
       */
      hasDefaultStyle: true,
      
      /**
       * @property hasStripeStyles
       * @type Boolean
       * True to add the striping styles (defaults to true)
       */
      hasStripeStyles: true,
      
      windowHeight    : 9000,
      windowWidth     : 50000,      
      protectStructure: false,
      protectWindows  : false
    });
    
    if (this.hasDefaultStyle) this.addDefaultStyle();
    if (this.hasStripeStyles) this.addStripedStyles();
    
    this.addTitleStyle();
    this.addHeaderStyle();
  },
  
  render: function() {
    this.compileStyles();
    this.joinedCompiledStyles = this.compiledStyles.join("");
    
    this.compileWorksheets();
    this.joinedWorksheets = this.compiledWorksheets.join("");
    
    return this.tpl.apply(this);
  },
  
  /**
   * Adds a worksheet to this workbook based on a store and optional config
   * @param {Ext.data.Store} store The store to initialize the worksheet with
   * @param {Object} config Optional config object
   * @return {Ext.ux.Exporter.ExcelFormatter.Worksheet} The worksheet
   */
  addWorksheet: function(store, config) {
    var worksheet = new Ext.ux.Exporter.ExcelFormatter.Worksheet(store, config);
    
    this.worksheets.push(worksheet);
    
    return worksheet;
  },
  
  /**
   * Adds a new Ext.ux.Exporter.ExcelFormatter.Style to this Workbook
   * @param {Object} config The style config, passed to the Style constructor (required)
   */
  addStyle: function(config) {
    var style = new Ext.ux.Exporter.ExcelFormatter.Style(config || {});
    
    this.styles.push(style);
    
    return style;
  },
  
  /**
   * Compiles each Style attached to this Workbook by rendering it
   * @return {Array} The compiled styles array
   */
  compileStyles: function() {
    this.compiledStyles = [];
    
    Ext.each(this.styles, function(style) {
      this.compiledStyles.push(style.render());
    }, this);
    
    return this.compiledStyles;
  },
  
  /**
   * Compiles each Worksheet attached to this Workbook by rendering it
   * @return {Array} The compiled worksheets array
   */
  compileWorksheets: function() {
    this.compiledWorksheets = [];
    
    Ext.each(this.worksheets, function(worksheet) {
      this.compiledWorksheets.push(worksheet.render());
    }, this);
    
    return this.compiledWorksheets;
  },
  
  tpl: new Ext.XTemplate(
    '<?xml version="1.0" encoding="utf-8"?>',
    '<ss:Workbook xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:o="urn:schemas-microsoft-com:office:office">',
      '<o:DocumentProperties>',
        '<o:Title>{title}</o:Title>',
      '</o:DocumentProperties>',
      '<ss:ExcelWorkbook>',
        '<ss:WindowHeight>{windowHeight}</ss:WindowHeight>',
        '<ss:WindowWidth>{windowWidth}</ss:WindowWidth>',
        '<ss:ProtectStructure>{protectStructure}</ss:ProtectStructure>',
        '<ss:ProtectWindows>{protectWindows}</ss:ProtectWindows>',
      '</ss:ExcelWorkbook>',
      '<ss:Styles>',
        '{joinedCompiledStyles}',
      '</ss:Styles>',
        '{joinedWorksheets}',
    '</ss:Workbook>'    
  ),
  
  /**
   * Adds the default Style to this workbook. This sets the default font face and size, as well as cell borders
   */
  addDefaultStyle: function() {
    var borderProperties = [
      {name: "Color",     value: this.cellBorderColor},
      {name: "Weight",    value: "1"},
      {name: "LineStyle", value: "Continuous"}
    ];
    
    this.addStyle({
      id: 'Default',
      attributes: [
        {
          name: "Alignment",
          properties: [
            {name: "Vertical", value: "Top"},
            {name: "WrapText", value: "1"}
          ]
        },
        {
          name: "Font",
          properties: [
            {name: "FontName", value: "arial"},
            {name: "Size",     value: "10"}
          ]
        },
        {name: "Interior"}, {name: "NumberFormat"}, {name: "Protection"},
        {
          name: "Borders",
          children: [
            {
              name: "Border",
              properties: [{name: "Position", value: "Top"}].concat(borderProperties)
            },
            {
              name: "Border",
              properties: [{name: "Position", value: "Bottom"}].concat(borderProperties)
            },
            {
              name: "Border",
              properties: [{name: "Position", value: "Left"}].concat(borderProperties)
            },
            {
              name: "Border",
              properties: [{name: "Position", value: "Right"}].concat(borderProperties)
            }
          ]
        }
      ]
    });
  },
  
  addTitleStyle: function() {
    this.addStyle({
      id: "title",
      attributes: [
        {name: "Borders"},
        {name: "Font"},
        {
          name: "NumberFormat",
          properties: [
            {name: "Format", value: "@"}
          ]
        },
        {
          name: "Alignment",
          properties: [
            {name: "WrapText",   value: "1"},
            {name: "Horizontal", value: "Center"},
            {name: "Vertical",   value: "Center"}
          ]
        }
      ]
    });
  },
  
  addHeaderStyle: function() {
    this.addStyle({
      id: "headercell",
      attributes: [
        {
          name: "Font",
          properties: [
            {name: "Bold", value: "1"},
            {name: "Size", value: "10"}
          ]
        },
        {
          name: "Interior",
          properties: [
            {name: "Pattern", value: "Solid"},
            {name: "Color",   value: "#B3B3B3"}
          ]
        },
        {
          name: "Alignment",
          properties: [
            {name: "WrapText",   value: "1"},
            {name: "Horizontal", value: "Center"}
          ]
        }
      ]
    });
  },
  
  /**
   * Adds the default striping styles to this workbook
   */
  addStripedStyles: function() {
    this.addStyle({
      id: "even",
      attributes: [
        {
          name: "Interior",
          properties: [
            {name: "Pattern", value: "Solid"},
            {name: "Color",   value: "#FFFFFF"}
          ]
        }
      ]
    });
    
    this.addStyle({
      id: "odd",
      attributes: [
        {
          name: "Interior",
          properties: [
            {name: "Pattern", value: "Solid"},
            {name: "Color",   value: "#E6E6E6"}
          ]
        }
      ]
    });
    
    Ext.each(['even', 'odd'], function(parentStyle) {
      this.addChildNumberFormatStyle(parentStyle, parentStyle + 'date', "[ENG][$-409]dd\-mmm\-yyyy;@");
      this.addChildNumberFormatStyle(parentStyle, parentStyle + 'int', "0");
      this.addChildNumberFormatStyle(parentStyle, parentStyle + 'float', "0.00");
    }, this);    
  },
  
  /**
   * Private convenience function to easily add a NumberFormat style for a given parentStyle
   * @param {String} parentStyle The ID of the parentStyle Style
   * @param {String} id The ID of the new style
   * @param {String} value The value of the NumberFormat's Format property
   */
  addChildNumberFormatStyle: function(parentStyle, id, value) {
    this.addStyle({
      id: id,
      parentStyle: "even",
      attributes: [
        {
          name: "NumberFormat",
          properties: [{name: "Format", value: value}]
        }
      ]
    });
  }
});

/**
 * @class Ext.ux.Exporter.ExcelFormatter.Worksheet
 * @extends Object
 * Represents an Excel worksheet
 * @cfg {Ext.data.Store} store The store to use (required)
 */
Ext.ux.Exporter.ExcelFormatter.Worksheet = Ext.extend(Object, {

  constructor: function(store, config) {
    config = config || {};
    
    this.store = store;
    
    Ext.applyIf(config, {
      hasTitle   : true,
      hasHeadings: true,
      stripeRows : true,
      
      title      : "Workbook",
      columns    : store.fields == undefined ? {} : store.fields.items
    });
    
    Ext.apply(this, config);
    
    Ext.ux.Exporter.ExcelFormatter.Worksheet.superclass.constructor.apply(this, arguments);
  },
  
  /**
   * @property dateFormatString
   * @type String
   * String used to format dates (defaults to "Y-m-d"). All other data types are left unmolested
   */
  dateFormatString: "Y-m-d",
  
  worksheetTpl: new Ext.XTemplate(
    '<ss:Worksheet ss:Name="{title}">',
      '<ss:Names>',
        '<ss:NamedRange ss:Name="Print_Titles" ss:RefersTo="=\'{title}\'!R1:R2" />',
      '</ss:Names>',
      '<ss:Table x:FullRows="1" x:FullColumns="1" ss:ExpandedColumnCount="{colCount}" ss:ExpandedRowCount="{rowCount}">',
        '{columns}',
        '<ss:Row ss:Height="38">',
            '<ss:Cell ss:StyleID="title" ss:MergeAcross="{colCount - 1}">',
              '<ss:Data xmlns:html="http://www.w3.org/TR/REC-html40" ss:Type="String">',
                '<html:B><html:U><html:Font html:Size="15">{title}',
                '</html:Font></html:U></html:B></ss:Data><ss:NamedCell ss:Name="Print_Titles" />',
            '</ss:Cell>',
        '</ss:Row>',
        '<ss:Row ss:AutoFitHeight="1">',
          '{header}',
        '</ss:Row>',
        '{rows}',
      '</ss:Table>',
      '<x:WorksheetOptions>',
        '<x:PageSetup>',
          '<x:Layout x:CenterHorizontal="1" x:Orientation="Landscape" />',
          '<x:Footer x:Data="Page &amp;P of &amp;N" x:Margin="0.5" />',
          '<x:PageMargins x:Top="0.5" x:Right="0.5" x:Left="0.5" x:Bottom="0.8" />',
        '</x:PageSetup>',
        '<x:FitToPage />',
        '<x:Print>',
          '<x:PrintErrors>Blank</x:PrintErrors>',
          '<x:FitWidth>1</x:FitWidth>',
          '<x:FitHeight>32767</x:FitHeight>',
          '<x:ValidPrinterInfo />',
          '<x:VerticalResolution>600</x:VerticalResolution>',
        '</x:Print>',
        '<x:Selected />',
        '<x:DoNotDisplayGridlines />',
        '<x:ProtectObjects>False</x:ProtectObjects>',
        '<x:ProtectScenarios>False</x:ProtectScenarios>',
      '</x:WorksheetOptions>',
    '</ss:Worksheet>'
  ),
  
  /**
   * Builds the Worksheet XML
   * @param {Ext.data.Store} store The store to build from
   */
  render: function(store) {
    return this.worksheetTpl.apply({
      header  : this.buildHeader(),
      columns : this.buildColumns().join(""),
      rows    : this.buildRows().join(""),
      colCount: this.columns.length,
      rowCount: this.store.getCount() + 2,
      title   : this.title
    });
  },
  
  buildColumns: function() {
    var cols = [];
    
    Ext.each(this.columns, function(column) {
      cols.push(this.buildColumn());
    }, this);
    
    return cols;
  },
  
  buildColumn: function(width) {
    return String.format('<ss:Column ss:AutoFitWidth="1" ss:Width="{0}" />', width || 164);
  },
  
  buildRows: function() {
    var rows = [];
    
    this.store.each(function(record, index) {
      rows.push(this.buildRow(record, index));
    }, this);
    
    return rows;
  },
  
  buildHeader: function() {
    var cells = [];
    
    Ext.each(this.columns, function(col) {
      var title;
      
      if (col.header != undefined) {
        title = col.header;
      } else {
        //make columns taken from Record fields (e.g. with a col.name) human-readable
        title = col.name.replace(/_/g, " ");
        title = title.charAt(0).toUpperCase() + title.substr(1).toLowerCase();
      }
      
      cells.push(String.format('<ss:Cell ss:StyleID="headercell"><ss:Data ss:Type="String">{0}</ss:Data><ss:NamedCell ss:Name="Print_Titles" /></ss:Cell>', title));
    }, this);
    
    return cells.join("");
  },
  
  buildRow: function(record, index) {
    var style,
        cells = [];
    if (this.stripeRows === true) style = index % 2 == 0 ? 'even' : 'odd';
    
    Ext.each(this.columns, function(col) {
      var name  = col.name || col.dataIndex;
      
      //if given a renderer via a ColumnModel, use it and ensure data type is set to String
      if (Ext.isFunction(col.renderer)) {
        var value = col.renderer(record.get(name), null, record),
            type = "String";
      } else {
        var value = record.get(name),
            type  = this.typeMappings[col.type || record.fields.item(name).type];
      }
      
      cells.push(this.buildCell(value, type, style).render());
    }, this);
    
    return String.format("<ss:Row>{0}</ss:Row>", cells.join(""));
  },
  
  buildCell: function(value, type, style) {
    if (type == "DateTime" && Ext.isFunction(value.format)) value = value.format(this.dateFormatString);
    //TODO
	value=stripTags(value);
    return new Ext.ux.Exporter.ExcelFormatter.Cell({
      value: value,
      type : type,
      style: style
    });
  },
  
  /**
   * @property typeMappings
   * @type Object
   * Mappings from Ext.data.Record types to Excel types
   */
  typeMappings: {
    'int'   : "Number",
    'string': "String",
    'float' : "Number",
    'date'  : "DateTime"
  }
});

/**
 * @class Ext.ux.Exporter.ExcelFormatter.Cell
 * @extends Object
 * Represents a single cell in a worksheet
 */
Ext.ux.Exporter.ExcelFormatter.Cell = Ext.extend(Object, {

  constructor: function(config) {
    Ext.applyIf(config, {
      type: "String"
    });
    
    Ext.apply(this, config);
    
    Ext.ux.Exporter.ExcelFormatter.Cell.superclass.constructor.apply(this, arguments);
  },
  
  render: function() {
    return this.tpl.apply(this);
  },
  
  tpl: new Ext.XTemplate(
    '<ss:Cell ss:StyleID="{style}">',
      '<ss:Data ss:Type="{type}"><![CDATA[{value}]]></ss:Data>',
    '</ss:Cell>'
  )
});

/**
 * @class Ext.ux.Exporter.ExcelFormatter.Style
 * @extends Object
 * Represents a style declaration for a Workbook (this is like defining CSS rules). Example:
 * 
 * new Ext.ux.Exporter.ExcelFormatter.Style({
 *   attributes: [
 *     {
 *       name: "Alignment",
 *       properties: [
 *         {name: "Vertical", value: "Top"},
 *         {name: "WrapText", value: "1"}
 *       ]
 *     },
 *     {
 *       name: "Borders",
 *       children: [
 *         name: "Border",
 *         properties: [
 *           {name: "Color", value: "#e4e4e4"},
 *           {name: "Weight", value: "1"}
 *         ]
 *       ]
 *     }
 *   ]
 * })
 * 
 * @cfg {String} id The ID of this style (required)
 * @cfg {Array} attributes The attributes for this style
 * @cfg {String} parentStyle The (optional parentStyle ID)
 */
Ext.ux.Exporter.ExcelFormatter.Style = Ext.extend(Object, {

  constructor: function(config) {
    config = config || {};
    
    Ext.apply(this, config, {
      parentStyle: '',
      attributes : []
    });
    
    Ext.ux.Exporter.ExcelFormatter.Style.superclass.constructor.apply(this, arguments);
    
    if (this.id == undefined) throw new Error("An ID must be provided to Style");
    
    this.preparePropertyStrings();
  },
  
  /**
   * Iterates over the attributes in this style, and any children they may have, creating property
   * strings on each suitable for use in the XTemplate
   */
  preparePropertyStrings: function() {
    Ext.each(this.attributes, function(attr, index) {
      this.attributes[index].propertiesString = this.buildPropertyString(attr);
      this.attributes[index].children = attr.children || [];
      
      Ext.each(attr.children, function(child, childIndex) {
        this.attributes[index].children[childIndex].propertiesString = this.buildPropertyString(child);
      }, this);
    }, this);
  },
  
  /**
   * Builds a concatenated property string for a given attribute, suitable for use in the XTemplate
   */
  buildPropertyString: function(attribute) {
    var propertiesString = "";
    
    Ext.each(attribute.properties || [], function(property) {
      propertiesString += String.format('ss:{0}="{1}" ', property.name, property.value);
    }, this);
    
    return propertiesString;
  },
  
  render: function() {
    return this.tpl.apply(this);
  },
  
  tpl: new Ext.XTemplate(
    '<tpl if="parentStyle.length == 0">',
      '<ss:Style ss:ID="{id}">',
    '</tpl>',
    '<tpl if="parentStyle.length != 0">',
      '<ss:Style ss:ID="{id}" ss:Parent="{parentStyle}">',
    '</tpl>',
    '<tpl for="attributes">',
      '<tpl if="children.length == 0">',
        '<ss:{name} {propertiesString} />',
      '</tpl>',
      '<tpl if="children.length > 0">',
        '<ss:{name} {propertiesString}>',
          '<tpl for="children">',
            '<ss:{name} {propertiesString} />',
          '</tpl>',
        '</ss:{name}>',
      '</tpl>',
    '</tpl>',
    '</ss:Style>'
  )
});


/**
 * Little helper function to strip tags from a string.
 * @param strMod
 * @return strMod
 */
function stripTags(strMod){
	strMod = strMod.replace(/<(.|\n)*?>/gi, '');
	var	tarea = document.createElement('textarea');
	tarea.innerHTML = strMod;
	return tarea.value;
	//return strMod;
}
