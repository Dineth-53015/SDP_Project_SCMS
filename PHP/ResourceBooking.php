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

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_user_bookings') {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit;
        }

        $userId = $_SESSION['user_id'];

        $stmt = $pdo->prepare("SELECT rb.booking_id, r.resource_name, u.name, rb.start_time, rb.end_time, rb.reason, rb.status 
                            FROM resource_bookings rb
                            JOIN resources r ON rb.resource_id = r.resource_id
                            JOIN users u ON rb.user_id = u.user_id
                            WHERE rb.user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $userBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $userBookings]);
        exit;
    }

    // Fetch pending bookings
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_pending_bookings') {
        $stmt = $pdo->prepare("SELECT rb.booking_id, r.resource_name, u.name, rb.start_time, rb.end_time, rb.reason 
                               FROM resource_bookings rb
                               JOIN resources r ON rb.resource_id = r.resource_id
                               JOIN users u ON rb.user_id = u.user_id
                               WHERE rb.status = 'Pending'");
        $stmt->execute();
        $pendingBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $pendingBookings]);
        exit;
    }

    // Fetch bookings for the current week
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_current_week_bookings') {
        $startOfWeek = date('Y-m-d', strtotime('last sunday'));
        $endOfWeek = date('Y-m-d', strtotime('next saturday'));

        $stmt = $pdo->prepare("SELECT r.resource_name, rb.start_time, rb.end_time 
                               FROM resource_bookings rb
                               JOIN resources r ON rb.resource_id = r.resource_id
                               WHERE rb.start_time >= :startOfWeek AND rb.end_time <= :endOfWeek");
        $stmt->bindParam(':startOfWeek', $startOfWeek);
        $stmt->bindParam(':endOfWeek', $endOfWeek);
        $stmt->execute();
        $currentWeekBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $currentWeekBookings]);
        exit;
    }

    // Fetch booking status distribution
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_booking_status_distribution') {
        $stmt = $pdo->prepare("SELECT status, COUNT(*) AS count FROM resource_bookings GROUP BY status");
        $stmt->execute();
        $bookingStatusData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $bookingStatusData]);
        exit;
    }

    // Fetch all bookings
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_all_bookings') {
        $searchTerm = isset($_GET['search_term']) ? $_GET['search_term'] : '';

        $query = "SELECT rb.booking_id, r.resource_name, u.name AS allocated_to, rb.start_time, rb.end_time, rb.reason, rb.status 
                  FROM resource_bookings rb
                  JOIN resources r ON rb.resource_id = r.resource_id
                  JOIN users u ON rb.user_id = u.user_id";

        if (!empty($searchTerm)) {
            $query .= " WHERE r.resource_name LIKE :searchTerm 
                        OR u.name LIKE :searchTerm 
                        OR rb.start_time LIKE :searchTerm 
                        OR rb.end_time LIKE :searchTerm 
                        OR rb.status LIKE :searchTerm";
        }

        $stmt = $pdo->prepare($query);

        if (!empty($searchTerm)) {
            $searchTerm = "%$searchTerm%";
            $stmt->bindParam(':searchTerm', $searchTerm);
        }

        $stmt->execute();
        $allBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $allBookings]);
        exit;
    }

    // Fetch booking details by booking_id
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_booking_details' && isset($_GET['booking_id'])) {
        $bookingId = $_GET['booking_id'];
        $stmt = $pdo->prepare("SELECT rb.booking_id, u.name AS user_name, r.resource_name, rb.start_time, rb.end_time, rb.status, rb.requested_at, rb.reason, rb.approved_by, rb.updated_at 
                               FROM resource_bookings rb
                               JOIN users u ON rb.user_id = u.user_id
                               JOIN resources r ON rb.resource_id = r.resource_id
                               WHERE rb.booking_id = :booking_id");
        $stmt->bindParam(':booking_id', $bookingId);
        $stmt->execute();
        $bookingDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $bookingDetails]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_booking_status') {
        $bookingId = $_POST['booking_id'];
        $status = $_POST['status'];
        $approvedBy = $_POST['approved_by'];
    
        // Fetch booking details to include in the notification
        $bookingDetailsStmt = $pdo->prepare("
            SELECT r.resource_name, rb.start_time, rb.end_time, rb.reason, rb.user_id 
            FROM resource_bookings rb
            JOIN resources r ON rb.resource_id = r.resource_id
            WHERE rb.booking_id = :booking_id
        ");
        $bookingDetailsStmt->bindParam(':booking_id', $bookingId);
        $bookingDetailsStmt->execute();
        $bookingDetails = $bookingDetailsStmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$bookingDetails) {
            echo json_encode(['success' => false, 'message' => 'Booking not found!']);
            exit;
        }
    
        $resourceName = $bookingDetails['resource_name'];
        $startTime = $bookingDetails['start_time'];
        $endTime = $bookingDetails['end_time'];
        $reason = $bookingDetails['reason'];
        $userId = $bookingDetails['user_id'];
    
        // Update booking status and approved_by
        $stmt = $pdo->prepare("
            UPDATE resource_bookings 
            SET status = :status, approved_by = :approved_by, updated_at = NOW() 
            WHERE booking_id = :booking_id
        ");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':approved_by', $approvedBy);
        $stmt->bindParam(':booking_id', $bookingId);
        $stmt->execute();
    
        // Insert notification for status update with detailed message
        $notificationTitle = "Booking Status Updated";
        $notificationMessage = "Your booking for the '$resourceName' from $startTime to $endTime has been updated to: $status.";
    
        $notificationStmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, created_at) 
            VALUES (:user_id, :title, :message, NOW())
        ");
        $notificationStmt->bindParam(':user_id', $userId);
        $notificationStmt->bindParam(':title', $notificationTitle);
        $notificationStmt->bindParam(':message', $notificationMessage);
        $notificationStmt->execute();
    
        // Fetch user's email and email preference
        $userStmt = $pdo->prepare("
            SELECT u.email, np.email as email_preference 
            FROM users u
            LEFT JOIN notification_preferences np ON u.user_id = np.user_id
            WHERE u.user_id = :user_id
        ");
        $userStmt->bindParam(':user_id', $userId);
        $userStmt->execute();
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
        if ($user && $user['email_preference'] == 1) {
            // Prepare email details
            $emailSubject = "Booking Status Updated: " . $resourceName;
            $emailHeader = "Booking Status Update Announcement";
            $emailIntro = "Your booking status has been updated. Below are the details:";
            $emailCTA = "For further inquiries, feel free to ";
            $emailFooter = "Thank you for your attention, and we hope to see you there!";
    
            $emailSent = sendResourceBookingEmail(
                $user['email'],
                $resourceName,
                $startTime,
                $endTime,
                $reason,
                $status,
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
    
        if ($status === 'Approved') {
            $resourceStmt = $pdo->prepare("
                UPDATE resources 
                SET availability_status = 'Partially Booked' 
                WHERE resource_id = (SELECT resource_id FROM resource_bookings WHERE booking_id = :booking_id)
            ");
            $resourceStmt->bindParam(':booking_id', $bookingId);
            $resourceStmt->execute();
        }
    
        echo json_encode(['success' => true, 'message' => 'Booking status updated successfully!']);
        exit;
    }

    // Create a new booking
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['resource_id']) || empty($data['user_id']) || empty($data['start_time']) || empty($data['end_time']) || empty($data['approved_by'])) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    // Check for conflicting bookings before inserting
    $conflictCheckStmt = $pdo->prepare("SELECT start_time, end_time 
                                        FROM resource_bookings 
                                        WHERE resource_id = :resource_id 
                                          AND status = 'Approved' 
                                          AND (
                                              (start_time < :end_time AND end_time > :start_time)
                                          )");
    $conflictCheckStmt->bindParam(':resource_id', $data['resource_id']);
    $conflictCheckStmt->bindParam(':start_time', $data['start_time']);
    $conflictCheckStmt->bindParam(':end_time', $data['end_time']);
    $conflictCheckStmt->execute();
    $conflicts = $conflictCheckStmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($conflicts)) {
        $conflictMessages = array_map(function ($conflict) {
            return "Conflicting booking: Start Time - {$conflict['start_time']}, End Time - {$conflict['end_time']}";
        }, $conflicts);

        $errorMessage = "The selected resource has conflicting bookings during the selected time:\n" . implode("\n", $conflictMessages);
        echo json_encode(['success' => false, 'message' => $errorMessage]);
        exit;
    }

    // Insert the new booking if no conflicts
    $stmt = $pdo->prepare("INSERT INTO resource_bookings (
        resource_id, user_id, start_time, end_time, status, requested_at, approved_by, updated_at, reason
    ) VALUES (
        :resource_id, :user_id, :start_time, :end_time, :status, NOW(), :approved_by, NOW(), :reason
    )");
    $stmt->bindParam(':resource_id', $data['resource_id']);
    $stmt->bindParam(':user_id', $data['user_id']);
    $stmt->bindParam(':start_time', $data['start_time']);
    $stmt->bindParam(':end_time', $data['end_time']);
    $stmt->bindParam(':status', $data['status']); // Bind status from the request
    $stmt->bindParam(':approved_by', $data['approved_by']);
    $stmt->bindParam(':reason', $data['reason']);
    $stmt->execute();

    // Fetch resource name to include in the notification
    $resourceStmt = $pdo->prepare("SELECT resource_name FROM resources WHERE resource_id = :resource_id");
    $resourceStmt->bindParam(':resource_id', $data['resource_id']);
    $resourceStmt->execute();
    $resourceName = $resourceStmt->fetchColumn();

    // Insert notification for new booking with detailed message
    $notificationTitle = "New Booking Created";
    $notificationMessage = "Your booking for the '$resourceName' from {$data['start_time']} to {$data['end_time']} has been created successfully.";

    $notificationStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, created_at) 
                                       VALUES (:user_id, :title, :message, NOW())");
    $notificationStmt->bindParam(':user_id', $data['user_id']);
    $notificationStmt->bindParam(':title', $notificationTitle);
    $notificationStmt->bindParam(':message', $notificationMessage);
    $notificationStmt->execute();

    echo json_encode(['success' => true, 'message' => 'Resource booking created successfully!']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function sendResourceBookingEmail($recipientEmail, $resource_name, $start_time, $end_time, $reason, $booking_status, $emailSubject, $emailHeader, $emailIntro, $emailCTA, $emailFooter) {
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

        $mail->setFrom('sdpprojectgroup04@gmail.com', 'Resource Booking Announcement');
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
                .booking-details {
                    margin: 20px 0;
                    padding: 15px;
                    background-color: #f9f9f9;
                    border: 1px solid #eeeeee;
                    border-radius: 8px;
                    line-height: 1.6;
                }
                .booking-details p {
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
                    <p>Dear User,</p>
                    <p>' . htmlspecialchars($emailIntro) . '</p>
                    <div class="booking-details">
                        <p><strong>Resource:</strong> ' . $resource_name . '</p>
                        <p><strong>Start Time:</strong> ' . $start_time . '</p>
                        <p><strong>End Time:</strong> ' . $end_time . '</p>
                        <p><strong>Reason:</strong> ' . $reason . '</p>
                        <p><strong>Status:</strong> ' . $booking_status . '</p>
                    </div>
                    <p>' . htmlspecialchars($emailCTA) . '<a href="https://wa.me/+94772957834">contact support</a>.</p>
                </div>
                <div class="footer">
                    <p>' . htmlspecialchars($emailFooter) . '</p>
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