<?php
$pageTitle = "Verify Medicines";
include '../includes/header.php';

if (!isAdmin()) {
    redirect('../user/dashboard.php');
}

// Handle verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify_medicine'])) {
        $medicine_id = sanitize($_POST['medicine_id']);
        $query = "UPDATE medicines SET is_verified = 1 WHERE medicine_id = $medicine_id";
        
        if (mysqli_query($conn, $query)) {
            setAlert('Medicine verified successfully!', 'success');
        }
        redirect('verify-medicines.php');
    }
    
    if (isset($_POST['reject_medicine'])) {
        $medicine_id = sanitize($_POST['medicine_id']);
        $query = "DELETE FROM medicines WHERE medicine_id = $medicine_id";
        
        if (mysqli_query($conn, $query)) {
            setAlert('Medicine rejected and deleted!', 'warning');
        }
        redirect('verify-medicines.php');
    }
}

// Fetch pending medicines
$query = "SELECT m.*, mt.name as type_name, mf.name as manufacturer_name, u.full_name as added_by_name,
          GROUP_CONCAT(CONCAT(mc.ingredient, ' ', mc.percentage, mc.unit) SEPARATOR ', ') as composition
          FROM medicines m
          LEFT JOIN medicine_types mt ON m.medicine_type_id = mt.type_id
          LEFT JOIN manufacturers mf ON m.manufacturer_id = mf.manufacturer_id
          LEFT JOIN users u ON m.added_by = u.user_id
          LEFT JOIN medicine_compositions mc ON m.medicine_id = mc.medicine_id
          WHERE m.is_verified = 0
          GROUP BY m.medicine_id
          ORDER BY m.created_at DESC";
$medicines = mysqli_query($conn, $query);
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>Verify Medicines</h2>
        </div>
        
        <?php if (mysqli_num_rows($medicines) > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Generic Name</th>
                        <th>Type</th>
                        <th>Strength</th>
                        <th>Composition</th>
                        <th>Manufacturer</th>
                        <th>Added By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($medicine = mysqli_fetch_assoc($medicines)): ?>
                        <tr>
                            <td><strong><?php echo $medicine['name']; ?></strong></td>
                            <td><?php echo $medicine['generic_name']; ?></td>
                            <td><?php echo $medicine['type_name']; ?></td>
                            <td><?php echo $medicine['strength']; ?></td>
                            <td><?php echo $medicine['composition'] ?: 'N/A'; ?></td>
                            <td><?php echo $medicine['manufacturer_name'] ?: 'N/A'; ?></td>
                            <td><?php echo $medicine['added_by_name']; ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="medicine_id" value="<?php echo $medicine['medicine_id']; ?>">
                                    <button type="submit" name="verify_medicine" class="btn btn-success btn-sm">Verify</button>
                                </form>
                                <form method="POST" style="display: inline;" onsubmit="return confirmDelete('Reject and delete this medicine?');">
                                    <input type="hidden" name="medicine_id" value="<?php echo $medicine['medicine_id']; ?>">
                                    <button type="submit" name="reject_medicine" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding: 2rem; text-align: center;">
                <p>No medicines pending verification.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
