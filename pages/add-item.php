<!--TODO create a proper form-->
<h2>Pridanie tovaru</h2>
<form action="pages/add-item-submit.php" method="POST">
    <label>Mesto: <input name="pc" type="text" /></label><br/><br/>
    <label>Názov: <input name="nazov" type="text" /></label><br/><br/>
    <label>Výrobca: <input name="vyrobca" type="text"/></label><br/><br/>
    <label>Popis: <input name="popis" type="text"/></label><br/><br/>
    <label>Kusov: <input name="kusov" type="number"/></label><br/><br/>
    <label>Cena: <input name="cena" type="number" step="0.01"/></label><br/><br/>
    <label>Kód: <input name="kod" type="text"/></label><br/><br/>
    <input type="submit" name="submit" value="Odoslať"/>
    <input type="reset" value="Vymazať"/>
</form>
