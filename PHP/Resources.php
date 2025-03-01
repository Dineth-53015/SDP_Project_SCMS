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

    if ($action === 'add') {
        // Add a new resource
        $stmt = $pdo->prepare("INSERT INTO resources (
            resource_name, category, location, capacity, availability_status, features, added_by, created_at, updated_at
        ) VALUES (:resource_name, :category, :location, :capacity, :availability_status, :features, :added_by, NOW(), NOW())");
        $stmt->bindParam(':resource_name', $data['resource_name']);
        $stmt->bindParam(':category', $data['category']);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':capacity', $data['capacity']);
        $stmt->bindParam(':availability_status', $data['availability_status']);
        $stmt->bindParam(':features', $data['features']);
        $stmt->bindParam(':added_by', $data['added_by']);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Resource added successfully!']);

    } elseif ($action === 'update') {
        // Update an existing resource
        $stmt = $pdo->prepare("UPDATE resources SET 
            resource_name = :resource_name,
            category = :category,
            location = :location,
            capacity = :capacity,
            availability_status = :availability_status,
            features = :features,
            added_by = :added_by,
            updated_at = NOW()
        WHERE resource_id = :resource_id");
        $stmt->bindParam(':resource_id', $data['resource_id']);
        $stmt->bindParam(':resource_name', $data['resource_name']);
        $stmt->bindParam(':category', $data['category']);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':capacity', $data['capacity']);
        $stmt->bindParam(':availability_status', $data['availability_status']);
        $stmt->bindParam(':features', $data['features']);
        $stmt->bindParam(':added_by', $data['added_by']);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Resource updated successfully!']);

    } elseif ($action === 'delete') {
        // Delete a resource
        $stmt = $pdo->prepare("DELETE FROM resources WHERE resource_id = :resource_id");
        $stmt->bindParam(':resource_id', $data['resource_id']);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Resource deleted successfully!']);

    } elseif ($action === 'fetch') {
        // Fetch all resources or a single resource by resource_id
        if (isset($data['resource_id'])) {
            $stmt = $pdo->prepare("SELECT * FROM resources WHERE resource_id = :resource_id");
            $stmt->bindParam(':resource_id', $data['resource_id']);
            $stmt->execute();
            $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Fetch ALL resources
            $stmt = $pdo->prepare("SELECT * FROM resources");
            $stmt->execute();
            $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        echo json_encode(['success' => true, 'data' => $resources]);

    } elseif ($action === 'search') {
        // Search resources by name
        $searchTerm = '%' . $data['search_term'] . '%';
        $stmt = $pdo->prepare("SELECT * FROM resources WHERE resource_name LIKE :search_term");
        $stmt->bindParam(':search_term', $searchTerm);
        $stmt->execute();
        $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $resources]);

    } elseif ($action === 'get_resource_distribution_by_location') {
        // Fetch resource distribution by location
        $stmt = $pdo->prepare("SELECT location, COUNT(*) AS count FROM resources GROUP BY location");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($action === 'get_resource_capacity_overview') {
        // Fetch total capacity grouped by location for a line chart
        $stmt = $pdo->prepare("SELECT location, SUM(capacity) AS total_capacity FROM resources GROUP BY location");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); // Use fetchAll to get all rows
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($action === 'get_resource_distribution_by_category') {
        // Fetch resource distribution by category
        $stmt = $pdo->prepare("SELECT category, COUNT(*) AS count FROM resources GROUP BY category");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($action === 'get_availability_status') {
        // Fetch availability status distribution
        $stmt = $pdo->prepare("SELECT availability_status, COUNT(*) AS count FROM resources GROUP BY availability_status");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($action === 'fetch_rooms') {
        // Fetch only Lecture Rooms, Labs, and Conference Rooms (new functionality)
        $stmt = $pdo->prepare("SELECT resource_id, resource_name FROM resources WHERE category IN ('Lecture Room', 'Lab', 'Conference Room')");
        $stmt->execute();
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $rooms]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>