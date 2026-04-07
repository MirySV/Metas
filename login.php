<?php
//recordar la variable de sesion
include 'conexion.php';
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
    <!-- Archivo CSS -->
    <link rel="stylesheet" href="./css/style.css">
    <title>Tiendas Africam Safari</title>
</head>

<body>
    <header class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom">
        <a href="login.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-decoration-none">
            <img src="./assets/img/logo.png" width="10%" alt="50%" />
            <h2 class="text-black" id="nombrepag">Tiendas Africam Safari</h2>
        </a>
    </header>

    <main>
        <div class="container px-1 px-sm-5 mx-auto">

        <form class="form-inline" action="validar.php" method="post">

            <section class="">
                <div class="container py-5 h-100 fondo">
                    <div class="row d-flex justify-content-center align-items-center h-100">
                        <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                            <div class="card shadow-2-strong" style="border-radius: 1rem;">
                                <div class="card-body p-5 text-center">
                                    <h3 class="text-center">
                                        <img src="assets/img/person-circle.svg" width="50%" alt="" />
                                    </h3>

                                    <br><br>

                                    <div class="mb-3">
                                        <label for="" class="form-label">Usuario</label>
                                        <input type="text" class="form-control" name="usuario" required>
                                    </div>


                                    <div class="mb-3">
                                        <label for="" class="form-label">Contraseña</label>
                                        <input type="password" class="form-control" name="contraseña" required>
                                    </div>


                                    <br>
                                    <button type="submit" value="iniciarsesion" name="iniciarsesion" class="btn btn-primary "><a class="text-light">Iniciar sesion</a></button>
                                    <br>
                                    <br>
                                    <!-- <button type="submit" value="registrar" href="registro_usuarios.php" class="btn btn-primary btn-lg"><a href="#" class="text-light">Registrarse</a></button> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </form>
    </div>
    </main>

    <!-- Footer -->
  <footer class="py-3 my-4">
    <p class="text-center">Africam Safari SA de CV &copy; 2026</p>
  </footer>

    <!-- CDN Boostrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

</body>

</html>