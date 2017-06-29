<html>
<body>
<form>
    <label>Kouzlo čaroděje<br>
        <textarea rows="10" cols="50" name="wizard_spell" class="wizard"></textarea>
    </label>
    <br>
    <input type="submit" value="Odeslat">
</form>
<?php
if (!empty($_GET['wizard_spell'])) {
    require_once __DIR__ . '/wizard.php';
    $spellAsTable = wizard_spell_to_table($_GET['wizard_spell']);
    ?>
    <pre id="selected"><?= $spellAsTable ?></pre>
<?php } ?>
<script type="text/javascript">
    let wizard = document.getElementsByClassName('wizard')[0];
    wizard.focus();
</script>
</body>
</html>