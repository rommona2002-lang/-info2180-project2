<?php
require_once 'includes/config.php';

// Check if user is logged in
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!isset($_SESSION['user_id'])) {
    if ($isAjax) {
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        exit();
    }
    header('Location: login.php');
    exit();
}

$user_name = $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];
$user_role = $_SESSION['role'];

$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT 
        c.id AS contact_id, 
        c.title, 
        c.firstname, 
        c.lastname, 
        c.email, 
        c.company, 
        c.type
        FROM Contacts c";
$params = [];

if($filter === 'sales'){
    $sql .= " WHERE c.type = 'Sales Lead'";
}elseif($filter === 'support'){
    $sql .= " WHERE c.type = 'Support'";
}elseif($filter === 'assigned'){
    $sql .= " WHERE c.assigned_to = :user_id";
    $params['user_id'] = $_SESSION['user_id'];
}

$sql .= " ORDER BY c.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($contacts);
    exit();
 }
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

            <!Filter>
            <div class="filters">
                <h3>
                    <span class="filter-heading">Filter By:</span>
                    <a href="index.php" class="<?= $filter === 'all' ? 'active' : '' ?>">All</a>
                    <a href="index.php?filter=sales" class="<?= $filter === 'sales' ? 'active' : '' ?>">Sales Lead</a>
                    <a href="index.php?filter=support" class="<?= $filter === 'support' ? 'active' : '' ?>">Support</a>
                    <a href="index.php?filter=assigned" class="<?= $filter === 'assigned' ? 'active' : '' ?>">Assigned to me</a>
                </h3>

                <a href="new_contact.php" class="btn-switch">+ Add New Contact</a>
            </div>

            <!Contact Table>
            <div class="table-section">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Company</th>
                            <th>Type</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($contacts)): ?>
                            <tr>
                                <td colspan="5">No Contacts Found</td>
                            </tr>
                        <?php else:?>
                            <?php foreach($contacts as $contact): ?> 
                                <tr>
                                    <td><?= htmlspecialchars($contact['title']. '. '. $contact['firstname']. ' '. $contact['lastname'])?></td>
                                    <td><?= htmlspecialchars($contact['email'])?></td>
                                    <td><?= htmlspecialchars($contact['company'])?></td>
                                    <td>
                                        <span class="badge">
                                            <?= htmlspecialchars($contact['type'])?>
                                        </span>
                                    </td>
                                    <td><a href="view_contact.php?id=<?= $contact['contact_id']?>">View</a></td>
                                </tr>
                            <?php endforeach; ?>
                            
                        <?php endif; ?>
                    </tbody>


                </table>

            </div>


        </main>
    </div>

    <script src="http://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/general.js"></script>
</body>
</html>