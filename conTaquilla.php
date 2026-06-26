<?PHP 
 //$conn1 = ibase_connect("10.10.50.5/3050:M:\Microsip Datos\TAQUILLAS.FDB", "sysdba", "masterkey");
 $conn1 = ibase_connect("C:\Microsip Datos\TAQUILLA.FDB", "sysdba", "sys.20.T97");
 

 //if ($conn1) 
 //{
   //echo "Acceso Correcto!";
 //}
//else 
//{
  //echo "Acceso Denegado!";
//}

/*
$consulta=ibase_query($conn1, "SELECT * FROM CLIENTES");

if (!$consulta) {
    echo "Error en la consulta: " . ibase_errmsg();
    exit;
}
$fila = ibase_fetch_row($consulta);
echo $fila[1];


while($fila = ibase_fetch_row($consulta))
{
	echo "Si entra";
echo "<br>".$fila[1]."</br>";
}
echo "No entra";*/

// $Q = ibase_query("SELECT * FROM CLIENTES");

// while ($R = ibase_fetch_object($Q)) {

//   echo $R->NOMBRE ;
  
//   }

?>