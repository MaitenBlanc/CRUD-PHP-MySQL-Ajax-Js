<?php

namespace app\controllers;

use app\models\mainModel;

class userController extends mainModel
{

    // Controlador para registrar un usuario
    public function userRegisterController()
    {
        // Almacenar datos
        $name = $this->clearString($_POST['usuario_nombre']);
        $last_name = $this->clearString($_POST['usuario_apellido']);
        $user = $this->clearString($_POST['usuario_usuario']);
        $email = $this->clearString($_POST['usuario_email']);
        $key1 = $this->clearString($_POST['usuario_clave_1']);
        $key2 = $this->clearString($_POST['usuario_clave_2']);

        // Verificar campos obligatorios
        if ($name == "" || $last_name == "" || $user == "" || $key1 == "" || $key2 == "") {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado.",
                "text" => "No has llenado todos los campos obligatorios.",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        // Verificar la integridad de los datos
        if ($this->verifyData("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}", $name)) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado.",
                "text" => "El nombre no coincide con el formato solicitado.",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        if ($this->verifyData("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}", $last_name)) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado.",
                "text" => "El apellido no coincide con el formato solicitado.",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        if ($this->verifyData("[a-zA-Z0-9]{4,20}", $user)) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado.",
                "text" => "El usuario no coincide con el formato solicitado.",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        if ($this->verifyData("[a-zA-Z0-9$@.-]{7,100}", $key1) || $this->verifyData("[a-zA-Z0-9$@.-]{7,100}", $key2)) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado.",
                "text" => "Las claves no coincide con el formato solicitado.",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        // Verificar email
        if ($email != "") {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $checkEmail = $this->runQuery("SELECT usuario_email FROM usuario WHERE usuario_email='$email'");

                if ($checkEmail->rowCount() > 0) {
                    $alert = [
                        "type" => "simple",
                        "title" => "Ocurrió un error inesperado.",
                        "text" => "El correo electrónico ya se encuentra registrado.",
                        "icon" => "error"
                    ];
                    return json_encode($alert);
                    exit();
                }
            } else {
                $alert = [
                    "type" => "simple",
                    "title" => "Ocurrió un error inesperado.",
                    "text" => "Ha ingresado un correo electrónico no válido.",
                    "icon" => "error"
                ];
                return json_encode($alert);
                exit();
            }
        }

        // Verificar claves
        if ($key1 != $key2) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado.",
                "text" => "Las claves no coinciden.",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        } else {
            $key = password_hash($key1, PASSWORD_BCRYPT, ["cost" => 10]);
        }

        // Verificar usuario
        $checkUser = $this->runQuery("SELECT usuario_usuario FROM usuario WHERE usuario_usuario='$user'");
        if ($checkUser->rowCount() > 0) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado.",
                "text" => "El usuario ya se encuentra registrado.",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        // Directorio de imágenes
        $imgDir = "../views/photos/";

