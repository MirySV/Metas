<?php

include 'conexion.php'; //Conexion a la base de datos

session_start();
$usuario = $_SESSION['username'];
$rol = $_SESSION['rol'];
//echo "Bienvenido, " .$usuario; //Confirmo el usuario que ha iniciado sesion
if (!isset($usuario)) {
    header('Location: index.php'); //En caso de que no haya una sesion abierta, redirecciona al index
}
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
    <style>
        main {
            flex: 1;
        }

        .navbarindex {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin: 0px 0px 10px 0px;
            padding: 65px 0px 55px 0px;
        }

        .cerrarsesion {
            font-family: "Macondo", cursive;
            font-size: 22px;
            position: absolute;
            right: 20px;
            top: 40px;
        }

        .cerrarsesion a {
            text-decoration: none;
            color: #330a04;
        }

        .cerrarsesion a:hover {
            color: #ffffff;
        }

        #login {
            position: absolute;
            left: 20px;
        }

        #custom-cards {
            margin: 30px 15px 55px 75px;
        }

        #tarjetas {
            text-decoration: none;
        }

        .mi-card {
            background-color: #330a04;
        }

        font {
            font-family: "Macondo", cursive;
        }
    </style>
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

                <div class="col-3">
                    <div class="card card-cover h-100 overflow-hidden mi-card rounded-4 shadow-lg">
                        <div class="d-flex flex-column h-100 p-5 pb-3 text-white text-shadow-1">
                            <h5 class="pt-5 mt-5 mb-4 fs-2 lh-1 fw-bold">
                                <font dir="auto" style="vertical-align: inherit;">
                                    <a href="colaboradores.php" style="text-decoration: none; color: white;">Colaboradores</a>
                                </font>
                            </h5>
                        </div>
                    </div>
                </div>


                <div class="col-3">
                    <div class="card card-cover h-100 overflow-hidden mi-card rounded-4 shadow-lg">
                        <div class="d-flex flex-column h-100 p-5 pb-3 text-white text-shadow-1">
                            <h5 class="pt-5 mt-5 mb-4 fs-2 lh-1 fw-bold">
                                <font dir="auto" style="vertical-align: inherit;">
                                    <a href="tiendas_exp.php" style="text-decoration: none; color: white;">Tiendas Explanada</a>
                                </font>
                            </h5>
                        </div>
                    </div>
                </div>

                <div class="col-3">
                    <div class="card card-cover h-100 overflow-hidden mi-card rounded-4 shadow-lg">
                        <div class="d-flex flex-column h-100 p-5 pb-3 text-white text-shadow-1">
                            <h5 class="pt-5 mt-5 mb-4 fs-2 lh-1 fw-bold">
                                <font dir="auto" style="vertical-align: inherit;">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#modalVentas"
                                        style="text-decoration: none; color: white;">
                                        Ventas por persona
                                    </a>
                                </font>
                            </h5>
                        </div>
                    </div>
                </div>

                <div class="col-3">
                    <div class="card card-cover h-100 overflow-hidden mi-card rounded-4 shadow-lg">
                        <div class="d-flex flex-column h-100 p-5 pb-3 text-white text-shadow-1">
                            <h5 class="pt-5 mt-5 mb-4 fs-2 lh-1 fw-bold">
                                <font dir="auto" style="vertical-align: inherit;">
                                    <a href="comisiones.php" style="text-decoration: none; color: white;">Comision</a>
                                </font>
                            </h5>
                        </div>
                    </div>
                </div>

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

<div class="modal fade" id="modalVentas" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Selecciona parámetros</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- Temporada -->
        <label class="form-label">Temporada:</label><br>

        <button class="btn btn-outline-primary" onclick="setTemporada('semanasanta')">Semana Santa</button>
        <button class="btn btn-outline-primary" onclick="setTemporada('verano')">Verano</button>
        <button class="btn btn-outline-primary" onclick="setTemporada('diciembre')">Diciembre</button>

        <input type="hidden" id="temporada">

        <br><br>

        <!-- Fechas -->
        <label>Fecha inicio:</label>
        <input type="date" id="fechaInicio" class="form-control">

        <label class="mt-2">Fecha fin:</label>
        <input type="date" id="fechaFin" class="form-control">

      </div>

      <div class="modal-footer">
        <button class="btn btn-success" onclick="irAVentas()">Ingresar</button>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>

    </div>
  </div>
</div>

<script>
function setTemporada(valor) {
  document.getElementById("temporada").value = valor;
}

function irAVentas() {
  const temp = document.getElementById("temporada").value;
  const inicio = document.getElementById("fechaInicio").value;
  const fin = document.getElementById("fechaFin").value;

  if (!temp || !inicio || !fin) {
    alert("Completa todos los campos");
    return;
  }

  // Redirigir con parámetros
  window.location.href = "ventas_persona.php?temporada=" 
    + temp + "&inicio=" + inicio + "&fin=" + fin;
}

function setTemporada(valor) {
  document.getElementById("temporada").value = valor;

  document.querySelectorAll(".modal-body button").forEach(btn => {
    btn.classList.remove("active");
  });

  event.target.classList.add("active");
}
</script>

</body>

</html>