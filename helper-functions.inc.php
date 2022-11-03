<?php
/**
 * This file contains a number of "pure" helper funciton used for
 * sanitising and validating user inputs
 *
 * These functions have no side effects
 *
 * PHP Version 7.1
 *
 * @category Validation
 * @package  Validation
 * @author   Evan Wills <evan.i.wills@gmail.com>
 * @license  GPL2 <https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html>
 * @link     https://github.com/evanwills/php-utils
 */


if (!defined('GST_PERCENT')) {
    /**
     * The amount of GST charged
     *
     * @var float GST_PERCENT
     */
    define('GST_PERCENT', 10);
}

if (!defined('GST_FACTOR')) {
    /**
     * Proportion of displayed dollar value that is GST
     *
     * @var float GST_FACTOR
     */
    define('GST_FACTOR', (1 / ((100 + GST_PERCENT) / 100)));
}
if (!defined('SHOW_COMMENTS')) {
    define('SHOW_COMMENTS', false);
}
if (!defined('ABSOLUTE_MIN_TIME')) {
    /**
     * Unix timestamp for absolute minimum time allowed
     *
     * @var integer ABSOLUTE_MIN_TIME
     */
    define('ABSOLUTE_MIN_TIME', strtotime('- 110 years'));
}
if (!defined('ABSOLUTE_MAX_TIME')) {
    /**
     * Unix timestamp for absolute maximum time allowed
     *
     * @var integer ABSOLUTE_MAX_TIME
     */
    define('ABSOLUTE_MAX_TIME', strtotime('+ 50 years'));
}

if (!function_exists('newrelic_add_custom_tracer')) {
    function newrelic_add_custom_tracer() {} // phpcs:ignore
}

/**
 * Checks an if an array has the specified key, if not returns
 * the default value supplied. If so, it then passes the matched
 * value through an additional validation or sanitisation
 * function then returns the result of that.
 *
 * @param string   $key        Array key to be checked
 * @param string[] $inputArray Array (almost always $_POST) whose
 *                             key is to be checked
 * @param mixed    $default    Default value to be returned if key
 *                             is not present or invalid
 * @param string   $mode       What (if any) extra
 *                             validation/sanitisation should be
 *                             applied to the matched value
 *                             Mode options are:
 *                             * `anyphone` Any Australian or international
 *                             phone number
 *                             * `bool`     Used for checkboxes
 *                             (expected value: 1 or 'true' or 'yes' or 'on' or TRUE)
 *                             * `callable` Passes found value (& default)
 *                             to callback function and returns the result
 *                             * `checkbox` Returns TRUE if key exits
 *                             (value is ignored)
 *                             * `date`     Expected input is an ISO 8601
 *                             date formated string
 *                             (return value is an integer YYYYMMDD)
 *                             * `email`    Any valid email
 *                             * `html`     Make sure HTML doesn't have any nasties
 *                             * `int`      Make sure a value is an integer
 *                             * `landline` Australian land line
 *                             * `mobile`   Australian mobile phone number
 *                             * `name`     Make sure name only has alpha-numeric
 *                             characters, hyphens, apostropies and/or spaces
 *                             * `numeric`  Make sure value is either integer
 *                             or float
 *                             * `refid`    SecurePay refid value
 *                             * `osphone`  Any international phone number
 *                             * `select`   Make sure the user supplied value
 *                             is one of the options available
 *                             * `time`     ISO 8601 time formatted string
 *                             * `text`     title
 * @param mixed    $modifiers  modifiers to pass on to validation function
 *
 * @return string|false If the value was found
 */
function getValidFromArray(
    $key,
    $inputArray,
    $default = false,
    $mode = 'default',
    $modifiers = null
) {
    if ( NEW_RELIC ) { newrelic_add_custom_tracer('getValidFromArray'); } // phpcs:ignore

    $_default = is_string($default)
        ? $default
        : false;

    if (array_key_exists($key, $inputArray)) {
        $output = trim($inputArray[$key]);
        switch (strtolower($mode)) {
        case 'anyphone':
            return sanitiseAnyPhone($output, $_default, $modifiers);
        case 'aupostcode':
            return validateAUPostCode($output, $_default, $modifiers);
        case 'bool':
            $output = strtolower($output);
            return ($output == 1 || $output == 'true'
                    || $output == 'yes' || $output == 'on'
                    || $output === true || $output == $key);
        case 'callback':
            if (is_callable($modifiers)) {
                return $modifiers($output, $_default);
            } else {
                trigger_error(
                    'getValidFromArray() expects fifth '.
                    'parameter to be a callable function when '.
                    'fourth parameter `$mode` = "callback"',
                    E_USER_ERROR
                );
            }
        case 'checkbox':
            return true;
        case 'date':
            return validateIsoDate($output, $_default, $modifiers);
        case 'datetime':
            return validateIsoDateTime($output, $_default, $modifiers);
        case 'email':
            return validateEmail($output, $_default);
        case 'fixedphone':
            return sanitiseLandline($output, $_default);
        case 'html':
            return sanitiseHTML($output);
        case 'int':
            return sanitiseInt($output, $default, $modifiers);
        case 'mobile':
            return sanitiseMobile($output, $_default);
        case 'name':
            return sanitiseName($output, $modifiers);
        case 'numeric':
            return sanitiseNumeric($output, $default, $modifiers);
        case 'refid':
            return validateRefID($output, $default);
        case 'osphone':
            return sanitiseOsPhone($output, $_default);
        case 'select':
            return validateSelected($output, $modifiers, $_default);
        case 'text':
            return sanitiseText($output, $modifiers);
        case 'time':
            return validateIsoTime($output, $default, $modifiers);
        case 'title':
            return sanitiseTitle($output, $modifiers);
        case 'url':
            return validateURL($output, $_default);
        case 'year':
            return validateYear($output, $_default, $modifiers);

        default:
            return $output;
        }
    }
    return $default;
}

// =======================================================
// START: Sanitisation functions


/**
 * Convert any numeric type to an float
 *
 * @param mixed $input    Numeric value to be forced to float
 * @param float $_default Default value to be returned
 *
 * @return integer If input is numeric, it will be converted to a
 *                 float. Otherwise $_default will be returned
 */
function makeFloat($input, float $_default = 0.0)
{
    if ( NEW_RELIC ) { newrelic_add_custom_tracer('makeFloat'); } // phpcs:ignore

    if (!is_numeric($input)) {
        return $_default;
    }

    $output = $input * 1;

    settype($output, 'float');

    return $output;
}

