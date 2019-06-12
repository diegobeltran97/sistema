<?php

require(dirname( __FILE__ ) . '/includes/config.php');

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$current_mod = basename( __FILE__, '.php' );

switch ( $action ) {

    default:
        die;

    /**
     * LISTADO DE CLIENTES
     */
    case "list":

        if ( isset($_SESSION[$current_mod][$action]) ) {
            $msg->addMsg( $_SESSION[$current_mod][$action], "success" );
            unset($_SESSION[$current_mod][$action]);
        }

        if ( !isset($_SESSION["account"]) || !isset($_SESSION["logged_in"]) )
            header( 'Location: login.php' );

        if ( in_array( "2-1", $current_access ) ) {
            $show_form = true;
        } else {
            $msg->addMsg( PERM_MSG, "warning" );
            $show_form = false;
        }

        $search_text = _GET( "s" );
        $current_page = _GET( "page" ) ? (int)_GET( "page" ) : 1;
        $items_per_page = $options["customers_per_page"];
        list($search_results, $total_items) = customer_search( $search_text, $current_page, $items_per_page );
        $total_pages = ceil( $total_items / $items_per_page );
        $first_item = $items_per_page * ($current_page - 1) + 1;
        $last_item = $current_page != $total_pages ? $items_per_page * $current_page : $total_items;

        $buttons_per_page = 7;

        $paginationParams = getPaginationParams( $buttons_per_page, $current_page, $total_pages );


        if ( !$search_results )
            $msg->addMsg( "No se ha encontrado ningún resultado con sus criterios de búsqueda.", "warning" );

        $template_data = array(
            'search_string'  => $search_text,
            'customer_list'  => $search_results,
            "pagination"     => $paginationParams,
            "items_per_page" => $items_per_page,
            "total_items"    => $total_items,
            "first_item"     => $first_item,
            "last_item"      => $last_item,
            'show_form'      => $show_form,
        );

        $template = $twig->loadTemplate( 'customer-list.twig' );
        echo $template->render( templateContext( $template_data ) );

        break;

    /**
     * CREAR UN NUEVO CLIENTE
     */
    case "create":

        if ( !isset($_SESSION["account"]) || !isset($_SESSION["logged_in"]) )
            header( 'Location: login.php' );

        if ( in_array( "2-2", $current_access ) ) {
            $show_form = true;
        } else {
            $msg->addMsg( PERM_MSG, "warning" );
            $show_form = false;
        }

        $template_data = array(
            'user_access_list' => user_access_list(),
            'show_form'        => $show_form,
        );
        $template = $twig->loadTemplate( 'customer-create.twig' );
        echo $template->render( templateContext( $template_data ) );

        break;

    case "edit":

        if ( !isset($_SESSION["account"]) || !isset($_SESSION["logged_in"]) )
            header( 'Location: login.php' );

        if ( in_array( "2-1", $current_access ) ) {
            $show_form = true;
        } else {
            $msg->addMsg( PERM_MSG, "warning" );
            $show_form = false;
        }

        $customer_id = _GET( "id" );
        $customer_info = customer_info( $customer_id );

        if ( !$customer_info )
            $msg->addMsg( "No se ha encontrado el cliente especificado", "danger" );

        $template_data = array(
            "customer_info"  => $customer_info,
            "customer_names" => get_customer_names( $customer_id ),
            "customer_sedes" => get_sedes( $customer_id ),
            'show_form'      => $show_form,
        );
        $template = $twig->loadTemplate( 'customer-edit.twig' );
        echo $template->render( templateContext( $template_data ) );

        break;

    case "create_request":

        $_d = new debug();

        if ( !in_array( "2-2", $current_access ) )
            $_d->response( $_d->msg( 0, PERM_MSG ) );

        if ( empty($_POST["razon_social"]) )
            $_d->response( $_d->msg( 0, 'Por favor ingrese una razón social.', "razon_social" ) );


        $sedes = array();

        if ( isset($_POST["sede_nombre"]) && is_array( $_POST["sede_nombre"] ) )
            foreach ( $_POST["sede_nombre"] as $k => $sede )
                if ( !empty($sede) ) {
                    $sedes[] = array( "nombre" => $sede, "info" => $_POST["sede_info"][$k] );

                }

        if ( count( $sedes ) < 1 )
            $_d->response( $_d->msg( 0, 'Por favor debe ingresar al menos una sede.', "sede_nombre\[\]" ) );


        if ( db_insert( "clientes", array(
            "reg_date"    => date( 'Y-m-d H:i:s' ),
            "descripcion" => $_POST["descripcion"],
        ) )
        ) {

            $cliente_id = $db->last_insert_id();

            $customer_data = array(
                "cliente_id"   => $cliente_id,
                "razon_social" => $_POST["razon_social"],
                "actual"       => 1,
            );

            if ( db_insert( "clientes_nombres", $customer_data ) ) {

                foreach ( $sedes as $sede ) {
                    db_insert( "clientes_sedes", array(
                        "cliente_id" => $cliente_id,
                        "info"       => $sede["info"],
                    ) );
                    $sede_id = $db->last_insert_id();

                    db_insert( "clientes_sedes_nombres", array(
                        "sede_id" => $sede_id,
                        "nombre"  => $sede["nombre"],
                        "actual"  => 1,
                    ) );
                }

                $_SESSION[$current_mod]['edit'] = '<span class="glyphicon glyphicon-ok"></span> Se ha creado el cliente satisfactoriamente';
                $_d->response( $_d->msg( 1, "customers.php?action=edit&id=$cliente_id" ) );
            }

        }

        break;

    case "add_headquarter_request":

        if ( isset($_GET["id"]) && !empty($_GET["id"]) ) {

            $_d = new debug();

            if ( !in_array( "2-3", $current_access ) )
                $_d->response( $_d->msg( 0, PERM_MSG ) );

            if ( empty($_POST["nombre_sede"]) )
                $_d->response( $_d->msg( 0, 'Por favor ingrese el nombre de la sede.', "nombre_sede" ) );

            $headquarter_data = array(
                "cliente_id" => $_GET["id"],
                "info"       => $_POST["info"],
            );

            if ( db_insert( "clientes_sedes", $headquarter_data ) ) {

                $headquarter_id = $db->last_insert_id();

                if ( db_insert( "clientes_sedes_nombres", array(
                    "sede_id" => $headquarter_id,
                    "nombre"  => $_POST["nombre_sede"],
                    "actual" => 1,
                ) ) ) {
                    $_d->response( $_d->msg( 1 ) );
                }
            }

        }

        break;

    case "sedeinfo_request":

        $headquarter_id = _GET( "id" );
        $headquarter_info = get_headquarter_info( $headquarter_id );

        if ( isset($headquarter_info["nombre"]) )
            print('{HEADQUARTER_NAME: "' . $headquarter_info["nombre"] . '"}');

        if ( isset($headquarter_info["info"]) )
            print('{HEADQUARTER_INFO: "' . $headquarter_info["info"] . '"}');

        die;

        break;

    case "customername_info_request":

        $customer_name_id = _GET( "id" );
        $customer_name_info = customer_name_info( $customer_name_id );

        if ( isset($customer_name_info["razon_social"]) )
            print('{NAME: "' . $customer_name_info["razon_social"] . '"}');

        if ( isset($customer_name_info["fecha_asignacion"]) )
            print('{DATE: "' . $customer_name_info["fecha_asignacion"] . '"}');

        if ( isset($customer_name_info["actual"]) )
            print('{CURRENT: "' . $customer_name_info["actual"] . '"}');

        die;

        break;

    case "edit_headquarter_request":

        $_d = new debug();

        if ( !in_array( "2-3", $current_access ) )
            $_d->response( $_d->msg( 0, PERM_MSG ) );

        $headquarter_id = _GET( "id" );
        $headquarter_info = get_headquarter_info( $headquarter_id );

        if ( !$headquarter_info )
            $_d->response( $_d->msg( 0, "No se encontró información de la sede seleccionada" ) );

        if ( !$headquarter_info )
            $_d->response( $_d->msg( 0, "No se encontró información de la sede seleccionada" ) );

        $headquarter_data = array(
            "info" => _POST( 'info' ),
        );

        $db->beginTransaction();
        if ( db_update( "clientes_sedes", $headquarter_data, "WHERE sede_id = '$headquarter_id'" ) ) {

            if ( $headquarter_info['nombre'] != _POST( 'nombre_sede' ) ) {
                if ( _POST( 'guardar_nombre' ) ) {
                    db_update( "clientes_sedes_nombres", array(
                        "actual" => 0,
                    ), "WHERE sede_id = '$headquarter_id' AND actual = '1'" );

                    db_insert( "clientes_sedes_nombres", array(
                        "sede_id" => $headquarter_id,
                        "nombre"  => _POST( 'nombre_sede' ),
                        "actual"  => 1,
                    ) );
                } else {
                    db_update( "clientes_sedes_nombres", array(
                        "nombre" => _POST( 'nombre_sede' ),
                    ), "WHERE sede_id = '$headquarter_id' AND actual = '1'" );
                }
            }
            $db->commit();
            $_d->response( $_d->msg( 1 ) );
        }
        break;

    case "add_name_request":

        $customer_id = _GET( "id" );
        $_d = new debug();

        if ( !in_array( "2-3", $current_access ) )
            $_d->response( $_d->msg( 0, PERM_MSG ) );

        if ( empty($_POST["razon_social"]) )
            $_d->response( $_d->msg( 0, 'Por favor ingrese la razón social.', "razon_social" ) );

        $customer_data = array(
            "cliente_id"   => $customer_id,
            "razon_social" => $_POST["razon_social"],
        );

        if ( !empty($_POST["fecha_asignacion"]) )
            $customer_data["fecha_asignacion"] = db_date_format( $_POST["fecha_asignacion"] );


        if ( db_insert( "clientes_nombres", $customer_data ) ) {

            $customer_name_id = $db->last_insert_id();

            if ( isset($_POST["actual"]) && $_POST["actual"] == 1 )
                set_current_customer_name( $customer_id, $customer_name_id );


            $_d->response( $_d->msg( 1 ) );
        }
        die;
        break;

    case "edit_name_request":

        $_d = new debug();

        if ( !in_array( "2-3", $current_access ) )
            $_d->response( $_d->msg( 0, PERM_MSG ) );

        if ( empty($_POST["razon_social"]) )
            $_d->response( $_d->msg( 0, 'Por favor ingrese la razón social.', "razon_social" ) );

        $customer_name_id = _GET( "id" );
        $customer_name_info = customer_name_info( $customer_name_id );
        if ( $customer_name_info ) {
            $customer_id = $customer_name_info["cliente_id"];

            $customer_name_data = array(
                "razon_social" => isset($_POST["razon_social"]) ? $_POST["razon_social"] : '',
            );

            if ( !empty($_POST["fecha_asignacion"]) )
                $customer_name_data["fecha_asignacion"] = db_date_format( $_POST["fecha_asignacion"] );

            if ( db_update( "clientes_nombres", $customer_name_data, "WHERE razon_id = '$customer_name_id'" ) ) {

                if ( isset($_POST["actual"]) && $_POST["actual"] == 1 )
                    set_current_customer_name( $customer_id, $customer_name_id );

                $_d->response( $_d->msg( 1 ) );
            }
        }
        break;

    case "set_current_name_request":

        $_d = new debug();

        if ( !in_array( "2-3", $current_access ) )
            $_d->response( $_d->msg( 0, PERM_MSG ) );

        $customer_name_id = _GET( "id" );
        $customer_name_info = customer_name_info( $customer_name_id );

        if ( $customer_name_info ) {
            $customer_id = $customer_name_info["cliente_id"];
            if ( set_current_customer_name( $customer_id, $customer_name_id ) )
                $_d->response( $_d->msg( 1 ) );
        }
        break;

    case "delete_request":

        $_d = new debug();

        if ( !in_array( "2-4", $current_access ) )
            $_d->response( $_d->msg( 0, PERM_MSG ) );

        $customerid = _POST( "customerid" );


        if ( !empty($customerid) ) {
            $db->query( "DELETE FROM clientes WHERE cliente_id = '$customerid'" );
            $_d->response( $_d->msg( 1 ) );
        }

        break;

    case "delete_customername_request":

        $_d = new debug();

        if ( !in_array( "2-3", $current_access ) )
            $_d->response( $_d->msg( 0, PERM_MSG ) );

        $customer_name_id = _GET( "id" );

        if ( db_delete( "clientes_nombres", "WHERE razon_id = '$customer_name_id'" ) )
            $_d->response( $_d->msg( 1 ) );

        break;

    case "delete_headquarter_request":

        $_d = new debug();

        if ( !in_array( "2-3", $current_access ) )
            $_d->response( $_d->msg( 0, PERM_MSG ) );

        $headquarter_id = _GET( "id" );
        $db->beginTransaction();

        if ( db_delete( "clientes_sedes", "WHERE sede_id = '$headquarter_id'" ) ) {
            if ( db_delete( "clientes_sedes_nombres", "WHERE sede_id = '$headquarter_id'" ) ) {
                $db->commit();
                $_d->response( $_d->msg( 1 ) );
            }
        }

        break;

    case "set_headquarter_name_request":

        $_d = new debug();

        if ( !in_array( "2-3", $current_access ) )
            $_d->response( $_d->msg( 0, PERM_MSG ) );

        $headquarter_name_id = _GET( "id" );
        $headquarter_name_info = headquarter_name_info( $headquarter_name_id );

        if ( $headquarter_name_info ) {
            $headquarter_id = $headquarter_name_info["sede_id"];
            if ( set_current_headquarter_name( $headquarter_id, $headquarter_name_id ) )
                $_d->response( $_d->msg( 1 ) );
        }
        break;

    case "delete_headquarter_name_request":

        $_d = new debug();

        if ( !in_array( "2-3", $current_access ) )
            $_d->response( $_d->msg( 0, PERM_MSG ) );

        $headquarter_name_id = _GET( "id" );

        if ( db_delete( "clientes_sedes_nombres", "WHERE sede_nombre_id = '$headquarter_name_id'" ) )
            $_d->response( $_d->msg( 1 ) );

        break;

    case "move_headquarer_request":

        $_d = new debug();

        if ( !in_array( "2-3", $current_access ) )
            $_d->response( $_d->msg( 0, PERM_MSG ) );

        $headquarter_id = _POST( 'id' );

        if ( !$headquarter_id )
            $_d->response( $_d->msg( 0, 'Por favor seleccione una sede.' ) );

        $headquarter_info = get_headquarter_info( $headquarter_id );

        if ( !$headquarter_info )
            $_d->response( $_d->msg( 0, 'No se ha encontrado la sede seleccionada.' ) );

        $customer_id = 0;

        if ( !_POST( 'type' ) ) {
            if ( !_POST( "razon_social" ) )
                $_d->response( $_d->msg( 0, 'Por favor ingrese una razón social.', "razon_social" ) );

            if ( db_insert( "clientes", array( "reg_date" => date( 'Y-m-d H:i:s' ) ) ) ) {

                $customer_id = $db->last_insert_id();
                $customer_data = array(
                    "cliente_id"   => $customer_id,
                    "razon_social" => _POST( "razon_social" ),
                    "actual"       => 1,
                );

                db_insert( "clientes_nombres", $customer_data );
            }

        } else {
            $customer_id = _POST( 'cliente' );
            if ( !$customer_id )
                $_d->response( $_d->msg( 0, 'Por favor seleccione un cliente.', "cliente" ) );
        }

        if ( $customer_id ) {
            db_update( "clientes_sedes", array(
                "cliente_id" => $customer_id,
            ), "WHERE sede_id = '$headquarter_id'" );
            db_update( "pozos", array(
                "cliente_id" => $customer_id,
            ), "WHERE sede_id = '$headquarter_id'" );
            $_d->response( $_d->msg( 1, "customers.php?action=edit&id=$customer_id" ) );
        }

        break;

    case "headquarter-names-request":

        $_d = new debug();

        if ( !in_array( "2-3", $current_access ) )
            $_d->response( $_d->msg( 0, PERM_MSG ) );

        $headquarter_id = _GET( 'id' );
        $headquarter_names = get_headquarter_names( $headquarter_id );

        foreach ( $headquarter_names as $headquarter_name ) {
            ?>
            <tr<?php if ( $headquarter_name["actual"] == 1 ) echo ' class="info"' ?>>
                <td>
                    <?php echo $headquarter_name["nombre"] ?>
                </td>
                <td width="25%">
                    <a class="btn btn-info btn-list act-headquartername-set"
                       data-headquarterid="<?php echo $headquarter_name["sede_nombre_id"] ?>" data-toggle="tooltip"
                        <?php if ( $headquarter_name["actual"] == 1 ) echo ' disabled' ?>
                       title="Establecer como Actual">
                        <i class="fa fa-check"></i>
                    </a>
                    <a class="btn btn-danger btn-list act-headquartername-delete"
                       data-headquarterid="<?php echo $headquarter_name["sede_nombre_id"] ?>" data-toggle="tooltip"
                        <?php if ( $headquarter_name["actual"] == 1 ) echo ' disabled' ?>
                       title="Eliminar">
                        <i class="fa fa-trash-o"></i>
                    </a>
                </td>
            </tr>
            <?php
        }

        break;
}

?>