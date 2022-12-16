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

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

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
class HelperNumericTest extends TestCase
{
    /**
     * Test that the happy path for makeFloat() behaves as expected
     *
     * @return void
     */
    public function testMakeFloatGood1()
    {
        $input = '1.23';
        $eOut = 1.23;
        $aOut = makeFloat($input);

        $this->assertIsFloat(
            $aOut,
            'Numeric string (float '.$eOut.') can be converted to '.
            'a float'
        );
        $this->assertEquals(
            $eOut,
            $aOut,
            'Numeric string ('.$eOut.') is correctly converted to '.
            'a float'
        );
    }

    /**
     * Test that the happy path for makeFloat() behaves as expected
     *
     * @return void
     */
    public function testMakeFloatGood2()
    {
        $input = '45';
        $eOut = 45.0;
        $aOut = makeFloat($input);

        $this->assertIsFloat(
            $aOut,
            'Numeric string (int '.$eOut.') can be converted to a '.
            'float'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Numeric string (int '.$eOut.') is correctly converted '.
            'to a float'
        );
    }

    /**
     * Test that the happy path for makeFloat() behaves as expected
     *
     * @return void
     */
    public function testMakeFloatGood3()
    {
        $input = '-1.23';
        $eOut = -1.23;
        $aOut = makeFloat($input);

        $this->assertIsFloat(
            $aOut,
            'Numeric string (float '.$eOut.') can be converted to '.
            'a float'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Numeric string ('.$eOut.') is correctly converted to '.
            'a float'
        );
    }

    /**
     * Test that the happy path for makeFloat() behaves as expected
     *
     * @return void
     */
    public function testMakeFloatGood4()
    {
        $input = '-45';
        $eOut = -45.0;
        $aOut = makeFloat($input);

        $this->assertIsFloat(
            $aOut,
            'Numeric string (int '.$eOut.') can be converted to a '.
            'float'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Numeric string (int '.$eOut.') is correctly converted '.
            'to a float'
        );
    }

    /**
     * Test that the happy path for makeFloat() behaves as expected
     *
     * @return void
     */
    public function testMakeFloatGood5()
    {
        $input = 'asdg-1.23';
        $eOut = 0.0;
        $aOut = makeFloat($input);

        $this->assertIsFloat(
            $aOut,
            'Non-numeric string cannot be converted to a float so '.
            'default ('.$eOut.') is returned'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Non-numeric string cannot be converted to a float so '.
            'default ('.$eOut.') is returned'
        );
    }

    /**
     * Test that the happy path for makeFloat() behaves as expected
     *
     * @return void
     */
    public function testMakeFloatGood6()
    {
        $input = '-erou45';
        $eOut = 0.0;
        $aOut = makeFloat($input);

        $this->assertIsFloat(
            $aOut,
            'Non-numeric string cannot be converted to a float so '.
            'default ('.$eOut.') is returned'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Non-numeric string cannot be converted to a float so '.
            'custom default ('.$eOut.') is returned'
        );
    }

    /**
     * Test that the sad path for makeFloat() behaves as expected and
     * callers are warned of poor usage
     *
     * @return void
     */
    public function testMakeFloatBad1()
    {
        $this->expectError();
        // Optionally test that the message is equal to a string
        $this->expectErrorMessage(
            'Argument 2 passed to makeFloat() must be '.
            'of the type float'
        );
        makeFloat('1.23', 'abcd');
    }


    //  END:  makeFloat()
    // --------------------------------------------------------------
    // START: makeInt()


    /**
     * Test that the happy path for makeInt() behaves as expected
     *
     * @return void
     */
    public function testMakeIntGood1()
    {
        $input = '45';
        $eOut = 45;
        $aOut = makeInt($input);

        $this->assertIsInt(
            $aOut,
            'Numeric string (int '.$input.') can be converted to '.
            'an integer'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Numeric string (int '.$input.') is correctly '.
            'converted to an integer'
        );
    }

    /**
     * Test that the happy path for makeInt() behaves as expected
     *
     * @return void
     */
    public function testMakeIntGood2()
    {
        $input = '1652184544';
        $eOut = 1652184544;
        $aOut = makeInt($input);

        $this->assertIsInt(
            $aOut,
            'Numeric string (int '.$input.') can be converted to '.
            'an integer'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Numeric string (int '.$input.') is correctly '.
            'converted to an integer'
        );
    }

