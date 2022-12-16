# Random String generator

* [Introduction](#introduction)
  * [Basic usage](#basic-usage)
* [Methods](#methods)
  * [getStr()](#getstrstring-words)
  * [getCustomStr()](#getcustomstrarray-strings)
  * [getAddress()](#getaddress)
  * [getCountry()](#getcountryint-skew--5)
  * [getDomain()](#getdomainint-skew--8)
  * [getEmail()](#getemail)
  * [getIntPhone()](#getintphoneint-maxdigits--9-int-mindigits--8)
  * [getPhone()](#getphonebool-mobile--true])
  * [getPostCode()](#getpostcode)
  * [getTitle()](#gettitlebool-common--false)
  * [getwords()](#getwordsint-max--5-int-min---1)
* [List of words](#list-of-words)
* [Singleton/Factory](#singletonfactory)

## Introduction

`RandomStr` is a class that provides a collection of methods for
getting different types of strings.

It is intended for use in Unit Testing and anonymising existing data sets which may contain Personally Identifiable Information.

### Basic usage
```php
require_once('random-str.class.php');

$rand = RandomStr::get();

$str = $rand->getStr(); // some random word
```

## Methods

### `getStr(string $words)`

By default returns a random word (non-noun) is returned.

Or one of the following strings can be passed to return as specific
type of word/string:

* `"word"`    = Non-noun word (default)
* `"name`     = Noun
* `"street"`  = Street name suffix (e.g. "*St*")
* `"state"`   = Australian State Abbreviation (e.g. "*NSW*")
* `"country"` = Name of one of the 222 United nations recognised.
                __Note:__ for more flexibility use [`getCountry()`](#getcountry)
* `"tld"`     = Top level domain,
* `"title"`   = Honorific title abbreviation (e.g. "*Ms*" or "*Mr*")
* `"alpha"`   = Single (ASCII) alphabetical character


```php
require_once('random-str.class.php');

$rand = RandomStr::get();

$word = $rand->getStr(); // random non-noun word
$word = $rand->getStr('word'); // random non-noun word

$noun = $rand->getStr('name'); // random noun (e.g. "Flobert")

$street = $rand->getStr('street'); // random street name suffix (e.g. "st")

$state = $rand->getStr('state'); // random Australian state abbreviation (e.g. "Vic")

$country = $rand->getStr('country'); // random name of country (e.g. "Italy")

$tld = $rand->getStr('tld'); // random top level domain (e.g. "org")

$title = $rand->getStr('title'); // random honorific title (e.g. "Ms")

$alpha = $rand->getStr('alpha'); // single random ASCII alphabetical character
```

### `getCustomStr(array $strings)`

Returns one string of the supplied list strings at random;

```php
require_once('random-str.class.php');

$rand = RandomStr::get();

$list = ['me', 'myself', 'i', 'you', 'them', 'they'];

$str = $rand->getCustomStr($list); // Get random word from supplied list (e.g. "myself")
```

### `getAddress()`

Get a random street address (street number, name and type)

```php
require_once('random-str.class.php');

$rand = RandomStr::get();

$str = $rand->getAddress(); // e.g. "122 Amonate Dr"
```


### `getCountry(int $skew = 5)`

Get a random country skewed towards returning the "Home" country
(in this case Australia) 50% of the time.

If `$skew` is greater than or equal to 10, then the "Home" country
will always be returned.

If `$skew` is less than zero, the "Home" country is will be returned
as often as any other country


```php
require_once('random-str.class.php');

$rand = RandomStr::get();

// "Australia" will be returned 50% of the time. The rest of the time
// any random country (including "Australia") will be returned
$country1 = $rand->getCountry();

// "Australia" will be returned 20% of the time
$country2 = $rand->getCountry(2);

// "Australia" will be returned as often as any other country
$country2 = $rand->getCountry(-1);

// "Australia" will be always returned
$country2 = $rand->getCountry(10);
```

### `getDomain(int $skew = 8)`

Get a randomly generated web domain/host. 80% of the time it will return a domain with a country suffix

If `$skew` is greater than or equal to 10, then the a country suffix
will always be included.

If `$skew` is less than zero, the "Home" country suffix will never
be included.

```php
require_once('random-str.class.php');

$rand = RandomStr::get();

// 80% of the time, a country suffix will be included
$country1 = $rand->getDomain();

// 20% of the time, a country suffix will be included
$country2 = $rand->getDomain(2);

// A country suffix will never be included
$country2 = $rand->getDomain(-1);

// A country suffix will always be included
$country2 = $rand->getDomain(10);
```

### `getEmail()`

Get a randomly generated email address (optionally ACU Staff or Student)

```php
require_once('random-str.class.php');

$rand = RandomStr::get();

// Random user at random domain email address
$email1 = $rand->getEmail();
```

### `getIntPhone(int $maxDigits = 9, int $minDigits = 8)`

Get a randomly generated international phone number (with country
code prefix)

This generates phone numbers that are valid for Australia and
New Zealand

```php
require_once('random-str.class.php');

$rand = RandomStr::get();

// Random user at random domain email address
$austNZ = $rand->getIntPhone(); // e.g "+7998135706" or "+350975034"

$other = $rand->getIntPhone(11, 6) // e.g. "+6795458023487" or "+23357034"
```

### `getPhone(bool $mobile = true)`

Get a randomly generated Australian phone number

This generates valid Australian mobile and fixed line phone numbers

```php
require_once('random-str.class.php');

$rand = RandomStr::get();

// Random user at random domain email address
$mobile = $rand->getPhone(); // Mobile phone number e.g "0476795164"

$fixed = $rand->getPhone(false) // Fixed line phone e.g. "0242647842"
```

### `getPostCode()`

Get an Australian post code

```php
require_once('random-str.class.php');

$rand = RandomStr::get();

$postcode = $rand->getPostCode(); // Return an Australian Post code (e.g. 2060)
```

### `getTitle(bool $common = false)`

Returns one of the 22 standard honorific titles. If `$common` is `TRUE`, the the list of titles is reduced to the 5 most common (inclusive) honorific titles.

```php
require_once('random-str.class.php');

$rand = RandomStr::get();

$str = $rand->getTitle(); // Get random honorific title (e.g. "prof")

$common = $rand->getTitle(true); // Get random (common) honorific title (e.g. "Ms")
```

### `getwords(int $max = 5, int $min = -1)`

Get a string of space separated words.

By default returns 5 randomly select words each separate by a space.

If you supply a `$max` value alone, exactly that many words will be returned.

If you also supply a `$min` value that is greater than or equal to zero, a random number of words between $min and $max will be returned.

```php
require_once('random-str.class.php');

$rand = RandomStr::get();

$fiveWords = $rand->getWords(); // always returns 5 words

$nineWords = $rand->getWords(9); // always returns 9 words

$twoToEight = $rand->getWords(8, 2); // returns between 2 and 8 words.
```

## List of words

RandomStr relies on the constant: `WORDS_FILE`. A list of words to
use as the source of random strings. By default it uses the file
`word-list.txt` supplied in the same directory as this README.

`word-list.txt` is a list of 410,725 english language words. It was
downloaded from a public site on the internet. (Unfortunately, I
didn't document where and now I can't find the source.) I have
endevoured to remove all the inappropriate words I could find
however, I may have missed some.


## Singleton/Factory

`RandomStr` does not have a public constructor. Instead, it is
[singleton](https://en.wikipedia.org/wiki/Singleton_pattern)
class with a [factory method](https://en.wikipedia.org/wiki/Factory_method_pattern):
`RandomStr::get()` that provides access to the RandomStr object from
anywhere in your code.