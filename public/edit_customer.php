<?php
// public/edit_customer.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';
require_login();

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: customers.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM customer WHERE customer_id = :id");
$stmt->execute([':id' => $id]);
$customer = $stmt->fetch();
if (!$customer) { header('Location: customers.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $type = $_POST['customer_type'];
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    if ($name === '') $errors[] = 'Name required.';

    if (empty($errors)) {
        $sql = "UPDATE customer SET customer_type=:type, name=:name, address=:address, city=:city, phone=:phone, email=:email WHERE customer_id=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':type'=>$type, ':name'=>$name, ':address'=>$address, ':city'=>$city, ':phone'=>$phone, ':email'=>$email, ':id'=>$id
        ]);
        header('Location: customers.php');
        exit;
    }
}
?>
<h2>Edit Customer</h2>
<?php if(!empty($errors)): ?><div class="alert"><?php foreach($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?></div><?php endif; ?>
<form method="post">
  <label>Name</label><input name="name" required value="<?= htmlspecialchars($customer['name']) ?>" />
  <label>Type</label>
  <select name="customer_type">
    <option <?= $customer['customer_type']=='Household'?'selected':'' ?>>Household</option>
    <option <?= $customer['customer_type']=='Business'?'selected':'' ?>>Business</option>
    <option <?= $customer['customer_type']=='Government'?'selected':'' ?>>Government</option>
  </select>
  <label>Address</label><textarea name="address"><?= htmlspecialchars($customer['address']) ?></textarea>
  <label>City</label><input name="city" value="<?= htmlspecialchars($customer['city']) ?>" />
  <label>Phone</label><input name="phone" value="<?= htmlspecialchars($customer['phone']) ?>" />
  <label>Email</label><input name="email" type="email" value="<?= htmlspecialchars($customer['email']) ?>" />
  <button class="btn" type="submit">Update</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
