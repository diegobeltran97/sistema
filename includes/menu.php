<?php
function generateMenu($current_userinfo = array())
{

    $menu = new Menu();
    $access = isset($current_userinfo["access"]) ? $current_userinfo["access"] : array();

    /* POZOS */
    if (in_array("0-1", $access) || in_array("0-2", $access) || in_array("1-2", $access))
        $menu->createSection("Pozos", "fa-tint");

    $menu->addButton("Buscar Pozos", "wells.php?action=search", "Pozos", in_array("0-1", $access));
    $menu->addButton("Nuevo Pozo", "wells.php?action=create", "Pozos", in_array("0-2", $access));
    $menu->addButton("Nuevo Trabajo", "wells.php?action=new_task", "Pozos", in_array("1-2", $access));

    /* CLIENTES */
    if (in_array("2-1", $access) || in_array("2-2", $access))
        $menu->createSection("Clientes", "fa-list-alt");

    $menu->addButton("Listado de Clientes", "customers.php", "Clientes", in_array("2-1", $access));
    $menu->addButton("Nuevo Cliente", "customers.php?action=create", "Clientes", in_array("2-2", $access));

    /* USUARIOS */
    if (in_array("3-1", $access) || in_array("3-2", $access))
        $menu->createSection("Usuarios", "fa-users");

    $menu->addButton("Listado de Usuarios", "users.php", "Usuarios", in_array("3-1", $access));
    $menu->addButton("Crear Usuario", "users.php?action=create", "Usuarios", in_array("3-2", $access));

    /* AJUSTES DEL SISTEMA */
    if (in_array("4-1", $access))
        $menu->createSection("Ajustes del Sistema", "fa-cog");

    $menu->addButton("Importar Datos", "data-transfer.php", "Ajustes del Sistema", in_array("4-1", $access));
    $menu->addButton("Configuración General", "adjustments.php", "Ajustes del Sistema", in_array("4-1", $access));

    return $menu->menu;
}

class Menu
{

    var $menu = array();

    function addButton($text, $url, $section = '#', $show = true)
    {
        if (!$show)
            return false;

        if ($section == '#' || empty($section)) {
            $last_section = key(array_slice($this->menu, -1, 1, TRUE));
            $section = !empty($last_section) ? $last_section : 'MENU';
        }
        $url = SITE_URL . $url;
        $current_url = getCurrentUrl();
        $class = $url == $current_url ? 'class="selected"' : '';

        $this->menu[$section]["content"][] = array(
            'text' => $text,
            'url' => $url,
            'class' => $class,
        );

        return true;
    }

    function createSection($section, $icon = "", $show = true)
    {
        if (!$show)
            return false;

        $this->menu[$section] = array();
        $this->menu[$section]["icon"] = $icon;

        return true;
    }

}