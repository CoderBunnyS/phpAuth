<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">

    <style>
        <?php include_once './styles/styles.css'; ?>
    </style>
</head>
<body>
    <header>
        <h1 class="site-header">Let the Adventure Begin</h1>
    </header>
    <main>
        <p >Welcome to your dashboard!</p>
        <?php
            // Display user information
            echo '<pre>';
            print_r($session->user);
            echo '</pre>';
        
            // Logout link
            
            echo '<p>You can now <a href="/logout">log out</a>.</p>';
        ?>
    </main>
</body>
</html>
