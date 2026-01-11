<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
requireAdmin();

$message = '';
$error = '';

// Handle add/edit drug
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $medicine_type_id = (int)$_POST['medicine_type_id'];
        $manufacturer_id = (int)$_POST['manufacturer_id'];
        $generic_name = mysqli_real_escape_string($conn, $_POST['generic_name']);
        $dosage = mysqli_real_escape_string($conn, $_POST['dosage']);
        $unit = mysqli_real_escape_string($conn, $_POST['unit']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $side_effects = mysqli_real_escape_string($conn, $_POST['side_effects']);
        $warnings = mysqli_real_escape_string($conn, $_POST['warnings']);
        $price = (float)$_POST['price'];

        if (empty($name) || empty($dosage) || empty($unit)) {
            $error = 'Please fill in all required fields';
        } else {
            if ($_POST['action'] === 'add') {
                $query = "INSERT INTO drugs (name, medicine_type_id, manufacturer_id, generic_name, dosage, unit, description, side_effects, warnings, price) 
                         VALUES ('$name', $medicine_type_id, $manufacturer_id, '$generic_name', '$dosage', '$unit', '$description', '$side_effects', '$warnings', $price)";
                if (mysqli_query($conn, $query)) {
                    $message = 'Drug added successfully!';
                } else {
                    $error = 'Error adding drug: ' . mysqli_error($conn);
                }
            } else {
                $drug_id = (int)$_POST['drug_id'];
                $query = "UPDATE drugs SET name='$name', medicine_type_id=$medicine_type_id, manufacturer_id=$manufacturer_id, 
                         generic_name='$generic_name', dosage='$dosage', unit='$unit', description='$description', 
                         side_effects='$side_effects', warnings='$warnings', price=$price WHERE id=$drug_id";
                if (mysqli_query($conn, $query)) {
                    $message = 'Drug updated successfully!';
                } else {
                    $error = 'Error updating drug: ' . mysqli_error($conn);
                }
            }
        }
    } elseif ($_POST['action'] === 'delete') {
        $drug_id = (int)$_POST['drug_id'];
        $query = "DELETE FROM drugs WHERE id=$drug_id";
        if (mysqli_query($conn, $query)) {
            $message = 'Drug deleted successfully!';
        } else {
            $error = 'Error deleting drug: ' . mysqli_error($conn);
        }
    }
}

// Get all drugs
$drugs_query = "SELECT d.*, mt.name as type_name, m.name as manufacturer_name 
                FROM drugs d 
                LEFT JOIN medicine_types mt ON d.medicine_type_id = mt.id 
                LEFT JOIN manufacturers m ON d.manufacturer_id = m.id 
                ORDER BY d.created_at DESC";
$drugs_result = mysqli_query($conn, $drugs_query);

// Get medicine types and manufacturers for dropdown
$types_query = "SELECT * FROM medicine_types ORDER BY name";
$types_result = mysqli_query($conn, $types_query);