        // Comprobar si es imagen
        if ($_FILES['usuario_foto']['name'] != "" && $_FILES['usuario_foto']['size'] > 0) {

            // Crear directorio
            if (!file_exists($imgDir)) {
                if (!mkdir($imgDir, 0777)) {
                    $alert = [
                        "type" => "simple",
                        "title" => "Ocurrió un error inesperado.",
                        "text" => "Error al crear el directorio.",
                        "icon" => "error"
                    ];
                    return json_encode($alert);
                    exit();
                }
            }

            // Verificar formato de imágenes
            if (
                mime_content_type($_FILES['usuario_foto']['tmp_name']) != "image/jpeg"
                && mime_content_type($_FILES['usuario_foto']['tmp_name']) != "image/png"
            ) {
                $alert = [
                    "type" => "simple",
                    "title" => "Ocurrió un error inesperado.",
                    "text" => "La imagen tiene un formato no permitido.",
                    "icon" => "error"
                ];
                return json_encode($alert);
                exit();
            }

            // Verificar peso imagen
            if (($_FILES['usuario_foto']['size'] / 1024) > 5120) {
                $alert = [
                    "type" => "simple",
                    "title" => "Ocurrió un error inesperado.",
                    "text" => "La imagen supera el peso permitido.",
                    "icon" => "error"
                ];
                return json_encode($alert);
                exit();
            }

            // Definir nombre de la foto
            $photo = str_ireplace(" ", "_", $name);
            $photo = $photo . "_" . rand(0, 100);

            // Extensión de la imagen
            switch (mime_content_type($_FILES['usuario_foto']['tmp_name'])) {
                case 'image/jpeg':
                    $photo = $photo . ".jpg";
                    break;
                case 'image/png':
                    $photo = $photo . ".png";
                    break;
            }

            chmod($imgDir, 0777);

            // Mover imagen al directorio
            if (!move_uploaded_file($_FILES['usuario_foto']['tmp_name'], $imgDir . $photo)) {
                $alert = [
                    "type" => "simple",
                    "title" => "Ocurrió un error inesperado.",
                    "text" => "No se pudo subir la imagen al sistema en este momento.",
                    "icon" => "error"
                ];
                return json_encode($alert);
                exit();
            }
        } else {
            $photo = "";
        }


        $userDataReg = [
            [
                "campo_nombre" => "usuario_nombre",
                "campo_marcador" => ":Nombre",
                "campo_valor" => $name
            ],
            [
                "campo_nombre" => "usuario_apellido",
                "campo_marcador" => ":Apellido",
                "campo_valor" => $last_name
            ],
            [
                "campo_nombre" => "usuario_email",
                "campo_marcador" => ":Email",
                "campo_valor" => $email
            ],
            [
                "campo_nombre" => "usuario_usuario",
                "campo_marcador" => ":USuario",
                "campo_valor" => $user
            ],
            [
                "campo_nombre" => "usuario_clave",
                "campo_marcador" => ":Clave",
                "campo_valor" => $key
            ],
            [
                "campo_nombre" => "usuario_foto",
                "campo_marcador" => ":Foto",
                "campo_valor" => $photo
            ],
            [
                "campo_nombre" => "usuario_creado",
                "campo_marcador" => ":Creado",
                "campo_valor" => date("Y-m-d H:i:s")
            ],
            [
                "campo_nombre" => "usuario_actualizado",
                "campo_marcador" => ":Actualizado",
                "campo_valor" => date("Y-m-d H:i:s")
            ]
        ];

        // Guardar consulta en la tabla
        $registerUser = $this->saveData("usuario", $userDataReg);