/**
 * Convert any numeric type to an integer
 *
 * @param mixed $input    Numeric value to be forced to integer
 * @param int   $_default Default value to be returned
 *
 * @return integer If input is numeric, it will be converted to an
 *                 integer. Otherwise $_default will be returned
 */
function makeInt($input, int $_default = 0)
{
    if ( NEW_RELIC ) { newrelic_add_custom_tracer('makeInt'); } // phpcs:ignore
    if (!is_int($_default)) {
        trigger_error(
            'makeInt() expects second parameter $_default to '.
            'be an integer. '.gettype($_default).' given',
            E_USER_ERROR
        );
    }

    if (!is_numeric($input)) {
        return $_default;
    }

    $output = $input * 1;
    settype($output, 'integer');

    return $output;
}

/**
 * Removes potentially dangerous tags and attributes from HTML
 *
 * @param string         $html       HTML content to be sanitised
 * @param string[]|false $_modifiers List of allowed HTML tags that
 *                                   should not be stripped.
 *                                   If FALSE, no extra tags will be
 *                                   stripped
 *
 * @return string Clean HTML code
 */
function sanitiseHTML($html, $_modifiers = false)
{
    if ( NEW_RELIC ) { newrelic_add_custom_tracer('sanitiseHTML'); } // phpcs:ignore

    $badTags = array(
        'applet',
        'datalist', 'details', 'dir', 'embed',
        'fieldset', 'form', 'frame', 'frameset',
        'iframe', 'input',
        'legend', 'link',
        'map', 'math', 'meta',
        'object', 'optgroup', 'option',
        'script', 'select', 'style',
        'textarea',
        'video',
    );
    $badAttrs = array(
        'align', 'alink',
        'background', 'bgcolor', 'border',
        'clear', 'data[a-z0-9-]+',
        'height', 'hspace',
        'language', 'link',
        'nowrap', 'on[a-z]+', 'style',
        'text', 'type',
        'vlink', 'vspace', 'width'
    );
    $depricated = array('center', 'font');

    $find = array(
        // 1 (repeated spaces)
        '/(?:\&nbsp;|\s)+/is',
        // 2 (bad tags)
        '`<('.implode('|', $badTags).')[^>]*>.*?</\1>`is',
        // 3 (self closing)
        '/<(?:link|meta|input)[^>]*>/is',
        // 4 (depricated tags)
        '`</?(?:'.implode('|', $depricated).')[^>]+>`is',
        // 5 (bad attributes)
        '`\s(?:'.implode('|', $badAttrs).')='.
        '(?:"[^">]+"|\'[^>\']+\'|[^\s>]+(?=\s|>))`i',
        // 6 (redundant tags)
        '/(?:<br ?\/?>)?\s*(<\/?p>)\s*(?:<br ?\/?>)?/i',
        // 7 (custom elements)
        '/<([a-z]+(?:-[a-z0-9]+)+)[^>]*>.*?<\/\1[^>]*>/i',
        // 8 (empty tags)
        '/<([a-z]+)[^>]*>\s*<\/\1[^>]*>/'
    );
    $replace = array(
        ' ',    // 1
        '',     // 2
        '',     // 3
        '',     // 4
        '',     // 4
        '\1',   // 5
        '',     // 6
        ''      // 7
    );

    $output = preg_replace($find, $replace, $html);

    if (is_array($_modifiers)) {
        $output = strip_tags($output, $_modifiers);
    }

    return $output;
}

/**
 * Make sure a person's name only has valid characters
 *
 * Characters allowed are:
 * * alphabetical
 * * spaces
 * * hypens
 * * full stops
 * * apostrophies
 *
 * NOTE: Numbers are forbidden
 *
 * @param string  $name      Name of person to be sanitised
 * @param integer $_modifier Maximum number of characters allowed
 *                           (default: 64, min: 1, max: 64)
 *
 * @return string
 */
function sanitiseName($name, $_modifier = 64)
{
    if ( NEW_RELIC ) { newrelic_add_custom_tracer('sanitiseName'); } // phpcs:ignore

    $_modifier = _charLimit($_modifier, 64, 1, 64);

    // trim is done second because we may end up with white space at
    // the beginin or end if there are invalid characters at the
    // begining or end of the string.
    return trim(
        substr(
            trim(
                preg_replace(
                    array(
                        '`[^\w \-.\']+`i',
                        '`\d+(?:\.\d+)*`',
                        // remove duplicate consecutive non-alpha characters
                        '`([ \-.\'])+`'
                    ),
                    array(
                        ' ',
                        ' ',
                        '\1'
                    ),
                    $name
                )
            ),
            0, $_modifier
        )
    );
}

/**
 * Ensure that studentID only has uppercase alphabetical characters
 * and numbers and in no more than 24 characters long
 *
 * @param string $studentID value supplied by user
 *
 * @todo set up validation on input so output is FALSE if input
 *       is invalid or unreasonable.
 *
 * @return integer
 */
function sanitiseStudentID($studentID)
{
    if ( NEW_RELIC ) { newrelic_add_custom_tracer('sanitiseStudentID'); } // phpcs:ignore

    $output = preg_replace('`[^A-Z0-9]+`', '', strtoupper($studentID));

    return (strlen($output) > 24)
        ? substr($output, 0, 24)
        : $output;
}

/**
 * Makes sure a text has no invalid characters.
 *
 * Characters allowed are: alpha numeric, spaces and basic punctuation.
 * Forbidden characters: ` ~ @ # $ % ^ * _ + = { } | \ ; " < >
 *                       plus most other special characters
 *
 * @param string        $text       Title to be sanitised
 * @param integer|array $_modifiers Maximum number of characters allowed
 *                                  (default: 128, min: 32, max: 2048)
 *                                  Or an array with the following keys:
 *                                  * `max` {int} maximum character length
 *                                  * `min` {int} minimum character length
 *                                  * `allowed` {string[]} List of extra
 *                                  characters that are also allowed in
 *                                  sanitised output
 *                                  * `allowonly` {string[]} List of
 *                                  characters that replace the default
 *                                  allowed characters. Only these
 *                                  characters will be returned in
 *                                  sanitised output
 *                                  * `allowraw` {string} custom regular expression
 *                                  * `dedupe` {bool} Whether or not
 *                                  to remove duplicate allowed characters
 *
 * @return string Clean, safe text string
 */