$manufacturers_query = "SELECT * FROM manufacturers ORDER BY name";
$manufacturers_result = mysqli_query($conn, $manufacturers_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Drugs - MediBuddy Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .drugs-container {
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

        .add-drug-btn {
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

        .add-drug-btn:hover {
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
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
            transition: background 0.3s;
        }

        .btn-submit:hover {
            background: #5568d3;
        }

        .btn-cancel {
            flex: 1;
            padding: 12px;
            background: #ddd;
            color: #333;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .drugs-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .drugs-table thead {
            background: #f8f9fa;
        }

        .drugs-table th,
        .drugs-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .drugs-table th {
            font-weight: 600;
            color: #333;
        }

        .drugs-table tbody tr:hover {
            background: #f5f5f5;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-edit,
        .btn-delete {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
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

        .price-cell {
            font-weight: bold;
            color: #667eea;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="drugs-container">
        <div class="header-section">
            <h1>Manage Drugs</h1>
            <button class="add-drug-btn" onclick="openModal()">+ Add New Drug</button>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <table class="drugs-table">
            <thead>
                <tr>
                    <th>Drug Name</th>
                    <th>Generic Name</th>
                    <th>Dosage</th>
                    <th>Type</th>
                    <th>Manufacturer</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($drug = mysqli_fetch_assoc($drugs_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($drug['name']); ?></td>
                        <td><?php echo htmlspecialchars($drug['generic_name']); ?></td>
                        <td><?php echo htmlspecialchars($drug['dosage'] . ' ' . $drug['unit']); ?></td>
                        <td><?php echo htmlspecialchars($drug['type_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($drug['manufacturer_name'] ?? 'N/A'); ?></td>
                        <td class="price-cell">Rs.<?php echo number_format($drug['price'], 2); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-edit" onclick="editDrug(<?php echo htmlspecialchars(json_encode($drug)); ?>)">Edit</button>
                                <button class="btn-delete" onclick="deleteDrug(<?php echo $drug['id']; ?>)">Delete</button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Add/Edit Modal -->
    <div id="drugModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Drug</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>

            <form id="drugForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="drug_id" id="drugId">

                <div class="form-group">
                    <label for="name">Drug Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="generic_name">Generic Name</label>
                    <input type="text" id="generic_name" name="generic_name">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="dosage">Dosage *</label>
                        <input type="text" id="dosage" name="dosage" placeholder="e.g., 500" required>
                    </div>
                    <div class="form-group">
                        <label for="unit">Unit *</label>
                        <input type="text" id="unit" name="unit" placeholder="e.g., mg, ml" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="medicine_type_id">Medicine Type</label>
                        <select id="medicine_type_id" name="medicine_type_id">
                            <option value="">Select Type</option>
                            <?php mysqli_data_seek($types_result, 0); ?>
                            <?php while ($type = mysqli_fetch_assoc($types_result)): ?>
                                <option value="<?php echo $type['id']; ?>"><?php echo htmlspecialchars($type['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="manufacturer_id">Manufacturer</label>
                        <select id="manufacturer_id" name="manufacturer_id">
                            <option value="">Select Manufacturer</option>
                            <?php mysqli_data_seek($manufacturers_result, 0); ?>
                            <?php while ($mfg = mysqli_fetch_assoc($manufacturers_result)): ?>
                                <option value="<?php echo $mfg['id']; ?>"><?php echo htmlspecialchars($mfg['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="price">Price (Rs.)</label>
                    <input type="number" id="price" name="price" step="0.01" placeholder="0.00">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Enter drug description..."></textarea>
                </div>

                <div class="form-group">
                    <label for="side_effects">Side Effects</label>
                    <textarea id="side_effects" name="side_effects" placeholder="List possible side effects..."></textarea>
                </div>

                <div class="form-group">
                    <label for="warnings">Warnings & Precautions</label>
                    <textarea id="warnings" name="warnings" placeholder="List warnings and precautions..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Save Drug</button>
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('drugModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Add New Drug';
            document.getElementById('formAction').value = 'add';
            document.getElementById('drugForm').reset();
            document.getElementById('drugId').value = '';
        }

        function closeModal() {
            document.getElementById('drugModal').classList.remove('active');
        }

        function editDrug(drug) {
            document.getElementById('drugModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Edit Drug';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('drugId').value = drug.id;
            document.getElementById('name').value = drug.name;
            document.getElementById('generic_name').value = drug.generic_name;
            document.getElementById('dosage').value = drug.dosage;
            document.getElementById('unit').value = drug.unit;
            document.getElementById('medicine_type_id').value = drug.medicine_type_id || '';
            document.getElementById('manufacturer_id').value = drug.manufacturer_id || '';
            document.getElementById('price').value = drug.price;
            document.getElementById('description').value = drug.description;
            document.getElementById('side_effects').value = drug.side_effects;
            document.getElementById('warnings').value = drug.warnings;
        }

        function deleteDrug(drugId) {
            if (confirm('Are you sure you want to delete this drug?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="drug_id" value="${drugId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        document.getElementById('drugModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
