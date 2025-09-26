<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title></title>

  </head>
  <body>
<table border="0" cellspacing="0"  bordercolor="#FFFFFF" width="900" height="221" >
  <tr>
    <td width="200" height="27" align="center" bgcolor="#000080" >
	<script>
function checkServerStatus() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log(this.responseText);
        }
    };
    xhttp.open("GET", "check_and_sync.php", true);
    xhttp.send();
}

// Spustenie každých 20 sekúnd
setInterval(checkServerStatus, 20000);
</script>
    	<font color="#FFFF00"></font>
    </td>
    <td width="700" height="27" bgcolor="#220075" >
    	<h1 ><font color="#FFFF00">Distribuovaná databáza</font></h1>
    </td>
  </tr>
  <tr>
 <td width="200" height="27" align="center" valign="top" bgcolor="#aa0055" >  
   <?php 
    include ("menu.php");
    ?>
     </td>
    <td width="800" height="510" valign="top" bgcolor="#FFFFCC" >
    	&nbsp;</p>
<DIV CLASS =dolezite>
    <?php
$m = $_GET["menu"];
if (($m!=1) and ($m!=2) and ($m!=3)and ($m!=4)and ($m!=5)and ($m!=6)and ($m!=7)and ($m!=8)and ($m!=9)and ($m!=10)and ($m!=11)and ($m!=12)) $m=3;
if (! isset($m)) $m=3;
switch ($m){
	        
			case 4:
	        	include ("synchro.php");
	        	break;
			case 2:
	        	include ("pridaj-tovar.php");
	        	break;
	        case 3:
	        	include ("vypis-db.php");
	        	break;
			
			case 5:
	        	include ("form-hladaj.php");
	        	break;	
	    case 6:
	        	include ("hladaj.php");
	        	break;    	
	    case 7:
	        	include ("pridaj-tovar-ok2.php");
	        	break;
      case 8:
	        	include ("vypis-tovar.php");
	        	break; 
      case 9:
	        	include ("hladaj-tovar-cena.php");
	        	break;  
      case 10:
	        	include ("vypis-tovar-cena.php");
	        	break; 
		case 11:
	        	include ("edit-tov-ok.php");
	        	break; 	
        case 12:
                include ("edit.php");
	        	break; 	
          default:
		        include ("vypis-tovar.php");
	        	break;
	                    }

?>
</DIV>
    </td>
  </tr>
</table>

  </body>
</html>