    /**
     * Test that the happy path for makeInt() behaves as expected
     *
     * @return void
     */
    public function testMakeIntGood3()
    {
        $input = '-95027880';
        $eOut = -95027880;
        $aOut = makeInt($input);

        $this->assertIsInt(
            $aOut,
            'Numeric string (int '.$input.') can be converted to an integer'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Numeric string ('.$input.') is correctly converted to an integer'
        );
    }

    /**
     * Test that the happy path for makeInt() behaves as expected
     *
     * @return void
     */
    public function testMakeIntGood4()
    {
        $input = '1.23';
        $eOut = 1;
        $aOut = makeInt($input);

        $this->assertIsInt(
            $aOut,
            'Numeric string (float '.$input.') can be converted to an integer'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Numeric string ('.$input.') is correctly converted to an integer'
        );
    }

    /**
     * Test that the happy path for makeInt() behaves as expected
     *
     * @return void
     */
    public function testMakeIntGood5()
    {
        $input = '1.987';
        $eOut = 1;
        $aOut = makeInt($input);

        $this->assertIsInt(
            $aOut,
            'Numeric string (float '.$input.') can be converted to '.
            'an integer'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Numeric string ('.$input.') is correctly converted to '.
            'an integer'
        );
    }

    /**
     * Test that the happy path for makeInt() behaves as expected
     *
     * @return void
     */
    public function testMakeIntGood6()
    {
        $input = 'asdg-1.23';
        $eOut = 0.0;
        $aOut = makeInt($input);

        $this->assertIsInt(
            $aOut,
            'Non-numeric string ('.$input.') cannot be converted '.
            'to an integer but integer is returned anyway.'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Non-numeric string ('.$input.') cannot be converted '.
            'to an integer so default (0.0) is returned'
        );

    }

    /**
     * Test that the happy path for makeInt() behaves as expected
     *
     * @return void
     */
    public function testMakeIntGood7()
    {
        $input = '-erou45';
        $eOut = 123;
        $aOut = makeInt('-erou45', 123);

        $this->assertIsInt(
            $aOut,
            'Non-numeric string ('.$input.') cannot be converted '.
            'to an integer but integer is returned anyway.'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Non-numeric string ('.$input.') cannot be converted '.
            'to an integer so custom default ('.$eOut.') is returned'
        );
    }

    /**
     * Test that the happy path for makeInt() behaves as expected and
     * callers are warned of bad usage
     *
     * @return void
     */
    public function testMakeIntBad1()
    {
        $this->expectError();
        // Optionally test that the message is equal to a string
        $this->expectErrorMessage(
            'Argument 2 passed to makeInt() must be '.
            'of the type int'
        );
        makeInt('1.23', 'abcd');
    }


    //  END:  makeInt()
    // --------------------------------------------------------------
    // START: sanitiseInt()


    /**
     * Test that the happy path for makeInt() behaves as expected
     *
     * @return void
     */
    public function testSanitiseInt1()
    {
        $input = '45';
        $eOut = 45;
        $aOut = sanitiseInt($input);

        $this->assertIsInt(
            $aOut,
            'Numeric string (int '.$input.') can be converted to '.
            'an integer'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Numeric string (int '.$input.') is correctly '.
            'converted to an integer'
        );
    }

    /**
     * Test that the happy path for makeInt() behaves as expected
     *
     * @return void
     */
    public function testSanitiseInt2()
    {
        $input = '-45';
        $eOut = -45;
        $aOut = sanitiseInt($input);

        $this->assertIsInt(
            $aOut,
            'Numeric string (int '.$input.') can be converted to '.
            'an integer'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Numeric string (int '.$input.') is correctly '.
            'converted to an integer'
        );
    }

    /**
     * Test that the happy path for makeInt() behaves as expected
     *
     * @return void
     */
    public function testSanitiseInt3()
    {
        $input = '1652184544';
        $eOut = 1652184544;
        $aOut = sanitiseInt($input);

        $this->assertIsInt(
            $aOut,
            'Numeric string (int '.$input.') can be converted to '.
            'an integer'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Numeric string (int '.$input.') is correctly '.
            'converted to an integer'
        );
    }

