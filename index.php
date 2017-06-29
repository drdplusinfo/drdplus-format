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
ini_set('error_reporting', -1);
ini_set('display_errors', '1');
if (!empty($_GET['wizard_spell'])) {
    require_once __DIR__ . '/wizard.php';
    $spellAsTable = wizard_spell_to_table($_GET['wizard_spell']);
    ?>
    <label>tabulka<br>
        <textarea rows="15" cols="80" id="wizard-result"><?= $spellAsTable ?></textarea>
    </label>
<?php } ?>
<script type="text/javascript">
    let wizardResult = document.getElementById('wizard-result');
    if (wizardResult.value) {
        wizardResult.focus();
    } else {
        let wizard = document.getElementsByClassName('wizard')[0];
        wizard.focus();
    }
</script>
</body>
</html>