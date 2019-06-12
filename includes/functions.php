<?php

function db_insert($table, $data, $extra_query = '')
{
    global $db;

    $query = "INSERT INTO $table ";
    $fields = implode(", ", array_keys($data));
    $input_parameters = array();

    foreach ($data as $k => $safe_data) {

        if (is_array($safe_data)) {
            $safe_data = serialize64($safe_data);
        } elseif (is_string($safe_data)) {
            $safe_data = stripslashes($safe_data);
        }

        $data[$k] = ':' . $k;
        $input_parameters[':' . $k] = $safe_data;

    }
    $values = "" . implode(", ", $data) . "";

    $query .= "($fields) VALUES (" . $values . ")";

    $result = $db->query($query . ' ' . $extra_query, $input_parameters);


    return $result;
}

function db_update($table, $data, $condition = "")
{
    global $db;

    $data_values = array();
    $query = "UPDATE $table SET";
    $input_parameters = array();

    foreach ($data as $field => $value) {

        if (is_array($value)) {
            $value = serialize64($value);
        } elseif (is_string($value)) {
            $value = stripslashes($value);
        }
        $input_parameters[':' . $field] = $value;
        $data_values[] = " $field = :" . $field . "";
    }
    $query .= implode(", ", $data_values) . " $condition";

    $result = $db->query($query, $input_parameters);

    return $result;
}

function db_select($table, $data = array(), $sep = "AND", $condition = "", $fields = "*")
{

    global $db;

    $data_values = array();

    $query = "SELECT $fields FROM $table";

    if (!empty($data)) {
        $query .= " WHERE";
    }

    foreach ($data as $field => $value) {
        $value = $db->make_arg_safe($value);
        $data_values[] = " $field = '$value'";
    }
    $query .= implode(" $sep ", $data_values) . " $condition";


    return $db->query($query);

}

function db_delete($table, $condition)
{

    global $db;

    $query = "DELETE FROM $table $condition";

    return $db->query($query);

}

function form_input($name, $id = "", $type = "text", $def_value = "", $label = "", $extra_html = "")
{

    $output = "";

    if (!empty($label)) {
        $output .= "<label";
        if (!empty($id)) {
            $output .= " id='$id'";
        }
        $output .= ">" . $label;
    }
    $output .= '<input type="' . $type . '" name="' . $name . '" value="' . htmlspecialchars($def_value) . '"';

    if (!empty($id) && empty($label)) {
        $output .= ' id="' . $id . '"';
    }

    $output .= " " . $extra_html . " />";

    if (!empty($label)) {
        $output .= "</label>";
    }

    echo $output;

}

function form_select($name, $id = "", $options, $def_value = "", $extra_html = "")
{

    $output = '<select name="' . $name . '"';

    if (!empty($id)) {
        $output .= ' id="' . $id . '"';
    }

    $output .= " " . $extra_html . ">";

    if (is_array($options)) {
        foreach ($options as $opt) {
            $sel = "";
            if ($def_value == $opt["value"]) {
                $sel = " selected";
            }

            $output .= '<option value="' . $opt["value"] . '"' . $sel . '>' . $opt["text"] . '</option>';

        }
    }

    $output .= "</select>";

    echo $output;

}

function form_label($text, $for = "", $extra_html = "")
{
    $output = '<label for="' . $for . '" ' . $extra_html . '>' . $text . '</label>';
    echo $output;
}

/* Ejecutar funcion en seccion especifica del codigo */
function add_action($tag, $function_to_add)
{
    global $$tag;

    $$tag = !isset($$tag) ? $$tag = array() : $$tag;

    ${$tag}[] = $function_to_add;

}

/* Ejecuta las funciones cargadas por add_action */
function load_function($function_to_load)
{
    global $$function_to_load;

    if (!is_array($$function_to_load)) {
        return false;
    }

    foreach ($$function_to_load as $function) {

        if (is_array($function)) {
            call_user_func_array($function[0], $function[1]);
        } elseif (function_exists($function)) {
            call_user_func($function);
        }

    }

}

function place_action($function_to_load)
{
    ob_start();
    load_function($function_to_load);
    $response_html = ob_get_contents();
    ob_end_clean();

    return $response_html;

}

function is_serialized($data)
{
// if it isn't a string, it isn't serialized
    if (!is_string($data)) {
        return false;
    }

    if ($base64_var = base64_decode($data, true)) {
        $data = $base64_var;
    }

    $data = trim($data);
    if ('N;' == $data) {
        return true;
    }
    $length = strlen($data);
    if ($length < 4) {
        return false;
    }
    if (':' !== $data[1]) {
        return false;
    }
    $lastc = $data[$length - 1];
    if (';' !== $lastc && '}' !== $lastc) {
        return false;
    }
    $token = $data[0];
    switch ($token) {
        case 's' :
            if ('"' !== $data[$length - 2]) {
                return false;
            }
        case 'a' :
        case 'O' :
            return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
        case 'b' :
        case 'i' :
        case 'd' :
            return (bool)preg_match("/^{$token}:[0-9.E-]+;\$/", $data);
    }
    return false;
}

function getUrl()
{
    $url = "http" . ((!empty($_SERVER['HTTPS'])) ? "s" : "") . "://" . $_SERVER['SERVER_NAME'];

    if ($_SERVER["SERVER_PORT"] != "80") {
        $url .= ":" . $_SERVER["SERVER_PORT"];
    }

    return $url;
}

function getCurrentUrl()
{
    $url = getUrl() . $_SERVER['REQUEST_URI'];
    return $url;
}

function getUrlWithout($getNames)
{
    $url = getCurrentUrl();
    $questionMarkExp = explode("?", $url);
    $questionMarkExp[1] = isset($questionMarkExp[1]) ? $questionMarkExp[1] : '';
    $urlArray = explode("&", $questionMarkExp[1]);
    $retUrl = $questionMarkExp[0];
    $retGet = "";
    $found = array();
    foreach ($getNames as $id => $name) {
        foreach ($urlArray as $key => $value) {
            if (isset($_GET[$name]) && $value == $name . "=" . $_GET[$name]) {
                unset($urlArray[$key]);
            }
        }
    }
    $urlArray = array_values($urlArray);
    foreach ($urlArray as $key => $value) {
        if ($key < sizeof($urlArray) && $retGet !== "") {
            $retGet .= "&";
        }
        $retGet .= $value;
    }
    return $retUrl . "?" . $retGet;
}

