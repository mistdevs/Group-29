<?php
// public/payments.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';
require_login();

$bills = $pdo->query("SELECT b.bill_id, b.total_amount, b.outstanding, b.period_start, b.period_end, m.meter_no, c.name as customer_name
                      FROM bill b
                      JOIN meter m ON b.meter_id = m.meter_id
                      JOIN customer c ON m.customer_id = c.customer_id
                      WHERE b.outstanding > 0
                      ORDER BY b.due_date ASC")->fetchAll();

$errors = [];
$success = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bill_id = intval($_POST['bill_id']);
    $amount = floatval($_POST['amount']);
    $method = $_POST['method'];

    if ($bill_id <= 0) $errors[] = 'Select bill.';
    if ($amount <= 0) $errors[] = 'Enter valid amount.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO payment (bill_id, amount, method, recorded_by) VALUES (:bill_id, :amount, :method, :rec_by)");
        $stmt->execute([':bill_id'=>$bill_id, ':amount'=>$amount, ':method'=>$method, ':rec_by'=>$_SESSION['user_id']]);
        // trigger will update bill.outstanding (trigger defined in SQL)
        $success = "Payment recorded.";
        // refresh bills
        $bills = $pdo->query("SELECT b.bill_id, b.total_amount, b.outstanding, b.period_start, b.period_end, m.meter_no, c.name as customer_name
                      FROM bill b
                      JOIN meter m ON b.meter_id = m.meter_id
                      JOIN customer c ON m.customer_id = c.customer_id
                      WHERE b.outstanding > 0
                      ORDER BY b.due_date ASC")->fetchAll();
    }
}
?>
<h2>Payments</h2>
<?php if($success): ?><div class="alert success"><?=htmlspecialchars($success)?></div><?php endif; ?>
<?php if(!empty($errors)): ?><div class="alert"><?php foreach($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?></div><?php endif; ?>

<form method="post">
  <label>Bill (with outstanding)</label>
  <select name="bill_id">
    <option value="">-- select bill --</option>
    <?php foreach($bills as $b): ?>
      <option value="<?=$b['bill_id']?>"><?=htmlspecialchars($b['customer_name']." - ".$b['meter_no']." (".$b['period_start']." to ".$b['period_end'].") - Outstanding: ".$b['outstanding'])?></option>
    <?php endforeach; ?>
  </select>
  <label>Amount</label><input name="amount" />
  <label>Method</label>
  <select name="method"><option>Cash</option><option>Card</option><option>Online</option></select>
  <button class="btn" type="submit">Record Payment</button>
</form>

<h3>Recent Payments</h3>
<table class="table">
  <thead><tr><th>ID</th><th>Bill</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead>
  <tbody>
    <?php
    $payments = $pdo->query("SELECT p.*, b.bill_id, m.meter_no, c.name as customer_name FROM payment p
                             JOIN bill b ON p.bill_id = b.bill_id
                             JOIN meter m ON b.meter_id = m.meter_id
                             JOIN customer c ON m.customer_id = c.customer_id
                             ORDER BY p.payment_date DESC LIMIT 50")->fetchAll();
    foreach($payments as $p): ?>
    <tr>
      <td><?= $p['payment_id'] ?></td>
      <td><?= htmlspecialchars($p['customer_name'].' - '.$p['meter_no'].' (bill '.$p['bill_id'].')') ?></td>
      <td><?= number_format($p['amount'],2) ?></td>
      <td><?= htmlspecialchars($p['method']) ?></td>
      <td><?= htmlspecialchars($p['payment_date']) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
