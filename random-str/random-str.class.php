<?php

/**
 * This file contains a singleton class that provides random string
 * for different purposes
 *
 * PHP version 7
 *
 * @category RandomStr
 * @package  RandomStr
 * @author   Evan Wills <evan.i.wills@gmail.com>
 * @license  GPL2 <https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html>
 * @link     https://github.com/evanwills/php-utils
 */

if (!defined('WORDS_FILE')) {
    $tmp = realpath(__DIR__.'/word-list.txt');
    if (is_file($tmp) && is_readable($tmp)) {
        define('WORDS_FILE', $tmp);
    } else {
        trigger_error(
            'No word list file has been supplied. '.
            'Cannot create anonymous strings without it.',
            E_USER_ERROR
        );
    }
} elseif (!is_string(WORDS_FILE) || !is_file(WORDS_FILE)
    || !is_readable(WORDS_FILE)
) {
    throw new Exception(
        'Constant `WORDS_FILE` must be a file system path to file '.
        'containing a source list of words to use as random strings'
    );
}


/**
 * This is a singleton class that provides random string for
 * different purposes
 *
 * @category RandomStr
 * @package  RandomStr
 * @author   Evan Wills <evan.i.wills@gmail.com>
 * @license  GPL2 <https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html>
 * @link     https://github.com/evanwills/php-utils
 */
class RandomStr
{
    const HOME = 'Australia';
    static private $_me = null;

    private $_words = [];
    private $_names = [];
    private $_titles = [
        'n/a',  'Mr', 'Ms', 'Mx', 'Dr', 'Hon', 'Jr', 'Most Rev', 'Miss',
        'Mrs', 'Prof', 'Assoc Prof', 'Em Prof', 'Sir', 'Sr', 'Sr Dr',
        'Sr Prof', 'Rev', 'Rev Dr', 'Rev Prof', 'Rt Hon', 'Very Rev'
    ];
    private $_streets = [
        'Ally', 'Arc', 'Ave', 'Cct', 'Cl', 'Crn', 'Ct', 'Cres', 'Cds',
        'Dr', 'Esp', 'Grn', 'Gr', 'Hwy', 'Jnc', 'Lane', 'Link', 'Mews',
        'Pde', 'Pl', 'Rdge', 'Rd', 'Sq', 'St', 'Tce'
    ];
    private $_states = ['ACT', 'NSW', 'NT', 'Qld', 'SA', 'Taz', 'Vic', 'WA'];
    private $_countries = [
        'Afghanistan', 'Albania', 'Algeria', 'Andorra', 'Angola',
        'Antigua and Barbuda', 'Argentina', 'Armenia', 'Artsakh',
        'Australia', 'Austria', 'Azerbaijan', 'Bahamas, The', 'Bahrain',
        'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin',
        'Bhutan', 'Bolivia', 'Bosnia and Herzegovina', 'Botswana',
        'Brazil', 'Brunei', 'Bulgaria', 'Burkina Faso', 'Burma', 'Burundi',
        'Cambodia', 'Cameroon', 'Canada', 'Cape Verde',
        'Central African Republic', 'Chad', 'Chile', 'China', 'Colombia',
        'Comoros', 'Congo, Democratic Republic of the',
        'Congo, Republic of the', 'Cook Islands', 'Costa Rica',
        'Cote d\'Ivoire', 'Croatia', 'Cuba', 'Cyprus', 'Czech Republic[r]',
        'Democratic People\'s Republic of Korea',
        'Democratic Republic of the Congo', 'Denmark', 'Djibouti',
        'Dominica', 'Dominican Republic', 'East Timor', 'Ecuador', 'Egypt',
        'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia',
        'Eswatini', 'Ethiopia', 'Fiji', 'Finland', 'France', 'Gabon',
        'Gambia, The', 'Georgia', 'Germany', 'Ghana', 'Greece', 'Grenada',
        'Guatemala', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti',
        'Holy See', 'Honduras', 'Hungary', 'Iceland[y]', 'India',
        'Indonesia', 'Iran', 'Iraq', 'Ireland[z]', 'Israel', 'Italy',
        'Ivory Coast', 'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya',
        'Kiribati', 'Korea, North', 'Korea, South', 'Kosovo', 'Kuwait',
        'Kyrgyzstan', 'Laos', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia',
        'Libya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macedonia',
        'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta',
        'Marshall Islands', 'Mauritania', 'Mauritius', 'Mexico',
        'Micronesia', 'Moldova', 'Monaco', 'Mongolia', 'Montenegro',
        'Morocco', 'Mozambique', 'Myanmar', 'Nagorno-Karabakh', 'Namibia',
        'Nauru', 'Nepal', 'Netherlands', 'New Zealand', 'Nicaragua',
        'Niger', 'Nigeria', 'Niue', 'North Korea', 'North Macedonia',
        'Northern Cyprus', 'Norway', 'Oman', 'Pakistan', 'Palau',
        'Palestine', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru',
        'Philippines', 'Poland', 'Portugal', 'Pridnestrovie', 'Qatar',
        'Republic of Korea', 'Republic of the Congo', 'Romania', 'Russia',
        'Rwanda', 'Sahrawi Arab Democratic Republic',
        'Saint Kitts and Nevis', 'Saint Lucia',
        'Saint Vincent and the Grenadines', 'Samoa', 'San Marino',
        'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Serbia',
        'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia',
        'Solomon Islands', 'Somalia', 'Somaliland', 'South Africa',
        'South Korea', 'South Ossetia', 'South Sudan', 'Spain', 'Sri Lanka',
        'Sudan', 'Sudan, South', 'Suriname', 'Swaziland', 'Sweden',
        'Switzerland', 'Syria', 'Taiwan', 'Tajikistan', 'Tanzania',
        'Thailand', 'The Bahamas', 'The Gambia', 'Timor-Leste', 'Togo',
        'Tonga', 'Transnistria', 'Trinidad and Tobago', 'Tunisia',
        'Turkey', 'Turkmenistan', 'Tuvalu', 'Uganda', 'Ukraine',
        'United Arab Emirates', 'United Kingdom', 'United States',
        'Uruguay', 'Uzbekistan', 'Vanuatu', 'Vatican City', 'Venezuela',
        'Vietnam', 'Yemen', 'Zambia', 'Zimbabwe'
    ];
    private $_tld = ['com', 'co', 'net', 'org', 'edu', 'gov'];
    private $_alpha = [
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l',
        'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'w', 'x', 'y',
        'z'
    ];
    private $_c = [
        'words' => 0, 'names' => 0, 'states' => 8, 'titles' => 22,
        'streets' => 25, 'countries' => 222, 'alpha' => 26,
        'tld' => 5
    ];

