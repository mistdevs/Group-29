<?php
// public/add_meter.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';
require_login();

$customer_id = intval($_GET['customer_id'] ?? 0);

$services = $pdo->query("SELECT * FROM service ORDER BY name")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meter_no = trim($_POST['meter_no']);
    $cust = intval($_POST['customer_id']);
    $service = intval($_POST['service_id']);
    $install = $_POST['install_date'] ?: null;

    if ($meter_no === '') $errors[] = 'Meter number required.';
    if ($cust <= 0) $errors[] = 'Select a customer.';

    if (empty($errors)) {
        $sql = "INSERT INTO meter (meter_no, customer_id, service_id, install_date, status)
                VALUES (:meter_no, :customer_id, :service_id, :install_date, 'Active')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':meter_no'=>$meter_no,
            ':customer_id'=>$cust,
            ':service_id'=>$service,
            ':install_date'=>$install
        ]);
        header('Location: meters.php?customer_id='.$cust);
        exit;
    }
}

$customers = $pdo->query("SELECT * FROM customer ORDER BY name")->fetchAll();
?>
<h2>Add Meter</h2>
<?php if(!empty($errors)): ?><div class="alert"><?php foreach($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?></div><?php endif; ?>
<form method="post">
  <label>Meter Number</label><input name="meter_no" required />
  <label>Customer</label>
  <select name="customer_id" required>
    <option value="">-- Select Customer --</option>
    <?php foreach($customers as $c): ?>
      <option value="<?=$c['customer_id']?>" <?= $customer_id==$c['customer_id']?'selected':'' ?>><?=htmlspecialchars($c['name'])?></option>
    <?php endforeach; ?>
  </select>
  <label>Service</label>
  <select name="service_id">
    <?php foreach($services as $s): ?>
      <option value="<?=$s['service_id']?>"><?=htmlspecialchars($s['name'])?></option>
    <?php endforeach; ?>
  </select>
  <label>Install Date</label><input type="date" name="install_date" />
  <button class="btn" type="submit">Save Meter</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
