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

    if ($action === 'add_event') {
        // Determine if the event is recurring
        $is_recurring = ($_POST['recurrence_pattern'] === 'Never') ? 0 : 1;

        // Add a new event
        $stmt = $pdo->prepare("INSERT INTO events (
            title, description, category, event_date, start_time, end_time, venue, user_id, max_participants, status, is_recurring, recurrence_pattern, created_at, updated_at
        ) VALUES (
            :title, :description, :category, :event_date, :start_time, :end_time, :venue, :user_id, :max_participants, :status, :is_recurring, :recurrence_pattern, NOW(), NOW()
        )");
        $stmt->bindParam(':title', $_POST['title']);
        $stmt->bindParam(':description', $_POST['description']);
        $stmt->bindParam(':category', $_POST['category']);
        $stmt->bindParam(':event_date', $_POST['event_date']);
        $stmt->bindParam(':start_time', $_POST['start_time']);
        $stmt->bindParam(':end_time', $_POST['end_time']);
        $stmt->bindParam(':venue', $_POST['venue']);
        $stmt->bindParam(':user_id', $_POST['user_id']);
        $stmt->bindParam(':max_participants', $_POST['max_participants']);
        $stmt->bindParam(':status', $_POST['status']);
        $stmt->bindParam(':is_recurring', $is_recurring);
        $stmt->bindParam(':recurrence_pattern', $_POST['recurrence_pattern']);
        $stmt->execute();

        // Get the ID of the newly inserted event
        $event_id = $pdo->lastInsertId();

        // Handle file uploads
        if (!empty($_FILES['eventFiles'])) {
            $uploadDir = __DIR__ . '/../Documents/Events/';

            // Ensure the directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['eventFiles']['tmp_name'] as $key => $tmp_name) {
                $original_file_name = $_FILES['eventFiles']['name'][$key];
                $file_tmp = $_FILES['eventFiles']['tmp_name'][$key];

                // Extract file extension
                $file_extension = pathinfo($original_file_name, PATHINFO_EXTENSION);

                // Generate a timestamp-based file name
                $timestamp = date('m-d-Y--H-i-s'); // Format: MM-DD-YYYY--HH-MM-SS
                $generated_file_name = $timestamp . '--' . pathinfo($original_file_name, PATHINFO_FILENAME) . '.' . $file_extension;

                // Define the relative file path
                $relative_file_path = 'Documents/Events/' . $generated_file_name;

                // Full file path for saving the file
                $file_path = $uploadDir . $generated_file_name;

                // Move the uploaded file to the target directory
                if (move_uploaded_file($file_tmp, $file_path)) {
                    // Insert record into event_resources table
                    $stmt = $pdo->prepare("INSERT INTO event_resources (event_id, file_name, file_path, uploaded_at) VALUES (:event_id, :file_name, :file_path, NOW())");
                    $stmt->bindParam(':event_id', $event_id);
                    $stmt->bindParam(':file_name', $generated_file_name);
                    $stmt->bindParam(':file_path', $relative_file_path);
                    $stmt->execute();
                }
            }
        }

        $stmt = $pdo->prepare("SELECT u.user_id, u.email, np.email as email_preference 
                               FROM users u
                               LEFT JOIN notification_preferences np ON u.user_id = np.user_id
                               WHERE u.role = 'Student' AND u.status = 'Active'");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $notification_title = "New Event: " . $_POST['title'];
        $notification_message = "A new event has been scheduled: " . $_POST['description'];

        foreach ($students as $student) {
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, created_at) VALUES (:user_id, :title, :message, NOW())");
            $stmt->bindParam(':user_id', $student['user_id']);
            $stmt->bindParam(':title', $notification_title);
            $stmt->bindParam(':message', $notification_message);
            $stmt->execute();
        }

        $recipientEmails = [];
        foreach ($students as $student) {
            if ($student['email_preference'] == 1) {
                $recipientEmails[] = $student['email'];
            }
        }

        if (!empty($recipientEmails)) {
            $emailSubject = "New Event Announcement: " . $_POST['title'];
            $emailHeader = "New Event Announcement";
            $emailIntro = "We are excited to announce a new event that has been scheduled. Below are the details of the event:";
            $emailCTA = "We encourage you to participate in this event as it will be a great opportunity to learn and connect with others. For further inquiries, feel free to ";
            $emailFooter = "Thank you for your attention, and we hope to see you there!";

            $emailSent = sendEventEmail($recipientEmails, $_POST['title'], $_POST['description'], $_POST['event_date'], $_POST['start_time'], $_POST['end_time'], $_POST['venue'], $_POST['category'], $_POST['max_participants'], $emailSubject, $emailHeader, $emailIntro, $emailCTA, $emailFooter);
            if ($emailSent) {
                error_log("Email sent successfully to all recipients.");
            } else {
                error_log("Failed to send email to all recipients.");
            }
        }

        echo json_encode(['success' => true, 'message' => 'Event added successfully!']);
    } elseif ($action === 'update_event') {
        // Update an existing event
        $is_recurring = ($_POST['recurrence_pattern'] === 'Never') ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE events SET 
            title = :title,
            description = :description,
            category = :category,
            event_date = :event_date,
            start_time = :start_time,
            end_time = :end_time,
            venue = :venue,
            user_id = :user_id,
            max_participants = :max_participants,
            status = :status,
            is_recurring = :is_recurring,
            recurrence_pattern = :recurrence_pattern,
            updated_at = NOW()
        WHERE event_id = :event_id");
        $stmt->bindParam(':event_id', $_POST['event_id']);
        $stmt->bindParam(':title', $_POST['title']);
        $stmt->bindParam(':description', $_POST['description']);
        $stmt->bindParam(':category', $_POST['category']);
        $stmt->bindParam(':event_date', $_POST['event_date']);
        $stmt->bindParam(':start_time', $_POST['start_time']);
        $stmt->bindParam(':end_time', $_POST['end_time']);
        $stmt->bindParam(':venue', $_POST['venue']);
        $stmt->bindParam(':user_id', $_POST['user_id']);
        $stmt->bindParam(':max_participants', $_POST['max_participants']);
        $stmt->bindParam(':status', $_POST['status']);
        $stmt->bindParam(':is_recurring', $is_recurring);
        $stmt->bindParam(':recurrence_pattern', $_POST['recurrence_pattern']);
        $stmt->execute();

        $stmt = $pdo->prepare("SELECT u.user_id, u.email, np.email as email_preference 
                               FROM users u
                               LEFT JOIN notification_preferences np ON u.user_id = np.user_id
                               WHERE u.role = 'Student' AND u.status = 'Active'");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $notification_title = "Event Updated: " . $_POST['title'];
        $notification_message = "An event has been updated: " . $_POST['description'];

        foreach ($students as $student) {
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, created_at) VALUES (:user_id, :title, :message, NOW())");
            $stmt->bindParam(':user_id', $student['user_id']);
            $stmt->bindParam(':title', $notification_title);
            $stmt->bindParam(':message', $notification_message);
            $stmt->execute();
        }

        $recipientEmails = [];
        foreach ($students as $student) {
            if ($student['email_preference'] == 1) {
                $recipientEmails[] = $student['email'];
            }
        }

        if (!empty($recipientEmails)) {
            $emailSubject = "Event Updated: " . $_POST['title'];
            $emailHeader = "Event Update Announcement";
            $emailIntro = "An existing event has been updated. Please review the updated details below:";
            $emailCTA = "If you have any questions or concerns about the changes, feel free to ";
            $emailFooter = "Thank you for your understanding, and we look forward to your participation.";

            $emailSent = sendEventEmail($recipientEmails, $_POST['title'], $_POST['description'], $_POST['event_date'], $_POST['start_time'], $_POST['end_time'], $_POST['venue'], $_POST['category'], $_POST['max_participants'], $emailSubject, $emailHeader, $emailIntro, $emailCTA, $emailFooter);
            if ($emailSent) {
                error_log("Email sent successfully to all recipients.");
            } else {
                error_log("Failed to send email to all recipients.");
            }
        }

        echo json_encode(['success' => true, 'message' => 'Event updated successfully!']);
    } elseif ($action === 'delete_event') {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = :event_id");
        $stmt->bindParam(':event_id', $_POST['event_id']);
        $stmt->execute();
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            echo json_encode(['success' => false, 'message' => 'Event not found!']);
            exit;
        }

        // Store event details in variables
        $event_title = $event['title'];
        $event_description = $event['description'];
        $event_date = $event['event_date'];
        $event_start_time = $event['start_time'];
        $event_end_time = $event['end_time'];
        $event_venue = $event['venue'];
        $event_category = $event['category'];
        $event_max_participants = $event['max_participants'];

        // Delete the event
        $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = :event_id");
        $stmt->bindParam(':event_id', $_POST['event_id']);
        $stmt->execute();

        // Fetch all active students with email preferences
        $stmt = $pdo->prepare("SELECT u.user_id, u.email, np.email as email_preference 
                            FROM users u
                            LEFT JOIN notification_preferences np ON u.user_id = np.user_id
                            WHERE u.role = 'Student' AND u.status = 'Active'");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare the notification message
        $notification_title = "Event Cancelled: " . $event_title;
        $notification_message = "An event has been cancelled: " . $event_description;

        // Insert a notification for each student
        foreach ($students as $student) {
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, created_at) VALUES (:user_id, :title, :message, NOW())");
            $stmt->bindParam(':user_id', $student['user_id']);
            $stmt->bindParam(':title', $notification_title);
            $stmt->bindParam(':message', $notification_message);
            $stmt->execute();
        }

        // Collect email addresses of users who have opted in for email notifications
        $recipientEmails = [];
        foreach ($students as $student) {
            if ($student['email_preference'] == 1) {
                $recipientEmails[] = $student['email'];
            }
        }

        if (!empty($recipientEmails)) {
            $emailSubject = "Event Cancelled: " . $event_title;
            $emailHeader = "Event Cancellation Announcement";
            $emailIntro = "We regret to inform you that the following event has been cancelled:";
            $emailCTA = "For further clarification, please feel free to ";
            $emailFooter = "We apologize for any inconvenience caused and appreciate your understanding.";

            $emailSent = sendEventEmail($recipientEmails, $event_title, $event_description, $event_date, $event_start_time, $event_end_time, $event_venue, $event_category, $event_max_participants, $emailSubject, $emailHeader, $emailIntro, $emailCTA, $emailFooter);
            if ($emailSent) {
                error_log("Email sent successfully to all recipients.");
            } else {
                error_log("Failed to send email to all recipients.");
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Event deleted successfully!']);
    } elseif ($action === 'fetch_events') {
        if (isset($_POST['event_id'])) {
            $stmt = $pdo->prepare("
                SELECT e.*, u.name AS organizer_name 
                FROM events e
                LEFT JOIN users u ON e.user_id = u.user_id
                WHERE e.event_id = :event_id
            ");
            $stmt->bindParam(':event_id', $_POST['event_id']);
            $stmt->execute();
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            $event_id = $_POST['event_id'];
            $file_stmt = $pdo->prepare("SELECT file_name, file_path FROM event_resources WHERE event_id = :event_id");
            $file_stmt->bindParam(':event_id', $event_id);
            $file_stmt->execute();
            $files = $file_stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if (!empty($files)) {
                $events[0]['files'] = $files;
            } else {
                $events[0]['files'] = [];
            }
        } else {
            $stmt = $pdo->prepare("
                SELECT e.*, u.name AS organizer_name 
                FROM events e
                LEFT JOIN users u ON e.user_id = u.user_id
            ");
            $stmt->execute();
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        echo json_encode(['success' => true, 'data' => $events]);
    } elseif ($action === 'search_events') {
        // Search events by title with user details
        $searchTerm = '%' . $_POST['search_term'] . '%';
        $stmt = $pdo->prepare("
            SELECT e.*, u.name AS organizer_name 
            FROM events e
            LEFT JOIN users u ON e.user_id = u.user_id
            WHERE e.title LIKE :search_term
        ");
        $stmt->bindParam(':search_term', $searchTerm);
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $events]);
    } elseif ($action === 'fetch_event_categories') {
        $stmt = $pdo->prepare("SELECT category, COUNT(*) as count FROM events GROUP BY category");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $categories]);
    } elseif ($action === 'fetch_event_durations') {
        $stmt = $pdo->prepare("SELECT category, AVG(TIMESTAMPDIFF(HOUR, start_time, end_time)) as avg_duration FROM events GROUP BY category");
        $stmt->execute();
        $durations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $durations]);
    } elseif ($action === 'fetch_participants_over_time') {
        $stmt = $pdo->prepare("SELECT DATE(registered_at) as date, COUNT(*) as count FROM event_registrations GROUP BY DATE(registered_at)");
        $stmt->execute();
        $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $participants]);
    }
    elseif ($action === 'fetch_event') {
        $event_id = $_POST['event_id'] ?? null;
    
        if (empty($event_id)) {
            echo json_encode(['success' => false, 'message' => 'Event ID is required.']);
            exit;
        }
    
        // Fetch event details
        $stmt = $pdo->prepare("
            SELECT * FROM events WHERE event_id = :event_id
        ");
        $stmt->bindParam(':event_id', $event_id);
        $stmt->execute();
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($event) {
            echo json_encode(['success' => true, 'data' => $event]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Event not found.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function sendEventEmail($recipientEmails, $title, $description, $event_date, $start_time, $end_time, $venue, $category, $max_participants, $emailSubject, $emailHeader, $emailIntro, $emailCTA, $emailFooter) {
    
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

        $mail->setFrom('sdpprojectgroup04@gmail.com', 'Event Announcement');

        // Add all recipients
        foreach ($recipientEmails as $email) {
            $mail->addAddress($email);
        }

        // Set email subject
        $mail->Subject = $emailSubject;

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
                    <p>Dear Student,</p>
                    <p>' . htmlspecialchars($emailIntro) . '</p>
                    <div class="event-details">
                        <p><strong>Title:</strong> ' . $title . '</p>
                        <p><strong>Description:</strong> ' . $description . '</p>
                        <p><strong>Date:</strong> ' . $event_date . '</p>
                        <p><strong>Time:</strong> ' . $start_time . ' - ' . $end_time . '</p>
                        <p><strong>Venue:</strong> ' . $venue . '</p>
                        <p><strong>Category:</strong> ' . $category . '</p>
                        <p><strong>Max Participants:</strong> ' . $max_participants . '</p>
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