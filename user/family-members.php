<?php 
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if(isAdmin()) redirect('../auth/login.php');

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

// =========================
// Handle Add Member
// =========================
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $name = sanitize($_POST['name']);
    $gender_id = intval($_POST['gender_id']);
    $dob = $_POST['dob'];
    $relationship_id = intval($_POST['relationship_id']);
    $medical_conditions = sanitize($_POST['medical_conditions']);
    $allergies = sanitize($_POST['allergies']);
    $phone = sanitize($_POST['phone']);
    
    $emergency_contact_name = sanitize($_POST['emergency_contact_name']);
    $emergency_contact_phone = sanitize($_POST['emergency_contact_phone']);
    $emergency_contact_relationship = sanitize($_POST['emergency_contact_relationship']);
    $emergency_doctor_name = sanitize($_POST['emergency_doctor_name']);
    $emergency_doctor_phone = sanitize($_POST['emergency_doctor_phone']);
    $doctor_specialization = sanitize($_POST['doctor_specialization']);
    $emergency_hospital_name = sanitize($_POST['emergency_hospital_name']);
    $emergency_hospital_address = sanitize($_POST['emergency_hospital_address']);
    $emergency_hospital_phone = sanitize($_POST['emergency_hospital_phone']);

    if(empty($name) || !$gender_id || !$relationship_id) {
        $error = "Please fill all required fields.";
    } else {
        if($action === 'add'){
            $sql = "INSERT INTO family_members (user_id, name, gender_id, date_of_birth, relationship_id, medical_conditions, allergies, phone, emergency_contact_name, emergency_contact_phone, emergency_contact_relationship, emergency_doctor_name, emergency_doctor_phone, doctor_specialization, emergency_hospital_name, emergency_hospital_address, emergency_hospital_phone) 
                    VALUES ('$userId', '$name', '$gender_id', '$dob', '$relationship_id', '$medical_conditions', '$allergies', '$phone', '$emergency_contact_name', '$emergency_contact_phone', '$emergency_contact_relationship', '$emergency_doctor_name', '$emergency_doctor_phone', '$doctor_specialization', '$emergency_hospital_name', '$emergency_hospital_address', '$emergency_hospital_phone')";
            $msg = "added";
        } elseif($action === 'edit') {
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

        if(mysqli_query($conn, $sql)) {
            $success = "Family member $msg successfully!";
        } else {
            $error = "Error! Please try again.";
        }
    }
}

// =========================
// Handle Delete
// =========================
if(isset($_GET['delete']) && isset($_GET['id'])) {
    $member_id = intval($_GET['id']);
    $sql = "DELETE FROM family_members WHERE id='$member_id' AND user_id='$userId'";
    if(mysqli_query($conn, $sql)) {
        $success = "Family member deleted successfully!";
    } else {
        $error = "Error deleting member.";
    }
}

// =========================
// Fetch Genders, Relationships
// =========================
$genders_array = [];
$result = mysqli_query($conn, "SELECT * FROM genders ORDER BY name");
while($r = mysqli_fetch_assoc($result)) $genders_array[$r['id']] = $r['name'];

$relationships_array = [];
$result = mysqli_query($conn, "SELECT * FROM relationships ORDER BY name");
while($r = mysqli_fetch_assoc($result)) $relationships_array[$r['id']] = $r['name'];