    /**
     * If necessary, do initial setup of words and names lists
     */
    private function __construct()
    {
        if (empty($this->_words) || empty($this->_names)) {
            $_words = fopen(WORDS_FILE, 'r');

            while ($word = fgets($_words)) {
                $word = trim($word);
                if (preg_match('`^[A-Z]`', $word)) {
                    $this->_names[] = $word;
                } else {
                    $this->_words[] = $word;
                }
            }

            $this->_c['words'] = (count($this->_words) - 1);
            $this->_c['names'] = (count($this->_names) - 1);
            $this->_c['countries'] = (count($this->_countries) - 1);
            $this->_c['titles'] = (count($this->_titles) - 1);
            $this->_c['streets'] = (count($this->_streets) - 1);
            $this->_c['states'] = (count($this->_states) - 1);
            $this->_c['tld'] = (count($this->_tld) - 1);
            $this->_c['alpha'] = (count($this->_alpha) - 1);
        }
    }

    /**
     * Factory method to get a RandomStr object.
     *
     * > __NOTE:__ RandomStr is a *"singleton"* class
     *
     * @return self
     */
    static public function get() : self
    {
        if (self::$_me === null) {
            self::$_me = new self();
        }

        return self::$_me;
    }

    /**
     * Get a random string from an array of strings
     *
     * @param string $type Type of string to return. Options are:
     *                     * `"word"`    = Non-noun word (default)
     *                     * `"name`     = Noun
     *                     * `"street"`  = Street name suffix (e.g. "*St*")
     *                     * `"state"`   = Australian State Abbreviation (e.g. "*NSW*")
     *                     * `"country"` = Name of one of the 222 United Nations recognised
     *                     [sovereign states](https://en.wikipedia.org/wiki/List_of_sovereign_states)
     *                     * `"tld"`     = Top level domain,
     *                     * `"title"`   = Honorific title abbreviation (e.g. "*Ms*" or "*Mr*")
     *                     * `"alpha"`   = Single (ASCII) alphabetical character
     *
     * @return string random string
     */
    public function getStr(string $type = 'words') : string
    {
        if (!is_string($type)) {
            throw new Exception(
                'RandomStr::getStr() expects only parameter '.
                '$type to be a string. Found: '.gettype($type)
            );
        }
        $type = strtolower($type);

        switch($type) {
        case 'name':
            $type = 'names';
            break;

        case 'street':
            $type = 'streets';
            break;

        case 'title':
            $type = 'titles';
            break;

        case 'country':
        case 'countrys':
            $type = 'countries';
            break;

        case 'tlds':
        case 'topdomain':
        case 'top-domain':
            $type = 'tld';
            break;

        case 'state':
            $type = 'states';
            break;

        case '':
            $type = 'words';
            break;
        }

        if (!array_key_exists($type, $this->_c)) {
            throw new Exception(
                'RandomStr::getStr() expects only parameter '.
                '$type to be a string matching one of the '.
                'following:. "'.
                implode('", "', array_keys($this->_c)).'"'
            );
        }

        $_type = '_'.$type;
        $i = random_int(0, $this->_c[$type]);

        return self::$$_type[$i];
    }