    /**
     * Test that the happy path for makeInt() behaves as expected
     *
     * @return void
     */
    public function testSanitiseInt4()
    {
        $input = '-95027880';
        $eOut = -95027880;
        $aOut = sanitiseInt($input);

        $this->assertIsInt(
            $aOut,
            'Numeric string (int '.$input.') is correctly '.
            'converted to an integer'
        );

        $this->assertEquals(
            $eOut, $aOut,
            'Numeric string (int '.$input.') can be converted to '.
            'an integer'
        );
    }

    /**
     * Test that the happy path for makeInt() behaves as expected
     *
     * @return void
     */
    public function testSanitiseInt5()
    {
        $input = '-593363160.95027880';
        $eOut = -593363161;
        $aOut = sanitiseInt($input);

        $this->assertIsInt(
            $aOut,
            'Numeric string (int '.$input.') is correctly '.
            'converted to an integer'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Numeric string (int '.$input.') is correctly '.
            'converted to an integer. NOTE: input is rounded '.
            'before being returned'
        );
    }

    /**
     * Test that the happy path for makeInt() behaves as expected
     *
     * @return void
     */
    public function testSanitiseInt6()
    {
        $input = ' asdg-1s.23st3 ';
        $eOut = -1;
        $aOut = sanitiseInt($input);

        $this->assertIsInt(
            $aOut,
            'Non-numeric characters are stripped out. Remaining '.
            'characters are numeric so can be converted into an '.
            'integer.'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Non-numeric characters are stipped leaving some '.
            'numeric characters which can be converted to an '.
            'integer so default ('.$eOut.') is returned'
        );
    }

    /**
     * Test that the happy path for makeInt() behaves as expected
     *
     * @return void
     */
    public function testSanitiseInt7()
    {
        $input = 'asdg95027880.73';
        $eOut = 95027881;
        $aOut = sanitiseInt($input);

        $this->assertIsInt(
            $aOut,
            'Non-numeric characters are stripped out. Remaining '.
            'characters are numeric so can be converted into an '.
            'integer.'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Non-numeric characters are stipped leaving some '.
            'numeric characters which can be converted to an '.
            'integer so default ('.$eOut.') is returned'
        );
    }

    /**
     * Test that the happy path for makeInt() behaves as expected
     *
     * @return void
     */
    public function testSanitiseInt8()
    {
        $input = 'One two three';
        $eOut = 123;
        $aOut = sanitiseInt($input, 123);

        $this->assertIsInt(
            $aOut,
            'Non-numeric string cannot be converted to an integer so '.
            'custom default (123) is returned'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Non-numeric string cannot be converted to an integer so '.
            'custom default (123) is returned'
        );
    }

    /**
     * Test that the happy path for makeInt() behaves as expected
     *
     * @return void
     */
    public function testSanitiseInt9()
    {
        $input = 'One two three';
        $eOut = 12;
        $aOut = sanitiseInt($input, 12.3356);

        $this->assertIsInt(
            $aOut,
            'Non-numeric string cannot be converted to an integer so '.
            'custom default (123) is returned'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Non-numeric string cannot be converted to an integer so '.
            'custom default (12) is returned. (PHP converts 12.6 '.
            'to int 12)'
        );
    }

