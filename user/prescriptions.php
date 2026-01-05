<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$userId = $_SESSION['user_id'];

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // ADD PRESCRIPTION
        if ($_POST['action'] === 'add') {
            $name = sanitize($_POST['prescription_name']);
            $description = sanitize($_POST['description']);
            $imagePath = null;

            // Handle file upload
            if (isset($_FILES['prescription_image']) && $_FILES['prescription_image']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['prescription_image']['tmp_name'];
                $fileName = $_FILES['prescription_image']['name'];
                $fileSize = $_FILES['prescription_image']['size'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowedExts = ['jpg','jpeg','png','gif','pdf'];

                if (in_array($fileExt, $allowedExts)) {
                    if($fileSize <= 2*1024*1024){ // 2MB limit
                        $newFileName = uniqid('presc_', true) . '.' . $fileExt;
                        $destPath = '../uploads/prescriptions/' . $newFileName;

                        if (move_uploaded_file($fileTmpPath, $destPath)) {
                            $imagePath = 'uploads/prescriptions/' . $newFileName;
                        } else {
                            setAlert("Failed to upload image.", "danger");
                        }
                    } else {
                        setAlert("File too large. Max 2MB.", "danger");
                    }
                } else {
                    setAlert("Invalid file type. Allowed: jpg, jpeg, png, gif, pdf", "danger");
                }
            }

            // Insert into database
            $insertQuery = "INSERT INTO prescriptions (user_id, name, description, image_path)
                            VALUES ('$userId', '$name', '$description', '$imagePath')";
            if (mysqli_query($conn, $insertQuery)) {
                setAlert("Prescription added successfully!", "success");
            } else {
                setAlert("Failed to add prescription: " . mysqli_error($conn), "danger");
            }
            redirect('prescriptions.php');
        }

        // DELETE PRESCRIPTION
        if ($_POST['action'] === 'delete') {
            $prescId = sanitize($_POST['presc_id']);

            // Get image path
            $res = mysqli_query($conn, "SELECT image_path FROM prescriptions WHERE id='$prescId' AND user_id='$userId'");
            if ($res && mysqli_num_rows($res) > 0) {
                $row = mysqli_fetch_assoc($res);
                if ($row['image_path'] && file_exists('../'.$row['image_path'])) {
                    unlink('../'.$row['image_path']); // Delete image file
                }
            }

            // Delete prescription
            mysqli_query($conn, "DELETE FROM prescriptions WHERE id='$prescId' AND user_id='$userId'");
            setAlert("Prescription deleted successfully!", "success");
            redirect('prescriptions.php');
        }
    }
}

// Fetch user prescriptions
$prescriptions = mysqli_query($conn, "SELECT * FROM prescriptions WHERE user_id='$userId' ORDER BY created_at DESC");

?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <div class="card">
        <div class="card-header flex justify-between align-center">
            <h2>Your Prescriptions</h2>
            <button onclick="openModal('addPrescriptionModal')" class="btn btn-primary">Add Prescription</button>
        </div>

        <?php if(mysqli_num_rows($prescriptions) > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($presc = mysqli_fetch_assoc($prescriptions)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($presc['name']); ?></td>
                        <td><?php echo htmlspecialchars($presc['description']); ?></td>
                        <td>
                            <?php if($presc['image_path']): ?>
                                <img src="../<?php echo $presc['image_path']; ?>" style="max-width:100px;" alt="Prescription">
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this prescription?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="presc_id" value="<?php echo $presc['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p style="padding: 20px; text-align:center;">No prescriptions added yet.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Add Prescription Modal -->
<div id="addPrescriptionModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addPrescriptionModal')">&times;</span>
        <h2>Add Prescription</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label>Prescription Name *</label>
                <input type="text" name="prescription_name" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control"></textarea>
            </div>

            <div class="form-group">
                <label>Upload Prescription Image</label>
                <input type="file" name="prescription_image" accept="image/*,application/pdf">
            </div>

            <button type="submit" class="btn btn-primary">Add Prescription</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
