<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin(); // Only admin can access

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
        $error = "Please fill required fields.";
    } else {
        $sql = "INSERT INTO medicines 
                (name, generic_name, medicine_type_id, manufacturer_id, description, is_verified) 
                VALUES ('$name', '$generic_name', '$medicine_type_id', '$manufacturer_id', '$description', 0)";
        if(mysqli_query($conn, $sql)) {
            $success = "Medicine added successfully! Pending verification.";
        } else {
            $error = "Error adding medicine: ".mysqli_error($conn);
        }
    }
}

// Fetch medicine types
$types_array = [];
$types = mysqli_query($conn, "SELECT * FROM medicine_types ORDER BY name");
while($t = mysqli_fetch_assoc($types)) $types_array[$t['id']] = $t['name'];

// Fetch manufacturers
$manufacturers_array = [];
$manufacturers = mysqli_query($conn, "SELECT * FROM manufacturers ORDER BY name");
while($m = mysqli_fetch_assoc($manufacturers)) $manufacturers_array[$m['id']] = $m['name'];

// Fetch all medicines
$medicines = mysqli_query($conn, "
    SELECT m.*, mt.name as type_name, mf.name as manufacturer_name
    FROM medicines m
    LEFT JOIN medicine_types mt ON m.medicine_type_id=mt.id
    LEFT JOIN manufacturers mf ON m.manufacturer_id=mf.id
    ORDER BY m.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Medicines - MediBuddy</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body{font-family:Arial,sans-serif;background:#f4f4f9;padding:20px;}
.container{max-width:1200px;margin:auto;}
h2{margin-bottom:1rem;}
.add-btn{padding:12px 24px;background:linear-gradient(135deg,#667eea,#764ba2);color:white;border:none;border-radius:8px;font-weight:bold;cursor:pointer;margin-bottom:20px;}
.add-btn:hover{transform:translateY(-2px);}
.card{background:white;padding:20px;border-radius:12px;box-shadow:0 5px 15px rgba(0,0,0,0.1);transition:transform 0.3s; margin-bottom:20px;}
.card:hover{transform:translateY(-5px);}
.card-title{font-weight:bold;font-size:18px;color:#333;margin-bottom:8px;}
.card-subtitle{color:#667eea;font-weight:600;margin-bottom:12px;}
.card-info{font-size:14px;color:#666;margin-bottom:8px;line-height:1.6;}
.card-info strong{color:#333;}
.card-actions{display:flex;gap:8px;margin-top:15px;}
.btn-action{flex:1;padding:8px;border:none;border-radius:6px;cursor:pointer;font-weight:bold;color:white;transition:opacity 0.3s;}
.btn-verify{background:#4caf50;}
.btn-delete{background:#e74c3c;}
.btn-action:hover{opacity:0.85;}
.alert{padding:15px;border-radius:8px;margin-bottom:20px;}
.alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;}
.alert-error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}
.modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;justify-content:center;align-items:center;}
.modal.active{display:flex;}
.modal-content{background:white;padding:30px;border-radius:12px;width:90%;max-width:600px;max-height:60vh;overflow-y:auto;box-shadow:0 10px 40px rgba(0,0,0,0.2);}
.modal-header{margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;}
.close-btn{background:none;border:none;font-size:28px;cursor:pointer;color:#666;}
.form-group{margin-bottom:15px;}
.form-group label{display:block;margin-bottom:5px;color:#333;font-weight:500;}
.form-group input, .form-group select, .form-group textarea{width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:14px;}
.form-actions{display:flex;gap:10px;margin-top:20px;}
.btn-submit{flex:1;padding:12px;background:#667eea;color:white;border:none;border-radius:6px;cursor:pointer;font-weight:bold;}
.btn-cancel{flex:1;padding:12px;background:#ddd;color:#333;border:none;border-radius:6px;cursor:pointer;}
</style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2>Manage Medicines</h2>
        <button class="add-btn" onclick="openModal()">+ Add Medicine</button>
    </div>

    <?php if($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;">
        <?php while($med=mysqli_fetch_assoc($medicines)): ?>
        <div class="card">
            <div class="card-title"><?= htmlspecialchars($med['name']) ?></div>
            <div class="card-subtitle"><?= htmlspecialchars($med['type_name'] ?? '-') ?> | <?= htmlspecialchars($med['manufacturer_name'] ?? '-') ?></div>
            <div class="card-info"><strong>Generic:</strong> <?= htmlspecialchars($med['generic_name'] ?: '-') ?></div>
            <div class="card-info"><strong>Description:</strong> <?= htmlspecialchars($med['description'] ?: '-') ?></div>
            <div class="card-info"><strong>Status:</strong> <?= $med['is_verified'] ? 'Verified' : 'Pending' ?></div>
            <div class="card-actions">
                <a href="verify-medicines.php" class="btn-action btn-verify">Verify</a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Add Medicine Modal -->
<div id="medicineModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Medicine</h2>
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Medicine Name *</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Generic Name</label>
                <input type="text" name="generic_name">
            </div>
            <div class="form-group">
                <label>Medicine Type *</label>
                <select name="medicine_type_id" required>
                    <option value="">Select Type</option>
                    <?php foreach($types_array as $id=>$name): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Manufacturer</label>
                <select name="manufacturer_id">
                    <option value="">Select Manufacturer</option>
                    <?php foreach($manufacturers_array as $id=>$name): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-submit">Add Medicine</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(){document.getElementById('medicineModal').classList.add('active');}
function closeModal(){document.getElementById('medicineModal').classList.remove('active');}
// Close modal on outside click
window.onclick=function(e){if(e.target==document.getElementById('medicineModal'))closeModal();}
</script>
</body>
</html>
