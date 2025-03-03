<?php
session_start();

// PHP Mailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? null;

    if ($action === 'fetch') {
        // Fetch User ID and Name
        $stmt = $pdo->prepare("SELECT user_id, name FROM users");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $users]);
    } elseif ($action === 'fetch_lecturers') {
        // Fetch Lecturers and Administrators
        $stmt = $pdo->prepare("SELECT user_id, name FROM users WHERE role IN ('Administrator', 'Lecturer')");
        $stmt->execute();
        $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $lecturers]);
    } elseif ($action === 'fetch_students') {
        // Fetch Students
        $stmt = $pdo->prepare("SELECT user_id, name FROM users WHERE role = 'Student'");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $students]);
    } elseif ($action === 'fetch_active_users') {
        // Fetch Active Users
        $search = $data['search'] ?? '';

        $sql = "
            SELECT * FROM users WHERE status = 'Active'
        ";

        if (!empty($search)) {
            $sql .= " AND (name LIKE :search OR role LIKE :search)";
        }

        $stmt = $pdo->prepare($sql);

        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $stmt->bindParam(':search', $searchTerm);
        }

        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $users]);

    } elseif ($action === 'suspend_user') {
        // Suspend Users
        $user_id = $data['user_id'] ?? null;
    
        if (empty($user_id)) {
            echo json_encode(['success' => false, 'message' => 'User ID is required.']);
            exit;
        }
    
        // Fetch user details
        $stmt = $pdo->prepare("
            SELECT email, name 
            FROM users 
            WHERE user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
            exit;
        }
    
        // Update the user status to 'Suspended'
        $stmt = $pdo->prepare("
            UPDATE users 
            SET status = 'Suspended'
            WHERE user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    
        // Send email notification
        $emailSubject = "Account Suspended";
        $emailHeader = "Account Suspension Announcement";
        $accountStatus = "Suspended";
        $emailIntro = "We regret to inform you that your account has been suspended.";
        $emailCTA = "For further clarification, please feel free to ";
        $emailFooter = "We apologize for any inconvenience caused and appreciate your understanding.";
    
        $emailSent = sendAccountStatusEmail(
            $user['email'],
            $accountStatus,
            $emailSubject,
            $emailHeader,
            $emailIntro,
            $emailCTA,
            $emailFooter
        );
    
        if ($emailSent) {
            error_log("Email sent successfully to the user.");
        } else {
            error_log("Failed to send email to the user.");
        }
    
        echo json_encode(['success' => true, 'message' => 'User suspended!']);
    } elseif ($action === 'fetch_suspended_users') {
        // Fetch Suspended Users
        $search = $data['search'] ?? '';

        $sql = "
            SELECT * FROM users WHERE status = 'Suspended'
        ";

        if (!empty($search)) {
            $sql .= " AND (name LIKE :search OR role LIKE :search)";
        }

        $stmt = $pdo->prepare($sql);

        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $stmt->bindParam(':search', $searchTerm);
        }

        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $users]);

    } elseif ($action === 'activate_user') {
        // Activate User
        $user_id = $data['user_id'] ?? null;
    
        if (empty($user_id)) {
            echo json_encode(['success' => false, 'message' => 'User ID is required.']);
            exit;
        }
    
        // Fetch user details
        $stmt = $pdo->prepare("
            SELECT email, name 
            FROM users 
            WHERE user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
            exit;
        }
    
        // Update the user status to 'Active'
        $stmt = $pdo->prepare("
            UPDATE users 
            SET status = 'Active'
            WHERE user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    
        // Send email notification
        $emailSubject = "Account Activated";
        $emailHeader = "Account Activation Announcement";
        $accountStatus = "Activated";
        $emailIntro = "We are pleased to inform you that your account has been activated.";
        $emailCTA = "For further inquiries, feel free to ";
        $emailFooter = "Thank you for your attention, and we hope to see you there!";
    
        $emailSent = sendAccountStatusEmail(
            $user['email'],
            $accountStatus,
            $emailSubject,
            $emailHeader,
            $emailIntro,
            $emailCTA,
            $emailFooter
        );
    
        if ($emailSent) {
            error_log("Email sent successfully to the user.");
        } else {
            error_log("Failed to send email to the user.");
        }
    
        echo json_encode(['success' => true, 'message' => 'User Activated!']);
    } elseif ($action === 'fetch_pending_users') {
        // Fetch Active Users
        $search = $data['search'] ?? '';

        $sql = "
            SELECT * FROM users WHERE status = 'Pending'
        ";

        if (!empty($search)) {
            $sql .= " AND (name LIKE :search OR role LIKE :search)";
        }

        $stmt = $pdo->prepare($sql);

        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $stmt->bindParam(':search', $searchTerm);
        }

        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $users]);

    } elseif ($action === 'fetch_user_registration_trend') {
        $stmt = $pdo->prepare("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') AS month, 
                COUNT(user_id) AS user_count
            FROM users
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month
        ");
        $stmt->execute();
        $registrationTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $registrationTrend]);
    } elseif ($action === 'fetch_user_status_changes') {
        $stmt = $pdo->prepare("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') AS month, 
                status, 
                COUNT(user_id) AS user_count
            FROM users
            GROUP BY DATE_FORMAT(created_at, '%Y-%m'), status
            ORDER BY month, status
        ");
        $stmt->execute();
        $statusChanges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $statusChanges]);
    } elseif ($action === 'fetch_user_role_distribution') {
        $stmt = $pdo->prepare("
            SELECT role, COUNT(user_id) AS user_count
            FROM users
            GROUP BY role
        ");
        $stmt->execute();
        $roleDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $roleDistribution]);
    } elseif ($action === 'fetch_user_status_distribution') {
        $stmt = $pdo->prepare("
            SELECT status, COUNT(user_id) AS user_count
            FROM users
            GROUP BY status
        ");
        $stmt->execute();
        $statusDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $statusDistribution]);
    } elseif ($action === 'fetch_user_faculty_distribution') {
        $stmt = $pdo->prepare("
            SELECT faculty, COUNT(user_id) AS user_count
            FROM users
            GROUP BY faculty
        ");
        $stmt->execute();
        $facultyDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $facultyDistribution]);
    } elseif ($action === 'sign_in') {
        // User Sign In Crediantials Check
        $usernameOrEmail = $data['usernameOrEmail'] ?? null;
        $password = $data['password'] ?? null;

        if (empty($usernameOrEmail) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Username/Email and Password are required.']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE (username = :usernameOrEmail OR email = :usernameOrEmail)
        ");
        $stmt->bindParam(':usernameOrEmail', $usernameOrEmail);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Invalid username or email.']);
            exit;
        }

        if (!password_verify($password, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid password.']);
            exit;
        }

        if ($user['status'] === 'Pending') {
            echo json_encode(['success' => false, 'message' => 'Your account is still pending activation. If this persists for more than 2 days, please contact support.']);
            exit;
        } elseif ($user['status'] === 'Suspended') {
            echo json_encode(['success' => false, 'message' => 'Your account has been suspended. Please contact the staff for more details.']);
            exit;
        } elseif ($user['status'] === 'Active') {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            $redirectUrl = '';
            switch ($user['role']) {
                case 'Administrator':
                    $redirectUrl = 'Home.php';
                    break;
                case 'Lecturer':
                    $redirectUrl = 'LHome.php';
                    break;
                case 'Student':
                    $redirectUrl = 'SHome.php';
                    break;
                default:
                    $redirectUrl = 'index.php';
            }

            echo json_encode(['success' => true, 'message' => 'Login successful!', 'redirectUrl' => $redirectUrl]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Your account status is invalid. Please contact support.']);
        }
    } elseif ($action === 'fetch_user_details') {
        // Fetch User Details
        $user_id = $data['user_id'] ?? null;

        if (empty($user_id)) {
            echo json_encode(['success' => false, 'message' => 'User ID is required.']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT name, email, username, role, phone_number, faculty, status, created_at
            FROM users
            WHERE user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $userDetails = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userDetails) {
            echo json_encode(['success' => true, 'data' => $userDetails]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
        }
    } elseif ($action === 'save_user') {
        // Update User Username or Password
        $user_id = $data['user_id'] ?? null;
        $username = $data['username'] ?? null;
        $new_password = $data['new_password'] ?? null;
        $current_username = $data['current_username'] ?? null;

        if (empty($user_id) || empty($username)) {
            echo json_encode(['success' => false, 'message' => 'User ID and Username are required.']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT user_id FROM users 
            WHERE username = :username AND user_id != :user_id
        ");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            echo json_encode(['success' => false, 'message' => 'Username is already taken.']);
            exit;
        }

        $updateFields = [];
        $params = [];

        $updateFields[] = "username = :username";
        $params[':username'] = $username;

        if (!empty($new_password)) {
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $updateFields[] = "password = :password";
            $params[':password'] = $hashedPassword;
        }

        $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE user_id = :user_id";
        $params[':user_id'] = $user_id;

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => &$value) {
            $stmt->bindParam($key, $value);
        }
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'User details updated successfully.']);
        exit;
    } elseif ($action === 'update_user_details') {
        $user_id = $data['user_id'] ?? null;
        $new_username = $data['username'] ?? null;
        $new_password = $data['password'] ?? null;

        if (empty($user_id) || empty($new_username)) {
            echo json_encode(['success' => false, 'message' => 'User ID and Username are required.']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT user_id FROM users 
            WHERE username = :username AND user_id != :user_id
        ");
        $stmt->bindParam(':username', $new_username);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            echo json_encode(['success' => false, 'message' => 'Username already taken.']);
            exit;
        }

        $sql = "UPDATE users SET username = :username";
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql .= ", password = :password";
        }
        $sql .= " WHERE user_id = :user_id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $new_username);
        $stmt->bindParam(':user_id', $user_id);
        if (!empty($new_password)) {
            $stmt->bindParam(':password', $hashed_password);
        }

        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'User details updated successfully.']);
    } elseif ($action === 'fetch_notification_preferences') {
        // Fetch Notification Preferences
        $user_id = $data['user_id'] ?? null;
    
        if (empty($user_id)) {
            echo json_encode(['success' => false, 'message' => 'User ID is required.']);
            exit;
        }
    
        $stmt = $pdo->prepare("
            SELECT app_alert, email, sms
            FROM notification_preferences
            WHERE user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $preferences = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($preferences) {
            echo json_encode(['success' => true, 'data' => $preferences]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Notification preferences not found.']);
        }
    } elseif ($action === 'toggle_notification_preference') {
        // Change Notification Preferences
        $user_id = $data['user_id'] ?? null;
        $type = $data['type'] ?? null;
    
        if (empty($user_id) || empty($type)) {
            echo json_encode(['success' => false, 'message' => 'User ID and Type are required.']);
            exit;
        }
    
        // Fetch current value
        $stmt = $pdo->prepare("
            SELECT $type
            FROM notification_preferences
            WHERE user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $currentValue = $stmt->fetchColumn();
    
        // Toggle the value
        $newValue = $currentValue ? 0 : 1;
    
        // Update the value
        $stmt = $pdo->prepare("
            UPDATE notification_preferences
            SET $type = :newValue
            WHERE user_id = :user_id
        ");
        $stmt->bindParam(':newValue', $newValue);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    
        echo json_encode(['success' => true, 'newValue' => $newValue]);
    } elseif ($action === 'fetch_active_students') {
        // Fetch Active Students
        $stmt = $pdo->prepare("
            SELECT user_id, name 
            FROM users 
            WHERE role = 'Student' 
            AND status = 'Active'
        ");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        echo json_encode(['success' => true, 'data' => $students]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function sendAccountStatusEmail($recipientEmail, $accountStatus, $emailSubject, $emailHeader, $emailIntro, $emailCTA, $emailFooter) {
    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'sdpprojectgroup04@gmail.com';
        $mail->Password = 'ksac hwpn pzwd wvwd';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('sdpprojectgroup04@gmail.com', 'Account Status Announcement');
        $mail->addAddress($recipientEmail);

        // Set email subject
        $mail->Subject = $emailSubject;

        // HTML email content
        $htmlContent = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Account Status</title>
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    font-family: Arial, sans-serif;
                    background-color: #ffffff;
                    color: #333333;
                }
                .email-container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    border: 1px solid #dddddd;
                    border-radius: 8px;
                    background-color: #ffffff;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .header h1 {
                    color: #ff7e5f;
                    font-size: 28px;
                    font-weight: bold;
                    margin: 0;
                }
                .content {
                    text-align: center;
                }
                .status-box {
                    display: inline-block;
                    margin: 20px 0;
                    padding: 15px 20px;
                    background-color: #ffffff;
                    color: #ff7e5f;
                    font-size: 24px;
                    font-weight: bold;
                    border: 2px solid #ff7e5f;
                    border-radius: 8px;
                    letter-spacing: 2px;
                }
                .instructions {
                    font-size: 16px;
                    line-height: 1.6;
                    color: #555555;
                }
                .footer {
                    margin-top: 20px;
                    text-align: center;
                    font-size: 14px;
                    color: #999999;
                }
                .footer a {
                    color: #ff7e5f;
                    text-decoration: none;
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="header">
                    <h1>' . htmlspecialchars($emailHeader) . '</h1>
                </div>
                <div class="content">
                    <p class="instructions">' . htmlspecialchars($emailIntro) . '</p>
                    <div class="status-box">' . htmlspecialchars($accountStatus) . '</div>
                    <p class="instructions">
                        ' . ($accountStatus === 'Activated' ? 
                            'Your account has been successfully activated. You can now log in and access all features.' : 
                            'Unfortunately, your account has been suspended. Please contact support for further assistance.') . '
                    </p>
                </div>
                <div class="footer">
                    <p>' . htmlspecialchars($emailCTA) . '<a href="https://wa.me/+94772957834">contact support</a>.</p>
                </div>
            </div>
        </body>
        </html>
        ';

        $mail->isHTML(true);
        $mail->Body = $htmlContent;

        if ($mail->send()) {
            return true;
        } else {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            return false;
        }
    } catch (Exception $e) {
        error_log("PHPMailer Exception: " . $e->getMessage());
        return false;
    }
}
?>