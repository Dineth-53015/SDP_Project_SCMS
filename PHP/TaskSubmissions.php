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

    if ($action === 'fetch_submissions') {
        // Fetch submissions with optional search and filter
        $search = $_POST['search'] ?? '';
        $filter = $_POST['filter'] ?? '';

        $sql = "
            SELECT ts.*, t.title AS task_title, u.name AS student_name, c.course_name, g.name AS graded_by_name
            FROM task_submissions ts
            JOIN tasks t ON ts.task_id = t.task_id
            JOIN users u ON ts.student_id = u.user_id
            JOIN courses c ON t.course_id = c.course_id
            LEFT JOIN users g ON ts.graded_by = g.user_id
            WHERE 1=1
        ";

        // If a search term is provided, add a condition to the SQL query
        if (!empty($search)) {
            $sql .= " AND (t.title LIKE :search OR u.name LIKE :search OR c.course_name LIKE :search)";
        }

        // If a filter is provided, add a condition to the SQL query
        if (!empty($filter)) {
            $sql .= " AND ts.grade = :filter";
        }

        $stmt = $pdo->prepare($sql);

        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
        }

        if (!empty($filter) && $filter !== 'Pending') {
            $stmt->bindParam(':filter', $filter, PDO::PARAM_STR);
        }

        $stmt->execute();

        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $submissions]);
    } elseif ($action === 'fetch_submission') {
        $submissionId = $_POST['submission_id'] ?? null;
    
        if ($submissionId) {
            $sql = "
                SELECT ts.*, t.title AS task_title, u.name AS student_name, c.course_name, g.name AS graded_by_name
                FROM task_submissions ts
                JOIN tasks t ON ts.task_id = t.task_id
                JOIN users u ON ts.student_id = u.user_id
                JOIN courses c ON t.course_id = c.course_id
                LEFT JOIN users g ON ts.graded_by = g.user_id
                WHERE ts.submission_id = :submission_id
            ";
    
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':submission_id', $submissionId, PDO::PARAM_INT);
            $stmt->execute();
    
            $submission = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($submission) {
                echo json_encode(['success' => true, 'data' => $submission]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Submission not found.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid submission ID.']);
        }
    } elseif ($action === 'update_grade') {
        $submissionId = $_POST['submission_id'] ?? null;
        $grade = $_POST['grade'] ?? null;
        $gradedById = $_POST['graded_by'] ?? null;

        if ($submissionId && $grade && $gradedById) {
            $sql = "
                UPDATE task_submissions
                SET grade = :grade, graded_by = :graded_by
                WHERE submission_id = :submission_id
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':grade', $grade, PDO::PARAM_STR);
            $stmt->bindParam(':graded_by', $gradedById, PDO::PARAM_INT);
            $stmt->bindParam(':submission_id', $submissionId, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['success' => true]);
        } //else {
            //echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
        //}
    } elseif ($action === 'fetch_submissions_over_time') {
        $sql = "SELECT DATE(submitted_at) AS date, COUNT(*) AS count FROM task_submissions GROUP BY DATE(submitted_at)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);
    } elseif ($action === 'fetch_submission_grade_distribution') {
        $sql = "SELECT COALESCE(grade, 'Not Graded') AS grade, COUNT(*) AS count FROM task_submissions GROUP BY grade";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);
    } elseif ($action === 'fetch_submissions_by_task') {
        $sql = "SELECT t.title AS task_title, COUNT(*) AS count FROM task_submissions ts JOIN tasks t ON ts.task_id = t.task_id GROUP BY t.title";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);
    } elseif ($action === 'check_submission') {
        $taskID = $_POST['task_id'] ?? null;
        $studentID = $_POST['student_id'] ?? null;

        if ($taskID && $studentID) {
            // Check if the student has already submitted for the task
            $sql = "
                SELECT COUNT(*) AS submission_count
                FROM task_submissions
                WHERE task_id = :task_id AND student_id = :student_id
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':task_id', $taskID, PDO::PARAM_INT);
            $stmt->bindParam(':student_id', $studentID, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['submission_count'] > 0) {
                echo json_encode(['success' => true, 'submitted' => true]);
            } else {
                echo json_encode(['success' => true, 'submitted' => false]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid task ID or student ID.']);
        }
    } elseif ($action === 'submit_task') {
        $taskID = $_POST['task_id'] ?? null;
        $studentID = $_POST['student_id'] ?? null;
        $files = $_FILES['files'] ?? null;

        if ($taskID && $studentID && $files) {
            $uploadDir = __DIR__ . '/../Documents/Task Submission/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $filePaths = [];

            // Process each file
            foreach ($files['name'] as $index => $name) {
                $fileTmpName = $files['tmp_name'][$index];
                $fileExtension = pathinfo($name, PATHINFO_EXTENSION);

                // Generate the new file name
                $currentDateTime = date('m-d-Y--H-i-s');
                $newFileName = "{$currentDateTime}--{$studentID}--{$name}";
                $filePath = $uploadDir . $newFileName;

                // Move the uploaded file to the target directory
                if (move_uploaded_file($fileTmpName, $filePath)) {
                    $filePaths[] = "Documents/Task Submission/{$newFileName}";
                } else {
                    throw new Exception("Failed to upload file: {$name}");
                }
            }

            // Combine multiple file paths into a single string
            $submissionPath = implode(', ', $filePaths);

            // Insert or update the submission in the database
            $sql = "
                INSERT INTO task_submissions (task_id, student_id, submission, submitted_at, grade, graded_by)
                VALUES (:task_id, :student_id, :submission, NOW(), 'Not Graded', 0)
                ON DUPLICATE KEY UPDATE
                    submission = VALUES(submission),
                    submitted_at = NOW(),
                    grade = 'Not Graded',
                    graded_by = 0
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':task_id', $taskID, PDO::PARAM_INT);
            $stmt->bindParam(':student_id', $studentID, PDO::PARAM_INT);
            $stmt->bindParam(':submission', $submissionPath, PDO::PARAM_STR);
            $stmt->execute();

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}