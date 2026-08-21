<?php

include 'conexion.php'; //Conexion a la base de datos

session_start();
$usuario = $_SESSION['username'];
$rol = $_SESSION['rol'];
//echo "Bienvenido, " .$usuario; //Confirmo el usuario que ha iniciado sesion

if (!isset($usuario)) {
    header('Location: index.php'); //En caso de que no haya una sesion abierta, redirecciona al index
}

// Buscar temporada activa
$temporadaActiva = mysqli_query($conec, "SELECT id_temporada, temporada FROM temporadas WHERE estatus = 1 LIMIT 1");
$datosTemporada = mysqli_fetch_assoc($temporadaActiva);

// Buscar puente activo
$puenteActivo = mysqli_query($conec, "SELECT id_puente, puente FROM puentes WHERE estatus = 1 LIMIT 1");
$datosPuente = mysqli_fetch_assoc($puenteActivo);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CDN Boostrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Icoons de Boostrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Favicon (Icono de la pagina web)-->
    <link rel="shortcut icon" href="./assets/img/shop.svg" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <!-- Tipografia Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Averia+Libre:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&family=Glory:ital,wght@0,100..800;1,100..800&family=Macondo&family=Marcellus&display=swap" rel="stylesheet">
    <!-- Archivo CSS -->
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/style_index.css">

    <title>Tiendas Africam Safari</title>
</head>

