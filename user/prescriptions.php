<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];
$success = '';
$error = '';
$editPrescription = null;
$editMedicines = [];

// Upload directory
$uploads_dir = '../uploads/prescriptions';
if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);

// =========================
// Handle Add/Edit/Delete
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // ----------------- Add/Edit Prescription -----------------
    if ($action === 'add_prescription' || $action === 'edit_prescription_submit') {
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

        if (!$family_member_id || !$doctor_id) $error = "Please fill all required fields.";

        if (!$error) {
            if ($prescription_id > 0) {
                // UPDATE
                if ($prescription_file) {
                    $stmt = $conn->prepare("UPDATE prescriptions SET family_member_id=?, doctor_id=?, prescription_date=?, description=?, prescription_file=? WHERE id=? AND user_id=?");
                    $stmt->bind_param("iisssii", $family_member_id, $doctor_id, $prescription_date, $description, $prescription_file, $prescription_id, $userId);
                } else {
                    $stmt = $conn->prepare("UPDATE prescriptions SET family_member_id=?, doctor_id=?, prescription_date=?, description=? WHERE id=? AND user_id=?");
                    $stmt->bind_param("iissii", $family_member_id, $doctor_id, $prescription_date, $description, $prescription_id, $userId);
                }
                $stmt->execute();
                $success = "Prescription updated successfully!";
                // Delete old medicines
                mysqli_query($conn, "DELETE FROM prescription_medicine WHERE prescription_id='$prescription_id'");
            } else {
                // INSERT
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

    // ----------------- Delete Prescription -----------------
    if ($action === 'delete_prescription') {
        $prescription_id = intval($_POST['prescription_id']);
        mysqli_query($conn, "DELETE FROM prescriptions WHERE id='$prescription_id' AND user_id='$userId'");
        mysqli_query($conn, "DELETE FROM prescription_medicine WHERE prescription_id='$prescription_id'");
        $success = "Prescription deleted!";
    }

    // ----------------- Edit Prescription (Pre-fill Modal) -----------------
    if ($action === 'edit_prescription') {
        $prescription_id = intval($_POST['prescription_id']);
        $res = mysqli_query($conn, "SELECT * FROM prescriptions WHERE id='$prescription_id' AND user_id='$userId'");
        $editPrescription = mysqli_fetch_assoc($res);
        $meds = mysqli_query($conn, "SELECT * FROM prescription_medicine WHERE prescription_id='$prescription_id'");
        while ($row = mysqli_fetch_assoc($meds)) $editMedicines[] = $row;
    }
}

// =========================
// Fetch Data
// =========================
$members_array = [];
$members = mysqli_query($conn, "SELECT id,name FROM family_members WHERE user_id='$userId' AND is_active=1");
while ($m = mysqli_fetch_assoc($members)) $members_array[$m['id']] = $m['name'];

$doctors_array = [];
$doctors = mysqli_query($conn, "SELECT id,first_name,last_name,specialization FROM doctors WHERE is_active=1");
while ($d = mysqli_fetch_assoc($doctors)) $doctors_array[$d['id']] = "Dr. ".$d['first_name'].' '.$d['last_name']." (".$d['specialization'].")";

$medicines_array = [];
$medicines = mysqli_query($conn, "SELECT id,name FROM medicines WHERE is_active=1");
while ($m = mysqli_fetch_assoc($medicines)) $medicines_array[$m['id']] = $m['name'];

$prescriptions = mysqli_query($conn, "
SELECT p.*, fm.name AS member_name, d.first_name, d.last_name, d.specialization
FROM prescriptions p
JOIN family_members fm ON p.family_member_id=fm.id
JOIN doctors d ON p.doctor_id=d.id
WHERE p.user_id='$userId'
ORDER BY p.prescription_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Prescriptions - MediBuddy</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
/* === Family Members Style Copied === */
.doctors-container { max-width:1200px; margin:30px auto; padding:20px; }
.header-section { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px; }
.add-doctor-btn { padding:12px 24px; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; border:none; border-radius:8px; cursor:pointer; font-size:16px; font-weight:bold; }
.add-doctor-btn:hover { transform:translateY(-2px); }
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center; }
.modal.active { display:flex; }
.modal-content { background:white; padding:30px; border-radius:12px; width:100%; max-width:700px; max-height:60vh; overflow-y:auto; box-shadow:0 10px 40px rgba(0,0,0,0.2); }
.modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
.modal-header h2 { margin:0; color:#333; }
.close-btn { background:none; border:none; font-size:28px; cursor:pointer; color:#666; }
.form-group { margin-bottom:15px; }
.form-group label { display:block; margin-bottom:5px; color:#333; font-weight:500; }
.form-group input, .form-group select, .form-group textarea { width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px; box-sizing:border-box; }
.form-actions { display:flex; gap:10px; margin-top:20px; }
.btn-submit { flex:1; padding:12px; background:#667eea; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:bold; }
.btn-cancel { flex:1; padding:12px; background:#ddd; color:#333; border:none; border-radius:6px; cursor:pointer; }
.doctors-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:20px; }
.doctor-card { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.1); transition:transform 0.3s, box-shadow 0.3s; }
.doctor-card:hover { transform:translateY(-5px); box-shadow:0 8px 16px rgba(0,0,0,0.15); }
.doctor-name { font-size:18px; font-weight:bold; color:#333; margin-bottom:8px; }
.doctor-specialization { color:#667eea; font-weight:600; margin-bottom:12px; }
.doctor-info { font-size:14px; color:#666; margin-bottom:8px; line-height:1.6; }
.doctor-info strong { color:#333; }
.card-actions { display:flex; gap:8px; margin-top:15px; padding-top:15px;border-top:1px solid #eee; }
.btn-edit, .btn-delete { flex:1; padding:8px; border:none; border-radius:6px; cursor:pointer; font-weight:bold; transition:opacity 0.3s; }
.btn-edit { background:#667eea; color:white; }
.btn-delete { background:#e74c3c; color:white; }
.btn-edit:hover, .btn-delete:hover { opacity:0.8; }
.alert { padding:15px; border-radius:8px; margin-bottom:20px; }
.alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
.alert-error { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
/* Medicine row */
/* Medicine row wrapper */
.medicine-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 15px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #f9f9f9;
    align-items: center;
}

/* Each field */
.medicine-row select,
.medicine-row input {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
}

/* Flex-grow fields nicely */
.medicine-row select { flex: 2; min-width: 150px; }
.medicine-row input { flex: 1; min-width: 80px; }

/* Remove button */
.medicine-row button {
    flex: 0;
    padding: 8px 12px;
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
.medicine-row button:hover { opacity: 0.8; }

/* Add Medicine button */
#medicine-container + button {
    margin-top: 10px;
    padding: 10px 15px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
#medicine-container + button:hover { opacity: 0.9; }
</style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="doctors-container">
    <div class="header-section">
        <h1>Manage Prescriptions</h1>
        <button class="add-doctor-btn" onclick="openModal()">+ Add Prescription</button>
    </div>

    <?php if($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="doctors-grid">
        <?php while($p = mysqli_fetch_assoc($prescriptions)): ?>
        <div class="doctor-card">
            <div class="doctor-name"><?= htmlspecialchars($p['member_name']) ?> - <?= $p['prescription_date'] ?></div>
            <div class="doctor-specialization">Dr. <?= htmlspecialchars($p['first_name'].' '.$p['last_name']) ?> (<?= htmlspecialchars($p['specialization']) ?>)</div>
            <?php if($p['prescription_file']): ?>
            <div class="doctor-info"><a href="../uploads/prescriptions/<?= $p['prescription_file'] ?>" target="_blank">View Prescription File</a></div>
            <?php endif; ?>
            <?php
            $pm = mysqli_query($conn, "SELECT pm.*, m.name FROM prescription_medicine pm JOIN medicines m ON pm.medicine_id=m.id WHERE pm.prescription_id='{$p['id']}'");
            if(mysqli_num_rows($pm)>0){
                echo '<div class="doctor-info"><strong>Medicines:</strong><ul>';
                while($med=mysqli_fetch_assoc($pm)){
                    echo '<li>'.$med['name'].' – '.$med['dosage'].' '.$med['unit'].' x '.$med['quantity'].'</li>';
                }
                echo '</ul></div>';
            }
            ?>
            <div class="card-actions">
                <form method="POST">
                    <input type="hidden" name="action" value="edit_prescription">
                    <input type="hidden" name="prescription_id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn-edit">Edit</button>
                </form>
                <form method="POST">
                    <input type="hidden" name="action" value="delete_prescription">
                    <input type="hidden" name="prescription_id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn-delete" onclick="return confirm('Delete this prescription?')">Delete</button>
                </form>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Modal -->
<div id="prescriptionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add Prescription</h2>
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" id="formAction" value="add_prescription">
            <input type="hidden" name="prescription_id" id="prescriptionId">

            <div class="form-group">
                <label>Family Member *</label>
                <select name="family_member_id" id="family_member_id" required>
                    <option value="">Select</option>
                    <?php foreach($members_array as $id=>$name): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Doctor *</label>
                <select name="doctor_id" id="doctor_id" required>
                    <option value="">Select</option>
                    <?php foreach($doctors_array as $id=>$name): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Date *</label>
                <input type="date" name="prescription_date" id="prescription_date" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="description"></textarea>
            </div>
            <div class="form-group">
                <label>Prescription File</label>
                <input type="file" name="prescription_file" id="prescription_file">
            </div>

            <h4>Medicines</h4>
        
            <div id="medicine-container"></div>
            <div id="medicine-template" class="medicine-row" style="display:none;">
                <select name="medicine_id[]">
                    <option value="">Select Medicine</option>
                    <?php foreach($medicines_array as $id=>$name): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="dosage[]" placeholder="Dosage">
                <input type="text" name="unit[]" placeholder="Unit">
                <input type="number" name="quantity[]" placeholder="Quantity" value="1" min="1"><br><br>
                <button type="button" onclick="removeMedicineRow(this)">Remove</button>
                
            </div>
            <button type="button" onclick="addMedicineRow()" >+ Add Medicine</button>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Save Prescription</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(){
    document.getElementById('prescriptionModal').classList.add('active');
    document.getElementById('modalTitle').textContent='Add Prescription';
    document.getElementById('formAction').value='add_prescription';
    document.getElementById('prescriptionId').value='';
    document.querySelector('form').reset();
    document.getElementById('medicine-container').innerHTML='';
}

function closeModal(){ document.getElementById('prescriptionModal').classList.remove('active'); }

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

// Pre-fill modal for edit
<?php if($editPrescription): ?>
openModal();
document.getElementById('modalTitle').textContent='Edit Prescription';
document.getElementById('formAction').value='edit_prescription_submit';
document.getElementById('prescriptionId').value='<?= $editPrescription['id'] ?>';
document.getElementById('family_member_id').value='<?= $editPrescription['family_member_id'] ?>';
document.getElementById('doctor_id').value='<?= $editPrescription['doctor_id'] ?>';
document.getElementById('prescription_date').value='<?= $editPrescription['prescription_date'] ?>';
document.getElementById('description').value='<?= addslashes($editPrescription['description']) ?>';

<?php foreach($editMedicines as $med): ?>
addMedicineRow('<?= $med['medicine_id'] ?>','<?= addslashes($med['dosage']) ?>','<?= addslashes($med['unit']) ?>','<?= $med['quantity'] ?>');
<?php endforeach; ?>
<?php endif; ?>
</script>
</body>
</html>
