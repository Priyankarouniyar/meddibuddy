<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if(isAdmin()) redirect('../auth/login.php');

$userId = $_SESSION['user_id'];

// Get filter parameters
$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
$filterType = isset($_GET['type']) ? $_GET['type'] : '';
$filterFrom = isset($_GET['from']) ? $_GET['from'] : '';
$filterTo = isset($_GET['to']) ? $_GET['to'] : '';

// Build where clause
$where = "WHERE nl.user_id='$userId'";

if(!empty($filterStatus)) {
    $filterStatus = mysqli_real_escape_string($conn, $filterStatus);
    $where .= " AND nl.status='$filterStatus'";
}

if(!empty($filterType)) {
    $filterType = mysqli_real_escape_string($conn, $filterType);
    $where .= " AND nl.notification_type='$filterType'";
}

if(!empty($filterFrom)) {
    $filterFrom = mysqli_real_escape_string($conn, $filterFrom);
    $where .= " AND DATE(nl.created_at) >= '$filterFrom'";
}

if(!empty($filterTo)) {
    $filterTo = mysqli_real_escape_string($conn, $filterTo);
    $where .= " AND DATE(nl.created_at) <= '$filterTo'";
}

// Get statistics with error checking
$statsQuery = "SELECT 
    COUNT(*) as total_notifications,
    SUM(CASE WHEN status='sent' THEN 1 ELSE 0 END) as sent_count,
    SUM(CASE WHEN status='failed' THEN 1 ELSE 0 END) as failed_count,
    SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending_count
FROM notification_logs $where";

$statsResult = mysqli_query($conn, $statsQuery);
if($statsResult) {
    $stats = mysqli_fetch_assoc($statsResult);
} else {
    $stats = ['total_notifications' => 0, 'sent_count' => 0, 'failed_count' => 0, 'pending_count' => 0];
}

// Get paginated results
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$sql = "SELECT nl.*, m.name as medicine_name, fm.name as family_member_name
        FROM notification_logs nl
        LEFT JOIN reminders r ON nl.reminder_id = r.id
        LEFT JOIN prescription_medicine pm ON r.prescription_medicine_id = pm.id
        LEFT JOIN medicines m ON pm.medicine_id = m.id
        LEFT JOIN family_members fm ON r.family_member_id = fm.id
        $where
        ORDER BY nl.created_at DESC
        LIMIT $offset, $perPage";

$notificationsResult = mysqli_query($conn, $sql);
$notifications = [];
if($notificationsResult) {
    $notifications = $notificationsResult;
}

