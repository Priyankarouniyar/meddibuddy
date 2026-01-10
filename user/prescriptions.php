<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];
$success = '';
$error = '';
$editPrescription = null; // for pre-filling form
$editMedicines = [];

// Upload directory
$uploads_dir = '../uploads/prescriptions';
if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);

/* =========================
   HANDLE FORM ACTIONS
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ADD PRESCRIPTION
    if ($_POST['action'] === 'add_prescription') {
        $prescription_id = isset($_POST['prescription_id']) ? intval($_POST['prescription_id']) : 0;
        $family_member_id = intval($_POST['family_member_id']);
        $doctor_id = intval($_POST['doctor_id']);
        $prescription_date = $_POST['prescription_date'];
        $description = sanitize($_POST['description']);
        $prescription_file = null;

        if (!empty($_FILES['prescription_file']['name'])) {
            $file = $_FILES['prescription_file'];
            $allowed = ['pdf','jpg','jpeg','png','doc','docx'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) $error = "Invalid file type.";
            elseif ($file['size'] > 5242880) $error = "File too large (max 5MB).";
            else {
                $newName = uniqid('rx_') . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], "$uploads_dir/$newName")) $prescription_file = $newName;
                else $error = "File upload failed.";
            }
        }

        if (!$family_member_id || !$doctor_id) $error = "All required fields must be filled.";

        if (!$error) {
            if ($prescription_id > 0) {
                // UPDATE existing prescription
                if ($prescription_file) {
                    $stmt = $conn->prepare("UPDATE prescriptions SET family_member_id=?, doctor_id=?, prescription_date=?, description=?, prescription_file=? WHERE id=? AND user_id=?");
                    $stmt->bind_param("iisssii", $family_member_id, $doctor_id, $prescription_date, $description, $prescription_file, $prescription_id, $userId);
                } else {
                    $stmt = $conn->prepare("UPDATE prescriptions SET family_member_id=?, doctor_id=?, prescription_date=?, description=? WHERE id=? AND user_id=?");
                    $stmt->bind_param("iissii", $family_member_id, $doctor_id, $prescription_date, $description, $prescription_id, $userId);
                }
                $stmt->execute();
                $success = "Prescription updated successfully!";
            } else {
                // INSERT new prescription
                $stmt = $conn->prepare("INSERT INTO prescriptions (user_id,family_member_id,doctor_id,created_by_user_id,prescription_date,description,prescription_file,file_uploaded_at) VALUES (?,?,?,?,?,?,?,NOW())");
                $stmt->bind_param("iiiisss", $userId, $family_member_id, $doctor_id, $userId, $prescription_date, $description, $prescription_file);
                $stmt->execute();
                $prescription_id = $stmt->insert_id;
                $success = "Prescription added successfully!";
            }

            // Add medicines
            if (isset($_POST['medicine_id'])) {
                foreach ($_POST['medicine_id'] as $i => $med_id) {
                    if ($med_id != '') {
                        $dosage = sanitize($_POST['dosage'][$i]);
                        $unit = sanitize($_POST['unit'][$i]);
                        $quantity = intval($_POST['quantity'][$i]);
                        $stmt2 = $conn->prepare("INSERT INTO prescription_medicine (prescription_id, medicine_id, dosage, unit, quantity) VALUES (?,?,?,?,?)");
                        $stmt2->bind_param("iissi", $prescription_id, $med_id, $dosage, $unit, $quantity);
                        $stmt2->execute();
                    }
                }
            }
        }
    }

    // DELETE PRESCRIPTION
    if ($_POST['action'] === 'delete_prescription') {
        $prescription_id = intval($_POST['prescription_id']);
        $stmt = $conn->prepare("DELETE FROM prescriptions WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $prescription_id, $userId);
        $stmt->execute();
        $success = "Prescription deleted!";
    }

    // EDIT PRESCRIPTION (pre-fill form)
    if ($_POST['action'] === 'edit_prescription') {
        $prescription_id = intval($_POST['prescription_id']);
        $res = mysqli_query($conn, "SELECT * FROM prescriptions WHERE id='$prescription_id' AND user_id='$userId'");
        $editPrescription = mysqli_fetch_assoc($res);
        $meds = mysqli_query($conn, "SELECT * FROM prescription_medicine WHERE prescription_id='$prescription_id'");
        while ($row = mysqli_fetch_assoc($meds)) $editMedicines[] = $row;
    }

    // EDIT MEDICINE
    if ($_POST['action'] === 'edit_medicine') {
        $pm_id = intval($_POST['pm_id']);
        $dosage = sanitize($_POST['dosage']);
        $unit = sanitize($_POST['unit']);
        $quantity = intval($_POST['quantity']);
        $stmt = $conn->prepare("UPDATE prescription_medicine SET dosage=?, unit=?, quantity=? WHERE id=?");
        $stmt->bind_param("ssii", $dosage, $unit, $quantity, $pm_id);
        $stmt->execute();
        $success = "Medicine updated!";
    }

    // DELETE MEDICINE
    if ($_POST['action'] === 'delete_medicine') {
        $pm_id = intval($_POST['pm_id']);
        $stmt = $conn->prepare("DELETE FROM prescription_medicine WHERE id=?");
        $stmt->bind_param("i", $pm_id);
        $stmt->execute();
        $success = "Medicine deleted!";
    }
}

/* =========================
   FETCH DATA
========================= */
$members = mysqli_query($conn, "SELECT id,name FROM family_members WHERE user_id='$userId' AND is_active=1");
$doctors = mysqli_query($conn, "SELECT id,first_name,last_name,specialization FROM doctors WHERE is_active=1");
$medicines = mysqli_query($conn, "SELECT id,name FROM medicines WHERE is_active=1");
$prescriptions = mysqli_query($conn, "
SELECT DISTINCT p.*, d.first_name, d.last_name, d.specialization, fm.name AS member_name
FROM prescriptions p
JOIN doctors d ON p.doctor_id=d.id
JOIN family_members fm ON p.family_member_id=fm.id
WHERE p.user_id='$userId'
ORDER BY p.prescription_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Prescriptions</title>
<style>
body { font-family: Arial,sans-serif; background:#f4f6f9; margin:0; padding:0; }
.main-content { max-width: 1000px; margin:30px auto; padding:20px; display:flex; gap:20px; }
.left, .right { flex:1; }
h2,h3 { color:#333; text-align:center; }
.card { background:#fff; padding:15px; margin-bottom:20px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.05); }
label { display:block; margin:10px 0 5px; font-weight:bold; }
input, select, textarea { width:100%; padding:8px; border-radius:4px; border:1px solid #ccc; margin-bottom:10px; }
button { background:#667eea; color:#fff; padding:8px 12px; border:none; border-radius:4px; cursor:pointer; }
button:hover { background:#5563c1; }
.alert { padding:10px; border-radius:5px; margin-bottom:15px; }
.alert-success { background:#d4edda; color:#155724; }
.alert-danger { background:#f8d7da; color:#721c24; }
ul { padding-left:20px; }
.medicine-row { display:flex; gap:10px; margin-bottom:10px; }
.medicine-row input, .medicine-row select { flex:1; }
.medicine-actions { display:flex; gap:5px; margin-top:5px; }
</style>
<script>
function addMedicineRow(med_id='',dosage='',unit='',qty=1){
    let container=document.getElementById('medicine-container');
    let template=document.getElementById('medicine-template');
    let clone=template.cloneNode(true);
    clone.style.display='flex';
    clone.removeAttribute('id');
    clone.querySelector('select').value=med_id;
    clone.querySelector('input[name="dosage[]"]').value=dosage;
    clone.querySelector('input[name="unit[]"]').value=unit;
    clone.querySelector('input[name="quantity[]"]').value=qty;
    container.appendChild(clone);
}
function removeMedicineRow(btn){ btn.parentNode.remove(); }
</script>
</head>
<body>
<?php require '../includes/header.php'; ?>
<main class="main-content">
<div class="left">
    <div class="card">
        <h3><?= $editPrescription ? 'Edit Prescription' : 'Add Prescription' ?></h3>
        <?php if($success):?><div class="alert alert-success"><?=$success?></div><?php endif;?>
        <?php if($error):?><div class="alert alert-danger"><?=$error?></div><?php endif;?>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_prescription">
            <input type="hidden" name="prescription_id" value="<?= $editPrescription['id'] ?? '' ?>">
            <label>Family Member *</label>
            <select name="family_member_id" required>
                <option value="">Select</option>
                <?php mysqli_data_seek($members,0); while($m=mysqli_fetch_assoc($members)): ?>
                <option value="<?= $m['id'] ?>" <?= ($editPrescription && $editPrescription['family_member_id']==$m['id'])?'selected':'' ?>><?= htmlspecialchars($m['name']) ?></option>
                <?php endwhile;?>
            </select>
            <label>Doctor *</label>
            <select name="doctor_id" required>
                <option value="">Select Doctor</option>
                <?php mysqli_data_seek($doctors,0); while($d=mysqli_fetch_assoc($doctors)): ?>
                <option value="<?= $d['id'] ?>" <?= ($editPrescription && $editPrescription['doctor_id']==$d['id'])?'selected':'' ?>>Dr. <?= htmlspecialchars($d['first_name'].' '.$d['last_name']) ?> (<?= htmlspecialchars($d['specialization']) ?>)</option>
                <?php endwhile;?>
            </select>
            <label>Date *</label>
            <input type="date" name="prescription_date" value="<?= $editPrescription['prescription_date'] ?? date('Y-m-d') ?>" required>
            <label>Description</label>
            <textarea name="description"><?= $editPrescription['description'] ?? '' ?></textarea>
            <label>Prescription File</label>
            <input type="file" name="prescription_file">
            <h4>Medicines</h4>
            <div id="medicine-container"></div>
            <div id="medicine-template" class="medicine-row" style="display:none;">
                <select name="medicine_id[]">
                    <option value="">Select Medicine</option>
                    <?php mysqli_data_seek($medicines,0); while($med=mysqli_fetch_assoc($medicines)): ?>
                    <option value="<?= $med['id'] ?>"><?= htmlspecialchars($med['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <input name="dosage[]" placeholder="Dosage">
                <input name="unit[]" placeholder="Unit">
                <input type="number" name="quantity[]"  placeholder="Quantity" value="1" min="1">
                <button type="button" onclick="removeMedicineRow(this)"> Remove</button>
            </div>
            <?php if($editMedicines): foreach($editMedicines as $em): ?>
            <script>addMedicineRow('<?=$em['medicine_id']?>','<?=$em['dosage']?>','<?=$em['unit']?>','<?=$em['quantity']?>');</script>
            <?php endforeach; endif;?>
            <button type="button" onclick="addMedicineRow()">Add Medicine</button><br><br>
            <button type="submit"><?= $editPrescription?'Update':'Add' ?> Prescription</button>
        </form>
    </div>
</div>

<div class="right">

<?php while($p=mysqli_fetch_assoc($prescriptions)): ?>
<div class="card">
<h3>Existing Prescriptions</h3>
<h4><?= htmlspecialchars($p['member_name']) ?> - <?= $p['prescription_date'] ?></h4>
<p><b>Doctor:</b> Dr. <?= htmlspecialchars($p['first_name'].' '.$p['last_name']) ?> (<?= htmlspecialchars($p['specialization']) ?>)</p>
<?php if($p['prescription_file']): ?>
<p><a href="../uploads/prescriptions/<?= $p['prescription_file'] ?>" target="_blank">View File</a></p>
<?php endif;?>
<form method="POST" style="display:inline-block;">
<input type="hidden" name="action" value="edit_prescription">
<input type="hidden" name="prescription_id" value="<?= $p['id'] ?>">
<button>Edit Prescription</button>
</form>
<form method="POST" style="display:inline-block;">
<input type="hidden" name="action" value="delete_prescription">
<input type="hidden" name="prescription_id" value="<?= $p['id'] ?>">
<button onclick="return confirm('Delete this prescription?')">Delete Prescription</button>
</form>

<?php
$pm = mysqli_query($conn, "SELECT pm.*, m.name FROM prescription_medicine pm JOIN medicines m ON pm.medicine_id=m.id WHERE pm.prescription_id='{$p['id']}'");
?>
<h5>Medicines</h5>
<ul>
<?php while($med=mysqli_fetch_assoc($pm)): ?>
<li>
<?= $med['name'] ?> – <?= $med['dosage'] ?> <?= $med['unit'] ?> x <?= $med['quantity'] ?>
<div class="medicine-actions">
<form method="POST">
<input type="hidden" name="action" value="edit_medicine">
<input type="hidden" name="pm_id" value="<?= $med['id'] ?>">
<input name="dosage" value="<?= $med['dosage'] ?>" placeholder="Dosage">
<input name="unit" value="<?= $med['unit'] ?>" placeholder="Unit">
<input type="number" name="quantity" value="<?= $med['quantity'] ?>" min="1" style="width:70px;"><br>
<button>Update</button>
<button onclick="return confirm('Delete this medicine?')">Delete</button>
</form>
</div>
</li>
<?php endwhile;?>
</ul>
</div>
<?php endwhile;?>
</div>
</main>
</body>
</html>
