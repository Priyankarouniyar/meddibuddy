<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireAdmin();

// Handle verification
if($_SERVER['REQUEST_METHOD']==='POST') {
    $medicine_id = intval($_POST['medicine_id']);
    if(isset($_POST['verify'])) {
        mysqli_query($conn, "UPDATE medicines SET is_verified=1 WHERE id=$medicine_id");
        setAlert("Medicine verified successfully!", "success");
    } elseif(isset($_POST['reject'])) {
        mysqli_query($conn, "DELETE FROM medicines WHERE id=$medicine_id");
        setAlert("Medicine rejected and deleted!", "warning");
    }
    redirect('verify-medicines.php');
}

// Fetch unverified medicines
$medicines = mysqli_query($conn, "
    SELECT m.*, mt.name AS type_name, mf.name AS manufacturer_name
    FROM medicines m
    LEFT JOIN medicine_types mt ON m.medicine_type_id=mt.id
    LEFT JOIN manufacturers mf ON m.manufacturer_id=mf.id
    WHERE m.is_verified=0
    ORDER BY m.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify Medicines - MediBuddy</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body{font-family:Arial,sans-serif;background:#f4f4f9;padding:20px;}
.container{max-width:1200px;margin:auto;}
h2{margin-bottom:1rem;}
.card{background:white;padding:20px;border-radius:12px;box-shadow:0 5px 15px rgba(0,0,0,0.1);transition:transform 0.3s;margin-bottom:20px;}
.card:hover{transform:translateY(-5px);}
.card-title{font-weight:bold;font-size:18px;color:#333;margin-bottom:8px;}
.card-subtitle{color:#667eea;font-weight:600;margin-bottom:12px;}
.card-info{font-size:14px;color:#666;margin-bottom:8px;line-height:1.6;}
.card-info strong{color:#333;}
.card-actions{display:flex;gap:8px;margin-top:15px;}
.btn-action{flex:1;padding:8px;border:none;border-radius:6px;cursor:pointer;font-weight:bold;color:white;transition:opacity 0.3s;}
.btn-verify{background:#4caf50;}
.btn-reject{background:#e74c3c;}
.btn-action:hover{opacity:0.85;}
.alert{padding:15px;border-radius:8px;margin-bottom:20px;}
.alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;}
.alert-warning{background:#fff3cd;color:#856404;border:1px solid #ffeeba;}
</style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container">
    <h2>Verify Medicines</h2>
    <?php if(mysqli_num_rows($medicines)==0): ?>
        <p>No medicines pending verification.</p>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;">
            <?php while($med=mysqli_fetch_assoc($medicines)): ?>
            <div class="card">
                <div class="card-title"><?= htmlspecialchars($med['name']) ?></div>
                <div class="card-subtitle"><?= htmlspecialchars($med['type_name'] ?? '-') ?> | <?= htmlspecialchars($med['manufacturer_name'] ?? '-') ?></div>
                <div class="card-info"><strong>Generic:</strong> <?= htmlspecialchars($med['generic_name'] ?: '-') ?></div>
                <div class="card-info"><strong>Description:</strong> <?= htmlspecialchars($med['description'] ?: '-') ?></div>
                <div class="card-actions">
                    <form method="POST" style="flex:1;">
                        <input type="hidden" name="medicine_id" value="<?= $med['id'] ?>">
                        <button type="submit" name="verify" class="btn-action btn-verify">Verify</button>
                    </form>
                    <form method="POST" style="flex:1;" onsubmit="return confirm('Reject this medicine?');">
                        <input type="hidden" name="medicine_id" value="<?= $med['id'] ?>">
                        <button type="submit" name="reject" class="btn-action btn-reject">Reject</button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
