# PHP Helper utilities

This repo contains three separate utilities:
* *`debug.inc.php`* which provides a single `debug()` function that can
  be used to help debugging PHP applications.
* *`enhancedPDO.clsss.php`* a class that adds some useful utilities for
  doing common PDO related things
* *`helper-functions.inc.php`* which provides a collection of functions
  to help with validation and sanitisation of user inputs.

It also includes `deploy-to` a utility for quickly uploading recently
changed files a server.

## `debug()`

By default `debug()` renders any values passed to it along with the
name of the file and line number where it's called.

It will do it's best to determine the type of value passed and give
as much extra info about that value as possible.

See [README.debug.md](README.debug.md) for full documentation.

## EnhancedPDO

## Helper Functions

Helper functions has one primary helper function plus many simple
single purpose functions for validating and sanitising values

[`getValidFromArray()`](README.validation.md) used to get user
supplied data from HTML input fields and return values that are
safe to use for their intended purpose.

## `deploy-to`

> __NOTE:__ This is not a PHP helper but is useful for quickly
  deploying code changes to an SSH enabled server.

It provides an easy way to upload recently updated files to one of a
number of configured deployment targets.

See [README.deployto.md](README.deployto.md) for full documentation.
