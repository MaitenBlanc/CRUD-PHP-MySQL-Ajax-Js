<?php

spl_autoload_register(function ($class) {

    $arch = __DIR__ . "/" . $class . ".php";
    $arch = str_replace("\\", "/", $arch);

    if (is_file($arch)) {
        require_once $arch;
    }
});
