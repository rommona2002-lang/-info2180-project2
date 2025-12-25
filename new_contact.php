<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $conn->prepare("SELECT id, firstname, lastname From Users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $required = ['title', 'firstname', 'lastname', 'email', 
    'type', 'assigned_to'];

    foreach($required as $entry){
        if(empty($_POST[$entry])){
            $error = 'Please fill in all required fields.';
            break;
        }
    }

    //sanitize
    if(empty($error)){
        $title = trim($_POST['title']);
        $firstname = trim($_POST['firstname']);
        $lastname = trim($_POST['lastname']);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $telephone = trim($_POST['telephone'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $type = $_POST['type'];
        $assigned_to = (int)$_POST['assigned_to'];
        $created_by = $_SESSION['user_id'];

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $error = 'Invalid email address';
        }else{
            $sql = "
            INSERT into Contacts
            (title, firstname, lastname, email, telephone, company, type, assigned_to,
            created_by, created_at, updated_at)
            VALUES
            (:title, :firstname, :lastname, :email, :telephone, :company, :type, :assigned_to,
            :created_by, NOW(), NOW())
            ";
            try{
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'title' => $title,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'email' => $email,
                    'telephone' => $telephone,
                    'company' => $company,
                    'type' => $type,
                    'assigned_to' => $assigned_to,
                    'created_by' => $created_by
                ]);

                $success = 'Contact added successfully';
            }catch(PDOException $e){

                $error = 'Error adding contact. Please try again.';

            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact - Dolphin CRM</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <h1>🐬 Dolphin CRM</h1>
            </div>
            <nav>
                <a href="index.php">Home</a>
                <a href="new_contact.php" class="active">New Contact</a>
                <a href="users.php">Users</a>
                <a href="logout.php">Logout</a>
            </nav>

        </header>

        <main>
            <h2>New Contact</h2>
            <div class="form-section">
                <?php if ($success): ?>
                    <p class="success-message"><?= htmlspecialchars($success) ?></p>
                <?php endif; ?>

                <?php if ($error): ?>
                    <p class="error-message"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <form method="POST" class="contact-form">
                    <!Title>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">Title *</label>
                            <select name="title"  id="title" required>
                                <option value="">Select</option>
                                <option value="Mr">Mr</option>
                                <option value="Mrs">Mrs</option>
                                <option value="Ms">Ms</option>
                                <option value="Dr">Dr</option>
                            </select>
                        </div>
                    </div>
                    
                    <!First Name & Last Name>
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
                    
                    <!Email & Telephone>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label foe="telephone">Telephone</label>
                            <input type="text" id="telephone" name="telephone">
                        </div>
                    </div>
                    
                    <!Company & Type>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="company">Company</label>
                            <input type="text" id="company" name="company">
                        </div>
                        
                        <div class="form-group">
                            <label for="type"> Type *</label>
                            <select name="type" id="type" required>
                                <option value="">Select</option>
                                <option value="Sales Lead">Sales Lead</option>
                                <option value="Support">Support</option>
                            </select>
                        </div>
                    </div>
                    
                    <!Assigned to>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="assigned_to">Assigned To *</label>
                            <select name="assigned_to" id="assigned_to" required>
                                <option value="">Select</option>
                                <?php foreach($users as $user): ?>
                                    <option value="<?= $user['id']?>">
                                    <?= htmlspecialchars($user['firstname']. ' '.$user['lastname'])?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    

                    <button type="submit" class="btn-primary">Save</button>
            
                </form>
            </div>
        </main>
    </div>
</body>
</html>