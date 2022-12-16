

# `getValidFromArray()`

`getValidFromArray()` Is primarily intended as a helper for doing
sophisticated validation of data coming from the user in `$_POST`,
`$_GET` or `$_COOKIES`.

```php
getValidFromArray(
    string $key,
    array $inputArray [,
        string|false|int|float|null $default [,
            string $mode [,
                mixed $modifier
            ]
        ]
    ]
) : mixed
```

As the name suggests this checks an array for a given key. Then, if
found, it validates the input as specified by `$mode`. If the input
passes validation it is returned. If not, the default value supplied
will be returned. It is essentially a shortcut for the following with
extra validation/sanitisation on top.

```php
$someVar = isset($arr['key']) && $arr['key'] !== '';
```

E.g. If you want make sure a user supplies a first name only you can do:
```php
$firstName = getValidFromArray('firstname', $_POST, '', 'name');
```

## Parameters

* `$key`: *[`string`]* The array key to search for
* `$inputArray`: *[`array`]* The array to search (usually $_POST or $_GET)
* `$default`: The default value to be returned if key cannot be found
   in input array or value does not validate. <br />
   Can be any type but `string` or `FALSE` is expected. (`FALSE` by default.)
* `$mode`: *[`string` - optional but recommended]* Validation mode - Options are:
  * [Simple text validation](#simple-text-validation)
    * `text` - [Plain english text with limited punctuation](#text-type-string)
    * `name` - [Names of people, places or things](#name-type-string)
    * `title` - [Like *name* but for longer strings and more punctuation](#title-type-string)
    * `html` - HTML with bad tags removed
  * [Special text validation](#special-text-validation)
    * `email` - Email address
    * `url` - Any url
  * [Phone number validation](#phone-number-validation)
    * `mobile` - [Australian mobile phone number](#australian-mobile-number)
    * `fixedphone` - [Australian fixed (landline) phone number](#australian-phone-fixedlandline)
    * `osphone` - [International phone numbers (requires a country prefix)](#international-phone-number)
    * `anyphone` - [Any phone number](#any-type-of-phone-number) (Australian landline or mobile, or international number)
  * [Date & time validation](#date-time-validation)
    * `date` - [ISO 8601 formatted date string](#date-iso-8601-date-format)
    * `datetime` - [ISO 8601 formatted date-time string](#datetime-iso-8601-datetime-format)
    * `time` - [ISO 8601 formatted time string](#time-iso-8601-time-format)
    * `year` - [Validates years between -150 years & + 50 years](#year)
  * [Integer and general numeric validation](#integer-and-general-numeric-validation)
    * `int` - [Integer (with optional min & max limits)](#integer)
    * `numeric` - [Integer/float (with optional min & max limits, decimal precision)](#numeric)
  * `bool` - Any value that can be interpreted as boolean
  * `checkbox` - Returns `TRUE` (regardless of value) if the provided
     $key is found in the input array
  * `select` - [response from a select or radio input](#select)
  * `callback` - [Passes found value through validation callback function](#callback)
* `$modifier`: *[`mixed` - optional]* Some value that augments the
  behavior of the modifier examples below for how modifiers can be
  used.<br />
  (See each mode for appropriate modifiers.)


## Returns [*`mixed`*]

If the input is valid, it will always return a the appropriate value type depending on the validation mode. (Always scalar. Usually string but sometimes boolean, integer or float).

If the input is not valid it will return whatever is provided in `$default` (usually `FALSE` or empty string).

-----

## Examples

Below are examples of how to use each of the validation modes available in `getValidFromArray()`


-----

## Simple text

Basic text values. e.g. Names, descriptions, general info, etc.



-----

### Text type string

Text type strings can be a maximum of 2048 characters long but the
default maximum is 128 characters.
They may contain alpha-numeric characters and most punctuation.
However, the following characters are forbidden:
<code>&grave;</code>, `~`, `@`, `#`, `$`, `%`, `^`, `*`, `_`, `+`,
`=`, `[`, `]`, `{`, `}`, `|`, `\`, `;`, `"`, `<`, `>`,
plus most other special characters

Text validation accepts a modifier array containing up to four keys:
* `max` - *[integer]* - maximum number of characters allowed.<br />
(Only values between 32 - 2048 allowed. A `max` value outside this
range will be updated to either 32 or 2048)
* `allowed` - *[string]* Extra allowed character not already included
(e.g. '`$+=`')
* `allowOnly` - *[string]* - Replaces the default list of allowed
characters with supplied string.
* `dedupe` - *[boolean]* - Whether or not to remove duplicate
consecuitive non-alphanumeric characters


-----

#### *`text`*: Example 1 (_GOOD_)

Text is good but longer than (default) 128 characters

__Input:__ `$_POST[`*`'text-good-1'`*`]`

```text
always wrapped at or before the specified width. So if you have a word that is larger than the given width, it is broken apart. (See second example). When false the function does not split the word even if the width is smaller than the word width.
```

__Usage:__

```php
getValidFromArray('text-good-1', $_POST, '', 'text');
```

__Expected output:__ *(Modified by validation)*
```
always wrapped at or before the specified width. So if you have a word that is larger than the given width, it is broken apart.
```


-----

#### *`text`*: Example 1 (_OK_)

Text has lots of bad characters which are stripped out
> __NOTE:__ Bad characters are replaced with spaces & multiple consecutive white spaces will be reduced to single spaces

__Input:__ `$_POST[`*`'text-ok-0'`*`]`

```text
if ( NEW_RELIC ) {
    newrelic_add_custom_tracer(\'sanitiseText\'); } // phpcs:ignore

	$_extra = (is_numeric($_extra) && is_int($_extra * 1) && $_extra > 32 && $_extra < 2048)
		? $_extra * 1
		: 128;

```

__Usage:__

```php
getValidFromArray('text-ok-0', $_POST, '', 'text');
```

__Expected output:__ *(Modified by validation)*
```
if ( NEW_RELIC )      newrelic_add_custom_tracer( 'sanitiseText ')    // phpcs:ignore _extra   (is_numeric( _extra) && is_int( _
```


-----

#### *`text`*: Example 2 (_OK_)

Long text truncated to custom length

__Input:__ `$_POST[`*`'text-ok-0'`*`]`

```text
Our comprehensive guide to CSS flexbox layout. This complete guide explains everything about flexbox, focusing on all the different possible properties for the parent element (the flex container) and the child elements (the flex items). It also includes history, demos, patterns, and a browser support chart.
```

__Usage:__

Modifier: `['max' => 200]` causes maximum length of output is 200 characters instead of 128.
> __Note:__ maximum allowable length for this type of validation is 2048 characters.

```php
getValidFromArray('text-ok-0', $_POST, '', 'text', ['max' => 200]);
```

__Expected output:__ *(Modified by validation)*
```
Our comprehensive guide to CSS flexbox layout. This complete guide explains everything about flexbox, focusing on all the different possible properties for the parent element (the flex container) and
```


-----

#### *`text`*: Example 2 (_GOOD_)

Text with more special characters allowed

__Input:__ `$_POST[`*`'text-good-2'`*`]`

```text
(1 + 1) * 28 = 56
```

__Usage:__

Modifier: `['allowed' => '+=*']` causes `+`, `=` & `*` are added to the allowed characters so are not stripped
> __Note:__ Default allowed characters are: a-z, A-Z, 0-9, "`&`", "`,`", "`-`", "`.`", "`?`", "`:`", "`!`", "`'`", "`(`", "`)`", & "`/`"

```php
getValidFromArray('text-good-2', $_POST, '', 'text', ['allowed' => '+=*']);
```

__Expected output:__ *(No change)*
```
(1 + 1) * 28 = 56
```


-----

#### *`text`*: Example 3 (_GOOD_)

Text with custom allowed characters

__Input:__ `$_POST[`*`'text-good-3'`*`]`

```text
if ( NEW_RELIC ) {
    newrelic_add_custom_tracer(\'sanitiseText\'); } // phpcs:ignore

	$_extra = (is_numeric($_extra) && is_int($_extra * 1) && $_extra > 32 && $_extra < 2048)
		? $_extra * 1
		: 128;

```

__Usage:__

Modifier: `['allowOnly' => '\w\\()$<>:;{}/\'[]', 'dedupe' => false]` causes default allowed characters to be replaced with characters from `allowOnly`
> __Note:__ Now allowed characters are: a-z, A-Z, 0-9, "`(`", "`)`", "`$`", "`<`", "`>`", "`:`", "`;`", "`{`", "`}`",
    "`/`", "`'`", "`\`", `[`" & "`]`"

```php
getValidFromArray('text-good-3', $_POST, '', 'text', ['allowOnly' => '\w\\()$<>:;{}/\'[]', 'dedupe' => false]);
```

__Expected output:__ *(Modified by validation)*
```
if ( NEW_RELIC ) {     newrelic_add_custom_tracer(\'sanitiseText\'); } // phpcs:ignore   $_extra   (is_numeric($_extra)    is_in
```



-----

### Name type string

Name type strings can be a maximum of 64 characters and may only
contain alpha-numeric characters spaces, hyphens, apostrophies and
full stops. All other characters are stripped along with leading &
trailing white space

> __NOTE:__ Maximum length can be decreased to as few characters as 1


-----

#### *`name`*: Example 1 (_GOOD_)

Input is good so is returned unchanged

__Input:__ `$_POST[`*`'name-good-1'`*`]`

```text
Evan Wills
```

__Usage:__

```php
getValidFromArray('name-good-1', $_POST, '', 'name');
```

__Expected output:__ *(No change)*
```
Evan Wills
```


-----

#### *`name`*: Example 1 (_OK_)

Input has bad characters that get stripped out

__Input:__ `$_POST[`*`'name-ok-0'`*`]`

```text
Evan (3) <input type="password" value="!#%$23|}{" />
```

__Usage:__

```php
getValidFromArray('name-ok-0', $_POST, '', 'name');
```

__Expected output:__ *(Modified by validation)*
```
Evan input type password value
```


-----

#### *`name`*: Example 2 (_OK_)

Input is good is good but too long (max length is set to 16)

__Input:__ `$_POST[`*`'name-ok-0'`*`]`

```text
Evan with a name that is way too long
```

__Usage:__

Modifier: `16` causes maximum length of output to be 16 characters instead of 64

```php
getValidFromArray('name-ok-0', $_POST, '', 'name', 16);
```

__Expected output:__ *(Modified by validation)*
```
Evan with a name
```



-----

## Phone number validation



-----

### Australian mobile number


-----

#### *`mobile`*: Example 1 (_GOOD_)

Mobile number is good and in desired format

__Input:__ `$_POST[`*`'mobile-good-1'`*`]`

```text
0412 345 678
```

__Usage:__

```php
getValidFromArray('mobile-good-1', $_POST, '', 'mobile');
```

__Expected output:__ *(No change)*
```
0412 345 678
```


-----

#### *`mobile`*: Example 2 (_GOOD_)

Mobile number is good but is in a weird format

__Input:__ `$_POST[`*`'mobile-good-2'`*`]`

```text
041 234 5678
```

__Usage:__

```php
getValidFromArray('mobile-good-2', $_POST, '', 'mobile');
```

__Expected output:__ *(Modified by validation)*
```
0412 345 678
```


-----

#### *`mobile`*: Example 1 (_OK_)

Mobile number is good but is in an even more weird format

__Input:__ `$_POST[`*`'mobile-ok-0'`*`]`

```text
(04) 1 23 4 56 78
```

__Usage:__

```php
getValidFromArray('mobile-ok-0', $_POST, '', 'mobile');
```

__Expected output:__ *(Modified by validation)*
```
0412 345 678
```


-----

#### *`mobile`*: Example 2 (_OK_)

Mobile number is good but has unwanted international prefix

__Input:__ `$_POST[`*`'mobile-ok-0'`*`]`

```text
+614 12 345 678
```

__Usage:__

```php
getValidFromArray('mobile-ok-0', $_POST, '', 'mobile');
```

__Expected output:__ *(Modified by validation)*
```
0412 345 678
```


-----

#### *`mobile`*: Example 1 (_BAD_)

Mobile number is invalid

__Input:__ `$_POST[`*`'mobile-bad-1'`*`]`

```text
4012 345 678
```

__Usage:__

```php
getValidFromArray('mobile-bad-1', $_POST, '', 'mobile');
```

__Expected output:__ *(Modified by validation)* `""` *[Empty string]*



-----

### Australian Phone (fixed/landline)


-----

#### *`fixedphone`*: Example 1 (_GOOD_)

Fixed line number is good and in desired format

__Input:__ `$_POST[`*`'fixedphone-good-1'`*`]`

```text
02 9123 4567
```

__Usage:__

```php
getValidFromArray('fixedphone-good-1', $_POST, '', 'fixedphone');
```

__Expected output:__ *(No change)*
```
02 9123 4567
```


-----

#### *`fixedphone`*: Example 2 (_GOOD_)

Fixed line number is good but is not in quite the right format

__Input:__ `$_POST[`*`'fixedphone-good-2'`*`]`

```text
(02) 9123-4567
```

__Usage:__

```php
getValidFromArray('fixedphone-good-2', $_POST, '', 'fixedphone');
```

__Expected output:__ *(Modified by validation)*
```
02 9123 4567
```


-----

#### *`fixedphone`*: Example 1 (_OK_)

Fixed line number is good but is in an even more weird format

__Input:__ `$_POST[`*`'fixedphone-ok-0'`*`]`

```text
(02)9-123-456-7
```

__Usage:__

```php
getValidFromArray('fixedphone-ok-0', $_POST, '', 'fixedphone');
```

__Expected output:__ *(Modified by validation)*
```
02 9123 4567
```


-----

#### *`fixedphone`*: Example 2 (_OK_)

Fixed line number is good but is in international format

__Input:__ `$_POST[`*`'fixedphone-ok-0'`*`]`

```text
+61(2)9-123-456-7
```

__Usage:__

```php
getValidFromArray('fixedphone-ok-0', $_POST, '', 'fixedphone');
```

__Expected output:__ *(Modified by validation)*
```
02 9123 4567
```


-----

#### *`fixedphone`*: Example 1 (_BAD_)

Fixed line number is valid mobile but invalid fixed line phone number

__Input:__ `$_POST[`*`'fixedphone-bad-1'`*`]`

```text
0412 345 678
```

__Usage:__

```php
getValidFromArray('fixedphone-bad-1', $_POST, '', 'fixedphone');
```

__Expected output:__ *(Modified by validation)* `""` *[Empty string]*


-----

#### *`fixedphone`*: Example 2 (_BAD_)

Fixed line number is valid mobile but invalid fixed line phone number

__Input:__ `$_POST[`*`'fixedphone-bad-2'`*`]`

```text
+610412 345 678
```

__Usage:__

```php
getValidFromArray('fixedphone-bad-2', $_POST, '', 'fixedphone');
```

__Expected output:__ *(Modified by validation)* `""` *[Empty string]*



-----

### International phone number

(Basically anything that looks like a phone number and has an
international prefix code)


-----

#### *`osphone`*: Example 1 (_GOOD_)

International phone number is good and in desired format

__Input:__ `$_POST[`*`'osphone-good-1'`*`]`

```text
+61 2 9123 4567
```

__Usage:__

```php
getValidFromArray('osphone-good-1', $_POST, '', 'osphone');
```

__Expected output:__ *(Modified by validation)*
```
+61 29123 4567
```


-----

#### *`osphone`*: Example 2 (_GOOD_)

International phone number is good but is not in quite the right format

__Input:__ `$_POST[`*`'osphone-good-2'`*`]`

```text
+61 291-234-567
```

__Usage:__

```php
getValidFromArray('osphone-good-2', $_POST, '', 'osphone');
```

__Expected output:__ *(Modified by validation)*
```
+61 29123 4567
```


-----

#### *`osphone`*: Example 3 (_GOOD_)

International phone number is good but is in a weird format

__Input:__ `$_POST[`*`'osphone-good-3'`*`]`

```text
+61 (2)9-123 456-7
```

__Usage:__

```php
getValidFromArray('osphone-good-3', $_POST, '', 'osphone');
```

__Expected output:__ *(Modified by validation)*
```
+61 29123 4567
```


-----

#### *`osphone`*: Example 4 (_GOOD_)

International phone number is valid Australian mobile but invalid international phone number

__Input:__ `$_POST[`*`'osphone-good-4'`*`]`

```text
0412 345 678
```

__Usage:__

```php
getValidFromArray('osphone-good-4', $_POST, '', 'osphone');
```

__Expected output:__ *(Modified by validation)* `""` *[Empty string]*



-----

### Any type of phone number

Useful for when you don't care what phone number users provide so long
as it\'s valid

> __NOTE:__ `anyphone` uses `mobile`, `fixedphone` & `osphone` to do
its validation so any phone numbers that work for those will also work
in `anyphone`.

`anyphone` accepts a modifier that is an array of strings matching
allowed phone types:
* [`mobile`](#australian-mobile-number) (or `cell`) validate
Australian mobile phone numbers
* [`fixed`](#australian-phone-fixedlandline) (or `landline`) validate
Australian fixed line phone numbers
* [`os`](#international-phone-number) (or `international`) validate
International phone numbers

> __NOTE:__ numbers are validated in the above order so if a phone
number is a valid Australian mobile phone number it will be returned
as a normal phone mobile number without the country code prefix

> __NOTE ALSO:__ If modifiers is an array but doesn't contain any of
the above types, an error is triggered.


-----

#### *`anyphone`*: Example 1 (_GOOD_)

Australian fixed line phone number is good and in desired format. (Country code is automatically stripped because international phone numbers are parsed last)

__Input:__ `$_POST[`*`'anyphone-good-1'`*`]`

```text
+61 2 9123 4567
```

__Usage:__

```php
getValidFromArray('anyphone-good-1', $_POST, '', 'anyphone');
```

__Expected output:__ *(Modified by validation)*
```
02 9123 4567
```


-----

#### *`anyphone`*: Example 2 (_GOOD_)

Australian fixed line phone number is good and in desired format.

__Input:__ `$_POST[`*`'anyphone-good-2'`*`]`

```text
02 9123 4567
```

__Usage:__

```php
getValidFromArray('anyphone-good-2', $_POST, '', 'anyphone');
```

__Expected output:__ *(No change)*
```
02 9123 4567
```


-----

#### *`anyphone`*: Example 3 (_GOOD_)

Australian mobile phone number is good and in desired format.

__Input:__ `$_POST[`*`'anyphone-good-3'`*`]`

```text
0412 345 678
```

__Usage:__

```php
getValidFromArray('anyphone-good-3', $_POST, '', 'anyphone');
```

__Expected output:__ *(No change)*
```
0412 345 678
```


-----

#### *`anyphone`*: Example 4 (_GOOD_)

New Zealand fixed line phone number is good.

__Input:__ `$_POST[`*`'anyphone-good-4'`*`]`

```text
+64 9 339 0852
```

__Usage:__

```php
getValidFromArray('anyphone-good-4', $_POST, '', 'anyphone');
```

__Expected output:__ *(Modified by validation)*
```
+64 9339 0852
```


-----

#### *`anyphone`*: Example 5 (_GOOD_)

Only accept Australian fixed line & mobile phone numbers

__Input:__ `$_POST[`*`'anyphone-good-5'`*`]`

```text
02 9123 4567
```

__Usage:__

```php
getValidFromArray('anyphone-good-5', $_POST, '', 'anyphone', ['mobile', 'fixed']);
```

__Expected output:__ *(No change)*
```
02 9123 4567
```


-----

#### *`anyphone`*: Example 1 (_BAD_)

Number is valid international phone number but fails because only Australian fixed line & mobile phone numbers are accepted

__Input:__ `$_POST[`*`'anyphone-bad-1'`*`]`

```text
+64 9 339 0852
```

__Usage:__

```php
getValidFromArray('anyphone-bad-1', $_POST, '', 'anyphone', ['mobile', 'fixed']);
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*



-----

## Date/Time

Validation for date, time & datetime values



-----

### Date (ISO 8601 date format)

Date string with the format YYYY-MM-DD

Date validation accepts a modifier array containing two keys:
* `min`
* `max`

`min` & `max` can have any of the following type of values:
* *`[string]`* A relative date string that can be parsed by PHP's
[strtotime()](https://www.php.net/manual/en/function.strtotime.php)
function
* *`[integer]`*A unix timestamp
* *`[string]`* An ISO 8601 formatted date string


-----

#### *`date`*: Example 1 (_GOOD_)

Date is good

__Input:__ `$_POST[`*`'date-good-1'`*`]`

```text
2022-04-11
```

__Usage:__

```php
getValidFromArray('date-good-1', $_POST, '', 'date');
```

__Expected output:__ *(No change)*
```
2022-04-11
```


-----

#### *`date`*: Example 1 (_OK_)

Date is OK but has whitespace which is stripped out

__Input:__ `$_POST[`*`'date-ok-0'`*`]`

```text
 1994-05-12
```

__Usage:__

```php
getValidFromArray('date-ok-0', $_POST, '', 'date');
```

__Expected output:__ *(Modified by validation)*
```
1994-05-12
```


-----

#### *`date`*: Example 2 (_OK_)

Date is leap year date that doesn't exist (automatically corrected to following date)

__Input:__ `$_POST[`*`'date-ok-0'`*`]`

```text
2022-02-29
```

__Usage:__

```php
getValidFromArray('date-ok-0', $_POST, '', 'date');
```

__Expected output:__ *(Modified by validation)*
```
2022-03-01
```


-----

#### *`date`*: Example 2 (_GOOD_)

Date is good and falls between min & max range

__Input:__ `$_POST[`*`'date-good-2'`*`]`

```text
2022-04-12
```

__Usage:__

```php
getValidFromArray('date-good-2', $_POST, '', 'date', ['min' => '2022-03-16', 'max' => '2022-12-16']);
```

__Expected output:__ *(No change)*
```
2022-04-12
```


-----

#### *`date`*: Example 1 (_BAD_)

Date is human readable but in English/Australian format. Day & month may be ambiguous so may not be translated correctly

__Input:__ `$_POST[`*`'date-bad-1'`*`]`

```text
11-05-2018
```

__Usage:__

```php
getValidFromArray('date-bad-1', $_POST, '', 'date');
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`date`*: Example 2 (_BAD_)

Rubish input

__Input:__ `$_POST[`*`'date-bad-2'`*`]`

```text
rubish
```

__Usage:__

```php
getValidFromArray('date-bad-2', $_POST, '', 'date');
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`date`*: Example 3 (_BAD_)

Date is OK but input also includes bad characters so is considdered bad

__Input:__ `$_POST[`*`'date-bad-3'`*`]`

```text
<2022-02-23>
```

__Usage:__

```php
getValidFromArray('date-bad-3', $_POST, '', 'date');
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`date`*: Example 4 (_BAD_)

Date is invalid format

__Input:__ `$_POST[`*`'date-bad-4'`*`]`

```text
today
```

__Usage:__

```php
getValidFromArray('date-bad-4', $_POST, '', 'date');
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`date`*: Example 5 (_BAD_)

Date is good but outside of relative minimum range

__Input:__ `$_POST[`*`'date-bad-5'`*`]`

```text
2017-07-17
```

__Usage:__

```php
getValidFromArray('date-bad-5', $_POST, '', 'date', ['min' => '- 1 year']);
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`date`*: Example 6 (_BAD_)

Date is good but outside of fixed minimum range (unix timestamp)

__Input:__ `$_POST[`*`'date-bad-6'`*`]`

```text
2020-07-29
```

__Usage:__

```php
getValidFromArray('date-bad-6', $_POST, '', 'date', ['min' => 1608102307]);
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`date`*: Example 7 (_BAD_)

Date is good but outside of fixed minimum range (ISO 8601 date string)

__Input:__ `$_POST[`*`'date-bad-7'`*`]`

```text
2021-03-29
```

__Usage:__

```php
getValidFromArray('date-bad-7', $_POST, '', 'date', ['min' => '2021-04-11']);
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`date`*: Example 8 (_BAD_)

Date is good but outside of relative maximum range

__Input:__ `$_POST[`*`'date-bad-8'`*`]`

```text
2024-08-19
```

__Usage:__

```php
getValidFromArray('date-bad-8', $_POST, '', 'date', ['max' => '+ 1 year']);
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`date`*: Example 9 (_BAD_)

Date is good but outside of fixed maximum range (unix timestamp)

__Input:__ `$_POST[`*`'date-bad-9'`*`]`

```text
2022-05-08
```

__Usage:__

```php
getValidFromArray('date-bad-9', $_POST, '', 'date', ['min' => 1657958707]);
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`date`*: Example 10 (_BAD_)

Date is good but outside of fixed maximum range (ISO 8601 date string)

__Input:__ `$_POST[`*`'date-bad-10'`*`]`

```text
2024-07-16
```

__Usage:__

```php
getValidFromArray('date-bad-10', $_POST, '', 'date', ['min' => '2023-04-11']);
```

__Expected output:__ *(No change)*
```
2024-07-16
```



-----

### Time (ISO 8601 time format)

Time string with the format HH:MM:SS

Time validation accepts a modifier array containing two keys:
* `min`
* `max`

`min` & `max` can have any of the following type of values:
* *`[integer]`* The hour of the day
* *`[integer]`* The number of seconds after midnight
* *`[string]`* An ISO 8601 formatted time string


-----

#### *`time`*: Example 1 (_GOOD_)

Time is good

__Input:__ `$_POST[`*`'time-good-1'`*`]`

```text
17:23:47
```

__Usage:__

```php
getValidFromArray('time-good-1', $_POST, '', 'time');
```

__Expected output:__ *(No change)*
```
17:23:47
```


-----

#### *`time`*: Example 2 (_GOOD_)

Time is good (no seconds in input but ":00" added to output

__Input:__ `$_POST[`*`'time-good-2'`*`]`

```text
09:31
```

__Usage:__

```php
getValidFromArray('time-good-2', $_POST, '', 'time');
```

__Expected output:__ *(Modified by validation)*
```
09:31:00
```


-----

#### *`time`*: Example 3 (_GOOD_)

Time is good (missing leading zero is added to output)

__Input:__ `$_POST[`*`'time-good-3'`*`]`

```text
9:37:00
```

__Usage:__

```php
getValidFromArray('time-good-3', $_POST, '', 'time');
```

__Expected output:__ *(Modified by validation)*
```
09:37:00
```


-----

#### *`time`*: Example 4 (_GOOD_)

Time is good and within min/max range

__Input:__ `$_POST[`*`'time-good-4'`*`]`

```text
10:45:00
```

__Usage:__

```php
getValidFromArray('time-good-4', $_POST, '', 'time', ['min' => '08:00', 'max' => '13:00']);
```

__Expected output:__ *(No change)*
```
10:45:00
```


-----

#### *`time`*: Example 1 (_BAD_)

Time format is OK but values are bad

__Input:__ `$_POST[`*`'time-bad-1'`*`]`

```text
49:31:30
```

__Usage:__

```php
getValidFromArray('time-bad-1', $_POST, '', 'time');
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`time`*: Example 2 (_BAD_)

Time is good but outside minimum range (defined by hour of day)

__Input:__ `$_POST[`*`'time-bad-2'`*`]`

```text
06:35:00
```

__Usage:__

```php
getValidFromArray('time-bad-2', $_POST, '', 'time', ['min' => 7]);
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`time`*: Example 3 (_BAD_)

Time is good but outside minimum range (defined by seconds after midnight)

__Input:__ `$_POST[`*`'time-bad-3'`*`]`

```text
07:55:00
```

__Usage:__

```php
getValidFromArray('time-bad-3', $_POST, '', 'time', ['min' => 28800]);
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`time`*: Example 4 (_BAD_)

Time is good but outside minimum range (defined by ISO 8601 time)

__Input:__ `$_POST[`*`'time-bad-4'`*`]`

```text
05:00:00
```

__Usage:__

```php
getValidFromArray('time-bad-4', $_POST, '', 'time', ['min' => '09:00']);
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`time`*: Example 5 (_BAD_)

Time is good but outside maximum range (defined by hour)

__Input:__ `$_POST[`*`'time-bad-5'`*`]`

```text
17:30:00
```

__Usage:__

```php
getValidFromArray('time-bad-5', $_POST, '', 'time', ['max' => 17]);
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`time`*: Example 1 (_OK_)

Time is good but outside maximum range (defined by seconds after midnight)

__Input:__ `$_POST[`*`'time-ok-5'`*`]`

```text
21:45:00
```

__Usage:__

```php
getValidFromArray('time-ok-5', $_POST, '', 'time', ['max' => 64800]);
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`time`*: Example 2 (_OK_)

Time is good but outside maximum range (defined by ISO 8601 time)

__Input:__ `$_POST[`*`'time-ok-5'`*`]`

```text
18:15:00
```

__Usage:__

```php
getValidFromArray('time-ok-5', $_POST, '', 'time', ['max' => '18:00']);
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*



-----

### DateTime (ISO 8601 datetime format)

Datetime works the same as Date but requires the time part of the
date/time string with the format `YYYY-MM-DD HH:MM:SS` Timezone part
of string is optional

DateTime validation accepts a modifier array containing four keys:
* `min` - The absolute lower limit of allowed input
* `max` - The absolute upper limit of allowed input
* `mintime` - The earliest time of day allowed on any given date
* `maxtime` - The latest time of day allowed on any given date

`min` & `max` can have any of the following type of values:
* *`[integer]`* The hour of the day
* *`[integer]`* The number of seconds after midnight
* *`[string]`* An ISO 8601 formatted time string`mintime` & `maxtime`
can have any of the following type of values:
* *`[integer]`* The hour of the day
* *`[integer]`* The number of seconds after midnight
* *`[string]`* An ISO 8601 formatted time string> See: [ISO 8601
formatted time string](#time-iso-8601-time-format-validation) for
examples of time allowed min/max time formats


-----

#### *`datetime`*: Example 1 (_GOOD_)

Date/time is good

__Input:__ `$_POST[`*`'datetime-good-1'`*`]`

```text
2022-04-11 09:23:54
```

__Usage:__

```php
getValidFromArray('datetime-good-1', $_POST, '', 'datetime');
```

__Expected output:__ *(No change)*
```
2022-04-11 09:23:54
```


-----

#### *`datetime`*: Example 2 (_GOOD_)

Date/time is bad because it does not include seconds)

__Input:__ `$_POST[`*`'datetime-good-2'`*`]`

```text
2022-02-22 17:00
```

__Usage:__

```php
getValidFromArray('datetime-good-2', $_POST, '', 'datetime');
```

__Expected output:__ *(Modified by validation)*
```
2022-02-22 17:00:00
```


-----

#### *`datetime`*: Example 3 (_GOOD_)

Date is good (includes timezone)

__Input:__ `$_POST[`*`'datetime-good-3'`*`]`

```text
2022-09-05 13:08:44+10:00
```

__Usage:__

```php
getValidFromArray('datetime-good-3', $_POST, '', 'datetime');
```

__Expected output:__ *(Modified by validation)*
```
2022-09-05 13:08:44
```


-----

#### *`datetime`*: Example 4 (_GOOD_)

Date/time is good (Includes `T` time separator & timezone without timezone minutes)

__Input:__ `$_POST[`*`'datetime-good-4'`*`]`

```text
2022-09-05T13:08:44+10
```

__Usage:__

```php
getValidFromArray('datetime-good-4', $_POST, '', 'datetime');
```

__Expected output:__ *(Modified by validation)*
```
2022-09-05 13:08:44
```


-----

#### *`datetime`*: Example 1 (_OK_)

Date/time is OK but has whitespace which is stripped out

__Input:__ `$_POST[`*`'datetime-ok-0'`*`]`

```text
	 1994-05-12 11:30:00AEST
```

__Usage:__

```php
getValidFromArray('datetime-ok-0', $_POST, '', 'datetime');
```

__Expected output:__ *(Modified by validation)*
```
1994-05-12 11:30:00
```


-----

#### *`datetime`*: Example 2 (_OK_)

Date is good and falls between min & max range

__Input:__ `$_POST[`*`'datetime-ok-0'`*`]`

```text
2022-04-12 23:09:11
```

__Usage:__

```php
getValidFromArray('datetime-ok-0', $_POST, '', 'datetime', ['min' => '2022-03-16 09:00:00', 'max' => '2022-12-16 17:00:00']);
```

__Expected output:__ *(No change)*
```
2022-04-12 23:09:11
```


-----

#### *`datetime`*: Example 3 (_OK_)

Date is good and falls between min & max range and within allowed time of day

__Input:__ `$_POST[`*`'datetime-ok-0'`*`]`

```text
2022-04-12 15:22:41
```

__Usage:__

```php
getValidFromArray('datetime-ok-0', $_POST, '', 'datetime', ['min' => '2022-03-16 09:00:00', 'max' => '2022-12-16 17:00:00', 'minTime' => 9, 'maxTime' => 17]);
```

__Expected output:__ *(No change)*
```
2022-04-12 15:22:41
```


-----

#### *`datetime`*: Example 1 (_BAD_)

Date is valid but does not include time part so valiation fails

__Input:__ `$_POST[`*`'datetime-bad-1'`*`]`

```text
2022-02-28
```

__Usage:__

```php
getValidFromArray('datetime-bad-1', $_POST, '', 'datetime');
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`datetime`*: Example 2 (_BAD_)

Date/time is leap year date that doesn't exist so cannot validate<br />(__Note:__ date validation will correct this but date/time will not.)

__Input:__ `$_POST[`*`'datetime-bad-2'`*`]`

```text
2022-02-29T10:30:00
```

__Usage:__

```php
getValidFromArray('datetime-bad-2', $_POST, '', 'datetime');
```

__Expected output:__ *(Modified by validation)*
```
2022-03-01 10:30:00
```


-----

#### *`datetime`*: Example 3 (_BAD_)

Date is human readable but in English/Australian format. Day & month may be ambiguous so may not be translated correctly

__Input:__ `$_POST[`*`'datetime-bad-3'`*`]`

```text
11-05-2018 22:00:00
```

__Usage:__

```php
getValidFromArray('datetime-bad-3', $_POST, '', 'datetime');
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`datetime`*: Example 4 (_BAD_)

Rubish input

__Input:__ `$_POST[`*`'datetime-bad-4'`*`]`

```text
rubish
```

__Usage:__

```php
getValidFromArray('datetime-bad-4', $_POST, '', 'datetime');
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`datetime`*: Example 5 (_BAD_)

DateTime is OK but input also includes bad characters so is considdered bad

__Input:__ `$_POST[`*`'datetime-bad-5'`*`]`

```text
<2022-02-23T00:30:00+1000>
```

__Usage:__

```php
getValidFromArray('datetime-bad-5', $_POST, '', 'datetime');
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`datetime`*: Example 6 (_BAD_)

datetime

__Input:__ `$_POST[`*`'datetime-bad-6'`*`]`

```text
DateTime is invalid format
```

__Usage:__

Modifier: `` causes today

```php
getValidFromArray('datetime-bad-6', $_POST, '', 'datetime');
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`datetime`*: Example 7 (_BAD_)

DateTime is good but outside of relative minimum range

__Input:__ `$_POST[`*`'datetime-bad-7'`*`]`

```text
2017-07-17 10:00:00+1100
```

__Usage:__

```php
getValidFromArray('datetime-bad-7', $_POST, '', 'datetime', ['min' => '- 1 year']);
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`datetime`*: Example 8 (_BAD_)

DateTime is good but outside of fixed minimum range (unix timestamp)

__Input:__ `$_POST[`*`'datetime-bad-8'`*`]`

```text
2020-07-29 00:00:00
```

__Usage:__

```php
getValidFromArray('datetime-bad-8', $_POST, '', 'datetime', ['min' => 1608102307]);
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`datetime`*: Example 9 (_BAD_)

DateTime is good but outside of fixed minimum range (ISO 8601 date string)

__Input:__ `$_POST[`*`'datetime-bad-9'`*`]`

```text
2021-03-29 12:00:00
```

__Usage:__

```php
getValidFromArray('datetime-bad-9', $_POST, '', 'datetime', ['min' => '2021-04-11']);
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`datetime`*: Example 10 (_BAD_)

DateTime is good but outside of relative maximum range

__Input:__ `$_POST[`*`'datetime-bad-10'`*`]`

```text
2023-10-16 18:05:07
```

__Usage:__

```php
getValidFromArray('datetime-bad-10', $_POST, '', 'datetime', ['max' => '+ 1 year']);
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`datetime`*: Example 11 (_BAD_)

DateTime is good but outside of fixed maximum range (unix timestamp)

__Input:__ `$_POST[`*`'datetime-bad-11'`*`]`

```text
2022-08-16 18:05:07
```

__Usage:__

```php
getValidFromArray('datetime-bad-11', $_POST, '', 'datetime', ['min' => 1657958707]);
```

__Expected output:__ *(No change)*
```
2022-08-16 18:05:07
```


-----

#### *`datetime`*: Example 12 (_BAD_)

DateTime is good but outside of fixed maximum range (ISO 8601 date string)

__Input:__ `$_POST[`*`'datetime-bad-12'`*`]`

```text
2024-07-16T19:01:01
```

__Usage:__

```php
getValidFromArray('datetime-bad-12', $_POST, '', 'datetime', ['min' => '2023-04-11']);
```

__Expected output:__ *(Modified by validation)*
```
2024-07-16 19:01:01
```



-----

### Year

Year validation works a bit like integer validation but for sanity
reasons `min` & `max` are limited to 150 years in the past & 50 years
in the future respectively. Min & max also accept relative values that
can be parsed by PHP's `strtotime()` function

DateTime validation accepts a modifier array containing four keys:
* `min` - The absolute lower limit of allowed year input
    (e.g. 2010) or relative string (e.g. "- 10 years")
* `max` - The absolute upper limit of allowed year input (e.g. 2022)
or relative string (e.g. "+ 6 months")


-----

#### *`year`*: Example 1 (_GOOD_)

Valid year

__Input:__ `$_POST[`*`'year-good-1'`*`]`

```text
2022
```

__Usage:__

```php
getValidFromArray('year-good-1', $_POST, '', 'year');
```

__Expected output:__ *(Modified by validation)*
```
2022
```


-----

#### *`year`*: Example 2 (_GOOD_)

Year (allowed student year of birth) is good and falls within min & max

__Input:__ `$_POST[`*`'year-good-2'`*`]`

```text
1999
```

__Usage:__

```php
getValidFromArray('year-good-2', $_POST, '', 'year', ['min' => '- 100 years', 'max' => '-15 years']);
```

__Expected output:__ *(Modified by validation)*
```
1999
```


-----

#### *`year`*: Example 1 (_BAD_)

Invalid year (absolute limit is - 110 years & + 50 years unless overridden by constants: `ABSOLUTE_MIN_TIME` or `ABSOLUTE_MAX_TIME`)

__Input:__ `$_POST[`*`'year-bad-1'`*`]`

```text
1903
```

__Usage:__

```php
getValidFromArray('year-bad-1', $_POST, '', 'year');
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

#### *`year`*: Example 2 (_BAD_)

Invalid year (of graduation)

__Input:__ `$_POST[`*`'year-bad-2'`*`]`

```text
2023
```

__Usage:__

Modifier: `['min' => '1989-01-01 ', 'max' => '2022']` causes `min` is absolute value but max is always the current year

```php
getValidFromArray('year-bad-2', $_POST, '', 'year', ['min' => '1989-01-01 ', 'max' => '2022']);
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*



-----

## Callback

Validation via callback function.

If `getValidFromArray()` is able to find the value being
requested, that value, along with the supplied default ,
is passed to the callback function (passed as the fifth
parameter to `getValidFromArray()`)

This allows applications to create custom validation
functions to achieve their own specific validation
requiremnets.

The callback function is passed two arguments: the found
value & the supplied default value. The returned value is
itself, returned by `getValidFromArray()`

Callback function is expected to have the following
signature:
```php
function (
    string|int|float|bool $input,
    string|int|float|bool $default
) : mixed;
```

If `getValidFromArray()` doesn't recieve a callback function
as the fifth argument, an error is triggered

```php
$callback = function ($input, $default) {
    if (preg_match('/^[a-z]{6,12}(?:[0-9]{0,2})$/i', $input)) {
        return preg_replace('/(?=[0-9]+)$/', ' ', strtolower($input));
    }
    return $default;
};

$_POST['username'] = 'JoSmith24';

$output = getValidFromArray(
    'username', $_POST, '', 'callback', $callback
);

// $output === "josmith 24"

// username should not pass validation
$_POST['username'] = 'Anne Blogs 1234';

$output = getValidFromArray(
    'username', $_POST, '', 'callback', $callback
);

// $output === "" (username was invalid)
```

> __NOTE:__ It is highly recommended that callback functions have no
side effects



-----

## Select

For validating responses from `<SELECT>` inputs

You provide an array of expected options as the modifier andthe input
is checked to see if it matches one of the expected options.


-----

### *`select`*: Example 1 (_GOOD_)

Good campus

__Input:__ `$_POST[`*`'select-good-1'`*`]`

```text
North Sydney
```

__Usage:__

```php
getValidFromArray('select-good-1', $_POST, '', 'select', ['Adelaide', 'Ballarat', 'Blacktown', 'Brisbane', 'Canberra', 'Melbourne', 'North Sydney', 'Strathfield', 'Online', 'National', 'Offshore']);
```

__Expected output:__ *(No change)*
```
North Sydney
```


-----

### *`select`*: Example 1 (_OK_)

Good campus (case insensitive)

__Input:__ `$_POST[`*`'select-ok-0'`*`]`

```text
strathfield
```

__Usage:__

Modifier: `['Adelaide', 'Ballarat', 'Blacktown', 'Brisbane', 'Canberra', 'Melbourne', 'North Sydney', 'Strathfield', 'Online', 'National', 'Offshore']` causes __NOTE:__ To ensure the most reliable results matches are whitespace & case insensitive

```php
getValidFromArray('select-ok-0', $_POST, '', 'select', ['Adelaide', 'Ballarat', 'Blacktown', 'Brisbane', 'Canberra', 'Melbourne', 'North Sydney', 'Strathfield', 'Online', 'National', 'Offshore']);
```

__Expected output:__ *(Modified by validation)*
```
Strathfield
```


-----

### *`select`*: Example 1 (_BAD_)

Bad campus (not part of list)

__Input:__ `$_POST[`*`'select-bad-1'`*`]`

```text
Perth
```

__Usage:__

```php
getValidFromArray('select-bad-1', $_POST, '', 'select', ['Adelaide', 'Ballarat', 'Blacktown', 'Brisbane', 'Canberra', 'Melbourne', 'North Sydney', 'Strathfield', 'Online', 'National', 'Offshore']);
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*


-----

### *`select`*: Example 2 (_GOOD_)

Good reason

__Input:__ `$_POST[`*`'select-good-2'`*`]`

```text
lost_stolen_destroyed
```

__Usage:__

```php
getValidFromArray('select-good-2', $_POST, '', 'select', ['lost_stolen_destroyed', 'damaged', 'name_change']);
```

__Expected output:__ *(No change)*
```
lost_stolen_destroyed
```


-----

### *`select`*: Example 2 (_OK_)

Good reason (case insensitive)

__Input:__ `$_POST[`*`'select-ok-1'`*`]`

```text
 LOST_STOLEN_DESTROYED
```

__Usage:__

Modifier: `['lost_stolen_destroyed', 'damaged', 'name_change']` causes __NOTE:__ To ensure the most reliable results matches are whitespace & case insensitive

```php
getValidFromArray('select-ok-1', $_POST, '', 'select', ['lost_stolen_destroyed', 'damaged', 'name_change']);
```

__Expected output:__ *(Modified by validation)*
```
lost_stolen_destroyed
```


-----

### *`select`*: Example 2 (_BAD_)

Bad reason (No match)

__Input:__ `$_POST[`*`'select-bad-2'`*`]`

```text
lost stolen destroyed
```

__Usage:__

Modifier: `['lost_stolen_destroyed', 'damaged', 'name_change']` causes Although the match is fairly tollerant, it does not convert non-alphanumeric characters so while the text is almost identical, it doesn't actually match

```php
getValidFromArray('select-bad-2', $_POST, '', 'select', ['lost_stolen_destroyed', 'damaged', 'name_change']);
```

__Expected output:__ *(Default returned)* `""` *[Empty string]*



-----

## Numeric & Integer

Numeric & integer value validation



-----

### Integer

Integer values


-----

#### *`int`*: Example 1 (_GOOD_)

Normal integer without any limits

__Input:__ `$_POST[`*`'int-good-1'`*`]`

```text
43
```

__Usage:__

```php
getValidFromArray('int-good-1', $_POST, 0, 'int');
```

__Expected output:__ *(Modified by validation)*
```
43
```


-----

#### *`int`*: Example 2 (_GOOD_)

Normal float is converted to integer

__Input:__ `$_POST[`*`'int-good-2'`*`]`

```text
3.14159
```

__Usage:__

```php
getValidFromArray('int-good-2', $_POST, 0, 'int');
```

__Expected output:__ *(Modified by validation)*
```
3
```


-----

#### *`int`*: Example 3 (_GOOD_)

Normal negative float

__Input:__ `$_POST[`*`'int-good-3'`*`]`

```text
-0.618
```

__Usage:__

```php
getValidFromArray('int-good-3', $_POST, 0, 'int');
```

__Expected output:__ *(Modified by validation)*
```
-1
```


-----

#### *`int`*: Example 1 (_OK_)

Bad (HEX) value. (While HEX "0Fa190" could be converted to a decimal it doesn't validate)

__Input:__ `$_POST[`*`'int-ok-0'`*`]`

```text
0Fa190
```

__Usage:__

```php
getValidFromArray('int-ok-0', $_POST, '', 'int');
```

__Expected output:__ *(Modified by validation)*
```
190
```


-----

#### *`int`*: Example 4 (_GOOD_)

Number is good and within limits

__Input:__ `$_POST[`*`'int-good-4'`*`]`

```text
5
```

__Usage:__

```php
getValidFromArray('int-good-4', $_POST, 0, 'int', ['max' => 5]);
```

__Expected output:__ *(Modified by validation)*
```
5
```


-----

#### *`int`*: Example 2 (_OK_)

Good number (Non-numeric characters are stripped out)

__Input:__ `$_POST[`*`'int-ok-0'`*`]`

```text
as-d%^5sdf15.2
```

__Usage:__

```php
getValidFromArray('int-ok-0', $_POST, '', 'int');
```

__Expected output:__ *(Modified by validation)*
```
-515
```


-----

#### *`int`*: Example 1 (_BAD_)

Non-numeric characters are stripped out but because of `min` restriction, default is returned

__Input:__ `$_POST[`*`'int-bad-1'`*`]`

```text
as-d%^5sdf15.2
```

__Usage:__

```php
getValidFromArray('int-bad-1', $_POST, 0, 'int', ['min' => 0]);
```

__Expected output:__ *(Default returned)* `0` *[integer]*


-----

#### *`int`*: Example 5 (_GOOD_)

Good number (Is a good number and within min & max)

__Input:__ `$_POST[`*`'int-good-5'`*`]`

```text
3
```

__Usage:__

```php
getValidFromArray('int-good-5', $_POST, 0, 'int', ['min' => -5, 'max' => 5]);
```

__Expected output:__ *(Modified by validation)*
```
3
```


-----

#### *`int`*: Example 6 (_GOOD_)

Good number

> __NOTE:__ Value will be rounded to integer, despite precision being set two decimal places

__Input:__ `$_POST[`*`'int-good-6'`*`]`

```text
1.61803399
```

__Usage:__

```php
getValidFromArray('int-good-6', $_POST, 0, 'int', ['precision' => 2]);
```

__Expected output:__ *(Modified by validation)*
```
2
```


-----

#### *`int`*: Example 2 (_BAD_)

Good number but above max

__Input:__ `$_POST[`*`'int-bad-2'`*`]`

```text
8
```

__Usage:__

Modifier: `['min' => -5, 'max' => 5]` causes means that default is returned because it's outside of range

```php
getValidFromArray('int-bad-2', $_POST, 0, 'int', ['min' => -5, 'max' => 5]);
```

__Expected output:__ *(Default returned)* `0` *[integer]*



-----

### Numeric

Any numeric value


-----

#### *`numeric`*: Example 1 (_GOOD_)

Normal integer without any limits

__Input:__ `$_POST[`*`'numeric-good-1'`*`]`

```text
43
```

__Usage:__

```php
getValidFromArray('numeric-good-1', $_POST, 0, 'numeric');
```

__Expected output:__ *(Modified by validation)*
```
43
```


-----

#### *`numeric`*: Example 2 (_GOOD_)

Normal float without limits

__Input:__ `$_POST[`*`'numeric-good-2'`*`]`

```text
3.14159
```

__Usage:__

```php
getValidFromArray('numeric-good-2', $_POST, 0, 'numeric');
```

__Expected output:__ *(Modified by validation)*
```
3.14159
```


-----

#### *`numeric`*: Example 3 (_GOOD_)

Normal negative float

__Input:__ `$_POST[`*`'numeric-good-3'`*`]`

```text
-0.618
```

__Usage:__

```php
getValidFromArray('numeric-good-3', $_POST, 0, 'numeric');
```

__Expected output:__ *(Modified by validation)*
```
-0.618
```


-----

#### *`numeric`*: Example 1 (_OK_)

Bad (HEX) value. (While HEX "0Fa190" could be converted to a decimal it doesn't validate)

__Input:__ `$_POST[`*`'numeric-ok-0'`*`]`

```text
0Fa190
```

__Usage:__

```php
getValidFromArray('numeric-ok-0', $_POST, '', 'numeric');
```

__Expected output:__ *(Modified by validation)*
```
190
```


-----

#### *`numeric`*: Example 4 (_GOOD_)

Number is good and within limits

__Input:__ `$_POST[`*`'numeric-good-4'`*`]`

```text
5
```

__Usage:__

```php
getValidFromArray('numeric-good-4', $_POST, 0, 'numeric', ['max' => 5]);
```

__Expected output:__ *(Modified by validation)*
```
5
```


-----

#### *`numeric`*: Example 2 (_OK_)

Good number (Non-numeric characters are stripped out)

__Input:__ `$_POST[`*`'numeric-ok-0'`*`]`

```text
as-d%^5sdf15.2
```

__Usage:__

```php
getValidFromArray('numeric-ok-0', $_POST, '', 'numeric');
```

__Expected output:__ *(Modified by validation)*
```
-515.2
```


-----

#### *`numeric`*: Example 1 (_BAD_)

Non-numeric characters are stripped out but because of `min` restriction, default is returned

__Input:__ `$_POST[`*`'numeric-bad-1'`*`]`

```text
as-d%^5sdf15.2
```

__Usage:__

```php
getValidFromArray('numeric-bad-1', $_POST, 0, 'numeric', ['min' => 0]);
```

__Expected output:__ *(Default returned)* `0` *[integer]*


-----

#### *`numeric`*: Example 5 (_GOOD_)

Good number (Is a good number and within min & max)

__Input:__ `$_POST[`*`'numeric-good-5'`*`]`

```text
3
```

__Usage:__

```php
getValidFromArray('numeric-good-5', $_POST, 0, 'numeric', ['min' => -5, 'max' => 5]);
```

__Expected output:__ *(Modified by validation)*
```
3
```


-----

#### *`numeric`*: Example 6 (_GOOD_)

Good number but will be rounded down to two decimal places

__Input:__ `$_POST[`*`'numeric-good-6'`*`]`

```text
1.61803399
```

__Usage:__

```php
getValidFromArray('numeric-good-6', $_POST, 0, 'numeric', ['precision' => 2]);
```

__Expected output:__ *(Modified by validation)*
```
1.62
```


-----

#### *`numeric`*: Example 2 (_BAD_)

Good number but above max

__Input:__ `$_POST[`*`'numeric-bad-2'`*`]`

```text
8
```

__Usage:__

Modifier: `['min' => -5, 'max' => 5]` causes means that default is returned because it's outside of range

```php
getValidFromArray('numeric-bad-2', $_POST, 0, 'numeric', ['min' => -5, 'max' => 5]);
```

__Expected output:__ *(Default returned)* `0` *[integer]*

