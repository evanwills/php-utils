<?php

/**
 * This file contains a single class for sending emails with attachemnts.
 *
 * PHP Version 7.1
 *
 * @category SimpleMail
 * @package  SimpleMail
 * @author   Evan Wills <evan.i.wills@gmail.com>
 * @license  GPL2 <https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html>
 * @link     https://github.com/evanwills/php-utilsplif/
 */

/**
 * Simple email class that can also attach files.
 *
 * @category SimpleMail
 * @package  SimpleMail
 * @author   Evan Wills <evan.i.wills@gmail.com>
 * @license  GPL2 <https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html>
 * @link     https://github.com/evanwills/php-utils
 */
class SimpleMail
{
    /**
     * Email address of recipient
     *
     * @var string
     */
    private $_toAddress = '';
    /**
     * Subject line for email
     *
     * @var string
     */
    private $_subject = '';
    /**
     * Email headers
     *
     * @var string
     */
    private $_headers = '';
    /**
     * Email body
     *
     * @var string
     */
    private $_message = '';

    /**
     * String that divides parts of the email
     *
     * @var string
     */
    private $_mimeBoundary = '';

    /**
     * Sets up variables and mail email
     *
     * @param string $toAddress Email recipient
     * @param string $subject   Email subject
     * @param string $message   Email message text
     * @param string $headers   Extra headers
     *
     * @return void
     */
    function __construct(
        string $toAddress, string $subject,
        string $message, string $headers
    ) {
        $this->_toAddress = $toAddress;
        $this->_subject = $subject;
        $this->_headers = $headers;
        $semi_rand = md5(time());
        $this->_mimeBoundary = "==Multipart_Boundary_x{$semi_rand}x";
        $this->_headers .=
            "\nMIME-Version: 1.0\n" .
            "Content-Type: multipart/mixed;\n" .
            " boundary=\"{$this->_mimeBoundary}\"";
        $this->_message .=
            "This is a multi-part message in MIME format.\n\n" .
            "--{$this->_mimeBoundary}\n" .
            "Content-Type:text/html; charset=\"iso-8859-1\"\n" .
            "Content-Transfer-Encoding: 8bit\n\n" .
            $message .
            "\n\n";
    }

    /**
     * Add attachment to email
     *
     * @param string $fileatt_type    Attachemnt content type
     * @param string $fileatt_name    Attachment file name
     * @param mixed  $fileatt_content Contents of attachment
     *
     * @return void
     */
    function attach(string $fileatt_type, string $fileatt_name, $fileatt_content)
    {
        $data = chunk_split(base64_encode($fileatt_content));

        $this->_message .= "\n--{$this->_mimeBoundary}\n" .
                                "Content-Type: {$fileatt_type};\n" .
                                " name=\"{$fileatt_name}\"\n" .
                                "Content-Transfer-Encoding: base64\n\n" .
                                trim($data)."\n" .
                                "--{$this->_mimeBoundary}";

        // unset($file);
        unset($data, $fileatt, $fileatt_type, $fileatt_name);
    }

    /**
     * Send email
     *
     * @return void
     */
    function send() : bool
    {
        $this->_message .= "--\n";
        //echo('<pre>' . $this->_message . '</pre>');
        return mail(
            $this->_toAddress,
            $this->_subject,
            $this->_message,
            $this->_headers
        );
    }

    /**
     * Read file and add as attachment
     *
     * @param string $file File system path to attachment file
     * @param string $name Alternate name for file
     *
     * @return void
     */
    function file($file, $name = "")
    {
        $o = fopen($file, "rb");
        $content = fread($o, filesize($file));
        fclose($o);

        $_name = $name == ""
            ? basename($file)
            : $name;

        $type = "application/octet-stream";

        $this->attach($type, $_name, $content);
    }
}
