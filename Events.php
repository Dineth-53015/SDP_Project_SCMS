<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="Images/Icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/Styles.css">
</head>

<body>
    <?php include 'NavBar.php'?>

    <?php include 'PHP/checkAdminLecturer.php'?>

    <?php include 'Message.html'?>

    <header>
        Event Management
    </header>

    <div class="container">

        <!-- Event Analytics & Reports -->
        <section id="overview" class="card">
            <h2>Events Overview</h2>
            <?php include 'EventCharts.html'?>
        </section>
        
        <!-- Event Overview Section -->
        <div class="card">
            <h2>Event Listings & Management</h2>
            <div class="RLM-Top">
                <input type="text" id="eventSearch" placeholder="Search events by title..." />
                <button id="openModal">Add New Event</button>
            </div>
            <table id="eventTable">
                <thead>
                    <tr>
                        <th>Event Title</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Event Date</th>
                        <th>Venue</th>
                        <th>Organizer</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>

            <!-- Info Overlay -->
            <div class="overlay" id="infoOverlay">
                <div class="overlay-content">
                    <h2>Event Details</h2>
                    <div class="scrollable-content">
                    <p><strong>Title:</strong> <span id="infoTitle"></span></p>
                    <p><strong>Description:</strong> <span id="infoDescription"></span></p>
                    <p><strong>Category:</strong> <span id="infoCategory"></span></p>
                    <p><strong>Event Date:</strong> <span id="infoEventDate"></span></p>
                    <p><strong>Start Time:</strong> <span id="infoStartTime"></span></p>
                    <p><strong>End Time:</strong> <span id="infoEndTime"></span></p>
                    <p><strong>Venue:</strong> <span id="infoVenue"></span></p>
                    <p><strong>Organizer:</strong> <span id="infoUserId"></span></p>
                    <p><strong>Max Participants:</strong> <span id="infoMaxParticipants"></span></p>
                    <p><strong>Status:</strong> <span id="infoStatus"></span></p>
                    <p><strong>Is Recurring:</strong> <span id="infoIsRecurring"></span></p>
                    <p><strong>Recurrence Pattern:</strong> <span id="infoRecurrencePattern"></span></p>
                    <p><strong>Created At:</strong> <span id="infoCreatedAt"></span></p>
                    <p><strong>Updated At:</strong> <span id="infoUpdatedAt"></span></p>
                    <p><strong>Uploaded Files:</strong></p>
                    <div id="infoFiles"></div>
                    </div>
                    <button id="closeOverlay">Close</button>
                </div>
            </div>
        </div>

        <!-- Add Event Form -->
        <div class="overlay" id="overlay">
            <div class="overlay-content">
                <button class="close-btn" id="closeModal">&times;</button>
                <h2>Add New Event</h2>
                <form id="addEventForm">
                    <div class="scrollable-content">
                        <label for="title">Title:</label>
                        <input type="text" id="title" name="title" required>

                        <label for="description">Description:</label>
                        <textarea id="description" name="description" rows="3"></textarea>

                        <label for="category">Category:</label>
                        <select id="category" name="category" required>
                            <option value="">-- Select Category --</option>
                            <option value="Seminar">Seminar</option>
                            <option value="Workshop">Workshop</option>
                            <option value="Guest Lecture">Guest Lecture</option>
                            <option value="Student Council">Student Council</option>
                            <option value="Other Event">Other Event</option>
                        </select>

                        <label for="event_date">Event Date:</label>
                        <input type="date" id="event_date" name="event_date" required>

                        <label for="start_time">Start Time:</label>
                        <input type="time" id="start_time" name="start_time" required>

                        <label for="end_time">End Time:</label>
                        <input type="time" id="end_time" name="end_time" required>

                        <label for="venue">Venue:</label>
                        <input type="text" id="venue" name="venue" required>

                        <label for="organizer">Organizer:</label>
                        <select id="organizer" name="organizer">
                            <option value="">-- Select Organizer --</option>
                        </select>

                        <label for="max_participants">Max Participants:</label>
                        <input type="text" id="max_participants" name="max_participants" required>

                        <label for="recquiring">Recquiring:</label>
                        <select id="recurrence_pattern" name="recurrence_pattern" required>
                            <option value="">-- Select Recurrence Pattern --</option>
                            <option value="Not Recquiring">Never</option>
                            <option value="Daily">Daily</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Yearly">Yearly</option>
                        </select>

                        <input type="text" id="status" name="status" value="Scheduled" disabled hidden>

                        <label for="eventFiles">Attach Files & Resources:</label>

                        <div class="file-upload-container">
                            <label for="eventFiles" class="file-upload-label">Select Files</label>
                            <input type="file" id="eventFiles" name="eventFiles" multiple />
                            <span id="selectedFiles" class="file-upload-info">No files chosen</span>
                        </div>
                        <input type="file" id="eventFiles" name="eventFiles" multiple style="display: none;" />

                    </div>
                    <div style="margin-top: 15px;">
                        <button type="submit">Submit</button>
                        <button type="button" id="cancelBtn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Event Form -->
        <div class="overlay" id="editEventOverlay">
            <div class="overlay-content">
                <button class="close-btn" id="closeEModal">&times;</button>
                <h2>Edit Event</h2>
                <form id="editEventForm">
                    <div class="scrollable-content">
                        <label for="title">Title:</label>
                        <input type="text" id="editTitle" name="title" required>

                        <label for="description">Description:</label>
                        <textarea id="editDescription" name="description" rows="3"></textarea>

                        <label for="category">Category:</label>
                        <select id="editCategory" name="category" required>
                            <option value="">-- Select Category --</option>
                            <option value="Seminar">Seminar</option>
                            <option value="Workshop">Workshop</option>
                            <option value="Guest Lecture">Guest Lecture</option>
                            <option value="Student Council">Student Council</option>
                            <option value="Other Event">Other Event</option>
                        </select>

                        <label for="event_date">Event Date:</label>
                        <input type="date" id="editEventDate" name="event_date" required>

                        <label for="start_time">Start Time:</label>
                        <input type="time" id="editStartTime" name="start_time" required>

                        <label for="end_time">End Time:</label>
                        <input type="time" id="editEndTime" name="end_time" required>

                        <label for="venue">Venue:</label>
                        <input type="text" id="editVenue" name="venue" required>

                        <label for="editOrganizer">Organizer:</label>
                        <select id="editOrganizer" name="organizer" required>
                            <option value="">-- Select Organizer --</option>
                        </select>

                        <label for="max_participants">Max Participants:</label>
                        <input type="text" id="editMaxParticipants" name="max_participants" required>

                        <label for="recquiring">Recquiring:</label>
                        <select id="editRecurrencePattern" name="recquiring" required>
                            <option value="">-- Select Recurrence Pattern --</option>
                            <option value="Not Recquiring">Never</option>
                            <option value="Daily">Daily</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Yearly">Yearly</option>
                        </select>

                        <label for="eventFiles">Attach Files & Resources:</label>

                        <div id="editUploadedFiles"></div>
                        
                    </div>
                    <div style="margin-top: 15px;">
                        <button type="submit">Submit</button>
                        <button type="button" id="cancelEBtn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Event Registrations Section -->
        <div class="card">
            <h2>Event Registrations</h2>
            <div class="RLM-Top">
                <input type="text" id="eRegistrationSearch" placeholder="Search Events..." />
            </div>
            <table id="eventRegistrationsTable" >
                <thead>
                    <tr>
                        <th>Event Title</th>
                        <th>User</th>
                        <th>Registration Status</th>
                        <th>Registered At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>

            <!-- Event Registrations Info Overlay -->
            <div class="overlay" id="infoOverlay">
                <div class="overlay-content">
                    <h2>Event Registration Details</h2>
                    <div class="scrollable-content">
                        <p><strong>Title:</strong> <span id="infoTitle"></span></p>
                        <p><strong>Description:</strong> <span id="infoDescription"></span></p>
                        <p><strong>Category:</strong> <span id="infoCategory"></span></p>
                        <p><strong>Event Date:</strong> <span id="infoEventDate"></span></p>
                        <p><strong>Start Time:</strong> <span id="infoStartTime"></span></p>
                        <p><strong>End Time:</strong> <span id="infoEndTime"></span></p>
                        <p><strong>Venue:</strong> <span id="infoVenue"></span></p>
                        <p><strong>Organizer:</strong> <span id="infoUserId"></span></p>
                        <p><strong>Max Participants:</strong> <span id="infoMaxParticipants"></span></p>
                        <p><strong>Status:</strong> <span id="infoStatus"></span></p>
                        <p><strong>Is Recurring:</strong> <span id="infoIsRecurring"></span></p>
                        <p><strong>Recurrence Pattern:</strong> <span id="infoRecurrencePattern"></span></p>
                        <p><strong>Created At:</strong> <span id="infoCreatedAt"></span></p>
                        <p><strong>Updated At:</strong> <span id="infoUpdatedAt"></span></p>
                    </div>
                    <button id="closeIOverlay">Close</button>
                </div>
            </div>        
        </div>

        <!-- Event Attendance Section -->
        <div class="card">
            <h2>Event Attendance</h2>
            <div class="BAS">
                <div>
                    <h3>Attendance Marking</h3>
                    <form id="AttendanceForm">
                        <label>Event:</label>
                        <select id="eventSelect" name="event" required>
                            <option value="">-- Select Event --</option>
                        </select>

                        <label>User:</label>
                        <select id="userSelect" name="user" required>
                            <option value="">-- Select User --</option>
                        </select>

                        <label>Select Action:</label>
                        <select id="check_status" name="check_status" required>
                            <option value="">-- Select Action --</option>
                            <option value="check_in">Check In</option>
                            <option value="check_out">Check Out</option>
                        </select>

                        <button type="submit">Mark Attendance</button>
                    </form>
                </div>
                <div>
                    <h3>Attendance</h3>
                    <table id="attendanceTable" class="scrollable-table">
                        <thead>
                            <tr>
                                <th>Events</th>
                                <th>User</th>
                                <th>Check In Time</th>
                                <th>Check Out Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <?php include 'Footer.html'?>

    <!-- Include Chat Section if Only Logged In User's Role is Student or Lecturer -->
    <?php
        if ($role === 'Student' || $role === 'Lecturer') {
        include 'Chats.php';
    }
    ?>


    <script>
        document.getElementById('eventFiles').addEventListener('change', function () {
            const fileNames = this.files.length > 0
                ? Array.from(this.files).map(file => file.name).join(', ')
                : 'No files chosen';
            document.getElementById('selectedFiles').textContent = fileNames;
        });

        const openModalBtn = document.getElementById('openModal');
        const closeModalBtn = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const overlay = document.getElementById('overlay');
        const infoOverlay = document.getElementById('infoOverlay');
        const closeIOverlay = document.getElementById('closeIOverlay');
        const editEventOverlay = document.getElementById('editEventOverlay');
        const closeEModalBtn = document.getElementById('closeEModal');
        const cancelEBtn = document.getElementById('cancelEBtn');
        const closeOverlayButton = document.getElementById('closeOverlay');

        openModalBtn.addEventListener('click', () => {
            overlay.style.display = 'flex';
        });

        closeModalBtn.addEventListener('click', () => {
            overlay.style.display = 'none';
        });

        cancelBtn.addEventListener('click', () => {
            overlay.style.display = 'none';
        });

        closeEModalBtn.addEventListener('click', () => {
            editEventOverlay.style.display = 'none';
        });

        cancelEBtn.addEventListener('click', () => {
            editEventOverlay.style.display = 'none';
        });

        closeIOverlay.addEventListener('click', () => {
            infoOverlay.style.display = 'none';
        });

        // Close Modal on Outside Click
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.style.display = 'none';
            }
        });

        // Close Modal on Outside Click
        editEventOverlay.addEventListener('click', (e) => {
            if (e.target === editEventOverlay) {
                editEventOverlay.style.display = 'none';
            }
        });

        // Close Modal on Outside Click
        infoOverlay.addEventListener('click', (e) => {
            if (e.target === infoOverlay) {
                infoOverlay.style.display = 'none';
            }
        });

        const addEventForm = document.getElementById('addEventForm');

        // Add events
        addEventForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData();

            formData.append('action', 'add_event');
            formData.append('title', document.getElementById('title').value);
            formData.append('description', document.getElementById('description').value);
            formData.append('category', document.getElementById('category').value);
            formData.append('event_date', document.getElementById('event_date').value);
            formData.append('start_time', document.getElementById('start_time').value);
            formData.append('end_time', document.getElementById('end_time').value);
            formData.append('venue', document.getElementById('venue').value);
            formData.append('user_id', document.getElementById('organizer').value);
            formData.append('max_participants', document.getElementById('max_participants').value);
            formData.append('status', document.getElementById('status').value);
            formData.append('recurrence_pattern', document.getElementById('recurrence_pattern').value);

            const files = document.getElementById('eventFiles').files;
            for (let i = 0; i < files.length; i++) {
                formData.append('eventFiles[]', files[i]);
            }

            try {
                const response = await fetch('PHP/Events.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    overlay.style.display = 'none';
                    show('success');
                } else {
                    alert('Error adding event: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while adding the event.');
            }
        });

        // Fetch events
        document.addEventListener("DOMContentLoaded", function () {
            const tableBody = document.querySelector("#eventTable tbody");

            function fetchEvents(searchTerm = "") {
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "PHP/Events.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);

                        if (response.success) {
                            const events = response.data;
                            tableBody.innerHTML = "";

                            events.forEach(event => {
                                const row = document.createElement("tr");

                                row.innerHTML = `
                                    <td data-label="Title">${event.title}</td>
                                    <td data-label="Description">${event.description}</td>
                                    <td data-label="Category">${event.category}</td>
                                    <td data-label="Event Date">${event.event_date}</td>
                                    <td data-label="Venue">${event.venue}</td>
                                    <td data-label="Organizer">${event.organizer_name}</td>
                                    <td data-label="Status">${event.status}</td>
                                    <td class="action-buttons">
                                        <button class="edit-btn" data-event-id="${event.event_id}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="remove-btn" data-event-id="${event.event_id}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <button class="info-btn" data-event-id="${event.event_id}">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                    </td>
                                `;

                                tableBody.appendChild(row);
                            });
                        } else {
                            console.error("Error fetching events:", response.message);
                        }
                    }
                };

                if (searchTerm.trim() === "") {
                    xhr.send("action=fetch_events");
                } else {
                    xhr.send(`action=search_events&search_term=${encodeURIComponent(searchTerm)}`);
                }
            }

            fetchEvents();

            // Listen for input changes in the search bar
            const searchInput = document.getElementById("eventSearch");
            searchInput.addEventListener("input", function () {
                const searchTerm = searchInput.value;
                fetchEvents(searchTerm);
            });

            document.addEventListener('click', function (event) {
                // Handle "Info" button click
                if (event.target.closest('.info-btn')) {
                    const eventId = event.target.closest('.info-btn').dataset.eventId;

                    fetch('PHP/Events.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=fetch_events&event_id=${eventId}`,
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.data.length > 0) {
                                const event = data.data[0];

                                document.getElementById('infoTitle').textContent = event.title;
                                document.getElementById('infoDescription').textContent = event.description;
                                document.getElementById('infoCategory').textContent = event.category;
                                document.getElementById('infoEventDate').textContent = event.event_date;
                                document.getElementById('infoStartTime').textContent = event.start_time;
                                document.getElementById('infoEndTime').textContent = event.end_time;
                                document.getElementById('infoVenue').textContent = event.venue;
                                document.getElementById('infoUserId').textContent = event.organizer_name;
                                document.getElementById('infoMaxParticipants').textContent = event.max_participants;
                                document.getElementById('infoStatus').textContent = event.status;
                                document.getElementById('infoIsRecurring').textContent = event.is_recurring ? 'Yes' : 'No';
                                document.getElementById('infoRecurrencePattern').textContent = event.recurrence_pattern;
                                document.getElementById('infoCreatedAt').textContent = event.created_at;
                                document.getElementById('infoUpdatedAt').textContent = event.updated_at;

                                const filesContainer = document.getElementById('infoFiles');
                                filesContainer.innerHTML = '';

                                if (event.files && event.files.length > 0) {
                                    event.files.forEach(file => {
                                        const fileLink = document.createElement('a');
                                        fileLink.href = file.file_path;
                                        fileLink.textContent = file.file_name;
                                        fileLink.download = file.file_name;
                                        fileLink.style.display = 'block';
                                        filesContainer.appendChild(fileLink);
                                    });
                                } else {
                                    filesContainer.textContent = 'No files uploaded.';
                                }

                                document.getElementById('infoOverlay').style.display = 'flex';
                            } else {
                                alert('Error fetching event details.');
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));
                }
                if (event.target === closeOverlayButton || event.target.closest('#closeOverlay')) {
                    infoOverlay.style.display = 'none';
                }
            });

            // Handle "Remove" button click
            document.addEventListener('click', function (event) {
                const removeBtn = event.target.closest('.remove-btn');
                if (removeBtn) {
                    const eventId = removeBtn.dataset.eventId;

                    show('confirm');

                    const yesButton = document.querySelector('.btns button:first-child');
                    const noButton = document.querySelector('.btns button:last-child');

                    const yesHandler = () => {
                        fetch('PHP/Events.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=delete_event&event_id=${eventId}`,
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    show('success');
                                } else {
                                    alert('Error deleting event: ' + data.message);
                                }
                            })
                            .catch(error => console.error('Delete error:', error));

                        yesButton.removeEventListener('click', yesHandler);
                        noButton.removeEventListener('click', noHandler);
                    };

                    const noHandler = () => {
                        hide();

                        yesButton.removeEventListener('click', yesHandler);
                        noButton.removeEventListener('click', noHandler);
                    };

                    yesButton.addEventListener('click', yesHandler);
                    noButton.addEventListener('click', noHandler);
                }
            });
        });

        document.addEventListener('click', function (event) {
            // Handle "Edit" button click
            if (event.target.closest('.edit-btn')) {
                const eventId = event.target.closest('.edit-btn').dataset.eventId;
                openEditOverlay(eventId);
            }
        });

        function openEditOverlay(eventId) {
            // Show the overlay
            document.getElementById('editEventOverlay').style.display = 'flex';

            document.getElementById('editTitle').dataset.eventId = eventId;

            fetch('PHP/Events.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=fetch_events&event_id=${eventId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    const event = data.data[0];

                    document.getElementById('editTitle').value = event.title;
                    document.getElementById('editDescription').value = event.description;
                    document.getElementById('editCategory').value = event.category;
                    document.getElementById('editEventDate').value = event.event_date.split(' ')[0];
                    document.getElementById('editStartTime').value = event.start_time;
                    document.getElementById('editEndTime').value = event.end_time;
                    document.getElementById('editVenue').value = event.venue;
                    document.getElementById('editOrganizer').value = event.user_id;
                    document.getElementById('editMaxParticipants').value = event.max_participants;
                    document.getElementById('editRecurrencePattern').value = event.recurrence_pattern;

                    const uploadedFilesDiv = document.getElementById('editUploadedFiles');
                    uploadedFilesDiv.innerHTML = '';
                    if (event.files && event.files.length > 0) {
                        event.files.forEach(file => {
                            const fileLink = document.createElement('a');
                            fileLink.href = file.file_path;
                            fileLink.textContent = file.file_name;
                            fileLink.download = file.file_name;
                            uploadedFilesDiv.appendChild(fileLink);
                            uploadedFilesDiv.appendChild(document.createElement('br'));
                        });
                    } else {
                        uploadedFilesDiv.textContent = 'No files uploaded.';
                    }
                } else {
                    alert('Failed to fetch event data.');
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Edit Event Form
        document.getElementById('editEventForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData();

            formData.append('action', 'update_event');
            formData.append('event_id', document.getElementById('editTitle').dataset.eventId);
            formData.append('title', document.getElementById('editTitle').value);
            formData.append('description', document.getElementById('editDescription').value);
            formData.append('category', document.getElementById('editCategory').value);
            formData.append('event_date', document.getElementById('editEventDate').value);
            formData.append('start_time', document.getElementById('editStartTime').value);
            formData.append('end_time', document.getElementById('editEndTime').value);
            formData.append('venue', document.getElementById('editVenue').value);
            formData.append('user_id', document.getElementById('editOrganizer').value);
            formData.append('max_participants', document.getElementById('editMaxParticipants').value);
            formData.append('status', 'Scheduled');
            formData.append('recurrence_pattern', document.getElementById('editRecurrencePattern').value);

            try {
                const response = await fetch('PHP/Events.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    editEventOverlay.style.display = 'none';
                    show('success');
                } else {
                    alert('Error updating event: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while updating the event.');
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Fetch Organizers
            async function fetchOrganizers() {
                try {
                    const response = await fetch('PHP/Users.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ action: 'fetch' }),
                    });

                    const result = await response.json();

                    if (result.success) {
                        const organizers = result.data;

                        const addEventOrganizerDropdown = document.getElementById('organizer');
                        populateDropdown(addEventOrganizerDropdown, organizers);

                        const editEventOrganizerDropdown = document.getElementById('editOrganizer');
                        populateDropdown(editEventOrganizerDropdown, organizers);
                    } else {
                        console.error('Error fetching organizers:', result.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }

            function populateDropdown(dropdown, organizers) {
                dropdown.innerHTML = '<option value="">-- Select Organizer --</option>';
                organizers.forEach(organizer => {
                    const option = document.createElement('option');
                    option.value = organizer.user_id;
                    option.textContent = organizer.name;
                    dropdown.appendChild(option);
                });
            }

            fetchOrganizers();
        });

        // Fetch Event Registrations
        document.addEventListener('DOMContentLoaded', function () {
            const eRegistrationSearch = document.getElementById('eRegistrationSearch');
            const tableBody = document.querySelector('#eventRegistrationsTable tbody');

            function fetchRegistrations(searchTerm = '') {
                fetch('PHP/EventRegistrations.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=fetch_registrations&search_term=${encodeURIComponent(searchTerm)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        tableBody.innerHTML = '';

                        data.data.forEach(registration => {
                            const row = `
                                <tr>
                                    <td data-label="Event Title">${registration.event_title}</td>
                                    <td data-label="User">${registration.user_name}</td>
                                    <td data-label="Registration Status">${registration.registration_status}</td>
                                    <td data-label="Registered At">${registration.registered_at}</td>
                                    <td class="action-buttons">
                                        <button onclick="rejectRegistration(${registration.event_id}, ${registration.user_id})">
                                            <i class="fa-solid fa-ban"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                            tableBody.innerHTML += row;
                        });
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            }

            fetchRegistrations();

            eRegistrationSearch.addEventListener('input', function () {
                const searchTerm = eRegistrationSearch.value.trim();
                fetchRegistrations(searchTerm);
            });

            window.rejectRegistration = function (eventId, userId) {
                fetch('PHP/EventRegistrations.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=reject_registration&event_id=${eventId}&user_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        show('success');
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            };
        });

        // Fetch Data for Select Inputs
        document.addEventListener('DOMContentLoaded', function () {
            const eventSelect = document.getElementById('eventSelect');
            const userSelect = document.getElementById('userSelect');

            eventSelect.addEventListener('change', function () {
                const selectedEventId = eventSelect.value;

                userSelect.innerHTML = '<option value="">-- Select User --</option>';

                if (selectedEventId) {
                    fetchUsersByEvent(selectedEventId);
                }
            });

            function fetchUsersByEvent(eventId) {
                console.log('Fetching users for event ID:', eventId);
                fetch('PHP/EventRegistrations.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=fetch_users_by_event&event_id=${eventId}` 
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Response from server:', data);
                    if (data.success) {
                        const users = data.data;
                        users.forEach(user => {
                            const option = document.createElement('option');
                            option.value = user.user_id;
                            option.textContent = user.user_name;
                            userSelect.appendChild(option);
                        });
                    } else {
                        console.error('Error fetching users:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }

            async function fetchEvents() {
                try {
                    const response = await fetch('PHP/Events.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=fetch_events'
                    });
                    const result = await response.json();
                    if (result.success) {
                        const events = result.data;
                        eventSelect.innerHTML = '<option value="">-- Select Event --</option>';
                        events.forEach(event => {
                            const option = document.createElement('option');
                            option.value = event.event_id;
                            option.textContent = event.title;
                            eventSelect.appendChild(option);
                        });
                    } else {
                        console.error('Error fetching events:', result.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }

            fetchEvents();
        });

        // Attendance Marking
        document.getElementById('AttendanceForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const eventId = document.getElementById('eventSelect').value;
            const userId = document.getElementById('userSelect').value;
            const action = document.getElementById('check_status').value;

            if (!eventId || !userId || !action) {
                alert('Please fill in all fields.');
                return;
            }

            try {
                const response = await fetch('PHP/EventAttendance.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `event_id=${eventId}&user_id=${userId}&action=${action}`
                });
                const result = await response.json();

                console.log('Server response:', result);
                
                if (result.success) {
                    show('success');
                } else {
                    alert('Error marking attendance: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                //alert('An error occurred while marking attendance.');
            }
        });

        // Initial Event Attendances
        document.addEventListener('DOMContentLoaded', function () {
            const attendanceTableBody = document.querySelector('#attendanceTable tbody');

            function fetchAttendanceData() {
                fetch('PHP/EventAttendance.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=fetch_attendance'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        attendanceTableBody.innerHTML = ''; 
                        
                        data.data.forEach(attendance => {
                            const row = `
                                <tr>
                                    <td data-label="Event Title">${attendance.event_title}</td>
                                    <td data-label="Name">${attendance.user_name}</td>
                                    <td data-label="Check In Time">${attendance.check_in_time || 'N/A'}</td>
                                    <td data-label="Check Out Time">${attendance.check_out_time || 'N/A'}</td>
                                </tr>
                            `;
                            attendanceTableBody.innerHTML += row;
                        });
                    } else {
                        console.error('Error fetching attendance data:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            }

            fetchAttendanceData();
        });
    </script>

</body>

</html>