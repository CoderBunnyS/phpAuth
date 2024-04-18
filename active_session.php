    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="./styles.css">
    <style>
        <?php include_once './styles.css'; ?>
</head>
<body>
    <header>
        <h1>User Dashboard</h1>
        <nav>
            <a href="/logout">Log Out</a>
        </nav>
    </header>
    <main>
        <p>Welcome to your dashboard!</p>
        <!-- Additional dashboard contents -->
        echo '<pre>';
            
    print_r($session->user);
    echo '</pre>';
  
    echo '<p>You can now <a href="/logout">log out</a>.</p>';

    echo '<pre>';
    print_r($session->user);
    
    echo '</pre>';
  
    echo '<p>You can now <a href="/logout">log out</a>.</p>';
    </main>
</body>
</html>
 