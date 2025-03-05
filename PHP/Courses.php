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
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? null;

    if ($action === 'fetch') {
        // Fetch all courses
        $stmt = $pdo->prepare("SELECT * FROM courses");
        $stmt->execute();
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $courses]);
    } elseif ($action === 'add_course') {
        // Add or update a course
        $course_code = $data['course_code'] ?? null;
        $course_name = $data['course_name'] ?? null;
        $description = $data['description'] ?? null;
        $faculty = $data['faculty'] ?? null;

        // Validate input
        if (empty($course_code)) {
            echo json_encode(['success' => false, 'message' => 'Course code is required.']);
            exit;
        }
        if (empty($course_name)) {
            echo json_encode(['success' => false, 'message' => 'Course name is required.']);
            exit;
        }
        if (empty($description)) {
            echo json_encode(['success' => false, 'message' => 'Description is required.']);
            exit;
        }
        if (empty($faculty)) {
            echo json_encode(['success' => false, 'message' => 'Faculty is required.']);
            exit;
        }

        // Check if the course_code already exists
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE course_code = :course_code");
        $stmt->bindParam(':course_code', $course_code);
        $stmt->execute();
        $existing_course = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_course) {
            // Course code already exists, prompt user to confirm update
            echo json_encode([
                'success' => false,
                'message' => 'Course code already exists. Do you want to update the existing record?',
                'existing_course' => $existing_course
            ]);
        } else {
            // Insert the new course into the database
            $stmt = $pdo->prepare("
                INSERT INTO courses (course_code, course_name, description, faculty, created_at)
                VALUES (:course_code, :course_name, :description, :faculty, NOW())
            ");
            $stmt->bindParam(':course_code', $course_code);
            $stmt->bindParam(':course_name', $course_name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':faculty', $faculty);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => 'Course added successfully!']);
        }
    } elseif ($action === 'update_course') {
        // Update an existing course
        $course_code = $data['course_code'] ?? null;
        $course_name = $data['course_name'] ?? null;
        $description = $data['description'] ?? null;
        $faculty = $data['faculty'] ?? null;

        // Validate input
        if (empty($course_code)) {
            echo json_encode(['success' => false, 'message' => 'Course code is required.']);
            exit;
        }
        if (empty($course_name)) {
            echo json_encode(['success' => false, 'message' => 'Course name is required.']);
            exit;
        }
        if (empty($description)) {
            echo json_encode(['success' => false, 'message' => 'Description is required.']);
            exit;
        }
        if (empty($faculty)) {
            echo json_encode(['success' => false, 'message' => 'Faculty is required.']);
            exit;
        }

        // Update the existing course in the database
        $stmt = $pdo->prepare("
            UPDATE courses
            SET course_name = :course_name, description = :description, faculty = :faculty
            WHERE course_code = :course_code
        ");
        $stmt->bindParam(':course_code', $course_code);
        $stmt->bindParam(':course_name', $course_name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':faculty', $faculty);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Course updated successfully!']);
    } elseif ($action === 'delete_course') {
        // Delete a course
        $course_id = $data['course_id'] ?? null;

        if (empty($course_id)) {
            echo json_encode(['success' => false, 'message' => 'Course ID is required.']);
            exit;
        }

        // Delete the course from the database
        $stmt = $pdo->prepare("DELETE FROM courses WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Course deleted successfully!']);
    } elseif ($action === 'fetch_course_by_id') {
        // Fetch a specific course by ID
        $course_id = $data['course_id'] ?? null;

        if (empty($course_id)) {
            echo json_encode(['success' => false, 'message' => 'Course ID is required.']);
            exit;
        }

        // Fetch the course details
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($course) {
            echo json_encode(['success' => true, 'data' => $course]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Course not found.']);
        }
    } elseif ($action === 'fetch_courses_over_time') {
        // Fetch courses created over time
        $stmt = $pdo->prepare("
            SELECT DATE(created_at) AS date, COUNT(*) AS count 
            FROM courses 
            GROUP BY DATE(created_at)
        ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $result]);

    } elseif ($action === 'fetch_course_distribution_by_faculty') {
        // Fetch course distribution by faculty
        $stmt = $pdo->prepare("
            SELECT faculty, COUNT(*) AS count 
            FROM courses 
            GROUP BY faculty
        ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $result]);

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>