<?php

require(dirname( dirname( __FILE__ ) ) . '/includes/config.php');

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ( $action ) {

    case "load_select_municipios":
        if ( isset($_GET["estado"]) ) {

            if ( !isset($_GET["filter"]) || empty($_GET["filter"]) )
                $default_text = "Seleccione un Municipio...";
            else
                $default_text = "Todos...";

            $option = get_dir_list( "municipios", $default_text, $_GET["estado"] );

            foreach ( $option as $opt )
                print("<option value='" . $opt['value'] . "'>" . ($opt['text']) . "</option>\n");

        }
        break;

    case "load_select_ciudades":
        if ( isset($_GET["estado"]) ) {

            if ( !isset($_GET["filter"]) || empty($_GET["filter"]) )
                $default_text = "Seleccione una Ciudad...";
            else
                $default_text = "Todos...";

            $option = get_dir_list( "ciudades", $default_text, $_GET["estado"] );

            foreach ( $option as $opt )
                print("<option value='" . $opt['value'] . "'>" . ($opt['text']) . "</option>\n");

        }
        break;

    case "load_select_sedes":

        if ( isset($_GET["cliente"]) ) {

            $option = get_sedes( $_GET["cliente"], true );

            print("<option value=''>Seleccione una Sede...</option>");

            foreach ( $option as $opt )
                print("<option value='" . $opt['value'] . "'>" . ($opt['text']) . "</option>\n");

        }

        break;

    case "load_select_pozos":

        if ( isset($_GET["sede"]) ) {

            $option = well_list( array( "sede" => $_GET["sede"] ) );

            print("<option value=''>Seleccione un Pozo...</option>");

            foreach ( $option as $opt )
                print("<option value='" . $opt['pozo_id'] . "'>" . ($opt['nombre']) . "</option>\n");

        }

        break;

    case "load_select_customers":

            $option =  customer_list();

            print("<option value=''>Seleccione un Cliente...</option>");

            foreach ( $option as $opt )
                print("<option value='" . $opt['cliente_id'] . "'>" . ($opt['razon_social']) . "</option>\n");

        break;

    case "customer_search_autocomplete":

        $term = _GET( "term" );

        if ( !empty($term) )
            echo customer_search_autocomplete( $term );

        break;

    case "well_search_autocomplete":

        $term = _GET( "term" );
        $mode = _GET( "mode" );

        if ( !empty($term) && !empty($mode) )
            echo well_search_autocomplete( $term, $mode );

        break;

    case "made_by_autocomplete":

        $term = _GET( "term" );
        if ( !empty($term) )
            echo made_by_autocomplete( $term );
        break;

    case "grua_autocomplete":

        $term = _GET( "term" );
        if ( !empty($term) )
            echo grua_autocomplete( $term );
        break;

    case "last_installed_bomb":

        $well_id = _GET( "id" );

        $last_installed_bomb = get_last_installed_bomb( $well_id );

        echo json_encode($last_installed_bomb);

        break;


}