function get_options()
{
    global $db;

    $query = "SELECT * FROM opciones";
    $result = $db->query($query);

    $list = array();

    while ($opt = $db->fetch_array($result)) {
        $list[$opt["opcion"]] = is_serialized($opt["valor"]) ? unserialize64($opt["valor"]) : $opt["valor"];
    }

    return $list;
}

function update_option($option_name, $value)
{
    global $db;

    $value = $db->make_arg_safe($value);
    $query = "INSERT INTO opciones (opcion, valor) VALUES ('$option_name', '$value')
ON DUPLICATE KEY UPDATE valor = '$value'";
    $result = $db->query($query);
    return $result;
}

function user_search($search_text = "", $pag = 1, $amount = 10, $count = false)
{

    global $db;

    $limit_start = $amount * ($pag - 1);

    $data = $count ? "COUNT(*) AS count" : "*";

    $query = "SELECT $data FROM usuarios";

    if (!$count) {
        $query .= " LIMIT $limit_start, $amount";

        $result = $db->query($query);

        $list = array();

        while ($user = $db->fetch_array($result)) {
            $user = array_merge($user, array(
                'access' => is_serialized($user['access']) ? unserialize64($user['access']) : array(),
            ));
            $list[] = $user;
        }
        return $list;

    } else {

        $result = $db->query($query);
        $row = $db->fetch_array($result);

        return isset($row["count"]) ? (int)$row["count"] : 0;
    }

}

function user_list($value = "", $field = "user_id")
{
    global $db;

    $query = "SELECT * FROM usuarios";

    if (!empty($value)) {
        $value = $db->make_arg_safe($value);
        $query .= " WHERE $field = '$value' LIMIT 1";
    }

    $result = $db->query($query);

    if ($value) {
        $user = $db->fetch_array($result);
        if (isset($user['access'])) {
            $user['access'] = is_serialized($user['access']) ? unserialize64($user['access']) : array();
        }
        return $user;
    }

    $list = array();

    while ($user = $db->fetch_array($result)) {
        if (isset($user['access'])) {
            $user['access'] = is_serialized($user['access']) ? unserialize64($user['access']) : array();
        }
        $list[] = $user;
    }

    return $list;
}

function customer_list($value = "", $field = "t1.cliente_id", $single = false)
{
    global $db;

    $value = $db->make_arg_safe($value);

    $query = "SELECT t1.*, t2.razon_social  FROM clientes AS t1, clientes_nombres AS t2 WHERE t1.cliente_id = t2.cliente_id AND t2.actual = 1";

    if (!empty($value) || $single) {
        $query .= " AND $field = :customer_value";
    }

    if (!empty($single)) {
        $query .= " LIMIT 1";
    }

    $result = $db->query($query, array(
        ':customer_value' => $value,
    ));

    $list = array();

    while ($customer = $db->fetch_array($result)) {
        $list[] = $customer;
    }


    return $list;

}

function customer_search($search_text = "", $pag = 1, $amount = 10)
{

    global $db;

    $limit_start = $amount * ($pag - 1);

    $query = "SELECT SQL_CALC_FOUND_ROWS clientes.*  FROM clientes LEFT JOIN clientes_nombres ON clientes_nombres.cliente_id = clientes.cliente_id WHERE clientes_nombres.razon_social LIKE '%$search_text%'";

    $query .= " GROUP BY clientes.cliente_id ORDER BY clientes_nombres.actual DESC LIMIT $limit_start, $amount";

    $result = $db->query($query);
    $total = $db->fetch_array($db->query('SELECT FOUND_ROWS() AS count'));
    $list = array();

    while ($customer = $db->fetch_array($result)) {

        $customer = array_merge(array(
            "razon_social" => get_customer_current_name($customer["cliente_id"]),
            "pozos_realizados" => well_count($customer["cliente_id"]),
            "trabajos_realizados" => task_count($customer["cliente_id"]),
        ), $customer);

        $list[] = $customer;

    }


    return array($list, $total['count']);

}

function get_customer_names($cliente_id, $string_mode = 0, $exclude_current = false)
{
    global $db;

    $query = "SELECT * FROM clientes_nombres WHERE cliente_id = '$cliente_id'";

    if ($exclude_current) {
        $query .= " AND actual != 1";
    }

    $query .= " ORDER BY actual DESC, fecha_asignacion DESC";

    $result = $db->query($query);
    $list = array();

    while ($customer_name = $db->fetch_array($result)) {

        if ($string_mode) {
            $list[] = $customer_name["razon_social"];
        } else {
            $customer_name["fecha_asignacion"] = form_date_format($customer_name["fecha_asignacion"]);
            $list[] = $customer_name;
        }
    }

    if ($string_mode && $string_mode != 2) {
        $list = implode(", ", $list);
    }

    return $list;
}

function get_customer_current_name($cliente_id)
{
    global $db;

    $query = "SELECT * FROM clientes_nombres WHERE cliente_id = '$cliente_id' AND actual = 1 LIMIT 1";

    $result = $db->query($query);
    $customer_name = $db->fetch_array($result);

    return isset($customer_name["razon_social"]) ? $customer_name["razon_social"] : '-';
}

function set_current_customer_name($cliente_id, $customer_name_id)
{
    global $db;
    if (!customer_name_info($customer_name_id)) {
        return false;
    } else {
        $query = "UPDATE clientes_nombres SET actual = CASE
        WHEN razon_id = $customer_name_id THEN 1
        ELSE 0 END WHERE cliente_id = '$cliente_id'";
    }

    return $db->query($query);
}

function set_current_headquarter_name($headquarter_id, $headquarter_name_id)
{
    global $db;
    if (!headquarter_name_info($headquarter_name_id)) {
        return false;
    } else {
        $query = "UPDATE clientes_sedes_nombres SET actual = CASE
        WHEN sede_nombre_id = $headquarter_name_id THEN 1
        ELSE 0 END WHERE sede_id = '$headquarter_id'";
    }

    return $db->query($query);
}

function customer_name_info($customer_name_id)
{
    global $db;

    $query = "SELECT * FROM clientes_nombres WHERE razon_id = '$customer_name_id' LIMIT 1";

    $result = $db->query($query);
    $customer_name_info = $db->fetch_array($result);

    return $customer_name_info;
}

