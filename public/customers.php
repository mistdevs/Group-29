<?php
// public/customers.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';
require_login();

$stmt = $pdo->query("SELECT * FROM customer ORDER BY name");
$customers = $stmt->fetchAll();
?>
<h2>Customers</h2>
<a class="btn" href="add_customer.php">Add Customer</a>
<table class="table">
  <thead><tr><th>ID</th><th>Name</th><th>Type</th><th>City</th><th>Phone</th><th>Actions</th></tr></thead>
  <tbody>
    <?php foreach($customers as $c): ?>
    <tr>
      <td><?= $c['customer_id'] ?></td>
      <td><?= htmlspecialchars($c['name']) ?></td>
      <td><?= $c['customer_type'] ?></td>
      <td><?= htmlspecialchars($c['city']) ?></td>
      <td><?= htmlspecialchars($c['phone']) ?></td>
      <td>
        <a href="edit_customer.php?id=<?= $c['customer_id'] ?>">Edit</a> |
        <a href="meters.php?customer_id=<?= $c['customer_id'] ?>">Meters</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
