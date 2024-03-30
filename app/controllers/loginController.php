<?php

namespace app\controllers;

use app\models\mainModel;

class loginController extends mainModel
{
    // Controlador inciar sesión
    public function loginController()
    {
        // Almacenar datos
        $user = $this->clearString($_POST['login_usuario']);
        $key = $this->clearString($_POST['login_clave']);


        // Verificar campos obligatorios
        if ($user == "" || $key == "") {
            echo "
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Ocurrió un error inesperado',
                        text: 'No has llenado todos los campos obligatorios.'
                    });
                </script>
            ";
        } else {
            // Verificar la integridad de los datos
            if ($this->verifyData("[a-zA-Z0-9]{4,20}", $user)) {
                echo "
                    <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Ocurrió un error inesperado',
                            text: 'El usuario no coincide con el formato solicitado.'
                        });
                    </script>
                ";
            } else {

                // Verificar la integridad de los datos
                if ($this->verifyData("[a-zA-Z0-9$@.-]{7,100}", $key)) {
                    echo "
                        <script>
                            Swal.fire({
                                icon: 'error',
                                title: 'Ocurrió un error inesperado',
                                text: 'La clave no coincide con el formato solicitado.'
                            });
                        </script>
                    ";
                } else {
                    // Verificar usuario
                    $checkUser = $this->runQuery("SELECT * FROM usuario WHERE usuario_usuario = '$user'");

                    if ($checkUser->rowCount() == 1) {
                        $checkUser = $checkUser->fetch();

                        if ($checkUser['usuario_usuario'] == $user && password_verify($key, $checkUser['usuario_clave'])) {
                            // Crear variables de sesión
                            $_SESSION['id'] = $checkUser['usuario_id'];
                            $_SESSION['name'] = $checkUser['usuario_nombre'];
                            $_SESSION['lastName'] = $checkUser['usuario_apellido'];
                            $_SESSION['user'] = $checkUser['usuario_usuario'];
                            $_SESSION['photo'] = $checkUser['usuario_foto'];

                            if (headers_sent()) {
                                echo "
                                    <script>
                                        window.location.href = '" . APP_URL . "dashboard/';
                                    </script>
                                ";
                            } else {
                                header("Location: " . APP_URL . "dashboard/");
                            }
                        } else {
                            echo "
                                <script>
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Ocurrió un error inesperado',
                                        text: 'Usuario o clave incorrectos.'
                                    });
                                </script>
                            ";
                        }
                    } else {
                        echo "
                            <script>
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Ocurrió un error inesperado',
                                    text: 'Usuario o clave incorrectos.'
                                });
                            </script>
                        ";
                    }
                }
            }
        }
    }

    // Controlador para cerrar la sesión
    public function logoutController()
    {
        session_destroy();

        if (headers_sent()) {
            echo "<script> window.location.href='" . APP_URL . "login/'; </script>";
        } else {
            header("Location: " . APP_URL . "login/");
        }
    }
}