function headquarter_name_info($headquarter_name_id)
{
    global $db;
    var_dump($headquarter_name_id);
    $query = "SELECT * FROM clientes_sedes_nombres WHERE sede_nombre_id = '$headquarter_name_id' LIMIT 1";

    $result = $db->query($query);
    $headquarter_name_info = $db->fetch_array($result);

    return $headquarter_name_info;
}

function customer_info($search_value, $search_field = "t1.cliente_id", $extended_info = true)
{
    $customer_info = customer_list($search_value, $search_field, true);

    if (isset($customer_info[0])) {

        $customer_info = $customer_info[0];

        if ($extended_info) {
            $customer_info = array_merge($customer_info, array(
                "pozos_realizados" => well_count($customer_info["cliente_id"]),
                "trabajos_realizados" => task_count($customer_info["cliente_id"]),
            ));
        }
    }

    return $customer_info;
}

function get_sedes($cliente_id, $list_mode = false)
{
    global $db;

    $query = "SELECT s.*, sn.nombre FROM clientes_sedes s
    LEFT JOIN clientes_sedes_nombres sn ON sn.sede_id = s.sede_id AND sn.actual = 1
    WHERE s.cliente_id = '$cliente_id' GROUP BY s.sede_id ORDER BY sn.nombre DESC";

    $result = $db->query($query);

    $list = array();

    while ($sede = $db->fetch_array($result)) {
        if ($list_mode) {
            $list[] = array('value' => $sede['sede_id'], 'text' => $sede['nombre']);
        } else {
            $list[] = $sede;
        }
    }

    return $list;
}

function get_headquarter_info($headquarter_id)
{

    global $db;

    $query = "SELECT s.*, sn.nombre FROM clientes_sedes s
    LEFT JOIN clientes_sedes_nombres sn ON sn.sede_id = s.sede_id AND sn.actual = 1
    WHERE s.sede_id = '$headquarter_id' LIMIT 1";

    $result = $db->query($query);
    $headquarter_info = $db->fetch_array($result);

    return $headquarter_info;
}

function get_headquarter_info_by_name($customer_id, $headquarter_name)
{

    global $db;

    $query = "SELECT s.*, sn.nombre FROM clientes_sedes s
    INNER JOIN clientes_sedes_nombres sn ON sn.sede_id = s.sede_id AND sn.actual = 1
    WHERE s.cliente_id = '$customer_id' AND sn.nombre = :headquarter_name LIMIT 1";

    $result = $db->query($query, array(
        ':headquarter_name' => $headquarter_name,
    ));
    $headquarter_info = $db->fetch_array($result);

    return $headquarter_info;
}

function get_well_info_by_name($customer_id, $headquarter_id, $well_name)
{

    global $db;

    $query = "SELECT * FROM pozos WHERE cliente_id = :customer_id AND sede_id = :headquarter_id
    AND nombre = :well_name LIMIT 1";

    $result = $db->query($query, array(
        ':well_name' => $well_name,
        ':headquarter_id' => $headquarter_id,
        ':customer_id' => $customer_id,
    ));
    $headquarter_info = $db->fetch_array($result);

    return $headquarter_info;
}

function get_headquarter_name($headquarter_id)
{
    $headquarter_info = get_headquarter_info($headquarter_id);
    return isset($headquarter_info['nombre']) ? $headquarter_info['nombre'] : '-';
}

function userinfo($data, $sep = "AND", $active = false)
{
    global $db;

    $condition = "";

    if ($active) {
        $condition .= "AND status = '1'";
    }

    $condition .= " LIMIT 1";

    $result = db_select("usuarios", $data, $sep, $condition);


    $userinfo = $db->fetch_array($result);

    if ($userinfo) {
        $userinfo = array_merge($userinfo, array(
            "access" => is_serialized($userinfo["access"]) ? unserialize64($userinfo["access"]) : array(),
        ));
    }

    return $userinfo;
}

function login($ci, $email, $pass)
{
    global $db;
    echo $pass;
    $ci = $db->make_arg_safe($ci);
    $email = $db->make_arg_safe($email);

    // $result = $db->query("SELECT * FROM usuarios WHERE cedula = '$ci' AND email = '$email' AND clave = '$pass' LIMIT 1");
    $result = $db->query("SELECT * FROM usuarios WHERE cedula = '$ci' AND email = '$email' LIMIT 1");
    
    return $db->fetch_array($result);

}

function login_check($ci, $pass)
{
    global $db;

    $ci = $db->make_arg_safe($ci);

    $result = $db->query("SELECT * FROM usuarios WHERE cedula = '$ci' AND clave = '$pass' LIMIT 1");

    return $db->fetch_array($result);

}

//Nombre del dia de la semana de una fecha
function weekDay($datetime, $length = "")
{
    $weekday = date("w", strtotime($datetime));

    $days = array(
        'Domingo',
        'Lunes',
        'Martes',
        'Miercoles',
        'Jueves',
        'Viernes',
        'Sabado',
    );
    $day = $days[$weekday];

    if ($length) {
        $day = substr($day, 0, $length);
    }

    return $day;
}

function formatDate($datetime, $mode = "")
{
    $months = array(
        "Enero",
        "Febrero",
        "Marzo",
        "Abril",
        "Mayo",
        "Junio",
        "Julio",
        "Agosto",
        "Septiembre",
        "Octubre",
        "Noviembre",
        "Diciembre"
    );

    $date = explode("-", date('Y-m-d', strtotime($datetime)));
    $year = $date[0];
    $month = ($date[1] - 1);
    $day = $date[2];
    switch ($mode) {
        default:
            $output_string = $day . " " . $months[$month] . "<br />" . $year;
            break;

        case 1:
            $output_string = $year;
            break;

        case 2:
            $output_string = $months[$month];
            break;

        case 3:
            $output_string = $day;
            break;

        case 4:
            $output_string = $day . " de " . $months[$month] . " de " . $year;
            break;
    }
    return $output_string;
}

function check_valid_date($date)
{
    if (preg_match("^(\d{1,2})\/(\d{1,2})\/(\d{4})^", $date, $matches)) {
        if (checkdate((int)$matches[2], (int)$matches[1], (int)$matches[0])) {
            return true;
        }
    }
    return false;
}

function check_valid_email($email)
{

    if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $email)) {
        return true;
    }

    return false;

}

function db_date_format($date)
{

    if (empty($date)) {
        return "";
    }

    $date = explode("/", $date);
    $year = isset($date[2]) ? $date[2] : "0000";
    $month = isset($date[1]) ? $date[1] : "00";
    $day = isset($date[0]) ? $date[0] : "00";

    return $year . "-" . $month . "-" . $day;

}

