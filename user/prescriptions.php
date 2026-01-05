<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

// Create uploads directory if it doesn't exist
$uploads_dir = '../uploads/prescriptions';
if(!is_dir($uploads_dir)) {
    if(!mkdir($uploads_dir, 0755, true)) {
        $error = "Warning: Could not create uploads directory. File upload may fail.";
    }
}

// Handle Add Prescription
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_prescription') {
    $family_member_id = intval($_POST['family_member_id']);
    $doctor_name = sanitize($_POST['doctor_name']);
    $prescription_date = $_POST['prescription_date'];
    $description = sanitize($_POST['description']);
    $prescription_file = '';

    if(isset($_FILES['prescription_file']) && $_FILES['prescription_file']['size'] > 0) {
        $file = $_FILES['prescription_file'];
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'bmp', 'gif'];
        $filename = basename($file['name']);
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if(!in_array($file_ext, $allowed)) {
            $error = "Invalid file type. Only PDF, JPG, PNG, DOC, DOCX are allowed.";
        } elseif($file['size'] > 5242880) { // 5MB
            $error = "File size exceeds 5MB limit.";
        } elseif($file['error'] !== UPLOAD_ERR_OK) {
            $error = "File upload error: " . $file['error'];
        } else {
            $new_filename = uniqid('prescription_') . '.' . $file_ext;
            $upload_path = $uploads_dir . '/' . $new_filename;

            if(move_uploaded_file($file['tmp_name'], $upload_path)) {
                chmod($upload_path, 0644);
                $prescription_file = $new_filename;
            } else {
                $error = "Error uploading file. Check folder permissions and disk space.";
            }
        }
    }

    if(empty($family_member_id) || empty($doctor_name)) {
        $error = "Please fill in all required fields.";
    } elseif(!$error) {
        $file_upload_at = $prescription_file ? "NOW()" : "NULL";
        $sql = "INSERT INTO prescriptions (user_id, family_member_id, created_by_user_id, doctor_name, prescription_date, description, prescription_file, file_uploaded_at) 
                VALUES ('$userId', '$family_member_id', '$userId', '$doctor_name', '$prescription_date', '$description', '$prescription_file', $file_upload_at)";
        if(mysqli_query($conn, $sql)) {
            $success = "Prescription created successfully!";
        } else {
            $error = "Error creating prescription.";
        }
    }
}

// Handle Add Medicine to Prescription
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_medicine') {
    $prescription_id = intval($_POST['prescription_id']);
    $medicine_id = intval($_POST['medicine_id']);
    $dosage = sanitize($_POST['dosage']);
    $unit = sanitize($_POST['unit']);
    $quantity = intval($_POST['quantity']);

    if(!$medicine_id || empty($dosage)) {
        $error = "Please select a medicine and dosage.";
    } else {
        $sql = "INSERT INTO prescription_medicine (prescription_id, medicine_id, dosage, unit, quantity) 
                VALUES ('$prescription_id', '$medicine_id', '$dosage', '$unit', '$quantity')";
        if(mysqli_query($conn, $sql)) {
            $success = "Medicine added to prescription!";
        } else {
            $error = "Error adding medicine.";
        }
    }
}

// Handle Delete Prescription
if(isset($_GET['delete']) && isset($_GET['id'])) {
    $prescription_id = intval($_GET['id']);
    // Fetch prescription to delete file
    $result = mysqli_query($conn, "SELECT prescription_file FROM prescriptions WHERE id='$prescription_id' AND user_id='$userId'");
    if($row = mysqli_fetch_assoc($result)) {
        if($row['prescription_file'] && file_exists($uploads_dir . '/' . $row['prescription_file'])) {
            unlink($uploads_dir . '/' . $row['prescription_file']);
        }
    }
    // Delete related medicines first
    mysqli_query($conn, "DELETE FROM prescription_medicine WHERE prescription_id='$prescription_id'");
    // Delete prescription
    $sql = "DELETE FROM prescriptions WHERE id='$prescription_id' AND user_id='$userId'";
    if(mysqli_query($conn, $sql)) {
        $success = "Prescription deleted successfully!";
    } else {
        $error = "Error deleting prescription.";
    }
}

// Handle Delete Medicine from Prescription
if(isset($_GET['delete_medicine']) && isset($_GET['pm_id'])) {
    $pm_id = intval($_GET['pm_id']);
    $sql = "DELETE FROM prescription_medicine WHERE id='$pm_id'";
    if(mysqli_query($conn, $sql)) {
        $success = "Medicine removed from prescription!";
    } else {
        $error = "Error removing medicine.";
    }
}