function sanitiseText($text, $_modifiers = false)
{
    if ( NEW_RELIC ) { newrelic_add_custom_tracer('sanitiseText'); } // phpcs:ignore

    $minLen = 32;
    $maxLen = 128;
    $allowedChars = '\w&, \-.?:!\'()\/';
    $regex = '';
    $doDedupe = false;
    $ignore = 'i';

    if (is_array($_modifiers)) {
        foreach ($_modifiers as $key => $value) {
            $_key = strtolower($key);
            $_modifiers[$_key] = $value;
        }

        if (array_key_exists('max', $_modifiers)) {
            // Set the maximum length
            $maxLen = _charLimit($_modifiers['max'], 128, $minLen, 2048);
        }
        if (array_key_exists('min', $_modifiers)) {
            // Set the maximum length
            $minLen = _charLimit($_modifiers['min'], 128, 0, $maxLen - 1);
        }

        if (array_key_exists('ignore', $_modifiers)
            && is_bool($_modifiers['ignore'])
        ) {
            $ignore = ($_modifiers['ignore'] === true)
                ? 'i'
                : '';
        }

        if (array_key_exists('allowed', $_modifiers)) {
            // Add extra characters to the allowed character class
            $regex = '/[^' .$allowedChars.
                        _sanitiseCharClass($_modifiers['allowed']).
                     ']/'.$ignore;
        } elseif (array_key_exists('allowonly', $_modifiers)) {
            // Replace the default allowed characters with caller
            // supplied characters
            $regex = '/[^' .
                        _sanitiseCharClass($_modifiers['allowonly']).
                     ']/'.$ignore;
        } elseif (array_key_exists('allowraw', $_modifiers)) {
            // Replace the default allowed characters with caller
            // supplied characters
            $regex = $_modifiers['allowraw'];
        }

        if (array_key_exists('dedupe', $_modifiers)
            && is_bool($_modifiers['dedupe'])
        ) {
            $doDedupe = $_modifiers['dedupe'];
        }
    } elseif (is_numeric($_modifiers)) {
        // Set the maximum length
        $maxLen = _charLimit($_modifiers, 128, $minLen, 2048);
    }

    if ($regex === '') {
        $regex = '/[^'.$allowedChars.']+/'.$ignore;
    }

    $find = [$regex];
    $replace = [' '];

    if ($doDedupe === true) {
        $find[] = '/\s+/';
        $replace[] = ' ';
    }

    // Trim is done second because we may end up with white space at
    // the beginin or end if there are invalid characters at the
    // begining or end of the string.
    return trim(
        substr(
            trim(
                preg_replace($find, $replace, $text)
            ),
            0, $maxLen
        )
    );
}

/**
 * Makes sure a title has no invalid characters.
 *
 * Characters allowed are: alpha numeric, spaces and basic punctuation.
 *
 * @param string  $title     Title to be sanitised
 * @param integer $_modifier Maximum number of characters allowed
 *                           (default: 64, min: 1, max: 64)
 *
 * @return string Clean, safe title string
 */
function sanitiseTitle(string $title, $_modifier = 128) : string
{
    if ( NEW_RELIC ) { newrelic_add_custom_tracer('sanitiseTitle'); } // phpcs:ignore

    $_modifier = _charLimit($_modifier, 64, 1, 64);

    // trim is done last because we may end up with white space at
    // the begining or end if there are invalid characters at the
    // begining and/or end of the string.
    return trim(
        substr(
            trim(
                preg_replace(
                    [
                        '/[\s\t\r\n ]+/',
                        '/[^\w\d&,.?:! \-_\'()]+/i',
                        '/([&,.?:! \-_\'()])\1+/i'
                    ],
                    [' ', '', '\1'],
                    $title
                ),
            ),
            0,
            $_modifier
        )
    );
}

/**
 * Sanitise Australian mobile phone number
 *
 * @param string $number phone number sanitised
 *
 * @return string sanitised phone number
 */
function sanitiseMobile(string $number) : string
{
    $number = preg_replace('/[^+0-9]+/', '', $number);
    // debug($number);
    if (preg_match('/^(?:\+61|0)(4\d{2})(\d{3})(\d{3})$/', $number, $matches)) {
        return '0'.$matches[1].' '.$matches[2].' '.$matches[3];
    }
    // debug('[[LINE]][[LINE]]', $number);
    return '';
}

/**
 * Sanitise Australian fixed line phone number
 *
 * @param string $number phone number sanitised
 *
 * @return string sanitised phone number
 */
function sanitiseLandline(string $number) : string
{
    $number = preg_replace('/[^+0-9]+/', '', $number);
    if (preg_match('/^(?:\+61|0)([2378])(\d{4})(\d{4})$/', $number, $matches)) {
        return '0'.$matches[1].' '.$matches[2].' '.$matches[3];
    }
    return '';
}

/**
 * Sanitise international phone number
 *
 * @param string $number phone number sanitised
 *
 * @return string sanitised phone number
 */
function sanitiseOsPhone(string $number) : string
{
    $number = preg_replace('/[^+0-9]+/', '', $number);
    if (preg_match('/^(\+\d{2})(\d{4,12})$/', $number, $matches)) {
        return  $matches[1].' '.
                strrev(
                    preg_replace(
                        '/(\d{4})/',
                        ' \1',
                        strrev($matches[2])
                    )
                );
    }
    return '';
}

/**
 * Sanitise any phone number
 *
 * @param string       $number     Phone number sanitised
 * @param string|mixed $_default   Default value to be returned if
 *                                 phone number didn't validate
 * @param array|mixed  $_modifiers To be used $_modifiers must be
 *                                 array containing any of the
 *                                 following strings:
 *                                 * `os` Overseas/International
 *                                 phone number
 *                                 * `fixed` Australian land-line
 *                                 phone number
 *                                 * `mobile` Australian mobile
 *                                 phone number
 *                                 > __NOTE:__ none of the above
 *                                 strings are present, the supplied
 *                                 $_default value will always be
 *                                 returned because nothing will
 *                                 validate
 *
 * @return string|mixed Sanitised phone number or default value if
 *                      phone number could not be validated.
 */