function form_date_format($date)
{

    if (empty($date)) {
        return "";
    }

    $str_date = explode(" ", $date);
    $date = isset($str_date[0]) ? $str_date[0] : $date;
    $time = isset($str_date[1]) ? " " . $str_date[1] : "";

    $date = explode("-", $date);
    $year = isset($date[2]) ? $date[2] : "0000";
    $month = isset($date[1]) ? $date[1] : "00";
    $day = isset($date[0]) ? $date[0] : "00";

    return $year . "/" . $month . "/" . $day . $time;

}

function list_dates($datetime_ini, $datetime_fin)
{

    $datetime_ini = date('Y-m-d', strtotime($datetime_ini));
    $datetime_fin = date('Y-m-d', strtotime($datetime_fin));

    $dates_array = array();
    $current_date = $datetime_ini;

    while (strtotime($current_date) <= strtotime($datetime_fin)) {
        $dates_array[] = $current_date;
        $current_date = date("Y-m-d", strtotime("+1 day", strtotime($current_date)));
    }
    return $dates_array;
}

function encrypt_pass($pass)
{
    return md5(sha1($pass . NONCE_SALT) . NONCE_SALT);
}

function send_email_userinfo($userid)
{

    require_once(ABSPATH . 'includes/PHPMailer/class.phpmailer.php');
    $options = get_options();
    $mail = new PHPMailer(); // defaults to using php "mail()"
    $mail->SMTPDebug = false;
    $mail->do_debug = 0;
    $mail->CharSet = 'UTF-8';
    $mail->IsMail(); // telling the class to use SendMail transport

    $user_data = userinfo(array('user_id' => $userid));

    $cedula = isset($user_data["cedula"]) ? $user_data["cedula"] : "";
    $email = isset($user_data["email"]) ? $user_data["email"] : "";
    $seguridad = isset($user_data["pregunta_seguridad"]) ? $user_data["pregunta_seguridad"] : "";
    $pregunta_seguridad = isset($seguridad["pregunta_seguridad"]) ? $seguridad["pregunta_seguridad"] : "";
    $respuesta_seguridad = isset($seguridad["respuesta_seguridad"]) ? $seguridad["respuesta_seguridad"] : "";

    $body = "<p><b>Datos de acceso:</b></p>
<p>Cédula de Identidad: $cedula</p>
<p>Correo Electrónico: $email</p>
<p>Pregunta de Seguridad: $pregunta_seguridad</p>
<p>Respuesta de Seguridad: $respuesta_seguridad</p>";


    $mail->SetFrom($options["email_from"], $options["site_name"]);

    $address_list = array($email);

    foreach ($address_list as $address) {
        if (!empty($address)) {
            $mail->AddAddress($address);
        }
    }
    $mail->Subject = "Datos de Acceso";

    $mail->MsgHTML($body);

    if ($mail->Send()) {
        return true;
    }

    return false;

}

function email_restablecer($user_data)
{

    $send_email = isset($user_data["email"]) ? $user_data["email"] : "";

    if (empty($send_email)) {
        return false;
    }

    $body = '';

    require_once(ABSPATH . 'includes/PHPMailer/class.phpmailer.php');
    $options = get_options();
    $mail = new PHPMailer(); // defaults to using php "mail()"
    $mail->SMTPDebug = false;
    $mail->do_debug = 0;
    $mail->CharSet = 'UTF-8';
    $mail->IsMail(); // telling the class to use SendMail transport


    $token = reg_token($user_data["user_id"]);

    $reset_password_url = SITE_URL . "login.php?action=reset_password&step=2&token=$token";

    $body .= "<p>Ha enviado una petición para restablecer su contraseña.</p>
<p>Presione el siguiente enlace para proceder <a href='$reset_password_url'>$reset_password_url</a></p>";

    $mail->SetFrom($options["email_from"], $options["site_name"]);

    $address_list = array($send_email);

    foreach ($address_list as $address) {
        if (!empty($address)) {
            $mail->AddAddress($address);
        }
    }
    $mail->Subject = "Restablecer Contraseña";

    $mail->MsgHTML($body);

    if ($mail->Send()) {
        return true;
    }

    return false;

}

function reg_token($userid)
{

    $token = gen_token();

    while (get_token($token)) {
        $token = gen_token();
    }


    $token_data = array(
        'token' => $token,
        'user_id' => $userid,
        'gen_date' => date('Y-m-d H:i:s'),
    );

    db_insert("tokens", $token_data);

    return $token;

}

function gen_token()
{

    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    $token_length = 20;
    $token = "";

    for ($ch = 1; $ch <= $token_length; $ch++) {
        $token .= $chars[rand(0, 59)];
    }
    return $token;
}

function get_token($token)
{

    global $db;

    $token = $db->make_arg_safe($token);

    $result = $db->query("SELECT * FROM tokens WHERE token = '$token' LIMIT 1");

    return $db->fetch_array($result);

}

function timeDiff($firstTime, $lastTime)
{

    $firstTime = strtotime(date("Y-m-d H:i:s", strtotime($firstTime)));
    $lastTime = strtotime(date("Y-m-d H:i:s", strtotime($lastTime)));


    $timeDiff = $firstTime - $lastTime;
    $timeDiff = (int)($timeDiff / 60);
    return $timeDiff;
}

function _POST($var, $default_value = "")
{
    if (isset($_POST[$var])) {
        return $_POST[$var];
    } else {
        return $default_value;
    }
}

function _GET($var, $default_value = "")
{
    if (isset($_GET[$var])) {
        return $_GET[$var];
    } else {
        return $default_value;
    }
}

function is_day($day)
{
    if ($day != "" && $day >= 0 && $day < 7) {
        return $day;
    }
}

function array_safe_arguments($array)
{

    foreach ($array as $k => $v) {
        $array[$k] = stripslashes($v);
    }

    return $array;

}

function serialize64($mixed_variable)
{

    return base64_encode(serialize($mixed_variable));
}

function unserialize64($mixed_variable)
{
    if ($base64_var = base64_decode($mixed_variable, true)) {
        return unserialize($base64_var);
    } else {
        return unserialize($mixed_variable);
    }
}

function safe_arguments($string)
{
    return stripslashes($string);
}

function user_access_list()
{
    return array(
        'Pozos',
        'Trabajos',
        'Clientes',
        'Usuarios',
        'Ajustes del Sistema',
    );
}

