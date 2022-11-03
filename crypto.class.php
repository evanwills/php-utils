<?php

/**
 * This file handles encrypting and decrypting strings and storing
 * NONCEs in the database
 *
 * > NOTE: It is expected that the supplied database contains a table
 * >       with the following structure:
 * >
 * > ```sql
 * > CREATE TABLE [table_name] (
 * >     `nonce_id` int(11) NOT NULL AUTO_INCREMENT,
 * >     `nonce_str` char(32) NOT NULL,
 * >     `nonce_single` tinyint(1) NOT NULL DEFAULT 1,
 * >     `nonce_created` timestamp NOT NULL DEFAULT current_timestamp(),
 * >     PRIMARY KEY (`nonce_id`),
 * >     UNIQUE KEY `UNI_nonce_str` (`nonce_str`)
 * > ) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb3
 * > ```
 *
 * PHP version 7
 *
 * @category Crypto
 * @package  Crypto
 * @author   Evan Wills <evan.i.wills@gmail.com>
 * @license  GPL2 <https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html>
 * @link     https://github.com/evanwills/php-utils
 */

/**
 * This class is used for encrypting and decrypting text messages.
 *
 * It requires a database connection to a database with a nonce list
 * table.
 *
 * @category Crypto
 * @package  Crypto
 * @author   Evan Wills <evan.i.wills@gmail.com>
 * @license  GPL2 <https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html>
 * @link     https://github.com/evanwills/php-utils
 */
class Crypto
{
    /**
     * Encryption key
     *
     * @var string
     */
    private $_key = '';

    /**
     * Database connection object
     *
     * @var EnhancedPDO
     */
    private $_db = null;

    /**
     * Name of nonce listing table;
     *
     * @var string
     */
    private $_tableName = '';


    /**
     * Sets encryption key & DB connector and nonce listing table name
     *
     * @param string      $key       Encryption key for whole
     *                               application
     * @param EnhancedPDO $db        Database connection object
     * @param string      $tableName Name of nonce listing table
     *
     * @return void
     */
    public function __construct(
        string $key, EnhancedPDO $db, string $tableName = 'nonce_list'
    ) {
        if (NEW_RELIC) { newrelic_add_custom_tracer('Crypto::__construct'); } // phpcs:ignore

        $this->_db = $db;
        $this->_key = $key;
        $this->_tableName = $tableName;

        $stmt = $this->_db->prepBindExec(
            "SELECT `nonce_id`
             FROM   `$tableName`
             LIMIT 0, 1"
        );
        $data = $stmt->errorInfo();
        if ($data[0] !== '00000') {
            // Something is wrong with the database's setup
            throw new Exception($data[2]);
        }
    }

    /**
     * Encrypt a message
     *
     * @param string  $msg       Message to be encrypted
     * @param string  $auth      Extra authentication for message
     * @param boolean $singleUse Whether or not this message's nonce
     *                           should be deleted after the first
     *                           use
     *
     * @return array containing nonce ID and encrypted message
     * @throws Exception if nonce couldn't be written to the DB
     */
    public function encrypt(
        string $msg, string $auth = '', bool $singleUse = true
    ) : array {
        if (NEW_RELIC) { newrelic_add_custom_tracer('Crypto::encrypt'); } // phpcs:ignore

        // Get random Nonce to protect encryption from replay attack
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);