    /**
     * Test that the happy path for makeInt() behaves as expected
     *
     * @return void
     */
    public function testSanitiseIntWithMinMax()
    {
        $this->assertEquals(
            5,
            sanitiseInt('5', 0, ['min' => 0]),
            'Numeric string (int 5) can be converted to an integer '.
            'and is greater than "min" (0)'
        );
        $this->assertEquals(
            0,
            sanitiseInt('3', 0, ['min' => 5]),
            'Numeric string (int 3) can be converted to an integer '.
            'but is less than "min" or equal to (5) so $_default '.
            '(0) is reutrned'
        );
        $this->assertEquals(
            0,
            sanitiseInt('9', 0, ['max' => 5]),
            'Numeric string (int 9) can be converted to an integer '.
            'but is greater than "max" (5) so $_default (0) is '.
            'reutrned'
        );
        $this->assertEquals(
            3,
            sanitiseInt('3', 0, ['min' => -5, 'max' => 5]),
            'Numeric string (int 3) can be converted to an integer '.
            'and is greater than or equal to "min" (-5) and is also '.
            'less than or equal to "max" (5) so input (3)is returned'
        );
        $this->assertEquals(
            0,
            sanitiseInt('3', 0, ['min' => 5, 'max' => 10]),
            'Numeric string (int 3) can be converted to an integer '.
            'and is less than or equal to "max" (10) but is also '.
            'less than "min" (5) so $_default (0) is returned'
        );
        $this->assertEquals(
            100,
            sanitiseInt('200', 100, ['min' => 50, 'max' => 150]),
            'Numeric string (int 200) can be converted to an '.
            'integer but is greater than "max" (150) so $_default '.
            '(100) is returned'
        );
    }

    /**
     * Test that the happy path for sanitiseInt() behaves as expected and
     * callers are warned of bad usage
     *
     * @return void
     */
    public function testSanitiseIntBadDefault1()
    {
        $this->expectError();
        // Optionally test that the message is equal to a string
        $this->expectErrorMessage(
            'Argument 2 passed to sanitiseInt() must be '.
            'of the type int'
        );
        sanitiseInt('95', 'abcd');
    }

    /**
     * Test that the sad path for sanitiseInt() behaves as expected and
     * callers are warned of bad usage
     *
     * @return void
     */
    public function testSanitiseIntBadMin()
    {
        $this->expectError();
        $this->expectErrorMessage(
            'sanitiseNumeric() expects `min` value to be an integer'
        );
        sanitiseInt('95', 0, ['min' => 'abc']);

        $this->expectError();
        $this->expectErrorMessage(
            'sanitiseNumeric() expects `max` value to be an integer'
        );
        sanitiseInt('95', 0, ['max' => 'abc']);
    }

    /**
     * Test that the sad path for sanitiseInt() behaves as expected and
     * callers are warned of bad usage
     *
     * @return void
     */
    public function testSanitiseIntBadMax()
    {
        $this->expectError();
        $this->expectErrorMessage(
            'sanitiseNumeric() expects `min` value to be an integer'
        );
        sanitiseInt('95', 0, ['min' => 'abc']);

        $this->expectError();
        $this->expectErrorMessage(
            'sanitiseNumeric() expects `max` value to be an integer'
        );
        sanitiseInt('95', 0, ['max' => 'abc']);
    }


    //  END:  sanitiseInt()
    // --------------------------------------------------------------
    // START: sanitiseNumeric()


    /**
     * Test that the happy path for sanitiseNumeric() behaves as expected
     *
     * @return void
     */
    public function testSanitiseNumeric1()
    {
        $input = '1.23';
        $eOut = 1.23;
        $aOut = sanitiseNumeric($input);

        $this->assertIsFloat(
            $aOut,
            'Numeric string (float '.$input.') can be converted '.
            'to a float'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Numeric string (float '.$input.') is correctly '.
            'converted to a float'
        );
    }

    /**
     * Test that the happy path for sanitiseNumeric() behaves as expected
     *
     * @return void
     */
    public function testSanitiseNumeric2()
    {
        $input = '1.987';
        $eOut = 1.987;
        $aOut = sanitiseNumeric($input);

        $this->assertIsFloat(
            $aOut,
            'Numeric string (float '.$input.') can be converted '.
            'to a float'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Numeric string (float '.$input.') is correctly '.
            'converted to a float'
        );
    }

    /**
     * Test that the happy path for sanitiseNumeric() behaves as expected
     *
     * @return void
     */
    public function testSanitiseNumeric3()
    {
        $input = '45';
        $eOut = 45;
        $aOut = sanitiseNumeric($input);

        $this->assertIsInt(
            $aOut,
            'Numeric string (int '.$input.') can be converted to an integer'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Numeric string (int '.$input.') is correctly converted to an '.
            'integer'
        );
    }

    /**
     * Test that the happy path for sanitiseNumeric() behaves as expected
     *
     * @return void
     */
    public function testSanitiseNumeric4()
    {
        $input = '-1.23';
        $eOut = -1.23;
        $aOut = sanitiseNumeric($input);

        $this->assertIsFloat(
            $aOut,
            'Numeric string (float '.$input.') can be converted to a float'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Numeric string (float '.$input.') is correctly converted to a '.
            'float'
        );
    }

