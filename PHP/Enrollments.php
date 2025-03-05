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
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? null;

    if ($action === 'enroll') {
        // Validate input
        $course_id = $data['course_id'] ?? null;
        $student_id = $data['student_id'] ?? null;

        if (empty($course_id)) {
            echo json_encode(['success' => false, 'message' => 'Course ID is required.']);
            exit;
        }
        if (empty($student_id)) {
            echo json_encode(['success' => false, 'message' => 'Student ID is required.']);
            exit;
        }

        // Check if the student is already enrolled in the course
        $check_stmt = $pdo->prepare("
            SELECT status FROM course_enrollment 
            WHERE course_id = :course_id AND student_id = :student_id
        ");
        $check_stmt->bindParam(':course_id', $course_id);
        $check_stmt->bindParam(':student_id', $student_id);
        $check_stmt->execute();

        $existing_enrollment = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_enrollment) {
            // Student is already enrolled, return the status
            $status = $existing_enrollment['status'];
            $message = "The selected student is already enrolled in this course with status: {$status}.";
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }

        // Insert the enrollment into the database
        $stmt = $pdo->prepare("
            INSERT INTO course_enrollment (course_id, student_id, enrollment_date, status)
            VALUES (:course_id, :student_id, NOW(), 'Active')
        ");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Enrollment successful!']);

    } elseif ($action === 'fetch_active_enrollments') {
        // Fetch active enrollments
        $search = $data['search'] ?? '';

        $sql = "
            SELECT ce.course_id, ce.student_id, ce.enrollment_date, ce.status, c.course_name, u.name 
            FROM course_enrollment ce
            JOIN courses c ON ce.course_id = c.course_id
            JOIN users u ON ce.student_id = u.user_id
            WHERE ce.status = 'Active'
        ";

        if (!empty($search)) {
            $sql .= " AND (u.name LIKE :search OR c.course_name LIKE :search)";
        }

        $stmt = $pdo->prepare($sql);

        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $stmt->bindParam(':search', $searchTerm);
        }

        $stmt->execute();
        $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $enrollments]);

    } elseif ($action === 'drop_enrollment') {
        // Validate input
        $course_id = $data['course_id'] ?? null;
        $student_id = $data['student_id'] ?? null;
    
        if (empty($course_id)) {
            echo json_encode(['success' => false, 'message' => 'Course ID is required.']);
            exit;
        }
        if (empty($student_id)) {
            echo json_encode(['success' => false, 'message' => 'Student ID is required.']);
            exit;
        }
    
        // Fetch course details
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$course) {
            echo json_encode(['success' => false, 'message' => 'Course not found!']);
            exit;
        }
    
        // Update the enrollment status to 'Dropped'
        $stmt = $pdo->prepare("
            UPDATE course_enrollment 
            SET status = 'Dropped'
            WHERE course_id = :course_id AND student_id = :student_id
        ");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
    
        // Prepare notification details
        $notification_title = "Enrollment Dropped: " . $course['course_name'];
        $notification_message = "Your enrollment for the course '{$course['course_name']}' has been dropped.";
    
        // Insert notification for the student
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, created_at) VALUES (:user_id, :title, :message, NOW())");
        $stmt->bindParam(':user_id', $student_id);
        $stmt->bindParam(':title', $notification_title);
        $stmt->bindParam(':message', $notification_message);
        $stmt->execute();
    
        // Fetch student's email and email preference
        $stmt = $pdo->prepare("
            SELECT u.email, np.email as email_preference 
            FROM users u
            LEFT JOIN notification_preferences np ON u.user_id = np.user_id
            WHERE u.user_id = :student_id
        ");
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($student && $student['email_preference'] == 1) {
            // Prepare email details
            $emailSubject = "Enrollment Dropped: " . $course['course_name'];
            $emailHeader = "Enrollment Dropped Announcement";
            $emailIntro = "We regret to inform you that your enrollment for the following course has been dropped:";
            $emailCTA = "For further clarification, please feel free to ";
            $emailFooter = "We apologize for any inconvenience caused and appreciate your understanding.";
    
            $emailSent = sendEnrollmentEmail(
                $student['email'],
                $course['course_name'],
                $course['course_code'],
                'Dropped',
                $course['faculty'],
                $course['description'],
                $emailSubject,
                $emailHeader,
                $emailIntro,
                $emailCTA,
                $emailFooter
            );
    
            if ($emailSent) {
                error_log("Email sent successfully to the student.");
            } else {
                error_log("Failed to send email to the student.");
            }
        }
    
        echo json_encode(['success' => true, 'message' => 'Enrollment dropped successfully!']);
    } elseif ($action === 'fetch_dropped_enrollments') {
        // Fetch active enrollments
        $search = $data['search'] ?? '';

        $sql = "
            SELECT ce.course_id, ce.student_id, ce.enrollment_date, ce.status, c.course_name, u.name 
            FROM course_enrollment ce
            JOIN courses c ON ce.course_id = c.course_id
            JOIN users u ON ce.student_id = u.user_id
            WHERE ce.status = 'Dropped'
        ";

        if (!empty($search)) {
            $sql .= " AND (u.name LIKE :search OR c.course_name LIKE :search)";
        }

        $stmt = $pdo->prepare($sql);

        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $stmt->bindParam(':search', $searchTerm);
        }

        $stmt->execute();
        $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $enrollments]);

    } elseif ($action === 'reactivate_enrollment') {
        // Validate input
        $course_id = $data['course_id'] ?? null;
        $student_id = $data['student_id'] ?? null;
    
        if (empty($course_id)) {
            echo json_encode(['success' => false, 'message' => 'Course ID is required.']);
            exit;
        }
        if (empty($student_id)) {
            echo json_encode(['success' => false, 'message' => 'Student ID is required.']);
            exit;
        }
    
        // Fetch course details
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$course) {
            echo json_encode(['success' => false, 'message' => 'Course not found!']);
            exit;
        }
    
        // Update the enrollment status to 'Active'
        $stmt = $pdo->prepare("
            UPDATE course_enrollment 
            SET status = 'Active'
            WHERE course_id = :course_id AND student_id = :student_id
        ");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
    
        // Prepare notification details
        $notification_title = "Enrollment Activated: " . $course['course_name'];
        $notification_message = "Your enrollment for the course '{$course['course_name']}' has been Activated.";
    
        // Insert notification for the student
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, created_at) VALUES (:user_id, :title, :message, NOW())");
        $stmt->bindParam(':user_id', $student_id);
        $stmt->bindParam(':title', $notification_title);
        $stmt->bindParam(':message', $notification_message);
        $stmt->execute();
    
        // Fetch student's email and email preference
        $stmt = $pdo->prepare("
            SELECT u.email, np.email as email_preference 
            FROM users u
            LEFT JOIN notification_preferences np ON u.user_id = np.user_id
            WHERE u.user_id = :student_id
        ");
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($student && $student['email_preference'] == 1) {
            // Prepare email details
            $emailSubject = "Enrollment Activated: " . $course['course_name'];
            $emailHeader = "Enrollment Activation Announcement";
            $emailIntro = "We are pleased to inform you that your enrollment for the following course has been activated:";
            $emailCTA = "For further inquiries, feel free to ";
            $emailFooter = "Thank you for your attention, and we hope to see you there!";
    
            $emailSent = sendEnrollmentEmail(
                $student['email'],
                $course['course_name'],
                $course['course_code'],
                'Active',
                $course['faculty'],
                $course['description'],
                $emailSubject,
                $emailHeader,
                $emailIntro,
                $emailCTA,
                $emailFooter
            );
    
            if ($emailSent) {
                error_log("Email sent successfully to the student.");
            } else {
                error_log("Failed to send email to the student.");
            }
        }
    
        echo json_encode(['success' => true, 'message' => 'Enrollment activated successfully!']);
    } elseif ($action === 'fetch_pending_enrollments') {
        // Fetch active enrollments
        $search = $data['search'] ?? '';

        $sql = "
            SELECT ce.course_id, ce.student_id, ce.enrollment_date, ce.status, c.course_name, u.name 
            FROM course_enrollment ce
            JOIN courses c ON ce.course_id = c.course_id
            JOIN users u ON ce.student_id = u.user_id
            WHERE ce.status = 'Pending'
        ";

        if (!empty($search)) {
            $sql .= " AND (u.name LIKE :search OR c.course_name LIKE :search)";
        }

        $stmt = $pdo->prepare($sql);

        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $stmt->bindParam(':search', $searchTerm);
        }

        $stmt->execute();
        $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $enrollments]);

    } elseif ($action === 'fetch_active_enrollments_by_course') {
        // Fetch active enrollments grouped by course
        $stmt = $pdo->prepare("
            SELECT c.course_name, COUNT(*) AS count 
            FROM course_enrollment ce
            JOIN courses c ON ce.course_id = c.course_id
            WHERE ce.status = 'Active'
            GROUP BY c.course_name
        ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $result]);

    } elseif ($action === 'fetch_enrollment_status_distribution') {
        // Fetch enrollment status distribution
        $stmt = $pdo->prepare("
            SELECT status, COUNT(*) AS count 
            FROM course_enrollment 
            GROUP BY status
        ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $result]);

    } elseif ($action === 'fetch_enrollments_over_time') {
        // Fetch enrollments over time
        $stmt = $pdo->prepare("
            SELECT DATE(enrollment_date) AS date, COUNT(*) AS count 
            FROM course_enrollment
            GROUP BY DATE(enrollment_date)
        ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $result]);

    } elseif ($action === 'check_enrollment') {
        $course_id = $data['course_id'] ?? null;
        $student_id = $data['student_id'] ?? null;
    
        if (empty($course_id) || empty($student_id)) {
            echo json_encode(['success' => false, 'message' => 'Course ID and Student ID are required.']);
            exit;
        }
    
        // Check if the student is already enrolled in the course
        $check_stmt = $pdo->prepare("
            SELECT status FROM course_enrollment 
            WHERE course_id = :course_id AND student_id = :student_id
        ");
        $check_stmt->bindParam(':course_id', $course_id);
        $check_stmt->bindParam(':student_id', $student_id);
        $check_stmt->execute();
    
        $existing_enrollment = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($existing_enrollment) {
            // Student is already enrolled, return the status
            $status = $existing_enrollment['status'];
            echo json_encode(['success' => true, 'enrolled' => true, 'status' => $status]);
        } else {
            echo json_encode(['success' => true, 'enrolled' => false]);
        }
    } elseif ($action === 'enrollnew') {
        $course_id = $data['course_id'] ?? null;
        $student_id = $data['student_id'] ?? null;
    
        if (empty($course_id)) {
            echo json_encode(['success' => false, 'message' => 'Course ID is required.']);
            exit;
        }
        if (empty($student_id)) {
            echo json_encode(['success' => false, 'message' => 'Student ID is required.']);
            exit;
        }
    
        // Check if the student is already enrolled in the course
        $check_stmt = $pdo->prepare("
            SELECT status FROM course_enrollment 
            WHERE course_id = :course_id AND student_id = :student_id
        ");
        $check_stmt->bindParam(':course_id', $course_id);
        $check_stmt->bindParam(':student_id', $student_id);
        $check_stmt->execute();
    
        $existing_enrollment = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($existing_enrollment) {
            // Student is already enrolled, return the status
            $status = $existing_enrollment['status'];
            $message = "The selected student is already enrolled in this course with status: {$status}.";
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }
    
        // Insert the enrollment into the database
        $stmt = $pdo->prepare("
            INSERT INTO course_enrollment (course_id, student_id, enrollment_date, status)
            VALUES (:course_id, :student_id, NOW(), 'Pending')
        ");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
    
        echo json_encode(['success' => true, 'message' => 'Enrollment successful!']);
    } elseif ($action === 'fetch_enrolled_courses') {
        $student_id = $data['student_id'] ?? null;
    
        if (empty($student_id)) {
            echo json_encode(['success' => false, 'message' => 'Student ID is required.']);
            exit;
        }
    
        // Fetch enrolled courses for the student
        $stmt = $pdo->prepare("
            SELECT c.course_id, c.course_code, c.course_name, c.description, c.faculty, ce.enrollment_date, ce.status
            FROM course_enrollment ce
            JOIN courses c ON ce.course_id = c.course_id
            WHERE ce.student_id = :student_id
        ");
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        $enrolled_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        echo json_encode(['success' => true, 'data' => $enrolled_courses]);
    } else {
        // Invalid action
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
    }
} catch (PDOException $e) {
    // Return error response
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function sendEnrollmentEmail($recipientEmail, $course_name, $course_code, $course_status, $faculty, $course_description, $emailSubject, $emailHeader, $emailIntro, $emailCTA, $emailFooter) {
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

        $mail->setFrom('sdpprojectgroup04@gmail.com', 'Enrollment Announcement');
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
                .course-details {
                    margin: 20px 0;
                    padding: 15px;
                    background-color: #f9f9f9;
                    border: 1px solid #eeeeee;
                    border-radius: 8px;
                    line-height: 1.6;
                }
                .course-details p {
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
                    <div class="course-details">
                        <p><strong>Course Name:</strong> ' . $course_name . '</p>
                        <p><strong>Course Code:</strong> ' . $course_code . '</p>
                        <p><strong>Status:</strong> ' . $course_status . '</p>
                        <p><strong>Faculty:</strong> ' . $faculty . '</p>
                        <p><strong>Description:</strong> ' . $course_description . '</p>
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