function get_dir_list($entity, $default_text = '', $filter = '', $selected = '', $list_mode = false)
{
    global $db;

    $list = array();

    if ($default_text) {
        $list[] = array('text' => $default_text, 'value' => "", "selected" => "");
    }


    switch ($entity) {

        default:
            return array();

        case "estados":
            $value = 'id_estado';
            $text = 'estado';
            break;

        case "municipios":
            $value = 'id_municipio';
            $text = 'municipio';
            if (empty($filter) && !$list_mode) {
                return $list;
            }
            break;

        case "ciudades":
            $value = 'id_ciudad';
            $text = 'ciudad';
            if (empty($filter) && !$list_mode) {
                return $list;
            }
            break;
    }

    $query = "SELECT * FROM $entity";

    if (!empty($filter)) {
        $query .= " WHERE id_estado = '$filter'";
    }

    $result = $db->query($query);


    while ($row = $db->fetch_array($result)) {
        if ($list_mode) {
            $list[$row[$value]] = ucwords(strtolower($row[$text]));
        } else {
            $list[] = array(
                'text' => ucwords(strtolower($row[$text])),
                'value' => $row[$value],
                'selected' => $selected == $row[$value] ? ' selected' : ''
            );
        }

    }

    return $list;
}

function get_city_name($city_id)
{
    global $db;

    $query = "SELECT * FROM ciudades WHERE id_ciudad = '$city_id' LIMIT 1";

    $result = $db->query($query);
    $city_info = $db->fetch_array($result);

    return isset($city_info["ciudad"]) ? $city_info["ciudad"] : '-';
}

function get_state_name($state_id)
{

    global $db;

    $query = "SELECT * FROM estados WHERE id_estado = '$state_id' LIMIT 1";

    $result = $db->query($query);
    $state_info = $db->fetch_array($result);

    return isset($state_info["estado"]) ? $state_info["estado"] : '-';
}

function get_municipality_name($municipality_id)
{
    global $db;

    $query = "SELECT * FROM municipios WHERE id_municipio = '$municipality_id' LIMIT 1";

    $result = $db->query($query);
    $municipality_info = $db->fetch_array($result);

    return isset($municipality_info["municipio"]) ? $municipality_info["municipio"] : '-';
}

function well_search($mode, $search_text = "", $filters = array(), $pag = 1, $amount = 10)
{
    global $db;

    $limit_start = $amount * ($pag - 1);

    $query = '';
    $extra_query = '';

    $filter_query = array(
        "municipio" => "pozos.municipio = '%s'",
        "estado" => "pozos.estado = '%s'",
        "ciudad" => "pozos.ciudad = '%s'",
        "diam_min" => "pozos.diametro >= '%s'",
        "diam_max" => "pozos.diametro <= '%s'",
        "prof_min" => "pozos.profundidad >= '%s'",
        "prof_max" => "pozos.profundidad <= '%s'",
        "fecha_min" => "pozos.fecha_construccion >= '%s'",
        "fecha_max" => "pozos.fecha_construccion <= '%s'",
        "status_trabajos" => "(SELECT count(pozos_trabajos.trabajo_id) FROM pozos_trabajos WHERE pozos_trabajos.pozo_id = pozos.pozo_id AND status = '%s') > 0",
    );

    switch ($mode) {

        case 1:
            $query = "SELECT SQL_CALC_FOUND_ROWS pozos.* FROM pozos WHERE pozos.nombre LIKE '%$search_text%'";
            break;

        case 2:
            $query = "SELECT SQL_CALC_FOUND_ROWS pozos.* FROM pozos LEFT JOIN clientes_nombres ON clientes_nombres.cliente_id = pozos.cliente_id WHERE clientes_nombres.razon_social LIKE '%$search_text%'";

            $extra_query = " GROUP BY pozos.pozo_id ORDER BY clientes_nombres.actual DESC";
            break;

        case 3:
            $query = "SELECT SQL_CALC_FOUND_ROWS pozos.* FROM pozos
            LEFT JOIN clientes_sedes_nombres ON clientes_sedes_nombres.sede_id = pozos.sede_id
            WHERE clientes_sedes_nombres.nombre LIKE '%$search_text%'";

            $extra_query = " GROUP BY pozos.pozo_id ORDER BY clientes_sedes_nombres.actual DESC";
            break;

        case 4:
            $query = "SELECT SQL_CALC_FOUND_ROWS pozos.* FROM pozos WHERE pozos.cliente_id = '$search_text'";
            break;

        case 5:
            $query = "SELECT SQL_CALC_FOUND_ROWS pozos.* FROM pozos WHERE pozos.sede_id = '$search_text'";
            break;

        case 6:
            $query = "SELECT SQL_CALC_FOUND_ROWS pozos.* FROM pozos WHERE pozos.pozo_id = '$search_text'";
            break;

        case 7:
            $query = "SELECT SQL_CALC_FOUND_ROWS pozos.* FROM pozos LEFT JOIN pozos_trabajos ON pozos_trabajos.pozo_id = pozos.pozo_id WHERE pozos_trabajos.trabajo_id = '$search_text'";
            break;

        default:
            return false;
    }


    $filter_query_process = array();

    foreach ($filters as $filter_name => $filter_value) {
        if (in_array($filter_name, array_keys($filter_query)) && strlen($filter_value) > 0) {
            $filter_query_process[] = sprintf($filter_query[$filter_name], $filter_value);
        }
    }

    if (is_array($filter_query_process) && count($filter_query_process) > 0) {
        $query .= " AND " . implode(" AND ", $filter_query_process);
    }


    $query .= $extra_query . " LIMIT $limit_start, $amount";

    $result = $db->query($query);

    $total = $db->fetch_array($db->query('SELECT FOUND_ROWS() AS count'));

    $list = array();

    while ($row = $db->fetch_array($result)) {

        $row = array_merge($row, array(
            "cliente" => get_customer_current_name($row["cliente_id"]),
            "cliente_nombres" => get_customer_names($row["cliente_id"], 1, true),
            "nombre_sede" => get_headquarter_name($row["sede_id"]),
            "fecha_construccion" => form_date_format($row["fecha_construccion"]),
            "nombre_ciudad" => get_city_name($row["ciudad"]),
            "nombre_estado" => get_state_name($row["estado"]),
            "nombre_municipio" => get_municipality_name($row["municipio"]),
        ));

        $list[] = $row;
    }

    return array($list, $total['count']);


}

