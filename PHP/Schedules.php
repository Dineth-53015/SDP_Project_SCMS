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

    if ($action === 'fetch_schedules_for_calendar') {
        // Calculate the start and end dates for the next 4 weeks
        $currentDate = new DateTime();
        $startDate = $currentDate->format('Y-m-d');
        $endDate = (clone $currentDate)->modify('+4 weeks')->format('Y-m-d');

        // Fetch schedules within the next 4 weeks
        $stmt = $pdo->prepare("
            SELECT 
                TRIM(
                    SUBSTRING_INDEX(SUBSTRING_INDEX(reason, 'Auto-approved: ', -1), ' -', LENGTH(reason) - LENGTH(REPLACE(reason, '-', '')))
                ) AS Title,
                DATE(start_time) AS Date,
                TIME(start_time) AS StartTime,
                TIME(end_time) AS EndTime
            FROM resource_bookings
            WHERE reason LIKE '%Auto-approved:%'
            AND DATE(start_time) BETWEEN :start_date AND :end_date
            ORDER BY Date, StartTime;
        ");
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Organize schedules by date
        $organizedSchedules = [];
        foreach ($schedules as $schedule) {
            $date = $schedule['Date'];
            if (!isset($organizedSchedules[$date])) {
                $organizedSchedules[$date] = [];
            }
            $organizedSchedules[$date][] = $schedule;
        }

        echo json_encode(['success' => true, 'data' => $organizedSchedules]);
    } elseif ($action === 'fetch_schedules') {
        // Fetch all schedules or search by title
        $searchTerm = $_POST['search_term'] ?? '';
        if (!empty($searchTerm)) {
            $searchTerm = '%' . $searchTerm . '%';
            $stmt = $pdo->prepare("
                SELECT s.*, c.course_name AS course_name
                FROM schedules s
                INNER JOIN courses c ON s.course_id = c.course_id
                WHERE s.title LIKE :search_term;
            ");
            $stmt->bindParam(':search_term', $searchTerm);
        } else {
            $stmt = $pdo->prepare("
                SELECT s.*, c.course_name AS course_name 
                FROM schedules s 
                INNER JOIN courses c ON s.course_id = c.course_id
            ");
        }
        $stmt->execute();
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $schedules]);

    } elseif ($action === 'fetch_schedule') {
        // Fetch a single schedule by schedule_id
        $scheduleId = $_POST['schedule_id'] ?? null;
        if (empty($scheduleId)) {
            echo json_encode(['success' => false, 'message' => 'Schedule ID is required.']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT s.*, c.course_name AS course_name, r.resource_name AS room_name , u.name AS user_name
            FROM schedules s
            INNER JOIN courses c ON s.course_id = c.course_id
            LEFT JOIN resources r ON s.room_id = r.resource_id
            LEFT JOIN users u ON s.user_id = u.user_id
            WHERE s.schedule_id = :schedule_id;
        ");
        $stmt->bindParam(':schedule_id', $scheduleId, PDO::PARAM_INT);
        $stmt->execute();
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($schedule) {
            echo json_encode(['success' => true, 'data' => [$schedule]]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Schedule not found.']);
        }

    } elseif ($action === 'delete_schedule') {
        $scheduleId = $_POST['schedule_id'] ?? null;
        if (empty($scheduleId)) {
            echo json_encode(['success' => false, 'message' => 'Schedule ID is required.']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM schedules WHERE schedule_id = :schedule_id");
        $stmt->bindParam(':schedule_id', $scheduleId);
        $stmt->execute();
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$schedule) {
            echo json_encode(['success' => false, 'message' => 'Schedule not found!']);
            exit;
        }

        $reason = "Auto-approved: {$schedule['title']} - {$schedule['schedule_type']}";
        $stmt = $pdo->prepare("DELETE FROM resource_bookings WHERE reason = :reason");
        $stmt->bindParam(':reason', $reason);
        $stmt->execute();

        $stmt = $pdo->prepare("DELETE FROM schedules WHERE schedule_id = :schedule_id");
        $stmt->bindParam(':schedule_id', $scheduleId);
        $stmt->execute();

        $stmt = $pdo->prepare("SELECT course_name FROM courses WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $schedule['course_id']);
        $stmt->execute();
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$course) {
            echo json_encode(['success' => false, 'message' => 'Course not found!']);
            exit;
        }

        $course_name = $course['course_name'];

        $stmt = $pdo->prepare("
            SELECT u.user_id, u.email, np.email as email_preference 
            FROM users u
            LEFT JOIN notification_preferences np ON u.user_id = np.user_id
            INNER JOIN course_enrollment ce ON u.user_id = ce.student_id
            WHERE u.role = 'Student' 
            AND u.status = 'Active'
            AND ce.course_id = :course_id
            AND ce.status = 'Active'
        ");
        $stmt->bindParam(':course_id', $schedule['course_id']);
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($students)) {
            echo json_encode(['success' => false, 'message' => 'No students found for this course!']);
            exit;
        }

        $notification_title = "Schedule Cancelled: " . $schedule['title'];
        $notification_message = "A schedule has been cancelled: " . $schedule['description'];

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
            $emailSubject = "Schedule Cancelled: " . $schedule['title'];
            $emailHeader = "Schedule Cancellation Announcement";
            $emailIntro = "We regret to inform you that the following schedule has been cancelled:";
            $emailCTA = "For further clarification, please feel free to ";
            $emailFooter = "We apologize for any inconvenience caused and appreciate your understanding.";

            $emailSent = sendScheduleEmail(
                $recipientEmails,
                $schedule['title'],
                $schedule['description'],
                $course_name,
                $schedule['schedule_type'],
                $schedule['start_date'],
                $schedule['start_time'],
                $schedule['end_time'],
                $schedule['room_id'],
                $schedule['is_recurring'],
                $schedule['recurrence_pattern'],
                $schedule['recurrence_end_date'],
                $emailSubject,
                $emailHeader,
                $emailIntro,
                $emailCTA,
                $emailFooter
            );

            if ($emailSent) {
                error_log("Email sent successfully to all recipients.");
            } else {
                error_log("Failed to send email to all recipients.");
            }
        }

        echo json_encode(['success' => true, 'message' => 'Schedule deleted successfully!']);

    } elseif ($action === 'add_schedule') {
        // Add Schedule
        $recurrence_pattern = $_POST['recurrence_pattern'];
        $is_recurring = ($recurrence_pattern === 'Never') ? 0 : 1;
        $room_id = $_POST['room_id'];
        $start_date = $_POST['start_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $recurrence_end_date = $_POST['recurrence_end_date'];
        $schedule_type = $_POST['schedule_type'];
        $user_id = $_POST['user_id'];
        $created_by = $_POST['created_by'];
        $status = 'Approved';
        $approved_by = "-";
        $reason = "Auto-approved: {$_POST['title']} - $schedule_type";

        $start_datetime = "$start_date $start_time";
        $end_datetime = "$start_date $end_time";

        $conflictQuery = "SELECT COUNT(*) FROM resource_bookings 
                        WHERE resource_id = :room_id 
                        AND status = 'Approved' 
                        AND (
                            (start_time < :end_datetime AND end_time > :start_datetime) 
                        )";
        $stmt = $pdo->prepare($conflictQuery);
        $stmt->bindParam(':room_id', $room_id);
        $stmt->bindParam(':start_datetime', $start_datetime);
        $stmt->bindParam(':end_datetime', $end_datetime);
        $stmt->execute();
        $conflictCount = $stmt->fetchColumn();

        if ($conflictCount > 0) {
            echo json_encode(['success' => false, 'message' => 'Time conflict detected for the selected room.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO schedules (
            title, description, course_id, user_id, schedule_type, start_date, start_time, end_time, 
            room_id, is_recurring, recurrence_pattern, recurrence_end_date, created_by, created_at, updated_at
        ) VALUES (
            :title, :description, :course_id, :user_id, :schedule_type, :start_date, :start_time, :end_time, 
            :room_id, :is_recurring, :recurrence_pattern, :recurrence_end_date, :created_by, NOW(), NOW()
        )");

        $stmt->bindParam(':title', $_POST['title']);
        $stmt->bindParam(':description', $_POST['description']);
        $stmt->bindParam(':course_id', $_POST['course_id']);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':schedule_type', $schedule_type);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':start_time', $start_time);
        $stmt->bindParam(':end_time', $end_time);
        $stmt->bindParam(':room_id', $room_id);
        $stmt->bindParam(':is_recurring', $is_recurring);
        $stmt->bindParam(':recurrence_pattern', $recurrence_pattern);
        $stmt->bindParam(':recurrence_end_date', $recurrence_end_date);
        $stmt->bindParam(':created_by', $created_by);
        $stmt->execute();
        
        $schedule_id = $pdo->lastInsertId();

        $insertBooking = "INSERT INTO resource_bookings (
            resource_id, user_id, start_time, end_time, reason, status, requested_at, approved_by, updated_at
        ) VALUES (
            :room_id, :user_id, :start_datetime, :end_datetime, :reason, :status, NOW(), :approved_by, NOW()
        )";

        $stmt = $pdo->prepare($insertBooking);
        $stmt->bindParam(':room_id', $room_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':start_datetime', $start_datetime);
        $stmt->bindParam(':end_datetime', $end_datetime);
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':approved_by', $approved_by);
        $stmt->execute();

        if ($is_recurring) {
            $currentDate = new DateTime($start_date);
            $endDate = new DateTime($recurrence_end_date);
            
            $interval = match ($recurrence_pattern) {
                'Daily' => '+1 day',
                'Weekly' => '+1 week',
                'Monthly' => '+1 month',
                default => null
            };

            if ($interval) {
                while ($currentDate <= $endDate) {
                    $recurringStartDate = $currentDate->format('Y-m-d');
                    $recurringStartDatetime = "$recurringStartDate $start_time";
                    $recurringEndDatetime = "$recurringStartDate $end_time";

                    $stmt = $pdo->prepare($conflictQuery);
                    $stmt->bindParam(':room_id', $room_id);
                    $stmt->bindParam(':start_datetime', $recurringStartDatetime);
                    $stmt->bindParam(':end_datetime', $recurringEndDatetime);
                    $stmt->execute();
                    $conflictCount = $stmt->fetchColumn();

                    if ($conflictCount === 0) {
                        $stmt = $pdo->prepare($insertBooking);
                        $stmt->bindParam(':room_id', $room_id);
                        $stmt->bindParam(':user_id', $user_id);
                        $stmt->bindParam(':start_datetime', $recurringStartDatetime);
                        $stmt->bindParam(':end_datetime', $recurringEndDatetime);
                        $stmt->bindParam(':reason', $reason);
                        $stmt->bindParam(':status', $status);
                        $stmt->bindParam(':approved_by', $approved_by);
                        $stmt->execute();
                    }

                    $currentDate->modify($interval);
                }
            }
        }

        $stmt = $pdo->prepare("SELECT course_name FROM courses WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $_POST['course_id']);
        $stmt->execute();
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        $course_name = $course['course_name'];

        $stmt = $pdo->prepare("
            SELECT u.user_id, u.email, np.email as email_preference 
            FROM users u
            LEFT JOIN notification_preferences np ON u.user_id = np.user_id
            INNER JOIN course_enrollment ce ON u.user_id = ce.student_id
            WHERE u.role = 'Student' 
            AND u.status = 'Active'
            AND ce.course_id = :course_id
            AND ce.status = 'Active'
        ");
        $stmt->bindParam(':course_id', $_POST['course_id']);
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $notification_title = "New Schedule: " . $_POST['title'];
        $notification_message = "A new schedule has been added: " . $_POST['description'];

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
            $emailSubject = "New Schedule: " . $_POST['title'];
            $emailHeader = "New Schedule Announcement";
            $emailIntro = "A new schedule has been added. Below are the details of the schedule:";
            $emailCTA = "For further inquiries, feel free to ";
            $emailFooter = "Thank you for your attention, and we hope to see you there!";

            $emailSent = sendScheduleEmail(
                $recipientEmails,
                $_POST['title'],
                $_POST['description'],
                $course_name,
                $_POST['schedule_type'],
                $_POST['start_date'],
                $_POST['start_time'],
                $_POST['end_time'],
                $_POST['room_id'],
                $is_recurring,
                $recurrence_pattern,
                $recurrence_end_date,
                $emailSubject,
                $emailHeader,
                $emailIntro,
                $emailCTA,
                $emailFooter
            );

            if ($emailSent) {
                error_log("Email sent successfully to all recipients.");
            } else {
                error_log("Failed to send email to all recipients.");
            }
        }

        echo json_encode(['success' => true, 'message' => 'Schedule added successfully!']);
    } elseif ($action === 'update_description') {
        // Update Schedule
        $title = $_POST['title'] ?? null;
        $newDescription = $_POST['description'] ?? null;
    
        if (empty($title)) {
            echo json_encode(['success' => false, 'message' => 'Title is required.']);
            exit;
        }
    
        if (empty($newDescription)) {
            echo json_encode(['success' => false, 'message' => 'Description cannot be empty.']);
            exit;
        }
    
        // Fetch the schedule details
        $stmt = $pdo->prepare("SELECT * FROM schedules WHERE title = :title");
        $stmt->bindParam(':title', $title);
        $stmt->execute();
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$schedule) {
            echo json_encode(['success' => false, 'message' => 'Schedule with the given title not found.']);
            exit;
        }
    
        $scheduleId = $schedule['schedule_id'];
    
        // Update the description in the database
        $stmt = $pdo->prepare("UPDATE schedules SET description = :description, updated_at = NOW() WHERE schedule_id = :schedule_id");
        $stmt->bindParam(':description', $newDescription);
        $stmt->bindParam(':schedule_id', $scheduleId, PDO::PARAM_INT);
        $stmt->execute();
    
        if ($stmt->rowCount() > 0) {
            // Fetch the updated schedule details
            $stmt = $pdo->prepare("SELECT * FROM schedules WHERE schedule_id = :schedule_id");
            $stmt->bindParam(':schedule_id', $scheduleId);
            $stmt->execute();
            $updatedSchedule = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // Fetch course details
            $stmt = $pdo->prepare("SELECT course_name FROM courses WHERE course_id = :course_id");
            $stmt->bindParam(':course_id', $updatedSchedule['course_id']);
            $stmt->execute();
            $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$course) {
                echo json_encode(['success' => false, 'message' => 'Course not found!']);
                exit;
            }
    
            $course_name = $course['course_name'];
    
            // Fetch students enrolled in the course
            $stmt = $pdo->prepare("
                SELECT u.user_id, u.email, np.email as email_preference 
                FROM users u
                LEFT JOIN notification_preferences np ON u.user_id = np.user_id
                INNER JOIN course_enrollment ce ON u.user_id = ce.student_id
                WHERE u.role = 'Student' 
                AND u.status = 'Active'
                AND ce.course_id = :course_id
                AND ce.status = 'Active'
            ");
            $stmt->bindParam(':course_id', $updatedSchedule['course_id']);
            $stmt->execute();
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if (empty($students)) {
                echo json_encode(['success' => false, 'message' => 'No students found for this course!']);
                exit;
            }
    
            // Prepare notification details
            $notification_title = "Schedule Updated: " . $updatedSchedule['title'];
            $notification_message = "A schedule has been updated. Updated description: " . $updatedSchedule['description'];
    
            // Insert notifications for each student
            foreach ($students as $student) {
                $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, created_at) VALUES (:user_id, :title, :message, NOW())");
                $stmt->bindParam(':user_id', $student['user_id']);
                $stmt->bindParam(':title', $notification_title);
                $stmt->bindParam(':message', $notification_message);
                $stmt->execute();
            }
    
            // Prepare email details
            $recipientEmails = [];
            foreach ($students as $student) {
                if ($student['email_preference'] == 1) {
                    $recipientEmails[] = $student['email'];
                }
            }
    
            if (!empty($recipientEmails)) {
                $emailSubject = "Schedule Updated: " . $updatedSchedule['title'];
                $emailHeader = "Schedule Update Announcement";
                $emailIntro = "The following schedule has been updated. Below are the updated details:";
                $emailCTA = "For further inquiries, feel free to ";
                $emailFooter = "Thank you for your attention, and we hope to see you there!";
    
                $emailSent = sendScheduleEmail(
                    $recipientEmails,
                    $updatedSchedule['title'],
                    $updatedSchedule['description'],
                    $course_name,
                    $updatedSchedule['schedule_type'],
                    $updatedSchedule['start_date'],
                    $updatedSchedule['start_time'],
                    $updatedSchedule['end_time'],
                    $updatedSchedule['room_id'],
                    $updatedSchedule['is_recurring'],
                    $updatedSchedule['recurrence_pattern'],
                    $updatedSchedule['recurrence_end_date'],
                    $emailSubject,
                    $emailHeader,
                    $emailIntro,
                    $emailCTA,
                    $emailFooter
                );
    
                if ($emailSent) {
                    error_log("Email sent successfully to all recipients.");
                } else {
                    error_log("Failed to send email to all recipients.");
                }
            }
    
            echo json_encode(['success' => true, 'message' => 'Description updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update description.']);
        }
    } elseif ($action === 'fetch_chart_data') {
        $chartType = $_POST['chart_type'] ?? null;

        if ($chartType === 'events_scheduled_per_day') {
            // Fetch data for Events Scheduled Per Day chart
            $stmt = $pdo->prepare("
                SELECT DATE(start_date) as date, COUNT(*) as count 
                FROM schedules 
                GROUP BY DATE(start_date)
                ORDER BY date ASC
            ");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);

        } elseif ($chartType === 'event_duration_by_type') {
            // Fetch data for Event Duration by Schedule Type chart
            $stmt = $pdo->prepare("
                SELECT schedule_type, AVG(TIMESTAMPDIFF(HOUR, start_time, end_time)) as avg_duration 
                FROM schedules 
                GROUP BY schedule_type
            ");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);

        } elseif ($chartType === 'recurring_vs_non_recurring') {
            // Fetch data for Recurring vs Non-Recurring Events chart
            $stmt = $pdo->prepare("
                SELECT is_recurring, COUNT(*) as count 
                FROM schedules 
                GROUP BY is_recurring
            ");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);

        } elseif ($chartType === 'room_utilization') {
            // Fetch data for Room Utilization chart
            $stmt = $pdo->prepare("
                SELECT r.resource_name as room_name, COUNT(s.schedule_id) as count 
                FROM schedules s
                JOIN resources r ON s.room_id = r.resource_id
                GROUP BY r.resource_name
            ");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);

        } elseif ($chartType === 'schedule_types_distribution') {
            // Fetch data for Schedule Types Distribution chart
            $stmt = $pdo->prepare("
                SELECT schedule_type, COUNT(*) as count 
                FROM schedules 
                GROUP BY schedule_type
            ");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);

        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid chart type specified.']);
        }
    } elseif ($action === 'fetch_schedules_for_attendance') {
        $stmt = $pdo->prepare("
            SELECT schedule_id, title 
            FROM schedules
        ");
        $stmt->execute();
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        echo json_encode(['success' => true, 'data' => $schedules]);
    } elseif ($action === 'fetch_related_schedules') {
        $user_id = $_POST['user_id'] ?? null;
    
        if (empty($user_id)) {
            echo json_encode(['success' => false, 'message' => 'User ID is required.']);
            exit;
        }
    
        $sql = "
            SELECT 
                s.*, 
                c.course_name,
                ca.attendance
            FROM schedules s
            JOIN courses c ON s.course_id = c.course_id
            JOIN course_enrollment ce ON s.course_id = ce.course_id
            LEFT JOIN class_attendance ca ON s.schedule_id = ca.schedule_id AND ca.user_id = :user_id
            WHERE ce.student_id = :user_id
            ORDER BY s.start_date DESC
        ";
    
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        echo json_encode(['success' => true, 'data' => $schedules]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function sendScheduleEmail($recipientEmails, $title, $description, $course_name, $schedule_type, $start_date, $start_time, $end_time, $room_id, $is_recurring, $recurrence_pattern, $recurrence_end_date, $emailSubject, $emailHeader, $emailIntro, $emailCTA, $emailFooter) {
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

        $mail->setFrom('sdpprojectgroup04@gmail.com', 'Schedule Announcement');

        foreach ($recipientEmails as $email) {
            $mail->addAddress($email);
        }

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
                .schedule-details {
                    margin: 20px 0;
                    padding: 15px;
                    background-color: #f9f9f9;
                    border: 1px solid #eeeeee;
                    border-radius: 8px;
                    line-height: 1.6;
                }
                .schedule-details p {
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
                    <div class="schedule-details">
                        <p><strong>Title:</strong> ' . $title . '</p>
                        <p><strong>Description:</strong> ' . $description . '</p>
                        <p><strong>Course:</strong> ' . $course_name . '</p>
                        <p><strong>Schedule Type:</strong> ' . $schedule_type . '</p>
                        <p><strong>Start Date:</strong> ' . $start_date . '</p>
                        <p><strong>Time:</strong> ' . $start_time . ' - ' . $end_time . '</p>
                        <p><strong>Room:</strong> ' . $room_id . '</p>
                        <p><strong>Recurring:</strong> ' . ($is_recurring ? 'Yes' : 'No') . '</p>
                        <p><strong>Recurrence Pattern:</strong> ' . $recurrence_pattern . '</p>
                        <p><strong>Recurrence End Date:</strong> ' . $recurrence_end_date . '</p>
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