<?php

class Msg
{
    var $message = array();

    function addMsg($msg, $type)
    {
        if ( empty($msg) )
            return false;

        $this->message[$type] = isset($this->message[$type]) ? $this->message[$type] . "<br />" : "";
        $this->message[$type] .= $msg;
    }

    function outputMsg()
    {
        if ( is_array( $this->message ) && count( $this->message ) > 0 ) {
            foreach ( $this->message as $type => $msg ) {
                switch ( $type ) {
                    case "success":
                    case "info":
                    case "warning":
                    case "danger":
                        echo '<div class="alert alert-' . $type . '">' . $msg . '</div>';
                        break;

                    default:
                        break;
                }
            }
        }
    }

}

class debug
{
    var $status = true;

    function msg($code, $msg = "", $info = "")
    {
        $code = '{RESPONSE CODE: "' . $code . '"}';
        $msg = !empty($msg) ? '{MSG: "' . $msg . '"}' : '';
        $info = !empty($info) ? '{INFO: "' . $info . '"}' : '';
        return $code . $msg . $info;
    }

    function response($msg, $die = true)
    {
        $this->status = false;
        if ($die) {
            die($msg);
        } else {
            return false;
        }
    }
}

?>