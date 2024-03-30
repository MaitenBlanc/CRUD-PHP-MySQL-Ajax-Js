<?php

namespace app\controllers;

use app\models\mainModel;

class searchController extends mainModel
{
    // Controlador módulos de búsqueda
    public function searchModuleController($module)
    {
        $modulesList = ['userSearch'];

        if (in_array($module, $modulesList)) {
            return false;
        } else {
            return true;
        }
    }


    // Controlador para iniciar la búsqueda
    public function searchInitController()
    {
        $url = $this->clearString($_POST['modulo_url']);
        $text = $this->clearString($_POST['txt_buscador']);

        if ($this->searchModuleController($url)) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado.",
                "text" => "No podemos procesar la petición en este momento.",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        if ($text == "") {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado.",
                "text" => "Introduce un criterio de búsqueda.",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        if ($this->verifyData("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{1,30}", $text)) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado.",
                "text" => "El término de búsqueda no coincide con el formato solicitado.",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        $_SESSION[$url] = $text;

        $alert = [
            "type" => "redireccionar",
            "url" => APP_URL . $url . "/"
        ];

        return json_encode($alert);
    }


    // Controlador para eliminar la búsqueda
    public function searchDeleteController()
    {
        $url = $this->clearString($_POST['modulo_url']);

        if ($this->searchModuleController($url)) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado.",
                "text" => "No podemos procesar la petición en este momento.",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        unset($_SESSION[$url]);

        $alert = [
            "type" => "redireccionar",
            "url" => APP_URL . $url . "/"
        ];

        return json_encode($alert);
    }
}
