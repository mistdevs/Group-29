<?php
// public/generate_bill.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';
require_login();

$meters = $pdo->query("SELECT m.meter_id, m.meter_no, c.name AS customer_name, s.name AS service_name
                       FROM meter m
                       JOIN customer c ON m.customer_id = c.customer_id
                       JOIN service s ON m.service_id = s.service_id
                       ORDER BY c.name, m.meter_no")->fetchAll();

$errors = [];
$success = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meter_id = intval($_POST['meter_id']);
    $start = $_POST['period_start'];
    $end = $_POST['period_end'];

    if ($meter_id <= 0) $errors[] = 'Select meter.';
    if (!$start || !$end) $errors[] = 'Select period start and end.';
    if (empty($errors)) {
        // call stored procedure sp_generate_bill_for_meter (created in SQL file)
        try {
            $stmt = $pdo->prepare("CALL sp_generate_bill_for_meter(:meter, :startd, :endd, :genby)");
            $stmt->execute([':meter'=>$meter_id, ':startd'=>$start, ':endd'=>$end, ':genby'=>$_SESSION['user_id']]);
            // consume any remaining result sets
            do { } while ($stmt->nextRowset());
            $success = "Bill generated successfully (check Bills in Reports).";
        } catch (PDOException $e) {
            $errors[] = "Error generating bill: " . $e->getMessage();
        }
    }
}
?>
<h2>Generate Bill</h2>
<?php if($success): ?><div class="alert success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if(!empty($errors)): ?><div class="alert"><?php foreach($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?></div><?php endif; ?>

<form method="post">
  <label>Meter</label>
  <select name="meter_id">
    <option value="">-- select meter --</option>
    <?php foreach($meters as $m): ?>
      <option value="<?=$m['meter_id']?>"><?=htmlspecialchars($m['customer_name'].' — '.$m['meter_no'].' ('.$m['service_name'].')')?></option>
    <?php endforeach; ?>
  </select>
  <label>Period Start</label><input type="date" name="period_start" />
  <label>Period End</label><input type="date" name="period_end" />
  <button class="btn" type="submit">Generate Bill</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