// Get total count for pagination
$totalQuery = "SELECT COUNT(*) as total FROM notification_logs $where";
$totalResult = mysqli_query($conn, $totalQuery);
$totalRow = $totalResult ? mysqli_fetch_assoc($totalResult) : ['total' => 0];
$totalPages = ceil($totalRow['total'] / $perPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification History - MediBuddy</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; border-top: 3px solid #667eea; }
        .stat-card.sent { border-top-color: #28a745; }
        .stat-card.failed { border-top-color: #dc3545; }
        .stat-card.pending { border-top-color: #ffc107; }
        .stat-number { font-size: 2rem; font-weight: bold; color: #667eea; }
        .stat-label { color: #999; font-size: 0.9rem; margin-top: 0.5rem; }
        .filter-section { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.3rem; font-weight: bold; font-size: 0.9rem; }
        .form-group input, .form-group select { width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
        .notification-card { background: white; padding: 1.5rem; margin-bottom: 1rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #667eea; }
        .notification-card.sent { border-left-color: #28a745; }
        .notification-card.failed { border-left-color: #dc3545; }
        .notification-card.pending { border-left-color: #ffc107; }
        .notification-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .notification-type { display: inline-block; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.85rem; font-weight: bold; background: #f0f4ff; color: #667eea; }
        .notification-type.sms { background: #f0fff4; color: #28a745; }
        .status-badge { display: inline-block; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.85rem; font-weight: bold; }
        .status-sent { background: #e8f5e9; color: #2e7d32; }
        .status-failed { background: #ffebee; color: #c62828; }
        .status-pending { background: #fff8e1; color: #f57f17; }
        .notification-details { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 1rem 0; }
        .detail-item { font-size: 0.9rem; }
        .detail-label { color: #999; font-weight: bold; }
        .pagination { text-align: center; margin: 2rem 0; }
        .pagination a, .pagination span { padding: 0.5rem 0.8rem; margin: 0 0.2rem; border-radius: 4px; display: inline-block; }
        .pagination a { background: #667eea; color: white; text-decoration: none; }
        .pagination a:hover { background: #5568d3; }
        .pagination .active { background: #667eea; color: white; }
        .no-data { text-align: center; padding: 2rem; color: #999; }
        .alert-info { background: #e3f2fd; color: #1976d2; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem; border-left: 4px solid #1976d2; }
    </style>
</head>
<body>
<?php require_once '../includes/header.php'; ?>

<main class="main-content">
    <h2>Notification History</h2>

    <!-- Added info alert for first-time users -->
    <div class="alert-info">
        <strong>Note:</strong> Notification history will appear here once your cron job sends reminders. To test manually, use the test panel in your notification settings.
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $stats['total_notifications'] ?? 0 ?></div>
            <div class="stat-label">Total Notifications</div>
        </div>
        <div class="stat-card sent">
            <div class="stat-number" style="color: #28a745;"><?= $stats['sent_count'] ?? 0 ?></div>
            <div class="stat-label">Sent Successfully</div>
        </div>
        <div class="stat-card failed">
            <div class="stat-number" style="color: #dc3545;"><?= $stats['failed_count'] ?? 0 ?></div>
            <div class="stat-label">Failed</div>
        </div>
        <div class="stat-card pending">
            <div class="stat-number" style="color: #ffc107;"><?= $stats['pending_count'] ?? 0 ?></div>
            <div class="stat-label">Pending</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-section">
        <h3 style="margin-top: 0;">Filter Notifications</h3>
        <form method="GET" id="filter-form" class="filter-grid">
            <div class="form-group">
                <label>Type</label>
                <select name="type">
                    <option value="">All Types</option>
                    <option value="email" <?= $filterType === 'email' ? 'selected' : '' ?>>Email</option>
                    <option value="sms" <?= $filterType === 'sms' ? 'selected' : '' ?>>SMS</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="">All Status</option>
                    <option value="sent" <?= $filterStatus === 'sent' ? 'selected' : '' ?>>Sent</option>
                    <option value="failed" <?= $filterStatus === 'failed' ? 'selected' : '' ?>>Failed</option>
                    <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>
            <div class="form-group">
                <label>From Date</label>
                <input type="date" name="from" value="<?= htmlspecialchars($filterFrom) ?>">
            </div>
            <div class="form-group">
                <label>To Date</label>
                <input type="date" name="to" value="<?= htmlspecialchars($filterTo) ?>">
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: 1.65rem;">Apply Filters</button>
        </form>
        <a href="notification-history.php" class="btn btn-secondary" style="margin-top: 0.5rem;">Clear Filters</a>
    </div>

    <!-- Notifications List -->
    <h3>Notification Records</h3>
    <?php 
    if(!$notifications || mysqli_num_rows($notifications) === 0):
    ?>
        <div class="no-data">
            <p>No notifications found matching your filters.</p>
        </div>
    <?php 
    else:
        while($notif = mysqli_fetch_assoc($notifications)):
            $statusClass = strtolower($notif['status']);
            $typeClass = strtolower($notif['notification_type']);
    ?>
        <div class="notification-card <?= $statusClass ?>">
            <div class="notification-header">
                <div>
                    <span class="notification-type <?= $typeClass ?>"><?= ucfirst($notif['notification_type']) ?></span>
                    <span class="status-badge status-<?= $statusClass ?>"><?= ucfirst($notif['status']) ?></span>
                </div>
                <div style="font-size: 0.9rem; color: #999;">
                    <?= date('Y-m-d H:i:s', strtotime($notif['created_at'])) ?>
                </div>
            </div>

            <div class="notification-details">
                <div class="detail-item">
                    <div class="detail-label">Medicine</div>
                    <div><?= htmlspecialchars($notif['medicine_name'] ?? 'N/A') ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Patient</div>
                    <div><?= htmlspecialchars($notif['family_member_name'] ?? 'N/A') ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Recipient</div>
                    <div><?= htmlspecialchars($notif['recipient_email'] ?? $notif['recipient_phone'] ?? 'N/A') ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Subject</div>
                    <div><?= htmlspecialchars(substr($notif['subject'], 0, 50)) ?></div>
                </div>
            </div>

            <?php if($notif['status'] === 'failed' && $notif['error_message']): ?>
                <div style="background: #ffebee; padding: 0.75rem; border-radius: 4px; color: #c62828; font-size: 0.9rem;">
                    <strong>Error:</strong> <?= htmlspecialchars($notif['error_message']) ?>
                </div>
            <?php endif; ?>

            <?php if($notif['sent_at']): ?>
                <div style="margin-top: 0.5rem; font-size: 0.9rem; color: #999;">
                    Sent: <?= date('Y-m-d H:i:s', strtotime($notif['sent_at'])) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php 
        endwhile;
    endif;
    ?>

    <!-- Pagination -->
    <?php if($totalPages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?>
                <a href="?page=1<?= !empty($filterStatus) ? '&status=' . htmlspecialchars($filterStatus) : '' ?>">First</a>
                <a href="?page=<?= $page - 1 ?><?= !empty($filterStatus) ? '&status=' . htmlspecialchars($filterStatus) : '' ?>">Previous</a>
            <?php endif; ?>

            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <?php if($i === $page): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?><?= !empty($filterStatus) ? '&status=' . htmlspecialchars($filterStatus) : '' ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?><?= !empty($filterStatus) ? '&status=' . htmlspecialchars($filterStatus) : '' ?>">Next</a>
                <a href="?page=<?= $totalPages ?><?= !empty($filterStatus) ? '&status=' . htmlspecialchars($filterStatus) : '' ?>">Last</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</main>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>
