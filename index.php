<?php
require(dirname( __FILE__ ) . '/includes/config.php');

if ( !isset($_SESSION["account"]) || !isset($_SESSION["logged_in"]) )
    header( 'Location: login.php' );
else
    header( 'Location: wells.php' );
