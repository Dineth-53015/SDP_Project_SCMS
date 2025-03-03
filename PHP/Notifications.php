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

    // Get POST data
    $action = $_POST['action'] ?? null;
    $user_id = $_POST['user_id'] ?? null;

    if ($action === 'fetch_notifications') {
        if (empty($user_id)) {
            echo json_encode(['success' => false, 'message' => 'User ID is required.']);
            exit;
        }

        // Fetch notifications for the logged-in user
        $stmt = $pdo->prepare("
            SELECT title, message, created_at 
            FROM notifications 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC
        ");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $notifications]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>