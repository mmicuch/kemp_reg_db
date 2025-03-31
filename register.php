<?php
require 'config.php';

// Získanie typu registrácie z URL, default "taborujuci"
$linkType = isset($_GET['type']) ? $_GET['type'] : 'taborujuci';
$allowedTypes = ['taborujuci', 'veduci', 'host'];
if (!in_array($linkType, $allowedTypes)) {
    $linkType = 'taborujuci';
}

// Získanie pohlavia z POST alebo nastavíme prázdnu hodnotu, ak ešte nebolo vybraté
$pohlavie = $_POST['pohlavie'] ?? null;

// SQL dotazy na načítanie možností ubytovania
if ($linkType === 'veduci') {
    // Vedúci vidia možnosti s $pohlavie, spoločné aj izby s typom "veduci"
    $queryAccommodation = $pdo->prepare("
        SELECT id, izba, typ, kapacita - (
            SELECT COUNT(*) FROM os_udaje_ubytovanie 
            WHERE os_udaje_ubytovanie.ubytovanie_id = ubytovanie.id
        ) AS volne_miesta
        FROM ubytovanie
        WHERE typ IN (?, 'spolocne', 'veduci')
    ");
    $queryAccommodation->execute([$pohlavie === 'M' ? 'muz' : 'zena']);
} else {
    // Bežní účastníci vidia možnosti, kde typ je buď podľa pohlavia alebo "spolocne"
    $queryAccommodation = $pdo->prepare("
        SELECT id, izba, typ, kapacita - (
            SELECT COUNT(*) FROM os_udaje_ubytovanie 
            WHERE os_udaje_ubytovanie.ubytovanie_id = ubytovanie.id
        ) AS volne_miesta
        FROM ubytovanie
        WHERE typ IN (?, 'spolocne')
    ");
    $queryAccommodation->execute([$pohlavie === 'M' ? 'muz' : 'zena']);
}
$accommodationOptions = $queryAccommodation->fetchAll(PDO::FETCH_ASSOC);

// Načítanie ďalších údajov z databázy
$queryYouth     = $pdo->query("SELECT nazov FROM mladez")->fetchAll(PDO::FETCH_ASSOC);
$queryAllergies = $pdo->query("SELECT id, nazov FROM alergie")->fetchAll(PDO::FETCH_ASSOC);

$queryWednesdayActivities = $pdo->query("
    SELECT id, nazov, kapacita - (
        SELECT COUNT(*) FROM os_udaje_aktivity 
        WHERE os_udaje_aktivity.aktivita_id = aktivity.id
    ) AS volne_miesta
    FROM aktivity 
    WHERE den = 'streda' AND kapacita > (
        SELECT COUNT(*) FROM os_udaje_aktivity 
        WHERE os_udaje_aktivity.aktivita_id = aktivity.id
    )
")->fetchAll(PDO::FETCH_ASSOC);

$queryThursdayActivities = $pdo->query("
    SELECT id, nazov, kapacita - (
        SELECT COUNT(*) FROM os_udaje_aktivity 
        WHERE os_udaje_aktivity.aktivita_id = aktivity.id
    ) AS volne_miesta
    FROM aktivity 
    WHERE den = 'stvrtok' AND kapacita > (
        SELECT COUNT(*) FROM os_udaje_aktivity 
        WHERE os_udaje_aktivity.aktivita_id = aktivity.id
    )
")->fetchAll(PDO::FETCH_ASSOC);

$queryFridayActivities = $pdo->query("
    SELECT id, nazov, kapacita - (
        SELECT COUNT(*) FROM os_udaje_aktivity 
        WHERE os_udaje_aktivity.aktivita_id = aktivity.id
    ) AS volne_miesta
    FROM aktivity 
    WHERE den = 'piatok' AND kapacita > (
        SELECT COUNT(*) FROM os_udaje_aktivity 
        WHERE os_udaje_aktivity.aktivita_id = aktivity.id
    )
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="sk">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrácia</title>
  <!-- Definícia globálnej premennej pre režim vedúceho pre JavaScript -->
  <script>
      var leaderMode = "<?= $linkType === 'veduci' ? 'true' : 'false' ?>";
  </script>
  <script src="script.js" defer></script>
</head>
<body>
  <!-- Udržujeme parameter type v action URL -->
  <form action="process_registration.php?type=<?= urlencode($linkType) ?>" method="POST">
      <h1>Registrácia - <?= htmlspecialchars(ucfirst($linkType)) ?></h1>

      <label for="meno">Meno:</label>
      <input type="text" id="meno" name="meno" required>

      <label for="priezvisko">Priezvisko:</label>
      <input type="text" id="priezvisko" name="priezvisko" required>

      <label for="datum_narodenia">Dátum narodenia:</label>
      <input type="date" id="datum_narodenia" name="datum_narodenia" onchange="validateAge()" required>

      <label for="pohlavie">Pohlavie:</label>
      <select id="pohlavie" name="pohlavie" required onchange="updateAccommodation()">
          <option value="" selected disabled>Vyberte pohlavie...</option>
          <option value="M">Muž</option>
          <option value="F">Žena</option>
      </select>

      <label for="ubytovanie">Ubytovanie:</label>
      <select id="ubytovanie" name="ubytovanie" required>
          <option value="" selected disabled>Vyberte ubytovanie...</option>
          <?php foreach ($accommodationOptions as $accom): ?>
              <option value="<?= htmlspecialchars($accom['id']) ?>" data-type="<?= htmlspecialchars($accom['typ']) ?>">
                  <?= htmlspecialchars($accom['izba']) ?> - Typ: <?= htmlspecialchars($accom['typ']) ?> - Voľné miesta: <?= $accom['volne_miesta'] ?>
              </option>
          <?php endforeach; ?>
      </select>

      <label for="mladez">Mládež:</label>
      <select id="mladez" name="mladez" required>
          <option value="" selected disabled>Vyberte mládež...</option>
          <?php foreach ($queryYouth as $youth): ?>
              <option value="<?= htmlspecialchars($youth['nazov']) ?>"><?= htmlspecialchars($youth['nazov']) ?></option>
          <?php endforeach; ?>
      </select>

      <label for="poznamka">Poznámka (max 200 znakov):</label>
      <textarea id="poznamka" name="poznamka" maxlength="200"></textarea>

      <label for="mail">Email:</label>
      <input type="email" id="mail" name="mail" required>

      <label for="novy">Nový účastník?</label>
      <input type="checkbox" id="novy" name="novy">

      <div class="allergyCheckboxes">
    <label>Potravinové alergie:</label>
    
    <div class="allergyOption">
        <input type="checkbox" id="allergy_none" name="alergie[]" value="none" onchange="handleNoneAllergy(this)">
        <label for="allergy_none">Žiadne</label>
    </div>
    
    <?php foreach ($queryAllergies as $alergy): ?>
    <div class="allergyOption">
        <input type="checkbox" id="allergy_<?= htmlspecialchars($alergy['id']) ?>" 
               name="alergie[]" value="<?= htmlspecialchars($alergy['id']) ?>" 
               onchange="handleAllergyChange()">
        <label for="allergy_<?= htmlspecialchars($alergy['id']) ?>"><?= htmlspecialchars($alergy['nazov']) ?></label>
    </div>
    <?php endforeach; ?>
    
    <div class="allergyOption">
        <input type="checkbox" id="allergy_other" name="alergie[]" value="other" onchange="toggleOtherAllergy()">
        <label for="allergy_other">Iné (špecifikujte)</label>
        <input type="text" id="alergie_other" name="alergie_other" style="display:none;" placeholder="Špecifikujte">
    </div>
</div>

<script>
// Ak je vybraté "Žiadne", ostatné sa odznačia
function handleNoneAllergy(checkbox) {
    if (checkbox.checked) {
        document.querySelectorAll('.allergyOption input[type="checkbox"]:not(#allergy_none)').forEach(cb => {
            cb.checked = false;
        });
        document.getElementById('alergie_other').style.display = 'none';
    }
}

// Ak je vybraná ľubovoľná alergia, "Žiadne" sa odznačí
function handleAllergyChange() {
    const hasChecked = document.querySelectorAll('.allergyOption input[type="checkbox"]:not(#allergy_none):checked').length > 0;
    if (hasChecked) {
        document.getElementById('allergy_none').checked = false;
    }
}

function toggleOtherAllergy() {
    const otherCheckbox = document.getElementById('allergy_other');
    document.getElementById('alergie_other').style.display = otherCheckbox.checked ? 'block' : 'none';
    if (otherCheckbox.checked) {
        document.getElementById('allergy_none').checked = false;
    }
}
</script>
      <input type="text" id="alergie_other" name="alergie_other" style="display:none;" placeholder="Špecifikujte">

      <label for="gdpr">Súhlasím so spracovaním osobných údajov:</label>
      <input type="checkbox" id="gdpr" name="gdpr" required>

      <h2>Aktivity</h2>
      <label for="aktivity_streda">Aktivity - Streda:</label>
      <select id="aktivity_streda" name="aktivity_streda" required>
          <option value="" selected disabled>Vyberte aktivitu...</option>
          <?php foreach ($queryWednesdayActivities as $activity): ?>
              <option value="<?= htmlspecialchars($activity['id']) ?>">
                  <?= htmlspecialchars($activity['nazov']) ?> - Voľné miesta: <?= $activity['volne_miesta'] ?>
              </option>
          <?php endforeach; ?>
      </select>

      <label for="aktivity_stvrtok">Aktivity - Štvrtok:</label>
      <select id="aktivity_stvrtok" name="aktivity_stvrtok" required>
          <option value="" selected disabled>Vyberte aktivitu...</option>
          <?php foreach ($queryThursdayActivities as $activity): ?>
              <option value="<?= htmlspecialchars($activity['id']) ?>">
                  <?= htmlspecialchars($activity['nazov']) ?> - Voľné miesta: <?= $activity['volne_miesta'] ?>
              </option>
          <?php endforeach; ?>
      </select>

      <label for="aktivity_piatok">Aktivity - Piatok:</label>
      <select id="aktivity_piatok" name="aktivity_piatok" required>
          <option value="" selected disabled>Vyberte aktivitu...</option>
          <?php foreach ($queryFridayActivities as $activity): ?>
              <option value="<?= htmlspecialchars($activity['id']) ?>">
                  <?= htmlspecialchars($activity['nazov']) ?> - Voľné miesta: <?= $activity['volne_miesta'] ?>
              </option>
          <?php endforeach; ?>
      </select>

      <button type="submit">Prihlásiť sa</button>
  </form>
</body>
</html>