function sanitiseAnyPhone(string $number, $_default = '', $_modifiers = false)
{
    if (!is_array($_modifiers)) {
        $output = sanitiseMobile($number);
        // debug($number, $output);
        if ($output !== '') {
            return $output;
        }
        $output = sanitiseLandline($number);
        // debug($number, $output);
        if ($output !== '') {
            return $output;
        }
        $output = sanitiseOsPhone($number);
        if ($output !== '') {
            return $output;
        }
    } else {
        // Standardise the identifiers for the phone number types
        for ($a = 0, $c = count($_modifiers); $a < $c; $a += 1) {
            if (is_string($_modifiers[$a])) {
                $_modifiers[$a] = strtolower(trim($_modifiers[$a]));
                if (substr($_modifiers[$a], 0, 3) === 'int') {
                    $_modifiers[$a] = 'os';
                } elseif (substr($_modifiers[$a], 0, 4) === 'land') {
                    $_modifiers[$a] = 'fixed';
                } elseif (substr($_modifiers[$a], 0, 4) === 'cell') {
                    $_modifiers[$a] = 'mobile';
                }
            }
        }
        $ok = false;

        if (in_array('mobile', $_modifiers)) {
            // Can be mobile number
            $output = sanitiseMobile($number);
            $ok = true;
            if ($output !== '') {
                return $output;
            }
        }
        if (in_array('fixed', $_modifiers)) {
            // Can be fixed/land line number
            $output = sanitiseLandline($number);
            $ok = true;
            if ($output !== '') {
                return $output;
            }
        }
        if (in_array('os', $_modifiers)) {
            // Can be overseas/international number
            $output = sanitiseOsPhone($number);
            $ok = true;
            if ($output !== '') {
                return $output;
            }
        }

        if ($ok === false) {
            trigger_error(
                'sanitiseAnyPhone() expects modifiers to be an '.
                'array containing at least one of the following '.
                'strings: `mobile`, `fixed`, `os`. None of these '.
                'were found so default value will be returned',
                E_USER_WARNING
            );
        }
    }

    return $_default;
}

/**
 * Make sure value is an integer and (if specified) within min & max
 *
 * > __NOTE:__ There is no validation done on $_default as this is
 *             always supplied by a developer who may have their own
 *             reasons for passing an alternative data type.
 *
 * @param string|integer $number     Year to be validated
 * @param int|mixed      $_default   Default value to return if year
 *                                   is invalid
 * @param array|false    $_modifiers Array with up to 2 integer values:
 *                                   * `min` - minimum allowable value
 *                                   * `max` - maximum allowable value
 *
 *                                   Other keys will be ignored
 *
 * @return integer|mixed If nubmer is valid then integer is returned.
 *                       Otherwise, $_default is returned
 */
function sanitiseInt(string $number, int $_default = 0, $_modifiers = false) : int
{
    if (is_array($_modifiers)) {
        $_modifiers['precision'] = 0;
    } else {
        $_modifiers = ['precision' => 0];
    }
    return sanitiseNumeric($number, $_default, $_modifiers);
}

/**
 * Make sure value is an integer and (if specified) within min & max
 *
 * @param string      $number     Year to be validated
 * @param mixed       $_default   Default value to return if year
 *                                is invalid
 * @param array|false $_modifiers Array with up to 3 integer values:
 *                                * `min` - minimum allowable value
 *                                * `max` - maximum allowable value
 *                                * `precision` - number of decimal places
 *
 *                                Other keys will be ignored
 *
 * @return integer|float|mixed If number is valid then integer or
 *                             float is returned. Otherwise,
 *                             $_default is returned
 */
function sanitiseNumeric(string $number, $_default = 0, $_modifiers = false)
{
    $_default = ($_default === false || $_default === null)
        ? 0
        : $_default;
    if (!is_numeric($_default)) {
        debug('backtrace');
        trigger_error(
            'sanitiseNumeric() expects second param `$_default` to be '.
            'numeric. '.gettype($_default).' given',
            E_USER_ERROR
        );
    }
    $_num = preg_replace(
        [
            '/[^0-9.-]+/',
            '/^(-?[0-9]+(?:\.[0-9]+)?).*$/'
        ],
        [
            '',
            '\1'
        ],
        $number
    );
    // debug($number, $_num);
    if (is_numeric($_num)) {
        $_num *= 1;
        // debug($_num);

        if (is_array($_modifiers)) {
            if (array_key_exists('min', $_modifiers)) {
                if (!is_numeric($_modifiers['min'])) {
                    trigger_error(
                        'sanitiseNumeric() expects `min` value to be an '.
                        'integer'.' "'.$_modifiers['min'].'" given',
                        E_USER_ERROR
                    );
                }
                if ($_num < $_modifiers['min']) {
                    return $_default;
                }
            }
            if (array_key_exists('max', $_modifiers)) {
                if (!is_numeric($_modifiers['max'])) {
                    trigger_error(
                        'sanitiseNumeric() expects `max` value to be an '.
                        'integer "'.$_modifiers['max'].'" given',
                        E_USER_ERROR
                    );
                }
                if ($_num > $_modifiers['max']) {
                    return $_default;
                }
            }

            if (array_key_exists('precision', $_modifiers)) {
                if (!is_int($_modifiers['precision'])) {
                    trigger_error(
                        'sanitiseNumeric() expects `precision` value to be an '.
                        'integer. "'.$_modifiers['precision'].'" given',
                        E_USER_ERROR
                    );
                }
                $_num = round($_num, $_modifiers['precision']);
            }
            // debug($_num);
        }
        // debug($_num);

        return $_num;
    }
    return $_default;
}

//  END:  Sanitisation functions
// =======================================================
// START: Validation functions
/**
 * Validate an Australian post code
 *
 * Data for this function came from
 * https://en.wikipedia.org/wiki/Postcodes_in_Australia
 *
 * @param string  $postCode HTML content to be sanitised
 * @param boolean $noPoBox  Exclude PO Box only post codes
 *
 * @return string Valid post code or empty string
 */
function validateAUPostCode(string $postCode, $noPoBox = true) : string
{
    if (strlen($postCode) === 4) {
        $_postCode = $postCode * 1;

        $normal = [
            [800, 899],
            [2000, 4999],
            [5000, 5799],
            [6000, 6797],
            [7000, 7799]
        ];

        for ($a = 0; $a < count($normal); $a += 1) {
            $min = $normal[$a][0];
            $max = $normal[$a][1];
            if ($_postCode >= $min && $_postCode <= $max) {
                return $postCode;
            }
        }

        if ($noPoBox === false) {
            $poBox = [
                [200, 299],
                [900, 999],
                [1000, 1999],
                [5800, 5999],
                [6800, 6999],
                [7800, 9999],
            ];

            for ($a = 0; $a < count($poBox); $a += 1) {
                $min = $poBox[$a][0];
                $max = $poBox[$a][1];
                if ($_postCode >= $min && $_postCode <= $max) {
                    return $postCode;
                }
            }
        }
    }
    return '';
}

/**
 * Checks whether a string is a valid URL
 *
 * @param string       $email    URL to be parsed
 * @param string|false $_default Default value to be returned
 *
 * @return string|false URL if it's valid, FALSE otherwise
 */
