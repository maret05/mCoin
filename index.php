<?php

    require 'McoinController.php';
    $mCoinOrder = new McoinController();
    $orders = $mCoinOrder->orders();

    // Clear Order
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {

        $orderId = $_POST['order_id'];
        // $user = auth()->user();
        $user = null;
        $adminPassword = "12345678";

        $mCoinOrder->clearOrder($user, $orderId, $adminPassword);
        
        // Reload orders after deletion
        $orders = $mCoinOrder->orders();
    }

    include 'Orders.php';
?>
