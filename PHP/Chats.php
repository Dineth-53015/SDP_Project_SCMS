<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

// Database connection
$host = 'localhost';
$dbname = 'SCMS';
$username = 'root';
$password = '';

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Determine if the request contains JSON or form data
    $isJson = strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
    $data = $isJson ? json_decode(file_get_contents('php://input'), true) : $_POST;
    $action = $data['action'] ?? null;

    if ($action === 'fetch_chats') {
        $loggedInUserId = $_SESSION['user_id'];
        $search = $data['search'] ?? '';
    
        // Base query to fetch conversations involving the logged-in user
        $sql = "
            SELECT 
                c.conversation_id, 
                c.type, 
                c.group_name, 
                GROUP_CONCAT(DISTINCT CASE WHEN p.user_id != :logged_in_user_id THEN u.name END) AS other_user_names,
                GROUP_CONCAT(DISTINCT CASE WHEN p.user_id != :logged_in_user_id THEN p.user_id END) AS other_user_ids,
                COUNT(DISTINCT p.user_id) AS participant_count
            FROM participants p 
            JOIN conversations c ON p.conversation_id = c.conversation_id
            LEFT JOIN users u ON p.user_id = u.user_id
            WHERE c.conversation_id IN (
                SELECT conversation_id
                FROM participants
                WHERE user_id = :logged_in_user_id
            )
        ";
    
        // Add search filter if a search term is provided
        if (!empty($search)) {
            $sql .= " AND (
                c.group_name LIKE :search OR 
                (u.name LIKE :search AND p.user_id != :logged_in_user_id) -- Exclude logged-in user's name
            )";
        }
    
        // Group by conversation_id
        $sql .= " GROUP BY c.conversation_id";
    
        // Prepare and execute the query
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':logged_in_user_id', $loggedInUserId);
    
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $stmt->bindParam(':search', $searchTerm);
        }
    
        $stmt->execute();
        $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Process the chats to determine the display name and selected user ID
        $processedChats = [];
        foreach ($chats as $chat) {
            if ($chat['type'] === 'group') {
                // Group chat: Use the group name
                $processedChats[] = [
                    'conversation_id' => $chat['conversation_id'],
                    'name' => $chat['group_name'],
                    'type' => 'group',
                    'selected_user_id' => null
                ];
            } else {
                // Private conversation
                $otherUserNames = $chat['other_user_names'];
                $otherUserIds = explode(',', $chat['other_user_ids']);
    
                // If there are other users in the conversation, use their name
                if (!empty($otherUserNames)) {
                    $processedChats[] = [
                        'conversation_id' => $chat['conversation_id'],
                        'name' => $otherUserNames,
                        'type' => 'private',
                        'selected_user_id' => $otherUserIds[0]
                    ];
                } else {
                    // If no other users are found, show "My Self"
                    $processedChats[] = [
                        'conversation_id' => $chat['conversation_id'],
                        'name' => 'My Self',
                        'type' => 'private',
                        'selected_user_id' => null
                    ];
                }
            }
        }
    
        echo json_encode(['success' => true, 'chats' => $processedChats]);
    } elseif ($action === 'send_message') {
        $loggedInUserId = $_SESSION['user_id'];
        $conversationId = $data['conversation_id'] ?? null;
        $selectedUserId = $data['selected_user_id'] ?? null;
        $message = $data['message'] ?? null;
        $file = $_FILES['file'] ?? null;
    
        // Validate input: Either conversation_id or selected_user_id must be provided
        if (empty($conversationId) && empty($selectedUserId)) {
            echo json_encode(['success' => false, 'message' => 'Either conversation ID or selected user ID is required.']);
            exit;
        }
    
        // Validate selected_user_id if it's being used
        if (empty($conversationId) && (!is_numeric($selectedUserId) || $selectedUserId <= 0)) {
            echo json_encode(['success' => false, 'message' => 'Invalid selected user ID.']);
            exit;
        }
    
        // If conversation_id is not provided, check if a conversation already exists between the two users
        if (empty($conversationId)) {
            $stmt = $pdo->prepare("
                SELECT p1.conversation_id 
                FROM participants p1
                JOIN participants p2 ON p1.conversation_id = p2.conversation_id
                WHERE p1.user_id = :logged_in_user_id AND p2.user_id = :selected_user_id
            ");
            $stmt->execute([
                ':logged_in_user_id' => $loggedInUserId,
                ':selected_user_id' => $selectedUserId
            ]);
            $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$conversation) {
                // Create a new conversation if none exists
                $stmt = $pdo->prepare("
                    INSERT INTO conversations (type, created_at, group_name)
                    VALUES ('private', NOW(), '-')
                ");
                $stmt->execute();
                $conversationId = $pdo->lastInsertId();
    
                // Add participants to the conversation
                $stmt = $pdo->prepare("
                    INSERT INTO participants (conversation_id, user_id, joined_at)
                    VALUES (:conversation_id, :user_id, NOW())
                ");
    
                // Add logged-in user as a participant
                $stmt->execute([
                    ':conversation_id' => $conversationId,
                    ':user_id' => $loggedInUserId
                ]);
    
                // Add selected user as a participant
                $stmt->execute([
                    ':conversation_id' => $conversationId,
                    ':user_id' => $selectedUserId
                ]);
            } else {
                $conversationId = $conversation['conversation_id'];
            }
        }
    
        // Handle file upload
        $filePath = '-';
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../Documents/Chats/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $date = date('m-d-Y');
            $fileName = $date . '--' . $conversationId . '--' . basename($file['name']);
            $filePath = $uploadDir . $fileName;
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $filePath = 'Documents/Chats/' . $fileName;
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload file.']);
                exit;
            }
        }
    
        // Save the message to the messages table
        $stmt = $pdo->prepare("
            INSERT INTO messages (conversation_id, sender_id, message, file_path, sent_at)
            VALUES (:conversation_id, :sender_id, :message, :file_path, NOW())
        ");
        $stmt->execute([
            ':conversation_id' => $conversationId,
            ':sender_id' => $loggedInUserId,
            ':message' => $message,
            ':file_path' => $filePath
        ]);
    
        echo json_encode(['success' => true, 'message' => 'Message sent successfully.', 'conversation_id' => $conversationId]);
    } elseif ($action === 'fetch_messages') {
        $conversationId = $data['conversation_id'] ?? null;
        $loggedInUserId = $_SESSION['user_id'];
    
        if (empty($conversationId)) {
            echo json_encode(['success' => false, 'message' => 'Conversation ID is required.']);
            exit;
        }
    
        // Fetch messages for the conversation
        $stmt = $pdo->prepare("
            SELECT m.message_id, m.sender_id, m.message, m.file_path, m.sent_at, u.name
            FROM messages m
            JOIN users u ON m.sender_id = u.user_id
            WHERE m.conversation_id = :conversation_id
            ORDER BY m.sent_at ASC
        ");
        $stmt->execute([':conversation_id' => $conversationId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Format messages for the UI
        $formattedMessages = [];
        foreach ($messages as $message) {
            $formattedMessages[] = [
                'sender_id' => $message['sender_id'],
                'sender_name' => $message['name'],
                'message' => $message['message'],
                'file_path' => $message['file_path'],
                'timestamp' => date('h:i A', strtotime($message['sent_at'])),
                'is_sent_by_me' => ($message['sender_id'] === $loggedInUserId)
            ];
        }
    
        echo json_encode(['success' => true, 'messages' => $formattedMessages]);
    } elseif ($action === 'fetch_conversation_id') {
        $loggedInUserId = $data['logged_in_user_id'] ?? null;
        $selectedUserId = $data['selected_user_id'] ?? null;
    
        if (empty($loggedInUserId) || empty($selectedUserId)) {
            echo json_encode(['success' => false, 'message' => 'Logged-in user ID and selected user ID are required.']);
            exit;
        }
    
        // Fetch conversation ID
        $stmt = $pdo->prepare("
            SELECT p1.conversation_id 
            FROM participants p1
            JOIN participants p2 ON p1.conversation_id = p2.conversation_id
            WHERE p1.user_id = :logged_in_user_id AND p2.user_id = :selected_user_id
        ");
        $stmt->execute([
            ':logged_in_user_id' => $loggedInUserId,
            ':selected_user_id' => $selectedUserId
        ]);
        $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($conversation) {
            echo json_encode(['success' => true, 'conversation_id' => $conversation['conversation_id']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No conversation found.']);
        }
    } elseif ($action === 'create_group') {
        $groupName = $data['group_name'] ?? '';
        $selectedUsers = $data['selected_users'] ?? [];
        $loggedInUserId = $data['logged_in_user_id'] ?? null;

        // Validate inputs
        if (empty($groupName)) {
            echo json_encode(['success' => false, 'message' => 'Group name is required.']);
            exit;
        }

        if (count($selectedUsers) < 2) {
            echo json_encode(['success' => false, 'message' => 'At least 2 users are required to create a group.']);
            exit;
        }

        if (empty($loggedInUserId)) {
            echo json_encode(['success' => false, 'message' => 'Logged-in user ID is required.']);
            exit;
        }

        // Insert into conversations table
        $stmt = $pdo->prepare("
            INSERT INTO conversations (type, created_at, group_name)
            VALUES ('group', NOW(), :group_name)
        ");
        $stmt->execute([':group_name' => $groupName]);
        $conversationId = $pdo->lastInsertId();

        // Insert participants into the participants table
        $stmt = $pdo->prepare("
            INSERT INTO participants (conversation_id, user_id, joined_at)
            VALUES (:conversation_id, :user_id, NOW())
        ");

        // Add the logged-in user to the participants ONLY if not already in the selected users list
        if (!in_array($loggedInUserId, $selectedUsers)) {
            $stmt->execute([
                ':conversation_id' => $conversationId,
                ':user_id' => $loggedInUserId
            ]);
        }

        // Add selected users to the participants
        foreach ($selectedUsers as $userId) {
            $stmt->execute([
                ':conversation_id' => $conversationId,
                ':user_id' => $userId
            ]);
        }

        echo json_encode(['success' => true, 'message' => 'Group created successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>