    /**
     * Test that the happy path for sanitiseNumeric() behaves as expected
     *
     * @return void
     */
    public function testSanitiseNumeric5()
    {
        $input = '-45';
        $eOut = -45;
        $aOut = sanitiseNumeric($input);

        $this->assertIsInt(
            $aOut,
            'Numeric string (int '.$input.') can be converted to an integer'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Numeric string (int '.$input.') is correctly converted to an '.
            'integer'
        );
    }

    /**
     * Test that the happy path for sanitiseNumeric() behaves as expected
     *
     * @return void
     */
    public function testSanitiseNumeric6()
    {
        $input = ' asdg-1s.23st3 ';
        $eOut = -1.233;
        $aOut = sanitiseNumeric($input);

        $this->assertIsFloat(
            $aOut,
            'Non-numeric characters are stripped out. Remaining '.
            'characters are numeric so can be converted into an '.
            'float.'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Non-numeric characters are stripped out. Remaining '.
            'characters are numeric so can be converted into a '.
            'float.'
        );
    }

    /**
     * Test that the happy path for sanitiseNumeric() behaves as expected
     *
     * @return void
     */
    public function testSanitiseNumeric7()
    {
        $input = 'asdg1673';
        $eOut = 1673;
        $aOut = sanitiseNumeric($input);

        $this->assertIsInt(
            $aOut,
            'Non-numeric characters are stripped out. Remaining '.
            'characters are numeric so can be converted into an '.
            'integer.'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Non-numeric characters are stripped out. Remaining '.
            'characters are numeric so can be converted into an '.
            'integer.'
        );
    }

    /**
     * Test that the happy path for sanitiseNumeric() behaves as expected
     *
     * @return void
     */
    public function testSanitiseNumeric8()
    {
        $input = 'One two three';
        $eOut = 123;
        $aOut = sanitiseNumeric($input, $eOut);

        $this->assertIsInt(
            $aOut,
            'Non-numeric string cannot be converted to an integer so '.
            'custom default (123) is returned'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Non-numeric string cannot be converted to an integer '.
            'or float so custom default ('.$eOut.') is returned'
        );
    }

    /**
     * Test that the happy path for sanitiseNumeric() behaves as expected
     *
     * @return void
     */
    public function testSanitiseNumeric9()
    {
        $input = 'Not a number.';
        $eOut = 12.6;
        $aOut = sanitiseNumeric($input, $eOut);

        $this->assertIsFloat(
            $aOut,
            'Non-numeric string cannot be converted to an integer '.
            'or float so custom default ('.$eOut.') is returned'
        );
        $this->assertEquals(
            $eOut, $aOut,
            'Non-numeric string cannot be converted to an integer '.
            'or float so custom default ('.$eOut.') is returned'
        );
    }

