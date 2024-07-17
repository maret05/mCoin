
<!DOCTYPE html>
<html lang="">
    <head>
        <title>
            Mcoin Order
        </title>

        <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        </style>
    </head>
    <body>
        <div>
            <h3> Orders </h3>
            <form action="McoinController.php" method="POST">
                <table>
                    <th>
                        <tr>
                            <td>#</td>
                            <td>date</td>
                            <td>status</td>
                            <td>price</td>
                            <td>items count</td>
                        </tr>
                    </th>
                    <tbody>
                        <?php foreach ($orders['orders'] as $order): ?>
                        <tr>
                            <td><?php echo $order['order_id']; ?></td>
                            <td><?php echo $order['date']; ?></td>
                            <td><?php echo $order['status']; ?></td>
                            <td><?php echo $order['total_amount']; ?></td>
                            <td><?php echo $order['items_count']; ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this order?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </form>
        </div>
    </body>
</html>