function well_list($filters = array())
{

    global $db;

    $query = 'SELECT * FROM pozos';

    $filter_query = array(
        "pozo" => "pozo_id = '%s'",
        "cliente" => "cliente_id = '%s'",
        "sede" => "sede_id = '%s'",
        "municipio" => "municipio = '%s'",
        "estado" => "estado = '%s'",
        "ciudad" => "ciudad = '%s'",
        "diam_min" => "diametro >= '%s'",
        "diam_max" => "diametro <= '%s'",
        "prof_min" => "profundidad >= '%s'",
        "prof_max" => "profundidad <= '%s'",
        "fecha_min" => "fecha_construccion >= '%s'",
        "fecha_max" => "fecha_construccion <= '%s'",
    );

    $filter_query_process = array();

    foreach ($filters as $filter_name => $filter_value) {
        if (in_array($filter_name, array_keys($filter_query)) && strlen($filter_value) > 0) {
            $filter_query_process[] = sprintf($filter_query[$filter_name], $filter_value);
        }
    }

    if (is_array($filter_query_process) && count($filter_query_process) > 0) {
        $query .= " WHERE " . implode(" AND ", $filter_query_process);
    }

    $result = $db->query($query);
    $list = array();

    while ($row = $db->fetch_array($result)) {
        $list[] = $row;
    }

    return $list;
}

function well_info($well_id)
{

    if (empty($well_id)) {
        $well_id = 0;
    }

    $well_data = well_list(array("pozo" => $well_id));
    $well_info = array();

    if (isset($well_data[0])) {

        $well_info = $well_data[0];

        $well_info = array_merge($well_info, array(
            "cliente" => get_customer_current_name($well_info["cliente_id"]),
            "nombre_sede" => get_headquarter_name($well_info["sede_id"]),
            "fecha_construccion" => form_date_format($well_info["fecha_construccion"]),
            "nombre_ciudad" => get_city_name($well_info["ciudad"]),
            "nombre_estado" => get_state_name($well_info["estado"]),
            "nombre_municipio" => get_municipality_name($well_info["municipio"]),
        ));
    }

    return $well_info;
}

function well_count($customer_id)
{

    global $db;

    $query = "SELECT COUNT(*) AS count FROM pozos WHERE cliente_id = '$customer_id'";

    $result = $db->query($query);
    $row = $db->fetch_array($result);

    return isset($row["count"]) ? (int)$row["count"] : 0;
}

function task_count($customer_id, $well_id = 0)
{

    global $db;

    $query = "SELECT COUNT(pozos_trabajos.pozo_id) AS count FROM pozos_trabajos LEFT JOIN pozos ON pozos.pozo_id = pozos_trabajos.pozo_id WHERE pozos.cliente_id = '$customer_id'";

    if (!empty($well_id)) {
        $query .= " AND pozos.pozo_id = '$well_id'";
    }

    $result = $db->query($query);
    $row = $db->fetch_array($result);

    return isset($row["count"]) ? (int)$row["count"] : 0;
}

function task_info($task_id)
{
    global $db;

    $query = "SELECT * FROM pozos_trabajos WHERE trabajo_id = '$task_id' LIMIT 1";
    $result = $db->query($query);

    $task_info = $db->fetch_array($result);
    if ($task_info) {
        $equipment = get_equipment($task_info["trabajo_id"]);

        $extraccion_bomba = isset($equipment[1]) ? $equipment[1] : array();
        $extraccion_motor = array();
        $extraccion_acc = array();

        if ($task_info["bomba_extraida"] && count($extraccion_bomba) > 0) {
            $extraccion_motor = $extraccion_bomba["motor"];
            $extraccion_acc = $extraccion_bomba["accesorios"];
        }


        $instalacion_bomba = isset($equipment[2]) ? $equipment[2] : array();
        $instalacion_motor = array();
        $instalacion_acc = array();
        $datos_arranque = array();

        if ($task_info["bomba_instalada"] && count($instalacion_bomba) > 0) {
            $instalacion_motor = $instalacion_bomba["motor"];
            $instalacion_acc = $instalacion_bomba["accesorios"];
            $datos_arranque = $instalacion_bomba["datos_arranque"];
        }

        $task_info = array_merge($task_info, array(
            "fecha_trabajo" => form_date_format($task_info["fecha_trabajo"]),
            "limpieza_pozo" => is_serialized($task_info["limpieza_pozo"]) ? unserialize64($task_info["limpieza_pozo"]) : array(),
            "prueba_bombeo" => is_serialized($task_info["prueba_bombeo"]) ? unserialize64($task_info["prueba_bombeo"]) : array(),
            'extraccion_bomba' => $extraccion_bomba,
            'extraccion_motor' => $extraccion_motor,
            'extraccion_acc' => $extraccion_acc,
            'instalacion_bomba' => $instalacion_bomba,
            'instalacion_motor' => $instalacion_motor,
            'instalacion_acc' => $instalacion_acc,
            'datos_arranque' => $datos_arranque,
        ));
    }

    return $task_info;
}

function get_well_task_list($well_id)
{
    global $db;

    $query = "SELECT * FROM pozos_trabajos WHERE pozo_id = '$well_id' ORDER BY trabajo_id DESC";
    $result = $db->query($query);

    $list = array();

    while ($task_info = $db->fetch_array($result)) {

        $equipment = get_equipment($task_info["trabajo_id"]);

        $extraccion_bomba = isset($equipment[1]) ? $equipment[1] : array();
        $extraccion_motor = array();
        $extraccion_acc = array();

        if ($task_info["bomba_extraida"] && count($extraccion_bomba) > 0) {
            $extraccion_motor = $extraccion_bomba["motor"];
            $extraccion_acc = $extraccion_bomba["accesorios"];
        }


        $instalacion_bomba = isset($equipment[2]) ? $equipment[2] : array();
        $instalacion_motor = array();
        $instalacion_acc = array();
        $datos_arranque = array();

        if ($task_info["bomba_instalada"] && count($instalacion_bomba) > 0) {
            $instalacion_motor = $instalacion_bomba["motor"];
            $instalacion_acc = $instalacion_bomba["accesorios"];
            $datos_arranque = $instalacion_bomba["datos_arranque"];
        }

        $task_info = array_merge($task_info, array(
            'fecha_trabajo' => form_date_format($task_info["fecha_trabajo"]),
            'limpieza_pozo' => is_serialized($task_info["limpieza_pozo"]) ? unserialize64($task_info["limpieza_pozo"]) : array(),
            'prueba_bombeo' => is_serialized($task_info["prueba_bombeo"]) ? unserialize64($task_info["prueba_bombeo"]) : array(),
            'extraccion_bomba' => $extraccion_bomba,
            'extraccion_motor' => $extraccion_motor,
            'extraccion_acc' => $extraccion_acc,
            'instalacion_bomba' => $instalacion_bomba,
            'instalacion_motor' => $instalacion_motor,
            'instalacion_acc' => $instalacion_acc,
            'datos_arranque' => $datos_arranque,
        ));
        $list[] = $task_info;
    }
    return $list;

}

