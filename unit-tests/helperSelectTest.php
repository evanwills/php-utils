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
class HelperSelectTest extends TestCase
{
    private $_campuses = [
        'Adelaide',
        'Ballarat',
        'Blacktown',
        'Brisbane',
        'Canberra',
        'Melbourne',
        'North Sydney',
        'Strathfield',
        'Online',
        'National',
        'Offshore'
    ];
    private $_reasons = [
        'lost_stolen_destroyed', 'damaged', 'name_change'
    ];

    /**
     * Test that the happy path for makeFloat() behaves as expected
     *
     * @return void
     */
    public function testSelectGood()
    {
        $input = 'North Sydney';
        $eOut = 'North Sydney';
        $aOut = validateSelected($input, $this->_campuses, '');

        $this->assertIsString(
            $aOut,
            'Should Always be a string'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'The output of `validateSelected()` always match one of '.
            'the items in the options array'
        );
    }
}
