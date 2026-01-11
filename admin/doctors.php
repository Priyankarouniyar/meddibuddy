<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
requireAdmin();

$message = '';
$error = '';

// Handle add/edit doctor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
        $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
        $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $specialization = mysqli_real_escape_string($conn, $_POST['specialization']);
        $hospital_clinic = mysqli_real_escape_string($conn, $_POST['hospital_clinic']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $license_number = mysqli_real_escape_string($conn, $_POST['license_number']);

        if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
            $error = 'Please fill in all required fields';
        } else {
            if ($_POST['action'] === 'add') {
                $query = "INSERT INTO doctors (first_name, last_name, email, phone, specialization, hospital_clinic, address, license_number) 
                         VALUES ('$first_name', '$last_name', '$email', '$phone', '$specialization', '$hospital_clinic', '$address', '$license_number')";
                if (mysqli_query($conn, $query)) {
                    $message = 'Doctor added successfully!';
                } else {
                    if (strpos(mysqli_error($conn), 'Duplicate entry') !== false) {
                        $error = 'Email already exists!';
                    } else {
                        $error = 'Error adding doctor: ' . mysqli_error($conn);
                    }
                }
            } else {
                $doctor_id = (int)$_POST['doctor_id'];
                $query = "UPDATE doctors SET first_name='$first_name', last_name='$last_name', email='$email', phone='$phone', 
                         specialization='$specialization', hospital_clinic='$hospital_clinic', address='$address', license_number='$license_number' 
                         WHERE id=$doctor_id";
                if (mysqli_query($conn, $query)) {
                    $message = 'Doctor updated successfully!';
                } else {
                    $error = 'Error updating doctor: ' . mysqli_error($conn);
                }
            }
        }
    } elseif ($_POST['action'] === 'delete') {
        $doctor_id = (int)$_POST['doctor_id'];
        $query = "DELETE FROM doctors WHERE id=$doctor_id";
        if (mysqli_query($conn, $query)) {
            $message = 'Doctor deleted successfully!';
        } else {
            $error = 'Error deleting doctor: ' . mysqli_error($conn);
        }
    }
}

// Get all doctors
$doctors_query = "SELECT * FROM doctors ORDER BY first_name ASC";
$doctors_result = mysqli_query($conn, $doctors_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Doctors - MediBuddy Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .doctors-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .add-doctor-btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: transform 0.3s;
        }

        .add-doctor-btn:hover {
            transform: translateY(-2px);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 60vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            color: #333;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #666;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-submit {
            flex: 1;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-cancel {
            flex: 1;
            padding: 12px;
            background: #ddd;
            color: #333;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .doctors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .doctor-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .doctor-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }

        .doctor-specialization {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .doctor-info {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .doctor-info strong {
            color: #333;
        }

        .card-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .btn-edit,
        .btn-delete {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: opacity 0.3s;
        }

        .btn-edit {
            background: #667eea;
            color: white;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .btn-edit:hover,
        .btn-delete:hover {
            opacity: 0.8;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="doctors-container">
        <div class="header-section">
            <h1>Manage Doctors</h1>
            <button class="add-doctor-btn" onclick="openModal()">+ Add New Doctor</button>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="doctors-grid">
            <?php while ($doctor = mysqli_fetch_assoc($doctors_result)): ?>
                <div class="doctor-card">
                    <div class="doctor-name">Dr. <?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?></div>
                    <div class="doctor-specialization"><?php echo htmlspecialchars($doctor['specialization'] ?? 'General Physician'); ?></div>
                    <div class="doctor-info">
                        <strong>Email:</strong> <?php echo htmlspecialchars($doctor['email']); ?>
                    </div>
                    <div class="doctor-info">
                        <strong>Phone:</strong> <?php echo htmlspecialchars($doctor['phone']); ?>
                    </div>
                    <div class="doctor-info">
                        <strong>Clinic:</strong> <?php echo htmlspecialchars($doctor['hospital_clinic'] ?? 'N/A'); ?>
                    </div>
                    <div class="doctor-info">
                        <strong>License:</strong> <?php echo htmlspecialchars($doctor['license_number'] ?? 'N/A'); ?>
                    </div>
                    <div class="card-actions">
                        <button class="btn-edit" onclick="editDoctor(<?php echo htmlspecialchars(json_encode($doctor)); ?>)">Edit</button>
                        <button class="btn-delete" onclick="deleteDoctor(<?php echo $doctor['id']; ?>)">Delete</button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="doctorModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Doctor</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>

            <form id="doctorForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="doctor_id" id="doctorId">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone *</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>

                <div class="form-group">
                    <label for="specialization">Specialization</label>
                    <input type="text" id="specialization" name="specialization" placeholder="e.g., Cardiologist, Pediatrician">
                </div>

                <div class="form-group">
                    <label for="hospital_clinic">Hospital/Clinic Name</label>
                    <input type="text" id="hospital_clinic" name="hospital_clinic">
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" placeholder="Enter complete address..."></textarea>
                </div>

                <div class="form-group">
                    <label for="license_number">License Number</label>
                    <input type="text" id="license_number" name="license_number" placeholder="Medical license number">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Save Doctor</button>
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('doctorModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Add New Doctor';
            document.getElementById('formAction').value = 'add';
            document.getElementById('doctorForm').reset();
            document.getElementById('doctorId').value = '';
        }

        function closeModal() {
            document.getElementById('doctorModal').classList.remove('active');
        }

        function editDoctor(doctor) {
            document.getElementById('doctorModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Edit Doctor';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('doctorId').value = doctor.id;
            document.getElementById('first_name').value = doctor.first_name;
            document.getElementById('last_name').value = doctor.last_name;
            document.getElementById('email').value = doctor.email;
            document.getElementById('phone').value = doctor.phone;
            document.getElementById('specialization').value = doctor.specialization || '';
            document.getElementById('hospital_clinic').value = doctor.hospital_clinic || '';
            document.getElementById('address').value = doctor.address || '';
            document.getElementById('license_number').value = doctor.license_number || '';
        }

        function deleteDoctor(doctorId) {
            if (confirm('Are you sure you want to delete this doctor?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="doctor_id" value="${doctorId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        document.getElementById('doctorModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
