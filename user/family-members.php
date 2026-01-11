<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (isAdmin()) redirect('../auth/login.php');

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

// =========================
// Handle Add/Edit/Delete
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
    $gender_id = isset($_POST['gender_id']) ? intval($_POST['gender_id']) : 0;
    $dob = isset($_POST['dob']) ? $_POST['dob'] : '';
    $relationship_id = isset($_POST['relationship_id']) ? intval($_POST['relationship_id']) : 0;
    $medical_conditions = isset($_POST['medical_conditions']) ? sanitize($_POST['medical_conditions']) : '';
    $allergies = isset($_POST['allergies']) ? sanitize($_POST['allergies']) : '';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $emergency_contact_name = isset($_POST['emergency_contact_name']) ? sanitize($_POST['emergency_contact_name']) : '';
     $emergency_contact_phone = isset($_POST['emergency_contact_phone']) ? sanitize($_POST['emergency_contact_phone']) : '';
     $emergency_contact_relationship = isset($_POST['emergency_contact_relationship']) ? sanitize($_POST['emergency_contact_relationship']) : '';
     $emergency_doctor_name = isset($_POST['emergency_doctor_name']) ? sanitize($_POST['emergency_doctor_name']) : '';
     $emergency_doctor_phone = isset($_POST['emergency_doctor_phone']) ? sanitize($_POST['emergency_doctor_phone']) : '';
     $doctor_specialization = isset($_POST['doctor_specialization']) ? sanitize($_POST['doctor_specialization']) : '';
     $emergency_hospital_name = isset($_POST['emergency_hospital_name']) ? sanitize($_POST['emergency_hospital_name']) : '';
     $emergency_hospital_address = isset($_POST['emergency_hospital_address']) ? sanitize($_POST['emergency_hospital_address']) : '';
     $emergency_hospital_phone = isset($_POST['emergency_hospital_phone']) ? sanitize($_POST['emergency_hospital_phone']) : '';
    if ($action === 'add' || $action === 'edit') {
        if (empty($name) || !$gender_id || !$relationship_id) {
            $error = "Please fill all required fields.";
        } else {
            if ($action === 'add') {
                $sql = "INSERT INTO family_members 
                    (user_id, name, gender_id, date_of_birth, relationship_id, medical_conditions, allergies, phone, 
                    emergency_contact_name, emergency_contact_phone, emergency_contact_relationship, 
                    emergency_doctor_name, emergency_doctor_phone, doctor_specialization,
                    emergency_hospital_name, emergency_hospital_address, emergency_hospital_phone) 
                    VALUES 
                    ('$userId','$name','$gender_id','$dob','$relationship_id','$medical_conditions','$allergies','$phone',
                    '$emergency_contact_name','$emergency_contact_phone','$emergency_contact_relationship',
                    '$emergency_doctor_name','$emergency_doctor_phone','$doctor_specialization',
                    '$emergency_hospital_name','$emergency_hospital_address','$emergency_hospital_phone')";
                $msg = "added";
            } else {
                $member_id = intval($_POST['member_id']);
                $sql = "UPDATE family_members SET 
                    name='$name', gender_id='$gender_id', date_of_birth='$dob', relationship_id='$relationship_id',
                    medical_conditions='$medical_conditions', allergies='$allergies', phone='$phone',
                    emergency_contact_name='$emergency_contact_name', emergency_contact_phone='$emergency_contact_phone',
                    emergency_contact_relationship='$emergency_contact_relationship', emergency_doctor_name='$emergency_doctor_name',
                    emergency_doctor_phone='$emergency_doctor_phone', doctor_specialization='$doctor_specialization',
                    emergency_hospital_name='$emergency_hospital_name', emergency_hospital_address='$emergency_hospital_address',
                    emergency_hospital_phone='$emergency_hospital_phone'
                    WHERE id='$member_id' AND user_id='$userId'";
                $msg = "updated";
            }

            if (mysqli_query($conn, $sql)) {
                $success = "Family member $msg successfully!";
            } else {
                $error = "Error! Please try again.";
            }
        }
    } elseif ($action === 'delete') {
        $member_id = intval($_POST['member_id']);
        $sql = "DELETE FROM family_members WHERE id='$member_id' AND user_id='$userId'";
        if (mysqli_query($conn, $sql)) {
            $success = "Family member deleted successfully!";
        } else {
            $error = "Error deleting member.";
        }
    }
}

