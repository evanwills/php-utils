<?php


/**
 * This file provides a class that adds extra functionalty to PHP's
 * PDO classes. It doesn't extend PDO but instead is a wrapper for
 * PDO with some methods for doing very common DB stuff
 *
 * PHP version 7.4
 *
 * @category EnancedPDO
 * @package  EnancedPDO
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
 * @category EnancedPDO
 * @package  EnancedPDO
 * @author   Evan Wills <evan.i.wills@gmail.com>
 * @license  GPL2 <https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html>
 * @link     https://github.com/evanwills/php-utils
 */
class EnhancedPDO
{
    /**
     * PDO database connection object
     *
     * @var ?PDO
     */
    private $_db = null;

    /**
     * Host name/IP address of server the database is on
     *
     * @var string
     */
    private $_dbHost = '';

    /**
     * Name of the database the PDO object was instantiated with
     *
     * @var string
     */
    private $_dbName = '';

    /**
     * Type of the database being connected to
     *
     * @var string
     */
    private $_dbType = '';

    /**
     * List of SQL Server response codes that indicate a query was
     * successfully executed
     *
     * > NOTE: Different DB engines may have different error/success
     * >       codes. As I find them, they'll be added here
     *
     * @var string[]
     */
    private $_successCodes = ['00000'];


