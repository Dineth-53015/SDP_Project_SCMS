<?php
// Database connection
$host = 'localhost';
$dbname = 'SCMS';
$username = 'root';
$password = '';
try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, true);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'fetch_attendance') {
            // Fetch attendance data
            $stmt = $pdo->prepare("
                SELECT 
                    e.title AS event_title,
                    u.name AS user_name,
                    ea.check_in_time,
                    ea.check_out_time
                FROM event_attendance ea
                JOIN events e ON ea.event_id = e.event_id
                JOIN users u ON ea.user_id = u.user_id
                ORDER BY ea.check_in_time DESC
            ");
            $stmt->execute();
            $attendanceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $attendanceData]);
            exit;
        } elseif ($action === 'fetch_attendance_statuses') {
            $stmt = $pdo->prepare("SELECT CASE WHEN check_out_time = '00:00:00' THEN 'Absent' ELSE 'Attended' END as status, COUNT(*) as count FROM event_attendance GROUP BY status");
            $stmt->execute();
            $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $statuses]);
        } else {
            $eventId = $_POST['event_id'];
            $userId = $_POST['user_id'];
            $action = $_POST['action'];

            // Validate input data
            if (empty($eventId) || empty($userId)) {
                echo json_encode(['success' => false, 'message' => 'Event ID or User ID is missing.']);
                exit;
            }

            if ($action === 'check_in') {
                // Check if the user has already checked in for this event
                $checkStmt = $pdo->prepare("SELECT * FROM event_attendance WHERE event_id = :event_id AND user_id = :user_id");
                $checkStmt->bindParam(':event_id', $eventId);
                $checkStmt->bindParam(':user_id', $userId);
                $checkStmt->execute();

                if ($checkStmt->rowCount() > 0) {
                    echo json_encode(['success' => false, 'message' => 'You have already checked in for this event.']);
                    exit;
                }

                // Insert check-in record
                $stmt = $pdo->prepare("INSERT INTO event_attendance (event_id, user_id, check_in_time, check_out_time) VALUES (:event_id, :user_id, NOW(), '00:00:00')");
                $stmt->bindParam(':event_id', $eventId);
                $stmt->bindParam(':user_id', $userId);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Check-in successful!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to insert check-in record.']);
                }
            } elseif ($action === 'check_out') {
                // Check if the user has already checked in for this event
                $checkStmt = $pdo->prepare("SELECT * FROM event_attendance WHERE event_id = :event_id AND user_id = :user_id");
                $checkStmt->bindParam(':event_id', $eventId);
                $checkStmt->bindParam(':user_id', $userId);
                $checkStmt->execute();

                if ($checkStmt->rowCount() === 0) {
                    // User hasn't checked in yet
                    echo json_encode(['success' => false, 'message' => 'You must check in before checking out.']);
                    exit;
                }

                // Fetch the record to check the check_out_time
                $record = $checkStmt->fetch(PDO::FETCH_ASSOC);

                // User has checked in but hasn't checked out, so proceed with check-out
                $updateStmt = $pdo->prepare("UPDATE event_attendance SET check_out_time = NOW() WHERE event_id = :event_id AND user_id = :user_id");
                $updateStmt->bindParam(':event_id', $eventId);
                $updateStmt->bindParam(':user_id', $userId);
                $updateStmt->execute();

                echo json_encode(['success' => true, 'message' => 'Check-out successful!']);
            } 
            else {
                echo json_encode(['success' => false, 'message' => 'Invalid action.']);
            }
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>