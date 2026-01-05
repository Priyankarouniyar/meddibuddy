<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || isAdmin()) redirect('../auth/login.php');

$userId = $_SESSION['user_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'add') {
        $firstName = sanitize($_POST['first_name']);
        $lastName = sanitize($_POST['last_name']);
        $relationship = sanitize($_POST['relationship']);
        $dob = sanitize($_POST['date_of_birth']);
        $gender = sanitize($_POST['gender']);
        $blood = sanitize($_POST['blood_group']);
        $allergies = sanitize($_POST['allergies']);

        $query = "INSERT INTO family_members 
                  (user_id, first_name, last_name, relationship, date_of_birth, gender, blood_group, allergies)
                  VALUES ('$userId','$firstName','$lastName','$relationship','$dob','$gender','$blood','$allergies')";

        if (mysqli_query($conn, $query)) setAlert("Family member added!", "success");
        else setAlert("Error adding member: ".mysqli_error($conn), "danger");

        redirect('family-members.php');
    }

    if ($_POST['action'] === 'delete') {
        $memberId = sanitize($_POST['member_id']);
        mysqli_query($conn, "DELETE FROM family_members WHERE member_id='$memberId' AND user_id='$userId'");
        setAlert("Family member deleted.", "success");
        redirect('family-members.php');
    }
}

// Fetch family members
$result = mysqli_query($conn, "SELECT * FROM family_members WHERE user_id='$userId'");
?>
<?php include '../includes/header.php'; ?>
<h2>Family Members</h2>
<button class="btn btn-primary" onclick="openModal('addMemberModal')">Add Member</button>

<?php if(mysqli_num_rows($result) > 0): ?>
<table class="table">
    <thead>
        <tr>
            <th>Name</th><th>Relationship</th><th>DOB</th><th>Gender</th><th>Blood</th><th>Allergies</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while($m = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $m['first_name'].' '.$m['last_name'] ?></td>
            <td><?= $m['relationship'] ?: '-' ?></td>
            <td><?= $m['date_of_birth'] ?: '-' ?></td>
            <td><?= $m['gender'] ?: '-' ?></td>
            <td><?= $m['blood_group'] ?: '-' ?></td>
            <td><?= $m['allergies'] ?: 'None' ?></td>
            <td>
                <form method="POST" onsubmit="return confirm('Delete?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="member_id" value="<?= $m['member_id'] ?>">
                    <button class="btn btn-danger btn-sm">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
<p>No family members added yet.</p>
<?php endif; ?>

<!-- Add Member Modal -->
<div id="addMemberModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addMemberModal')">&times;</span>
        <h3>Add Family Member</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
            <input type="text" name="relationship" placeholder="Relationship">
            <input type="date" name="date_of_birth" placeholder="DOB">
            <select name="gender">
                <option value="">Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
            <input type="text" name="blood_group" placeholder="Blood Group">
            <textarea name="allergies" placeholder="Allergies"></textarea>
            <button class="btn btn-primary" type="submit">Add Member</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
