<?php
// public/dashboard.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';
require_login();

// quick stats
$total_customers = $pdo->query('SELECT COUNT(*) FROM customer')->fetchColumn();
$total_meters = $pdo->query('SELECT COUNT(*) FROM meter')->fetchColumn();
$total_unpaid = $pdo->query('SELECT IFNULL(SUM(outstanding),0) FROM bill')->fetchColumn();
?>
<h2>Dashboard</h2>
<div class="grid">
  <div class="card">
    <h3>Total Customers</h3>
    <p class="big"><?= number_format($total_customers) ?></p>
  </div>
  <div class="card">
    <h3>Total Meters</h3>
    <p class="big"><?= number_format($total_meters) ?></p>
  </div>
  <div class="card">
    <h3>Total Outstanding (all bills)</h3>
    <p class="big"><?= number_format($total_unpaid,2) ?></p>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
