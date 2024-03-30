const forms_ajax = document.querySelectorAll(".FormularioAjax");

forms_ajax.forEach((forms) => {
    forms.addEventListener("submit", function (e) {
        e.preventDefault();

        Swal.fire({
            title: "¿Estás seguro?",
            text: "¿Quieres realizar la acción solicitada?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, realizar.",
            cancelButtonText: "No, cancelar."
        }).then((result) => {
            if (result.isConfirmed) {
                let data = new FormData(this);
                let method = this.getAttribute("method");
                let action = this.getAttribute("action");

                let headers = new Headers();

                let config = {
                    method: method,
                    headers: headers,
                    mode: 'cors',
                    cache: 'no-cache',
                    body: data
                }

                fetch(action, config)
                    .then(response => response.json())
                    .then(response => {
                        return alerts_ajax(response);
                    });
            }
        });
    });
});


function alerts_ajax(alert) {
    if (alert.type == "simple") {
        Swal.fire({
            icon: alert.icon,
            title: alert.title,
            text: alert.text,
            confirmButtonText: 'Aceptar'
        });

    } else if (alert.type == "recargar") {
        Swal.fire({
            icon: alert.icon,
            title: alert.title,
            text: alert.text,
            confirmButtonText: 'Aceptar'
        }).then((result) => {
            if (result.isConfirmed) {
                location.reload();
            }
        });

    } else if (alert.type == "limpiar") {
        Swal.fire({
            icon: alert.icon,
            title: alert.title,
            text: alert.text,
            confirmButtonText: 'Aceptar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.querySelector(".FormularioAjax").reset();
            }
        });

    } else if (alert.type == "redireccionar") {
        window.location.href = alert.url;
    }
}


// Botón para cerrar sesión
let btnExit = document.getElementById("btn_exit");

btnExit.addEventListener("click", function (e) {
    e.preventDefault();

    Swal.fire({
        title: "¿Quieres salir del sistema?",
        text: "La sesión actual se cerrará y saldrás del sistema.",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, salir.",
        cancelButtonText: "No, cancelar."
    }).then((result) => {
        if (result.isConfirmed) {
            let url = this.getAttribute("href");
            window.location.href = url;
        }
    });
});