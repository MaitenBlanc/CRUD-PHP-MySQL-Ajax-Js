<?php
require_once "../../config/app.php";
require_once "../views/inc/session_start.php";
require_once "../../autoload.php";

use app\controllers\userController;

if (isset($_POST['modulo_usuario'])) {

    $insUser = new userController();

    if ($_POST['modulo_usuario'] == "registrar") {
        echo $insUser->userRegisterController();
    }

    if ($_POST['modulo_usuario'] == "eliminar") {
        echo $insUser->deleteUserController();
    }

    if ($_POST['modulo_usuario'] == "actualizar") {
        echo $insUser->updateUserController();
    }

    if ($_POST['modulo_usuario'] == "actualizarFoto") {
        echo $insUser->updateUserPhotoController();
    }

    if ($_POST['modulo_usuario'] == "eliminarFoto") {
        echo $insUser->deleteUserPhotoController();
    }
} else {
    session_destroy();
    header("Location: " . APP_URL . "login/");
}
