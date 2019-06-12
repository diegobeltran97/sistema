<?php

$current_userinfo = array();

if (isset($_SESSION["account"]) && isset($_SESSION["logged_in"])) {

    $useraccount = $_SESSION["account"];
    $userid = $_SESSION["logged_in"];

    $user_fields = array(
        'user_id' => $userid,
        'cedula' => $useraccount,
        'status' => 1,
    );

    $current_userinfo = userinfo($user_fields, "AND", true);
    if (!$current_userinfo) {
        session_destroy();
        header("Location: login.php");
        exit;
    }
}

$current_access = isset($current_userinfo["access"]) ? $current_userinfo["access"] : array();

require_once(ABSPATH . 'includes/msg.php');
require_once(ABSPATH . 'includes/menu.php');
require_once(ABSPATH . 'includes/Twig/Autoloader.php');
Twig_Autoloader::register();

$msg = new Msg();
$loader = new Twig_Loader_Filesystem(ABSPATH . 'templates/' . CURRENT_TEMPLATE);
$twig = new Twig_Environment($loader, array(
    //'debug' => true,
));
$twig->addExtension(new Twig_Extension_Debug());

function templateContext($template_context = array())
{
    global $msg, $current_userinfo;

    $template_default_data = array_merge(array(
        'site_name' => SITE_NAME,
        'site_url' => SITE_URL,
        'template_url' => TEMPLATE_URL,
        '_GET' => $_GET,
        '_POST' => $_POST,
        'head' => place_action('head'),
        'body' => place_action('body'),
        'footer' => place_action('footer'),
        'msg' => place_action('msgOutput'),
        'main_menu' => generateMenu($current_userinfo),
        'current_userinfo' => $current_userinfo,
    ), $template_context);

    return $template_default_data;
}

function defaultHeadHTML($twig)
{

    print('
    <link rel="stylesheet" href="' . SITE_URL . 'css/default.css" />
    <link rel="stylesheet" href="' . SITE_URL . 'css/jquery-ui.css" />
    <link rel="stylesheet" href="' . SITE_URL . 'css/font-awesome.min.css" />

    <script type="text/javascript" src="' . SITE_URL . 'js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="' . SITE_URL . 'js/jquery-ui-1.10.4.custom.min.js"></script>
    <script type="text/javascript" src="' . SITE_URL . 'js/functions.js?v=20160125" type="text/javascript"></script>
    <script type="text/javascript" src="' . SITE_URL . 'js/obj-events.js?v=20160125" type="text/javascript"></script>
    ');

    if (isset($_SESSION["account"]) && isset($_SESSION["logged_in"])) {
        echo '<style type="text/css" media="screen">
            html { padding-top: 72px !important; }
        </style>';
    }
}

function defaultBodyHTML($twig, $current_userinfo)
{
    if ($current_userinfo) {

        if (file_exists(ABSPATH . 'templates/' . CURRENT_TEMPLATE . '/adminbar.twig')) {
            $template = $twig->loadTemplate('adminbar.twig');
            echo $template->render(array(
                'site_name' => SITE_NAME,
                'site_url' => SITE_URL,
                'template_url' => TEMPLATE_URL,
                'account' => $current_userinfo,
            ));
        }
    }

    print('<div id="loader"><img src="' . SITE_URL . 'images/loading.gif" width="60" /><span>Cargando, espere por favor...</span><div id="window_background"></div></div>
<div id="dialog-modal" title="Error"></div>');

}

add_action('head', array("defaultHeadHTML", array($twig, $current_userinfo)));
add_action('body', array("defaultBodyHTML", array($twig, $current_userinfo)));

add_action('msgOutput', array(array($msg, "outputMsg"), array()));

?>