function get_next_id($table)
{
    global $db;

    $result = $db->query("SHOW TABLE STATUS LIKE '$table'");
    $table_info = $db->fetch_array($result);

    return isset($table_info['Auto_increment']) ? (int)$table_info['Auto_increment'] : 0;
}

function equipment_info($equip_id)
{
    global $db;

    $query = "SELECT * FROM equipamiento WHERE equip_id = '$equip_id' LIMIT 1";

    $result = $db->query($query);
    $equipment_info = $db->fetch_array($result);

    return $equipment_info;

}

function get_themes()
{

    $templates = array_map("basename", glob(TEMPLATE_PATH . '*', GLOB_ONLYDIR));
    natcasesort($templates);

    return $templates;
}

function get_equipment($task_id, $action = 0)
{
    global $db;

    $query = "SELECT * FROM equipamiento WHERE trabajo_id='$task_id'";

    if (!empty($action)) {
        $query .= " AND accion = '$action'";
    }

    $result = $db->query($query);

    $list = array();

    while ($equipment_info = $db->fetch_array($result)) {

        foreach ($equipment_info as $n => $equipment) {
            if (is_serialized($equipment)) {
                $equipment_info[$n] = unserialize64($equipment);
            }
        }

        $list[$equipment_info["accion"]] = $equipment_info;
    }
    return $list;

}

function equipment_type_list()
{
    global $db;

    $query = "SELECT * FROM equipamiento_tipos";

    $result = $db->query($query);
    $list = array();

    while ($row = $db->fetch_array($result)) {
        $list[] = $row;
    }

    return $list;

}

function equipment_type($type)
{
    global $db;

    $query = "SELECT * FROM equipamiento_tipos WHERE equip_tipo_id = '$type' LIMIT 1";
    $result = $db->query($query);

    $equipment_type = $db->fetch_array($result);

    return isset($equipment_type["nombre"]) ? $equipment_type["nombre"] : "-";

}

function customer_search_autocomplete($term)
{

    global $db;

    $query = "SELECT t2.* FROM clientes AS t1, clientes_nombres AS t2 WHERE
	                  t2.razon_social LIKE '%$term%' AND t1.cliente_id = t2.cliente_id";

    $query .= " GROUP BY t2.razon_social LIMIT 6";

    $result = $db->query($query);
    $list = array();

    while ($row = $db->fetch_array($result)) {
        $list[] = array(
            "value" => $row["razon_social"],
        );
    }

    return json_encode($list);

}

function well_search_autocomplete($term, $mode)
{
    global $db;

    $t_alias = '';
    $field = '';
    $query = '';

    switch ($mode) {
        case 1:
            $field = 'nombre';
            $query = "SELECT * FROM pozos WHERE $field LIKE '%$term%'";
            break;

        case 2:
            $t_alias = 't2';
            $field = 'razon_social';
            $query = "SELECT $t_alias.* FROM clientes AS t1, clientes_nombres AS $t_alias WHERE
	                  $t_alias.$field LIKE '%$term%' AND t1.cliente_id = $t_alias.cliente_id";
            break;

        case 3:
            $t_alias = 't2';
            $field = 'nombre';
            $query = "SELECT * FROM clientes_sedes_nombres WHERE $field LIKE '%$term%'";
            break;

        case 4:

            break;
    }

    $group_by = $t_alias ? $t_alias . '.' . $field : $field;
    $query .= " GROUP BY $group_by LIMIT 6";

    $result = $db->query($query);
    $list = array();

    while ($row = $db->fetch_array($result)) {
        $list[] = array(
            "value" => $row[$field],
        );
    }

    return json_encode($list);
}

function made_by_autocomplete($term)
{

    global $db;

    $query = "SELECT realizado_por FROM pozos_trabajos WHERE realizado_por LIKE '%$term%'";
    $query .= " GROUP BY realizado_por LIMIT 6";

    $result = $db->query($query);
    $list = array();

    while ($row = $db->fetch_array($result)) {
        $list[] = array(
            "value" => $row["realizado_por"],
        );
    }

    return json_encode($list);

}

function grua_autocomplete($term)
{

    global $db;

    $query = "SELECT grua_usada FROM pozos_trabajos WHERE grua_usada LIKE '%$term%'";
    $query .= " GROUP BY grua_usada LIMIT 6";

    $result = $db->query($query);
    $list = array();

    while ($row = $db->fetch_array($result)) {
        $list[] = array(
            "value" => $row["grua_usada"],
        );
    }

    return json_encode($list);

}

function getPaginationParams($buttons_per_page, $current_page, $total_pages)
{

    $middle_button = ceil($buttons_per_page / 2);
    $buttons_to_right = $buttons_per_page - $middle_button;
    $buttons_to_left = $buttons_per_page - $buttons_to_right - 1;
    $buttons_offset = $current_page - $buttons_to_left + $buttons_per_page - 1 - $total_pages;

    $first_button = $current_page > $middle_button ? $current_page - $buttons_to_left : 1;

    if ($buttons_offset > 0 && $total_pages > $buttons_per_page) {
        $first_button -= $buttons_offset;
    }

    $last_button = $total_pages > $buttons_per_page ? $first_button + $buttons_per_page - 1 : $total_pages;

    $pagination_url = getUrlWithout(array("page")) . "&page=";
    $disabled_url = "javascript:void(0);";

    if ($current_page > 1) {
        $previous_url = $pagination_url . ($current_page - 1);
    } else {
        $previous_url = $disabled_url;
    }

    if ($current_page < $total_pages) {
        $next_url = $pagination_url . ($current_page + 1);
    } else {
        $next_url = $disabled_url;
    }

    return array(
        "btn_per_page" => $buttons_per_page,
        "first" => $first_button,
        "last" => $last_button,
        "current" => $current_page,
        "previous_url" => $previous_url,
        "next_url" => $next_url,
        "total" => $total_pages,
        "url" => $pagination_url,
    );

}