<body>
    <header class="navbarindex">
        <a id="login" href="login.php">
            <img src="./assets/img/logo.png" width="130px" alt="130px" />
        </a>

        <div class="cerrarsesion">
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </header>

    <main>

        <div class="container px-2 py-3" id="custom-cards">
            <div class="row g-4">
                <!-- COLABORADORES-->
                <div class="col-3">
                    <a href="colaboradores.php" class="mi-card-link">
                        <div class="card mi-card rounded-4 shadow-lg">
                            <div class="mi-card-content">
                                <div class="mi-card-icon">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <h5>
                                    Colaboradores
                                </h5>
                            </div>
                        </div>
                    </a>
                </div>

                <?php if ($rol == 'admin' || $rol == 'user') { ?>

                    <!-- TIENDAS EXPLANADA -->
                    <div class="col-3">
                        <a href="tiendas_exp.php" class="mi-card-link">
                            <div class="card mi-card rounded-4 shadow-lg">
                                <div class="mi-card-content">
                                    <div class="mi-card-icon">
                                        <i class="bi bi-shop"></i>
                                    </div>
                                    <h5>
                                        Tiendas Explanada
                                    </h5>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- VENTAS POR PAX -->

                    <div class="col-3">
                        <?php
                        if ($datosTemporada) {
                            $linkVentas = "ventas_pax.php?id_temporada=" .$datosTemporada['id_temporada'];
                        } elseif ($datosPuente) {
                            $linkVentas = "ventas_puentes.php?id_puente=" .$datosPuente['id_puente'];
                        } else {
                            $linkVentas = "#";
                        }
                        ?>
                        <?php if ($datosTemporada || $datosPuente) { ?>

                            <a href="<?php echo $linkVentas; ?>" class="mi-card-link">
                            <?php } else { ?>
                                <a href="#" class="mi-card-link" data-bs-toggle="modal" data-bs-target="#modalVentas">

                                <?php } ?>
                                <div class="card mi-card rounded-4 shadow-lg">
                                    <div class="mi-card-content">
                                        <div class="mi-card-icon">
                                            <i class="bi bi-bar-chart-fill"></i>
                                        </div>
                                        <h5>
                                            Ventas por Pax
                                        </h5>
                                    </div>
                                </div>
                            </a>
                    </div>

                    <!-- COMISIONES -->
                    <div class="col-3">
                        <a href="comisiones.php" class="mi-card-link">
                            <div class="card mi-card rounded-4 shadow-lg">
                                <div class="mi-card-content">
                                    <div class="mi-card-icon">
                                        <i class="bi bi-cash-coin"></i>
                                    </div>
                                    <h5>
                                        Comisiones
                                    </h5>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </main>
    <!-- Footer -->
    <footer>
        <p class="text-center">Africam Safari SA de CV &copy; 2026</p>
    </footer>

    <!-- CDN Boostrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    <!-- Modal -->
    <div class="modal fade" id="modalVentas" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <form action="guardarTemporada.php" method="POST" onsubmit="return validarFormulario()">

                    <div class="modal-header">
                        <h5 class="modal-title">Selecciona tus parámetros</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <h5>Selecciona tipo</h5>
                        <button type="button" class="btn btn-primary" onclick="mostrarTemporadas()">Temporada</button>

                        <button type="button" class="btn btn-success" onclick="mostrarPuentes()">Puente</button>

                        <br><br>

                        <!-- Temporadas -->
                        <div id="temporadas" style="display:none;">
                            <h5>Temporadas</h5>
                            <button type="button" class="btn btn-outline-primary opcion-btn" onclick="setOpcion('Semana Santa', this)"> Semana Santa</button>

                            <button type="button" class="btn btn-outline-primary opcion-btn" onclick="setOpcion('Verano', this)">Verano</button>

                            <button type="button" class="btn btn-outline-primary opcion-btn" onclick="setOpcion('Diciembre', this)">Diciembre</button>
                        </div>

                        <!-- Puentes -->
                        <div id="puentes" style="display:none;">
                            <h5>Puentes</h5>
                            <input type="text" name="puente" id="puente" class="form-control" placeholder="Nombre del puente">
                        </div>

                        <br>

                        <!-- Fechas -->
                        <div id="fechas" style="display:none;">
                            <label>Fecha inicio:</label>
                            <input type="date" name="inicio" id="fechaInicio" class="form-control">
                            <label class="mt-2">Fecha fin:</label>
                            <input type="date" name="fin" id="fechaFin" class="form-control">
                        </div>

                        <!-- Input oculto -->
                        <input type="hidden" name="tipo" id="tipo">
                        <input type="hidden" name="temporada" id="temporada">
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success"> Ingresar </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function mostrarTemporadas() {

            document.getElementById("temporadas").style.display = "block";
            document.getElementById("puentes").style.display = "none";

            document.getElementById("fechas").style.display = "block";

            document.getElementById("tipo").value = "temporada";

            //limpiar puente
            document.getElementById("puente").value = "";
        }

        function mostrarPuentes() {

            document.getElementById("puentes").style.display = "block";
            document.getElementById("temporadas").style.display = "none";

            document.getElementById("fechas").style.display = "block";

            document.getElementById("tipo").value = "puente";

            //limpiar temporada
            document.getElementById("temporada").value = "";

            //quitar active
            document.querySelectorAll(".opcion-btn").forEach(btn => {
                btn.classList.remove("active");
            });
        }

        function setOpcion(valor, boton) {

            // guardar valor
            document.getElementById("temporada").value = valor;

            // mostrar fechas
            document.getElementById("fechas").style.display = "block";

            // quitar active
            document.querySelectorAll(".opcion-btn").forEach(btn => {
                btn.classList.remove("active");
            });

            // activar seleccionado
            boton.classList.add("active");

        }

        function validarFormulario() {

            let tipo = document.getElementById("tipo").value;

            let temporada = document.getElementById("temporada").value;
            let puente = document.getElementById("puente").value;

            let inicio = document.getElementById("fechaInicio").value;
            let fin = document.getElementById("fechaFin").value;

            //validar tipo
            if (tipo == "") {
                alert("Selecciona temporada o puente");
                return false;
            }

            //validar temporadas
            if (tipo == "temporada" && temporada == "") {
                alert("Selecciona una temporada");
                return false;
            }

            //validar puentes
            if (tipo == "puente" && puente == "") {
                alert("Ingresa el nombre del puente");
                return false;
            }

            //validar fechas
            if (inicio == "" || fin == "") {
                alert("Selecciona las fechas");
                return false;
            }

            return true;
        }
    </script>


</body>

</html>