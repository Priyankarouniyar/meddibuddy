<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$success = '';
$error = '';

// Handle Add Medicine
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = sanitize($_POST['name']);
    $generic_name = sanitize($_POST['generic_name']);
    $medicine_type_id = intval($_POST['medicine_type_id']);
    $manufacturer_id = intval($_POST['manufacturer_id']);
    $description = sanitize($_POST['description']);

    if(empty($name) || !$medicine_type_id) {
        $error = "Please fill in required fields.";
    } else {
        $sql = "INSERT INTO medicines (name, generic_name, medicine_type_id, manufacturer_id, description) 
                VALUES ('$name', '$generic_name', '$medicine_type_id', '$manufacturer_id', '$description')";
        if(mysqli_query($conn, $sql)) {
            $success = "Medicine added successfully!";
        } else {
            $error = "Error adding medicine.";
        }
    }
}

// Handle Delete Medicine
if(isset($_GET['delete']) && isset($_GET['id'])) {
    $medicine_id = intval($_GET['id']);
    $sql = "DELETE FROM medicines WHERE id='$medicine_id'";
    if(mysqli_query($conn, $sql)) {
        $success = "Medicine deleted!";
    } else {
        $error = "Error deleting medicine.";
    }
}

// Fetch medicine types
$types = mysqli_query($conn, "SELECT * FROM medicine_types ORDER BY name");
$types_array = [];
while($t = mysqli_fetch_assoc($types)) {
    $types_array[$t['id']] = $t['name'];
}

// Fetch manufacturers
$manufacturers = mysqli_query($conn, "SELECT * FROM manufacturers ORDER BY name");
$manufacturers_array = [];
while($m = mysqli_fetch_assoc($manufacturers)) {
    $manufacturers_array[$m['id']] = $m['name'];
}

// Fetch medicines
$medicines = mysqli_query($conn, "SELECT m.*, mt.name as type_name, mf.name as manufacturer_name FROM medicines m LEFT JOIN medicine_types mt ON m.medicine_type_id=mt.id LEFT JOIN manufacturers mf ON m.manufacturer_id=mf.id ORDER BY m.name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Medicines - MediBuddy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; }
        .medicine-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .medicine-table th, .medicine-table td { padding: 0.8rem; text-align: left; border-bottom: 1px solid #eee; }
        .medicine-table th { background: #f5f5f5; font-weight: bold; border-bottom: 2px solid #ddd; }
        .medicine-table tbody tr:hover { background: #f9f9f9; }
    </style>
</head>
<body>
<?php require_once '../includes/header.php'; ?>

<main class="main-content">
    <h2>Manage Medicines</h2>
    
    <?php 
    if($success) echo "<div class='alert alert-success'>$success</div>";
    if($error) echo "<div class='alert alert-danger'>$error</div>";
    ?>

    <!-- Add Medicine Form -->
    <div class="card" style="margin-bottom: 2rem;">
        <h3>Add New Medicine</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Medicine Name *</label>
                    <input type="text" name="name" placeholder="e.g., Aspirin 500mg" required>
                </div>
                <div class="form-group">
                    <label>Generic Name</label>
                    <input type="text" name="generic_name" placeholder="e.g., Acetylsalicylic Acid">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Medicine Type *</label>
                    <select name="medicine_type_id" required>
                        <option value="">Select Type</option>
                        <?php foreach($types_array as $id => $name): ?>
                            <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Manufacturer</label>
                    <select name="manufacturer_id">
                        <option value="">Select Manufacturer</option>
                        <?php foreach($manufacturers_array as $id => $name): ?>
                            <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="Medicine description or usage" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Medicine</button>
        </form>
    </div>

    <!-- Medicines List -->
    <h3>All Medicines</h3>
    <div class="card">
        <?php 
        if(mysqli_num_rows($medicines) === 0):
        ?>
            <p>No medicines added yet.</p>
        <?php 
        else:
        ?>
            <table class="medicine-table">
                <thead>
                    <tr>
                        <th>Medicine Name</th>
                        <th>Generic Name</th>
                        <th>Type</th>
                        <th>Manufacturer</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($medicine = mysqli_fetch_assoc($medicines)): ?>
                    <tr>
                        <td><?= htmlspecialchars($medicine['name']) ?></td>
                        <td><?= htmlspecialchars($medicine['generic_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($medicine['type_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($medicine['manufacturer_name'] ?? '-') ?></td>
                        <td>
                            <span style="background: <?= $medicine['is_active'] ? '#e8f5e9' : '#ffebee' ?>; color: <?= $medicine['is_active'] ? '#2e7d32' : '#c62828' ?>; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.85rem;">
                                <?= $medicine['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <a href="?delete=1&id=<?= $medicine['id'] ?>" class="btn btn-sm" style="background: #dc3545; color: white; padding: 0.3rem 0.6rem; font-size: 0.8rem;" onclick="return confirm('Delete this medicine?')">Delete</a>
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
