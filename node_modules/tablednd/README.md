# TableDnD
[![npm version](https://badge.fury.io/js/tablednd.svg)](https://badge.fury.io/js/tablednd)
[![CDNJS version](https://img.shields.io/cdnjs/v/TableDnD.svg)](https://cdnjs.com/libraries/TableDnD)
[![jsDelivr Hits](https://data.jsdelivr.com/v1/package/gh/isocra/TableDnD/badge?style=rounded)](https://www.jsdelivr.com/package/gh/isocra/TableDnD)

## Installation

TableDnD is easy to install:
```
npm install --save tablednd
```
or
```
yarn add tablednd
```
or
```
bower install https://github.com/isocra/TableDnD.git
```
Alternatively you can simply reference from CDNJS:
```html
<script src="https://cdnjs.cloudflare.com/ajax/libs/TableDnD/0.9.1/jquery.tablednd.js" integrity="sha256-d3rtug+Hg1GZPB7Y/yTcRixO/wlI78+2m08tosoRn7A=" crossorigin="anonymous"></script>
```
or
```html
<script src="https://cdnjs.cloudflare.com/ajax/libs/TableDnD/0.9.1/jquery.tablednd.js" integrity="sha256-d3rtug+Hg1GZPB7Y/yTcRixO/wlI78+2m08tosoRn7A=" crossorigin="anonymous"></script>
```
You'll also need to include [jQuery](https://jquery.com) before you include this plugin (so that jQuery is defined).

---

## Getting Started

Let's create a simple table. The HTML for the table is very straight forward (no Javascript, pure HTML, we haven't added `thead` or `tbody` elements, but it works fine with these too):

```html
<table id="table-1" cellspacing="0" cellpadding="2">
    <tr id="1"><td>1</td><td>One</td><td>some text</td></tr>
    <tr id="2"><td>2</td><td>Two</td><td>some text</td></tr>
    <tr id="3"><td>3</td><td>Three</td><td>some text</td></tr>
    <tr id="4"><td>4</td><td>Four</td><td>some text</td></tr>
    <tr id="5"><td>5</td><td>Five</td><td>some text</td></tr>
    <tr id="6"><td>6</td><td>Six</td><td>some text</td></tr>
</table>
```
To add in the "draggability" all we need to do is add a line to the `$(document).ready(...)` function as follows:
```html
<script type="text/javascript">
$(document).ready(function() {
    // Initialise the table
    $("#table-1").tableDnD();
});
</script>
```
Basically we get the table element and call `tableDnD`. If you try this, you'll see that the rows are now draggable.

In the example above we're not setting any parameters at all so we get the default settings. There are a number of parameters you can set in order to control the look and feel of the table and also to add custom behaviour on drag or on drop. The parameters are specified as a map in the usual way and are described the [full documentation](http://isocra.github.io/TableDnD):

You can also play and experiment with TableDnD using this [jsFiddle](http://jsfiddle.net/DenisHo/dxpLrcd9/embedded/result/). Here you get the documentation, plus live examples.
