<html>
    <head>
        <title>Inventory Dashboard</title>
    </head>
    <body>
        <h3>Active Verified Users:</h3>
        <?php echo $active_verified_users; ?>
        <hr/>
        <h3>Active Verified Attached Users:</h3>
        <?php echo $active_verified_attached_users; ?>
        <hr/>
        <h3>Active Products:</h3>
        <?php echo $active_products; ?>
        <hr/>
        <h3>Active Products Without User:</h3>
        <?php echo $active_products_without_user; ?>
        <hr/>
        <h3>Active Attached Products:</h3>
        <?php echo $active_attached_products; ?>
        <hr/>
        <h3>Active Attached Products Price Sum:</h3>
        <?php echo $active_attached_products_price; ?>
        <hr/>
        <h3>Active Attached Products Price Per User</h3>
        <table cellspacing="0" cellpadding="10" border="1" width="300">
            <thead>
                <tr>
                    <td>User</td>
                    <td>Price</td>
                </tr>
            </thead>
            <tbody>
                <?php if (count($active_attached_products_price_per_user)): ?>
                    <?php foreach($active_attached_products_price_per_user as $row): ?>
                        <tr>
                            <td><?php echo $row->user_name; ?></td>
                            <td><?php echo $row->price; ?>$</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </body>
</html>