        if ($registerUser->rowCount() == 1) {
            $alert = [
                "type" => "limpiar",
                "title" => "Usuario registrado.",
                "text" => "El usuario " . $name . " " . $last_name . " se ha registrado con exito.",
                "icon" => "success"
            ];
        } else {

            if (is_file($imgDir . $photo)) {
                chmod($imgDir . $photo, 0777);
                unlink($imgDir . $photo);
            }

            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado.",
                "text" => "No se pudo registrar el usuario, por favor intente nuevamente.",
                "icon" => "error"
            ];
        }
        return json_encode($alert);
    }


    // Controlador para listar un usuario
    public function listUserController($page, $records, $url, $search)
    {
        $page = $this->clearString($page);
        $records = $this->clearString($records);

        $url = $this->clearString($url);
        $url = APP_URL . $url . "/";

        $search = $this->clearString($search);

        $table = "";

        $page = (isset($page) && $page > 0) ? (int) $page : 1;
        $home = ($page > 0) ? (($page * $records) - $records) : 0;

        if (isset($search) && $search != "") {
            $dataQuery = "SELECT * FROM usuario WHERE ((usuario_id!='" . $_SESSION['id'] . "' AND usuario_id!='1') AND (usuario_nombre LIKE '%$search%' OR usuario_apellido LIKE '%$search%' OR usuario_email LIKE '%$search%' OR usuario_usuario LIKE '%$search%')) ORDER BY usuario_nombre ASC LIMIT $home,$records";

            $totalQuery = "SELECT COUNT(usuario_id) FROM usuario WHERE ((usuario_id!='" . $_SESSION['id'] . "' AND usuario_id!='1') AND (usuario_nombre LIKE '%$search%' OR usuario_apellido LIKE '%$search%' OR usuario_email LIKE '%$search%' OR usuario_usuario LIKE '%$search%'))";
        } else {
            $dataQuery = "SELECT * FROM usuario WHERE usuario_id!='" . $_SESSION['id'] . "' AND usuario_id!='1' ORDER BY usuario_nombre ASC LIMIT $home,$records";

            $totalQuery = "SELECT COUNT(usuario_id) FROM usuario WHERE usuario_id!='" . $_SESSION['id'] . "' AND usuario_id!='1'";
        }

        $data = $this->runQuery($dataQuery);
        $data = $data->fetchAll();

        $total = $this->runQuery($totalQuery);
        $total = (int) $total->fetchColumn();

        $pageNumbers = ceil($total / $records);     // Con ceil se redondea al entero próximo

        $table .= '
        <div class="table-container">
        <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
            <thead>
                <tr>
                    <th class="has-text-centered">#</th>
                    <th class="has-text-centered">Nombre</th>
                    <th class="has-text-centered">Usuario</th>
                    <th class="has-text-centered">Email</th>
                    <th class="has-text-centered">Creado</th>
                    <th class="has-text-centered">Actualizado</th>
                    <th class="has-text-centered" colspan="3">Opciones</th>
                </tr>
            </thead>
            <tbody>
        ';

        if ($total >= 1 && $page <= $pageNumbers) {
            $counter = $home + 1;
            $pagHome = $home + 1;
            foreach ($data as $rows) {
                $table .= '
                <tr class="has-text-centered" >
                <td>' . $counter . '</td>
                <td>' . $rows['usuario_nombre'] . ' ' . $rows['usuario_apellido'] . '</td>
                <td>' . $rows['usuario_usuario'] . '</td>
                <td>' . $rows['usuario_email'] . '</td>
                <td>' . date("d-m-Y  h:i:s A", strtotime($rows['usuario_creado'])) . '</td>
                <td>' . date("d-m-Y  h:i:s A", strtotime($rows['usuario_actualizado'])) . '</td>
                <td>
                    <a href="' . APP_URL . 'userPhoto/' . $rows['usuario_id'] . '/" class="button is-info is-rounded is-small">Foto</a>
                </td>
                <td>
                    <a href="' . APP_URL . 'userUpdate/' . $rows['usuario_id'] . '/" class="button is-success is-rounded is-small">Actualizar</a>
                </td>
                <td>
                    <form class="FormularioAjax" action="' . APP_URL . 'app/ajax/usuarioAjax.php" method="POST" autocomplete="off" >

                        <input type="hidden" name="modulo_usuario" value="eliminar">
                        <input type="hidden" name="usuario_id" value="' . $rows['usuario_id'] . '">

                        <button type="submit" class="button is-danger is-rounded is-small">Eliminar</button>
                    </form>
                </td>
            </tr>
                ';
                $counter++;
            }

            $pagEnd = $counter - 1;
        } else {
            if ($total >= 1) {
                $table .= '
                <tr class="has-text-centered" >
                <td colspan="7">
                    <a href="' . $url . '1/" class="button is-link is-rounded is-small mt-4 mb-4">
                        Haga clic acá para recargar el listado
                    </a>
                </td>
            </tr>
                ';
            } else {
                $table .= '
                <tr class="has-text-centered" >
                <td colspan="7">
                    No hay registros en el sistema
                </td>
            </tr>
                ';
            }
        }

        $table .= '</tbody></table></div>';

        // Paginación
        if ($total > 0 && $page <= $pageNumbers) {
            $table .= '<p class="has-text-right">Mostrando usuarios <strong>' . $pagHome . '</strong> al <strong>' . $pagEnd . '</strong> de un <strong>total de ' . $total . '</strong></p>';

            $table .= $this->tablePagination($page, $pageNumbers, $url, 7);
        }
        return $table;
    }


    // Controlador para eliminar un usuario
    public function deleteUserController()
    {
        $id = $this->clearString($_POST['usuario_id']);

        if ($id == 1) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado",
                "text" => "No podemos eliminar el usuario principal del sistema",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        // Verificar usuario
        $data = $this->runQuery("SELECT * FROM usuario WHERE usuario_id='$id'");
        if ($data->rowCount() <= 0) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado",
                "text" => "No hemos encontrado el usuario en el sistema",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        } else {
            $data = $data->fetch();
        }

        $deleteUser = $this->deleteData("usuario", "usuario_id", $id);

        if ($deleteUser->rowCount() == 1) {
            if (is_file("../views/photos/" . $data['usuario_foto'])) {
                chmod("../views/photos/" . $data['usuario_foto'], 0777);
                unlink("../views/photos/" . $data['usuario_foto']);
            }

            $alert = [
                "type" => "recargar",
                "title" => "Usuario eliminado.",
                "text" => "El usuario " . $data['usuario_nombre'] . " " . $data['usuario_apellido'] . " se eliminó con exito.",
                "icon" => "success"
            ];
        } else {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado.",
                "text" => "No se pudo eliminar el usuario " . $data['usuario_nombre'] . " " . $data['usuario_apellido'] . ", por favor intente nuevamente.",
                "icon" => "error"
            ];
        }

        return json_encode($alert);
    }


    // Controlador para actualizar un usuario
    public function updateUserController()
    {
        $id = $this->clearString($_POST['usuario_id']);

        # Verificando usuario #
        $data = $this->runQuery("SELECT * FROM usuario WHERE usuario_id='$id'");
        if ($data->rowCount() <= 0) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado",
                "text" => "No hemos encontrado el usuario en el sistema",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        } else {
            $data = $data->fetch();
        }

        $adminUser = $this->clearString($_POST['administrador_usuario']);
        $adminKey = $this->clearString($_POST['administrador_clave']);

        # Verificando campos obligatorios admin #
        if ($adminUser == "" || $adminKey == "") {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado",
                "text" => "No ha llenado todos los campos que son obligatorios, que corresponden a su USUARIO y CLAVE",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        if ($this->verifyData("[a-zA-Z0-9]{4,20}", $adminUser)) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado",
                "text" => "Su USUARIO no coincide con el formato solicitado",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        if ($this->verifyData("[a-zA-Z0-9$@.-]{7,100}", $adminKey)) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado",
                "text" => "Su CLAVE no coincide con el formato solicitado",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        # Verificando administrador #
        $checkAdmin = $this->runQuery("SELECT * FROM usuario WHERE usuario_usuario='$adminUser' AND usuario_id='" . $_SESSION['id'] . "'");
        if ($checkAdmin->rowCount() == 1) {

            $checkAdmin = $checkAdmin->fetch();

            if ($checkAdmin['usuario_usuario'] != $adminUser || !password_verify($adminKey, $checkAdmin['usuario_clave'])) {

                $alert = [
                    "type" => "simple",
                    "title" => "Ocurrió un error inesperado",
                    "text" => "USUARIO o CLAVE de administrador incorrectos",
                    "icon" => "error"
                ];
                return json_encode($alert);
                exit();
            }
        } else {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado",
                "text" => "USUARIO o CLAVE de administrador incorrectos",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }


        # Almacenando datos#
        $name = $this->clearString($_POST['usuario_nombre']);
        $last_name = $this->clearString($_POST['usuario_apellido']);

        $user = $this->clearString($_POST['usuario_usuario']);
        $email = $this->clearString($_POST['usuario_email']);
        $key1 = $this->clearString($_POST['usuario_clave_1']);
        $key2 = $this->clearString($_POST['usuario_clave_2']);

        # Verificando campos obligatorios #
        if ($name == "" || $last_name == "" || $user == "") {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado",
                "text" => "No has llenado todos los campos que son obligatorios",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        # Verificando integridad de los datos #
        if ($this->verifyData("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}", $name)) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado",
                "text" => "El NOMBRE no coincide con el formato solicitado",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        if ($this->verifyData("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}", $last_name)) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado",
                "text" => "El APELLIDO no coincide con el formato solicitado",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        if ($this->verifyData("[a-zA-Z0-9]{4,20}", $user)) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado",
                "text" => "El USUARIO no coincide con el formato solicitado",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        # Verificando email #
        if ($email != "" && $data['usuario_email'] != $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $checkEmail = $this->runQuery("SELECT usuario_email FROM usuario WHERE usuario_email='$email'");
                if ($checkEmail->rowCount() > 0) {
                    $alert = [
                        "type" => "simple",
                        "title" => "Ocurrió un error inesperado",
                        "text" => "El EMAIL que acaba de ingresar ya se encuentra registrado en el sistema, por favor verifique e intente nuevamente",
                        "icon" => "error"
                    ];
                    return json_encode($alert);
                    exit();
                }
            } else {
                $alert = [
                    "type" => "simple",
                    "title" => "Ocurrió un error inesperado",
                    "text" => "Ha ingresado un correo electrónico no valido",
                    "icon" => "error"
                ];
                return json_encode($alert);
                exit();
            }
        }

        # Verificando claves #
        if ($key1 != "" || $key2 != "") {
            if ($this->verifyData("[a-zA-Z0-9$@.-]{7,100}", $key1) || $this->verifyData("[a-zA-Z0-9$@.-]{7,100}", $key2)) {

                $alert = [
                    "type" => "simple",
                    "title" => "Ocurrió un error inesperado",
                    "text" => "Las CLAVES no coinciden con el formato solicitado",
                    "icon" => "error"
                ];
                return json_encode($alert);
                exit();
            } else {
                if ($key1 != $key2) {

                    $alert = [
                        "type" => "simple",
                        "title" => "Ocurrió un error inesperado",
                        "text" => "Las nuevas CLAVES que acaba de ingresar no coinciden, por favor verifique e intente nuevamente",
                        "icon" => "error"
                    ];
                    return json_encode($alert);
                    exit();
                } else {
                    $key = password_hash($key1, PASSWORD_BCRYPT, ["cost" => 10]);
                }
            }
        } else {
            $key = $data['usuario_clave'];
        }

        # Verificando usuario #
        if ($data['usuario_usuario'] != $user) {
            $checkUser = $this->runQuery("SELECT usuario_usuario FROM usuario WHERE usuario_usuario='$user'");
            if ($checkUser->rowCount() > 0) {
                $alert = [
                    "type" => "simple",
                    "title" => "Ocurrió un error inesperado",
                    "text" => "El USUARIO ingresado ya se encuentra registrado, por favor elija otro",
                    "icon" => "error"
                ];
                return json_encode($alert);
                exit();
            }
        }

        $userDataUpdate = [
            [
                "campo_nombre" => "usuario_nombre",
                "campo_marcador" => ":Nombre",
                "campo_valor" => $name
            ],
            [
                "campo_nombre" => "usuario_apellido",
                "campo_marcador" => ":Apellido",
                "campo_valor" => $last_name
            ],
            [
                "campo_nombre" => "usuario_usuario",
                "campo_marcador" => ":Usuario",
                "campo_valor" => $user
            ],
            [
                "campo_nombre" => "usuario_email",
                "campo_marcador" => ":Email",
                "campo_valor" => $email
            ],
            [
                "campo_nombre" => "usuario_clave",
                "campo_marcador" => ":Clave",
                "campo_valor" => $key
            ],
            [
                "campo_nombre" => "usuario_actualizado",
                "campo_marcador" => ":Actualizado",
                "campo_valor" => date("Y-m-d H:i:s")
            ]
        ];

        $condition = [
            "condicion_campo" => "usuario_id",
            "condicion_marcador" => ":ID",
            "condicion_valor" => $id
        ];

        if ($this->updateData("usuario", $userDataUpdate, $condition)) {

            if ($id == $_SESSION['id']) {
                $_SESSION['nombre'] = $name;
                $_SESSION['apellido'] = $last_name;
                $_SESSION['usuario'] = $user;
            }

            $alert = [
                "type" => "recargar",
                "title" => "Usuario actualizado",
                "text" => "Los datos del usuario " . $data['usuario_nombre'] . " " . $data['usuario_apellido'] . " se actualizaron correctamente",
                "icon" => "success"
            ];
        } else {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado",
                "text" => "No hemos podido actualizar los datos del usuario " . $data['usuario_nombre'] . " " . $data['usuario_apellido'] . ", por favor intente nuevamente",
                "icon" => "error"
            ];
        }

        return json_encode($alert);
    }


    // Controlador para actualizar foto de un usuario
    public function updateUserPhotoController()
    {
        $id = $this->clearString($_POST['usuario_id']);

        # Verificando usuario #
        $data = $this->runQuery("SELECT * FROM usuario WHERE usuario_id='$id'");
        if ($data->rowCount() <= 0) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado",
                "text" => "No hemos encontrado el usuario en el sistema",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        } else {
            $data = $data->fetch();
        }

        // Directorio de imágenes
        $imgDir = "../views/photos/";

        // Comprobar si es imagen
        if ($_FILES['usuario_foto']['name'] == "" && $_FILES['usuario_foto']['size'] <= 0) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado",
                "text" => "No ha seleccionado una foto válida para el usuario.",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        // Crear directorio
        if (!file_exists($imgDir)) {
            if (!mkdir($imgDir, 0777)) {
                $alert = [
                    "type" => "simple",
                    "title" => "Ocurrió un error inesperado.",
                    "text" => "Error al crear el directorio.",
                    "icon" => "error"
                ];
                return json_encode($alert);
                exit();
            }
        }

        // Verificar formato de imágenes
        if (
            mime_content_type($_FILES['usuario_foto']['tmp_name']) != "image/jpeg"
            && mime_content_type($_FILES['usuario_foto']['tmp_name']) != "image/png"
        ) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado.",
                "text" => "La imagen tiene un formato no permitido.",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        // Verificar peso imagen
        if (($_FILES['usuario_foto']['size'] / 1024) > 5120) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado.",
                "text" => "La imagen supera el peso permitido.",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        // Definir nombre de la foto
        if ($data['usuario_foto'] != "") {
            $photo = explode(".", $data['usuario_foto']);
            $photo = $photo[0];
        } else {
            $photo = str_ireplace(" ", "_", $data['usuario_nombre']);
            $photo = $photo . "_" . rand(0, 100);
        }

        // Extensión de la imagen
        switch (mime_content_type($_FILES['usuario_foto']['tmp_name'])) {
            case 'image/jpeg':
                $photo = $photo . ".jpg";
                break;
            case 'image/png':
                $photo = $photo . ".png";
                break;
        }

        chmod($imgDir, 0777);       // Permisos de lectura y escritura

        // Mover imagen al directorio
        if (!move_uploaded_file($_FILES['usuario_foto']['tmp_name'], $imgDir . $photo)) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado.",
                "text" => "No se pudo subir la imagen al sistema en este momento.",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        // Eliminar la imagen anterior
        if (is_file($imgDir . $data['usuario_foto']) && $data['usuario_foto'] != $photo) {
            chmod($imgDir . $data['usuario_foto'], 0777);
            unlink($imgDir . $data['usuario_foto']);
        }

        $userDataUpdate = [
            [
                "campo_nombre" => "usuario_foto",
                "campo_marcador" => ":Foto",
                "campo_valor" => $photo
            ],
            [
                "campo_nombre" => "usuario_actualizado",
                "campo_marcador" => ":Actualizado",
                "campo_valor" => date("Y-m-d H:i:s")
            ]
        ];

        $condition = [
            "condicion_campo" => "usuario_id",
            "condicion_marcador" => ":ID",
            "condicion_valor" => $id
        ];

        if ($this->updateData("usuario", $userDataUpdate, $condition)) {

            if ($id == $_SESSION['id']) {
                $_SESSION['foto'] = $photo;
            }

            $alert = [
                "type" => "recargar",
                "title" => "Foto actualizada",
                "text" => "La foto del usuario " . $data['usuario_nombre'] . " " . $data['usuario_apellido'] . " se actualizó correctamente.",
                "icon" => "success"
            ];
        } else {
            $alert = [
                "type" => "recargar",
                "title" => "Foto actualizada",
                "text" => "No hemos podido actualizar algunos datos del usuario " . $data['usuario_nombre'] . " " . $data['usuario_apellido'] . ", sin embargo la foto se actualizó con éxito.",
                "icon" => "warning"
            ];
        }
        return json_encode($alert);
    }


    // Controlador para eliminar foto de un usuario
    public function deleteUserPhotoController()
    {
        $id = $this->clearString($_POST['usuario_id']);

        # Verificando usuario #
        $data = $this->runQuery("SELECT * FROM usuario WHERE usuario_id='$id'");
        if ($data->rowCount() <= 0) {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado",
                "text" => "No hemos encontrado el usuario en el sistema",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        } else {
            $data = $data->fetch();
        }

        // Directorio de imágenes
        $imgDir = "../views/photos/";

        chmod($imgDir, 0777);

        if (is_file($imgDir . $data['usuario_foto'])) {

            chmod($imgDir . $data['usuario_foto'], 0777);

            if (!unlink($imgDir . $data['usuario_foto'])) {
                $alert = [
                    "type" => "simple",
                    "title" => "Ocurrió un error inesperado",
                    "text" => "Error al intentar eliminar la foto del usuario, por favor intente nuevamente.",
                    "icon" => "error"
                ];
                return json_encode($alert);
                exit();
            }
        } else {
            $alert = [
                "type" => "simple",
                "title" => "Ocurrió un error inesperado",
                "text" => "No hemos encontrado la foto del usuario en el sistema.",
                "icon" => "error"
            ];
            return json_encode($alert);
            exit();
        }

        $userDataUpdate = [
            [
                "campo_nombre" => "usuario_foto",
                "campo_marcador" => ":Foto",
                "campo_valor" => ""
            ],
            [
                "campo_nombre" => "usuario_actualizado",
                "campo_marcador" => ":Actualizado",
                "campo_valor" => date("Y-m-d H:i:s")
            ]
        ];

        $condition = [
            "condicion_campo" => "usuario_id",
            "condicion_marcador" => ":ID",
            "condicion_valor" => $id
        ];

        if ($this->updateData("usuario", $userDataUpdate, $condition)) {

            if ($id == $_SESSION['id']) {
                $_SESSION['foto'] = "";
            }

            $alert = [
                "type" => "recargar",
                "title" => "Foto eliminada",
                "text" => "La foto del usuario " . $data['usuario_nombre'] . " " . $data['usuario_apellido'] . " se eliminó correctamente.",
                "icon" => "success"
            ];
        } else {
            $alert = [
                "type" => "recargar",
                "title" => "Foto eliminada",
                "text" => "No hemos podido actualizar algunos datos del usuario " . $data['usuario_nombre'] . " " . $data['usuario_apellido'] . ", sin embargo la foto se eliminó con éxito.",
                "icon" => "warning"
            ];
        }
        return json_encode($alert);
    }
}