    /**
     * Constructor for DB base class
     *
     * @param string  $dsn      The Data Source Name, or DSN,
     *                          contains the information required to
     *                          connect to the database.
     * @param ?string $username The user name for the DSN string.
     *                          This parameter is optional for some
     *                          PDO drivers.
     * @param ?string $password The password for the DSN string.
     *                          This parameter is optional for some
     *                          PDO drivers.
     * @param ?array  $options  A key => value array of driver
     *                          specific connection options.
     */
    public function __construct(
        string $dsn,
        ?string $username = null,
        ?string $password = null,
        ?array $options = null
    ) {
        if (NEW_RELIC) { newrelic_add_custom_tracer('EnhancedPDO::__construct'); } // phpcs:ignore

        try {
            $this->_db = new PDO(
                $dsn,
                (is_string($username)) ? $username : null,
                (is_string($password)) ? $password : null,
                (is_array($options)) ? $options : null
            );
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        $this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (preg_match('/;host=(.*?)(?=;|$)/i', $dsn, $matches)) {
            $this->_dbHost = $matches[1];
        }

        if (preg_match('/;dbname=(.*?)(?=;|$)/i', $dsn, $matches)) {
            $this->_dbName = $matches[1];
        }

        if (preg_match('/^([^:]+):.*$/i', $dsn, $matches)) {
            $this->_dbType = $matches[1];
        }
    }

    /**
     * Get the primary database connection object
     *
     * @return PDO
     */
    public function getDB() : PDO
    {
        if (NEW_RELIC) { newrelic_add_custom_tracer('EnhancedPDO::getDB'); } // phpcs:ignore

        return $this->_db;
    }

    /**
     * Get the host name/IP address of server the database is on
     *
     * @return string
     */
    public function getDbHost() : string
    {
        if (NEW_RELIC) { newrelic_add_custom_tracer('EnhancedPDO::getDbHost'); } // phpcs:ignore

        return $this->_dbHost;
    }

    /**
     * Get the name of the database specified in the Database
     * connection string.
     *
     * @return string
     */
    public function getDbName() : string
    {
        if (NEW_RELIC) { newrelic_add_custom_tracer('EnhancedPDO::getDbName'); } // phpcs:ignore

        return $this->_dbName;
    }

    /**
     * Get the type of the database PDO is connected to
     *
     * @return string
     */
    public function getDbType() : string
    {
        if (NEW_RELIC) { newrelic_add_custom_tracer('EnhancedPDO::getDbType'); } // phpcs:ignore

        return $this->_dbType;
    }

    /**
     * Get a prepared PDO statement
     *
     * @param string               $sql SQL statement
     *                                  (Must be non-empty string)
     * @param PDO|EnhancedPDO|null $db  Override DB connection
     *
     * @return PDOStatement|false
     */
    public function prepare(string $sql, $db = null)
    {
        if (NEW_RELIC) { newrelic_add_custom_tracer('EnhancedPDO::prepare'); } // phpcs:ignore

        if (!is_string($sql) || trim($sql) === '') {
            throw new Exception(
                'EnhancedPDO::prepare() expects first parameter '.
                '$sql to be a non-empty string.'
            );
        }

        if ($db !== null) {
            if (is_object($db)) {
                if (is_a($db, 'PDO')) {
                    $_db = $db;
                } elseif (is_a($db, 'EnhancedPDO')) {
                    $_db = $db->getDB();
                } else {
                    throw new Exception(
                        'EnhancedPDO::prepare() expects second '.
                        'parameter $db to either be NULL or to be '.
                        'an PDO object. '.get_class($db).' given'
                    );
                }
            } else {
                throw new Exception(
                    'EnhancedPDO::prepare() expects second parameter $db '.
                    'to either be NULL or to be an PDO object. '.
                    gettype($db).' given'
                );
            }
        } else {
            // Just use the default DB connection
            $_db = $this->_db;
        }

        try {
            return $_db->prepare($sql);
        } catch (Exception $e) {
            debug(
                'level=-1',
                'Error when preparing SQL statement',
                // $_db->errorCode(), Code also returned by errorInfo()
                $_db->errorInfo(),
                $e->getMessage()
            );
            return false;
        }
    }

    /**
     * Get a prepared PDO statement
     *
     * NOTE: This is just an alias of EnhancedPDO::prepare();
     *
     * @param string               $sql SQL statement
     * @param PDO|EnhancedPDO|null $db  Override DB connection
     *
     * @return PDOStatement|false
     */
    public function prep($sql, $db = null)
    {
        if (NEW_RELIC) { newrelic_add_custom_tracer('EnhancedPDO::prep'); } // phpcs:ignore

        try {
            return $this->prepare($sql, $db);
        } catch(Exception $e) {
            // throw new Exception($this->_rethrow($e, 'prep'));
            throw $e;
        }
    }

    /**
     * Execute supplied PDO statement then check if there were any
     * errors. If so, throw an exception with the error text as the
     * Exception message
     *
     * @param PDOStatement $stmt PDO Statement to be executed and
     *                           checked
     * @param integer      $up   Debug level (Makes the debug
     *                           metadata show where this this
     *                           method was called from rather
     *                           than this method itself)
     *
     * @return PDOStatement
     * @throws Exception if there is an error with the query
     */
    public function execute(PDOStatement $stmt, int $up = 1) : PDOStatement
    {
        if (NEW_RELIC) { newrelic_add_custom_tracer('EnhancedPDO::execute'); } // phpcs:ignore

        try {
            $stmt->execute();
        } catch(Exception $e) {
            // Capture the contents of PDOStatement::debugDumpParams()
            // so it can be used when throwing an exception;
            $bits = $this->debug($stmt, true, $up);

            if (trim($bits['msg']) === '') {
                throw $e;
            } else {
                throw new Exception("{$bits['msg']}\n\n{$bits['sent']}");
 	        }
        }

        if (!in_array($stmt->errorCode(), $this->_successCodes)) {
            // Capture the contents of PDOStatement::debugDumpParams()
            // so it can be used when throwing an exception;
            $bits = $this->debug($stmt, true, $up);

            throw new Exception($bits['msg']."\n\n".$bits['sent']."\n");
        }

        return $stmt;
    }

    /**
     * Execute supplied PDO statement then check if there were any
     * errors. If so, throw an exception with the error text as the
     * Exception message
     *
     * NOTE: This is just an alias of EnhancedPDO::execute();
     *
     * @param PDOStatement $stmt PDO Statement to be executed and
     *                           checked
     * @param integer      $up   Debug level (Makes the debug
     *                           metadata show where this this
     *                           method was called from rather
     *                           than this method itself)
     *
     * @return PDOStatement
     * @throws Exception if there is an error with the query
     */
    public function exec(PDOStatement $stmt, int $up = 1) : PDOStatement
    {
        if (NEW_RELIC) { newrelic_add_custom_tracer('EnhancedPDO::exec'); } // phpcs:ignore

        try {
            return $this->execute($stmt, $up + 1);
        } catch (Exception $e) {
            // throw new Exception($this->_rethrow($e, 'exec'));
            throw $e;
        }
    }

    /**
     * Capture the contents of PDOStatement::debugDumpParams() so
     * it can be used elsewhere
     *
     * @param PDOStatement $stmt  Statement to be debuged
     * @param boolean      $debug Whether or not to pass return value
     *                            into debug() before returning it.
     * @param integer      $up    How many levels up the stack should
     *                            the debug message appear to come
     *                            from
     *
     * @return array with 5 keys:
     *               `raw`    - Raw SQL passed to prepare();
     *               `sent`   - The SQL with parameters bound in
     *                          NOTE: `sent` will be empty if
     *                                 statement has not yet been
     *                                 executed;
     *               `params` - List of all the parameters the query
     *                          expects;
     *               `code`   - SQL Error code (if query has been
     *                          executed)
     *               `msg`    - SQL Error message (if query has been
     *                          executed)
     */
    public function debug(
        PDOStatement $stmt, bool $debug = false, int $up = 1
    ) : array {
        if (NEW_RELIC) { newrelic_add_custom_tracer('EnhancedPDO::debug'); } // phpcs:ignore

        // Capture the contents of PDOStatement::debugDumpParams()
        // so it can be used elsewhere
        ob_start();
        $stmt->debugDumpParams();
        $data = ob_get_clean();

        $output = [
            'raw' => '',
            'sent' => '',
            'params' => [],
            'code' => $stmt->errorCode(),
            'msg' => ''
        ];

        $regex1 = '/^.*?SQL:(?: *\[[0-9]+\])?(?<raw>.*?)[\r\n]+'.
                '(?:Sent SQL:(?: *\[[0-9]+\])(?<sent>.*?)[\r\n]+)?'.
                             'Params: *[0-9]+(?<params>.*)$/is';

        $regex2 = '/Key: Name: \[[0-9]+\] (?<name1>:[a-z0-9_]+)'.
                              '.*?paramno=(?<num>-?[0-9]+).*?'.
                        'name=\[[0-9]+\] "(?<name2>[^"]+)".*?'.
                                'is_param=(?<isParam>[0-9]+).*?'.
                              'param_type=(?<type>[0-9]+)/is';

        if (preg_match($regex1, $data, $bits)) {
            $output['raw'] = trim($bits['raw']);
            $output['sent'] = trim($bits['sent']);
            $bits = trim($bits['params']);

            if (preg_match_all($regex2, $bits, $params, PREG_SET_ORDER)) {
                for ($a = 0; $a < count($params); $a += 1) {
                    $output['params'][] = [
                        'name' => ($params[$a]['name2'] !== '')
                            ? $params[$a]['name2']
                            : $params[$a]['name1'],
                        'number' => $params[$a]['num'],
                        'type' => $params[$a]['type'],
                        'isParam' => ($params[$a]['isParam'] == 1)
                    ];
                }
            }
        }

        if (!in_array($output['code'], $this->_successCodes)) {
            // Query has been set to server, get whatever error info
            // is available and update the output
            $tmp = $stmt->errorInfo();
            $output['code'] = $tmp[0];
            $output['msg'] = $tmp[2];
        }

        if ($output['raw'] === '') {
            // Looks like query may not have been sent to the server.
            // Just get the original query string
            $output['raw'] = $stmt->queryString;
        }

        $up += 1;

        if ($debug === true) {
            debug($output, 'meta-level='.$up);
        }

        return $output;
    }

    /**
     * Prepare a PDO statement with :ID parameter, bind the supplied
     * ID value to the statement then return the statement for
     * further binding and execution
     *
     * NOTE: When binding ID this assumes that the statement's SQL
     *       contains an :ID parameter, however, you can override
     *       this by passing a different string as the third parameter
     *
     * @param string               $sql   SQL statement string to be
     *                                    prepared
     * @param integer              $id    ID of thing to be bound in
     *                                    (If ID is false bind step
     *                                    is skipped)
     * @param string               $param Parameter name to bind to
     * @param PDO|EnhancedPDO|null $db    Override database
     *                                    connection object
     *
     * @return PDOStatement Query with supplied single string
     *                      parameter bound in
     * @throws Exception if SQL is not a string or is empty
     */
    public function prepBind(
        string $sql, int $id, string $param = 'ID', $db = null
    ) : PDOStatement {
        if (NEW_RELIC) { newrelic_add_custom_tracer('EnhancedPDO::prepBind'); } // phpcs:ignore

        try {
            $stmt = $this->prepare($sql, $db);
        } catch(Exception $e) {
            // throw new Exception($this->_rethrow($e, 'prepBind'));
            throw $e;
        }

        $stmt->bindParam(":$param", $id, PDO::PARAM_INT);

        return $stmt;
    }

    /**
     * Prepare a PDO statement with :ID parameter, bind the supplied
     * ID value to the statement then return the statement for
     * further binding and execution
     *
     * NOTE: When binding $str this assumes that the statement's SQL
     *       contains an :STR parameter, however, you can override
     *       this by passing a different string as the third parameter
     *
     * @param string               $sql   SQL statement string to be
     *                                    prepared
     * @param string               $str   String to be bound in
     * @param string               $param Parameter name to bind to
     * @param PDO|EnhancedPDO|null $db    Override database
     *                                    connection object
     *
     * @return PDOStatement Query with supplied a single string
     *                      parameter bound in
     * @throws Exception if SQL is not a string or is empty
     */
    public function prepBindStr(
        string $sql, string $str, string $param = 'STR', $db = null
    ) : PDOStatement {
        if (NEW_RELIC) { newrelic_add_custom_tracer('EnhancedPDO::prepBindStr'); } // phpcs:ignore

        try {
            $stmt = $this->prepare($sql, $db);
        } catch(Exception $e) {
            // throw new Exception($this->_rethrow($e, 'prepBindStr'));
            throw $e;
        }

        $stmt->bindParam(":$param", $str, PDO::PARAM_STR);

        return $stmt;
    }

    /**
     * Prepare a PDO statement that uses a "IN" clause as it's
     * only clause, build a list of parameters for the IN clause and
     * bind all the values to the parameters and return the statement.
     *
     * This assumes that the query contains an "IN" clause with a
     * value of `[[PARAMS]]` e.g.
     * ```sql
     * SELECT *
     * FROM   users
     * WHERE  user_id IN ( [[PARAMS]] );
     * ```
     *
     * @param string               $sql  SQL statement containing
     * @param array                $ins  List of values
     * @param integer              $type PDO PARAM type
     *                                   [default: 1 (PDO::PARAM_INT)]
     * @param PDO|EnhancedPDO|null $db   Database connection object
     *
     * @return PDOStatement
     */
    public function prepBindIn(
        string $sql, array $ins, int $type = PDO::PARAM_INT, $db = null
    ) : PDOStatement {
        if (NEW_RELIC) { newrelic_add_custom_tracer('prepBindIn'); } // phpcs:ignore

        if (!is_string($sql) || $sql === '') {
            throw new Exception(
                'EnhancedPDO::prepBindIn() expects first parameter '.
                '$sql to be a non-empty string'
            );
        }

        $params = '';
        $sep = '';
        for ($a = 0, $c = count($ins); $a < $c; $a += 1) {
            $params .= $sep.':IN_'.$a;
            $sep = ', ';
        }

        $stmt = $this->prep(
            str_replace('[[PARAMS]]', $params, $sql),
            $db
        );

        for ($a = 0, $c = count($ins); $a < $c; $a += 1) {
            $stmt->bindParam(':IN_'.$a, $ins[$a], $type);
        }

        return $stmt;
    }

    /**
     * Prepare a PDO statement that uses a "IN" clause as it's
     * only clause, build a list of parameters for the IN clause and
     * bind all the values to the parameters, execute the statement
     * and return it.
     *
     * This assumes that the query contains an "IN" clause with a
     * value of `[[PARAMS]]` e.g.
     * ```sql
     * SELECT *
     * FROM   users
     * WHERE  user_id IN ( [[PARAMS]] );
     * ```
     *
     * @param string               $sql  SQL statement containing
     * @param array                $ins  List of values
     * @param integer              $type PDO PARAM type
     *                                   [default: 1 (PDO::PARAM_INT)]
     * @param PDO|EnhancedPDO|null $db   Database connection object
     *
     * @return PDOStatement
     */
    public function prepBindExecIn(
        string $sql, array $ins, int $type = PDO::PARAM_INT, $db = null
    ) : PDOStatement {
        if (NEW_RELIC) { newrelic_add_custom_tracer('prepBindExecIn'); } // phpcs:ignore

        try {
            $stmt = $this->prepBindIn($sql, $ins, $type, $db);
        } catch(Exception $e) {
            // throw new Exception($this->_rethrow($e, 'prepBindExec'));
            throw $e;
        }

        try {
            $stmt = $this->execute($stmt, 2);
        } catch(Exception $e) {
            // throw new Exception($this->_rethrow($e, 'prepBindExec'));
            throw $e;
        }

        return $stmt;
    }

    /**
     * Take a pre-prepared PDO statement, bind a single ID to it,
     * execute it then return the statement
     *
     * NOTE: When binding ID this assumes that the statement's SQL
     *       contains a single integer :ID parameter, however, you
     *       can override this by passing a different string as the
     *       third parameter.
     * NOTE ALSO: If there are multiple parameters, they must be
     *       bound in to the statement before calling
     *       EnhancedPDO::bindExec()
     *
     * @param PDOStatement $stmt  PDO Statement to which parameters
     *                            are bound and executed
     * @param ?integer     $id    ID of thing to be bound in
     *                            (If ID is NULL bind step is skipped)
     * @param string       $param Parameter name to bind to
     *
     * @return PDOStatement
     * @throws Exception if there is an error with the query
     */
    public function bindExec(
        PDOStatement $stmt, ?int $id = null, string $param = 'ID'
    ) : PDOStatement {
        if (NEW_RELIC) { newrelic_add_custom_tracer('EnhancedPDO::bindExec'); } // phpcs:ignore

        if ($id !== null) {
            $stmt->bindParam(":$param", $id, PDO::PARAM_INT);
        }

        try {
            return $this->execute($stmt, 2);
        } catch (Exception $e) {
            // throw new Exception($this->_rethrow($e, 'bindExec'));
            throw $e;
        }
    }

    /**
     * Take a pre-prepared PDO statement, bind a single string value
     * to it, execute it then return the statement
     *
     * NOTE: When binding $str this assumes that the statement's SQL
     *       contains a single :STR parameter (and no other params)
     *
     * @param PDOStatement $stmt  PDO Statement to which parameters
     *                            are bound and executed
     * @param string       $str   String value to be bound in
     * @param string       $param Parameter name to bind to
     *
     * @return PDOStatement
     * @throws Exception if there is an error with the query
     */
    public function bindExecStr(
        PDOStatement $stmt, string $str, string $param = 'STR'
    ) : PDOStatement {
        if (NEW_RELIC) { newrelic_add_custom_tracer('EnhancedPDO::bindExecStr'); } // phpcs:ignore

        $stmt->bindParam(":$param", $str, PDO::PARAM_STR);

        try {
            return $this->execute($stmt, 2);
        } catch (Exception $e) {
            // throw new Exception($this->_rethrow($e, 'bindExecStr'));
            throw $e;
        }
    }

    /**
     * Prepare a PDO statement with :ID parameter, bind the supplied
     * ID value to the statement then execute the statement then
     * return the statement for further use
     *
     * NOTE: When binding ID this assumes that the statement's SQL
     *       contains a single :ID parameter (and no other params)
     *
     * @param string               $sql   SQL statement string to be
     *                                    prepared
     * @param ?integer             $id    ID of thing to be bound in
     *                                    (If ID is false bind step
     *                                    is skipped)
     * @param string               $param Parameter name to bind to
     * @param PDO|EnhancedPDO|null $db    Override database
     *                                    connection object
     *
     * @return PDOStatement Query with supplied parameters
     *                      bound in and executed, ready for fetching
     *                      (if appropriate).
     *                      Or FALSE if
     * @throws Exception if there is an error with the query
     */
    public function prepBindExec(
        string $sql, ?int $id = null, string $param = 'ID', $db = null
    ) : PDOStatement {
        if (NEW_RELIC) { newrelic_add_custom_tracer('EnhancedPDO::prepBindExec'); } // phpcs:ignore

        try {
            $stmt = $this->prepare($sql, $db);
        } catch (Exception $e) {
            // throw new Exception($this->_rethrow($e, 'prepBindExec'));
            throw $e;
        }

        if ($id !== null) {
            $stmt->bindParam(':'.$param, $id, PDO::PARAM_INT);
        }

        try {
            return $this->execute($stmt, 2);
        } catch (Exception $e) {
            // throw new Exception($this->_rethrow($e, 'prepBindExec'));
            throw $e;
        }
    }

    /**
     * Prepare a PDO statement with :STR parameter, bind the supplied
     * $str value to the statement then execute the statement then
     * return the statement for further use
     *
     * NOTE: When binding $str this assumes that the statement's SQL
     *       contains an :STR parameter
     *
     * @param string               $sql   SQL statement string to be
     *                                    prepared
     * @param string               $str   String value to be bound in
     *                                    (If Str is false bind step
     *                                    is skipped)
     * @param string               $param Parameter name to bind to
     * @param PDO|EnhancedPDO|null $db    Override database
     *                                    connection object
     *
     * @return PDOStatement|false Query with supplied parameters
     *                      bound in and executed, ready for fetching
     *                      (if appropriate).
     *                      Or FALSE if
     * @throws Exception if there is an error with the query
     */
    public function prepBindExecStr(
        string $sql, string $str, string $param = 'STR', $db = null
    ) : PDOStatement {
        if (NEW_RELIC) { newrelic_add_custom_tracer('EnhancedPDO::prepBindExecStr'); } // phpcs:ignore

        try {
            $stmt = $this->prepare($sql, $db);
        } catch (Exception $e) {
            // throw new Exception($this->_rethrow($e, 'prepBindExecStr'));
            throw $e;
        }

        $stmt->bindParam(':'.$param, $str, PDO::PARAM_STR);

        try {
            return $this->execute($stmt, 2);
        } catch (Exception $e) {
            // throw new Exception($this->_rethrow($e, 'prepBindExecStr'));
            throw $e;
        }
    }

    /**
     * Bind string value to parameter in PDO statement
     *
     * @param PDOStatement $stmt  PDO Statement to which parameters
     *                            are bound and executed
     * @param string       $param Parameter name to bind to
     * @param string       $value String value to be bound in
     *
     * @return PDOStatement
     */
    public function bindStr(
        PDOStatement $stmt, string $param, string $value
    ) : PDOStatement {
        $stmt->bindParam(':'.$param, $value, PDO::PARAM_STR);
        return $stmt;
    }

    /**
     * Bind integer value to parameter in PDO statement
     *
     * This is a useful shortcut when the value you want to bind in
     * is the return value of a function or method. Using this saves
     * you having to create a separate variable before you bind in
     * the value.
     *
     * @param PDOStatement $stmt  PDO Statement to which parameters
     *                            are bound and executed
     * @param string       $param Parameter name to bind to
     * @param integer      $value String value to be bound in
     *
     * @return PDOStatement
     */
    public function bindInt(
        PDOStatement $stmt, string $param, int $value
    ) : PDOStatement {
        $stmt->bindParam(':'.$param, $value, PDO::PARAM_INT);
        return $stmt;
    }

    /**
     * Bind integer value to parameter in PDO statement
     *
     * This is a useful shortcut when the value you want to bind in
     * is the return value of a function or method. Using this saves
     * you having to create a separate variable before you bind in
     * the value.
     *
     * @param PDOStatement $stmt  PDO Statement to which parameters
     *                            are bound and executed
     * @param string       $param Parameter name to bind to
     * @param boolean      $value String value to be bound in
     *
     * @return PDOStatement
     */
    public function bindBool(
        PDOStatement $stmt, string $param, bool $value
    ) : PDOStatement {
        $stmt->bindParam(':'.$param, $value, PDO::PARAM_BOOL);
        return $stmt;
    }

    /**
     * Bind integer value to parameter in PDO statement
     *
     * This is a useful shortcut when the value you want to bind in
     * is the return value of a function or method. Using this saves
     * you having to create a separate variable before you bind in
     * the value.
     *
     * @param PDOStatement $stmt  PDO Statement to which parameters
     *                            are bound and executed
     * @param string       $param Parameter name to bind to
     *
     * @return PDOStatement
     */
    public function bindNull(PDOStatement $stmt, string $param) : PDOStatement
    {
        $stmt->bindParam(':'.$param, null, PDO::PARAM_NULL);
        return $stmt;
    }

    /**
     * Get the ID of the last row inserted into the database.
     *
     * @return string|int|float
     */
    public function getLastID()
    {
        if (NEW_RELIC) { newrelic_add_custom_tracer('EnhancedPDO::getLastID'); } // phpcs:ignore

        $output = $this->_db->lastInsertId();
        return (is_numeric($output))
            ? $output * 1
            : $output;
    }

    /**
     * Is the PDO statement OK and will it return any rows
     *
     * @param mixed $stmt Value that should be a PDO statement but
     *                    may also be false
     *
     * @return bool TRUE if statement is OK and some rows were
     *              selected or effected
     */
    public function isOK($stmt) : bool
    {
        if (NEW_RELIC) { newrelic_add_custom_tracer('EnhancedPDO::isOK'); } // phpcs:ignore

        return ($stmt instanceof PDOStatement
                && $stmt->errorCode() === '00000');
    }

    /**
     * Replace original method name with current method name
     *
     * @param Exception $e      Exception being thrown
     * @param string    $method Method name where exception is being
     *                          rethrown
     *
     * @return string
     */
    private function _rethrow(Exception $e, string $method) : string
    {
        if (NEW_RELIC) { newrelic_add_custom_tracer('EnhancedPDO::_rethrow'); } // phpcs:ignore

        return str_replace(
            [
                'prepare', 'prepBind', 'prepBindStr', 'execute',
                'bindExec', 'bindExecStr', 'prepBindIn'
            ],
            $method,
            $e->getMessage()
        );
    }
}

if (!defined('NEW_RELIC')) {
    define('NEW_RELIC', extension_loaded('newrelic'));
    if (!function_exists('newrelic_add_custom_tracer')) {
        function newrelic_add_custom_tracer() {} // phpcs:ignore
    }
}
