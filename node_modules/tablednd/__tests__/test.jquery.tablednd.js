const $ = require('jquery')
window.jQuery = $;
const tableDnD = require('../js/jquery.tablednd');

beforeEach(function() {
    document.body.innerHTML =
        '<table id="table1">' +
        '  <thead>' +
        '    <tr><th>Col1</th></tr>' +
        '  </thead>' +
        '  <tbody>' +
        '    <tr id="row1"><td>Row1</td></tr>' +
        '    <tr id="row2"><td>Row2</td></tr>' +
        '    <tr id="row3"><td>Row3</td></tr>' +
        '</tbody>';
});

test('Creates a TableDnD table', function() {
    var $table = $('#table1');
    var table = $table.tableDnD();
    expect(table).not.toBeUndefined();
});

test('Simulate drag and drop', function() {
    var $table = $('#table1');
    var table = $table.tableDnD();
    var $row = $('#row1');
    TestUtils.Simulate.dragStart($row, {dataTransfer: null});

});