function get_last_installed_bomb($well_id)
{

    global $db;

    $query = "SELECT * FROM pozos_trabajos WHERE pozo_id = '$well_id' AND bomba_instalada = 1 ORDER BY trabajo_id DESC LIMIT 1";
    $result = $db->query($query);

    $list = array();
    if ($task_info = $db->fetch_array($result)) {
        $equipment = get_equipment($task_info["trabajo_id"]);

        $extraccion_bomba = isset($equipment[1]) ? $equipment[1] : array();
        $extraccion_motor = array();
        $extraccion_acc = array();

        if ($task_info["bomba_extraida"] && count($extraccion_bomba) > 0) {
            $extraccion_motor = $extraccion_bomba["motor"];
            $extraccion_acc = $extraccion_bomba["accesorios"];
        }


        $instalacion_bomba = isset($equipment[2]) ? $equipment[2] : array();
        $instalacion_motor = array();
        $instalacion_acc = array();
        $datos_arranque = array();

        if ($task_info["bomba_instalada"] && count($instalacion_bomba) > 0) {
            $instalacion_motor = $instalacion_bomba["motor"];
            $instalacion_acc = $instalacion_bomba["accesorios"];
            $datos_arranque = $instalacion_bomba["datos_arranque"];
        }

        $list = array(
            'instalacion_bomba' => $instalacion_bomba,
            'instalacion_motor' => $instalacion_motor,
            'instalacion_acc' => $instalacion_acc,
            'datos_arranque' => $datos_arranque,
        );
    }
    return $list;
}

function get_headquarter_names($headquarter_id)
{

    global $db;

    $query = "SELECT s.sede_id, sn.sede_nombre_id, sn.nombre, sn.actual, sn.fecha_asignacion FROM clientes_sedes s
    INNER JOIN clientes_sedes_nombres sn ON sn.sede_id = s.sede_id
    WHERE s.sede_id = '$headquarter_id'";

    $result = $db->query($query);

    $headquarter_names = array();

    while ($headquarter_name = $db->fetch_array($result)) {
        $headquarter_names[] = $headquarter_name;
    }

    return $headquarter_names;
}

/**
 * Rellena el arreglo de la linea CSV a exportar con valores en blanco para completar las columnas del archivo necesarias
 *
 * @param array $csvLine arreglo de datos para la linea csv a exportar
 * @param int $max numero de colunas en blanco a completar
 */
function csvCompletarEspacios(array &$csvLine, $max = 0, $string='')
{
    for ($i = 0; $i < $max; $i++) {
        $csvLine[] = $string;
    }
}

/**
 * Logica para exportar los datos del motor a CSV.
 *
 * @param array $csvLine arreglo de datos para la linea csv a exportar
 * @param array $motor arreglo con los datos del motor
 * @param int $tipoBomba sumergible o turbina
 * @param string $prefijoStr prefijo sX_ del objeto serializado
 */
function exportMotorData(&$csvLine, array $motor, $tipoBomba, $prefijoStr)
{
    /*if(key_exists($prefijoStr . 'tipo_motor', $motor ) && $motor[$prefijoStr . 'tipo_motor'] == 2){
        csvCompletarEspacios($csvLine,10);
    }*/
    foreach ($motor as $key => $value) {
        $fixedKey = preg_replace('/^s\d_(tipo\d_)?/i', '', $key);
        switch ($tipoBomba) {
            case 1:
                //Sumergible
                $csvLine[] = $value;
                break;
            case 2:
                //Turbina
                switch ($motor[$prefijoStr . 'tipo_motor']) {
                    case 1:
                        //Electrico
                        if ($key == $prefijoStr . 'tipo_motor') {
                            //Este indice no intereza en el CSV asi que lo saltamos.
                            continue;
                        }
                        $csvLine[] = $value;
                        break 2;
                    case 2:
                        //Disel
                        if ($key == $prefijoStr . 'tipo_motor') {
                            //Este indice no intereza en el CSV asi que lo saltamos.
                            continue;
                        } elseif (in_array($fixedKey, array('marca_motor', 'hp_motor'))) {
                            $csvLine[] = $value;
                        } else {
                            $csvLine[] = '';
                        }
                        break 2;
                }
                break;
        }
    }
    /*if ($tipoBomba == 1) {
        csvCompletarEspacios($csvLine, 2);
    }*/
}

/**
 * Ubicar los contenidos de accesorios en sus respectivas columnas del CSV
 *
 * @param array $csvLine datos para la linea csv a exportar
 * @param array $acc datos de los accesorios
 * @param int $tipoBomba sumergible o turbina
 */
function exportAccesoriosData(&$csvLine, array $acc, $tipoBomba)
{
    foreach ($acc as $key => $value) {
        $fixedKey = preg_replace('/^s\d_(tipo\d_)?/i', '', $key);
        switch ($tipoBomba) {
            case 1:
                //Sumergible
                $csvLine[] = $value;
                break;
            case 2:
                //Turbina
                if ($fixedKey == 'cable_n_acc') {
                    //Hay que iniciar en otra columna mas adelante
                    csvCompletarEspacios($csvLine, 10);
                } elseif (in_array($fixedKey, array('cant_electrodos_acc'))) {
                    //Este indice no intereza en el CSV asi que lo saltamos.
                    continue;
                }
                $csvLine[] = $value;
                break;
            default:
                break;
        }

    }
}

function floatVeToSQL($number)
{
    return floatval(number_format($number, 1, '.', ''));
}

function floatSQLToVe($number)
{
    return number_format(floatval($number), 1, ',', '');
}

/**
 * @param $csvLine
 * @param $columnaCorrecta
 */
function iniciarEnColumna(&$csvLine, $columnaCorrecta)
{
    $enColumna = count($csvLine);
    if ($enColumna < $columnaCorrecta) {
        //Si no tenemos esta cantidad de columas hay un error en la data por lo que completamos su contenido en blanco
        csvCompletarEspacios($csvLine, $columnaCorrecta - $enColumna - 1);
    }
}


function checkLogin()
{
    if (!isset($_SESSION["account"]) || !isset($_SESSION["logged_in"])) {
        header('Location: login.php');
    }
}
