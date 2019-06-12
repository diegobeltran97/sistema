<?php

class mysql_connection
{

    /* public: connection parameters */
    private $Host = "";
    private $Database = "";
    private $User = "";
    private $Password = "";

    /* public: configuration parameters */
    private $Debug = false; ## Set to true for debugging messages.
    private $Halt_On_Error = true; ## Set to true (halt with message), Set to false (ignore errors quietly)
    private $Mail_On_Halt = true; // mail error ($Halt_On_Error must be true to work)

    /* public: current error number and error text */
    private $Errno = 0;
    private $Error = "";
    private $db;

    public function __construct($Host = '', $Database = '', $User = '', $Password = '')
    {
        if (!empty($Host) && !empty($Database) && !empty($User)) {
            $this->connect($Host, $Database, $User, $Password);
        }
    }

    /* public: connection management */
    public function connect($Host = '', $Database = '', $User = '', $Password = '')
    {

        $na = func_num_args();

        if ($na > 0)
            $this->Host = $Host;
        if ($na > 1)
            $this->User = $User;
        if ($na > 2)
            $this->Password = $Password;
        if ($na > 3)
            $this->Database = $Database;

        /* establish connection, select database */
        if (!$this->db) {
            if ($this->Debug)
                printf("Init DB: = %s<br>\n", $Database);

            try {

                $opt = array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                );

                $this->db = new PDO("mysql:dbname={$this->Database};host={$this->Host}",
                    $this->User, $this->Password, $opt);

            } catch (PDOException $e) {
                $this->halt('Connection failed: ' . $e->getMessage());
                return 0;
            }
        }

        return $this->db;
    }

    /* public: perform a query */
    public function query($query_string, $input_parameters = array())
    {
        if (empty($query_string) || !is_array($input_parameters))
            return 0;

        if (!$this->connect()) {
            return 0; /* we already complained in connect() about that. */
        };

        if ($this->Debug)
            printf("Debug: query = %s<br>\n", $this->sql_debug($query_string, $input_parameters));

        try {
            if (count($input_parameters) > 0) {
                $sent = $this->db->prepare($query_string);
                $sent->execute($input_parameters);
                return $sent;
            } else {
                return $this->db->query($query_string);
            }
        } catch (PDOException $e) {
            $this->halt($e->getMessage());
            die();
        }
    }

// BEGIN wes addition

    public function last_insert_id()
    {
        if (!$this->db)
            return 0;

        return $this->db->lastInsertId();
    }

// END wes addition

    public function make_arg_safe($argument)
    {
        if (!is_string($argument))
            return $argument;

        return stripslashes($argument);
    }

    public function fetch_row($res)
    {
        return $res->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);
    }

    public function fetch_array($res)
    {
        return $res->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
    }

    public function num_rows($res)
    {
        return $res->rowCount();
    }

    public function affected_rows($res)
    {
        return $this->num_rows($res);
    }

    public function beginTransaction()
    {
        $this->db->beginTransaction();
    }

    public function commit()
    {
        $this->db->commit();
    }

    public function rollBack()
    {
        $this->db->rollBack();
    }


    /* private: error handling */

    public function sql_debug($sqlString, $placeholders)
    {
        foreach ($placeholders as $k => $v) {
            $sqlString = preg_replace('/' . $k . '/', "'" . $v . "'", $sqlString);
        }
        return $sqlString;
    }

    public function halt($msg = '')
    {

        //if can't use Link_ID
        if (!$this->Halt_On_Error)
            return;

        if ($this->db) {
            $errorInfo = $this->db->errorInfo();
            if ($errorInfo[1]) {
                $this->Error = $errorInfo[2];
                $this->Errno = $errorInfo[1];
            }
        }

        $this->haltmsg($msg);
        die("Session halted.");
    }

    public function haltmsg($msg = '')
    {
        $haltmsg = '';

        if ($msg) {
            $haltmsg .= sprintf("<b>Database error:</b> %s<br>\n", $msg);
        }
        if ($this->Errno || $this->Error) {
            $haltmsg .= sprintf("<b>MySQL Error</b>: %s (%s)<br>\n",
                $this->Errno,
                $this->Error);
        }

        printf($haltmsg);

        $haltmsg_extra = '';

        foreach(debug_backtrace() as $fdebug) {
            if(basename($fdebug['file']) != basename(__FILE__) ) {
                $haltmsg_extra = "
                <b>Archivo:</b> {$fdebug['file']}<br />
                <b>Línea:</b> {$fdebug['line']}<br />";
                break;
            }
        }
        var_dump($haltmsg_extra);
        /* Send Mail On MySQL Error
        if ($this->Mail_On_Halt)
            db_error_mail($haltmsg . $haltmsg_extra);*/
    }

    public function error_return()
    {
        if ($this->db) {
            $errorInfo = $this->db->errorInfo();
            if ($errorInfo[1]) {
                return '(' . $errorInfo[2] . ') ' . $errorInfo[1];
            }
        }
        return '';
    }

}
