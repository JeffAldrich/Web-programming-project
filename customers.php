<?php

require_once "php/db.php";

$sql = "
    SELECT id, username, email
    FROM users
    ORDER BY id ASC
";

$result = $conn->query($sql);

$users = [];

if ($result) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>JAC - Registered Users</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

    <header>

        <img src="Images/Final LOGO.svg" alt="JAC Logo" class="logo">

        <nav>
            <a href="index.php#home">HOME</a>
            <a href="index.php#about">ABOUT US</a>
            <a href="index.php#collections">COLLECTION</a>
            <a href="store.php">STORE</a>
            <a href="index.php#contact">CONTACT</a>
        </nav>

    </header>


    <section class="student-page">

        <h1>REGISTERED USERS</h1>

        <p>
            <a href="index.php">← BACK TO HOME</a>
        </p>


        <?php if (empty($users)): ?>

            <p>No users registered yet.</p>

        <?php else: ?>

            <table border="1" cellpadding="10" cellspacing="0">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($users as $user): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($user['id']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($user['username']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($user['email']) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php endif; ?>

    </section>


</body>

</html>