function validateEmail($email, $_default = false)
{
    if ( NEW_RELIC ) { newrelic_add_custom_tracer('validateEmail'); } // phpcs:ignore

    $regex = '`^[\d\w\-_.\']+@[\d\w-]+(?:\.[\d\w-]+)*(?:\.[a-z]+){1,2}$`i';
    $_tmp = explode('@', $email);


    return (isset($_tmp[1])
            // Don't accept email addresses with example in the domain
            && substr_count(strtolower($_tmp[1]), 'example') === 0
            && preg_match($regex, $email))
        ? $email
        : $_default;
}

/**
 * Normalise select values so they are easier to match
 *
 * @param string $input String to be normalised
 *
 * @return string Normalised string
 */
function normalise(string $input) : string
{
    return trim(strtolower(preg_replace('/\s+/', ' ', $input)));
}

/**
 * Check whether the selected item matches one of the options
 * available for that field
 *
 * @param string       $selected option user selected
 * @param string[]     $options  list of valid options available
 * @param string|false $_default Default value to be returned
 *
 * @return string|false String if option is valid.
 *                      FALSE otherwise
 */
function validateSelected($selected, $options, $_default = false)
{
    if ( NEW_RELIC ) { newrelic_add_custom_tracer('validateSelected'); } // phpcs:ignore

    $_options = $options;
    if (!is_array($_options)) {
        trigger_error(
            'validateSelected() expects second parameter '.
            '$options to be an array of strings or numbers. '.
            gettype($options).' given',
            E_USER_ERROR
        );
    } else {
        for ($a = 0; $a < count($_options); $a += 1) {
            if (is_string($_options[$a])) {
                $_options[$a] = normalise($_options[$a]);
            } elseif (!is_numeric($_options[$a])) {
                trigger_error(
                    'validateSelected() expects second parameter '.
                    '$options to be an array of strings or numbers. '.
                    'Option '.$a.' is a '.gettype($_options[$a]),
                    E_USER_WARNING
                );
            }
        }
    }

    $key = array_search(normalise($selected), $_options);

    return ($key !== false)
        ? $options[$key]
        : $_default;
}

/**
 * Validates value of an `<input type="date" />` field to make sure
 * it conforms to an ISO 8601 date formatted string and is between
 * min & max if supplied
 *
 * @param string       $input      Value supplied by user
 * @param string|false $_default   Default value to be returned
 * @param array|false  $_modifiers Array containing a min and/or max
 *                                 value both of which can be either
 *                                 a unix timestamp or an  ISO 8601
 *                                 date/time string (or any string
 *                                 parsable by strtotime())
 * @param string       $_format    String to format output date/time
 *                                 string
 *
 * @return string|false
 */
function validateIsoDate(
    $input, $_default = false, $_modifiers = false, $_format = 'Y-m-d'
) {
    if ( NEW_RELIC ) { newrelic_add_custom_tracer('validateIsoDate'); } // phpcs:ignore
    $regex = '/^(19|20)[0-9]{2}-(0[0-9]|1[0-2])-([0-2][0-9]|3[01])$/';

    $_tmp = strtotime($input);
    if ($_tmp === false || !preg_match($regex, trim($input))) {
        return $_default;
    }

    $_min = _limitDate('min', $_modifiers);
    $_max = _limitDate('max', $_modifiers);
    $_days = _limitDays('days', $_modifiers);

    return (($_min === true || $_min < $_tmp)
            && ($_max === true || $_max > $_tmp)
            && ($_days === true || in_array(date('D', $_tmp), $_days)))
        ? date('Y-m-d', $_tmp)
        : $_default;
}

/**
 * Validates an ISO 8601 date time formatted string and is between
 * min & max if supplied
 *
 * @param string       $input      Value supplied by user
 * @param string|false $_default   Default value to be returned
 * @param array|false  $_modifiers Array containing any of the
 *                                 following keys:
 *                                 `min` Minimum date/time (can be
 *                                 either a unix timestamp or an ISO
 *                                 8601 date/time string (or any
 *                                 string parsable by strtotime())
 *                                 `max` Maximium date/time
 *                                 (same as `min`)
 *                                 `mintime` Minimum time of day
 *                                 allowed (can be either number of
 *                                 seconds after midnight or an
 *                                 ISO 8601 time string)
 *                                 `maxtime` Maximum time of day
 *                                 (same as `mintime`)
 *                                 `days` Comma separated list of
 *                                 names of days of the week allowed
 *
 * @return string|false
 */
function validateIsoDateTime(
    $input, $_default = false, $_modifiers = false
) {
    if ( NEW_RELIC ) { newrelic_add_custom_tracer('validateIsoDateTime'); } // phpcs:ignore

    $regex = '/^(19|20)[0-9]{2}-(0[0-9]|1[0-2])-([0-2][0-9]|3[01])[T ]([01][0-9]|2[0-3]):([0-5][0-9])(?::([0-5][0-9]))?([+-](?:0[0-9]|1[01])(?::?[0-5][0-9])?|Z|[A-Z]{2,5})?$/'; // phpcs:ignore

    $_tmp = strtotime($input);
    if ($_tmp === false || !preg_match($regex, trim($input), $matches)) {
        return $_default;
    }

    $_min = _limitDate('min', $_modifiers);
    $_max = _limitDate('max', $_modifiers);
    $_minTime = _limitTime('mintime', $_modifiers);
    $_maxTime = _limitTime('maxtime', $_modifiers);
    $_days = _limitDays('days', $_modifiers);
    $time = (($matches[4] * 3600) + ($matches[5] * 60));

    return (($_min === true || $_min < $_tmp)
            && ($_max === true || $_max > $_tmp)
            && ($_minTime === true || $_minTime < $time)
            && ($_maxTime === true || $_maxTime > $time)
            && ($_days === true || in_array(date('D', $time), $_days)))
        ? date('Y-m-d H:i:s', $_tmp)
        : $_default;
}

/**
 * Converts the returned value of an `<input type="time" />` field
 * into the an integer between 1 & 24 so it can be stored in the
 * db
 *
 * @param string       $time      ISO8601 Time string supplied by user
 *                                (must include seconds)
 * @param string|false $_default  Default value to be returned
 * @param array|false  $_modifier Array containing a min & max value
 *                                both of which can be either
 *                                a hour of day, the number of seconds
 *                                after midnight or an ISO 8601 time
 *                                string (with or without seconds)
 *
 * @return string|false
 */
