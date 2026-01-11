<?php
// includes/header.php
session_start();
function is_logged_in() { return isset($_SESSION['user_id']); }
function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>U-UMS</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="/u-ums/assets/css/style.css">
</head>
<body>
<header class="site-header">
  <div class="wrap">
    <h1><a href="/u-ums/public/dashboard.php">Utility Management System</a></h1>
    <?php if(is_logged_in()): ?>
      <nav class="top-nav">
        <a href="/u-ums/public/dashboard.php">Dashboard</a>
        <a href="/u-ums/public/customers.php">Customers</a>
        <a href="/u-ums/public/meters.php">Meters</a>
        <a href="/u-ums/public/readings.php">Readings</a>
        <a href="/u-ums/public/reports.php">Reports</a>
        <a href="/u-ums/public/payments.php">Payments</a>
        <a href="/u-ums/public/logout.php">Logout</a>
      </nav>
    <?php endif; ?>
  </div>
</header>
<main class="wrap">
