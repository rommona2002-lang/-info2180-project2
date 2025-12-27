<?php
require_once 'includes/config.php';
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    if ($isAjax) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Not authenticated'
        ]);
        exit();
    }
    header('Location: login.php');
    exit();
}

// Check if user is Admin
if ($_SESSION['role'] !== 'Admin') {
    if ($isAjax) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Forbidden'
        ]);
        exit();
    }
    header('Location: index.php');
    exit();
}


$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    
    // Validate inputs
    if (empty($firstname) || empty($lastname) || empty($email) || empty($password) || empty($role)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Email already exists';
        } else {
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO Users (firstname, lastname, email, password, role) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$firstname, $lastname, $email, $hashed_password, $role])) {
                $success = 'User created successfully!';
            } else {
                $error = 'Failed to create user';
            }
        }
    }
}
if ($isAjax && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => empty($error),
        'error' => $error,
        'message' => $success
    ]);
    exit();
}
// Fetch all users
$stmt = $conn->query("SELECT id, firstname, lastname, email, role, created_at FROM Users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php if (!$isAjax): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Dolphin CRM</title>
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
                <a href="index.php">Home</a>
                <a href="contacts.php">Contacts</a>
                <a href="new_contact.php">New Contact</a>
                <a href="users.php" class="active">Users</a>
                <a href="logout.php">Logout</a>
            </nav>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']); ?></span>
                <span class="role">(<?php echo htmlspecialchars($_SESSION['role']); ?>)</span>
            </div>
        </header>
        <main id="main-content">
        <?php endif; ?>

        <!-- Main Content -->
        <div class="users-content">
            <h2>User Management</h2>
            
            <!-- Add New User Form -->

            
                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form id="userForm" method="POST" class="user-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstname">First Name *</label>
                            <input type="text" id="firstname" name="firstname" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="lastname">Last Name *</label>
                            <input type="text" id="lastname" name="lastname" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" id="password" name="password" required minlength="8">
                            <small>Must be at least 8 characters</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="Admin">Admin</option>
                            <option value="Member">Member</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-primary">Add User</button>
                </form>
                </div>
            
            <!-- Users Table -->
            <div class="table-section">
                <h3>All Users</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower($user['role']); ?>">
                                        <?php echo htmlspecialchars($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            
<?php if (!$isAjax): ?>
        </main>
    </div>
</body>
</html>  
<?php endif; ?>