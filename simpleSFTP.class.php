<?php

/**
 * This file contains a single class to make it easy to move files
 * between servers, delete files from remote servers and create new
 * directories on remote servers
 *
 * PHP Version 7.2
 *
 * @category SFTP
 * @package  SFTP
 * @author   Evan Wills <evan.i.wills@gmail.com>
 * @license  GPL2 <https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html>
 * @link     https://github.com/evanwills/php-utils
 */


/**
 * SimpleSFTP aims make it easy to move files between servers,
 * delete files from remote servers and create new directories
 * on remote servers
 *
 * @category SFTP
 * @package  SFTP
 * @author   Evan Wills <evan.i.wills@gmail.com>
 * @license  GPL2 <https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html>
 * @link     https://github.com/evanwills/php-utils
 */
class SimpleSFTP
{
    private $_connection = false;
    public $sftpConnection = false;
    public $last_error = "";

    private $_sshFuncs = [
        'ssh2_connect', 'ssh2_auth_password', 'ssh2_auth_pubkey_file',
        'ssh2_sftp', 'ssh2_disconnect', 'ssh2_scp_send',
        'ssh2_scp_recv', 'ssh2_sftp_unlink', 'ssh2_sftp_unlink'
    ];

    /**
     * Initialise an SSH/SFTP connection to a remote server
     *
     * @param string $host       Host name (or IP address) for server
     *                           to connect to
     * @param string $user       Username for authenticating once
     *                           connected to the server
     * @param string $password   Password for authenticating once
     *                           connected to the server
     * @param string $publicKey  Path to public key file for SSH key
     *                           authentication
     * @param string $privateKey Path to public key file for SSH key
     *                           authentication
     */
    function __construct(
        string $host, string $user, string $password,
        $publicKey = false, $privateKey = false
    ) {
        // Check that we have SSH functions on the server
        for ($a = 0; $a < count($this->_sshFuncs); $a += 1) {
            if (!function_exists($this->_sshFuncs[$a])) {
                throw new Exception(
                    'SimpleSFTP requires all of the following '.
                    'functions to be installed: '.
                    implode('(), ', $this->_sshFuncs).' '.
                    $this->_sshFuncs[$a].'() does not exist on '.
                    'this server'
                );
            }
        }

        if (!is_string($host) || empty(trim($host))) {
            throw new Exception(
                'SimpleSFTP expects first parameter $host to be '.
                'a non-empty string'
            );
        }

        if (!is_string($user) || empty(trim($user))) {
            throw new Exception(
                'SimpleSFTP expects second parameter $user to '.
                'be a non-empty string'
            );
        }

        $this->_connection = ssh2_connect($host, 22);

        if (is_resource($this->_connection)) {
            $ok = false;

            if ($publicKey === false) {
                if (!is_string($password) && empty(trim($password))) {
                    // This is a coding error so we'll throw an exception
                    throw new Exception(
                        'SimpleSFTP expects third parameter '.
                        '$password to be non-empty string when '.
                        'connecting using username & password'
                    );
                } else {
                    $loginResult = ssh2_auth_password(
                        $this->_connection, $user, $password
                    );

                    if (!$loginResult) {
                        $this->last_error = 'Username/password '.
                                            'authentication failed '.
                                            "($user@$host)";
                    } else {
                        $ok = true;
                    }
                }
            } else {
                if (!is_string($publicKey) || !is_file($publicKey)
                    || !is_string($privateKey) || !is_file($privateKey)
                ) {
                    // This is a coding error so we'll throw an exception
                    throw new Exception(
                        'SimpleSFTP expects both $publicKey and '.
                        '$privateKey to be strings pointing to SSH '.
                        'public & private key files'
                    );
                } else {
                    $password = (is_string($password))
                        ? $password
                        : '';
                    $loginResult = ssh2_auth_pubkey_file(
                        $this->_connection, $user,
                        $publicKey, $privateKey, $password
                    );

                    if (!$loginResult) {
                        $this->last_error = 'SSH Key '.
                                            'authentication failed '.
                                            "($user@$host)";
                    } else {
                        $ok = true;
                    }
                }

            }

            if ($ok === true) {
                $this->sftpConnection = ssh2_sftp($this->_connection);

                if (!$this->sftpConnection) {
                    $this->last_error = "SFTP connection failed";
                }
            }
        } else {
            $this->last_error = "SSH connection refused";
        }
    }

