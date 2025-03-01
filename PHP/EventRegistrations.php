<?php
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
    $action = $_POST['action'] ?? null;

    if ($action === 'fetch_registrations') {
        // Fetch all registrations or filter by search term
        $searchTerm = '%' . ($_POST['search_term'] ?? '') . '%';
        // Prepare the SQL query
        $stmt = $pdo->prepare("
            SELECT 
                er.event_id, 
                e.title AS event_title, 
                u.name AS user_name, 
                u.user_id,
                er.registration_status, 
                er.registered_at 
            FROM event_registrations er
            LEFT JOIN events e ON er.event_id = e.event_id
            LEFT JOIN users u ON er.user_id = u.user_id
            WHERE e.title LIKE :search_term OR u.name LIKE :search_term
        ");
        $stmt->bindParam(':search_term', $searchTerm);
        $stmt->execute();
        $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $registrations]);
    } elseif ($action === 'reject_registration') {
        // Reject a registration (update status to "Rejected")
        $event_id = $_POST['event_id'];
        $user_id = $_POST['user_id'];
    
        error_log("Rejecting registration for event_id: $event_id, user_id: $user_id");
    
        // Check the current registration status
        $checkStmt = $pdo->prepare("
            SELECT registration_status 
            FROM event_registrations 
            WHERE event_id = :event_id AND user_id = :user_id
        ");
        $checkStmt->bindParam(':event_id', $event_id);
        $checkStmt->bindParam(':user_id', $user_id);
        $checkStmt->execute();
    
        $currentStatus = $checkStmt->fetchColumn();
    
        if ($currentStatus === 'Rejected') {
            // If already rejected, return a specific message
            echo json_encode(['success' => false, 'message' => 'Registration is already rejected.']);
        } else {
            // Update the registration status
            $updateStmt = $pdo->prepare("
                UPDATE event_registrations 
                SET registration_status = 'Rejected' 
                WHERE event_id = :event_id AND user_id = :user_id
            ");
            $updateStmt->bindParam(':event_id', $event_id);
            $updateStmt->bindParam(':user_id', $user_id);
            $updateStmt->execute();
    
            // Check if the update was successful
            if ($updateStmt->rowCount() > 0) {
                // Fetch event details
                $stmt = $pdo->prepare("
                    SELECT title, description, event_date, start_time, end_time, venue, category, max_participants 
                    FROM events 
                    WHERE event_id = :event_id
                ");
                $stmt->bindParam(':event_id', $event_id);
                $stmt->execute();
                $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if (!$event) {
                    echo json_encode(['success' => false, 'message' => 'Event not found!']);
                    exit;
                }
    
                // Prepare notification details
                $notification_title = "Registration Rejected: " . $event['title'];
                $notification_message = "Your registration for the event '{$event['title']}' has been rejected.";
    
                // Insert notification for the user
                $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, created_at) VALUES (:user_id, :title, :message, NOW())");
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':title', $notification_title);
                $stmt->bindParam(':message', $notification_message);
                $stmt->execute();
    
                // Fetch user's email and email preference
                $stmt = $pdo->prepare("
                    SELECT u.email, np.email as email_preference 
                    FROM users u
                    LEFT JOIN notification_preferences np ON u.user_id = np.user_id
                    WHERE u.user_id = :user_id
                ");
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if ($user && $user['email_preference'] == 1) {
                    // Prepare email details
                    $emailSubject = "Registration Rejected: " . $event['title'];
                    $emailHeader = "Registration Rejected Announcement";
                    $emailIntro = "We regret to inform you that your registration for the following event has been rejected:";
                    $emailCTA = "For further clarification, please feel free to ";
                    $emailFooter = "We apologize for any inconvenience caused and appreciate your understanding.";
    
                    $emailSent = sendEventRegistrationEmail(
                        $user['email'],
                        $event['title'],
                        $event['description'],
                        $event['event_date'],
                        $event['start_time'],
                        $event['end_time'],
                        $event['venue'],
                        $event['category'],
                        $event['max_participants'],
                        'Rejected',
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
                }
    
                echo json_encode(['success' => true, 'message' => 'Registration rejected successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No rows updated. Registration not found.']);
            }
        }
    } elseif ($action === 'fetch_registration_statuses') {
        $stmt = $pdo->prepare("SELECT registration_status, COUNT(*) as count FROM event_registrations GROUP BY registration_status");
        $stmt->execute();
        $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $statuses]);
    } elseif ($action === 'fetch_users_by_event') {
        // Fetch users registered for a specific event
        $event_id = $_POST['event_id'];
    
        // Prepare the SQL query
        $stmt = $pdo->prepare("
            SELECT 
                u.user_id, 
                u.name AS user_name
            FROM event_registrations er
            LEFT JOIN users u ON er.user_id = u.user_id
            WHERE er.event_id = :event_id
        ");
        $stmt->bindParam(':event_id', $event_id);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        echo json_encode(['success' => true, 'data' => $users]);
    }
    elseif ($action === 'fetch_registrations') {
        $user_id = $_POST['user_id'] ?? null;

        if (empty($user_id)) {
            echo json_encode(['success' => false, 'message' => 'User ID is required.']);
            exit;
        }

        // Fetch registrations for the user
        $stmt = $pdo->prepare("
            SELECT 
                er.event_id, 
                e.title AS event_title, 
                u.name AS user_name, 
                er.registration_status, 
                er.registered_at 
            FROM event_registrations er
            LEFT JOIN events e ON er.event_id = e.event_id
            LEFT JOIN users u ON er.user_id = u.user_id
            WHERE er.user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $registrations]);
    } elseif ($action === 'check_registration') {
        $event_id = $_POST['event_id'] ?? null;
        $user_id = $_POST['user_id'] ?? null;

        if (empty($event_id) || empty($user_id)) {
            echo json_encode(['success' => false, 'message' => 'Event ID and User ID are required.']);
            exit;
        }

        // Check if the user is already registered for the event
        $stmt = $pdo->prepare("
            SELECT registration_status 
            FROM event_registrations 
            WHERE event_id = :event_id AND user_id = :user_id
        ");
        $stmt->bindParam(':event_id', $event_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $registration = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($registration) {
            echo json_encode(['success' => true, 'registered' => true, 'status' => $registration['registration_status']]);
        } else {
            echo json_encode(['success' => true, 'registered' => false]);
        }
    } elseif ($action === 'register') {
        $event_id = $_POST['event_id'] ?? null;
        $user_id = $_POST['user_id'] ?? null;

        if (empty($event_id)) {
            echo json_encode(['success' => false, 'message' => 'Event ID is required.']);
            exit;
        }
        if (empty($user_id)) {
            echo json_encode(['success' => false, 'message' => 'User ID is required.']);
            exit;
        }

        // Check if the user is already registered for the event
        $check_stmt = $pdo->prepare("
            SELECT registration_status 
            FROM event_registrations 
            WHERE event_id = :event_id AND user_id = :user_id
        ");
        $check_stmt->bindParam(':event_id', $event_id);
        $check_stmt->bindParam(':user_id', $user_id);
        $check_stmt->execute();
        $existing_registration = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_registration) {
            echo json_encode(['success' => false, 'message' => 'You are already registered for this event.']);
            exit;
        }

        // Insert the registration into the database
        $stmt = $pdo->prepare("
            INSERT INTO event_registrations (event_id, user_id, registration_status, registered_at)
            VALUES (:event_id, :user_id, 'Registered', NOW())
        ");
        $stmt->bindParam(':event_id', $event_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Registration successful!']);
    } elseif ($action === 'count_registrations') {
        $event_id = $_POST['event_id'] ?? null;
    
        if (empty($event_id)) {
            echo json_encode(['success' => false, 'message' => 'Event ID is required.']);
            exit;
        }
    
        // Count the number of registrations for the event
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS count 
            FROM event_registrations 
            WHERE event_id = :event_id
        ");
        $stmt->bindParam(':event_id', $event_id);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($count) {
            echo json_encode(['success' => true, 'count' => $count['count']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to count registrations.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function sendEventRegistrationEmail($recipientEmail, $event_title, $event_description, $event_date, $start_time, $end_time, $venue, $category, $max_participants, $registration_status, $emailSubject, $emailHeader, $emailIntro, $emailCTA, $emailFooter) {
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

        $mail->setFrom('sdpprojectgroup04@gmail.com', 'Event Registration Announcement');
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
            <title>' . htmlspecialchars($emailSubject) . '</title>
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
                    text-align: left;
                }
                .event-details {
                    margin: 20px 0;
                    padding: 15px;
                    background-color: #f9f9f9;
                    border: 1px solid #eeeeee;
                    border-radius: 8px;
                    line-height: 1.6;
                }
                .event-details p {
                    margin: 8px 0;
                    font-size: 16px;
                    color: #555555;
                }
                .footer {
                    margin-top: 20px;
                    text-align: center;
                    font-size: 14px;
                    color: #999999;
                }
                a {
                    color: #ff7e5f;
                    text-decoration: none;
                }
                a:hover {
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
                    <p>Dear Participant,</p>
                    <p>' . htmlspecialchars($emailIntro) . '</p>
                    <div class="event-details">
                        <p><strong>Title:</strong> ' . $event_title . '</p>
                        <p><strong>Description:</strong> ' . $event_description . '</p>
                        <p><strong>Date:</strong> ' . $event_date . '</p>
                        <p><strong>Time:</strong> ' . $start_time . ' - ' . $end_time . '</p>
                        <p><strong>Venue:</strong> ' . $venue . '</p>
                        <p><strong>Category:</strong> ' . $category . '</p>
                        <p><strong>Max Participants:</strong> ' . $max_participants . '</p>
                        <p><strong>Registration Status:</strong> ' . $registration_status . '</p>
                    </div>
                    <p>For further clarification, please feel free to <a href="https://wa.me/+94772957834">contact support</a>.</p>
                </div>
                <div class="footer">
                    <p>We apologize for any inconvenience caused and appreciate your understanding.</p>
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