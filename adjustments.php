<?php

require(dirname( __FILE__ ) . '/includes/config.php');
$action = _GET( 'action', 'adjustments' );
$current_mod = basename( __FILE__, '.php' );

switch ( $action ) {
    default:
    case 'adjustments':

        if ( !isset($_SESSION["account"]) || !isset($_SESSION["logged_in"]) )
            header( 'Location: login.php' );

        if ( in_array( "4-1", $current_access ) ) {
            $show_form = true;
        } else {
            $msg->addMsg( PERM_MSG, "warning" );
            $show_form = false;
        }

        $template_data = array(
            "options" => get_options(),
            "theme_list" => get_themes(),
            'show_form' => $show_form,
        );

        $template = $twig->loadTemplate( 'adjustments.twig' );
        echo $template->render( templateContext( $template_data ) );
        break;

    case 'save':

        if ( count( $_POST ) > 0 ) {
            $_d = new debug();

            if (!in_array("4-3", $current_access))
                $_d->response($_d->msg(0, PERM_MSG));

            if ( isset($_POST["site_name"]) )
                update_option( "site_name", $_POST["site_name"] );

            if ( isset($_POST["root_path"]) )
                update_option( "root_path", $_POST["root_path"] );

            if ( isset($_POST["default_theme"]) )
                update_option( "default_theme", $_POST["default_theme"] );

            if ( isset($_POST["email_from"]) )
                update_option( "email_from", $_POST["email_from"] );

            if ( isset($_POST["wells_per_page"]) )
                update_option( "wells_per_page", $_POST["wells_per_page"] );

            if ( isset($_POST["customers_per_page"]) )
                update_option( "customers_per_page", $_POST["customers_per_page"] );

            if ( isset($_POST["users_per_page"]) )
                update_option( "users_per_page", $_POST["users_per_page"] );

            update_option( "deny_delete_customers", $_POST["deny_delete_customers"] );
            update_option( "deny_delete_wells", $_POST["deny_delete_wells"] );

            $_d->response( $_d->msg( 1 ) );
        }

        break;

}