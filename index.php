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
<form method="post">
    <label>Tabulka<br>
        <textarea rows="5" cols="50" name="table" class="generic"></textarea>
    </label>
    <br>
    <input type="submit" value="Odeslat">
</form>
<form method="post">
    <label>Kouzlo čaroděje<br>
        <textarea rows="5" cols="50" name="wizard_spell" class="wizard"></textarea>
    </label>
    <br>
    <input type="submit" value="Odeslat">
</form>
<form method="post">
    <label>Kouzla z abecedního seznamu<br>
        <textarea rows="5" cols="50" name="wizard_spells_from_table_of_content" class="wizard"></textarea>
    </label>
    <br>
    <input type="submit" value="Odeslat">
</form>
<form method="post">
    <label>Bojové parametry kouzla<br>
        <textarea rows="5" cols="50" name="wizard_spell_combat_properties" class="wizard"></textarea>
    </label>
    <br>
    <input type="submit" value="Odeslat">
</form>
<div class="clear"></div>
<form method="post">
    <label>Zlodějova schopnost<br>
        <textarea rows="5" cols="50" name="thief_skill_properties" class="thief"></textarea>
    </label>
    <br>
    <input type="submit" value="Odeslat">
</form>
<form method="post">
    <label>Rozšířený hod na úspěch<br>
        <textarea rows="5" cols="50" name="extended_roll_on_success" class="thief"></textarea>
    </label>
    <br>
    <input type="submit" value="Odeslat">
</form>
<form method="post">
    <label>Bojová dovednost zloděje<br>
        <textarea rows="5" cols="50" name="thief_combat_parameters" class="thief"></textarea>
    </label>
    <br>
    <input type="submit" value="Odeslat">
</form>
<form method="post">
    <label>Příšera<br>
        <textarea rows="5" cols="50" name="bestiary" class="bestiary"></textarea>
    </label>
    <br>
    <input type="submit" value="Odeslat">
</form>
<div class="clear"></div>
<form method="post">
    <label>Tabulka PPJ<br>
        <textarea rows="5" cols="50" name="table_dm" class="generic"></textarea>
    </label>
    <br>
    <input type="submit" value="Odeslat">
</form>
<form method="post">
    <label>Text<br>
        <textarea rows="5" cols="50" name="text" class="text"></textarea>
    </label>
    <br>
    <input type="submit" value="Odeslat">
</form>
<div class="clear"></div>
<?php
ini_set('error_reporting', -1);
ini_set('display_errors', '1');
if (!empty($_POST['table'])) {
    require_once __DIR__ . '/generic.php';
    $table = to_table($_POST['table']);
    ?>
    <label>tabulka<br>
        <textarea rows="20" cols="80" id="result"><?= $table ?></textarea>
    </label>
<?php } elseif (!empty($_POST['wizard_spell'])) {
    require_once __DIR__ . '/wizard.php';
    $spellAsTable = wizard_spell_to_table($_POST['wizard_spell']);
    ?>
    <label>tabulka<br>
        <textarea rows="20" cols="80" id="result"><?= $spellAsTable ?></textarea>
    </label>
<?php } elseif (!empty($_POST['wizard_spells_from_table_of_content'])) {
    require_once __DIR__ . '/wizard.php';
    $spellAsTable = wizard_spells_from_table_of_content_to_table($_POST['wizard_spells_from_table_of_content']);
    ?>
    <label>tabulka<br>
        <textarea rows="20" cols="80" id="result"><?= $spellAsTable ?></textarea>
    </label>
<?php } elseif (!empty($_POST['wizard_spell_combat_properties'])) {
    require_once __DIR__ . '/wizard.php';
    $spellAsTable = wizard_spell_combat_parameters_to_table($_POST['wizard_spell_combat_properties']);
    ?>
    <label>tabulka<br>
        <textarea rows="10" cols="80" id="result"><?= $spellAsTable ?></textarea>
    </label>
<?php } elseif (!empty($_POST['thief_skill_properties'])) {
    require_once __DIR__ . '/thief.php';
    $propertiesHighlighted = thief_properties_highlighted($_POST['thief_skill_properties']);
    ?>
    <label>dovednost<br>
        <textarea rows="10" cols="80" id="result"><?= $propertiesHighlighted ?></textarea>
    </label>
<?php } elseif (!empty($_POST['extended_roll_on_success'])) {
    require_once __DIR__ . '/thief.php';
    $extendedRollOnSuccess = format_extended_roll_on_success($_POST['extended_roll_on_success']);
    ?>
    <label>hod<br>
        <textarea rows="9" cols="70" id="result"><?= $extendedRollOnSuccess ?></textarea>
    </label>
<?php } elseif (!empty($_POST['thief_combat_parameters'])) {
    require_once __DIR__ . '/thief.php';
    $thiefCombatParameters = combat_parameters_to_table($_POST['thief_combat_parameters']);
    ?>
    <label>tabulka<br>
        <textarea rows="9" cols="70" id="result"><?= $thiefCombatParameters ?></textarea>
    </label>
<?php } elseif (!empty($_POST['bestiary'])) {
    require_once __DIR__ . '/bestiary.php';
    $creature = format_creature($_POST['bestiary']);
    ?>
    <label>tabulka<br>
        <textarea rows="20" cols="150" id="result"><?= $creature ?></textarea>
    </label>
<?php } elseif (!empty($_POST['table_dm'])) {
    require_once __DIR__ . '/dm.php';
    $dmTable = to_dm_table($_POST['table_dm']);
    ?>
    <label>tabulka<br>
        <textarea rows="20" cols="150" id="result"><?= $dmTable ?></textarea>
    </label>
<?php } elseif (!empty($_POST['text'])) {
    require_once __DIR__ . '/text.php';
    $text = format_text($_POST['text']);
    ?>
    <label>formátovaný text<br>
        <textarea rows="20" cols="150" id="result"><?= $text ?></textarea>
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