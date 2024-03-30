<?php
require_once "../../config/app.php";
require_once "../views/inc/session_start.php";
require_once "../../autoload.php";

use app\controllers\searchController;

if (isset($_POST['modulo_buscador'])) {

    $insSearch = new searchController();

    if ($_POST['modulo_buscador'] == "buscar") {
        echo $insSearch->searchInitController();
    }

    if ($_POST['modulo_buscador'] == "eliminar") {
        echo $insSearch->searchDeleteController();
    }
} else {
    session_destroy();
    header("Location: " . APP_URL . "login/");
}
