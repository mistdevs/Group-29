<?php
// public/reports.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';
require_login();

$unpaid = $pdo->query("SELECT * FROM vw_unpaid_bills ORDER BY due_date ASC LIMIT 200")->fetchAll();
$revenue = $pdo->query("SELECT * FROM vw_monthly_revenue ORDER BY month DESC LIMIT 12")->fetchAll();
?>
<h2>Reports</h2>

<h3>Unpaid / Outstanding Bills</h3>
<table class="table">
  <thead><tr><th>Bill ID</th><th>Customer</th><th>Service</th><th>Period</th><th>Total</th><th>Outstanding</th><th>Due Date</th></tr></thead>
  <tbody>
    <?php foreach($unpaid as $u): ?>
    <tr>
      <td><?= $u['bill_id'] ?></td>
      <td><?= htmlspecialchars($u['customer']) ?></td>
      <td><?= htmlspecialchars($u['service']) ?></td>
      <td><?= htmlspecialchars($u['period_start']).' to '.htmlspecialchars($u['period_end']) ?></td>
      <td><?= number_format($u['total_amount'],2) ?></td>
      <td><?= number_format($u['outstanding'],2) ?></td>
      <td><?= htmlspecialchars($u['due_date']) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<h3>Monthly Revenue (last 12 months)</h3>
<table class="table">
  <thead><tr><th>Month</th><th>Total Collected</th></tr></thead>
  <tbody>
    <?php foreach($revenue as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r['month']) ?></td>
      <td><?= number_format($r['total_collected'],2) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
