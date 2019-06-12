<?php

require(dirname(__FILE__) . '/includes/config.php');
$action = _GET('action', 'adjustments');
$current_mod = basename(__FILE__, '.php');

switch ($action) {
    default:
    case 'adjustments':

        if (!isset($_SESSION["account"]) || !isset($_SESSION["logged_in"])) {
            header('Location: login.php');
        }


        $municipios = get_dir_list("municipios", "", "", "", true);
        $ciudades = get_dir_list("ciudades", "", "", "", true);
        $estados = get_dir_list("estados", "", "", "", true);


        if (in_array("4-1", $current_access)) {
            $show_form = true;


            if (count($_POST) > 0) {
                if (in_array("4-3", $current_access)) {
                    if (isset($_FILES["uploadedfile"])) {

                        $csv_mimetypes = array(
                            'text/csv',
                            'text/plain',
                            'application/csv',
                            'text/comma-separated-values',
                            'application/excel',
                            'application/vnd.ms-excel',
                            'application/vnd.msexcel',
                            'text/anytext',
                            'application/octet-stream',
                            'application/txt',
                        );

                        if ($_FILES["uploadedfile"]["error"] > 0) {
                            $msg->addMsg("Ha ocurrido un error al subir el archivo (" . $_FILES["uploadedfile"]["error"] . ")",
                                "warning");
                        } elseif (isset($_FILES['uploadedfile']['type']) && !in_array($_FILES['uploadedfile']['type'],
                                $csv_mimetypes)
                        ) {
                            $msg->addMsg("El archivo a importar debe ser en formato .CSV", "warning");
                        } else {

                            $csvFile = $_FILES["uploadedfile"]["tmp_name"];
                            $file_handle = fopen($csvFile, 'r');
                            stream_filter_append($file_handle, 'convert.iconv.ISO-8859-1/UTF-8', STREAM_FILTER_READ);
                            $n = 0;
                            $opt_affirmative = array("SI", "Si", "si", "1", "YES");
                            $opt_negative = array("NO", "No", "no", "0", "");
                            while (!feof($file_handle)) {

                                $row = fgetcsv($file_handle, 0, ";");
                                $n++;
                                if ($n == 1) {
                                    continue;
                                }

                                $customer_current_name = isset($row[1]) ? $row[1] : '';
                                $customer_previous_name = isset($row[2]) ? $row[2] : '';

                                if (!empty($customer_current_name)) {

                                    /* ================================================================ */
                                    /* IMPORTACION DE CLIENTES */
                                    $customer_info = customer_info($customer_current_name, "t2.razon_social", false);
                                    $cliente_id = isset($customer_info["cliente_id"]) ? $customer_info["cliente_id"] : '';

                                    if (!$customer_info) {

                                        if (db_insert("clientes", array(
                                            "reg_date" => date('Y-m-d H:i:s'),
                                        ))
                                        ) {

                                            $cliente_id = $db->last_insert_id();

                                            $customer_data = array(
                                                "cliente_id" => $cliente_id,
                                                "razon_social" => $customer_current_name,
                                                "actual" => 1,
                                            );

                                            db_insert("clientes_nombres", $customer_data);

                                        }
                                    }

                                    if (!empty($customer_previous_name) && $customer_previous_name != $customer_current_name) {
                                        $customer_names = get_customer_names($cliente_id, 2);

                                        if (!in_array($customer_previous_name, $customer_names)) {
                                            $customer_data = array(
                                                "cliente_id" => $cliente_id,
                                                "razon_social" => $customer_previous_name,
                                            );

                                            db_insert("clientes_nombres", $customer_data);
                                        }
                                    }
                                    /* ================================================================ */
                                    /* IMPORTACION DE SEDES */

                                    $headquarter_name = trim($row[3]);
                                    if (empty($headquarter_name)) {
                                        $headquarter_name = "UNICA";
                                    }
                                    $headquarter_info = get_headquarter_info_by_name($cliente_id, $headquarter_name);
                                    $headquarter_id = isset($headquarter_info["sede_id"]) ? $headquarter_info["sede_id"] : "";

                                    if (!$headquarter_info) {
                                        db_insert("clientes_sedes", array(
                                            "cliente_id" => $cliente_id,
                                        ));

                                        $headquarter_id = $db->last_insert_id();

                                        db_insert("clientes_sedes_nombres", array(
                                            "sede_id" => $headquarter_id,
                                            "nombre" => $headquarter_name,
                                            "actual" => 1,
                                        ));
                                    }

                                    /* ================================================================ */
                                    /* IMPORTACION DE POZOS */

                                    $well_name = $row[4];
                                    $well_info = get_well_info_by_name($cliente_id, $headquarter_id, $well_name);
                                    $well_id = isset($well_info["pozo_id"]) ? $well_info["pozo_id"] : "";

                                    if (!$well_info) {

                                        $w_estado = array_search(ucwords(strtolower($row[6])), $estados);
                                        $w_municipio = array_search(ucwords(strtolower($row[7])), $municipios);
                                        $w_ciudad = array_search(ucwords(strtolower($row[8])), $ciudades);
                                        $por_parko = in_array($row[123], $opt_affirmative) ? 1 : 0;

                                        $well_data = array(
                                            "cliente_id" => $cliente_id,
                                            "sede_id" => $headquarter_id,
                                            "nombre" => $well_name,
                                            "direccion" => $row[5],
                                            "estado" => $w_estado,
                                            "municipio" => $w_municipio,
                                            "ciudad" => $w_ciudad,
                                            "coord_n" => $row[9],
                                            "coord_e" => $row[10],
                                            "diametro" => $row[11],
                                            "profundidad" => floatVeToSQL($row[12]),
                                            "material" => $row[13],
                                            "fecha_construccion" => db_date_format($row[14]),
                                            "descripcion" => $row[15],
                                            "por_parko" => $por_parko,
                                        );

                                        db_insert("pozos", $well_data);
                                        $well_id = $db->last_insert_id();
                                    }


                                    /* ================================================================ */
                                    /* IMPORTACION DE TRABAJOS */

                                    $offset_bomba_extraida = 26;
                                    $offset_bomba_instalada = 66;

                                    $opt_bomba_extraida = $row[$offset_bomba_extraida];
                                    $opt_bomba_instalada = $row[$offset_bomba_instalada];

                                    $opt_bomba_extraida = in_array($opt_bomba_extraida, $opt_affirmative) ? 1 : 0;
                                    $opt_bomba_instalada = in_array($opt_bomba_instalada, $opt_affirmative) ? 1 : 0;

                                    $status = isset($row[121]) ? strtolower($row[121]) : "";

                                    switch ($status) {
                                        default:
                                            $status = 0;
                                            break;
                                        case "terminado":
                                        case "finalizado":
                                        case "1":
                                            $status = 1;
                                    }

                                    $row_id = !empty($row[0]) && is_numeric($row[0]) ? (int)$row[0] : 0;

                                    $row_data = array(
                                        "pozo_id" => $well_id,
                                        "fecha_trabajo" => db_date_format($row[16]),
                                        "realizado_por" => $row[17],
                                        "grua_usada" => $row[18],
                                        "bomba_extraida" => $opt_bomba_extraida,
                                        "bomba_instalada" => $opt_bomba_instalada,
                                        "nivel_estatico" => $row[19],
                                        "profundidad_inicial" => floatVeToSQL($row[20]),
                                        "observaciones" => $row[120],
                                        "status" => $status,
                                        "titulo" => $row[122],
                                    );

                                    if (!empty($row_id)) {
                                        $row_data["trabajo_id"] = $row_id;
                                    }

                                    $limpieza_data = array();

                                    if (in_array($row[21], $opt_affirmative)) {
                                        $limpieza_data = array(
                                            "profundidad_final" => floatVeToSQL($row[22]),
                                            "metodo_usado" => $row[23],
                                            "nivel_bombeo" => $row[24],
                                            "caudal" => $row[25],
                                        );
                                    }

                                    $test_bombeo = array();

                                    if (in_array($row[116], $opt_affirmative)) {
                                        $test_bombeo = array(
                                            "nivel_estatico_prueba_bombeo" => $row[117],
                                            "nivel_prueba_bombeo" => $row[118],
                                            "caudal_prueba_bombeo" => $row[119],
                                        );
                                    }

                                    $datos_arranque = array();

                                    if (in_array($row[66], $opt_affirmative)) {
                                        $datos_arranque = array(
                                            "caudal_bomba_instalada" => $row[106],
                                            "VA_VL1_bomba_instalada" => $row[107],
                                            "VA_VL2_bomba_instalada" => $row[108],
                                            "VA_VL3_bomba_instalada" => $row[109],
                                            "VE_VBF1_bomba_instalada" => $row[110],
                                            "VE_VBF2_bomba_instalada" => $row[111],
                                            "VE_VBF3_bomba_instalada" => $row[112],
                                            "consumo_L1_bomba_instalada" => $row[113],
                                            "consumo_L2_bomba_instalada" => $row[114],
                                            "consumo_L3_bomba_instalada" => $row[115],
                                        );
                                    }

                                    $prefijoS = array(
                                        "bomba_extraida" => "s1_",
                                        "bomba_instalada" => "s2_",
                                    );


                                    foreach ($prefijoS as $section => $prefix) {

                                        $$section = array();
                                        $bomba_data = array();
                                        $motor_data = array();
                                        $acc_data = array();

                                        if (${'opt_' . $section}) {

                                            $offset = ${'offset_' . $section};

                                            $tipo_bomba_txt = $row[$offset + 1];

                                            if (in_array($tipo_bomba_txt, array('S', '1'))) {
                                                $tipo_bomba_txt = 1;
                                            } elseif (in_array($tipo_bomba_txt, array('T', '2'))) {
                                                $tipo_bomba_txt = 2;
                                            } else {
                                                $tipo_bomba_txt = 0;
                                            }

                                            $bomba_data = array(
                                                "tipo" => $tipo_bomba_txt,
                                                "modelo" => $row[$offset + 2],
                                                "marca" => $row[$offset + 3],
                                                "etapas" => $row[$offset + 4],
                                                "serial" => $row[$offset + 5],
                                            );

                                            switch ($tipo_bomba_txt) {

                                                default:
                                                    break;

                                                case 1: //Sumergible

                                                    $motor_data = array(
                                                        "{$prefix}tipo1_marca_motor" => $row[$offset + 6],
                                                        "{$prefix}tipo1_diam_motor" => $row[$offset + 7],
                                                        "{$prefix}tipo1_nema_motor" => $row[$offset + 8],
                                                        "{$prefix}tipo1_hp_motor" => $row[$offset + 9],
                                                        "{$prefix}tipo1_voltaje_motor" => $row[$offset + 10],
                                                        "{$prefix}tipo1_amp_nominal_motor" => $row[$offset + 11],
                                                        "{$prefix}tipo1_amp_max_motor" => $row[$offset + 12],
                                                        "{$prefix}tipo1_fases_motor" => $row[$offset + 13],
                                                        "{$prefix}tipo1_rpm_motor" => $row[$offset + 14],
                                                        "{$prefix}tipo1_serial_motor" => $row[$offset + 15],
                                                    );

                                                    $acc_data = array(
                                                        "{$prefix}tipo1_cant_tubos0_acc" => $row[$offset + 18],
                                                        "{$prefix}tipo1_long_tubos0_acc" => $row[$offset + 19],
                                                        "{$prefix}tipo1_diam_tubos0_acc" => $row[$offset + 20],
                                                        "{$prefix}tipo1_cant_tubos1_acc" => $row[$offset + 21],
                                                        "{$prefix}tipo1_long_tubos1_acc" => $row[$offset + 22],
                                                        "{$prefix}tipo1_diam_tubos1_acc" => $row[$offset + 23],
                                                        "{$prefix}tipo1_cable_n_acc" => $row[$offset + 24],
                                                        "{$prefix}tipo1_tipo_cable_acc" => $row[$offset + 25],
                                                        "{$prefix}tipo1_long_cable_acc" => $row[$offset + 26],
                                                        "{$prefix}tipo1_lineas_cable_acc" => $row[$offset + 27],
                                                        "{$prefix}tipo1_nro_check0_acc" => $row[$offset + 28],
                                                        "{$prefix}tipo1_diam_check0_acc" => $row[$offset + 29],
                                                        "{$prefix}tipo1_nro_check1_acc" => $row[$offset + 30],
                                                        "{$prefix}tipo1_diam_check1_acc" => $row[$offset + 31],
                                                        "{$prefix}tipo1_cable_sonda_acc" => $row[$offset + 32],
                                                        "{$prefix}tipo1_cant_electrodos_acc" => $row[$offset + 33],
                                                    );

                                                    break;

                                                case 2: //Turbina

                                                    if (!empty($row[$offset + 16]) || !empty($row[$offset + 17])) {
                                                        $tipo_motor = 2;
                                                    } else {
                                                        $tipo_motor = 1;
                                                    }

                                                    switch ($tipo_motor) {

                                                        default:
                                                            break;

                                                        case 1:
                                                            //Electrico
                                                            $motor_data = array(
                                                                "{$prefix}tipo_motor" => $tipo_motor,
                                                                "{$prefix}tipo2_marca_motor" => $row[$offset + 6],
                                                                "{$prefix}tipo2_diam_motor" => $row[$offset + 7],
                                                                "{$prefix}tipo2_nema_motor" => $row[$offset + 8],
                                                                "{$prefix}tipo2_hp_motor" => $row[$offset + 9],
                                                                "{$prefix}tipo2_voltaje_motor" => $row[$offset + 10],
                                                                "{$prefix}tipo2_amp_nominal_motor" => $row[$offset + 11],
                                                                "{$prefix}tipo2_amp_max_motor" => $row[$offset + 12],
                                                                "{$prefix}tipo2_fases_motor" => $row[$offset + 13],
                                                                "{$prefix}tipo2_rpm_motor" => $row[$offset + 14],
                                                                "{$prefix}tipo2_serial_motor" => $row[$offset + 15],
                                                            );
                                                            break;

                                                        case 2: //Disel
                                                            $motor_data = array(
                                                                "{$prefix}tipo_motor" => $tipo_motor,
                                                                "{$prefix}tipo2_marca_motor" => $row[$offset + 16],
                                                                "{$prefix}tipo2_hp_motor" => $row[$offset + 17],
                                                            );
                                                            break;
                                                    }

                                                    $acc_data = array(
                                                        "{$prefix}tipo2_cant_tubos0_acc" => $row[$offset + 18],
                                                        "{$prefix}tipo2_long_tubos0_acc" => $row[$offset + 19],
                                                        "{$prefix}tipo2_diam_tubos0_acc" => $row[$offset + 20],
                                                        "{$prefix}tipo2_cant_tubos1_acc" => $row[$offset + 21],
                                                        "{$prefix}tipo2_long_tubos1_acc" => $row[$offset + 22],
                                                        "{$prefix}tipo2_diam_tubos1_acc" => $row[$offset + 23],
                                                        "{$prefix}tipo2_cable_n_acc" => $row[$offset + 34],
                                                        "{$prefix}tipo2_tipo_cable_acc" => $row[$offset + 35],
                                                        "{$prefix}tipo2_long_cable_acc" => $row[$offset + 36],
                                                        "{$prefix}tipo2_lineas_cable_acc" => $row[$offset + 37],
                                                        "{$prefix}tipo2_check_acc" => $row[$offset + 38],
                                                        "{$prefix}tipo2_cable_sonda_acc" => $row[$offset + 39],
                                                        "{$prefix}tipo2_cant_electrodos_acc" => $row[$offset + 40], //TODO confirmar si este campo es un error
                                                    );

                                                    break;
                                            }
                                            $$section = array_merge($bomba_data, array(
                                                "motor" => $motor_data,
                                                "accesorios" => $acc_data,
                                            ));
                                        }
                                    }

                                    $row_data = array_merge($row_data, array(
                                        "limpieza_pozo" => $limpieza_data,
                                        "prueba_bombeo" => $test_bombeo,
                                    ));

                                    if (db_insert("pozos_trabajos", $row_data)) {

                                        $row_id = $db->last_insert_id();

                                        if ($row_data["bomba_extraida"]) {
                                            db_insert("equipamiento", array_merge(array(
                                                "trabajo_id" => $row_id,
                                                "accion" => 1,
                                            ), $bomba_extraida));
                                        }

                                        if ($row_data["bomba_instalada"]) {
                                            db_insert("equipamiento", array_merge(array(
                                                "trabajo_id" => $row_id,
                                                "accion" => 2,
                                                "datos_arranque" => $datos_arranque,
                                            ), $bomba_instalada));
                                        }
                                    }

                                }


                            }
                            fclose($file_handle);

                            $msg->addMsg("El archivo ha sido importado exitosamente.", "success");
                        }
                    }
                } else {
                    $msg->addMsg(PERM_MSG, "warning");
                }
            }
        } else {
            $msg->addMsg(PERM_MSG, "warning");
            $show_form = false;
        }

        $template_data = array(
            "options" => get_options(),
            "theme_list" => get_themes(),
            'show_form' => $show_form,
        );

        $template = $twig->loadTemplate('data-transfer.twig');
        echo $template->render(templateContext($template_data));
        break;
    case 'export':
        if (!isset($_SESSION["account"]) || !isset($_SESSION["logged_in"])) {
            header('Location: login.php');
        }

        $bombaTipo = array();
        $tipos = equipment_type_list();
        foreach ($tipos as $tipo) {
            $bombaTipo[$tipo['equip_tipo_id']] = $tipo['nombre'];
        }
        $encabezados = array(
            'Trabajo ID',
            'Cliente',
            'Nombre de Cliente Anterior',
            'Sede',
            'Nombre del Pozo',
            'Direccion',
            'Estado',
            'Municipio',
            'Ciudad',
            'Coord N',
            'Coord E',
            'Diametro(pulg)',
            'Profundidad',
            'Material',
            'Fecha Construccion',
            'Descripcion',
            'Fecha del Trabajo',
            'Realizado por',
            'Grua Usada',
            'Nivel Estatico (m)',
            'Profundidad Inicial',
            'Limpieza de pozo (SI/NO)',
            'Profundidad Final',
            'Metodo Usado',
            'Nivel de Bombeo (m)',
            'Caudal Limpieza (I/s)',
            'Bomba Extraida (SI/NO)',
            'Tipo Bomba Extraida',
            'Modelo BE ',
            'Marca BE ',
            'Etapas BE ',
            'Serial BE',
            'Marca Mot Elec BE',
            'Diam. Motor BE (pulg)',
            'NEMA BE',
            'Hp Mot Elec BE',
            'Voltaje Nominal BE',
            'Amp Nominal BE',
            'Amp. Max BE',
            'Fases BE',
            'RPM BE',
            'Serial Mot Elec BE',
            'Marca Mot Diesel BE',
            'Hp Mot Diesel BE',
            'Cant. Tubos BE',
            'Long Tub BE (m)',
            'Diam Tubo BE (pulg)',
            'Cant. Tubos 2 BE',
            'Long Tub 2 BE (m)',
            'Diam Tub 2 BE (pulg)',
            'Cable # BE ',
            'Tipo cable BE',
            'Long del cable (m) BE',
            'Lineas de Cable BE',
            'Nro de Check BE',
            'Diam de Check BE (pulg)',
            'Nro de Check 2 BE',
            'Diam Check 2 BE (pulg)',
            'Cable Sonda # BE',
            'Cant. Electrodos BE',
            'Diam. Tubo Funda BE (Pulg)',
            'Diam. Eje BE (Pulg)',
            'Diam. Colador BE (Pulg) ',
            'Diam. Descarga BE (Pulg)',
            'Tipo Cabezal BE',
            'Diam. Cabezal BE (Pulg)',
            'Bomba Instalada (SI/NO)',
            'Tipo Bomba Instalada',
            'Modelo BI ',
            'Marca BI ',
            'Etapas BI ',
            'Serial BI',
            'Marca Mot Elec BI',
            'Diam. Motor BI (pulg)',
            'NEMA BI',
            'Hp Mot Elec BI',
            'Voltaje Nominal BI',
            'Amp Nominal BI',
            'Amp. Max BI',
            'Fases BI',
            'RPM BI',
            'Serial Mot Elec BI',
            'Marca Mot Diesel BI',
            'Hp Mot Diesel BI',
            'Cant. Tubos BI',
            'Long Tub BI (m)',
            'Diam Tubo BI (pulg)',
            'Cant. Tubos 2 BI',
            'Long Tub 2 BI (m)',
            'Diam Tub 2 BI (pulg)',
            'Cable # BI ',
            'Tipo cable BI',
            'Long del cable (m) BI',
            'Lineas de Cable BI',
            'Nro de Check BI',
            'Diam de Check BI (pulg)',
            'Nro de Check 2 BI',
            'Diam Check 2 BI (pulg)',
            'Cable Sonda # BI',
            'Cant. Electrodos BI',
            'Diam. Tubo Funda BI (Pulg)',
            'Diam. Eje BI (Pulg)',
            'Diam. Colador BI (Pulg) ',
            'Diam. Descarga BI (Pulg)',
            'Tipo CaBIzal BI',
            'Diam. CaBIzal BI (Pulg)',
            'Caudal Arranque (l/s)',
            'V L1',
            'VBF1',
            'L1 (Amp)',
            'V L2',
            'VBF2',
            'L2 (Amp)',
            'V L3',
            'VBF3',
            'L3 (Amp)',
            'Prueba de Bombeo (SI/NO)',
            'Nivel Estatico PB (m)',
            'Nivel de Bombeo PB (m)',
            'Caudal Prueba PB (l/s)',
            'Observaciones y Material Dañado',
            'Status (En Proceso / Finalizado)',
            'Titulo del trabajo',
            'Construido por Parko (SI/NO)'
        );

        $separadorCSV = ';';
        $debug = false;
        if (!$debug) {
            $filename = 'export-' . date("Y-m-d") . ".csv";
            header('Content-Encoding: UTF-8');
            header('Content-Type: application/csv;charset=UTF-8');
            header('Content-Disposition: attachment; filename=' . $filename);
            header("Pragma: no-cache");
            header("Expires: 0");

            $f = fopen('php://output', 'w');
            fputcsv($f, $encabezados, $separadorCSV);
        }

        $query = "SELECT pt.trabajo_id, cn.razon_social, (SELECT GROUP_CONCAT(cn_aux.razon_social SEPARATOR ' / ') 
            FROM clientes_nombres AS cn_aux WHERE cn_aux.actual = 0 AND cn_aux.cliente_id = p.cliente_id
            ) AS nombre_anterior, cs.info , p.nombre, p.direccion, e.estado, m.municipio, ci.ciudad, p.coord_n, 
            p.coord_e, p.diametro, p.profundidad, p.material, p.fecha_construccion, p.descripcion, pt.fecha_trabajo, 
            pt.realizado_por, pt.grua_usada, pt.nivel_estatico, pt.profundidad_inicial, pt.limpieza_pozo, 
            pt.bomba_extraida, pt.bomba_instalada, pt.prueba_bombeo, pt.observaciones, pt.`status`, pt.titulo, p.por_parko 
            FROM pozos_trabajos AS pt
            INNER JOIN pozos AS p ON pt.pozo_id = p.pozo_id
            INNER JOIN clientes_nombres AS cn ON p.cliente_id = cn.cliente_id
            INNER JOIN clientes_sedes AS cs ON p.sede_id = cs.sede_id
            LEFT JOIN estados AS e ON p.estado = e.id_estado
            LEFT JOIN municipios AS m ON m.id_municipio = p.municipio
            LEFT JOIN ciudades AS ci ON ci.id_ciudad = p.ciudad 
            WHERE 1 AND cn.actual = 1 
            /*AND p.pozo_id=1110*/
            ";
        if($debug){
            $query .= 'LIMIT 450,50';
        }
        $results = $db->query($query);
        while ($task = $db->fetch_array($results)) {
            //Iniciemos las variables comunes por cada linea
            $csvLine = $extraccion_bomba = $instalacion_bomba = $motor = $acc = $arranque = array();

            $csvLine[] = $task['trabajo_id'];
            $csvLine[] = $task['razon_social'];
            $csvLine[] = $task['nombre_anterior'];
            $csvLine[] = $task['info'];
            $csvLine[] = $task['nombre'];
            $csvLine[] = $task['direccion'];
            $csvLine[] = $task['estado'];
            $csvLine[] = $task['municipio'];
            $csvLine[] = $task['ciudad'];
            $csvLine[] = $task['coord_n'];
            $csvLine[] = $task['coord_e'];
            $csvLine[] = $task['diametro'];
            $csvLine[] = floatSQLToVe($task['profundidad']);
            $csvLine[] = $task['material'];
            $csvLine[] = form_date_format($task['fecha_construccion']);
            $csvLine[] = str_replace($separadorCSV, '.', $task['descripcion']);
            $csvLine[] = form_date_format($task['fecha_trabajo']);
            $csvLine[] = $task['realizado_por'];
            $csvLine[] = $task['grua_usada'];
            $csvLine[] = floatSQLToVe($task['nivel_estatico']);
            $csvLine[] = floatSQLToVe($task['profundidad_inicial']);

            //Limpieza de pozo
            if ($task['limpieza_pozo'] != 'YTowOnt9') {
                $data = unserialize64($task['limpieza_pozo']);
                $csvLine[] = 'SI';
                $csvLine[] = (isset($data['profundidad_final'])) ? $data['profundidad_final'] : '';
                $csvLine[] = (($data['metodo_usado'] == 1) ? 'Compresor' : (($data['metodo_usado'] == 2) ? 'Bomba' : ''));
                $csvLine[] = $data['nivel_bombeo'];
                $csvLine[] = $data['caudal'];
            } else {
                //Es un objeto vacio
                $csvLine[] = 'NO';
            }

            $prefijoS = array(
                "bomba_extraida" => "s1_",
                "bomba_instalada" => "s2_",
            );

            //Bomba extraida y/o instalada
            $equipment = array();
            if ($task['bomba_extraida'] == 1 || $task['bomba_instalada'] == 1) {
                $equipment = get_equipment($task["trabajo_id"]);
                $extraccion_bomba = isset($equipment[1]) ? $equipment[1] : array();
                $instalacion_bomba = isset($equipment[2]) ? $equipment[2] : array();
            }

            //Datos de extraccion
            iniciarEnColumna($csvLine, 27);
            if ($equipment && $task["bomba_extraida"] && count($extraccion_bomba) > 0) {
                $csvLine[] = 'SI';
                $csvLine[] = ($extraccion_bomba['tipo']) ? $bombaTipo[$extraccion_bomba['tipo']] : '';
                $csvLine[] = $extraccion_bomba["modelo"];
                $csvLine[] = $extraccion_bomba["marca"];
                $csvLine[] = $extraccion_bomba["etapas"];
                $csvLine[] = $extraccion_bomba["serial"];

                //Datos del motor
                exportMotorData($csvLine, $extraccion_bomba["motor"], $extraccion_bomba['tipo'],
                    $prefijoS["bomba_extraida"]);

                //Datos de los accesorios
                iniciarEnColumna($csvLine, 45);
                exportAccesoriosData($csvLine, $extraccion_bomba['accesorios'], $extraccion_bomba['tipo']);
            } else {
                //Es un objeto vacio
                $csvLine[] = 'NO';
            }

            //Datos de instalacion
            iniciarEnColumna($csvLine, 67);
            if ($equipment && $task["bomba_instalada"] && count($instalacion_bomba) > 0) {
                $csvLine[] = 'SI';
                $csvLine[] = ($instalacion_bomba['tipo']) ? $bombaTipo[$instalacion_bomba['tipo']] : '';
                $csvLine[] = $instalacion_bomba["modelo"];
                $csvLine[] = $instalacion_bomba["marca"];
                $csvLine[] = $instalacion_bomba["etapas"];
                $csvLine[] = $instalacion_bomba["serial"];

                //Datos del motor
                exportMotorData($csvLine, $instalacion_bomba["motor"], $instalacion_bomba['tipo'],
                    $prefijoS["bomba_instalada"]);

                //Datos de los accesorios
                iniciarEnColumna($csvLine, 85);
                exportAccesoriosData($csvLine, $instalacion_bomba['accesorios'], $instalacion_bomba['tipo']);

                //Datos al arranque
                iniciarEnColumna($csvLine, 107);
                $arranque = $instalacion_bomba["datos_arranque"];
                if ($arranque) {
                    foreach ($arranque as $key => $value) {
                        $csvLine[] = $value;
                    }
                }
            } else {
                //Es un objeto vacio
                $csvLine[] = 'NO';
            }

            //Prueba de bombeo
            iniciarEnColumna($csvLine, 117);
            if ($task['prueba_bombeo'] != 'YTowOnt9') {
                $data = unserialize64($task['prueba_bombeo']);
                $csvLine[] = 'SI';
                $csvLine[] = $data['nivel_estatico_prueba_bombeo'];
                $csvLine[] = $data['nivel_prueba_bombeo'];
                $csvLine[] = $data['caudal_prueba_bombeo'];
            } else {
                //Es un objeto vacio
                $csvLine[] = 'NO';
            }

            iniciarEnColumna($csvLine, 121);
            $csvLine[] = str_replace($separadorCSV, '.', $task['observaciones']);
            $csvLine[] = (($task['status'] == "1") ? 'Finalizado' : (($task['status'] == "0") ? 'En Proceso' : ''));
            $csvLine[] = $task['titulo'];
            $csvLine[] = (($task['por_parko'] == "1") ? 'SI' : (($task['por_parko'] == "0") ? 'NO' : ''));

            //
//            die();
            if (!$debug) {
                fputcsv($f, $csvLine, ";");
            } else {
                print_r($csvLine);
            }

        }
        if (!$debug) {
            fclose($f);
        }
        break;
}