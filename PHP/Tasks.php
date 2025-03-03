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

    $action = $_POST['action'] ?? null;

    if ($action === 'add_task' || $action === 'update_task') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $task_type = $_POST['type'] ?? '';
        $course_id = $_POST['course'] ?? '';
        $created_by = $_POST['created_by'] ?? '';
        $deadline = $_POST['deadline'] ?? '';
        $file_path = '-';
    
        // Check if a file is uploaded
        if (!empty($_FILES['task_file']['name'][0])) {
            $uploadDir = __DIR__ . '/../Documents/Tasks/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
    
            // Loop through each uploaded file
            foreach ($_FILES['task_file']['tmp_name'] as $key => $tmp_name) {
                $fileName = date('m-d-Y--H-i-s') . '--' . basename($_FILES['task_file']['name'][$key]);
                $filePath = $uploadDir . $fileName;
    
                if (move_uploaded_file($tmp_name, $filePath)) {
                    $file_path = 'Documents/Tasks/' . $fileName;
                } else {
                    echo json_encode(['success' => false, 'message' => 'File upload failed.']);
                    exit;
                }
            }
        }

        if ($action === 'add_task') {
            // Check if a task with the same title and course_id already exists
            $stmt = $pdo->prepare("SELECT * FROM tasks WHERE title = :title AND course_id = :course_id");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':course_id', $course_id);
            $stmt->execute();
            $existingTask = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingTask) {
                // If task exists, ask user if they want to update it
                echo json_encode(['success' => false, 'message' => 'A task with the same title and course already exists.', 'existing_task' => $existingTask]);
                exit;
            } else {
                // Insert new task
                $stmt = $pdo->prepare("
                    INSERT INTO tasks (
                        title, description, task_type, file_path, course_id, created_by, deadline, status, created_at, updated_at
                    ) VALUES (
                        :title, :description, :task_type, :file_path, :course_id, :created_by, :deadline, 'Open', NOW(), NOW()
                    )
                ");

                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':task_type', $task_type);
                $stmt->bindParam(':file_path', $file_path);
                $stmt->bindParam(':course_id', $course_id);
                $stmt->bindParam(':created_by', $created_by);
                $stmt->bindParam(':deadline', $deadline);
                $stmt->execute();

                echo json_encode(['success' => true, 'message' => 'Task added successfully!']);
            }
        } elseif ($action === 'update_task') {
            // Update existing task
            $task_id = $_POST['task_id'] ?? null;

            // Update the task
            $stmt = $pdo->prepare("
                UPDATE tasks SET
                    title = :title,
                    description = :description,
                    task_type = :task_type,
                    file_path = :file_path,
                    course_id = :course_id,
                    created_by = :created_by,
                    deadline = :deadline,
                    updated_at = NOW()
                WHERE task_id = :task_id
            ");

            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':task_type', $task_type);
            $stmt->bindParam(':file_path', $file_path);
            $stmt->bindParam(':course_id', $course_id);
            $stmt->bindParam(':created_by', $created_by);
            $stmt->bindParam(':deadline', $deadline);
            $stmt->bindParam(':task_id', $task_id);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => 'Task updated successfully!']);
        }
    } elseif ($action === 'fetch_tasks') {
        // Fetch tasks with optional search
        $search = $_POST['search'] ?? '';
        $sql = "
            SELECT t.*, c.course_name 
            FROM tasks t
            JOIN courses c ON t.course_id = c.course_id
            WHERE 1=1
        ";
    
        // If a search term is provided, add a condition to the SQL query
        if (!empty($search)) {
            $sql .= " AND (t.title LIKE :search OR c.course_name LIKE :search OR t.created_by LIKE :search)";
        }
    
        // Prepare the SQL statement
        $stmt = $pdo->prepare($sql);
    
        // Bind the search parameter if it exists
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';  // Add wildcards for partial matching
            $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
        }
    
        // Execute the query
        $stmt->execute();
    
        // Fetch all tasks that match the criteria
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        echo json_encode(['success' => true, 'data' => $tasks]);
    } elseif ($action === 'delete_task') {
        // Delete a task
        $task_id = $_POST['task_id'] ?? null;
    
        if (empty($task_id)) {
            echo json_encode(['success' => false, 'message' => 'Task ID is required.']);
            exit;
        }
    
        // Delete the task from the database
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE task_id = :task_id");
        $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
        $stmt->execute();
    
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Task deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Task not found or already deleted.']);
        }
    } elseif ($action === 'fetch_task') {
        // Fetch a single task by task_id
        $task_id = $_POST['task_id'] ?? null;
    
        if (empty($task_id)) {
            echo json_encode(['success' => false, 'message' => 'Task ID is required.']);
            exit;
        }
    
        $stmt = $pdo->prepare("
            SELECT t.*, c.course_name 
            FROM tasks t
            JOIN courses c ON t.course_id = c.course_id
            WHERE t.task_id = :task_id
        ");
        $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
        $stmt->execute();
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($task) {
            echo json_encode(['success' => true, 'data' => $task]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Task not found.']);
        }
    } elseif ($action === 'fetch_tasks_over_time') {
        $sql = "SELECT DATE(created_at) AS date, COUNT(*) AS count FROM tasks GROUP BY DATE(created_at)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);
    } elseif ($action === 'fetch_task_distribution_by_course') {
        $sql = "SELECT c.course_name, COUNT(*) AS count FROM tasks t JOIN courses c ON t.course_id = c.course_id GROUP BY c.course_name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);
    } elseif ($action === 'fetch_related_tasks') {
        // Fetch tasks for the logged-in user based on their enrolled courses
        $user_id = $_POST['user_id'] ?? null;
        $search = $_POST['search'] ?? '';

        if (empty($user_id)) {
            echo json_encode(['success' => false, 'message' => 'User ID is required.']);
            exit;
        }

        $sql = "
            SELECT t.*, c.course_name 
            FROM tasks t
            JOIN courses c ON t.course_id = c.course_id
            JOIN course_enrollment ce ON t.course_id = ce.course_id
            WHERE ce.student_id = :user_id
        ";

        // If a search term is provided, add a condition to the SQL query
        if (!empty($search)) {
            $sql .= " AND (t.title LIKE :search OR c.course_name LIKE :search OR t.created_by LIKE :search)";
        }

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
        }

        $stmt->execute();

        // Fetch all tasks that match the criteria
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $tasks]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}