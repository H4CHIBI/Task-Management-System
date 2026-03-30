<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

require_once '../../config/config.php'; 
require_once '../../admin/libs/dompdf/autoload.inc.php'; 

use Dompdf\Dompdf;
use Dompdf\Options;

$user_id = $_SESSION['user_id'];

try {
    // 1. Fetch User's Real Name using the 'username' column per your database
    $userStmt = $pdo->prepare("SELECT username FROM users_tbl WHERE id = ?");
    $userStmt->execute([$user_id]);
    $user = $userStmt->fetch();
    $display_name = $user ? $user['username'] : 'System User';

    // 2. Fetch Overview Stats
    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed
        FROM task_tbl WHERE assigned_to = ?");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch();

    // 3. Fetch Project Breakdown
    $projStmt = $pdo->prepare("SELECT p.name, COUNT(t.id) as total, SUM(t.is_completed) as done
                               FROM project_tbl p JOIN task_tbl t ON p.id = t.project_id
                               WHERE t.assigned_to = ? GROUP BY p.id");
    $projStmt->execute([$user_id]);
    $projects = $projStmt->fetchAll();

} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}

ob_start(); 
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #334155; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 2px solid #10b981; padding-bottom: 20px; margin-bottom: 30px; }
        .workspace-tag { font-size: 9px; font-weight: bold; color: #10b981; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 5px; }
        .title { font-size: 20px; font-weight: bold; color: #0f172a; margin: 0; text-transform: uppercase; }
        .user-name { font-size: 16px; color: #1e293b; font-weight: bold; margin-top: 5px; }
        .meta { font-size: 10px; color: #94a3b8; margin-top: 5px; text-transform: uppercase; }
        
        .stats-row { width: 100%; margin-bottom: 35px; clear: both; }
        .stat-box { 
            width: 30%; 
            float: left; 
            text-align: center; 
            padding: 15px 0; 
            border: 1px solid #e2e8f0; 
            border-radius: 12px;
            margin-right: 3%;
        }
        .last-box { margin-right: 0; }
        .stat-val { font-size: 22px; font-weight: bold; color: #10b981; display: block; }
        .stat-label { font-size: 8px; text-transform: uppercase; font-weight: bold; color: #94a3b8; margin-top: 4px; }

        .section-title { font-size: 12px; font-weight: bold; color: #475569; margin-bottom: 10px; text-transform: uppercase; border-left: 3px solid #10b981; padding-left: 8px; }

        table { width: 100%; border-collapse: collapse; }
        th { background-color: #f8fafc; text-align: left; font-size: 9px; padding: 10px; border-bottom: 1px solid #e2e8f0; color: #64748b; text-transform: uppercase; }
        td { padding: 10px; border-bottom: 1px solid #f1f5f9; font-size: 11px; color: #334155; }
        
        .footer { margin-top: 50px; font-size: 9px; color: #cbd5e1; text-align: center; border-top: 1px solid #f1f5f9; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="workspace-tag">Member Workspace</div>
        <div class="title">Performance Report</div>
        <div class="user-name"><?= htmlspecialchars($display_name) ?></div>
        <div class="meta">Generated on <?= date('F d, Y') ?></div>
    </div>

    <div class="stats-row">
        <div class="stat-box">
            <span class="stat-val"><?= $stats['total'] ?></span>
            <span class="stat-label">Tasks Assigned</span>
        </div>
        <div class="stat-box">
            <span class="stat-val"><?= $stats['completed'] ?? 0 ?></span>
            <span class="stat-label">Completed</span>
        </div>
        <div class="stat-box last-box">
            <span class="stat-val">
                <?= ($stats['total'] > 0) ? round(($stats['completed'] / $stats['total']) * 100) : 0 ?>%
            </span>
            <span class="stat-label">Overall Efficiency</span>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="section-title">Project Completion Summary</div>
    <table>
        <thead>
            <tr>
                <th>Project Name</th>
                <th>Task Ratio</th>
                <th>Status</th>
                <th style="text-align: right;">Progress</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($projects as $p): 
                $rate = ($p['total'] > 0) ? ($p['done'] / $p['total']) * 100 : 0;
            ?>
            <tr>
                <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                <td><?= $p['done'] ?> / <?= $p['total'] ?></td>
                <td><?= ($rate == 100) ? 'Completed' : 'In Progress' ?></td>
                <td style="text-align: right; font-weight: bold; color: #10b981;"><?= round($rate) ?>%</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        Generated by Task Management System &copy; <?= date('Y') ?> • Official Performance Record
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("Performance_Report_" . str_replace(' ', '_', $display_name) . ".pdf", array("Attachment" => 0));
?>