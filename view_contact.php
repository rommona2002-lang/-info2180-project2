<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$contact_id = $_GET['id'] ?? null;
if (!$contact_id) {
    header('Location: index.php');
    exit();
}

//Contact
$stmt = $conn->prepare("SELECT c.*, 
                u.firstname AS assigned_firstname, 
                u.lastname AS assigned_lastname,
                cu.firstname AS creator_firstname,
                cu.lastname AS creator_lastname
                FROM Contacts c
                LEFT JOIN Users u ON c.assigned_to = u.id
                LEFT JOIN Users cu ON c.created_by = cu.id
                WHERE c.id = :id
                ");
$stmt->execute(['id' => $contact_id]);
$contact = $stmt->fetch(PDO::FETCH_ASSOC);


//Notes
$stmt = $conn->prepare(" SELECT n.comment, n.created_at, u.firstname, u.lastname
                        FROM Notes n
                        LEFT JOIN Users u ON n.created_by = u.id
                        WHERE n.contact_id = :id
                        ORDER BY n.created_at ASC
                        ");
$stmt->execute(['id' => $contact_id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);


$created_on = date("F j, Y", strtotime($contact['created_at']));
$updated_on = date("F j, Y", strtotime($contact['updated_at']));

// switch button
$switch_to = ($contact['type'] === 'Support') ? 'Sales Lead' : 'Support';
$switch_label = 'Switch to ' . $switch_to;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['assign_to_me'])) {
        $stmt = $conn->prepare("
            UPDATE Contacts 
            SET assigned_to = :user, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute(['user' => $_SESSION['user_id'], 'id' => $contact_id]);
    }

    if (isset($_POST['switch_type'])) {
        $stmt = $conn->prepare("
            UPDATE Contacts 
            SET type = :type, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute(['type' => $switch_to, 'id' => $contact_id]);
    }

    if (!empty($_POST['note_text'])) {
        $stmt = $conn->prepare("
            INSERT INTO Notes (contact_id, comment, created_by, created_at)
            VALUES (:contact_id, :comment, :created_by, NOW())
        ");
        $stmt->execute([
            'contact_id' => $contact_id,
            'comment' => trim($_POST['note_text']),
            'created_by' => $_SESSION['user_id']
        ]);
    }

    header("Location: view_contact.php?id=$contact_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Contact - Dolphin CRM</title>
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
                <a href="new_contact.php">New Contact</a>
                <a href="users.php">Users</a>
                <a href="logout.php">Logout</a>
            </nav>

        </header>
        
        <main>
            <h2><?= htmlspecialchars($contact['title'].' '.$contact['firstname'].' '.$contact['lastname']) ?></h2>
            <div class="contact-header">
                <div class="contactinfo">
                    <p>Created on <?= $created_on ?> by 
                        <?= htmlspecialchars($contact['creator_firstname'].' '.$contact['creator_lastname']) ?>
                    </p>
                    <p>Updated on <?= $updated_on ?></p>
                </div>

                <div class="contact-actions">
                    <form method="POST">
                        <button type="submit" name="assign_to_me" class="btn-primary">
                        Assign to Me
                        </button>
                        <button type="submit" name="switch_type" class="btn-switch">
                            <?= $switch_label ?>
                        </button>
                    </form>
                </div>
            </div>

            <div class="contact-info-grid">
                <div>
                    <label>Email</label>
                    <p><?= htmlspecialchars($contact['email']) ?></p>
                </div>
                <div>
                    <label>Telephone</label>
                    <p><?= htmlspecialchars($contact['telephone']) ?></p>
                </div>
                <div>
                    <label>Company</label>
                    <p><?= htmlspecialchars($contact['company']) ?></p>
                </div>
                <div>
                    <label>Assigned To</label>
                    <p><?= htmlspecialchars($contact['assigned_firstname'].' '.$contact['assigned_lastname']) ?></p>
                </div>
            </div>

            
            <section class="contact-notes">
                <h3>Notes</h3>
                <?php foreach ($notes as $note): ?>
                    <div class="note">
                        <p class="note-author">
                            <?= htmlspecialchars($note['firstname'].' '.$note['lastname']) ?>
                        </p>
                        <p><?= htmlspecialchars($note['comment']) ?></p>
                        <small><?= date("F j, Y \\a\\t g:i A", strtotime($note['created_at']))?></small>
                    </div>
                <?php endforeach; ?>

                <form method="POST" class="note-form">
                    <textarea name="note-text" placeholder="Enter details here" required></textarea>
                    <button type="submit" class="btn-primary">Add Note</button>
                </form>
            </section>

        </main>
    </div>

</body>
</html>
