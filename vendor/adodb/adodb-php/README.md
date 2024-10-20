ADOdb Library for PHP
======================

[![Join chat on Gitter](https://img.shields.io/gitter/room/adodb/adodb?logo=gitter)](https://gitter.im/adodb/adodb?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![SourceForge Downloads Shield](https://img.shields.io/sourceforge/dm/adodb?label=SourceForge&logo=sourceforge&color=informational)](https://sourceforge.net/projects/adodb/files/latest/download)
[![Packagist Downloads Shield](https://img.shields.io/packagist/dm/ADOdb/ADOdb-php?label=Packagist&logo=packagist&logoColor=white&color=informational)](https://packagist.org/packages/adodb/adodb-php)

(c) 2000-2013 John Lim (jlim@natsoft.com)  
(c) 2014      Damien Regad, Mark Newnham and the
              [ADOdb community](https://github.com/ADOdb/ADOdb/graphs/contributors)

The ADOdb Library is dual-licensed, released under both the
[BSD 3-Clause](https://github.com/ADOdb/ADOdb/blob/master/LICENSE.md#bsd-3-clause-license) 
and the
[GNU Lesser General Public Licence (LGPL) v2.1](https://github.com/ADOdb/ADOdb/blob/master/LICENSE.md#gnu-lesser-general-public-license)
or, at your option, any later version. 
This means you can use it in proprietary products;
see [License](https://github.com/ADOdb/ADOdb/blob/master/LICENSE.md) for details.

Home page: https://adodb.org/


Introduction
============

PHP's database access functions are not standardized. This creates a
need for a database class library to hide the differences between the
different databases (encapsulate the differences) so we can easily
switch databases.

The library currently supports MySQL, Firebird & Interbase, PostgreSQL, SQLite3, Oracle,
Microsoft SQL Server, Foxpro ODBC, Access ODBC, Informix, DB2, Sybase,
Sybase SQL Anywhere, generic ODBC and Microsoft's ADO.

We hope more people will contribute drivers to support other databases.


Installation
============

Unpack all the files into a directory accessible by your web server.

To test, try modifying some of the tutorial examples.
Make sure you customize the connection settings correctly.

You can debug using:

``` php
<?php
include('adodb/adodb.inc.php');

$db = adoNewConnection($driver); # eg. 'mysqli' or 'oci8'
$db->debug = true;
$db->connect($server, $user, $password, $database);
$rs = $db->execute('select * from some_small_table');
print "<pre>";
print_r($rs->getRows());
print "</pre>";
```


Documentation and Examples
==========================

Refer to the [ADOdb website](https://adodb.org/) for library documentation and examples. The documentation can also be [downloaded for offline viewing](https://sourceforge.net/projects/adodb/files/Documentation/).

- [Main documentation](https://adodb.org/dokuwiki/doku.php?id=v5:userguide:userguide_index): Query, update and insert records using a portable API.
- [Data dictionary](https://adodb.org/dokuwiki/doku.php?id=v5:dictionary:dictionary_index) describes how to create database tables and indexes in a portable manner.
- [Database performance monitoring](https://adodb.org/dokuwiki/doku.php?id=v5:performance:performance_index) allows you to perform health checks, tune and monitor your database.
- [Database-backed sessions](https://adodb.org/dokuwiki/doku.php?id=v5:session:session_index).

There is also a [tutorial](https://adodb.org/dokuwiki/doku.php?id=v5:userguide:mysql_tutorial) that contrasts ADOdb code with PHP native MySQL code.


Files
=====

- `adodb.inc.php` is the library's main file. You only need to include this file.
- `adodb-*.inc.php` are the database specific driver code.
- `adodb-session.php` is the PHP4 session handling code.
- `test.php` contains a list of test commands to exercise the class library.
- `testdatabases.inc.php` contains the list of databases to apply the tests on.
- `Benchmark.php` is a simple benchmark to test the throughput of a SELECT
statement for databases described in testdatabases.inc.php. The benchmark
tables are created in test.php.


Support
=======

To discuss with the ADOdb development team and users, connect to our
[Gitter chatroom](https://gitter.im/adodb/adodb) using your Github credentials.

Please report bugs, issues and feature requests on Github:

https://github.com/ADOdb/ADOdb/issues

You may also find legacy issues in

- the [SourceForge tickets section](http://sourceforge.net/p/adodb/_list/tickets)

However, please note that they are not actively monitored and should
only be used as reference.
