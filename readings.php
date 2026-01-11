<?php
// public/readings.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';
require_login();

$readings = $pdo->query("SELECT r.*, m.meter_no, c.name as customer_name, s.name as service_name
                         FROM meter_reading r
                         JOIN meter m ON r.meter_id = m.meter_id
                         JOIN customer c ON m.customer_id = c.customer_id
                         JOIN service s ON m.service_id = s.service_id
                         ORDER BY r.reading_date DESC, r.reading_id DESC LIMIT 200")->fetchAll();
?>
<h2>Meter Readings</h2>
<a class="btn" href="log_reading.php">Log Reading</a>
<table class="table">
  <thead><tr><th>ID</th><th>Meter</th><th>Customer</th><th>Service</th><th>Date</th><th>Value</th></tr></thead>
  <tbody>
    <?php foreach($readings as $r): ?>
    <tr>
      <td><?= $r['reading_id'] ?></td>
      <td><?= htmlspecialchars($r['meter_no']) ?></td>
      <td><?= htmlspecialchars($r['customer_name']) ?></td>
      <td><?= htmlspecialchars($r['service_name']) ?></td>
      <td><?= htmlspecialchars($r['reading_date']) ?></td>
      <td><?= htmlspecialchars($r['reading_value']) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