function validateIsoTime(string $time, $_default = false, $_modifier = false)
{
    if ( NEW_RELIC ) { newrelic_add_custom_tracer('validateIsoTime'); } // phpcs:ignore

    $_time = trim($time);
    $regex1 = '/^([01]?[0-9]|2[0-3]):([0-5][0-9])(?::([0-5][0-9]))?$/';

    if (preg_match($regex1, $_time, $matches)) {
        if (!isset($matches[3])) {
            $matches[3] = 0;
            $_time .= ':00';
        }
        if (strlen($matches[1]) === 1) {
            $_time = '0'.$_time;
        }
        if (!is_array($_modifier) || (!array_key_exists('min', $_modifier)
            && !array_key_exists('max', $_modifier))
        ) {
            return $_time;
        }

        $_tmp = (($matches[1] * 3600) + ($matches[2] * 60) + $matches[3]);
        $_min = _limitTime('min', $_modifier);
        $_max = _limitTime('max', $_modifier);

        return (($_min === true || $_min < $_tmp)
                && ($_max === true || $_max > $_tmp))
            ? $_time
            : $_default;

    } else {
        return $_default;
    }
}

/**
 * Checks whether a string is a valid SecurePay refid
 *
 * @param string       $refID    refid to be parsed
 * @param string|false $_default Default value to be returned
 *
 * @return string|false refid if it's valid, FALSE otherwise
 */
function validateRefID($refID, $_default = false)
{
    if ( NEW_RELIC ) { newrelic_add_custom_tracer('validateRefID'); } // phpcs:ignore

    if (!is_string($refID)) {
        return $_default;
    }

    $regex = '/^[a-z0-9]+_[1-9][0-9]+$/i';
    $refID = trim($refID);

    return preg_match($regex, $refID)
        ? $refID
        : $_default;
}

/**
 * Checks whether a string is a valid URL
 *
 * @param string       $url      URL to be parsed
 * @param string|false $_default Default value to be returned
 *
 * @return string|false URL if it's valid, FALSE otherwise
 */
function validateURL($url, $_default = false)
{
    if ( NEW_RELIC ) { newrelic_add_custom_tracer('validateURL'); } // phpcs:ignore

    $regex = '`^https://[\d\w-]+(\.[\d\w-]+)+(\.[a-z]+){1,2}/`i';

    return preg_match($regex, $url) ? $url : $_default;
}

/**
 * Validate Year
 *
 * @param string|integer $year       Year to be validated
 * @param mixed          $_default   Default value to return if year
 *                                   is invalid
 * @param array|false    $_modifiers Min/Max limits for year
 *
 * @return integer|mixed If year is valid then Integer is returned.
 *                       Otherwise, default is returned
 */
function validateYear($year, $_default = false, $_modifiers = false)
{
    if (is_numeric($year) && is_int($year * 1)) {
        settype($year, 'integer');
        $absMinY = date('Y', ABSOLUTE_MIN_TIME);
        $absMaxY = date('Y', ABSOLUTE_MAX_TIME);
        // debug($absMinY, $absMaxY);

        if (!is_array($_modifiers)) {
            // debug('No modifiers');
            if ($year < $absMinY || $year > $absMaxY) {
                return $_default;
            } else {
                return $year;
            }
        } else {
            $_min = _yearLimit('min', $_modifiers);

            if (($_min !== false && $year < $_min) || $year < $absMinY) {
                return $_default;
            }

            $_max = _yearLimit('max', $_modifiers);

            if (($_max !== false && $year > $_max) || $year > $absMaxY) {
                return $_default;
            }

            return $year;
        }
    }
    debug('Not an integer');

    return $_default;
}


//  END:  Validation functions
// =======================================================

/**
 * Convert a boolean value into the integer, zero or one
 *
 * @param boolean $input Input to be converted
 *
 * @return integer 1 or 0 for TRUE & FALSE respectively
 */
function boolToInt($input)
{
    if ( NEW_RELIC ) { newrelic_add_custom_tracer('boolToInt'); } // phpcs:ignore

    return ($input === true) ? 1 : 0;
}

/**
 * Convert a number into a string that can be used for displaying
 * monetary values
 *
 * @param string|int|float $amount     Numeric value to be converted
 * @param boolean          $showDollar Show "$" before amount
 *
 * @return string A numeric string with two decimal points.
 */
function getMoney($amount, $showDollar = true) : string
{
    if ( NEW_RELIC ) { newrelic_add_custom_tracer('getMoney'); } // phpcs:ignore

    if (is_numeric($amount)) {
        $prefix = ($showDollar === true)
            ? '$'
            : '';

        return $prefix.number_format($amount, 2, '.', '');
    } else {
        trigger_error(
            'getMoney() expects first parameter $amount to be a '.
            'numeric value. '.gettype($amount).' given.',
            E_USER_ERROR
        );
    }
}

/**
 * Convert a camel case string a character sperated string
 *
 * @param string $input String to be converted
 * @param bool   $upper Make output uppercase (like PHP constant)
 * @param string $sep   Character to use as snake/kebab case
 *                      separator
 *
 * @return string Separated string
 */
function camelToSeparatedStr(
    string $input ,
    bool $upper = false,
    string $sep = '_'
) : string {
    $output = strtolower(
        preg_replace(
            '/(?<=[a-z0-9])(?=[A-Z0-9][a-z0-9]+)/', $sep, $input
        )
    );

    return ($input !== $output && $upper === true)
      ? strtoupper($output)
      : $output;
}

/**
 * Convert a camel case string to snake case
 *
 * @param string $input String to be converted
 * @param bool   $upper Make output uppercase (like PHP constant)
 *
 * @return string
 */
function camelToSnake(string $input, bool $upper = false) : string
{
    return camelToSeparatedStr($input, $upper, '_');
}

/**
 * Convert a camel case string to kebab case
 *
 * @param string  $input String to be converted
 * @param boolean $upper Make output uppercase
 *
 * @return string
 */
function camelToKebab(string $input, bool $upper = false) : string
{
    return camelToSeparatedStr($input, $upper, '-');
}

/**
 * Convert a camel case string to kebab case
 *
 * @param string $input String to be converted
 *
 * @return string
 */
function snakeToText(string $input) : string
{
    return preg_replace('/[_-]/i', ' ', $input);
}

/**
 * Convert snake case string to camel case
 *
 * NOTE: input can be hyphen case or camel case
 *
 * @param string  $input String to be converted
 * @param integer $start Index of the first part to be included
 *                       in the output
 *
 * @return string
 */
function snakeToCamel(string $input, int $start = 0) : string
{
    $splitter = '';
    if (preg_match('/[ _-]/', $input, $matches)) {
        $splitter = $matches[0];
    }

    if ($splitter !== '') {
        $tmp = explode($splitter, $input);
        $output = strtolower($tmp[$start]);

        for ($a = $start + 1; $a < count($tmp); $a += 1) {
            $output += ucfirst(strtolower($tmp[$a]));
        }

        return $output;
    } else {
        return $input;
    }
}

