<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$success = '';
$error = '';

// Handle Add Manufacturer
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = sanitize($_POST['name']);
    $country = sanitize($_POST['country']);

    if(empty($name)) {
        $error = "Please enter manufacturer name.";
    } else {
        $sql = "INSERT INTO manufacturers (name, country) VALUES ('$name', '$country')";
        if(mysqli_query($conn, $sql)) {
            $success = "Manufacturer added successfully!";
        } else {
            $error = "Manufacturer already exists or database error.";
        }
    }
}

// Handle Delete
if(isset($_GET['delete']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM manufacturers WHERE id='$id'";
    if(mysqli_query($conn, $sql)) {
        $success = "Manufacturer deleted!";
    }
}

// Fetch manufacturers
$manufacturers = mysqli_query($conn, "SELECT * FROM manufacturers ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Manufacturers - MediBuddy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php require_once '../includes/header.php'; ?>

<main class="main-content">
    <h2>Manage Manufacturers</h2>
    
    <?php 
    if($success) echo "<div class='alert alert-success'>$success</div>";
    if($error) echo "<div class='alert alert-danger'>$error</div>";
    ?>

    <!-- Add Manufacturer Form -->
    <div class="card" style="margin-bottom: 2rem;">
        <h3>Add New Manufacturer</h3>
        <form method="POST" style="max-width: 500px;">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Manufacturer Name *</label>
                <input type="text" name="name" placeholder="e.g., Pfizer India" required>
            </div>
            <div class="form-group">
                <label>Country</label>
                <input type="text" name="country" placeholder="e.g., India">
            </div>
            <button type="submit" class="btn btn-primary">Add Manufacturer</button>
        </form>
    </div>

    <!-- Manufacturers List -->
    <h3>All Manufacturers</h3>
    <div class="card">
        <?php 
        if(mysqli_num_rows($manufacturers) === 0):
        ?>
            <p>No manufacturers added yet.</p>
        <?php 
        else:
        ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                        <th style="padding: 0.8rem; text-align: left;">Name</th>
                        <th style="padding: 0.8rem; text-align: left;">Country</th>
                        <th style="padding: 0.8rem; text-align: left;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($mfg = mysqli_fetch_assoc($manufacturers)): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 0.8rem;"><?= htmlspecialchars($mfg['name']) ?></td>
                        <td style="padding: 0.8rem;"><?= htmlspecialchars($mfg['country'] ?? '-') ?></td>
                        <td style="padding: 0.8rem;">
                            <a href="?delete=1&id=<?= $mfg['id'] ?>" class="btn btn-sm" style="background: #dc3545; color: white; padding: 0.3rem 0.6rem; font-size: 0.8rem;" onclick="return confirm('Delete this manufacturer?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>
