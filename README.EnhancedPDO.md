# `EnhancedPDO`

* [Introduction](#introduction)
* [Basic usage](#basic-usage)
* [Shortcut methods](#shortcut-methods)
  * [`prepBindExec()`](#prepbindexec)
  * [`prepBindExecStr()`](#prepBindExecStr)
  * [`prepBind()` & `prepBindStr()`](#prepbind-and-prepbindstr)
  * [`bindExec()` & `bindExecStr()`](#bindexec--bindexecstr)
  * [`bindInt()`](#bindint)
  * [`bindStr()`](#bindstr)
  * [`bindBool()`](#bindbool)
  * [`bindNull()`](#bindnull)
* [General methods](#general-methods)
  * [`prepare()`](#prepare)
  * [`execute()`](#execute)
  * [`debug()`](#debug)
  * [`getDB()`](#getdb)
  * [`getDbHost()`](#getDbHost)
  * [`getDbName()`](#getDbName)
  * [`getDbType()`](#getDbType)

## Introduction

`EnhancedPDO` provides helper methods that build on top of PHP's
PDO classes. It doesn't extend PDO, instead is a wrapper for PDO
with some methods for doing very common DB stuff.

> __Note:__ `EnhancedPDO` always sets PDO's error mode to *exception*
>           because an error in your SQL is a serious bug and should
>           either be caught or kill the applicaiton.

## Basic usage

`EnhancedPDO` is instantiated in exactly the same way (with all the same
parameters) as PDO. It also has a number of the same methods.

```php
$dsn = 'mysql:host=localhost;dbname=my_db',

$db = new EnhancedPDO($dsn, 'username', 'password');

$stmt = $db->prepare('SELECT * FROM `my_table` WHERE `id` = 10');
```

or

```php
$dsn = 'mysql:host=localhost;dbname=my_db',
$options = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']

$db = new EnhancedPDO($dsn, 'username', 'password', $options);

$stmt = $db->prepare('SELECT * FROM `my_table` WHERE `id` = 10');
```

## Shortcut methods

### `prepBindExec()`

The single most common type of query I do is a select statement with
an `WHERE` clause that matches an ID (integer).

> __Note:__ The default query parameter name is "`:ID`".

> __Note also:__ `prepBindExec()` will throw an exception if there is
            an error in your SQL query. This is because I have had
            too many instances where I've had a bug in my code but
            not known the cause because PDO silently fails on SQL
            errors. This is a complete pain becasue I've wasted time
            digging around my code for PHP errors when the problem
            was with the SQL.

e.g.
```php
$dsn = 'mysql:host=localhost;dbname=my_db',

$db = new EnhancedPDO($dsn, 'username', 'password');

// ----------------------------
// $stmt = $db->prepare('SELECT * FROM `my_table` WHERE `id` = :ID');
// $stmt->bindParam(':ID', 10, PDO::PARAM_INT);
// $stmt->execute();
//
// or
//
// $stmt = $db->prepare('SELECT * FROM `my_table` WHERE `id` = :ID');
// $stmt->execute([':ID' => 10]);

$stmt = $db->prepBindExec(
  'SELECT * FROM `my_table` WHERE `id` = :ID', // NOTE the ":ID" parameter name.
  10
);

$result = $stmt->fetch(PDO::FETCH_OBJ);
```

This eliminates much of the boiler plate code associated with using PDO.

If your query doesn't have a `WHERE` clause you can just pass the
SQL string:
```php
$dsn = 'mysql:host=localhost;dbname=my_db',

$db = new EnhancedPDO($dsn, 'username', 'password');

$stmt = $db->prepBindExec('SELECT * FROM `my_table`');

while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
  // do some stuff
}
```

Sometimes you may want to make your code clearer and using ":ID" in
your query isn't what you want. In this case, you can pass the
parameter name as the third parameter to `prepBindExec()`.

e.g.
```php
$dsn = 'mysql:host=localhost;dbname=my_db',

$db = new EnhancedPDO($dsn, 'username', 'password');

$stmt = $db->prepBindExec(
  'SELECT * FROM `user_table` WHERE `age` > :YEARS', // Note the ":YEARS" parameter name.
  18,
  'YEARS' // We pass the "YEARS" parameter name here (without the colon ":").
);

while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
  // do some stuff
}
```

> __Note:__ Under the hood, `EnhancedPDO::prepBindExec()` uses [`EnhancedPDO::prepare()`](#prepare) and [`EnhancedPDO::bindExec()`](#bindExec)


### `prepBindExecStr()`

`prepBindExecStr()` works much the same way as [`prepBindExec()`](#prepbindexec)
except for strings (and the string value is required).

> __Note:__ If your query doesn't have a `WHERE` clause use
>           [`prepBindExec()`](#prepbindexec) instead.

> __Note also:__ Like `prepBindExec()`, `prepBindExecStr()` also
>           throws an exception if there's an error in your SQL.

e.g.
```php
$dsn = 'mysql:host=localhost;dbname=my_db',

$db = new EnhancedPDO($dsn, 'username', 'password');

$stmt = $db->prepBindExecStr(
  'SELECT * FROM `user_table` WHERE `name` = :STR', // Note the ":STR" parameter name.
  'joanne'
);

while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
  // do some stuff
}
```

Like with [`prepBindExec()`](#prepbindexec), you may not always want to user the parameter name "`:STR`" because it's not as friendly to your colleagues or future self. In this case you should pass the parameter name used in the query as the third parameter

e.g.
```php
$dsn = 'mysql:host=localhost;dbname=my_db',

$db = new EnhancedPDO($dsn, 'username', 'password');

$stmt = $db->prepBindExecStr(
  // Note the ":COLOUR" parameter name.
  'SELECT * FROM `my_table` WHERE `favourite_colour` = :COLOUR',
  'indigo',
  'COLOUR' // Passing override parameter name here
);

while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
  // do some stuff
}
```


### `prepBind()` and `prepBindStr()`

These both work the same way as [`prepBindExec()`](#prepbindexec) and [`prepBindExecStr()](#prepbindexecstr) but without the execution step. They are most useful when coupled with `bindExec()` & `bindExecStr()` for queries with multiple parameters to bind in.

e.g.
```php
$dsn = 'mysql:host=localhost;dbname=my_db',

$db = new EnhancedPDO($dsn, 'username', 'password');

$stmt = $db->prepBind(
  'SELECT *
   FROM   `my_table`
   WHERE  `id` > :ID
   AND    `favourite_colour` = :STR',
  39 // 39 is bound to the ":ID" parameter
);
$db->bindExecStr($stmt, 'MistyRose'); // "MistyRose" is bound to the ":STR" parameter

while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
  // do some stuff
}
```



### `bindExec()` & `bindExecStr()`

These both work the same way as [`prepBindExec()`](#prepbindexec) and [`prepBindExecStr()](#prepbindexecstr) but instead of passing an SQL string as the first parameter, you pass an already prepared `PDOStatement` object. These are useful if you have a query with more than one parameter to bind in. Combining these methods with `prepBind()` and `prepBindStr()`

### `bindInt()

`PDO::bindParam()` requires the value being bound in to be a variable. Sometimes that's not convenient because you want to modify the value as it's being bound in or the value is coming from a function/method return.

`bindInt()` allows you to easily bind an integer value that is a raw value (not a variable), an object property or a function/method return value.

```php
$id = 9
$myArray = [1, 2, 3, 4, 5];
$stmt = $db->prepare(
  'UPDATE `my_table`
   SET    `count` = :COUNT,
          `number` = :NUMBER
   WHERE  `id` = :ID;'
);
$db->bindInt($stmt, 'COUNT', count($myArray));
$db->bindInt($stmt, 'NUMBER', 6);
$db->bindExec($stmt, $id);
```

### `bindStr()

Like `bindInt()`, `bindStr()`  allows you to easily bind a `string`
value that is a raw value (not a variable) or a function/method
return value.

```php
$id = 8
function yes() {
  return 'YES!!!';
}
$stmt = $db->prepare(
  'UPDATE `my_table`
   SET    `name` = :NAME,
          `returned` = :RETURNED
   WHERE  `id` = :ID;'
);
$db->bindStr($stmt, 'NAME', 'Jo Blogs');
$db->bindStr($stmt, 'RETURNED', yes());
$db->bindExec($stmt, $id);
```

### `bindBool()

Like `bindInt()`, `bindBool()` allows you to easily bind a `boolean`
value that is a raw value (not a variable) or a function/method
return value.

```php
$id = 3
function isTrue() {
  return true;
}
$stmt = $db->prepare(
  'UPDATE `my_table`
   SET    `good` = :GOOD
          `bad` = :BAD
   WHERE  `id` = :ID;'
);
$db->bindBool($stmt, 'GOOD', true);
$db->bindBool($stmt, 'BAD', !isTrue());
$db->bindExec($stmt, $id);
```

### `bindNull()

Like `bindInt()`, `bindNull()` allows you to easily bind a `NULL` value that is a raw value (not a variable) a function/method return value.

```php
$id = 2
function getNull() {
  return null;
}
$stmt = $db->prepare(
  'UPDATE `my_table`
   SET    `null_val` = :IS_NULL
          `ret_null` = :RETURNED
   WHERE  `id` = :ID;'
);
$db->bindNull($stmt, 'IS_NULL', null);
$db->bindNull($stmt, 'RETURNED', getNull());
$db->bindExec($stmt, $id);
```


-----

## General methods

### `getDB()`

Get the PDO database connection object this EnhancedPDO object wraps.

There are times where it's useful to go back to bare bones and use a raw PDO database connection. This method allows you to do just that.

```php
$dsn = 'mysql:host=localhost;dbname=my_db',

$db = new EnhancedPDO($dsn, 'username', 'password');

$db->getDB(); // PDO object - same as if you called `new PDO($dsn, 'username', 'password')`
```

### `getDbHost()`

You should always know what the DB host is but sometimes when you're
testing it's useful just to confirm that the host you're actually
using is the host you're expecting. Get the host name/IP address of
server the database is on.

```php
$dsn = 'mysql:host=localhost;dbname=my_db',

$db = new EnhancedPDO($dsn, 'username', 'password');

$db->getHost(); // returns "localhost"`
```

### `getDbName()`

Get the name of the database the PDO connection is connected to.

This is useful for debugging.

```php
$dsn = 'mysql:host=localhost;dbname=my_db',

$db = new EnhancedPDO($dsn, 'username', 'password');

$db->getDbName(); // returns 'my_db'
```

### `getDbType()`

Get the type of database PDO is connected to (usually `MySQL`)

### `prepare()`

Prepare a PDO Statement.

> __Note:__ This is identical to PDO::prepare() except that you can
>           supply an alternate PDO object to connect to a different
>           database.

> __Note also:__ You can also use the alias `prep()` for less key
>           strokes.

### `execute()`

Execute a PDO statement and throw an exception if there's an error
with the SQL.

Normally, if there's an error with an SQL statement PDO fails
silently. This is not ideal because a bad query is an error and your
code should know about it.

By using `EnhancedPDO::execute()` you will be warned when you have a
bad query.

```php
$dsn = 'mysql:host=localhost;dbname=my_db',

$db = new EnhancedPDO($dsn, 'username', 'password');

$stmt = $db->prepare('SELECT * FROM `no_table` WHERE `id` = 10');

// throws an error if no_table doesn't exist
// (or there's any other error with the SQL)
$db->execute($stmt);
```

> __Note:__ For those of you who think less is more, you can use
>           `exec()` insteade and save you four whole key strokes.

### `debug()`

Get the SQL statement with all the parameters bound in.

> __Note:__ This is basically the same as `PDOStatement::debugDumpParams()`
>           but it returns the string instead of rendering the output.

Very useful for tracking down issues with your SQL.

