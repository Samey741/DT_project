<?php
echo '<br /><br /><br /><center>';
echo '<b>Pridanie tovaru  </b><br/><br /> ' ;
echo '<form action="index.php?menu=7" method="POST">';
echo 'Mesto: <input name="pc" type="text" /><br/><br />';
echo 'Názov: <input name="nazov" type="text" /><br/><br />';
echo 'Výrobca: <input name="vyrobca" type="text"/><br/><br />';
echo 'Popis: <input name="popis" type="text"/><br/><br/>';
echo 'kusov: <input name="kusov" type="text" /><br /><br />';
echo 'cena: <input name="cena" type="text" /><br /><br />';
echo 'kód: <input name="kod" type="text" /><br />';
echo '<input type="submit" name="submit" value="Odoslat"/>';
echo '<input type="reset" value="Vymaz"/>';
echo '</form></center>';
?>
