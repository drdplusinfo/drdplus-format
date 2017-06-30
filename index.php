<html>
<body>
<form style="float: left">
    <label>Kouzlo čaroděje<br>
        <textarea rows="10" cols="50" name="wizard_spell" class="wizard"></textarea>
    </label>
    <br>
    <input type="submit" value="Odeslat">
</form>
<form style="float: left; margin-left: 1em" method="post">
    <label>Kouzla z abecedního seznamu<br>
        <textarea rows="10" cols="50" name="wizard_spells_from_table_of_content" class="wizard"></textarea>
    </label>
    <br>
    <input type="submit" value="Odeslat">
</form>
<form style="float: left; margin-left: 1em" method="get">
    <label>Bojové parametry kouzla<br>
        <textarea rows="10" cols="50" name="wizard_spell_combat_properties" class="wizard"></textarea>
    </label>
    <br>
    <input type="submit" value="Odeslat">
</form>
<div style="clear: both"></div>
<?php
ini_set('error_reporting', -1);
ini_set('display_errors', '1');
if (!empty($_GET['wizard_spell'])) {
    require_once __DIR__ . '/wizard.php';
    $spellAsTable = wizard_spell_to_table($_GET['wizard_spell']);
    ?>
    <label>tabulka<br>
        <textarea rows="20" cols="80" id="wizard-result"><?= $spellAsTable ?></textarea>
    </label>
<?php } else if (!empty($_POST['wizard_spells_from_table_of_content'])) {
    require_once __DIR__ . '/wizard.php';
    $spellAsTable = wizard_spells_from_table_of_content_to_table($_POST['wizard_spells_from_table_of_content']);
    ?>
    <label>tabulka<br>
        <textarea rows="20" cols="80" id="wizard-result"><?= $spellAsTable ?></textarea>
    </label>
<?php } else if (!empty($_GET['wizard_spell_combat_properties'])) {
    require_once __DIR__ . '/wizard.php';
    $spellAsTable = wizard_spell_combat_properties_to_table($_GET['wizard_spell_combat_properties']);
    ?>
    <label>tabulka<br>
        <textarea rows="10" cols="80" id="wizard-result"><?= $spellAsTable ?></textarea>
    </label>
<?php } ?>
<script type="text/javascript">
    let wizardResult = document.getElementById('wizard-result');
    if (wizardResult.value) {
        wizardResult.focus();
    }
</script>
</body>
</html>