    /**
     * Close SFTP connection
     *
     * @return void
     */
    public function disconnect()
    {
        if (is_resource($this->_connection)) {
            $this->sftpConnection = false;
            $this->_connection = ssh2_disconnect($this->_connection);
        }
    }

    /**
     * Upload a single file from the local file system to the
     * remote file system
     *
     * @param string  $localPath  Path to file in local file system
     * @param string  $remotePath Remote file system path to place
     *                            the file
     * @param integer $mode       Unix file permissions value
     *
     * @return boolean TRUE if file was successfully sent.
     *                 FALSE otherwise
     */
    public function upload(
        string $localPath, string $remotePath, int $mode = 0664
    ) : bool {
        if ($this->sftpConnection === false) {
            throw new Exception(
                'SimpleSFTP\'s has no SFTP connection! Either the '.
                'connetion has already been closed or no '.
                'connection was made'
            );
        }
        if (!is_string($localPath) || !is_file($localPath)
            || !is_readable($localPath)
        ) {
            throw new Exception(
                'SimpleSFTP::upload() expects first parameter '.
                '$localPath to be a string pointing to a file '.
                'in the local file system.'
            );
        }
        if (!is_string($remotePath) || !empty(trim($remotePath))) {
            throw new Exception(
                'SimpleSFTP::upload() expects second parameter '.
                '$remotePath to be a string pointing to a file '.
                'in the local file system.'
            );
        }

        return ssh2_scp_send(
            $this->_connection, $localPath, $remotePath, $mode
        );
    }

    /**
     * Download a single file from the local file system to the
     * remote file system
     *
     * @param string $remotePath Remote file system path to place
     *                           the file
     * @param string $localPath  Path to file in local file system
     *
     * @return boolean TRUE if file was successfully received.
     *                 FALSE otherwise
     */
    public function download(string $remotePath, string $localPath) : bool
    {
        if ($this->sftpConnection === false) {
            throw new Exception(
                'SimpleSFTP\'s has no SFTP connection! Either the '.
                'connetion has already been closed or no '.
                'connection was made'
            );
        }
        if (!is_string($remotePath) || !empty(trim($remotePath))) {
            throw new Exception(
                'SimpleSFTP::download() expects first parameter '.
                '$remotePath to be a string pointing to a file '.
                'in the local file system.'
            );
        }
        if (!is_string($localPath) || !is_writable($localPath)) {
            throw new Exception(
                'SimpleSFTP::download() expects second parameter '.
                '$localPath to be a string pointing to a writable '.
                'file/directory in the local file system.'
            );
        }

        return ssh2_scp_recv(
            $this->_connection, $remotePath, $localPath
        );
    }

    /**
     * Delete a single file from the remote file system
     *
     * @param string $remotePath Remote file system path to place
     *                           the file
     *
     * @return boolean TRUE if file was successfully deleted.
     *                 FALSE otherwise
     */
    public function delete(string $remotePath) : bool
    {
        if ($this->sftpConnection === false) {
            throw new Exception(
                'SimpleSFTP\'s has no SFTP connection! Either the '.
                'connetion has already been closed or no '.
                'connection was made'
            );
        }
        if (!is_string($remotePath) || !empty(trim($remotePath))) {
            throw new Exception(
                'SimpleSFTP::download() expects first parameter '.
                '$remotePath to be a string pointing to a file '.
                'in the local file system.'
            );
        }

        return ssh2_sftp_unlink(
            $this->_connection, $remotePath
        );
    }

    /**
     * Delete a single file from the remote file system
     *
     * @param string  $remotePath Remote file system path to place
     *                            the file
     * @param integer $mode       Unix file permissions value
     *
     * @return boolean TRUE if file was successfully deleted.
     *                 FALSE otherwise
     */
    public function mkdir(string $remotePath, int $mode  = 0664) : bool
    {
        if ($this->sftpConnection === false) {
            throw new Exception(
                'SimpleSFTP\'s has no SFTP connection! Either the '.
                'connetion has already been closed or no '.
                'connection was made'
            );
        }
        if (!is_string($remotePath) || !empty(trim($remotePath))) {
            throw new Exception(
                'SimpleSFTP::download() expects first parameter '.
                '$remotePath to be a string pointing to a file '.
                'in the local file system.'
            );
        }

        return ssh2_sftp_unlink(
            $this->_connection, $remotePath
        );
    }
}
