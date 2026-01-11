<?php
// public/log_reading.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';
require_login();

$meters = $pdo->query("SELECT m.meter_id, m.meter_no, c.name as customer_name, s.name as service_name
                       FROM meter m
                       JOIN customer c ON m.customer_id = c.customer_id
                       JOIN service s ON m.service_id = s.service_id
                       ORDER BY c.name, m.meter_no")->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meter_id = intval($_POST['meter_id']);
    $reading_date = $_POST['reading_date'];
    $value = $_POST['reading_value'];

    if ($meter_id <= 0) $errors[] = 'Select meter.';
    if ($reading_date === '') $errors[] = 'Reading date required.';
    if ($value === '' || !is_numeric($value)) $errors[] = 'Valid reading value required.';

    if (empty($errors)) {
        // unique constraint prevents duplicate same-date reading
        $sql = "INSERT INTO meter_reading (meter_id, reading_date, reading_value, recorded_by)
                VALUES (:meter_id, :reading_date, :reading_value, :rec_by)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':meter_id'=>$meter_id, ':reading_date'=>$reading_date, ':reading_value'=>$value,
            ':rec_by'=>$_SESSION['user_id']
        ]);
        header('Location: readings.php');
        exit;
    }
}
?>
<h2>Log Meter Reading</h2>
<?php if(!empty($errors)): ?><div class="alert"><?php foreach($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?></div><?php endif; ?>
<form method="post">
  <label>Meter</label>
  <select name="meter_id" required>
    <option value="">-- select meter --</option>
    <?php foreach($meters as $m): ?>
      <option value="<?=$m['meter_id']?>"><?=htmlspecialchars($m['customer_name'].' — '.$m['meter_no'].' ('.$m['service_name'].')')?></option>
    <?php endforeach; ?>
  </select>
  <label>Reading Date</label><input type="date" name="reading_date" required />
  <label>Reading Value</label><input name="reading_value" required />
  <button class="btn" type="submit">Save</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