// =========================
// Fetch Family Members
// =========================
$members = mysqli_query($conn, "SELECT * FROM family_members WHERE user_id='$userId' AND is_active=1 ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Family Members - MediBuddy</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body { font-family: Arial, sans-serif; }
.main-content { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin: 2rem; }
.card { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
.btn { padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; }
.btn-primary { background: #007bff; color: white; }
.btn-secondary { background: #6c757d; color: white; }
.btn-danger { background: #dc3545; color: white; }
.member-card { background: #f9f9f9; padding: 1rem; margin-bottom: 1rem; border-radius: 8px; }
.member-info { font-size: 0.9rem; margin: 0.3rem 0; }
.emergency-section { margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed #ddd; }
.emergency-header { font-weight: bold; color: #d9534f; margin-bottom: 0.3rem; }
.edit-form { display: none; margin-top: 1rem; padding: 1rem; background: #f5f5f5; border-radius: 8px; }
</style>
</head>
<body>

<?php require_once '../includes/header.php'; ?>

<main class="main-content">

<!-- Left Column: Add Member Form -->
<div class="card">
    <h3>Add New Family Member</h3>
    <?php if($success) echo "<div style='color:green;'>$success</div>"; ?>
    <?php if($error) echo "<div style='color:red;'>$error</div>"; ?>
    <form method="POST">
        <input type="hidden" name="action" value="add">
        <div class="form-group"><label>Full Name *</label><input type="text" name="name" required></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group"><label>Gender *</label>
                <select name="gender_id" required>
                    <option value="">Select</option>
                    <?php foreach($genders_array as $id=>$g): ?>
                        <option value="<?= $id ?>"><?= $g ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Relationship *</label>
                <select name="relationship_id" required>
                    <option value="">Select</option>
                    <?php foreach($relationships_array as $id=>$r): ?>
                        <option value="<?= $id ?>"><?= $r ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group"><label>DOB</label><input type="date" name="dob"></div>
        <div class="form-group"><label>Phone</label><input type="tel" name="phone"></div>
        <div class="form-group"><label>Medical Conditions</label><textarea name="medical_conditions"></textarea></div>
        <div class="form-group"><label>Allergies</label><textarea name="allergies"></textarea></div>

        <h4>Emergency Contact</h4>
        <div class="form-group"><label>Name</label><input type="text" name="emergency_contact_name"></div>
        <div class="form-group"><label>Phone</label><input type="tel" name="emergency_contact_phone"></div>
        <div class="form-group"><label>Relationship</label><input type="text" name="emergency_contact_relationship"></div>

        <h4>Doctor Info</h4>
        <div class="form-group"><label>Doctor Name</label><input type="text" name="emergency_doctor_name"></div>
        <div class="form-group"><label>Doctor Phone</label><input type="tel" name="emergency_doctor_phone"></div>
        <div class="form-group"><label>Specialization</label><input type="text" name="doctor_specialization"></div>

        <h4>Hospital Info</h4>
        <div class="form-group"><label>Hospital Name</label><input type="text" name="emergency_hospital_name"></div>
        <div class="form-group"><label>Address</label><input type="text" name="emergency_hospital_address"></div>
        <div class="form-group"><label>Phone</label><input type="tel" name="emergency_hospital_phone"></div>

        <button type="submit" class="btn btn-primary">Add Member</button>
    </form>
</div>

<!-- Right Column: Family Members List -->
<div>
    <h3>Your Family Members</h3>
    <?php if(mysqli_num_rows($members)===0): ?>
        <p>No family members yet.</p>
    <?php else: while($member=mysqli_fetch_assoc($members)): ?>
        <div class="member-card">
            <h4><?= htmlspecialchars($member['name']) ?></h4>
            <div class="member-info"><strong>Relationship:</strong> <?= $relationships_array[$member['relationship_id']] ?? 'N/A' ?></div>
            <div class="member-info"><strong>Gender:</strong> <?= $genders_array[$member['gender_id']] ?? 'N/A' ?></div>
            <?php if($member['phone']): ?><div class="member-info"><strong>Phone:</strong> <?= $member['phone'] ?></div><?php endif; ?>
            <?php if($member['medical_conditions']): ?><div class="member-info"><strong>Medical:</strong> <?= $member['medical_conditions'] ?></div><?php endif; ?>
            <?php if($member['allergies']): ?><div class="member-info"><strong>Allergies:</strong> <?= $member['allergies'] ?></div><?php endif; ?>

            <!-- Emergency Info -->
            <?php if($member['emergency_contact_name'] || $member['emergency_doctor_name'] || $member['emergency_hospital_name']): ?>
            <div class="emergency-section">
                <div class="emergency-header">EMERGENCY INFO</div>
                <?php if($member['emergency_contact_name']): ?>
                    <div class="member-info"><strong>Contact:</strong> <?= $member['emergency_contact_name'] ?> (<?= $member['emergency_contact_relationship'] ?>)</div>
                    <div class="member-info"><strong>Phone:</strong> <?= $member['emergency_contact_phone'] ?></div>
                <?php endif; ?>
                <?php if($member['emergency_doctor_name']): ?>
                    <div class="member-info"><strong>Doctor:</strong> <?= $member['emergency_doctor_name'] ?> (<?= $member['doctor_specialization'] ?>)</div>
                    <div class="member-info"><strong>Phone:</strong> <?= $member['emergency_doctor_phone'] ?></div>
                <?php endif; ?>
                <?php if($member['emergency_hospital_name']): ?>
                    <div class="member-info"><strong>Hospital:</strong> <?= $member['emergency_hospital_name'] ?></div>
                    <div class="member-info"><strong>Address:</strong> <?= $member['emergency_hospital_address'] ?></div>
                    <div class="member-info"><strong>Phone:</strong> <?= $member['emergency_hospital_phone'] ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div style="margin-top:0.5rem;">
                <button class="btn btn-secondary btn-sm" onclick="toggleEditForm(<?= $member['id'] ?>)">Edit</button>
                <a href="?delete=1&id=<?= $member['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
            </div>

            <!-- Edit Form -->
            <div class="edit-form" id="edit-form-<?= $member['id'] ?>">
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                    <div class="form-group"><label>Name</label><input type="text" name="name" value="<?= htmlspecialchars($member['name']) ?>"></div>
                    <div class="form-group"><label>Gender</label>
                        <select name="gender_id">
                            <?php foreach($genders_array as $id=>$g): ?>
                                <option value="<?= $id ?>" <?= $member['gender_id']==$id?'selected':'' ?>><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Relationship</label>
                        <select name="relationship_id">
                            <?php foreach($relationships_array as $id=>$r): ?>
                                <option value="<?= $id ?>" <?= $member['relationship_id']==$id?'selected':'' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>DOB</label><input type="date" name="dob" value="<?= $member['date_of_birth'] ?>"></div>
                    <div class="form-group"><label>Phone</label><input type="tel" name="phone" value="<?= $member['phone'] ?>"></div>
                    <div class="form-group"><label>Medical Conditions</label><textarea name="medical_conditions"><?= $member['medical_conditions'] ?></textarea></div>
                    <div class="form-group"><label>Allergies</label><textarea name="allergies"><?= $member['allergies'] ?></textarea></div>

                    <h4>Emergency Contact</h4>
                    <div class="form-group"><label>Name</label><input type="text" name="emergency_contact_name" value="<?= $member['emergency_contact_name'] ?>"></div>
                    <div class="form-group"><label>Phone</label><input type="tel" name="emergency_contact_phone" value="<?= $member['emergency_contact_phone'] ?>"></div>
                    <div class="form-group"><label>Relationship</label><input type="text" name="emergency_contact_relationship" value="<?= $member['emergency_contact_relationship'] ?>"></div>

                    <h4>Doctor Info</h4>
                    <div class="form-group"><label>Doctor Name</label><input type="text" name="emergency_doctor_name" value="<?= $member['emergency_doctor_name'] ?>"></div>
                    <div class="form-group"><label>Doctor Phone</label><input type="tel" name="emergency_doctor_phone" value="<?= $member['emergency_doctor_phone'] ?>"></div>
                    <div class="form-group"><label>Specialization</label><input type="text" name="doctor_specialization" value="<?= $member['doctor_specialization'] ?>"></div>

                    <h4>Hospital Info</h4>
                    <div class="form-group"><label>Hospital Name</label><input type="text" name="emergency_hospital_name" value="<?= $member['emergency_hospital_name'] ?>"></div>
                    <div class="form-group"><label>Address</label><input type="text" name="emergency_hospital_address" value="<?= $member['emergency_hospital_address'] ?>"></div>
                    <div class="form-group"><label>Phone</label><input type="tel" name="emergency_hospital_phone" value="<?= $member['emergency_hospital_phone'] ?>"></div>

                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEditForm(<?= $member['id'] ?>)">Cancel</button>
                </form>
            </div>

        </div>
    <?php endwhile; endif; ?>
</div>

</main>

<?php require_once '../includes/footer.php'; ?>

<script>
function toggleEditForm(id){
    const form = document.getElementById('edit-form-'+id);
    form.style.display = form.style.display==='none'?'block':'none';
}
</script>

</body>
</html>
