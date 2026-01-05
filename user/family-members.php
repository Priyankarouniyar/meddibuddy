<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if(isAdmin()) redirect('../auth/login.php');

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Add Member
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
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
        $sql = "INSERT INTO family_members (user_id, name, gender_id, date_of_birth, relationship_id, medical_conditions, allergies, phone, emergency_contact_name, emergency_contact_phone, emergency_contact_relationship, emergency_doctor_name, emergency_doctor_phone, doctor_specialization, emergency_hospital_name, emergency_hospital_address, emergency_hospital_phone) 
                VALUES ('$userId', '$name', '$gender_id', '$dob', '$relationship_id', '$medical_conditions', '$allergies', '$phone', '$emergency_contact_name', '$emergency_contact_phone', '$emergency_contact_relationship', '$emergency_doctor_name', '$emergency_doctor_phone', '$doctor_specialization', '$emergency_hospital_name', '$emergency_hospital_address', '$emergency_hospital_phone')";
        if(mysqli_query($conn, $sql)) {
            $success = "Family member added successfully!";
        } else {
            $error = "Error adding family member. Please try again.";
        }
    }
}

// Handle Edit Member
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $member_id = intval($_POST['member_id']);
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

    $sql = "UPDATE family_members SET name='$name', gender_id='$gender_id', date_of_birth='$dob', 
            relationship_id='$relationship_id', medical_conditions='$medical_conditions', 
            allergies='$allergies', phone='$phone', emergency_contact_name='$emergency_contact_name',
            emergency_contact_phone='$emergency_contact_phone', emergency_contact_relationship='$emergency_contact_relationship',
            emergency_doctor_name='$emergency_doctor_name', emergency_doctor_phone='$emergency_doctor_phone',
            doctor_specialization='$doctor_specialization', emergency_hospital_name='$emergency_hospital_name',
            emergency_hospital_address='$emergency_hospital_address', emergency_hospital_phone='$emergency_hospital_phone'
            WHERE id='$member_id' AND user_id='$userId'";
    if(mysqli_query($conn, $sql)) {
        $success = "Family member updated successfully!";
    } else {
        $error = "Error updating family member.";
    }
}

// Handle Delete Member
if(isset($_GET['delete']) && isset($_GET['id'])) {
    $member_id = intval($_GET['id']);
    $sql = "DELETE FROM family_members WHERE id='$member_id' AND user_id='$userId'";
    if(mysqli_query($conn, $sql)) {
        $success = "Family member deleted successfully!";
    } else {
        $error = "Error deleting family member.";
    }
}

// Fetch all genders
$genders = mysqli_query($conn, "SELECT * FROM genders ORDER BY name");
$genders_array = [];
while($g = mysqli_fetch_assoc($genders)) {
    $genders_array[$g['id']] = $g['name'];
}

// Fetch all relationships
$relationships = mysqli_query($conn, "SELECT * FROM relationships ORDER BY name");
$relationships_array = [];
while($r = mysqli_fetch_assoc($relationships)) {
    $relationships_array[$r['id']] = $r['name'];
}