    /**
     * Test that the happy path for sanitiseNumeric() behaves as
     * expected with min, max & precision
     *
     * @return void
     */
    public function testSanitiseNumericWithMinMax()
    {
        $this->assertEquals(
            5,
            sanitiseNumeric('5', 0, ['min' => 0]),
            'Numeric string (int 5) can be converted to an integer '.
            'and is greater than "min" (0)'
        );
        $this->assertEquals(
            5,
            sanitiseNumeric('5', 0, ['max' => 5]),
            'Numeric string (int 5) can be converted to an integer '.
            'and is less than or equal to "max" (5)'
        );
        $this->assertEquals(
            515.2,
            sanitiseNumeric('asd%^5sdf15.2', 0, ['min' => 0]),
            'Non-numeric characters are stripped out. Remaining '.
            'characters are numeric and can be converted to a '.
            'float and is greater than "min" (0)'
        );
        $this->assertEquals(
            0,
            sanitiseNumeric('asd-%^5sdf15.2', 0, ['min' => 0]),
            'Non-numeric characters are stripped out. Remaining '.
            'characters are numeric and can be converted to a '.
            'float but value is less than "min" (0) so $_default '.
            '(0) is returned'
        );
        $this->assertEquals(
            0,
            sanitiseNumeric('3.5', 0, ['min' => 5]),
            'Numeric string (float 3.5) can be converted to an integer '.
            'but is less than "min" or equal to (5) so $_default '.
            '(0) is reutrned'
        );
        $this->assertEquals(
            0,
            sanitiseNumeric('9', 0, ['max' => 5]),
            'Numeric string (int 9) can be converted to an integer '.
            'but is greater than "max" (5) so $_default (0) is '.
            'reutrned'
        );
        $this->assertEquals(
            3,
            sanitiseNumeric('3', 0, ['min' => -5, 'max' => 5]),
            'Numeric string (int 3) can be converted to an integer '.
            'and is greater than or equal to "min" (-5) and is also '.
            'less than or equal to "max" (5) so input (3)is returned'
        );
        $this->assertEquals(
            0,
            sanitiseNumeric('3', 0, ['min' => 5, 'max' => 10]),
            'Numeric string (int 3) can be converted to an integer '.
            'and is less than or equal to "max" (10) but is also '.
            'less than "min" (5) so $_default (0) is returned'
        );
        $this->assertEquals(
            100,
            sanitiseNumeric('200', 100, ['min' => 50, 'max' => 150]),
            'Numeric string (int 200) can be converted to an '.
            'integer but is greater than "max" (150) so $_default '.
            '(100) is returned'
        );
        $this->assertEquals(
            3.14,
            sanitiseNumeric('3.1415926', 0, ['precision' => 2]),
            'Numeric string (float Pi) can be converted to an '.
            'float but is rounded down to two decimal places'
        );
    }

    /**
     * Test that the sad path for sanitiseNumeric() behaves as expected and
     * callers are warned of bad usage
     *
     * @return void
     */
    public function testSanitiseNumericBadDefault1()
    {
        $this->expectError();
        $this->expectErrorMessage(
            'sanitiseNumeric() expects second param `$_default` to be '.
            'numeric'
        );
        sanitiseNumeric('95', 'abcd');
    }

    /**
     * Test that the sad path for sanitiseNumeric() behaves as expected and
     * callers are warned of bad usage
     *
     * @return void
     */
    public function testSanitiseNumericBadMin()
    {
        $this->expectError();
        $this->expectErrorMessage(
            'sanitiseNumeric() expects `min` value to be an integer'
        );
        sanitiseNumeric('95', 0, ['min' => 'abc']);
    }

    /**
     * Test that the sad path for sanitiseNumeric() behaves as expected and
     * callers are warned of bad usage
     *
     * @return void
     */
    public function testSanitiseNumericBadMax()
    {
        $this->expectError();
        $this->expectErrorMessage(
            'sanitiseNumeric() expects `max` value to be an integer'
        );
        sanitiseNumeric('95', 0, ['max' => 'abc']);
    }

    /**
     * Test that the sad path for sanitiseNumeric() behaves as expected and
     * callers are warned of bad usage
     *
     * @return void
     */
    public function testSanitiseNumericBadPrecision1()
    {
        $this->expectError();
        $this->expectErrorMessage(
            'sanitiseNumeric() expects `precision` value to be an '.
            'integer.'
        );
        sanitiseNumeric('95', 0, ['precision' => '5']);
    }

    /**
     * Test that the sad path for sanitiseNumeric() behaves as expected and
     * callers are warned of bad usage
     *
     * @return void
     */
    public function testSanitiseNumericBadPrecision2()
    {
        $this->expectError();
        $this->expectErrorMessage(
            'sanitiseNumeric() expects `precision` value to be an '.
            'integer.'
        );
        sanitiseNumeric('95', 0, ['precision' => 5.4]);
    }

    /**
     * Test that the sad path for sanitiseNumeric() behaves as expected and
     * callers are warned of bad usage
     *
     * @return void
     */
    public function testSanitiseNumericBadPrecision3()
    {
        $this->expectError();
        $this->expectErrorMessage(
            'sanitiseNumeric() expects `precision` value to be an '.
            'integer.'
        );
        sanitiseNumeric('95', 0, ['precision' => 'abcd']);
    }


    //  END:  sanitiseNumeric()
    // --------------------------------------------------------------


}
