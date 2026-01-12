<?php
// public/add_customer.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';
require_login();

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
        $sql = "INSERT INTO customer (customer_type, name, address, city, phone, email)
                VALUES (:type, :name, :address, :city, :phone, :email)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':type' => $type,
            ':name' => $name,
            ':address' => $address,
            ':city' => $city,
            ':phone' => $phone,
            ':email' => $email
        ]);
        header('Location: customers.php');
        exit;
    }
}
?>
<h2>Add Customer</h2>
<?php if(!empty($errors)): ?><div class="alert"><?php foreach($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?></div><?php endif; ?>
<form method="post">
  <label>Name</label><input name="name" required />
  <label>Type</label>
  <select name="customer_type">
    <option>Household</option>
    <option>Business</option>
    <option>Government</option>
  </select>
  <label>Address</label><textarea name="address"></textarea>
  <label>City</label><input name="city" />
  <label>Phone</label><input name="phone" />
  <label>Email</label><input name="email" type="email" />
  <button class="btn" type="submit">Save</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