/**
 * Convert snake case string to camel case
 *
 * NOTE: input can be hyphen case or camel case
 *
 * @param string  $input String to be converted
 * @param string  $sep   Separator character to replace white space
 * @param boolean $upper Whether or not to covert output to uppercase
 *                       before returning
 *
 * @return string
 */
function textToSnake(string $input, string $sep = '_', bool $upper = false) : string
{
    $output = preg_replace('/\s+/', $sep, $input);

    return ($upper === true)
        ? strtoupper($output)
        : $output;
}


/**
 * Render a comment to screen;
 *
 * @param string|array $msg    Message string (or list of message
 *                             strings) to be rendered in HTML
 *                             comments
 * @param boolean|null $start  Whether comment is for start of block
 *                             (or end)
 * @param boolean      $return Whether or not to return the comment
 *                             string or echo it out
 *
 * @return void|string
 */
function cmnt($msg, $start = null, $return = false)
{
    if (NEW_RELIC) { newrelic_add_custom_tracer('cmnt'); } // phpcs:ignore

    if (SHOW_COMMENTS === true) {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[0];

        $firstMsg = '';
        $extraMsg = '';
        if (is_array($msg)) {
            foreach ($msg as $key => $value) {
                $tmp = (is_int($key))
                    ? $value
                    : $key.': '.$value;
                if ($firstMsg === '') {
                    $firstMsg = $tmp;
                } else {
                    $extraMsg .= "\n ! $tmp";
                }
            }
        } else {
            $firstMsg = $msg;
        }

        $output = '';

        if ($start !== null) {
            $prefix = $start === true
                ? 'START:'
                : ' END: ';
            $output = "\n\n<!--\n ! $prefix $firstMsg\n" .
                " ! File: {$backtrace['file']}\n" .
                " ! Line: {$backtrace['line']}$extraMsg\n ! -->\n\n";
        } else {
            if ($firstMsg !== '') {
                $firstMsg = "\n ! $firstMsg";
            }
            $output = "\n\n<!-- (Line: {$backtrace['line']}; " .
                                "File: {$backtrace['file']})".
                $firstMsg.$extraMsg."\n ! -->\n\n";
        }
        if ($return === true) {
            return $output;
        } else {
            echo $output;
        }
    }
}

/**
 * Join an array of things into a gramatically correct, comma
 * separated list.
 *
 * @param array<string> $input List of things to make human readable
 * @param string        $amp   Word or symbol to join the second last
 *                             & last items in the list
 *
 * @return string
 */
function humanList(array $input, string $amp = '&') : string
{
    if (NEW_RELIC) { newrelic_add_custom_tracer('humanList'); } // phpcs:ignore

    $last = array_pop($input);
    $sep = '';
    $output = '';

    for ($a = 0, $c = count($input); $a < $c; $a += 1) {
        $output .= $sep.$input[$a];
        $sep = ', ';
    }

    $and = ($sep !== '')
        ? " $amp "
        : '';

    return $output.$and.$last;
}

/**
 * Get a long human friendly date/time string for the specified
 * timestamp
 *
 * @param integer $timestamp Timestamp
 * @param boolean $timeFirst Whether or not to render the time part
 *                           of the date time string first
 * @param boolean $html      Whether or not wrap the nth string in
 *                           HTML <SUP> tags
 *
 * @return string
 */
function humanDate(
    int $timestamp, bool $timeFirst = false, bool $html = false
) : string {
    if (NEW_RELIC) { newrelic_add_custom_tracer('humanDate'); } // phpcs:ignore

    $_dateStr = ($html === true)
        ? 'l \t\h\e jS \o\f F, Y'
        : 'l \t\h\e j<\s\u\p>S</\s\u\p> \o\f F, Y';

    return ($timeFirst === false)
        ? date('D, jS F Y, g:i a', $timestamp)
        : date('g:ia \o\n '.$_dateStr, $timestamp);
}

/**
 * Get the full amount paid/asked, GST part and base part
 *
 * @param float   $price   The amount from which to extract the GST
 *                         part and base amount
 * @param boolean $showGST Include GST & base value parts of amount
 *                         in output
 *
 * @return array<float> `amount` => full total amount paid;
 *                      `gst`   =>  GST part of amount paid;
 *                      `base`  =>  Base amount before GST;
 */
function getPriceParts(float $price, bool $showGST) : array
{
    if (NEW_RELIC) { newrelic_add_custom_tracer('getPriceParts'); } // phpcs:ignore

    $output = [
        'amount' => $price,
        'gst' => 0.0,
        'base' => 0.0
    ];

    if ($showGST === true) {
        $gstPart = $price * GST_FACTOR;

        $output['gst'] = $gstPart;
        $output['base'] = $price - $gstPart;
    }

    return $output;
}

/**
 * Get a limit date/datetime as Unix timestamp
 *
 * @param string $key limit ley in array
 * @param mixed  $arr Array in which time limit key is to be found
 *
 * @return integer|true TRUE if there's no limit or a Unix Timestamp
 *                      representing an upper or lower limit to the
 *                      date/datetime allowed
 */
function _limitDate(string $key, $arr)
{
    if (!is_array($arr) || !array_key_exists($key, $arr)) {
        return true;
    }

    $val = is_string($arr[$key])
        ? strtotime($arr[$key])
        : $arr[$key];

    if ($val === false
        || $val < ABSOLUTE_MIN_TIME
        || $val > ABSOLUTE_MAX_TIME
    ) {
        trigger_error(
            'Minimum/Maximum validation value supplied to '.
            'validateIsoDate() or validateIsoDateTime() is invalid. '.
            'Expected an unix timestamp (integer) or string parsable '.
            'by `strtotime()`. '.
            'It must also represent a date/time that falls between '.
            date('Y-m-d H:i:s', ABSOLUTE_MIN_TIME).' & '.
            date('Y-m-d H:i:s', ABSOLUTE_MAX_TIME).' (inclusive). '.
            $arr[$key].' given',
            E_USER_ERROR
        );
    }

    if (is_int($val)) {
        // Assume this is a unix timestamp
        return $val;
    } elseif (is_string($val)) {
        $output = strtotime($val);

        if ($output !== false) {
            // We now have a unix timestamp
            return $output;
        }
    }
    // There was something there but we couldn't convert it.
    trigger_error(
        'Minimum/Maximum validation value supplied to '.
        'validateIsoDate() or validateIsoDateTime() is invalid',
        E_USER_ERROR
    );
}