// =========================
// Fetch Genders, Relationships
// =========================
$genders_array = [];
$result = mysqli_query($conn, "SELECT * FROM genders ORDER BY name");
while ($r = mysqli_fetch_assoc($result)) $genders_array[$r['id']] = $r['name'];

$relationships_array = [];
$result = mysqli_query($conn, "SELECT * FROM relationships ORDER BY name");
while ($r = mysqli_fetch_assoc($result)) $relationships_array[$r['id']] = $r['name'];

// =========================
// Fetch Family Members
// =========================
$members_result = mysqli_query($conn, "SELECT * FROM family_members WHERE user_id='$userId' AND is_active=1 ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Family Members - MediBuddy</title>

<link rel="stylesheet" href="../assets/css/style.css">
<style>
/* Reuse styles from Doctors page */
.doctors-container { max-width: 1200px;
    margin: 0 auto;
     padding: 20px;
     }

.header-section { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; flex-wrap:wrap; gap:15px; }
.add-doctor-btn { padding:12px 24px; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; border:none; border-radius:8px; cursor:pointer; font-size:16px; font-weight:bold; }
.add-doctor-btn:hover { transform:translateY(-2px); }
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center; }
.modal.active { display:flex; }
.modal-content { background:white; padding:30px; border-radius:12px; width:90%; max-width:700px; max-height:50vh; overflow-y:auto; box-shadow:0 10px 40px rgba(0,0,0,0.2); }
.modal-header { margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; }
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
.card-actions { display:flex; gap:8px; margin-top:15px; padding-top:15px; border-top:1px solid #eee; }
.btn-edit, .btn-delete { flex:1; padding:8px; border:none; border-radius:6px; cursor:pointer; font-weight:bold; transition:opacity 0.3s; }
.btn-edit { background:#667eea; color:white; }
.btn-delete { background:#e74c3c; color:white; }
.btn-edit:hover, .btn-delete:hover { opacity:0.8; }
.alert { padding:15px; border-radius:8px; margin-bottom:20px; }
.alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
.alert-error { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
</style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="doctors-container">
    <div class="header-section">
        <h1>Manage Family Members</h1>
        <button class="add-doctor-btn" onclick="openModal()">+ Add New Member</button>
    </div>

    <?php if($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="doctors-grid">
        <?php while($member = mysqli_fetch_assoc($members_result)): ?>
        <div class="doctor-card">
            <div class="doctor-name"><?= htmlspecialchars($member['name']) ?></div>
            <div class="doctor-specialization"><?= $relationships_array[$member['relationship_id']] ?? 'N/A' ?> | <?= $genders_array[$member['gender_id']] ?? 'N/A' ?></div>
            <div class="doctor-info"><strong>DOB:</strong> <?= $member['date_of_birth'] ?: 'N/A' ?></div>
            <div class="doctor-info"><strong>Phone:</strong> <?= $member['phone'] ?: 'N/A' ?></div>
            <div class="doctor-info"><strong>Medical-condition:</strong> <?= $member['medical_conditions'] ?: 'N/A' ?></div>
            <div class="doctor-info"><strong>Allergies:</strong> <?= $member['allergies'] ?: 'N/A' ?></div>

            <div class="doctor-info"><strong>Emergency Contact:</strong> <?= $member['emergency_contact_name'] ?: 'N/A' ?> (<?= $member['emergency_contact_relationship'] ?: 'N/A' ?>)</div>
            <div class="doctor-info"><strong>Contact Phone:</strong> <?= $member['emergency_contact_phone'] ?: 'N/A' ?></div>
            <div class="doctor-info"><strong>Doctor:</strong> <?= $member['emergency_doctor_name'] ?: 'N/A' ?> (<?= $member['doctor_specialization'] ?: 'N/A' ?>)</div>
            <div class="doctor-info"><strong>Doctor Phone:</strong> <?= $member['emergency_doctor_phone'] ?: 'N/A' ?></div>
            <div class="doctor-info"><strong>Hospital:</strong> <?= $member['emergency_hospital_name'] ?: 'N/A' ?></div>
            <div class="doctor-info"><strong>Hospital Address:</strong> <?= $member['emergency_hospital_address'] ?: 'N/A' ?></div>
            <div class="doctor-info"><strong>Hospital Phone:</strong> <?= $member['emergency_hospital_phone'] ?: 'N/A' ?></div>

            <div class="card-actions">
                <button class="btn-edit" onclick='editMember(<?= json_encode($member) ?>)'>Edit</button>
                <form method="POST" style="flex:1;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                    <button type="submit" class="btn-delete" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="memberModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Member</h2>
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>

        <form id="memberForm" method="POST">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="member_id" id="memberId">

            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" id="member_name" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                <div class="form-group">
                    <label>Gender *</label>
                    <select name="gender_id" id="member_gender" required>
                        <option value="">Select</option>
                        <?php foreach($genders_array as $id=>$g): ?>
                        <option value="<?= $id ?>"><?= $g ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Relationship *</label>
                    <select name="relationship_id" id="member_relationship" required>
                        <option value="">Select</option>
                        <?php foreach($relationships_array as $id=>$r): ?>
                        <option value="<?= $id ?>"><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group"><label>DOB</label><input type="date" name="dob" id="member_dob"></div>
            <div class="form-group"><label>Phone</label><input type="tel" name="phone" id="member_phone"></div>
            <div class="form-group"><label>Medical Conditions</label><textarea name="medical_conditions" id="member_medical"></textarea></div>
            <div class="form-group"><label>Allergies</label><textarea name="allergies" id="member_allergies"></textarea></div>

            <h4>Emergency Contact</h4>
            <div class="form-group"><label>Name</label><input type="text" name="emergency_contact_name" id="member_emergency_name"></div>
            <div class="form-group"><label>Phone</label><input type="tel" name="emergency_contact_phone" id="member_emergency_phone"></div>
            <div class="form-group"><label>Relationship</label><input type="text" name="emergency_contact_relationship" id="member_emergency_relationship"></div>

            <h4>Doctor Info</h4>
            <div class="form-group"><label>Doctor Name</label><input type="text" name="emergency_doctor_name" id="member_doctor_name"></div>
            <div class="form-group"><label>Doctor Phone</label><input type="tel" name="emergency_doctor_phone" id="member_doctor_phone"></div>
            <div class="form-group"><label>Specialization</label><input type="text" name="doctor_specialization" id="member_doctor_spec"></div>

            <h4>Hospital Info</h4>
            <div class="form-group"><label>Hospital Name</label><input type="text" name="emergency_hospital_name" id="member_hospital_name"></div>
            <div class="form-group"><label>Address</label><input type="text" name="emergency_hospital_address" id="member_hospital_address"></div>
            <div class="form-group"><label>Phone</label><input type="tel" name="emergency_hospital_phone" id="member_hospital_phone"></div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Save Member</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(){
    document.getElementById('memberModal').classList.add('active');
    document.getElementById('modalTitle').textContent='Add New Member';
    document.getElementById('formAction').value='add';
    document.getElementById('memberForm').reset();
    document.getElementById('memberId').value='';
}

function closeModal(){
    document.getElementById('memberModal').classList.remove('active');
}

function editMember(member){
    openModal();
    document.getElementById('modalTitle').textContent='Edit Member';
    document.getElementById('formAction').value='edit';
    document.getElementById('memberId').value = member.id;

    document.getElementById('member_name').value = member.name;
    document.getElementById('member_gender').value = member.gender_id;
    document.getElementById('member_relationship').value = member.relationship_id;
    document.getElementById('member_dob').value = member.date_of_birth;
    document.getElementById('member_phone').value = member.phone;
    document.getElementById('member_medical').value = member.medical_conditions;
    document.getElementById('member_allergies').value = member.allergies;
    document.getElementById('member_emergency_name').value = member.emergency_contact_name;
    document.getElementById('member_emergency_phone').value = member.emergency_contact_phone;
    document.getElementById('member_emergency_relationship').value = member.emergency_contact_relationship;
    document.getElementById('member_doctor_name').value = member.emergency_doctor_name;
    document.getElementById('member_doctor_phone').value = member.emergency_doctor_phone;
    document.getElementById('member_doctor_spec').value = member.doctor_specialization;
    document.getElementById('member_hospital_name').value = member.emergency_hospital_name;
    document.getElementById('member_hospital_address').value = member.emergency_hospital_address;
    document.getElementById('member_hospital_phone').value = member.emergency_hospital_phone;
}

// Close modal on outside click
window.onclick = function(e){
    if(e.target==document.getElementById('memberModal')) closeModal();
}
</script>

</body>
</html>
