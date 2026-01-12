<?php
// public/meters.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';
require_login();

$customer_id = intval($_GET['customer_id'] ?? 0);

if ($customer_id) {
    $stmt = $pdo->prepare("SELECT * FROM meter WHERE customer_id = :cid");
    $stmt->execute([':cid'=>$customer_id]);
    $meters = $stmt->fetchAll();

    $cust = $pdo->prepare("SELECT name FROM customer WHERE customer_id = :cid");
    $cust->execute([':cid'=>$customer_id]);
    $custname = $cust->fetchColumn();
    echo "<h2>Meters for " . htmlspecialchars($custname) . "</h2>";
    echo "<a class='btn' href='add_meter.php?customer_id={$customer_id}'>Add Meter</a>";
} else {
    $meters = $pdo->query("SELECT m.*, c.name as customer_name, s.name as service_name
                           FROM meter m
                           JOIN customer c ON m.customer_id = c.customer_id
                           JOIN service s ON m.service_id = s.service_id
                           ORDER BY m.meter_id")->fetchAll();
    echo "<h2>All Meters</h2>";
    echo "<a class='btn' href='add_meter.php'>Add Meter</a>";
}
?>
<table class="table">
  <thead><tr><th>Meter ID</th><th>Meter No</th><th>Customer</th><th>Service</th><th>Install Date</th><th>Status</th></tr></thead>
  <tbody>
    <?php foreach($meters as $m): ?>
    <tr>
      <td><?= $m['meter_id'] ?></td>
      <td><?= htmlspecialchars($m['meter_no']) ?></td>
      <td><?= htmlspecialchars($m['customer_name'] ?? ($m['customer_id']??'')) ?></td>
      <td><?= htmlspecialchars($m['service_name'] ?? '') ?></td>
      <td><?= htmlspecialchars($m['install_date']) ?></td>
      <td><?= $m['status'] ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
