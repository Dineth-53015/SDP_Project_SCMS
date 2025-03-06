<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="Css/Chats.css">
</head>
<body>
    <button class="chat-toggle-btn">
        <i class="fas fa-comment-dots"></i>
    </button>

    <!-- Chat Overlay -->
    <div class="chat-overlay">
        <button class="close-overlay-btn">×</button>
        <div class="chat-container">
            <div class="left-section">
                <div class="header">
                    <input type="text" class="search-bar" id="search-bar" placeholder="Search...">
                    <button class="add-group-btn" id="addGroupBtn">+</button>
                </div>
                <div class="chats-list">
                    
                </div>
                <div class="separator"></div>
                <div class="contacts-list" id="contacts-list">
                    
                </div>
            </div>

            <!-- Chat Section -->
            <div class="right-section">
                <div class="conversation-header">
                    <button class="back-btn" aria-label="Go back">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    <span class="chat-title">Name</span>
                </div>
                <div class="messages-container">
                    
                </div>
                <div class="attachment-bar" style="display: none;">
                    <span class="file-name"></span>
                    <button class="remove-file-btn">×</button>
                </div>
                <div class="message-input">
                    <input type="text" class="message-box" placeholder="Type a message...">
                    <label class="attach-file-label">
                        <i class="fa-solid fa-paperclip"></i>
                        <input type="file" class="attach-file-btn"  id="attach-file-btn" style="display: none;">
                    </label>
                    <button class="send-btn">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </div>
            <div class="add-group-section" id="addGroupSection">
                <div class="add-group-header">
                    <h3>Create New Group</h3>
                    <button class="close-btn" id="closeAddGroupBtn">&times;</button>
                </div>
                <input type="text" class="u-search-bar" id="u-search-bar" placeholder="Search...">
                <div class="user-list" id="user-list">
                    
                </div>
                <div class="selected-user-list" id="selected-user-list">
                    
                </div>
                <input type="text" class="u-search-bar" id="group-name" placeholder="Group Name">
                <button class="create-group-btn" id="createGroupBtn">Create</button>
            </div>
        </div>
    </div>

    <script>

        document.addEventListener('DOMContentLoaded', function () {
            const chatToggleButton = document.querySelector('.chat-toggle-btn');
            const chatOverlay = document.querySelector('.chat-overlay');
            const chatItems = document.querySelectorAll('.chat-item');
            const backButtons = document.querySelectorAll('.back-btn');
            const leftSection = document.querySelector('.left-section');
            const rightSection = document.querySelector('.right-section');
            const searchBar = document.getElementById('search-bar');
            const contactsList = document.getElementById('contacts-list');
            const conversationHeader = document.querySelector('.conversation-header');
            const chatTitle = document.querySelector('.chat-title');
            const messageBox = document.querySelector('.message-box');
            const sendBtn = document.querySelector('.send-btn');
            const attachFileBtn = document.querySelector('.attach-file-btn');
            const attachmentBar = document.querySelector('.attachment-bar');
            const fileNameSpan = document.querySelector('.file-name');
            const removeFileBtn = document.querySelector('.remove-file-btn');
            const closeOverlayBtn = document.querySelector('.close-overlay-btn');

            let selectedUserId = null;
            let pollingInterval = null;
            let selectedConversationId = null;

            chatToggleButton.addEventListener('click', () => {
                chatOverlay.classList.toggle('active');
                if (!chatOverlay.classList.contains('active')) {
                    stopPolling();
                }
            });

            closeOverlayBtn.addEventListener('click', () => {
                chatOverlay.classList.remove('active');
                stopPolling();
            });

            // Open chat details (right section)
            chatItems.forEach(chatItem => {
                chatItem.addEventListener('click', () => {
                    leftSection.style.display = 'none';
                    rightSection.classList.add('active');
                });
            });

            // Go back to chat list (left section)
            backButtons.forEach(backButton => {
                backButton.addEventListener('click', () => {
                    leftSection.style.display = 'flex';
                    rightSection.classList.remove('active');
                    stopPolling();
                });
            });

            // Handle file selection
            attachFileBtn.addEventListener('change', (event) => {
                const file = event.target.files[0];
                if (file) {
                    attachmentBar.style.display = 'flex';
                    fileNameSpan.textContent = file.name;
                }
            });

            // Remove the file and hide the attachment bar
            removeFileBtn.addEventListener('click', () => {
                attachmentBar.style.display = 'none';
                fileNameSpan.textContent = '';
                attachFileBtn.value = '';
            });

            // Function to fetch contacts from the server
            function fetchContacts(searchTerm = '') {
                fetch('PHP/Users.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'fetch_active_users',
                        search: searchTerm
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderContacts(data.data);
                    } else {
                        console.error('Failed to fetch contacts:', data.message);
                    }
                })
                .catch(error => console.error('Error fetching contacts:', error));
            }

            // Function to render contacts in the contacts list
            function renderContacts(contacts) {
                contactsList.innerHTML = '';

                contacts.forEach(contact => {
                    if (contact.role === 'Student' || contact.role === 'Lecturer') {
                        const contactItem = document.createElement('div');
                        contactItem.classList.add('contact-item');
                        contactItem.textContent = `${contact.name} ( ${contact.role} )`;
                        contactItem.setAttribute('data-user-id', contact.user_id);
                        contactItem.setAttribute('data-name', contact.name);
                        contactItem.setAttribute('data-role', contact.role);
                        contactsList.appendChild(contactItem);
                    }
                });
            }

            // Event listener for the search bar
            searchBar.addEventListener('input', function () {
                const searchTerm = searchBar.value.trim();
                fetchChats(searchTerm);
                fetchContacts(searchTerm);
            });

            // Event delegation for contact items
            contactsList.addEventListener('click', function (event) {
                const contactItem = event.target.closest('.contact-item');
                if (contactItem) {
                    selectedUserId = contactItem.getAttribute('data-user-id');
                    const name = contactItem.getAttribute('data-name');
                    const role = contactItem.getAttribute('data-role');

                    chatTitle.textContent = `${name} ( ${role} )`;

                    leftSection.style.display = 'none';
                    rightSection.classList.add('active');

                    fetchConversationId(selectedUserId);
                }
            });

            // Function to fetch conversation ID and start polling
            function fetchConversationId(selectedUserId) {
                const loggedInUserId = <?php echo json_encode($_SESSION['user_id']); ?>;

                fetch('PHP/Chats.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'fetch_conversation_id',
                        logged_in_user_id: loggedInUserId,
                        selected_user_id: selectedUserId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const conversationId = data.conversation_id;
                        fetchMessages(conversationId);
                        startPolling(conversationId);
                    } else {
                        console.error('Failed to fetch conversation ID:', data.message);
                    }
                })
                .catch(error => console.error('Error fetching conversation ID:', error));
            }

            // Function to start polling for new messages
            function startPolling(conversationId) {
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                }
                pollingInterval = setInterval(() => {
                    fetchMessages(conversationId);
                }, 2000);
            }

            function stopPolling() {
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                    pollingInterval = null;
                }
            }

            // Function to fetch and display messages
            function fetchMessages(conversationId) {
                fetch('PHP/Chats.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'fetch_messages',
                        conversation_id: conversationId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderMessages(data.messages);
                    } else {
                        console.error('Failed to fetch messages:', data.message);
                    }
                })
                .catch(error => console.error('Error fetching messages:', error));
            }

            // Function to render messages in the UI
            function renderMessages(messages) {
                const messagesContainer = document.querySelector('.messages-container');
                messagesContainer.innerHTML = '';

                messages.forEach(message => {
                    const messageElement = document.createElement('div');
                    messageElement.classList.add('message');
                    messageElement.classList.add(message.is_sent_by_me ? 'sent' : 'received');

                    const messageContent = document.createElement('div');
                    messageContent.classList.add('message-content');

                    const messageHeader = document.createElement('div');
                    messageHeader.classList.add('message-header');

                    const senderName = document.createElement('span');
                    senderName.classList.add('sender-name');
                    senderName.textContent = message.sender_name;

                    const timestamp = document.createElement('span');
                    timestamp.classList.add('timestamp');
                    timestamp.textContent = message.timestamp;

                    messageHeader.appendChild(senderName);
                    messageHeader.appendChild(timestamp);
                    messageContent.appendChild(messageHeader);

                    if (message.file_path && message.file_path !== '-') {
                        const messageBodyContainer = document.createElement('div');
                        messageBodyContainer.classList.add('message-body-container');

                        const messageIcon = document.createElement('i');
                        messageIcon.classList.add('fas', 'fa-file-alt', 'message-icon');

                        const messageBody = document.createElement('div');
                        messageBody.classList.add('message-body');

                        const fileName = message.file_path.split('--').pop();
                        const fileLink = document.createElement('a');
                        fileLink.href = message.file_path;
                        fileLink.textContent = fileName;
                        fileLink.download = fileName;

                        fileLink.classList.add('file-link');

                        messageBody.appendChild(fileLink);
                        messageBodyContainer.appendChild(messageIcon);
                        messageBodyContainer.appendChild(messageBody);
                        messageContent.appendChild(messageBodyContainer);
                    }

                    if (message.message) {
                        const messageBody = document.createElement('div');
                        messageBody.classList.add('message-body');
                        messageBody.textContent = message.message;
                        messageContent.appendChild(messageBody);
                    }

                    messageElement.appendChild(messageContent);
                    messagesContainer.appendChild(messageElement);
                });

                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            // Send message on Enter key or button click
            function sendMessage() {
                const message = messageBox.value.trim();
                const file = attachFileBtn.files[0];

                if (!message && !file) {
                    alert('Please type a message or attach a file.');
                    return;
                }

                const formData = new FormData();
                formData.append('action', 'send_message');

                if (selectedConversationId) {
                    formData.append('conversation_id', selectedConversationId);
                    console.log('Selected Conversation ID:', selectedConversationId);
                } else {
                    formData.append('selected_user_id', selectedUserId);
                    console.log('Selected User ID:', selectedUserId);
                }

                formData.append('message', message);
                if (file) {
                    formData.append('file', file);
                }

                fetch('PHP/Chats.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageBox.value = '';
                        attachFileBtn.value = '';
                        attachmentBar.style.display = 'none';
                        fileNameSpan.textContent = '';
                        fetchMessages(data.conversation_id);
                    } else {
                        console.error('Failed to send message:', data.message);
                    }
                })
                .catch(error => console.error('Error sending message:', error));
            }

            // Send message on button click
            sendBtn.addEventListener('click', sendMessage);

            // Send message on Enter key
            messageBox.addEventListener('keypress', function (event) {
                if (event.key === 'Enter') {
                    sendMessage();
                }
            });

            // Initial fetch to load all contacts
            fetchContacts();

            function fetchChats(searchTerm = '') {
                fetch('PHP/Chats.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'fetch_chats',
                        search: searchTerm
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderChats(data.chats);
                    } else {
                        console.error('Failed to fetch chats:', data.message);
                    }
                })
                .catch(error => console.error('Error fetching chats:', error));
            }


            function renderChats(chats) {
            const chatsList = document.querySelector('.chats-list');
            chatsList.innerHTML = '';

            chats.forEach(chat => {
                const chatItem = document.createElement('div');
                chatItem.classList.add('chat-item');
                chatItem.setAttribute('data-conversation-id', chat.conversation_id);
                chatItem.setAttribute('data-selected-user-id', chat.selected_user_id);
                chatItem.innerHTML = `
                    <span class="chat-name">${chat.name}</span>
                `;
                chatsList.appendChild(chatItem);
            });

            // Add event listeners to chat items
            const chatItems = document.querySelectorAll('.chat-item');
            chatItems.forEach(chatItem => {
                chatItem.addEventListener('click', () => {
                    const conversationId = chatItem.getAttribute('data-conversation-id');
                    const selectedUserIdFromItem = chatItem.getAttribute('data-selected-user-id');
                    const chatName = chatItem.querySelector('.chat-name').textContent;

                    chatTitle.textContent = chatName;

                    leftSection.style.display = 'none';
                    rightSection.classList.add('active');

                    selectedUserId = selectedUserIdFromItem;
                    selectedConversationId = conversationId;

                    console.log('Selected Conversation ID:', selectedConversationId);
                    console.log('Selected User ID:', selectedUserId);

                    fetchMessages(conversationId);
                    startPolling(conversationId);
                });
            });
        }

        fetchChats();
        
        });

        document.addEventListener("DOMContentLoaded", function () {
            const addGroupBtn = document.getElementById("addGroupBtn");
            const closeAddGroupBtn = document.getElementById("closeAddGroupBtn");
            const addGroupSection = document.getElementById("addGroupSection");
            const userList = document.getElementById("user-list");
            const selectedUserList = document.getElementById("selected-user-list");
            const uSearchBar = document.getElementById("u-search-bar");

            addGroupBtn.addEventListener("click", function () {
                addGroupSection.style.display = "block";
                fetchUsers();
            });

            closeAddGroupBtn.addEventListener("click", function () {
                addGroupSection.style.display = "none";
                leftSection.classList.remove("hidden");
                rightSection.classList.remove("hidden");
            });

            // Fetch users
            function fetchUsers(searchTerm = '') {
                fetch('PHP/Users.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'fetch_active_users',
                        search: searchTerm
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderUsers(data.data);
                    } else {
                        console.error('Failed to fetch users:', data.message);
                    }
                })
                .catch(error => console.error('Error fetching users:', error));
            }

            // Render users in the user-list
            function renderUsers(users) {
                userList.innerHTML = '';
                const loggedInUserId = <?php echo json_encode($_SESSION['user_id']); ?>;

                users.forEach(user => {
                    if (user.user_id === loggedInUserId) return;

                    const userItem = document.createElement('div');
                    userItem.classList.add('contact-item');
                    userItem.setAttribute('data-user-id', user.user_id);
                    userItem.setAttribute('data-name', user.name);
                    userItem.setAttribute('data-role', user.role);
                    userItem.textContent = `${user.name} ( ${user.role} )`;

                    userItem.addEventListener('click', () => {
                        addUserToSelectedList(userItem);
                    });

                    userList.appendChild(userItem);
                });
            }

            // Add user to the selected-user-list
            function addUserToSelectedList(userItem) {
                const userId = userItem.getAttribute('data-user-id');
                const userName = userItem.getAttribute('data-name');
                const userRole = userItem.getAttribute('data-role');

                // Check if the user is already in the selected-user-list
                const isAlreadySelected = Array.from(selectedUserList.children).some(
                    item => item.getAttribute('data-user-id') === userId
                );

                if (!isAlreadySelected) {
                    const selectedUserItem = document.createElement('div');
                    selectedUserItem.setAttribute('data-user-id', userId);
                    selectedUserItem.textContent = `${userName} ( ${userRole} )`;

                    const removeButton = document.createElement('button');
                    removeButton.textContent = '×';
                    removeButton.classList.add('remove-selected-user-btn');
                    removeButton.addEventListener('click', () => {
                        selectedUserItem.remove();
                    });

                    selectedUserItem.appendChild(removeButton);
                    selectedUserList.appendChild(selectedUserItem);
                }
            }

            // Event listener for the search bar
            uSearchBar.addEventListener('input', function () {
                const searchTerm = uSearchBar.value.trim();
                fetchUsers(searchTerm);
            });

            fetchUsers();
        });

        // Function to create a group
        document.addEventListener("DOMContentLoaded", function () {
            const createGroupBtn = document.getElementById("createGroupBtn");
            const groupNameInput = document.getElementById("group-name");
            const selectedUserList = document.getElementById("selected-user-list");

            createGroupBtn.addEventListener("click", function () {
                const groupName = groupNameInput.value.trim();
                const selectedUsers = Array.from(selectedUserList.children).map(
                    item => item.getAttribute("data-user-id")
                );

                if (!groupName) {
                    alert("Group Name is needed.");
                    return;
                }

                if (selectedUsers.length < 2) {
                    alert("Please select 2 or more users.");
                    return;
                }

                createGroup(groupName, selectedUsers);
            });

            function createGroup(groupName, selectedUsers) {
                const loggedInUserId = <?php echo json_encode($_SESSION['user_id']); ?>;

                fetch('PHP/Chats.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'create_group',
                        group_name: groupName,
                        selected_users: selectedUsers,
                        logged_in_user_id: loggedInUserId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Group created successfully!");
                        addGroupSection.style.display = "none";
                        groupNameInput.value = "";
                        selectedUserList.innerHTML = "";
                        fetchChats();
                    } else {
                        console.error("Failed to create group:", data.message);
                        alert("Failed to create group: " + data.message);
                    }
                })
                .catch(error => {
                    console.error("Error creating group:", error);
                    alert("An error occurred while creating the group.");
                });
            }
        });

    </script>
</body>
</html>