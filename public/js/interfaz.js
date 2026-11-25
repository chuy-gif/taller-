/* PequeÃ±as ayudas visuales. La validaciÃ³n y los permisos se aplican en PHP. */
document.addEventListener("DOMContentLoaded", function () {
    const botonMenu = document.querySelector(".boton-menu");
    const menu = document.querySelector(".barra-lateral");

    const fondoMenu = document.querySelector(".fondo-menu");

    function cambiarMenu(abierto) {
        menu.classList.toggle("abierta", abierto);
        botonMenu.setAttribute("aria-expanded", String(abierto));
        fondoMenu.hidden = !abierto;
    }

    if (botonMenu && menu && fondoMenu) {
        botonMenu.addEventListener("click", function () {
            const abrir = !menu.classList.contains("abierta");
            cambiarMenu(abrir);
            if (abrir) menu.querySelector("nav a")?.focus();
        });
        fondoMenu.addEventListener("click", function () {
            cambiarMenu(false);
            botonMenu.focus();
        });
        document.addEventListener("keydown", function (evento) {
            if (evento.key === "Escape" && menu.classList.contains("abierta")) {
                cambiarMenu(false);
                botonMenu.focus();
            }
        });
    }

    document.querySelectorAll(".ver-contrasena").forEach(function (boton) {
        boton.addEventListener("click", function () {
            const campo = document.getElementById(
                boton.getAttribute("aria-controls"),
            );
            const mostrar = campo.type === "password";
            campo.type = mostrar ? "text" : "password";
            boton.textContent = mostrar ? "Ocultar" : "Mostrar";
            boton.setAttribute("aria-pressed", String(mostrar));
        });
    });

    document
        .querySelectorAll("form[data-confirmar]")
        .forEach(function (formulario) {
            formulario.addEventListener("submit", function (evento) {
                if (!window.confirm(formulario.dataset.confirmar)) {
                    evento.preventDefault();
                }
            });
        });

    // Facilita elegir una venta del cliente correcto; PHP vuelve a comprobarlo.
    const cliente = document.getElementById("actividad-cliente");
    const venta = document.getElementById("actividad-venta");
    if (cliente && venta) {
        function filtrarVentas() {
            Array.from(venta.options).forEach(function (opcion) {
                const ocultar =
                    opcion.value !== "" &&
                    opcion.dataset.cliente !== cliente.value;
                opcion.hidden = ocultar;
                opcion.disabled = ocultar;
            });
            if (venta.selectedOptions[0]?.disabled) venta.value = "";
        }
        cliente.addEventListener("change", filtrarVentas);
        filtrarVentas();
    }
});
