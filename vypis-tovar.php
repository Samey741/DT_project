 <!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
       <title>vyhladaj</title>
        <link href="style.css" rel=stylesheet type=text/css>
</head>
<body>
<p align = "left">

<?php
echo "<table border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse' bordercolor='#111111' width='700'>";
include ("config.php");   

$var = mysqli_connect("$servername","$username","$password","$dbname") or die ("connect error");
$sql = "SELECT id, pc, nazov, vyrobca, popis, kusov, cena, kod FROM ntovar";
$result = mysqli_query($var, $sql) or exit ("chybny dotaz");
//načítanie hodnôt do pola
while($row = mysqli_fetch_assoc($result))
		{ 
			$id = $row['id'];
			$pc = $row['pc'];
			$nazov = $row['nazov'];
			$vyrobca = $row['vyrobca'];
			$popis = $row['popis'];
			$kusov = $row['kusov'];
			$cena = $row['cena'];
			$kod = $row['kod'];
//výpis hodnôt
echo "<tr>
    <td width='200'bgcolor='#ffffff' height='22'><b> ".$kod."</b></td>
    <td width='300'bgcolor='#ffffff' height='22'>Nazov<b> ".$nazov."</b></td> 
    <td width='100'bgcolor='#ffffff' height='22'>Cena: <b> ".$cena."</b></td>
    <td width='100'bgcolor='#ffffff' height='22'><b><a href='index.php?menu=12&e=".$id."'>edituj</b></a></td>
     </tr>
     <tr>
    <td width='200'bgcolor='#FFFFee' height='32'>Vyrobca<b> ".$vyrobca."</b></td>
    <td width='300'bgcolor='#FFFFee' height='32'>popis <b>".$popis." </b></td>
    <td width='100'bgcolor='#FFFFee' height='32'>kusov <b>".$kusov."</b></td>
    <td width='100' color='ff0000' bgcolor='#FFFFee' height='32'><b><a href='zmazanietov.php?k=".$id."'>x</b></a></td>
  </tr>   
  <tr>
   <td width='200'bgcolor='#000000' height='1'></td>
    <td width='300'bgcolor='#000000' height='1'></td> 
    <td width='100'bgcolor='#000000' height='1'></td>
    <td width='100'bgcolor='#000000' height='1'></td>
    </tr>";
  }
  echo "</table>";
?>

 </body>
</html>
