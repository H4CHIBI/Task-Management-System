<?php
session_start();
require_once '../../config/config.php';
require_once '../libs/dompdf/autoload.inc.php'; 

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    die("Unauthorized access.");
}

// 1. Capture All Filters
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';
$status_val = $_GET['status'] ?? 'all';
$user_id    = $_GET['user_id'] ?? 'all';

$params = [];
$filters = "";
$meta_info = [];

// Handle Date Sorting Info
if (!empty($start_date) && !empty($end_date)) {
    $filters .= " AND t.created_at BETWEEN :start AND :end ";
    $params[':start'] = $start_date . " 00:00:00";
    $params[':end']   = $end_date . " 23:59:59";
    $meta_info[] = "Period: " . date('M d, Y', strtotime($start_date)) . " to " . date('M d, Y', strtotime($end_date));
} else {
    $meta_info[] = "Period: All Time";
}

// Handle Status Info
if ($status_val !== 'all') {
    $filters .= " AND t.is_completed = :is_done ";
    $params[':is_done'] = ($status_val === 'completed') ? 1 : 0;
    $status_label = ($status_val === 'completed') ? 'Completed Tasks' : 'Pending Tasks';
    $meta_info[] = "Status: " . $status_label;
} else {
    $meta_info[] = "Status: All Progress";
}

// Handle Assigned User Info
if ($user_id !== 'all') {
    $u_stmt = $pdo->prepare("SELECT username FROM users_tbl WHERE id = ?");
    $u_stmt->execute([$user_id]);
    $u_name = $u_stmt->fetchColumn();
    $filters .= " AND t.assigned_to = :user_id ";
    $params[':user_id'] = $user_id;
    $meta_info[] = "Assigned To: " . ($u_name ?: 'Unknown Member');
} else {
    $meta_info[] = "Assigned To: All Members";
}

// Create a clean subtitle string
$subtitle = implode("  |  ", $meta_info);

// 2. Fetch Data
try {
    // Note: We include the $filters inside the JOIN/WHERE to ensure counts reflect the sorting
    $query = "SELECT 
                p.name as project_name, 
                COUNT(t.id) as total_tasks,
                SUM(CASE WHEN t.is_completed = 1 THEN 1 ELSE 0 END) as done
              FROM project_tbl p 
              LEFT JOIN task_tbl t ON p.id = t.project_id $filters
              GROUP BY p.id 
              HAVING total_tasks > 0
              ORDER BY p.name ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $reports = $stmt->fetchAll();

    $g_tasks = 0; $g_done = 0;
    foreach($reports as $r) { $g_tasks += $r['total_tasks']; $g_done += $r['done']; }
    $g_percent = ($g_tasks > 0) ? round(($g_done / $g_tasks) * 100) : 0;

} catch (PDOException $e) {
    die("Export Error: " . $e->getMessage());
}

// 3. Dompdf Configuration
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$html = '
<style>
    body { font-family: "Helvetica", sans-serif; color: #334155; margin: 0; padding: 0; }
    .header { text-align: center; border-bottom: 2px solid #10b981; padding-bottom: 20px; margin-bottom: 30px; }
    .workspace-tag { font-size: 9px; font-weight: bold; color: #10b981; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 5px; }
    .title { font-size: 20px; font-weight: bold; color: #0f172a; margin: 0; text-transform: uppercase; }
    .subtitle { font-size: 10px; color: #475569; font-weight: bold; margin-top: 10px; background: #f1f5f9; padding: 8px; border-radius: 5px; display: inline-block; }
    .meta { font-size: 8px; color: #94a3b8; margin-top: 10px; text-transform: uppercase; }
    
    .stats-row { width: 100%; margin-bottom: 35px; }
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
    .stat-val { font-size: 22px; font-weight: bold; color: #0f172a; display: block; }
    .stat-label { font-size: 8px; text-transform: uppercase; font-weight: bold; color: #94a3b8; margin-top: 4px; }

    .section-title { font-size: 11px; font-weight: bold; color: #475569; margin-bottom: 15px; text-transform: uppercase; border-left: 3px solid #10b981; padding-left: 8px; }

    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background-color: #f8fafc; text-align: left; font-size: 9px; padding: 12px 10px; border-bottom: 1px solid #e2e8f0; color: #64748b; text-transform: uppercase; }
    td { padding: 12px 10px; border-bottom: 1px solid #f1f5f9; font-size: 10px; color: #334155; }
    
    .footer { position: fixed; bottom: -20px; width: 100%; font-size: 9px; text-align: center; color: #cbd5e1; border-top: 1px solid #f1f5f9; padding-top: 10px; }
</style>

<div class="header">
    <div class="workspace-tag">System Administrator Workspace</div>
    <div class="title">Project Performance Report</div>
    <div class="subtitle">' . $subtitle . '</div>
    <div class="meta">Generation Date: ' . date('F d, Y h:i A') . '</div>
</div>

<div class="stats-row">
    <div class="stat-box">
        <span class="stat-val">' . $g_tasks . '</span>
        <span class="stat-label">Filtered Tasks</span>
    </div>
    <div class="stat-box">
        <span class="stat-val">' . $g_done . '</span>
        <span class="stat-label">Tasks Completed</span>
    </div>
    <div class="stat-box last-box">
        <span class="stat-val">' . $g_percent . '%</span>
        <span class="stat-label">Productivity Rate</span>
    </div>
    <div style="clear: both;"></div>
</div>

<div class="section-title">Breakdown by Project</div>
<table>
    <thead>
        <tr>
            <th width="40%">Project Name</th>
            <th style="text-align: center;">Total Tasks</th>
            <th style="text-align: center;">Done</th>
            <th style="text-align: right;">Progress</th>
        </tr>
    </thead>
    <tbody>';

if (empty($reports)) {
    $html .= '<tr><td colspan="4" style="text-align:center; padding: 40px; color: #94a3b8;">No records match your selected criteria.</td></tr>';
} else {
    foreach ($reports as $row) {
        $percent = ($row['total_tasks'] > 0) ? round(($row['done'] / $row['total_tasks']) * 100) : 0;
        $html .= '<tr>
            <td><strong>' . htmlspecialchars($row['project_name']) . '</strong></td>
            <td style="text-align: center;">' . $row['total_tasks'] . '</td>
            <td style="text-align: center;">' . $row['done'] . '</td>
            <td style="text-align: right; font-weight: bold; color: #10b981;">' . $percent . '%</td>
        </tr>';
    }
}

$html .= '</tbody></table>
<div class="footer">
    Report Generated by Admin ID: ' . $_SESSION['user_id'] . ' &bull; TMS Performance Analytics &bull; Page 1
</div>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("TMS_Report_" . date('Y-m-d') . ".pdf", ["Attachment" => 0]);