// Fetch family members
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
        .edit-form { display: none; margin-top: 1rem; padding: 1rem; background: #f5f5f5; border-radius: 8px; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; }
        .member-card { background: white; padding: 1.5rem; margin: 1rem 0; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .member-card h4 { margin: 0 0 0.5rem 0; }
        .member-info { color: #666; font-size: 0.9rem; margin: 0.3rem 0; }
        .action-buttons { margin-top: 1rem; }
        .btn-sm { padding: 0.5rem 1rem; margin-right: 0.5rem; font-size: 0.9rem; }
        /* Added styling for emergency sections */
        .emergency-section { border-top: 1px solid #ddd; padding-top: 0.5rem; margin-top: 0.5rem; }
        .emergency-header { color: #d9534f; font-weight: bold; }
        .section-divider { border: 1px solid #ddd; padding: 1rem; border-radius: 4px; margin: 1rem 0; background: #f9f9f9; }
        .section-divider legend { padding: 0 0.5rem; color: #d9534f; font-weight: bold; }
    </style>
</head>
<body>
<?php require_once '../includes/header.php'; ?>

<main class="main-content">
    <h2>Family Members Management</h2>
    
    <?php 
    if($success) echo "<div class='alert alert-success'>$success</div>";
    if($error) echo "<div class='alert alert-danger'>$error</div>";
    ?>

    <!-- Add Member Form -->
    <div class="card" style="margin-bottom: 2rem;">
        <h3>Add New Family Member</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            
            <!-- Basic Information Section -->
            <fieldset class="section-divider">
                <legend>Basic Information</legend>
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" placeholder="Enter full name" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Gender *</label>
                        <select name="gender_id" required>
                            <option value="">Select Gender</option>
                            <?php foreach($genders_array as $id => $name): ?>
                                <option value="<?= $id ?>"><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Relationship *</label>
                        <select name="relationship_id" required>
                            <option value="">Select Relationship</option>
                            <?php foreach($relationships_array as $id => $name): ?>
                                <option value="<?= $id ?>"><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="dob">
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" name="phone" placeholder="Enter phone number">
                    </div>
                </div>
                <div class="form-group">
                    <label>Medical Conditions</label>
                    <textarea name="medical_conditions" placeholder="e.g., Diabetes, Hypertension" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Allergies</label>
                    <textarea name="allergies" placeholder="e.g., Penicillin, Aspirin" rows="2"></textarea>
                </div>
            </fieldset>

            <!-- Emergency Contact Section -->
            <fieldset class="section-divider">
                <legend>Emergency Contact Person</legend>
                <div class="form-group">
                    <label>Contact Name</label>
                    <input type="text" name="emergency_contact_name" placeholder="e.g., John Doe">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Contact Phone</label>
                        <input type="tel" name="emergency_contact_phone" placeholder="+1-234-567-8900">
                    </div>
                    <div class="form-group">
                        <label>Relationship</label>
                        <input type="text" name="emergency_contact_relationship" placeholder="e.g., Spouse, Parent, Sibling">
                    </div>
                </div>
            </fieldset>

            <!-- Doctor Information Section -->
            <fieldset class="section-divider">
                <legend>Primary Physician Information</legend>
                <div class="form-group">
                    <label>Doctor Name</label>
                    <input type="text" name="emergency_doctor_name" placeholder="e.g., Dr. Sarah Smith">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Doctor Phone</label>
                        <input type="tel" name="emergency_doctor_phone" placeholder="+1-234-567-8900">
                    </div>
                    <div class="form-group">
                        <label>Specialization</label>
                        <input type="text" name="doctor_specialization" placeholder="e.g., Cardiologist, Neurologist">
                    </div>
                </div>
            </fieldset>

            <!-- Hospital Information Section -->
            <fieldset class="section-divider">
                <legend>Hospital/Clinic Information</legend>
                <div class="form-group">
                    <label>Hospital/Clinic Name</label>
                    <input type="text" name="emergency_hospital_name" placeholder="e.g., City General Hospital">
                </div>
                <div class="form-group">
                    <label>Hospital Address</label>
                    <input type="text" name="emergency_hospital_address" placeholder="Full address">
                </div>
                <div class="form-group">
                    <label>Hospital Phone</label>
                    <input type="tel" name="emergency_hospital_phone" placeholder="+1-234-567-8900">
                </div>
            </fieldset>

            <button type="submit" class="btn btn-primary">Add Family Member</button>
        </form>
    </div>

    <!-- Members List -->
    <h3>Your Family Members</h3>
    <?php 
    $count = mysqli_num_rows($members);
    if($count === 0): 
    ?>
        <p style="color: #999;">No family members added yet. Add one above to get started!</p>
    <?php 
    else:
        while($member = mysqli_fetch_assoc($members)): 
    ?>
        <div class="member-card">
            <h4><?= htmlspecialchars($member['name']) ?></h4>
            
            <!-- Basic Information Display -->
            <div class="member-info">
                <strong>Relationship:</strong> <?= htmlspecialchars($relationships_array[$member['relationship_id']] ?? 'N/A') ?>
            </div>
            <?php if($member['date_of_birth']): ?>
                <div class="member-info">
                    <strong>DOB:</strong> <?= date('d-m-Y', strtotime($member['date_of_birth'])) ?>
                </div>
            <?php endif; ?>
            <div class="member-info">
                <strong>Gender:</strong> <?= htmlspecialchars($genders_array[$member['gender_id']] ?? 'N/A') ?>
            </div>
            <?php if($member['phone']): ?>
                <div class="member-info">
                    <strong>Phone:</strong> <?= htmlspecialchars($member['phone']) ?>
                </div>
            <?php endif; ?>
            <?php if($member['medical_conditions']): ?>
                <div class="member-info">
                    <strong>Medical Conditions:</strong> <?= htmlspecialchars($member['medical_conditions']) ?>
                </div>
            <?php endif; ?>
            <?php if($member['allergies']): ?>
                <div class="member-info">
                    <strong>Allergies:</strong> <?= htmlspecialchars($member['allergies']) ?>
                </div>
            <?php endif; ?>

            <!-- Emergency Contact Display -->
            <?php if($member['emergency_contact_name'] || $member['emergency_doctor_name'] || $member['emergency_hospital_name']): ?>
                <div class="emergency-section">
                    <div class="member-info emergency-header">EMERGENCY CONTACT INFORMATION</div>
                    
                    <!-- Contact Person Info -->
                    <?php if($member['emergency_contact_name']): ?>
                        <div class="member-info" style="margin-top: 0.5rem;">
                            <strong>Contact Person:</strong> <?= htmlspecialchars($member['emergency_contact_name']) ?>
                        </div>
                        <div class="member-info">
                            <strong>Relationship:</strong> <?= htmlspecialchars($member['emergency_contact_relationship'] ?? 'N/A') ?>
                        </div>
                        <div class="member-info">
                            <strong>Phone:</strong> <?= htmlspecialchars($member['emergency_contact_phone']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Doctor Info -->
                    <?php if($member['emergency_doctor_name']): ?>
                        <div class="member-info" style="margin-top: 0.5rem; border-top: 1px dashed #ddd; padding-top: 0.5rem;">
                            <strong>Primary Physician:</strong> <?= htmlspecialchars($member['emergency_doctor_name']) ?>
                        </div>
                        <?php if($member['doctor_specialization']): ?>
                            <div class="member-info">
                                <strong>Specialization:</strong> <?= htmlspecialchars($member['doctor_specialization']) ?>
                            </div>
                        <?php endif; ?>
                        <div class="member-info">
                            <strong>Doctor Phone:</strong> <?= htmlspecialchars($member['emergency_doctor_phone']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Hospital Info -->
                    <?php if($member['emergency_hospital_name']): ?>
                        <div class="member-info" style="margin-top: 0.5rem; border-top: 1px dashed #ddd; padding-top: 0.5rem;">
                            <strong>Hospital/Clinic:</strong> <?= htmlspecialchars($member['emergency_hospital_name']) ?>
                        </div>
                        <?php if($member['emergency_hospital_address']): ?>
                            <div class="member-info">
                                <strong>Address:</strong> <?= htmlspecialchars($member['emergency_hospital_address']) ?>
                            </div>
                        <?php endif; ?>
                        <div class="member-info">
                            <strong>Hospital Phone:</strong> <?= htmlspecialchars($member['emergency_hospital_phone']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="action-buttons">
                <button class="btn btn-secondary btn-sm" onclick="toggleEditForm(<?= $member['id'] ?>)">Edit</button>
                <a href="?delete=1&id=<?= $member['id'] ?>" class="btn btn-sm" style="background: #dc3545; color: white;" onclick="return confirm('Are you sure?')">Delete</a>
            </div>

            <!-- Edit Form (Hidden by default) -->
            <div class="edit-form" id="edit-form-<?= $member['id'] ?>">
                <h4>Edit Member</h4>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                    
                    <!-- Basic Information Section in Edit -->
                    <fieldset class="section-divider">
                        <legend>Basic Information</legend>
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($member['name']) ?>" required>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label>Gender</label>
                                <select name="gender_id">
                                    <option value="">Select Gender</option>
                                    <?php foreach($genders_array as $id => $name): ?>
                                        <option value="<?= $id ?>" <?= $member['gender_id'] == $id ? 'selected' : '' ?>><?= $name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Relationship</label>
                                <select name="relationship_id">
                                    <option value="">Select Relationship</option>
                                    <?php foreach($relationships_array as $id => $name): ?>
                                        <option value="<?= $id ?>" <?= $member['relationship_id'] == $id ? 'selected' : '' ?>><?= $name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" name="dob" value="<?= $member['date_of_birth'] ?>">
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="tel" name="phone" value="<?= htmlspecialchars($member['phone'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Medical Conditions</label>
                            <textarea name="medical_conditions" rows="2"><?= htmlspecialchars($member['medical_conditions'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Allergies</label>
                            <textarea name="allergies" rows="2"><?= htmlspecialchars($member['allergies'] ?? '') ?></textarea>
                        </div>
                    </fieldset>

                    <!-- Emergency Contact Section in Edit -->
                    <fieldset class="section-divider">
                        <legend>Emergency Contact Person</legend>
                        <div class="form-group">
                            <label>Contact Name</label>
                            <input type="text" name="emergency_contact_name" value="<?= htmlspecialchars($member['emergency_contact_name'] ?? '') ?>" placeholder="e.g., John Doe">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label>Contact Phone</label>
                                <input type="tel" name="emergency_contact_phone" value="<?= htmlspecialchars($member['emergency_contact_phone'] ?? '') ?>" placeholder="+1-234-567-8900">
                            </div>
                            <div class="form-group">
                                <label>Relationship</label>
                                <input type="text" name="emergency_contact_relationship" value="<?= htmlspecialchars($member['emergency_contact_relationship'] ?? '') ?>" placeholder="e.g., Spouse, Parent, Sibling">
                            </div>
                        </div>
                    </fieldset>

                    <!-- Doctor Information Section in Edit -->
                    <fieldset class="section-divider">
                        <legend>Primary Physician Information</legend>
                        <div class="form-group">
                            <label>Doctor Name</label>
                            <input type="text" name="emergency_doctor_name" value="<?= htmlspecialchars($member['emergency_doctor_name'] ?? '') ?>" placeholder="e.g., Dr. Sarah Smith">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label>Doctor Phone</label>
                                <input type="tel" name="emergency_doctor_phone" value="<?= htmlspecialchars($member['emergency_doctor_phone'] ?? '') ?>" placeholder="+1-234-567-8900">
                            </div>
                            <div class="form-group">
                                <label>Specialization</label>
                                <input type="text" name="doctor_specialization" value="<?= htmlspecialchars($member['doctor_specialization'] ?? '') ?>" placeholder="e.g., Cardiologist, Neurologist">
                            </div>
                        </div>
                    </fieldset>

                    <!-- Hospital Information Section in Edit -->
                    <fieldset class="section-divider">
                        <legend>Hospital/Clinic Information</legend>
                        <div class="form-group">
                            <label>Hospital/Clinic Name</label>
                            <input type="text" name="emergency_hospital_name" value="<?= htmlspecialchars($member['emergency_hospital_name'] ?? '') ?>" placeholder="e.g., City General Hospital">
                        </div>
                        <div class="form-group">
                            <label>Hospital Address</label>
                            <input type="text" name="emergency_hospital_address" value="<?= htmlspecialchars($member['emergency_hospital_address'] ?? '') ?>" placeholder="Full address">
                        </div>
                        <div class="form-group">
                            <label>Hospital Phone</label>
                            <input type="tel" name="emergency_hospital_phone" value="<?= htmlspecialchars($member['emergency_hospital_phone'] ?? '') ?>" placeholder="+1-234-567-8900">
                        </div>
                    </fieldset>

                    <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEditForm(<?= $member['id'] ?>)">Cancel</button>
                </form>
            </div>
        </div>
    <?php 
        endwhile;
    endif;
    ?>

</main>

<?php require_once '../includes/footer.php'; ?>

<script>
function toggleEditForm(memberId) {
    const form = document.getElementById('edit-form-' + memberId);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>
</body>
</html>