    /**
     * Get a random string from an array of strings
     *
     * @param string[] $strings Whether or not to return names or
     *                          normal words
     *
     * @return string random string
     * @throws Exception if supplied list or
     */
    public function getCustomStr(array $strings) : string
    {
        if (is_array($strings) && !empty($strings)) {
            $i = random_int(0, (count($strings) - 1));

            if (!array_key_exists($i, $strings) || !is_string($strings[$i])) {
                throw new Exception(
                    'RandomStr::getCustomStr() expects only '.
                    'parameter $strings to be an indexed array of '.
                    'strings. Item #'.$i.' could not be found or '.
                    'is not a string'
                );
            }

            return $strings[$i];
        } else {
            throw new Exception(
                'RandomStr::getCustomStr() expects only parameter '.
                '$strings to be an array of strings. Found: '.
                gettype($strings)
            );
        }
    }

    /**
     * Get a random street address
     *
     * @return string
     */
    public function getAddress() : string
    {
        return  random_int(0, 999).' '.
                ucfirst($this->getStr('words')).' '.
                $this->getStr('streets');
    }

    /**
     * Get a random country.
     *
     * > __Note:__ Given ACU's location and student population, the
     * >           output of this method is skewed towards returning
     * >           "Australia" as the random contry.
     *
     * @param integer $skew Out of 10, how often should "Australia"
     *                      be returned instead of a random country
     *                      name (which could also be "Australia").
     *
     * @return string
     */
    public function getCountry(int $skew = 5) : string
    {
        if (random_int(0, 10) <= $skew) {
            return self::HOME;
        }
        $i = random_int(0, $this->_c['countries']);

        return $this->_countries[$i];
    }

    /**
     * Generate a random web domain
     *
     * @param integer $skew Out of 10, how often should the returned
     *                      domain omit the country suffix
     *
     * @return string reasonable looking random email domain
     */
    public function getDomain(int $skew = 8) : string
    {
        $output = $this->getStr().'.'.$this->getStr('tld');
        if (random_int(0, 10) <= $skew) {
            $output .= '.'.$this->getStr('alpha').
                       $this->getStr('alpha');
        }

        return $output;
    }

    /**
     * Get random email address
     *
     * @return string randomly generated email address
     */
    public function getEmail() : string
    {
        $seps = ['.', '_', '-'];
        $sep = $seps[random_int(0, 2)];

        return $this->getStr('name').$sep.$this->getStr('name').
               '@'.$this->getDomain();
    }

    /**
     * Get an phone number with an country prefix
     *
     * @param integer $maxDigits Maximum number digits for phone
     *                           number (excluding country code part
     *                           of number)
     * @param integer $minDigits Minimum number digits for phone
     *                           number (excluding country code part
     *                           of number)
     *                           > __Note:__ If `$minDigits` is greater
     *                           > than `$maxDigits` `$minDigits` is
     *                           > forced to be the same as `$maxDigits`
     *
     * @return string Random international format phone number
     */
    public function getIntPhone(int $maxDigits = 9, int $minDigits = 8) : string
    {
        if ($minDigits > $maxDigits) {
            $minDigits = $maxDigits;
        }

        $digits = random_int($minDigits, $maxDigits);

        $max = pow(10, $digits);
        $min = $max * 0.1;
        $max -= 1;
        $number = random_int($min, $max);
        return '+'.random_int(10, 99).str_pad($number, $digits, '0', STR_PAD_LEFT);
    }

    /**
     * Get an Australian mobile or fixed line phone number
     *
     * @param boolean $mobile Whether or not phone number should be
     *                        Mobile
     *
     * @return string
     */
    public function getPhone(bool $mobile = true) : string
    {
        $codes = ['02', '03', '04', '07', '08'];
        $prefix = ($mobile === false)
            ? $codes[random_int(0, 5)]
            : '04';
        return $prefix.str_pad(random_int(10000, 999999), 8, '0', STR_PAD_LEFT);
    }

    /**
     * Get Australian postcode
     *
     * @return string
     */
    public function getPostCode() : string
    {
        return str_pad(random_int(800, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get honorific title
     *
     * @param boolean $common Whether or not to limit options to the
     *                        5 most common titles
     *
     * @return string String to use as an honorific title
     */
    public function getTitle(bool $common = false) : string
    {
        $max = ($common === true)
            ? 4
            : $this->_c['titles'];

        return $this->_titles[random_int(0, $max)];
    }

    /**
     * Get a number of random words
     *
     * @param integer $max Maximum number of words to be returned
     * @param integer $min Minimum number of words to be returned
     *                     > __Note:__ if $min is omitted, exactly
     *                     >           $max words will be returned.
     *                     >           Otherwise a random number of
     *                     >           words between $min and $max
     *                     >           (inclusive) will be returned
     *
     * @return string
     */
    public function getWords(int $max = 5, int $min = -1) : string
    {
        if ($min < 0) {
            $count = $max;
        } else {
            if ($min > $max) {
                $tmp = $min;
                $min = $max;
                $max = $tmp;
                unset($tmp);
            }

            $count = random_int($min, $max);
        }

        $output = '';
        $sep = '';
        for ($a = 0; $a < $count; $a += 1) {
            $output .= $sep.$this->getStr();
            $sep = ' ';
        }

        return $output;
    }
}