// Fetch family members
$members = mysqli_query($conn, "SELECT * FROM family_members WHERE user_id='$userId' AND is_active=1 ORDER BY name");
$members_array = [];
while($m = mysqli_fetch_assoc($members)) {
    $members_array[$m['id']] = $m['name'];
}

// Fetch medicines
$medicines = mysqli_query($conn, "SELECT * FROM medicines WHERE is_active=1 ORDER BY name");
$medicines_array = [];
while($med = mysqli_fetch_assoc($medicines)) {
    $medicines_array[$med['id']] = $med['name'];
}

// Fetch prescriptions
$prescriptions = mysqli_query($conn, "SELECT * FROM prescriptions WHERE user_id='$userId' ORDER BY prescription_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescriptions - MediBuddy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .medicine-form { display: none; margin-top: 1rem; padding: 1rem; background: #f5f5f5; border-radius: 8px; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .prescription-card { background: white; padding: 1.5rem; margin: 1rem 0; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .prescription-card h4 { margin: 0 0 0.5rem 0; color: #667eea; }
        .prescription-info { color: #666; font-size: 0.9rem; margin: 0.3rem 0; }
        .medicine-item { background: #f9f9f9; padding: 0.8rem; margin: 0.5rem 0; border-left: 4px solid #667eea; }
        .btn-sm { padding: 0.5rem 1rem; margin-right: 0.5rem; font-size: 0.9rem; }
        .medicine-table { width: 100%; border-collapse: collapse; margin-top: 0.5rem; }
        .medicine-table td { padding: 0.8rem; border-bottom: 1px solid #eee; }
        .medicine-table th { padding: 0.8rem; background: #f5f5f5; text-align: left; font-weight: bold; }
        .file-upload-area { border: 2px dashed #667eea; padding: 1.5rem; border-radius: 8px; text-align: center; background: #f9f9ff; cursor: pointer; transition: all 0.3s ease; }
        .file-upload-area:hover { background: #f0f2ff; border-color: #4c51bf; }
        .file-upload-area.dragover { background: #e0e7ff; border-color: #4c51bf; }
        .file-info { color: #666; font-size: 0.85rem; margin-top: 0.5rem; }
        .uploaded-file { background: #e8f5e9; padding: 0.8rem; border-radius: 4px; margin-top: 0.5rem; display: flex; justify-content: space-between; align-items: center; }
        .uploaded-file a { color: #667eea; text-decoration: none; }
        .uploaded-file a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<?php require_once '../includes/header.php'; ?>

<main class="main-content">
    <h2>Manage Prescriptions</h2>
    
    <?php 
    if($success) echo "<div class='alert alert-success'>$success</div>";
    if($error) echo "<div class='alert alert-danger'>$error</div>";
    ?>

    <!-- Add Prescription Form -->
    <div class="card" style="margin-bottom: 2rem;">
        <h3>Create New Prescription</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_prescription">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Family Member *</label>
                    <select name="family_member_id" required>
                        <option value="">Select Family Member</option>
                        <?php foreach($members_array as $id => $name): ?>
                            <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Doctor Name *</label>
                    <input type="text" name="doctor_name" placeholder="Enter doctor name" required>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Prescription Date *</label>
                    <input type="date" name="prescription_date" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Description/Diagnosis</label>
                <textarea name="description" placeholder="e.g., Patient has fever and cough. Treat with caution." rows="3"></textarea>
            </div>

            <!-- Added prescription file upload section -->
            <div class="form-group">
                <label>Upload Prescription File (Optional)</label>
                <div class="file-upload-area" id="upload-area">
                    <input type="file" name="prescription_file" id="prescription_file" style="display: none;" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.bmp,.gif">
                    <div>
                        <p><strong>📄 Drag and drop your prescription</strong></p>
                        <p>or click to browse</p>
                    </div>
                    <p class="file-info">Supported: PDF, JPG, PNG, DOC, DOCX, BMP, GIF (Max 5MB)</p>
                </div>
                <div id="file-preview" style="margin-top: 0.5rem;"></div>
            </div>

            <button type="submit" class="btn btn-primary">Create Prescription</button>
        </form>
    </div>

    <!-- Prescriptions List -->
    <h3>Your Prescriptions</h3>
    <?php 
    if(mysqli_num_rows($prescriptions) === 0):
    ?>
        <p style="color: #999;">No prescriptions yet. Create one above to get started!</p>
    <?php 
    else:
        while($prescription = mysqli_fetch_assoc($prescriptions)):
            $pm_result = mysqli_query($conn, "SELECT pm.*, m.name as medicine_name FROM prescription_medicine pm JOIN medicines m ON pm.medicine_id=m.id WHERE pm.prescription_id='{$prescription['id']}'");
    ?>
        <div class="prescription-card">
            <h4>Prescription for <?= htmlspecialchars($members_array[$prescription['family_member_id']] ?? 'N/A') ?></h4>
            <div class="prescription-info">
                <strong>Doctor:</strong> <?= htmlspecialchars($prescription['doctor_name']) ?>
            </div>
            <div class="prescription-info">
                <strong>Date:</strong> <?= date('d-m-Y', strtotime($prescription['prescription_date'])) ?>
            </div>
            <?php if($prescription['description']): ?>
                <div class="prescription-info">
                    <strong>Diagnosis:</strong> <?= htmlspecialchars($prescription['description']) ?>
                </div>
            <?php endif; ?>

            <!-- Display uploaded prescription file with download link -->
            <?php if($prescription['prescription_file'] && file_exists($uploads_dir . '/' . $prescription['prescription_file'])): ?>
                <div class="uploaded-file">
                    <span><strong>📎 Prescription Document</strong></span>
                    <a href="../uploads/prescriptions/<?= urlencode($prescription['prescription_file']) ?>" target="_blank" download>Download</a>
                </div>
            <?php endif; ?>

            <!-- Medicines List -->
            <h5 style="margin-top: 1rem; margin-bottom: 0.5rem;">Medicines:</h5>
            <?php 
            if(mysqli_num_rows($pm_result) === 0):
                echo "<p style='color: #999; font-size: 0.9rem;'>No medicines added yet.</p>";
            else:
            ?>
                <table class="medicine-table">
                    <thead>
                        <tr>
                            <th>Medicine Name</th>
                            <th>Dosage</th>
                            <th>Unit</th>
                            <th>Quantity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($pm = mysqli_fetch_assoc($pm_result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($pm['medicine_name']) ?></td>
                                <td><?= htmlspecialchars($pm['dosage']) ?></td>
                                <td><?= htmlspecialchars($pm['unit']) ?></td>
                                <td><?= $pm['quantity'] ?></td>
                                <td>
                                    <a href="?delete_medicine=1&pm_id=<?= $pm['id'] ?>" class="btn btn-sm" style="background: #dc3545; color: white; padding: 0.3rem 0.6rem; font-size: 0.8rem;" onclick="return confirm('Remove this medicine?')">Remove</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <!-- Add Medicine Form -->
            <button class="btn btn-secondary btn-sm" onclick="toggleMedicineForm(<?= $prescription['id'] ?>)" style="margin-top: 0.5rem;">Add Medicine</button>
            
            <div class="medicine-form" id="medicine-form-<?= $prescription['id'] ?>">
                <h5>Add Medicine to Prescription</h5>
                <form method="POST">
                    <input type="hidden" name="action" value="add_medicine">
                    <input type="hidden" name="prescription_id" value="<?= $prescription['id'] ?>">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Medicine *</label>
                            <select name="medicine_id" required>
                                <option value="">Select Medicine</option>
                                <?php foreach($medicines_array as $id => $name): ?>
                                    <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Dosage *</label>
                            <input type="text" name="dosage" placeholder="e.g., 500mg" required>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Unit</label>
                            <input type="text" name="unit" placeholder="e.g., tablet, ml, injection">
                        </div>
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" name="quantity" placeholder="e.g., 1" min="1" value="1">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Add Medicine</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleMedicineForm(<?= $prescription['id'] ?>)">Cancel</button>
                </form>
            </div>

            <!-- Delete Prescription -->
            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee;">
                <a href="?delete=1&id=<?= $prescription['id'] ?>" class="btn btn-sm" style="background: #dc3545; color: white;" onclick="return confirm('Delete this prescription? All medicines will be removed.')">Delete Prescription</a>
            </div>
        </div>
    <?php 
        endwhile;
    endif;
    ?>

</main>

<?php require_once '../includes/footer.php'; ?>

<script>
function toggleMedicineForm(prescriptionId) {
    const form = document.getElementById('medicine-form-' + prescriptionId);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

const uploadArea = document.getElementById('upload-area');
const fileInput = document.getElementById('prescription_file');
const filePreview = document.getElementById('file-preview');

uploadArea.addEventListener('click', () => fileInput.click());

uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    fileInput.files = e.dataTransfer.files;
    updateFilePreview();
});

fileInput.addEventListener('change', updateFilePreview);

function updateFilePreview() {
    filePreview.innerHTML = '';
    if(fileInput.files.length > 0) {
        const file = fileInput.files[0];
        const fileSize = (file.size / 1024).toFixed(2) + ' KB';
        filePreview.innerHTML = `
            <div class="uploaded-file">
                <span>✓ ${file.name} (${fileSize})</span>
                <button type="button" onclick="document.getElementById('prescription_file').value=''; document.getElementById('file-preview').innerHTML='';">Remove</button>
            </div>
        `;
    }
}
</script>
</body>
</html>
