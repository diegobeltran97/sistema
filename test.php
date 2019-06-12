<?php

require(dirname( __FILE__ ) . '/includes/config.php');



$estados = get_dir_list( "estados", "", "", "" );

?>
<style type="text/css">
    table, td, th {
        border: 1px solid #000;
        border-collapse: collapse;
    }
    th { background: #eaeaea;}
</style>
<table>
    <tr>
        <th>ESTADOS</th>
        <th>MUNICIPIOS</th>
        <th>CIUDADES</th>
    </tr>
    <?php

    foreach ( $estados as $estado ) {

        echo "<tr>
    <td>{$estado["text"]}</td>
    <td>";
        $municipios = get_dir_list( "municipios", "", $estado["value"], "", true );

        foreach ( $municipios as $municipio )
            echo "$municipio <br />";

        echo "</td><td>";

        $ciudades = get_dir_list( "ciudades", "", $estado["value"], "", true );

        foreach ( $ciudades as $ciudad )
            echo "$ciudad <br />";

        echo "</td></tr>";
    }

    ?>

</table>