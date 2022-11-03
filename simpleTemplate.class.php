<?php

/**
 * This file provides a super simple template rendering tool
 *
 * It's a single class with a single public (static) method: render()
 * SimpleTmpl::render() behaves like a pure function because the
 * template it uses is cached the first time it's used.
 *
 * PHP version 7.4
 *
 * @category SimpleTmpl
 * @package  SimpleTmpl
 * @author   Evan Wills <evan.i.wills@gmail.com>
 * @license  GPL2 <https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html>
 * @link     https://github.com/evanwills/php-utils
 */

if (!function_exists('debug')) { function debug() {} } // phpcs:ignore

/**
 * This class provides helper functions that build on top of PHP's
 * PDO classes. It doesn't extend PDO but instead is a wrapper for
 * PDO with some methods for doing very common DB stuff
 *
 * @category SimpleTmpl
 * @package  SimpleTmpl
 * @author   Evan Wills <evan.i.wills@gmail.com>
 * @license  GPL2 <https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html>
 * @link     https://github.com/evanwills/php-utils
 */
class SimpleTmpl
{
    /**
     * Cache of previously encountered templates
     *
     * @var SimpleTmpl[]
     */
    static private $_cache = [];

    /**
     * File system path to templates directory
     *
     * @var string
     */
    static private $_path = '';

    /**
     * Pattern for matching template tokens
     *
     * @var string
     */
    static private $_regex = '/\{\{(.*?)\}\}/i';

    /**
     * List of template tokens for this template
     *
     * @var array
     */
    private $_keys = [];

    /**
     * Template string containing tokens
     *
     * @var string
     */
    private $_tmpl = '';

    /**
     * Initialise a template
     *
     * @param string $fileName Name of file (in templates directory)
     *                         where template can be found
     *
     * @throws Exception If template directory hasn't been set yet or
     *                   if template file cannot be found.
     */
    private function __construct(string $fileName)
    {
        $file = realpath(self::$_path.$fileName);
        if (!is_file($file) || !is_readable($file)) {
            if (self::$_path === '') {
                $msg = 'Template directory path has been set yet.';
            } else {
                $msg = 'Could not find template file "'.$fileName.
                       '" ('.self::$_path.$fileName.')';
            }
            throw new Exception($msg);
        }

        $this->_tmpl = file_get_contents($file);

        if (preg_match_all(self::$_regex, $this->_tmpl, $tokens, PREG_SET_ORDER)) {
            for ($a = 0; $a < count($tokens); $a += 1) {
                $keys[$tokens[$a][1]] = $tokens[$a][0];
            }
        }
    }

    /**
     * Render the contents of the supplied data array/object into a
     * template
     *
     * > __Note:__ If the data array contains keys not referenced in
     * >           the template, the unused keys are ignored.
     *
     * @param string       $tmplName    File name of the template
     *                                  file to be used
     * @param array|object $data        Associative/hash array or
     *                                  object of key/value pairs
     *                                  where the key/prop matches
     *                                  the name of a token in the
     *                                  template beingv called
     * @param boolean      $camel2snake Whether or not to convert
     *                                  $data key/property names to
     *                                  uppercase snake case format
     *
     * @return string
     */
    static public function render(
        string $tmplName, $data, bool $camel2snake = true
    ) : string {
        if (!array_key_exists($tmplName, self::$_cache)) {
            try {
                self::$_cache[$tmplName] = new self($tmplName);
            } catch (Exception $e) {
                throw $e;
            }
        }

        return self::$_cache[$tmplName]->_render($data, $camel2snake);
    }

    /**
     * Do the actual rendering of the data into the template.
     *
     * > __Note:__ Any tokens in the template that don't have values
     * >           in the supplied data will be replaced with empty
     * >           strings.
     *
     * @param array|object $data        Associative/hash array or
     *                                  object of key/value pairs
     *                                  where the key/prop matches
     *                                  the name of a token in the
     *                                  template beingv called
     * @param boolean      $camel2snake Whether or not to convert
     *                                  $data key/property names to
     *                                  uppercase snake case format
     *
     * @return string
     * @throws Exception if $data is not an object or an array
     */
    private function _render($data, bool $camel2snake) : string
    {
        if (is_object($data)) {
            /**
             * Get replacement value for supplied key
             *
             * @param string $key  Possible object property name
             * @param object $data Object to search for property
             *
             * @return boolean TRUE if key is found in array. FALSE other
             */
            $getReplace = function ($key, $data) {
                $output = (property_exists($data, $key))
                    ? $data->$key
                    : '';

                return (is_scalar($output))
                    ? $output
                    : '';
            };
        } elseif (is_array($data)) {
            /**
             * Get replacement value for supplied key
             *
             * @param string $key  Possible array key
             * @param array  $data Array to search for key
             *
             * @return boolean TRUE if key is found in array. FALSE other
             */
            $getReplace = function ($key, $data) {
                $output = (array_key_exists($key, $data))
                    ? $data[$key]
                    : '';

                return (is_scalar($output))
                    ? $output
                    : '';
            };
        } else {
            throw new Exception(
                'Expected data supplied to SimpleTmpl::render() to '.
                'be an associative array or object with public '.
                'properties'
            );
        }

        $find = [];
        $replace = [];

        foreach ($this->_keys as $key => $token) {
            $find[] = $token;
            $replace[] = $getReplace($key, $data);
        }

        return str_replace($find, $replace, $this->_tmpl);
    }

    /**
     * Set file system path to template directory for application.
     *
     * @param string $path File system path to template directory
     *
     * @return boolean
     */
    static public function setPath(string $path) : bool
    {
        $path = realpath($path);

        if ($path !== '' && is_dir($path)) {
            self::$_path = $path.DIRECTORY_SEPARATOR;
            return true;
        }

        return false;
    }

    /**
     * Set opening and closing characters for template tokens
     *
     * @param string $open  Opening characters for template token.
     *                      [Default: '{{']
     * @param string $close Closing characters for template token.
     *                      [Default: '}}']
     *
     * @return boolean
     */
    static public function setWrapper(string $open, string $close) : bool
    {
        $l = strlen($open);
        if ($l > 0 && $l < 5) {
            $open = preg_quote($open);

            $l = strlen($close);
            if ($l > 0 & $l < 5) {
                $close = preg_quote($close);

                self::$_regex = "/$open(.*?)$close/i";
                return true;
            }
        }

        return false;
    }
}
