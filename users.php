<?php

require(dirname( __FILE__ ) . '/includes/config.php');

$action = _GET( 'action', 'list' );
$current_mod = basename( __FILE__, '.php' );

switch ( $action ) {

    default:
        /**
         * LISTADO DE USUARIOS
         */
    case "list":

        if ( isset($_SESSION[$current_mod][$action]) ) {
            $msg->addMsg( $_SESSION[$current_mod][$action], "success" );
            unset($_SESSION[$current_mod][$action]);
        }

        if ( !isset($_SESSION["account"]) || !isset($_SESSION["logged_in"]) )
            header( 'Location: login.php' );

        if ( in_array( "3-1", $current_access ) ) {
            $show_form = true;
        } else {
            $msg->addMsg( PERM_MSG, "warning" );
            $show_form = false;
        }

        $search_text = _GET( "s" );
        $current_page = _GET( "page" ) ? (int)_GET( "page" ) : 1;
        $items_per_page = $options["users_per_page"];
        $total_items = $search_results = user_search( $search_text, $current_page, $items_per_page, true );
        $total_pages = ceil( $total_items / $items_per_page );
        $first_item = $items_per_page * ($current_page - 1) + 1;
        $last_item = $current_page != $total_pages ? $items_per_page * $current_page : $total_items;

        $buttons_per_page = 7;

        $paginationParams = getPaginationParams( $buttons_per_page, $current_page, $total_pages );

        $search_results = user_search( $search_text, $current_page, $items_per_page );

        if ( !$search_results )
            $msg->addMsg( "No se ha encontrado ningún resultado con sus criterios de búsqueda.", "warning" );

        $template_data = array(
            'search_string' => $search_text,
            'user_list' => $search_results,
            "pagination" => $paginationParams,
            "items_per_page" => $items_per_page,
            "total_items" => $total_items,
            "first_item" => $first_item,
            "last_item" => $last_item,
            "show_form" => $show_form,
        );

        $template = $twig->loadTemplate( 'user-list.twig' );
        echo $template->render( templateContext( $template_data ) );

        break;

    /**
     * CREAR UN NUEVO USUARIO
     */
    case "create":

        if ( !isset($_SESSION["account"]) || !isset($_SESSION["logged_in"]) )
            header( 'Location: login.php' );

        if ( in_array( "3-2", $current_access ) ) {
            $show_form = true;
        } else {
            $msg->addMsg( PERM_MSG, "warning" );
            $show_form = false;
        }

        $template_data = array(
            'user_access_list' => user_access_list(),
            'show_form' => $show_form,
        );
        $template = $twig->loadTemplate( 'user-create.twig' );
        echo $template->render( templateContext( $template_data ) );

        break;

    case "delete_request":

        $_d = new debug();

        if ( !in_array( "3-4", $current_access ) )
            $_d->response( $_d->msg( 0, PERM_MSG ) );

        $userid = _POST( "userid" );

        if ( isset($current_userinfo["user_id"]) && $userid == $current_userinfo["user_id"] )
            $_d->response( $_d->msg( 0, "Disculpe, no es posible eliminar la cuenta donde se encuentra actualmente." ) );

        if ( !empty($userid) )
            if ( $db->query( "DELETE FROM usuarios WHERE user_id = '$userid'" ) )
                $_d->response( $_d->msg( 1 ) );

        break;

    case "create_request":

        $_d = new debug();

        if ( !in_array( "3-2", $current_access ) )
            $_d->response( $_d->msg( 0, PERM_MSG ) );

        global $adm_access;

        if ( empty($_POST["user_ci"]) )
            $_d->response( $_d->msg( 0, 'Por favor ingrese un numero de cédula de identidad.', "user_ci" ) );

        if ( user_list( $_POST["user_ci"], "cedula" ) )
            $_d->response( $_d->msg( 0, 'El numero de cédula de identidad ya está siendo utilizado por otro usuario', "user_ci" ) );

        if ( empty($_POST["user_email"]) )
            $_d->response( $_d->msg( 0, 'Por favor ingrese una dirección de correo.', "user_email" ) );

        if ( user_list( $_POST["user_email"], "email" ) )
            $_d->response( $_d->msg( 0, 'El correo ingresado ya está siendo utilizado por otro usuario', "adm_email" ) );

        if ( empty($_POST["pregunta_seguridad"]) )
            $_d->response( $_d->msg( 0, 'Por favor introduzca una pregunta de seguridad.', "pregunta_seguridad" ) );

        if ( strlen( $_POST["pregunta_seguridad"] ) < 4 )
            $_d->response( $_d->msg( 0, 'La pregunta de seguridad debe tener 4 caracteres como mínimo.', "pregunta_seguridad" ) );

        if ( empty($_POST["respuesta_seguridad"]) )
            $_d->response( $_d->msg( 0, 'Por favor introduzca una respuesta de seguridad.', "respuesta_seguridad" ) );

        if ( strlen( $_POST["respuesta_seguridad"] ) < 4 )
            $_d->response( $_d->msg( 0, 'La respuesta de seguridad debe tener 4 caracteres como mínimo.', "respuesta_seguridad" ) );

        if ( empty($_POST["user_pass"]) )
            $_d->response( $_d->msg( 0, 'Por favor introduzca su contraseña.', "user_pass" ) );

        if ( !empty($_POST["user_pass"]) && empty($_POST["user_re_pass"]) )
            $_d->response( $_d->msg( 0, 'Por favor repita su contraseña.', "user_re_pass" ) );

        if ( $_POST["user_pass"] != $_POST["user_re_pass"] )
            $_d->response( $_d->msg( 0, 'Las contraseñas ingresadas no coinciden.', "user_re_pass" ) );


        $access = isset($_POST["user_access"]) && is_array( $_POST["user_access"] ) ? serialize64( $_POST["user_access"] ) : "";

        $user_data = array(
            "nombre" => $_POST["user_firstname"],
            "apellidos" => $_POST["user_lastname"],
            "cedula" => $_POST["nacionalidad"] . "-" . $_POST["user_ci"],
            "clave" => encrypt_pass( $_POST["user_pass"] ),
            "email" => $_POST["user_email"],
            "access" => $access,
            "reg_date" => date( 'Y-m-d H:i:s' ),
            "informacion_seguridad" => serialize64( array(
                "pregunta_seguridad" => $_POST["pregunta_seguridad"],
                "respuesta_seguridad" => $_POST["respuesta_seguridad"],
            ) ),
            "status" => 1,
        );

        if ( db_insert( "usuarios", $user_data ) ) {
            $_SESSION[$current_mod]['list'] = "Se ha creado el usuario satisfactoriamente";
            $_d->response( $_d->msg( 1, "users.php" ) );
        }

        break;

    case "userinfo_request":

        if ( isset($_GET["id"]) && is_numeric( $_GET["id"] ) ) {
            $user = user_list( $_GET["id"] );

            if ( isset($user["nombre"]) )
                print('{USER_FIRSTNAME: "' . $user["nombre"] . '"}');

            if ( isset($user["apellidos"]) )
                print('{USER_LASTNAME: "' . $user["apellidos"] . '"}');

            if ( isset($user["email"]) )
                print('{USER_EMAIL: "' . $user["email"] . '"}');

            if ( isset($user["cedula"]) )
                print('{USER_CI: "' . $user["cedula"] . '"}');

            print('{USER_ACCESS: "');


            $access = user_access_list();

            print("<table class='table'>
                                <tr>
                                    <th width='40%'></th>
                                    <th width='15%'>
                                        <a id='v_user_access' href='#'><input type='hidden'/>Ver</a></th>
            <th width='15%'>
                                        <a id='c_user_access' href='#'><input type='hidden'/>Crear</a></th>
                                    <th width='15%'>
                                        <a id='m_user_access' href='#'><input type='hidden'/>Modificar</a></th>
                                    <th width='15%'>
                                        <a id='e_user_access' href='#'><input type='hidden'/>Eliminar</a></th>
                                </tr>");

            foreach ( $access as $n => $acc ) {

                print("<tr>

                <td>$acc</td>");

                if ( in_array( $n . '-1', $user["access"] ) ) $checked = " checked"; else $checked = "";
                print("<td><input name='user_access[]' id='user_access-{$n}-1' type='checkbox' value='{$n}-1'$checked></td>");

                if ( in_array( $n . '-2', $user["access"] ) ) $checked = " checked"; else $checked = "";
                print("<td><input name='user_access[]' id='user_access-{$n}-2' type='checkbox' value='{$n}-2'$checked></td>");

                if ( in_array( $n . '-3', $user["access"] ) ) $checked = " checked"; else $checked = "";
                print("<td><input name='user_access[]' id='user_access-{$n}-3' type='checkbox' value='{$n}-3'$checked></td>");

                if ( in_array( $n . '-4', $user["access"] ) ) $checked = " checked"; else $checked = "";
                print("<td><input name='user_access[]' id='user_access-{$n}-4' type='checkbox' value='{$n}-4'$checked></td>");

                print("</tr>");
            }
            print('</table>"}');

            $informacion_seguridad = isset($user["informacion_seguridad"]) && is_serialized( $user["informacion_seguridad"] ) ? unserialize64( $user["informacion_seguridad"] ) : '';

            if ( isset($informacion_seguridad["pregunta_seguridad"]) )
                print('{USER_PS: "' . $informacion_seguridad["pregunta_seguridad"] . '"}');

            if ( isset($informacion_seguridad["respuesta_seguridad"]) )
                print('{USER_RS: "' . $informacion_seguridad["respuesta_seguridad"] . '"}');

        }
        die;

        break;

    case "edit_request":

        $_d = new debug();

        $edit_id = $_GET["id"];

        if ( $edit_id != $current_userinfo["user_id"] && !in_array( "3-3", $current_access ) )
            $_d->response( $_d->msg( 0, PERM_MSG ) );

        $check_mail = user_list( $_POST["user_email"], "email" );

        if ( empty($_POST["user_email"]) )
            $_d->response( $_d->msg( 0, 'Por favor ingrese una dirección de correo.', "user_email" ) );

        if ( $check_mail && $check_mail["user_id"] != $_GET["id"] )
            $_d->response( $_d->msg( 0, 'El correo ingresado ya está siendo utilizado por otro usuario', "user_email" ) );

        if ( empty($_POST["pregunta_seguridad"]) )
            $_d->response( $_d->msg( 0, 'Por favor introduzca una pregunta de seguridad.', "pregunta_seguridad" ) );

        if ( strlen( $_POST["pregunta_seguridad"] ) < 4 )
            $_d->response( $_d->msg( 0, 'La pregunta de seguridad debe tener 4 caracteres como mínimo.', "pregunta_seguridad" ) );

        if ( empty($_POST["respuesta_seguridad"]) )
            $_d->response( $_d->msg( 0, 'Por favor introduzca una respuesta de seguridad.', "respuesta_seguridad" ) );

        if ( strlen( $_POST["respuesta_seguridad"] ) < 4 )
            $_d->response( $_d->msg( 0, 'La respuesta de seguridad debe tener 4 caracteres como mínimo.', "respuesta_seguridad" ) );

        if ( !empty($_POST["user_pass"]) && empty($_POST["user_re_pass"]) )
            $_d->response( $_d->msg( 0, 'Por favor repita su contraseña.', "user_re_pass" ) );

        if ( $_POST["user_re_pass"] != $_POST["user_pass"] )
            $_d->response( $_d->msg( 0, 'Las contraseñas ingresadas no coinciden.', "user_re_pass" ) );

        else {

            $user_data = array(
                "nombre" => $_POST["user_firstname"],
                "apellidos" => $_POST["user_lastname"],
                "email" => $_POST["user_email"],
                "informacion_seguridad" => serialize64( array(
                    "pregunta_seguridad" => $_POST["pregunta_seguridad"],
                    "respuesta_seguridad" => $_POST["respuesta_seguridad"],
                ) ),
            );

            if ( isset($_POST["user_access"]) && is_array( $_POST["user_access"] ) && in_array( "3-3", $current_access ) )
                $user_data["access"] = serialize64( $_POST["user_access"] );

            if ( !empty($_POST["user_re_pass"]) && !empty($_POST["user_pass"]) && $_POST["user_re_pass"] == $_POST["user_pass"] )
                $user_data["clave"] = encrypt_pass( $_POST["user_pass"] );

            if ( db_update( "usuarios", $user_data, "WHERE user_id = '" . $edit_id . "'" ) ) {
                $location_url = '';

                if ( isset($user_data["clave"]) ) {
                    session_destroy();
                    $location_url = "login.php";
                    $_SESSION["login"]["login"] = "Su contrase&ntilde;a ha sido cambiada exitosamente.";
                }
                $_d->response( $_d->msg( 1, $location_url ) );

            }
        }

        break;

}