/**
 * Get a limit time as seconds in a day (or true if there's no limit)
 *
 * @param string $key limit ley in array
 * @param mixed  $arr Array in which time limit key is to be found
 *
 * @return integer|true TRUE if there's no limit or the number of
 *                      seconds in a day representing an upper or
 *                      lower limit to allowable time
 */
function _limitTime(string $key, $arr)
{
    $_key = strtolower($key);
    $_val = false;

    if (!is_array($arr)) {
        return true;
    }
    foreach ($arr as $_prop => $value) {
        $_prop = strtolower($_prop);
        if ($_key === $_prop) {
            $_val = $value;
            break;
        }
    }

    if ($_val === false) {
        return true;
    }

    $regex = '/^([01][0-9]|2[0-3]):([05][0-9])(?::[05][0-9])?$/';

    if (is_numeric($_val)) {
        if ($_val < 24) {
            // Assume that the limit is an hour of the day and
            // convert that to seconds in a day
            return $_val * 3600;
        } elseif ($_val < 86400) {
            // Assume that the limit is the number of seconds in a day
            // This is what we want so just return it.
            return $_val;
        }
    } elseif (is_string($_val) && preg_match($regex, trim($_val), $matches)) {
        // Assume limit is an ISO 8601 time formatted string and
        // convert that to seconds in a day
        return ($matches[1] * 3600) + $matches[2] * 60;
    }

    // There was something there but we couldn't convert it.
    trigger_error(
        'Minimum/Maximum validation value supplied to '.
        'validateIsoTime() is invalid',
        E_USER_ERROR
    );
}

/**
 * Get valid days of the week
 *
 * @param string      $_key       Key in modifiers array
 * @param array|false $_modifiers List of modifiers supplied by
 *                                calling code
 *
 * @return string[]|true List allowed days of the week or TRUE if no
 *                       days are specified
 */
function _limitDays(string $_key, $_modifiers)
{
    if (is_array($_modifiers)
        && array_key_exists($_key, $_modifiers)
        && $_modifiers[$_key] !== ''
    ) {
        $_days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        $days = explode(',', $_modifiers[$_key]);

        $output = [];
        for ($a = 0; $a < count($days); $a += 1) {
            $day = strtolower(substr(trim($days[$a]), 0, 3));

            if (in_array($day, $_days) && !in_array($day, $output)) {
                // Make day string compatible with date('D');
                $output[] = ucfirst($day);
            }
        }

        return $output;
    }
    return true;
}

/**
 * Validate year min & max limits
 *
 * @param string $_key       Key in modifiers array
 * @param array  $_modifiers List of modifiers supplied by calling code
 *
 * @return void
 */
function _yearLimit(string $_key, array $_modifiers)
{
    if (array_key_exists($_key, $_modifiers)) {
        $absMin = date('Y', ABSOLUTE_MIN_TIME);
        $absMax = date('Y', ABSOLUTE_MAX_TIME);

        $_val = $_modifiers[$_key];

        if (is_string($_val)) {
            // Massage the value into the right shape
            if (!is_numeric($_val)) {
                $_tmp = strtotime($_val);
                if ($_tmp !== false) {
                    $_tmp = date('Y', $_tmp);
                    $_val = $_tmp;
                }
            }
            settype($_val, 'integer');
        }

        if (!is_int($_val)
            || $_val < $absMin
            || $_val > $absMax
        ) {
            trigger_error(
                'validateYear() expects '.$_key.' value to be an '.
                'integer between or equal to '.$absMin.' & '.$absMax.
                ' or a string parsable by `strtotime()`. "'.
                $_val.'" given',
                E_USER_ERROR
            );
        } else {
            return $_val;
        }
    }

    return false;
}

/**
 * Make sure the limit is within range.
 *
 * @param mixed   $input   Value to use a limit
 * @param integer $default Default value to use if unable to get
 *                         new value
 * @param integer $min     Minimum value allowed
 * @param integer $max     Maximum value allowed
 *
 * @return integer
 */
function _charLimit($input, int $default, int $min, int $max) : int
{
    if (is_numeric($input)) {
        settype($input, 'integer');

        if ($input < $min) {
            return $min;
        } else {
            return($input < $max)
                ? $input
                : $max;
        }
    } else {
        return $default;
    }
}

/**
 * Append new characters to the allowed characters in a regex
 * character class
 *
 * @param string $input   New characters to append to allowed
 *                        characters
 * @param string $allowed Default allowed characters for validation
 *
 * @return string (Possibly) modified version of $allowed containing
 *                the original characters plus any new unique
 *                characters that are also allowed in validation
 */
function _txtAllowed($input, $allowed) : string
{
    if (is_string($input)) {
        $_tmp = _sanitiseCharClass($input);
        for ($a = 0; $a < count($_tmp); $a += 1) {
            if (substr_count($allowed, $_tmp[$a]) === 0) {
                $allowed .= $_tmp[$a];
            }
        }
    }
    return $allowed;
}

/**
 * Sanitise characters that are to be used in a regex character class
 *
 * @param string $input Characters to be used in a character class
 *
 * @return array List of characters that are safe to use in a regex
 *               character class
 */
function _sanitiseCharClass(string $input) : string
{
    if ($input === '') {
        return [];
    }

    $output = [];
    $_escapes = [
        'd', 'D', 'h','H', 's', 'S', 'v', 'V', 'w', 'W', 'b', 'B', 't', 'r', 'n'
    ];
    $regex = '/([a-z]-[a-z]|[0-9]-[0-9])/i';
    if (preg_match_all($regex, $input, $matches, PREG_SET_ORDER)) {
        for ($a = 0; $a < count($matches); $a += 1) {
            $output = $matches[$a][1];
            $input = str_replace($matches[$a][1], '', $input);
        }
    }
    $_tmp = str_split($input);
    for ($a = 0; $a < count($_tmp); $a += 1) {
        $b = $a + 1;
        if ($_tmp[$a] === '\\' && isset($_tmp[$b])
            && in_array($_tmp[$b], $_escapes)
        ) {
            $output[] = '\\'.$_tmp[$b];
            $a += 1;
        } else {
            $output[] = (in_array($_tmp[$a], [']', '^', '-', '\\', '/']))
                ? '\\'.$_tmp[$a]
                : $_tmp[$a];
        }
    }
    return implode('', $output);
}

define('HELPER_FUNCTIONS', true);

if (!defined('NEW_RELIC')) {
    define('NEW_RELIC', extension_loaded('newrelic'));
}
