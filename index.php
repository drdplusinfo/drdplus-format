<html>
<head>
    <style>
        form {
            float: left;
            margin-left: 1em
        }

        form:first-child {
            margin-left: 0;
        }

        .clear {
            clear: both;
        }

        .clear + form {
            margin-left: 0;
        }
    </style>
</head>
<body>
<form method="get">
    <label>Kouzlo čaroděje<br>
        <textarea rows="10" cols="50" name="wizard_spell" class="wizard"></textarea>
    </label>
    <br>
    <input type="submit" value="Odeslat">
</form>
<form method="post">
    <label>Kouzla z abecedního seznamu<br>
        <textarea rows="10" cols="50" name="wizard_spells_from_table_of_content" class="wizard"></textarea>
    </label>
    <br>
    <input type="submit" value="Odeslat">
</form>
<form method="get">
    <label>Bojové parametry kouzla<br>
        <textarea rows="10" cols="50" name="wizard_spell_combat_properties" class="wizard"></textarea>
    </label>
    <br>
    <input type="submit" value="Odeslat">
</form>
<div class="clear"></div>
<form method="get">
    <label>Zlodějova schopnost<br>
        <textarea rows="10" cols="50" name="thief_skill_properties" class="thief"></textarea>
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
<?php } else if (!empty($_GET['thief_skill_properties'])) {
    require_once __DIR__ . '/thief.php';
    $propertiesHighlighted = thief_properties_highlighted($_GET['thief_skill_properties']);
    ?>
    <label>dovednost<br>
        <textarea rows="10" cols="80" id="result"><?= $propertiesHighlighted ?></textarea>
    </label>
<?php } ?>
<script type="text/javascript">
    let result = document.getElementById('result');
    if (result.value) {
        result.focus();
    }
</script>
</body>
</html>