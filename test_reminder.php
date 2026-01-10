<?php
$conn = mysqli_connect("localhost","root","","medibuddy");

$sql = "INSERT INTO reminders 
(prescription_medicine_id, family_member_id, frequency_id, reminder_time, is_active)
VALUES (19, 4, 1, '09:00', 1)";

if(mysqli_query($conn,$sql)){
    echo "PHP INSERT WORKED";
} else {
    echo mysqli_error($conn);
}
