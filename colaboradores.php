<?php

session_start();
$usuario = $_SESSION['username'];
if (!isset($usuario)) {
  header('Location: index.php'); //En caso de que no haya una sesion abierta, redirecciona al index
} else {

?>

  <?php
  include 'config/conexion.php'; //Conexion a la base de datos

  if (isset($_POST['agregar'])) { //Si no recibe el boton de agregar, no entra al ciclo

    ///////////////////////////////////// Variables recibidas del form de agregar empleados ///////////////////////////////////////
    $id = $_POST['id']; //id del empleado
    $codigo = $_POST['codigo']; // Matricula
    $ap = $_POST['ap'];
    $am = $_POST['am'];
    $nombres = $_POST['nombre'];
    $nombre = $ap . " " . $am . " " . $nombres; //Nombre concatenado
    $sexo = $_POST['sexo']; //Sexo
    $cargo = $_POST['puesto']; //Puesto
    $emp = $_POST['empresa']; //Empresa
    $horario_empleado = $_POST['horario']; //horario


    ///////////////////////////////////////////////ACTUALIZA EMPLEADOS EN CASO DE QUE YA EXISTAN EN LA BASE DE DATOS ///////////////////////////////////////

    $consulta = "SELECT * FROM empleados WHERE idEmpleado=$id";
    $resultado = mysqli_query($conec, $consulta);

    if (mysqli_num_rows($resultado) > 0) {

      $consulta_codigo = "SELECT codigo,idEmpleado FROM empleados WHERE codigo = '$codigo' AND idEmpleado != '$id'";
      $resultado_codigo = mysqli_query($conec, $consulta_codigo);
      if (mysqli_num_rows($resultado_codigo) > 0) {
        // Si encuentra un codigo de empleado igual, arroja el siguiente letrero
        echo '<script type="text/javascript">alert("EL CODIGO DE EMPLEADO YA EXISTE");
        window.location.href="empleados.php";
      </script>';
      } else {

        $actualizar = "UPDATE empleados SET codigo='$codigo', nombre='$nombres',sexo='$sexo', puesto_id='$cargo', idEmpresa='$emp', idHorarios='$horario_empleado' WHERE idEmpleado=$id";
        $resultado2 = mysqli_query($conec, $actualizar);

        if (mysqli_query($conec, $actualizar)) {
          echo '<script type="text/javascript">alert("INFORMACION ACTUALIZADA CON EXITO");
              window.location.href="empleados.php";
            </script>';
        } else {

          echo "Error: " . $actualizar . " <br>" . $id . "<br>" . mysqli_error($conec);
        }
      }
    } else {

/////////////////////////////////////////////////////////////// INSERTAR EMPLEADOS ///////////////////////////////////////////////////////////////////////

      $repetido = "SELECT codigo FROM empleados WHERE codigo=$codigo"; //Consulta para saber si hay un numero de empleado repetido
      $resultado_repetidos = (mysqli_query($conec, $repetido));
      if (mysqli_num_rows($resultado_repetidos) > 0) {
        echo '<script type="text/javascript">alert("EL CODIGO DE EMPLEADO YA EXISTE");
                window.location.href="empleados.php";
              </script>';
      } else {
        $sql = "INSERT empleados (codigo, nombre, sexo, puesto_id, fecha, status, idEmpresa, idHorarios) VALUES ('$codigo','$nombre', '$sexo','$cargo', NOW(), 1, '$emp','$horario_empleado')";

        if (mysqli_query($conec, $sql)) {
          echo '<script type="text/javascript">alert("EMPLEADO REGISTRADO");
                  window.location.href="empleados.php";
                </script>';
        } else {
          echo "Error: " . $sql . "<br>" . mysqli_error($conec);
        }
      }
    }
  }

  /////////////////////////////////////////////////////// PROCESO PARA DAR DE BAJA A EMPLEADOS //////////////////////////////////////////////////////////////

  if (isset($_POST['baja'])) { //Si no recibe el boton de baja, no entra al ciclo
    $matricula = $_POST['matricula'];
    $sql2 = "DELETE FROM empleados WHERE codigo = $matricula";

    if (mysqli_query($conec, $sql2)) {

      echo '<script type="text/javascript">alert("BAJA CORRECTA");
                window.location.href="empleados.php";
              </script>';
    } else {
      echo "Error: " . $sql . "<br>" . mysqli_error($conec);
    }
  }

  ///////////////////////////////////////////////////////// PROCESO PARA BUSCAR EMPLEADOS /////////////////////////////////////////////////////////////////

  if (isset($_POST['buscar'])) { //Si no recibe el boton buscar, no entra a ciclo
    $matricula = $_POST['matricula'];

    $consulta_busqueda = "SELECT codigo FROM empleados WHERE codigo = $matricula";
    $resultado_busqueda = mysqli_query($conec, $consulta_busqueda);
    if (mysqli_num_rows($resultado_busqueda) <= 0) {
      // Si hay al menos un resultado, significa que el código ya existe
      echo '<script type="text/javascript">alert("EMPLEADO NO ENCONTRADO");
        window.location.href="empleados.php";
      </script>';
    } else {

      $sql3 = "SELECT empleados.idEmpleado AS id, empleados.codigo, 
      empleados.nombre AS nombre_empleado, 
      empleados.sexo, empleados.puesto_id, empleados.idEmpresa, empleados.idHorarios, 
      horarios.nombre AS nombre_horario, 
      horarios.idHorarios AS id_horarios,
      horarios.h_e AS h_e,
      horarios.h_s AS h_s,
      horarios.diaFlotante AS diaFlotante,
      puestos.nombre AS nombre_puesto, 
      empresas.nombre AS nombre_empresa
      FROM empleados INNER JOIN puestos INNER JOIN horarios INNER JOIN empresas
      WHERE codigo=$matricula AND empleados.puesto_id=puestos.id_puesto AND empleados.idHorarios=horarios.idHorarios AND empleados.idEmpresa=empresas.idEmpresa";

      $result = mysqli_query($conec, $sql3);

      $mostrar = mysqli_fetch_array($result);

      //Variables despues de la busqueda
      $id = $mostrar['id'];
      $codigob = $mostrar['codigo'];
      $nombreb = $mostrar['nombre_empleado'];
      $sexob = $mostrar['sexo'];

      $puestob_nombre = $mostrar['nombre_puesto'];

      $puestob = $mostrar['puesto_id'];

      $empresab = $mostrar['idEmpresa'];
      $empresa_nombre = $mostrar['nombre_empresa'];
      $horariob_nombre = $mostrar['nombre_horario'];
      $horariob_id = $mostrar['id_horarios'];
      $horariobh_e = $mostrar['h_e'];
      $horariobh_s = $mostrar['h_s'];
      $horariob_diaFlotante = $mostrar['diaFlotante'];
      
      $horariob = $mostrar['idHorarios'];
    }
  }
  ?>

  <!------------------------------------------------------- INCICIO DEL FORMULARIO DE EMPLEADOS ------------------------------------------------------------>

  <!doctype html>

  <html lang="en">

  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="./assets/css/estilos.css">
    <link rel="icon" href="./assets/img/icono.ico" type="image/x-icon">
    <link rel="stylesheet" href="./assets/css/estilos.css">
    <style>
      .btn a {
        text-decoration: none;
        color: inherit;
      }
    </style>

    <title>Sistemas Africam Safari</title>
  </head>

  <body>

    <?php

    include "navbar.php";

    ?>

    <script>
      //Scriptpara convertir en mayusculas los campos de texto
      function mayus(e) {
        e.value = e.value.toUpperCase();
      }
    </script>

    <!--------------------------------------- FORMULARIO AGREGAR EMPLEADOS ------------------------------------------>
    <br>
    <div class="container-fluid">
      <div class="row">
        <!-- Primer div -->
        <div class="row d-flex justify-content-center align-items-center h-100">
          <div class="col-12 col-md-8 ">
            <div class="card shadow-2-strong" style="border-radius: 1rem;">
              <div class="card-body p-2 text-center">

               
                <form action="empleados.php" method="post">

                  <h3 class="text-center">Agregar Empleado</h3>
                  <br><br>

                  <input type="hidden" name="id" value="<?php echo $id; ?>">
                  <div class="mb-3">
                    <label for="" class="form-label">Matricula</label>
                    <input type="text" class="form-control" value="<?php echo $codigob; ?>" name="codigo" required>
                  </div>

                  <div class="mb-3">
                    <label for="" class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="nombre" value="<?php echo $nombreb; ?>" onkeyup="mayus(this);" required>
                  </div>
                  <?php
                  if (!isset($codigob)) {


                  ?>
                    <div class="mb-3">
                      <label for="" class="form-label">Apellido paterno</label>
                      <input type="text" class="form-control" name="ap" onkeyup="mayus(this);">
                    </div>

                    <div class="mb-3">
                      <label for="" class="form-label">Apellido Materno</label>
                      <input type="text" class="form-control" name="am" onkeyup="mayus(this);">
                    </div>
                  <?php
                  }
                  ?>

                  <div class="mb-3">
                    <label class="form-label">Sexo</label>
                    <select class="form-select form-select-sm" aria-label=".form-select-sm example" name="sexo">

                      <option value="<?php echo $sexob; ?>"><?php echo $sexob; ?></option>
                      <option value="M">Masculino</option>
                      <option value="F">Femenino</option>

                    </select>
                  </div>


                  <div class="mb-3">
                    <label class="form-label">Puesto</label>
                    <select class="form-select form-select-sm" aria-label=".form-select-sm example" name="puesto">
                      <option value="<?php echo $puestob ?>"><?php echo $puestob_nombre ?></option>
                      <?php

                      $sql_puesto = "SELECT nombre, id_puesto FROM puestos ORDER BY nombre ASC";

                      $puesto = mysqli_query($conec, $sql_puesto);
                      while ($fila = mysqli_fetch_array($puesto)) {

                      ?>

                        <option value="<?php echo $fila['id_puesto'] ?>"><?php echo $fila['id_puesto'] . " - " . $fila['nombre'] ?></option>
                      <?php
                      }
                      ?>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Tipo de empleado</label>
                    <select class="form-select form-select-sm" aria-label=".form-select-sm example" name="empresa">
                      <option value="<?php echo $empresab ?>"><?php echo $empresa_nombre ?></option>
                      <?php
                      $sql_empresa = "SELECT DISTINCT empresas.nombre AS nombree, empresas.idEmpresa FROM empleados INNER JOIN empresas WHERE empleados.idEmpresa=empresas.idEmpresa ORDER BY empresas.idEmpresa";
                      $empresa = mysqli_query($conec, $sql_empresa);
                      while ($fempresa = mysqli_fetch_array($empresa)) {
                      ?>

                        <option value="<?php echo $fempresa['idEmpresa'] ?>"><?php echo $fempresa['idEmpresa'] . " - " . $fempresa['nombree']; ?></option>
                      <?php
                      }
                      ?>

                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="" class="form-label">Horario</label>
                    <select class="form-select form-select-sm" aria-label=".form-select-sm example" name="horario">

                      <option value="<?php echo $horariob ?>"><?php echo $horariob_id." - ".$mostrar['nombre_horario']." - (".$horariobh_e." - ".$horariobh_s.") - ".$horariob_diaFlotante; ?></option>

                      <?php
                    

                      $sql_horario = "SELECT DISTINCT horarios.nombre, horarios.idHorarios, horarios.h_e, horarios.h_s, horarios.diaFlotante FROM horarios ORDER BY horarios.idHorarios";

                      $horario = mysqli_query($conec, $sql_horario);
                      while ($fhorario = mysqli_fetch_array($horario)) {
                      ?>
                        <option value="<?php echo $fhorario['idHorarios']; ?>"><?php echo $fhorario['idHorarios'] . " - " . $fhorario['nombre']." - "."(".$fhorario['h_e']." - ".$fhorario['h_s'].")"." - ".$fhorario['diaFlotante']; ?></option>
                      <?php
                      }
                      ?>

                    </select>
                  </div>

                  <br>
                  <?php
                  if (!isset($codigob)) {
                  ?>
                    <input type="submit" name="agregar" value="AGREGAR" class="btn btn-primary btn-lg">
                  <?php
                  } else {
                  ?>
                    <input type="submit" name="agregar" value="ACTUALIZAR" class="btn btn-primary btn-lg">
                  <?php
                  }
                  ?>

                  <br>
                  <br>
                  <br>

                </form>

                <div>
                  <input data-bs-toggle="modal" data-bs-target="#baja" type="submit" name="baja" value="Baja de empleado" class="btn btn-primary btn-lg">

                  <form action="empleados.php" method="post"> <!-- MODAL PARA DAR DE BAJA EMPLEADOS -->
                    <div class="modal" id="baja">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">BAJA DE EMPLEADO</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <form>
                              <div class="mb-3">
                                <label class="form-label required">Matricula</label>
                                <input type="text" class="form-control" name="matricula" required>
                              </div>
                            </form>
                          </div>
                          <div class="modal-footer">
                            <button type="submit" name="baja" class="btn btn-danger">Dar de baja</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>


                  <br>
                  <input data-bs-toggle="modal" data-bs-target="#buscar" type="submit" name="buscar" value="Buscar para modificar empleado" class="btn btn-primary btn-lg">

                  <form action="empleados.php" method="post"><!-- MODAL BUSCAR PARA MODIFICAR EMPLEADOS -->
                    <div class="modal" id="buscar">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">MODIFICAR EMPLEADO</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <form>
                              <div class="mb-3">
                                <label class="form-label required">Matricula</label>
                                <input type="text" class="form-control" name="matricula" required>
                              </div>
                            </form>
                          </div>
                          <div class="modal-footer">
                            <button type="submit" name="buscar" class="btn btn-primary">Buscar</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>

                </div>
              </div>
            </div>
          </div>
        </div>



        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

  </body>

  </html>
<?php
}

?>