<?php

require(dirname( __FILE__ ) . '/includes/config.php');

$action = _GET( 'action', 'login' );
$current_mod = basename( __FILE__, '.php' );
echo "test $action";
switch ( $action ) {

    /* INICIO DE SESIÓN  */
    default:
    case 'login':

        if ( isset($_SESSION[$current_mod][$action]) ) {
            $msg->addMsg( $_SESSION[$current_mod][$action], "warning" );
            unset($_SESSION[$current_mod][$action]);
        }

        if ( isset($_SERVER["HTTP_REFERER"]) && basename( $_SERVER["HTTP_REFERER"], '.php' ) != $current_mod ) {
            $_SESSION["login_referral_url"] = $_SERVER["HTTP_REFERER"];
        }

        if ( isset($_SESSION["account"]) && isset($_SESSION["logged_in"]) ) {
            if ( isset($_SESSION["login_referral_url"]) && parse_url( SITE_URL, PHP_URL_HOST ) == parse_url( $_SESSION["login_referral_url"], PHP_URL_HOST ) ) {
                $login_referral_url = $_SESSION["login_referral_url"];
                unset($_SESSION["login_referral_url"]);
                header( 'Location: ' . $login_referral_url );
            } else {
                header( 'Location: index.php' );
            }
        }
        $captcha_rand_id = md5( uniqid() );

        $template_data = array(
            'rand_id' => $captcha_rand_id,
        );

        $template = $twig->loadTemplate( 'login.twig' );

        echo $template->render( templateContext( $template_data ) );
        break;

    /* PETICIÓN DE INICIO DE SESIÓN  */
    case 'login_request':

    echo "esto es login request";
        $txtUser = $_POST['nacionalidad'] . "-" . $_POST['cedula'];
        $txtEmail = $_POST['correo'];
        $txtPass = encrypt_pass( $_POST['clave'] );

        $securimage = new Securimage();
        $_d = new debug();

        if ( empty($_POST["cedula"]) )
            $_d->response( $_d->msg( 0, 'Por favor introduzca su número de cédula de identidad.', "cedula" ) );

        if ( empty($_POST["correo"]) )
            $_d->response( $_d->msg( 0, 'Por favor introduzca su correo electronico.', "correo" ) );

        if ( !$securimage->check( $_POST['ct_captcha'] ) )
            $_d->response( $_d->msg( 0, "El codigo de seguridad ingresado es incorrecto.", 'ct_captcha' ) );

        elseif ( $login = login( $txtUser, $txtEmail, $txtPass ) ) {

            if ( $login["status"] == 1 ) {

                $_SESSION["account"] = $login["cedula"];
                $_SESSION["logged_in"] = $login["user_id"];

                $_d->response( $_d->msg( 1 ) );

            } else {
                $_d->response( $_d->msg( -1, "La cuenta ingresada ha sido desactivada por el administrador." ) );
            }

        } else {
            $_d->response( $_d->msg( 0, "Los datos de ingreso no coinciden / son incorrectos." ) );
        }


        break;

    /* CERRAR SESIÓN  */
    case 'logout':
        session_destroy();
        header( 'Location: login.php' );
        break;

    /* RESTABLECER CONTRASEÑA */
    case 'reset_password':

        $step = _GET( "step", 1 );

        switch ( $step ) {

            default:
                break;

            case 1:
                if ( isset($_SESSION["account"]) && isset($_SESSION["logged_in"]) )
                    header( 'Location: index.php' );

                $captcha_rand_id = md5( uniqid() );

                $template_data = array(
                    'rand_id' => $captcha_rand_id,
                );

                $template = $twig->loadTemplate( 'reset-password.twig' );

                echo $template->render( templateContext( $template_data ) );
                break;

            case 2:

                $token = _GET( "token" );
                $show_form = false;
                $p_data = array();

                if ( $tokeninfo = get_token( $token ) ) {
                    $timediff = timeDiff( date( 'Y-m-d H:i:s' ), $tokeninfo['gen_date'] );

                    if ( empty($token) ) {
                        $msg->addMsg( "Disculpe, este enlace no es v&aacute;lido.", "warning" );
                    } elseif ( $timediff > 30 ) {
                        $msg->addMsg( "Disculpe, este enlace ya no es v&aacute;lido, el codigo token ha expirado.", "warning" );
                        db_delete( "tokens", "WHERE token = '$token'" );
                    } else {
                        $show_form = true;

                        $token_userinfo = userinfo( array( 'user_id' => $tokeninfo["user_id"] ) );

                        if ( !$token_userinfo ) {
                            $show_form = false;
                            $msg->addMsg( "Disculpe, no pudo ser encontrado el usuario a que se hace referencia.", "warning" );
                        } else {
                            $p_data = isset($token_userinfo['informacion_seguridad']) && is_serialized( $token_userinfo['informacion_seguridad'] ) ? unserialize64( $token_userinfo['informacion_seguridad'] ) : array();

                            if ( empty($p_data) || !isset($p_data['pregunta_seguridad']) || empty($p_data['pregunta_seguridad']) || !isset($p_data['respuesta_seguridad']) || empty($p_data['respuesta_seguridad']) ) {
                                $show_form = false;
                                $msg->addMsg( "Disculpe, no pudo ser cargada su información de seguridad. Por favor intente nuevamente, si el problema persiste contacte con un administrador.", "warning" );
                            }
                        }

                    }
                } else {
                    $msg->addMsg( 'Disculpe, este enlace no existe actualmente, es posible que el codigo token haya expirado.', "warning" );
                }

                $template_data = array(
                    "token" => $token,
                    "show_form" => $show_form,
                    "security_info" => $p_data,
                );

                $template = $twig->loadTemplate( 'reset-password-step-2.twig' );

                echo $template->render( templateContext( $template_data ) );
                break;
        }
        break;

    /* PETICIÓN DE RESTABLECER CONTRASEÑA */
    case 'reset_password_request':

        $request_step = _GET( "step" );

        switch ( $request_step ) {

            default:
            case 1:

                $_d = new debug();

                if ( empty($_POST["correo"]) )
                    $_d->response( $_d->msg( 0, 'Por favor introduzca su correo electronico.', "correo" ) );

                $securimage = new Securimage();

                if ( !$securimage->check( $_POST["ct_captcha"] ) )
                    $_d->response( $_d->msg( 0, 'El codigo de seguridad ingresado es incorrecto.', "ct_captcha" ) );

                if ( !$userinfo = userinfo( array( 'email' => $_POST["correo"] ) ) ) {

                    $_d->response( $_d->msg( 0, 'El correo electrónico ingresado no se encuentra asociado a ningún usuario.', "correo" ) );

                } else {

                    if ( email_restablecer( $userinfo ) ) {
                        $_SESSION[$current_mod]["login"] = "Se ha enviado un e-mail con la información necesaria para restablecer su contraseña, por favor revise su bandeja de entrada.";
                        $_d->response( $_d->msg( 1, SITE_URL . "login.php" ) );
                    } else {
                        $_d->response( $_d->msg( 0, "Ha ocurrido un error al enviar el correo electrónico, por favor intente mas tarde." ) );
                    };

                }
                break;

            case 2:

                $max_tries = 6;

                $_d = new debug();

                if ( empty($_POST["respuesta_seguridad"]) )
                    $_d->response( $_d->msg( 0, 'Por favor ingrese su respuesta de seguridad.', "respuesta_seguridad" ) );

                elseif ( empty($_POST["user_pass"]) )
                    $_d->response( $_d->msg( 0, 'Por favor introduzca su nueva contraseña.', "user_pass" ) );

                elseif ( empty($_POST["user_re_pass"]) )
                    $_d->response( $_d->msg( 0, 'Por favor repita su contraseña.', "user_re_pass" ) );

                elseif ( $_POST["user_re_pass"] != $_POST["user_pass"] )
                    $_d->response( $_d->msg( 0, 'Las contraseñas ingresadas no coinciden.', "user_re_pass" ) );

                else {

                    $token = _POST( "token" );

                    if ( $tokeninfo = get_token( $token ) ) {
                        $timediff = timeDiff( date( 'Y-m-d H:i:s' ), $tokeninfo['gen_date'] );
                        if ( empty($token) ) {

                            $_d->response( $_d->msg( 0, "Disculpe, este enlace no es válido." ) );

                        } elseif ( $timediff > 30 ) {
                            db_delete( "tokens", "WHERE token = '$token'" );
                            $_d->response( $_d->msg( 0, "Disculpe, este enlace ya no es válido, el codigo token ha expirado." ) );

                        } elseif ( $tokeninfo['tries'] >= $max_tries ) {
                            db_delete( "tokens", "WHERE token = '$token'" );
                            $_d->response( $_d->msg( 0, "Disculpe, este enlace ya no es válido, ha realizado muchos intentos fallidos." ) );


                        } else {

                            $token_userinfo = userinfo( array( 'user_id' => $tokeninfo["user_id"] ) );

                            if ( !$token_userinfo ) {
                                $_d->response( $_d->msg( 0, "Disculpe, no pudo ser encontrado el usuario a que se hace referencia." ) );
                            } else {
                                $p_data = isset($token_userinfo['informacion_seguridad']) && is_serialized( $token_userinfo['informacion_seguridad'] ) ? unserialize64( $token_userinfo['informacion_seguridad'] ) : array();

                                if ( empty($p_data) || !isset($p_data['pregunta_seguridad']) || empty($p_data['pregunta_seguridad']) || !isset($p_data['respuesta_seguridad']) || empty($p_data['respuesta_seguridad']) ) {
                                    $_d->response( $_d->msg( 0, "Disculpe, no pudo ser cargada su información de seguridad. Por favor intente nuevamente, si el problema persiste contacte con un administrador." ) );
                                }
                                if ( $p_data['respuesta_seguridad'] != $_POST['respuesta_seguridad'] ) {

                                    $current_try = $tokeninfo["tries"] + 1;
                                    $token_data = array(
                                        'tries' => $current_try,
                                    );

                                    db_update( "tokens", $token_data, "WHERE token = '$token'" );

                                    $_d->response( $_d->msg( 0, "La respuesta de seguridad ingresada es incorrecta. (Intento $current_try/$max_tries)", "respuesta_seguridad" ) );

                                } else {
                                    $user_data = array(
                                        'clave' => encrypt_pass( $_POST['user_pass'] ),
                                    );
                                    if ( db_update( "usuarios", $user_data, "WHERE user_id = '" . $tokeninfo["user_id"] . "'" ) ) {
                                        db_delete( "tokens", "WHERE token = '$token'" );

                                        $_SESSION[$current_mod]["login"] = "Ha cambiado su contraseña satisfactoriamente.";
                                        $_d->response( $_d->msg( 1, SITE_URL . "login.php" ) );
                                    } else $_d->response( $_d->msg( 0, "Ha ocurrido un error al actualizar los datos." ) );
                                }
                            }

                        }
                    } else {
                        $_d->response( $_d->msg( 0, 'Disculpe, este enlace no existe actualmente, es posible que el codigo token haya expirado.' ) );
                    }
                }
                break;
        }
        break;


}