        // Store nonce in DB so we can use it to decrypt later
        $stmt = $this->_db->prepare(
            "INSERT INTO `{$this->_tableName}` (
                `nonce_str`,
                `nonce_single`
            ) VALUES (
                :NONCE,
                :SINGLE
            )"
        );
        $stmt->bindParam(':NONCE', $nonce, PDO::PARAM_STR);
        $stmt->bindParam(':SINGLE', $singleUse, PDO::PARAM_BOOL);
        $stmt->execute();

        if ($stmt->rowCount() !== 1) {
            throw new Exception(
                'Could not insert new entry into nonce listing table'
            );
        }

        $output = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            $msg, $auth, $nonce, $this->_key
        );

        return [
            'id' => ($this->_db->getLastID() * 1),
            'msg' => bin2hex($output)
        ];
    }

    /**
     * Decrypt message
     *
     * @param string  $msg     Encrypted message
     * @param integer $nonceID nonce listing table ID of nonce
     * @param string  $auth    Extra authentication for message
     *
     * @return string|false Decrypted message if decryption was
     *                      successful. FALSE otherwise
     */
    public function decrypt(string $msg, int $nonceID, string $auth = '')
    {
        if (NEW_RELIC) { newrelic_add_custom_tracer('Crypto::decrypt'); } // phpcs:ignore

        // Get the nonce used to encrypt supplied message
        $stmt = $this->_db->prepBindExec(
            "SELECT `nonce_id` AS `id`,
                    `nonce_str` AS `nonce`,
                    `nonce_single` AS `singleUse`
             FROM   `{$this->_tableName}`
             WHERE  `nonce_id` = :ID",
            $nonceID
        );
        $data = $stmt->fetch(PDO::FETCH_OBJ);

        if ($data === false) {
            // Could not find nonce to use in decryption
            return false;
        }

        $output = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            $msg, $auth, $data->nonce, $this->_key
        );

        if ($data->singleUse == true) {
            // This uses a single use nonce so delete it
            $this->_db->prepBindExec(
                "DELETE FROM `{$this->_tableName}`
                 WHERE  `nonce_id` = :ID",
                $data->id
            );
        }

        return $output;
    }

    /**
     * This generates a security key for symetric encryption
     *
     * > __Note:__ This method should only be called once per
     * >           installation. If multiple installations share the
     * >           same database, the key this method generates
     * >           should be added to all installations that share
     * >           the same DB otherwise decryption will not be
     * >           possible
     *
     * @param string $fileDir  File system path to directory where
     *                         security key file can be written
     * @param string $fileName Name of the security key file
     *
     * @return boolean TRUE if security key file was written to
     *                 file system. FALSE otherwise
     * @throws Exception if
     */
    static public function generateKey(
        string $fileDir, string $fileName
    ) : bool {
        if (NEW_RELIC) { newrelic_add_custom_tracer('Crypto::generateKey'); } // phpcs:ignore

        if ($fileDir !== '' || !is_dir($fileDir)
            || !is_writable($fileDir)
        ) {
            throw new Exception(
                'Crypto::generateKey() expects first parameter '.
                '$fileDir to be a file system path to a writable '.
                'directory'
            );
        }
        $filePath = $fileDir.$fileName;
        if (is_file($filePath) && !is_writable($filePath)) {
            throw new Exception(
                'Crypto::generateKey() expects second parameter '.
                '$fileName to be a new file name in "'.$fileDir.'" '.
                'or a file system path to a writable file'
            );
        }

        return file_put_contents(
            $filePath, sodium_crypto_aead_xchacha20poly1305_ietf_keygen()
        );
    }
}

/**
 * Light weight singleton wrapper for Crypto so it can be called
 * anywhere within the code without the need for the Crypto object
 * to be passed in.
 *
 * Implements encrypt() and decrypt as static methods with the same
 * call signature as in Crypto.
 *
 * @category Crypto
 * @package  Crypto
 * @author   Evan Wills <evan.i.wills@gmail.com>
 * @license  GPL2 <https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html>
 * @link     https://github.com/evanwills/php-utils
 */
class CryptoNow
{
    const NO_INIT = 'CryptoNow has not been initialised yet';
    /**
     * Crypto object to use as singleton
     *
     * @var Crypto
     */
    static private $_crypto = null;

    /**
     * Encrypt a message
     *
     * @param string  $msg       Message to be encrypted
     * @param string  $auth      Extra authentication for message
     * @param boolean $singleUse Whether or not this message's nonce
     *                           should be deleted after the first
     *                           use
     *
     * @return array
     */
    static public function encrypt(
        string $msg, string $auth = '', bool $singleUse = true
    ) : array {
        if (NEW_RELIC) { newrelic_add_custom_tracer('CryptoNow::encrypt'); } // phpcs:ignore

        if (self::$_crypto === null) {
            throw new Exception(self::NO_INIT);
        }

        return self::$_crypto->encrypt($msg, $auth, $singleUse);
    }

    /**
     * Decrypt message
     *
     * @param string  $msg     Encrypted message
     * @param integer $nonceID nonce listing table ID of nonce
     * @param string  $auth    Extra authentication for message
     *
     * @return string|false Decrypted message if decryption was
     *                      successful. FALSE otherwise
     */
    static public function decrypt(
        string $msg, int $nonceID, string $auth = ''
    ) {
        if (NEW_RELIC) { newrelic_add_custom_tracer('CryptoNow::decrypt'); } // phpcs:ignore

        if (self::$_crypto === null) {
            throw new Exception(self::NO_INIT);
        }

        return self::$_crypto->decrypt($msg,  $nonceID, $auth);
    }

    /**
     * Initialise CryptoNow
     *
     * @param Crypto $crypto Crypto object to use as singleton
     *
     * @return boolean TRUE if initialisation was successful.
     *                 FALSE otherwise.
     */
    static public function init(Crypto $crypto) : bool
    {
        if (NEW_RELIC) { newrelic_add_custom_tracer('CryptoNow::init'); } // phpcs:ignore

        if (self::$_crypto === null) {
            self::$_crypto = $crypto;
            return true;
        }

        return false;
    }
}
