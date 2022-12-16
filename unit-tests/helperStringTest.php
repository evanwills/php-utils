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
class HelperStringTest extends TestCase
{
    /**
     * Test that the happy path for makeFloat() behaves as expected
     *
     * @return void
     */
    public function testTextGood()
    {
        $input = 'You should always wrapped at or before the specified width. '.
        'So if you have a word that is larger than the given '.
        'width, it is broken apart. (See second example). When '.
        'false the function does not split the word even if the '.
        'width is smaller than the word width.';
        $eOut = 'You should always wrapped at or before the '.
                'specified width. So if you have a word that is '.
                'larger than the given width, it is bro';
        $aOut = sanitiseText($input);

        $this->assertIsString(
            $aOut,
            'Should Always be a string'
        );
        $this->assertEquals(
            128, strlen($aOut),
            'The string length of `sanitiseText()` output should '.
            'be truncated to 128 characters.'."\n\"$aOut\""
        );
        $this->assertEquals(
            $eOut, $aOut,
            'The output of `sanitiseText()` should be a truncated '.
            'version of input'
        );
    }

}
