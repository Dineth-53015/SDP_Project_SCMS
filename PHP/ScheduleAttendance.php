<?php
session_start();
// Database Connection
$host = 'localhost';
$dbname = 'SCMS';
$username = 'root';
$password = '';

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $action = $_POST['action'] ?? null;

    // Mark Attendance
    if ($action === 'mark_attendance') {
        $scheduleId = $_POST['schedule_id'] ?? null;
        $userId = $_POST['user_id'] ?? null;

        if (empty($scheduleId) || empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'Schedule ID and User ID are required.']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO class_attendance (schedule_id, user_id, attendance, attended_at)
            VALUES (:schedule_id, :user_id, '1', NOW())
        ");
        $stmt->bindParam(':schedule_id', $scheduleId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Attendance marked successfully!']);
    } elseif ($action === 'fetch') {
        // Fetch Attendance Details
        $stmt = $pdo->prepare("SELECT 
                                ca.attendance_id,
                                s.title AS schedule_title,
                                u.name AS student_name,
                                CASE ca.attendance WHEN '1' THEN 'Yes' ELSE 'No' END AS attendance_status,
                                ca.attended_at
                            FROM class_attendance ca
                            JOIN schedules s ON ca.schedule_id = s.schedule_id
                            JOIN users u ON ca.user_id = u.user_id
                            ORDER BY ca.attended_at DESC");
        $stmt->execute();
        $class_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $class_attendance]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>