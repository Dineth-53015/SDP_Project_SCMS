<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'scms');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get OTP from the form
$data = json_decode(file_get_contents('php://input'), true);
$otp = $data['otp'];

// Verify OTP
if ($otp == $_SESSION['otp']) {
    $registration_data = $_SESSION['registration_data'];

    // Function to check if username or email already exists
    function checkExistingUser($conn, $username, $email) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->num_rows > 0;
    }

    // Check if username or email already exists
    if (checkExistingUser($conn, $registration_data['username'], $registration_data['email'])) {
        $stmt_check = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt_check->bind_param("ss", $registration_data['username'], $registration_data['email']);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $stmt_check->close();

        if ($result_check->num_rows > 0) {
            $row = $result_check->fetch_assoc();
            if ($row['username'] == $registration_data['username']) {
                echo json_encode(['success' => false, 'message' => 'Username already exists']);
            } elseif ($row['email'] == $registration_data['email']) {
                echo json_encode(['success' => false, 'message' => 'Email already exists']);
            }
            exit();
        }
    }

    // Hash the password
    $hashed_password = password_hash($registration_data['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, username, password, role, phone_number, faculty, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");
    $stmt->bind_param("sssssss", $registration_data['name'], $registration_data['email'], $registration_data['username'], $hashed_password, $registration_data['role'], $registration_data['phone_number'], $registration_data['faculty']);

    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        
        $app_alert = 1;
        $email = 1;
        $sms = 1;
        
        $stmt_notification = $conn->prepare("INSERT INTO notification_preferences (user_id, app_alert, email, sms) VALUES (?, ?, ?, ?)");
        $stmt_notification->bind_param("iiii", $user_id, $app_alert, $email, $sms);
        
        if ($stmt_notification->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add notification preferences']);
        }
        
        $stmt_notification->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Sorry, something went wrong creating your account. Please try again later.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
}

$conn->close();
?>
