<?php
/**
 * This file contains a collection of unit tests for testing "pure"
 * stand-alone helper functions. In particular this tests numeric
 * validation/sanitisation functions
 *
 * PHP Version 7.2
 *
 * @category Validation
 * @package  Validation
 * @author   Evan Wills <evan.i.wills@gmail.com>
 * @license  GPL2 <https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html>
 * @link     https://github.com/evanwills/php-utils
 */

require_once realpath(__DIR__.'/../vendor/autoload.php');
require_once realpath(__DIR__.'/../debug.inc.php');
require_once realpath(__DIR__.'/../helper-functions.inc.php');

// use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
// use SebastianBergmann\RecursionContext\InvalidArgumentException;

/**
 * This file contains a collection of unit tests for testing "pure"
 * stand-alone helper functions. In particular this tests numeric
 * validation/sanitisation functions
 *
 * @category Validation
 * @package  Validation
 * @author   Evan Wills <evan.i.wills@gmail.com>
 * @license  GPL2 <https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html>
 * @link     https://github.com/evanwills/php-utils
 */
class HelperDateTimeTest extends TestCase
{
    /**
     * Test that the happy path for makeFloat() behaves as expected
     *
     * @return void
     */
    public function testYearGood()
    {
        $input = 2019;
        $eOut = 2019;
        $aOut = validateYear($input);

        $this->assertIsInt(
            $aOut,
            'Should be integer if integer is supplied and within '.
            'default valid range - 110 years & + 50 years'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'The output of `sanitiseText()` should be a truncated '.
            'version of input'
        );
    }

}
