<?php

require(dirname(__FILE__) . '/includes/config.php');

$action = _GET("action");

switch ($action) {

    default:
        header("Location: " . SITE_URL . "wells.php?action=search");
        die;

    case "create":

        checkLogin();


        if (in_array("0-2", $current_access)) {
            $show_form = true;
        } else {
            $msg->addMsg(PERM_MSG, "warning");
            $show_form = false;
        }

        $material_list = array(
            array('text' => 'Metal', 'value' => 'Metal'),
            array('text' => 'PVC', 'value' => 'PVC'),
            array('text' => 'Otro', 'value' => 'Otro'),
        );

        $template_data = array(
            'customer_list' => customer_list(),
            'estados_list' => get_dir_list("estados", "Seleccione un Estado..."),
            'municipios_list' => get_dir_list("municipios", "Seleccione un Municipio...", 0),
            'ciudades_list' => get_dir_list("ciudades", "Seleccione una Ciudad...", 0),
            'material_list' => $material_list,
            'show_form' => $show_form,
        );

        $template = $twig->loadTemplate('well-create.twig');
        echo $template->render(templateContext($template_data));

        break;

    case "edit":

        checkLogin();

        if (in_array("0-3", $current_access)) {
            $show_form = true;
        } else {
            $msg->addMsg(PERM_MSG, "warning");
            $show_form = false;
        }

        $well_id = _GET("id");
        $well_info = well_info($well_id);

        if (!$well_info) {
            $msg->addMsg("No se ha encontrado el pozo especificado", "danger");
        }

        $ubicacion_filter = isset($well_info["estado"]) ? $well_info["estado"] : 0;

        $material_list = array(
            array('text' => 'Metal', 'value' => 'Metal'),
            array('text' => 'PVC', 'value' => 'PVC'),
            array('text' => 'Otro', 'value' => 'Otro'),
        );

        $customer_id = isset($well_info["cliente_id"]) ? $well_info["cliente_id"] : '';

        $template_data = array(
            'well_info' => $well_info,
            'customer_list' => customer_list(),
            'sede_list' => get_sedes($customer_id),
            'estados_list' => get_dir_list("estados", "Seleccione un Estado..."),
            'municipios_list' => get_dir_list("municipios", "Seleccione un Municipio...", $ubicacion_filter),
            'ciudades_list' => get_dir_list("ciudades", "Seleccione una Ciudad...", $ubicacion_filter),
            'material_list' => $material_list,
            'edit_mode' => true,
            'show_form' => $show_form,
        );
        $template = $twig->loadTemplate('well-create.twig');
        echo $template->render(templateContext($template_data));

        break;

    case "search":

        checkLogin();

        if (in_array("0-1", $current_access)) {
            $show_form = true;
        } else {
            $msg->addMsg(PERM_MSG, "warning");
            $show_form = false;
        }

        $search_mode = array(
            array('text' => 'Nombre de Cliente', 'value' => '2', 'selected' => ''),
            array('text' => 'Nombre de Pozo', 'value' => '1', 'selected' => ''),
            array('text' => 'Nombre de Sede', 'value' => '3', 'selected' => ''),
            array('text' => 'Nro. de Cliente', 'value' => '4', 'selected' => ''),
            array('text' => 'Nro. de Trabajo', 'value' => '7', 'selected' => ''),
            array('text' => 'Nro. de Sede', 'value' => '5', 'selected' => ''),
        );

        $search_string = htmlspecialchars(stripslashes(_GET("s")));

        foreach ($search_mode as $k => $mode) {
            if (_GET("mode") == $mode['value']) {
                $search_mode[$k]['selected'] = ' selected';
            }
        }

        $template_data = array(
            'search_string' => $search_string,
            'searchmode_list' => $search_mode,
            'estados_list' => get_dir_list("estados", "Todos...", "", _GET("estado")),
            'municipios_list' => get_dir_list("municipios", "Todos...", _GET("estado"), _GET("municipio")),
            'ciudades_list' => get_dir_list("ciudades", "Todos...", _GET("estado"), _GET("ciudad")),
            'show_form' => $show_form,
        );

        if (isset($_GET['s'])) {

            $status_trabajos = '';

            if (_GET('status_trabajos')) {

                switch ($_GET["status_trabajos"]) {
                    case "En Proceso":
                        $status_trabajos = 0;
                        break;
                    case "Finalizados":
                        $status_trabajos = 1;
                        break;
                }

            }

            $filters = array(
                "municipio" => _GET('municipio'),
                "estado" => _GET('estado'),
                "ciudad" => _GET('ciudad'),
                "diam_min" => _GET('diam_min'),
                "diam_max" => _GET('diam_max'),
                "prof_min" => _GET('prof_min'),
                "prof_max" => _GET('prof_max'),
                "fecha_min" => _GET('fecha_min'),
                "fecha_max" => _GET('fecha_max'),
                "status_trabajos" => $status_trabajos,
            );


            $current_page = _GET("page") ? (int)_GET("page") : 1;
            $items_per_page = $options["wells_per_page"];
            list($search_results, $total_items) = well_search($_GET['mode'], $_GET['s'], $filters, $current_page,
                $items_per_page);
            $total_pages = ceil($total_items / $items_per_page);
            $first_item = $items_per_page * ($current_page - 1) + 1;
            $last_item = $current_page != $total_pages ? $items_per_page * $current_page : $total_items;

            $buttons_per_page = 7;

            $paginationParams = getPaginationParams($buttons_per_page, $current_page, $total_pages);

            if (!$search_results) {
                $msg->addMsg("No se ha encontrado ningún resultado con sus criterios de búsqueda.", "warning");
            }

            $template_data = array_merge(array(
                "search_results" => $search_results,
                "pagination" => $paginationParams,
                "items_per_page" => $items_per_page,
                "total_items" => $total_items,
                "first_item" => $first_item,
                "last_item" => $last_item,
            ), $template_data);


        }

        $template = $twig->loadTemplate('well-search.twig');
        echo $template->render(templateContext($template_data));

        break;

    case "details":

        checkLogin();

        if (in_array("0-1", $current_access)) {
            $show_well = true;
        } else {
            $msg->addMsg(PERM_MSG, "warning");
            $show_well = false;
        }

        if (in_array("1-1", $current_access)) {
            $show_task = true;
        } else {
            $show_task = false;
        }

        $well_id = _GET("id");
        $well_info = well_info($well_id);
        $task_list = get_well_task_list($well_id);

        $customer_id = isset($well_info["cliente_id"]) ? $well_info["cliente_id"] : "";

        if (is_array($task_list) && count($task_list) > 0) {
            $default_task = _GET("task") ? _GET("task") : $task_list[0]["trabajo_id"];
        } else {
            $default_task = 0;
        }

        if (!$well_info) {
            $msg->addMsg("No se ha encontrado el pozo especificado", "danger");
        }

        $template_data = array(
            "well_info" => $well_info,
            "task_list" => $task_list,
            "customer_names" => get_customer_names($customer_id, 1, true),
            "default_task" => $default_task,
            'show_well' => $show_well,
            'show_task' => $show_task,
        );

        $template = $twig->loadTemplate('well-details.twig');
        echo $template->render(templateContext($template_data));

        break;

    case "print_task":

        checkLogin();

        if (in_array("0-1", $current_access)) {
            $show_form = true;
        } else {
            $msg->addMsg(PERM_MSG, "warning");
            $show_form = false;
        }

        $task_id = _GET("id");
        $task_info = task_info($task_id);
        $well_id = isset($task_info["pozo_id"]) ? $task_info["pozo_id"] : 0;
        $well_info = well_info($well_id);

        if (!$well_info) {
            $msg->addMsg("No se ha encontrado el pozo especificado", "danger");
        }

        $template_data = array(
            "well_info" => $well_info,
            "task_info" => $task_info,
            'show_form' => $show_form,
        );
/*
        echo '<pre>';
        print_r($template_data);
        echo '</pre>';
        die();*/

        $template = $twig->loadTemplate('well-print.twig');
        echo $template->render(templateContext($template_data));

        break;

    case "new_task":

        checkLogin();

        if (in_array("1-2", $current_access)) {
            $show_form = true;
        } else {
            $msg->addMsg(PERM_MSG, "warning");
            $show_form = false;
        }

        $well_id = _GET("well");
        $well_info = well_info($well_id);
        $customer_id = isset($well_info["cliente_id"]) ? $well_info["cliente_id"] : 0;
        $sede_id = isset($well_info["sede_id"]) ? $well_info["sede_id"] : 0;

        $long_tubos_list = array(
            array("text" => "3.05", "value" => "3.05"),
            array("text" => "3.20", "value" => "3.20"),
            array("text" => "6.40", "value" => "6.40"),
        );

        $status_list = array(
            array("text" => "En Proceso", "value" => 0),
            array("text" => "Finalizado", "value" => 1),
        );


        $template_data = array(
            "task_id" => get_next_id('pozos_trabajos'),
            'customer_list' => customer_list(),
            'sede_list' => get_sedes($customer_id, true),
            'well_list' => well_list(array("cliente" => $customer_id, "sede" => $sede_id)),
            'well_info' => $well_info,
            'task_info' => array(),
            'tipos_bombas' => equipment_type_list(),
            'long_tubos_list' => $long_tubos_list,
            'status_list' => $status_list,
            'current_date' => date('d/m/Y'),
            'show_form' => $show_form,
        );

        $template = $twig->loadTemplate('well-new-task.twig');
        echo $template->render(templateContext($template_data));

        break;

    case "edit_task":

        checkLogin();

        if (in_array("1-3", $current_access)) {
            $show_form = true;
        } else {
            $msg->addMsg(PERM_MSG, "warning");
            $show_form = false;
        }

        $task_id = _GET("id");
        $task_info = task_info($task_id);

        $well_id = isset($task_info["pozo_id"]) ? $task_info["pozo_id"] : "";
        $well_info = well_info($well_id);
        $limpieza_pozo = isset($task_info["limpieza_pozo"]) ? $task_info["limpieza_pozo"] : array();
        $prueba_bombeo = isset($task_info["prueba_bombeo"]) ? $task_info["prueba_bombeo"] : array();
        $customer_id = isset($well_info["cliente_id"]) ? $well_info["cliente_id"] : "";
        $sede_id = isset($well_info["sede_id"]) ? $well_info["sede_id"] : "";

        if (!$task_info) {
            $msg->addMsg("No se ha encontrado el trabajo especificado", "danger");
        }

        $long_tubos_list = array(
            array("text" => "3.05", "value" => "3.05"),
            array("text" => "3.20", "value" => "3.20"),
            array("text" => "6.40", "value" => "6.40"),
        );

        $status_list = array(
            array("text" => "En Proceso", "value" => 0),
            array("text" => "Finalizado", "value" => 1),
        );

        $equipment = get_equipment($task_id);

        $extraccion_bomba = isset($equipment[1]) ? $equipment[1] : array();
        $extraccion_motor = array();
        $extraccion_acc = array();

        if (count($task_info) > 0 && $task_info["bomba_extraida"] && count($extraccion_bomba) > 0) {
            $extraccion_motor = $extraccion_bomba["motor"];
            $extraccion_acc = $extraccion_bomba["accesorios"];
        }

        $instalacion_bomba = isset($equipment[2]) ? $equipment[2] : array();
        $instalacion_motor = array();
        $instalacion_acc = array();
        $datos_arranque = array();

        if (count($task_info) > 0 && $task_info["bomba_instalada"] && count($instalacion_bomba) > 0) {
            $instalacion_motor = $instalacion_bomba["motor"];
            $instalacion_acc = $instalacion_bomba["accesorios"];
            $datos_arranque = $instalacion_bomba["datos_arranque"];
        }

        $template_data = array(
            "task_id" => $task_id,
            'task_info' => $task_info,
            'well_info' => $well_info,
            'limpieza_pozo' => $limpieza_pozo,
            'prueba_bombeo' => $prueba_bombeo,
            'extraccion_bomba' => $extraccion_bomba,
            'extraccion_motor' => $extraccion_motor,
            'extraccion_acc' => $extraccion_acc,
            'instalacion_bomba' => $instalacion_bomba,
            'instalacion_motor' => $instalacion_motor,
            'instalacion_acc' => $instalacion_acc,
            'datos_arranque' => $datos_arranque,
            'customer_id' => $customer_id,
            'sede_id' => $sede_id,
            'customer_list' => customer_list(),
            'sede_list' => get_sedes($customer_id, true),
            'well_list' => well_list(array("cliente" => $customer_id, "sede" => $sede_id)),
            'tipos_bombas' => equipment_type_list(),
            'long_tubos_list' => $long_tubos_list,
            'status_list' => $status_list,
            'edit_mode' => true,
            'show_form' => $show_form,
        );

        $template = $twig->loadTemplate('well-new-task.twig');
        echo $template->render(templateContext($template_data));

        break;

    case "edit_request":
    case "create_request":

        $_d = new debug();

        if ($action == "create_request") {
            $customer_id = $_POST["cliente"];
            $headquarter_id = $_POST["sede"];
        } else {
            if (!$well_info = well_info(_GET("id"))) {
                $_d->response($_d->msg(0, 'No se pudo encontrar información del pozo seleccionado.'));
            }
            $customer_id = $well_info["cliente_id"];
            $headquarter_id = $well_info["sede_id"];
        }

        if ($action == "create_request" && empty($_POST["cliente"])) {
            $_d->response($_d->msg(0, 'Por favor seleccione un cliente.', "cliente"));
        }

        if ($action == "create_request" && empty($_POST["sede"])) {
            $_d->response($_d->msg(0, 'Por favor seleccione una sede.', "sede"));
        }

        if (empty($_POST["nombre"])) {
            $_d->response($_d->msg(0, 'Por favor ingrese el nombre del pozo.', "nombre"));
        }


        if ($check_well_name = get_well_info_by_name($customer_id, $headquarter_id, $_POST["nombre"])) {
            if (!_GET("id") || $headquarter_id != $check_well_name["sede_id"]) {
                $_d->response($_d->msg(0, 'Ya existe otro pozo en la sede con el mismo nombre', "nombre"));
            }
        }

        $well_data = array(
            "nombre" => $_POST["nombre"],
            "fecha_construccion" => db_date_format($_POST["fecha_construccion"]),
            "estado" => $_POST["estado"],
            "municipio" => $_POST["municipio"],
            "ciudad" => $_POST["ciudad"],
            "direccion" => $_POST["direccion"],
            "coord_n" => $_POST["coord_n"],
            "coord_e" => $_POST["coord_e"],
            "diametro" => $_POST["diametro"],
            "profundidad" => $_POST["profundidad"],
            "material" => $_POST["material"],
            "descripcion" => $_POST["descripcion"],
            "por_parko" => $_POST["por_parko"],
        );

        if ($action == "create_request") {
            if (!in_array("0-2", $current_access)) {
                $_d->response($_d->msg(0, PERM_MSG));
            }

            $well_data = array_merge(array(
                "cliente_id" => $_POST["cliente"],
                "sede_id" => $_POST["sede"],
            ), $well_data);

            if (db_insert("pozos", $well_data)) {
                $_d->response($_d->msg(1, "wells.php?action=details&id=" . $db->last_insert_id()));
            }
        } elseif ($action == "edit_request") {

            if (!in_array("0-3", $current_access)) {
                $_d->response($_d->msg(0, PERM_MSG));
            }

            $well_id = _GET("id");
            if (db_update("pozos", $well_data, "WHERE pozo_id='$well_id'")) {
                $_d->response($_d->msg(1, "wells.php?action=details&id=" . $well_id));
            }
        }
        break;

    case "new_task_request":
    case "edit_task_request":

        $_d = new debug();

        if (isset($_POST["cliente"]) && empty($_POST["cliente"])) {
            $_d->response($_d->msg(0, 'Por favor seleccione un cliente.', "cliente"));
        }

        if (isset($_POST["sede"]) && empty($_POST["sede"])) {
            $_d->response($_d->msg(0, 'Por favor seleccione una sede.', "sede"));
        }

        if ((isset($_POST["pozo"]) || $action == "new_task_request") && empty($_POST["pozo"])) {
            $_d->response($_d->msg(0, 'Por favor seleccione un pozo.', "pozo"));
        }

        if (empty($_POST["fecha_trabajo"])) {
            $_d->response($_d->msg(0, 'Por favor ingrese la fecha del trabajo.', "fecha_trabajo"));
        }

        if (strlen($_POST["status"]) < 1) {
            $_d->response($_d->msg(0, 'Por favor seleccione el status del trabajo.', "status"));
        }

        if (!isset($_POST["limpieza"])) {
            $_d->response($_d->msg(0, 'Por favor indique si se realizó limpieza de pozo.', "limpieza"));
        }

        if (!isset($_POST["bomba_extraida"])) {
            $_d->response($_d->msg(0, 'Por favor indique si se realizó una extracción de bomba.', "bomba_extraida"));
        }

        if (!isset($_POST["bomba_instalada"])) {
            $_d->response($_d->msg(0, 'Por favor indique si se realizó una instalación de bomba.', "bomba_instalada"));
        }

        if (!isset($_POST["prueba_bombeo"])) {
            $_d->response($_d->msg(0, 'Por favor indique si se realizó una prueba de bombeo.', "prueba_bombeo"));
        }

        $task_data = array(
            "pozo_id" => $_POST["pozo"],
            "fecha_trabajo" => db_date_format($_POST["fecha_trabajo"]),
            "realizado_por" => $_POST["realizado_por"],
            "grua_usada" => $_POST["grua_usada"],
            "status" => $_POST["status"],
            "bomba_extraida" => $_POST["bomba_extraida"],
            "bomba_instalada" => $_POST["bomba_instalada"],
            "nivel_estatico" => $_POST["nivel_estatico"],
            "profundidad_inicial" => $_POST["profundidad_inicial"],
            "observaciones" => $_POST["observaciones"],
            "titulo" => $_POST["titulo"],
        );

        $limpieza_data = array();

        if ($_POST["limpieza"] == 1) {
            $limpieza_data = array(
                "profundidad_final" => $_POST["profundidad_final"],
                "metodo_usado" => $_POST["metodo_usado"],
                "nivel_bombeo" => $_POST["nivel_bombeo"],
                "caudal" => $_POST["caudal"],
            );

            if ($pi = _POST("profundidad_inicial") && $pf = _POST("profundidad_final")) {
                if ($pf < $pi) {
                    $_d->response($_d->msg(0, 'La Profundidad Final debe ser mayor o igual que la Profundidad Inicial',
                        "profundidad_final"));
                }
            }
        }

        $test_bombeo = array();

        if ($_POST["prueba_bombeo"] == 1) {
            $test_bombeo = array(
                "nivel_estatico_prueba_bombeo" => $_POST["nivel_estatico_prueba_bombeo"],
                "nivel_prueba_bombeo" => $_POST["nivel_prueba_bombeo"],
                "caudal_prueba_bombeo" => $_POST["caudal_prueba_bombeo"],
            );

            if ($nb = _POST("nivel_bombeo") && $ne = _POST("nivel_estatico_prueba_bombeo")) {
                if ($nb < $ne) {
                    $_d->response($_d->msg(0, 'El Nivel de Bombeo debe ser mayor o igual al Nivel Estático',
                        "nivel_bombeo"));
                }
            }

        }

        $datos_arranque = array();

        if ($_POST["bomba_instalada"] == 1) {
            $datos_arranque = array(
                "caudal_bomba_instalada" => $_POST["caudal_bomba_instalada"],
                "VA_VL1_bomba_instalada" => $_POST["VA_VL1_bomba_instalada"],
                "VA_VL2_bomba_instalada" => $_POST["VA_VL2_bomba_instalada"],
                "VA_VL3_bomba_instalada" => $_POST["VA_VL3_bomba_instalada"],
                "VE_VBF1_bomba_instalada" => $_POST["VE_VBF1_bomba_instalada"],
                "VE_VBF2_bomba_instalada" => $_POST["VE_VBF2_bomba_instalada"],
                "VE_VBF3_bomba_instalada" => $_POST["VE_VBF3_bomba_instalada"],
                "consumo_L1_bomba_instalada" => $_POST["consumo_L1_bomba_instalada"],
                "consumo_L2_bomba_instalada" => $_POST["consumo_L2_bomba_instalada"],
                "consumo_L3_bomba_instalada" => $_POST["consumo_L3_bomba_instalada"],
            );
        }

        $sections = array(
            "bomba_extraida" => "s1_",
            "bomba_instalada" => "s2_",
        );


        foreach ($sections as $section => $prefix) {

            $$section = array();
            $bomba_data = array();
            $motor_data = array();
            $acc_data = array();
            if ($_POST[$section] == 1) {

                $tipo_bomba_var = $prefix . "tipo_bomba";
                $$tipo_bomba_var = (int)_POST($tipo_bomba_var);

                switch ($$tipo_bomba_var) {

                    default:
                        $_d->response($_d->msg(0, 'Por favor seleccione el tipo de bomba.', "{$prefix}tipo_bomba"));
                        break;

                    case 1:

                        $bomba_data = array(
                            "tipo" => $_POST["{$prefix}tipo_bomba"],
                            "modelo" => $_POST["{$prefix}tipo1_modelo"],
                            "marca" => $_POST["{$prefix}tipo1_marca"],
                            "etapas" => $_POST["{$prefix}tipo1_etapas"],
                            "serial" => $_POST["{$prefix}tipo1_serial"],
                        );

                        $motor_data = array(
                            "{$prefix}tipo1_marca_motor" => $_POST["{$prefix}tipo1_marca_motor"],
                            "{$prefix}tipo1_diam_motor" => $_POST["{$prefix}tipo1_diam_motor"],
                            "{$prefix}tipo1_nema_motor" => $_POST["{$prefix}tipo1_nema_motor"],
                            "{$prefix}tipo1_hp_motor" => $_POST["{$prefix}tipo1_hp_motor"],
                            "{$prefix}tipo1_voltaje_motor" => $_POST["{$prefix}tipo1_voltaje_motor"],
                            "{$prefix}tipo1_amp_nominal_motor" => $_POST["{$prefix}tipo1_amp_nominal_motor"],
                            "{$prefix}tipo1_amp_max_motor" => $_POST["{$prefix}tipo1_amp_max_motor"],
                            "{$prefix}tipo1_fases_motor" => $_POST["{$prefix}tipo1_fases_motor"],
                            "{$prefix}tipo1_rpm_motor" => $_POST["{$prefix}tipo1_rpm_motor"],
                            "{$prefix}tipo1_serial_motor" => $_POST["{$prefix}tipo1_serial_motor"],
                        );

                        $acc_data = array(
                            "{$prefix}tipo1_cant_tubos0_acc" => $_POST["{$prefix}tipo1_cant_tubos0_acc"],
                            "{$prefix}tipo1_long_tubos0_acc" => $_POST["{$prefix}tipo1_long_tubos0_acc"],
                            "{$prefix}tipo1_diam_tubos0_acc" => $_POST["{$prefix}tipo1_diam_tubos0_acc"],
                            "{$prefix}tipo1_cant_tubos1_acc" => $_POST["{$prefix}tipo1_cant_tubos1_acc"],
                            "{$prefix}tipo1_long_tubos1_acc" => $_POST["{$prefix}tipo1_long_tubos1_acc"],
                            "{$prefix}tipo1_diam_tubos1_acc" => $_POST["{$prefix}tipo1_diam_tubos1_acc"],
                            "{$prefix}tipo1_cable_n_acc" => $_POST["{$prefix}tipo1_cable_n_acc"],
                            "{$prefix}tipo1_tipo_cable_acc" => $_POST["{$prefix}tipo1_tipo_cable_acc"],
                            "{$prefix}tipo1_long_cable_acc" => $_POST["{$prefix}tipo1_long_cable_acc"],
                            "{$prefix}tipo1_lineas_cable_acc" => $_POST["{$prefix}tipo1_lineas_cable_acc"],
                            "{$prefix}tipo1_nro_check0_acc" => $_POST["{$prefix}tipo1_nro_check0_acc"],
                            "{$prefix}tipo1_diam_check0_acc" => $_POST["{$prefix}tipo1_diam_check0_acc"],
                            "{$prefix}tipo1_nro_check1_acc" => $_POST["{$prefix}tipo1_nro_check1_acc"],
                            "{$prefix}tipo1_diam_check1_acc" => $_POST["{$prefix}tipo1_diam_check1_acc"],
                            "{$prefix}tipo1_cable_sonda_acc" => $_POST["{$prefix}tipo1_cable_sonda_acc"],
                            "{$prefix}tipo1_cant_electrodos_acc" => $_POST["{$prefix}tipo1_cant_electrodos_acc"],
                        );

                        break;

                    case 2:

                        $bomba_data = array(
                            "tipo" => $_POST["{$prefix}tipo_bomba"],
                            "modelo" => $_POST["{$prefix}tipo2_modelo"],
                            "marca" => $_POST["{$prefix}tipo2_marca"],
                            "etapas" => $_POST["{$prefix}tipo2_etapas"],
                            "serial" => $_POST["{$prefix}tipo2_serial"],
                        );

                        $tipo_motor_var = $prefix . "tipo_motor";
                        $$tipo_motor_var = (int)_POST($tipo_motor_var);

                        switch ($$tipo_motor_var) {

                            default:
                                $_d->response($_d->msg(0, 'Por favor seleccione el tipo de motor.',
                                    "{$prefix}tipo_motor"));
                                break;

                            case
                            1:
                                $motor_data = array(
                                    "{$prefix}tipo_motor" => $$tipo_motor_var,
                                    "{$prefix}tipo2_marca_motor" => $_POST["{$prefix}tipo2_marca_motor"],
                                    "{$prefix}tipo2_diam_motor" => $_POST["{$prefix}tipo2_diam_motor"],
                                    "{$prefix}tipo2_nema_motor" => $_POST["{$prefix}tipo2_nema_motor"],
                                    "{$prefix}tipo2_hp_motor" => $_POST["{$prefix}tipo2_hp_motor"],
                                    "{$prefix}tipo2_voltaje_motor" => $_POST["{$prefix}tipo2_voltaje_motor"],
                                    "{$prefix}tipo2_amp_nominal_motor" => $_POST["{$prefix}tipo2_amp_nominal_motor"],
                                    "{$prefix}tipo2_amp_max_motor" => $_POST["{$prefix}tipo2_amp_max_motor"],
                                    "{$prefix}tipo2_fases_motor" => $_POST["{$prefix}tipo2_fases_motor"],
                                    "{$prefix}tipo2_rpm_motor" => $_POST["{$prefix}tipo2_rpm_motor"],
                                    "{$prefix}tipo2_serial_motor" => $_POST["{$prefix}tipo2_serial_motor"],
                                );
                                break;

                            case 2:
                                $motor_data = array(
                                    "{$prefix}tipo_motor" => $$tipo_motor_var,
                                    "{$prefix}tipo2_marca_motor" => $_POST["{$prefix}tipo2_marca_motor"],
                                    "{$prefix}tipo2_hp_motor" => $_POST["{$prefix}tipo2_hp_motor"],
                                );
                                break;
                        }

                        $acc_data = array(
                            "{$prefix}tipo2_cant_tubos0_acc" => $_POST["{$prefix}tipo2_cant_tubos0_acc"],
                            "{$prefix}tipo2_long_tubos0_acc" => $_POST["{$prefix}tipo2_long_tubos0_acc"],
                            "{$prefix}tipo2_diam_tubos0_acc" => $_POST["{$prefix}tipo2_diam_tubos0_acc"],
                            "{$prefix}tipo2_cant_tubos1_acc" => $_POST["{$prefix}tipo2_cant_tubos1_acc"],
                            "{$prefix}tipo2_long_tubos1_acc" => $_POST["{$prefix}tipo2_long_tubos1_acc"],
                            "{$prefix}tipo2_diam_tubos1_acc" => $_POST["{$prefix}tipo2_diam_tubos1_acc"],
                            "{$prefix}tipo2_cable_n_acc" => $_POST["{$prefix}tipo2_cable_n_acc"],
                            "{$prefix}tipo2_tipo_cable_acc" => $_POST["{$prefix}tipo2_tipo_cable_acc"],
                            "{$prefix}tipo2_long_cable_acc" => $_POST["{$prefix}tipo2_long_cable_acc"],
                            "{$prefix}tipo2_lineas_cable_acc" => $_POST["{$prefix}tipo2_lineas_cable_acc"],
                            "{$prefix}tipo2_check_acc" => $_POST["{$prefix}tipo2_check_acc"],
                            "{$prefix}tipo2_cable_sonda_acc" => $_POST["{$prefix}tipo2_cable_sonda_acc"],
                            "{$prefix}tipo2_cant_electrodos_acc" => $_POST["{$prefix}tipo2_cant_electrodos_acc"],
                            "{$prefix}tipo2_diam_tubo_funda_acc" => $_POST["{$prefix}tipo2_diam_tubo_funda_acc"],
                            "{$prefix}tipo2_diam_eje_acc" => $_POST["{$prefix}tipo2_diam_eje_acc"],
                            "{$prefix}tipo2_diam_colador_acc" => $_POST["{$prefix}tipo2_diam_colador_acc"],
                            "{$prefix}tipo2_diam_descarga_acc" => $_POST["{$prefix}tipo2_diam_descarga_acc"],
                            "{$prefix}tipo2_tipo_cabezal_acc" => $_POST["{$prefix}tipo2_tipo_cabezal_acc"],
                            "{$prefix}tipo2_diam_cabezal_acc" => $_POST["{$prefix}tipo2_diam_cabezal_acc"],
                        );

                        break;
                }
                $$section = array_merge($bomba_data, array(
                    "motor" => $motor_data,
                    "accesorios" => $acc_data,
                ));
            }
        }

        $task_data = array_merge($task_data, array(
            "limpieza_pozo" => $limpieza_data,
            "prueba_bombeo" => $test_bombeo,
        ));


        if ($action == "new_task_request") {

            if (!in_array("1-2", $current_access)) {
                $_d->response($_d->msg(0, PERM_MSG));
            }

            if (isset($_POST["id_trabajo"])) {
                if (!task_info($_POST["id_trabajo"])) {
                    $task_data["trabajo_id"] = $_POST["id_trabajo"];
                } else {
                    $_d->response($_d->msg(0,
                        "El Nro. de Trabajo ingresado ({$_POST['id_trabajo']}) ya se encuentra en uso."));
                }
            }

            if (db_insert("pozos_trabajos", $task_data)) {

                $task_id = $db->last_insert_id();

                if ($task_data["bomba_extraida"]) {
                    db_insert("equipamiento", array_merge(array(
                        "trabajo_id" => $task_id,
                        "accion" => 1,
                    ), $bomba_extraida));
                }

                if ($task_data["bomba_instalada"]) {
                    db_insert("equipamiento", array_merge(array(
                        "trabajo_id" => $task_id,
                        "accion" => 2,
                        "datos_arranque" => $datos_arranque,
                    ), $bomba_instalada));
                }

                $_d->response($_d->msg(1, "wells.php?action=details&id=" . $task_data["pozo_id"] . "&task=$task_id"));
            }
        } else {
            if ($action == "edit_task_request") {

                if (!in_array("1-3", $current_access)) {
                    $_d->response($_d->msg(0, PERM_MSG));
                }

                $task_id = _GET("id");

                if (db_update("pozos_trabajos", $task_data, "WHERE trabajo_id = '$task_id'")) {

                    if ($task_data["bomba_extraida"]) {
                        if (get_equipment($task_id, $action = 1)) {
                            db_update("equipamiento", $bomba_extraida,
                                "WHERE trabajo_id = '$task_id' AND accion = '1'");
                        } else {
                            db_insert("equipamiento", array_merge(array(
                                "trabajo_id" => $task_id,
                                "accion" => 1,
                            ), $bomba_extraida));
                        }

                    } else {
                        db_delete("equipamiento", "WHERE trabajo_id = '$task_id' AND accion = '1'");
                    }
                    if ($task_data["bomba_instalada"]) {
                        if (get_equipment($task_id, $action = 2)) {
                            db_update("equipamiento",
                                array_merge($bomba_instalada, array("datos_arranque" => $datos_arranque)),
                                "WHERE trabajo_id = '$task_id' AND accion = '2'");
                        } else {
                            db_insert("equipamiento", array_merge(array(
                                "trabajo_id" => $task_id,
                                "accion" => 2,
                                "datos_arranque" => $datos_arranque,
                            ), $bomba_instalada));
                        }
                    } else {
                        db_delete("equipamiento", "WHERE trabajo_id = '$task_id' AND accion = '2'");
                    }

                    $_d->response($_d->msg(1,
                        "wells.php?action=details&id=" . $task_data["pozo_id"] . "&task=$task_id"));
                }

            }
        }

        break;

    case "delete_request":

        $_d = new debug();

        if (!in_array("0-4", $current_access)) {
            $_d->response($_d->msg(0, PERM_MSG));
        }

        $wellid = _POST("wellid");

        if (!empty($wellid)) {
            if ($db->query("DELETE FROM pozos WHERE pozo_id = '$wellid'")) {
                $_d->response($_d->msg(1));
            }
        }

        break;

    case "delete_task_request":

        $_d = new debug();

        if (!in_array("1-4", $current_access)) {
            $_d->response($_d->msg(0, PERM_MSG));
        }

        $taskid = _POST("taskid");


        if (!empty($taskid)) {
            if ($db->query("DELETE FROM pozos_trabajos WHERE trabajo_id = '$taskid'")) {
                $_d->response($_d->msg(1));
            }
        }

        break;

}
