<?php
require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_name = $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];
$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Dolphin CRM</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <!-- Header/Navigation -->
        <header>
            <div class="logo">
                <h1>🐬 Dolphin CRM</h1>
            </div>
            <nav>
                <a href="index.php" class="active">Home</a>
                <a href="contacts.php">Contacts</a>
                <a href="new_contact.php">New Contact</a>
                <?php if ($user_role === 'Admin'): ?>
                    <a href="users.php">Users</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            </nav>
            <div class="user-info">
                <span><?php echo htmlspecialchars($user_name); ?></span>
                <span class="role">(<?php echo htmlspecialchars($user_role); ?>)</span>
            </div>
        </header>

        <!-- Main Content -->
        <main>
            <h2>Dashboard</h2>
            <p>Welcome to Dolphin CRM, <?php echo htmlspecialchars($_SESSION['firstname']); ?>!</p>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="contacts.php">View All Contacts</a></li>
                        <li><a href="new_contact.php">Add New Contact</a></li>
                        <?php if ($user_role === 'Admin'): ?>
                            <li><a href="users.php">Manage Users</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</body>
</html>