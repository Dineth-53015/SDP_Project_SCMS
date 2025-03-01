<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="Images/Icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/Styles.css">
</head>
<body>

    <?php include 'NavBar.php'?>

    <?php include 'PHP/checkAdminLecturer.php'?>
    
    <?php include 'Message.html'?>

    <header>
        Schedule Management
    </header>

    <div class="container">

        <!-- Event Analytics & Reports -->
        <section id="overview" class="card">
            <h2>Schedules Overview</h2>
            <?php include 'ScheduleCharts.html'?>
        </section>

        <!-- Schedule Listings & Management Section -->
        <div class="card">

            <h2>Schedule Listings & Management</h2>

            <div class="RLM-Top">
                <input type="text" id="scheduleSearch" placeholder="Search schedules by title..." />
                <button id="openModal">Add New Schedule</button>
            </div>

            <table id="scheduleTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Course</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Recurrence Pattern</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>

            <!-- Schedule Info Overlay -->
            <div class="overlay" id="infoOverlay">
                <div class="overlay-content">
                    <h2>Event Details</h2>
                    <div class="scrollable-content">
                    <p><strong>Title:</strong> <span id="infoTitle"></span></p>
                    <p><strong>Description:</strong> <span id="infoDescription"></span></p>
                    <p><strong>Course:</strong> <span id="infoCourseName"></span></p>
                    <p><strong>Lecturer In Charge:</strong> <span id="infoLecturerInCharge"></span></p>
                    <p><strong>Schedule Type:</strong> <span id="infoScheduleType"></span></p>
                    <p><strong>Start Date:</strong> <span id="infoStartDate"></span></p>
                    <p><strong>Start Time:</strong> <span id="infoStartTime"></span></p>
                    <p><strong>End Time:</strong> <span id="infoEndTime"></span></p>
                    <p><strong>Room ID:</strong> <span id="infoRoomName"></span></p>
                    <p><strong>Is Recurring:</strong> <span id="infoIsRecurring"></span></p>
                    <p><strong>Recurrence Pattern:</strong> <span id="infoRecurrencePattern"></span></p>
                    <p><strong>Recurrence End Date:</strong> <span id="infoRecurrenceEndDate"></span></p>
                    <p><strong>Created By:</strong> <span id="infoCreatedBy"></span></p>
                    <p><strong>Created At:</strong> <span id="infoCreatedAt"></span></p>
                    <p><strong>Updated At:</strong> <span id="infoUpdatedAt"></span></p>
                    </div>
                    <button id="closeOverlay">Close</button>
                </div>
            </div>
        </div>

        <!-- Add Schedule Overlay -->
        <div class="overlay" id="overlay">
            <div class="overlay-content">
                <button class="close-btn" id="closeModal">&times;</button>
                <h2>Add New Schedule</h2>
                <form id="addScheduleForm">
                    <div class="scrollable-content">
                        <label for="title">Title:</label>
                        <input type="text" id="title" name="title" required>

                        <label for="description">Description:</label>
                        <textarea id="description" name="description" rows="3"></textarea>

                        <label for="course_id">Course:</label>
                        <select id="course_id" name="course_id" required>
                            
                        </select>

                        <label for="user_id">Lecturer In Charge:</label>
                        <select id="user_id" name="user_id" required>
                            <option value="">-- Select Course --</option>
                        </select>

                        <label for="schedule_type">Schedule Type:</label>
                        <select id="schedule_type" name="schedule_type" required>
                            <option value="">-- Select Schedule Type --</option>
                            <option value="Lecture">Lecture</option>
                            <option value="Lab">Lab</option>
                            <option value="Tutorial">Tutorial</option>
                            <option value="Exam">Exam</option>
                        </select>

                        <label for="start_date">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" required>

                        <label for="start_time">Start Time:</label>
                        <input type="time" id="start_time" name="start_time" required>

                        <label for="end_time">End Time:</label>
                        <input type="time" id="end_time" name="end_time" required>

                        <label for="room_id">Room:</label>
                        <select id="room_id" name="room_id" required>
                            <option value="">-- Select Room --</option>
                        </select>

                        <label for="recurrence_pattern">Recurrence Pattern:</label>
                        <select id="recurrence_pattern" name="recurrence_pattern">
                            <option value="">-- Select Recurrence Pattern --</option>
                            <option value="Never">Never</option>
                            <option value="Daily">Daily</option>
                            <option value="Weekly">Weekly</option>
                            <option value="Monthly">Monthly</option>
                        </select>

                        <label for="recurrence_end_date">Recurrence End Date:</label>
                        <input type="date" id="recurrence_end_date" name="recurrence_end_date">

                        <label for="created_by">Created By:</label>
                        <input type="text" id="created_by" name="created_by" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" required readonly>
                    </div>
                    <div style="margin-top: 15px;">
                        <button type="submit">Submit</button>
                        <button type="button" id="cancelBtn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Schedule Overlay -->
        <div class="overlay" id="editScheduleOverlay">
            <div class="overlay-content">
                <button class="close-btn" id="closeEModal">&times;</button>
                <h2>Edit Event</h2>
                <form id="editScheduleForm">
                    <div class="scrollable-content">
                        <label for="title">Title:</label>
                        <input type="text" id="edit_title" name="title" disabled>

                        <label for="description">Description:</label>
                        <textarea id="edit_description" name="description" rows="3"></textarea>

                        <label for="course_id">Course:</label>
                        <input type="text" id="course_id" name="course_id" disabled>

                        <label for="user_id">Lecturer In Charge:</label>
                        <input type="text" id="user_id" name="user_id" disabled>

                        <label for="schedule_type">Schedule Type:</label>
                        <select id="schedule_type" name="schedule_type" disabled>
                            <option value="">-- Select Schedule Type --</option>
                            <option value="Lecture">Lecture</option>
                            <option value="Lab">Lab</option>
                            <option value="Tutorial">Tutorial</option>
                            <option value="Exam">Exam</option>
                        </select>

                        <label for="start_date">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" disabled>

                        <label for="start_time">Start Time:</label>
                        <input type="time" id="start_time" name="start_time" disabled>

                        <label for="end_time">End Time:</label>
                        <input type="time" id="end_time" name="end_time" disabled>

                        <label for="room_id">Room:</label>
                        <input type="text" id="room_id" name="room_id" disabled>

                        <label for="recurrence_pattern">Recurrence Pattern:</label>
                        <select id="recurrence_pattern" name="recurrence_pattern" disabled>
                            <option value="">-- Select Recurrence Pattern --</option>
                            <option value="Never">Never</option>
                            <option value="Daily">Daily</option>
                            <option value="Weekly">Weekly</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Yearly">Yearly</option>
                        </select>

                        <label for="recurrence_end_date">Recurrence End Date:</label>
                        <input type="date" id="recurrence_end_date" name="recurrence_end_date" disabled>

                        <label for="created_by">Created By:</label>
                        <input type="text" id="created_by" name="created_by" disabled>                        
                    </div>
                    <div style="margin-top: 15px;">
                        <button type="submit">Submit</button>
                        <button type="button" id="cancelEBtn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Schedule Attendance Section -->
        <div class="card">
            <h2>Schedule Attendance</h2>
            <div class="BAS">
                <div>
                    <h3>Attendance Marking</h3>
                    <form id="AttendanceForm">
                        <label>Schedule:</label>
                        <select id="scheduleSelect" name="event" required>
                            <option value="">-- Select Schedule --</option>
                        </select>

                        <label>Student:</label>
                        <select id="userSelect" name="student" required>
                            <option value="">-- Select Student --</option>
                        </select>

                        <button type="submit">Mark Attendance</button>
                    </form>
                </div>
                <div>
                    <h3>Attendance</h3>
                    <table id="attendanceTable" class="scrollable-table">
                        <thead>
                            <tr>
                                <th>Schedule</th>
                                <th>Student</th>
                                <th>Attendance</th>
                                <th>Marked At</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Courses Section -->
        <div class="card">
            <h2>Courses</h2>
            <div class="BAS">
                <div>
                    <h3>Course Details</h3>
                    <form id="CoursesForm">
                        <label>Course Code:</label>
                        <input type="text" id="course_code" name="course_code" required>

                        <label>Course Name:</label>
                        <input type="text" id="course_name" name="course_name" required>

                        <label>Description:</label>
                        <input type="text" id="course_description" name="course_description" required>

                        <label>Faculty:</label>
                        <select id="course_faculty" name="course_faculty" required>
                            <option value="">-- Select Faculty --</option>
                            <option value="Information Technology">Information Technology</option>
                            <option value="Arts">Arts</option>
                            <option value="Education">Education</option>
                            <option value="Graduate Studies">Graduate Studies</option>
                            <option value="Indigenous Medicine">Indigenous Medicine</option>
                            <option value="Education">Education</option>
                            <option value="Law">Law</option>
                            <option value="Management & Finance">Management & Finance</option>
                            <option value="Medicine">Medicine</option>
                            <option value="Science">Science</option>
                            <option value="Technology">Technology</option>
                            <option value="Nursing">Nursing</option>
                        </select>

                        <button type="submit">Submit</button>
                    </form>
                </div>
                <div>
                    <h3>All Course</h3>
                    <table id="coursesTable">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Faculty</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <section id="calendar" class="card">
            <h2>Schedule Calendar</h2>
            <?php include 'PHP/ScheduleCalender.html'?>
        </section>

    </div>

    <?php include 'Footer.html'?>

    <?php
        if ($role === 'Student' || $role === 'Lecturer') {
        include 'Chats.php';
    }
    ?>
    
    <script>

        const openModalBtn = document.getElementById('openModal');
        const closeModalBtn = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const overlay = document.getElementById('overlay');
        const infoOverlay = document.getElementById('infoOverlay');
        const editScheduleOverlay = document.getElementById('editScheduleOverlay');
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
            editScheduleOverlay.style.display = 'none';
        });

        cancelEBtn.addEventListener('click', () => {
            editScheduleOverlay.style.display = 'none';
        });

        // Close Modal on Outside Click
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.style.display = 'none';
            }
        });

        editScheduleOverlay.addEventListener('click', (e) => {
            if (e.target === editScheduleOverlay) {
                editScheduleOverlay.style.display = 'none';
            }
        });

        infoOverlay.addEventListener('click', (e) => {
            if (e.target === infoOverlay) {
                infoOverlay.style.display = 'none';
            }
        });
        
        document.addEventListener("DOMContentLoaded", function () {
            const scheduleTableBody = document.querySelector("#scheduleTable tbody");

            // Fetch Schedules
            function fetchSchedules(searchTerm = "") {
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "PHP/Schedules.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);

                        if (response.success) {
                            const schedules = response.data;
                            scheduleTableBody.innerHTML = "";

                            schedules.forEach(schedule => {
                                const row = document.createElement("tr");

                                row.innerHTML = `
                                    <td data-label="Title">${schedule.title}</td>
                                    <td data-label="Description">${schedule.description}</td>
                                    <td data-label="Course Name">${schedule.course_name}</td>
                                    <td data-label="Start Time">${schedule.start_time}</td>
                                    <td data-label="End Time">${schedule.end_time}</td>
                                    <td data-label="Recurrence Pattern">${schedule.recurrence_pattern}</td>
                                    <td class="action-buttons">
                                        <button class="edit-btn" data-schedule-id="${schedule.schedule_id}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="remove-btn" data-schedule-id="${schedule.schedule_id}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <button class="info-btn" data-schedule-id="${schedule.schedule_id}">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                    </td>
                                `;

                                scheduleTableBody.appendChild(row);
                            });
                        } else {
                            console.error("Error fetching schedules:", response.message);
                        }
                    }
                };

                if (searchTerm.trim() === "") {
                    xhr.send("action=fetch_schedules");
                } else {
                    xhr.send(`action=fetch_schedules&search_term=${encodeURIComponent(searchTerm)}`);
                }
            }

            fetchSchedules();

            // Listen for input changes in the search bar
            const scheduleSearchInput = document.getElementById("scheduleSearch");
            scheduleSearchInput.addEventListener("input", function () {
                const searchTerm = scheduleSearchInput.value;
                fetchSchedules(searchTerm);
            });
        });

        document.addEventListener('click', function (event) {
            // Schedule Info
            if (event.target.closest('.info-btn')) {
                const scheduleId = event.target.closest('.info-btn').dataset.scheduleId;
                fetch('PHP/Schedules.php', { 
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=fetch_schedule&schedule_id=${scheduleId}`,
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data.length > 0) {
                            const schedule = data.data[0];
                            document.getElementById('infoTitle').textContent = schedule.title;
                            document.getElementById('infoDescription').textContent = schedule.description;
                            document.getElementById('infoCourseName').textContent = schedule.course_name;
                            document.getElementById('infoLecturerInCharge').textContent = schedule.user_name;
                            document.getElementById('infoScheduleType').textContent = schedule.schedule_type;
                            document.getElementById('infoStartDate').textContent = schedule.start_date;
                            document.getElementById('infoStartTime').textContent = schedule.start_time;
                            document.getElementById('infoEndTime').textContent = schedule.end_time;
                            document.getElementById('infoRoomName').textContent = schedule.room_name;
                            document.getElementById('infoIsRecurring').textContent = schedule.is_recurring ? 'Yes' : 'No';
                            document.getElementById('infoRecurrencePattern').textContent = schedule.recurrence_pattern || 'N/A';
                            document.getElementById('infoRecurrenceEndDate').textContent = schedule.recurrence_end_date || 'N/A';
                            document.getElementById('infoCreatedBy').textContent = schedule.created_by;
                            document.getElementById('infoCreatedAt').textContent = schedule.created_at;
                            document.getElementById('infoUpdatedAt').textContent = schedule.updated_at;

                            document.getElementById('infoOverlay').style.display = 'flex';
                        } else {
                            alert('Error fetching schedule details.');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            }

            if (event.target === closeOverlayButton || event.target.closest('#closeOverlay')) {
                infoOverlay.style.display = 'none';
            }
        });

        
        document.addEventListener('click', function (event) {
            // Delete Schedule
            const removeBtn = event.target.closest('.remove-btn');
            if (removeBtn) {
                const scheduleId = removeBtn.dataset.scheduleId;
                show('confirm');

                const yesButton = document.querySelector('.btns button:first-child');
                const noButton = document.querySelector('.btns button:last-child');

                const yesHandler = () => {
                    fetch('PHP/Schedules.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=delete_schedule&schedule_id=${scheduleId}`,
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                show('success');
                            } else {
                                alert('Error deleting schedule: ' + data.message);
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

        document.addEventListener("DOMContentLoaded", function () {
            const openModalBtn = document.getElementById('openModal');
            const courseSelect = document.getElementById('course_id');
            const lecturerSelect = document.getElementById('user_id');
            const roomSelect = document.getElementById('room_id');

            openModalBtn.addEventListener('click', () => {
                overlay.style.display = 'flex';

                // Fetch courses
                fetch('PHP/Courses.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ action: 'fetch' })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            courseSelect.innerHTML = '<option value="">-- Select Course --</option>';
                            data.data.forEach(course => {
                                const option = document.createElement('option');
                                option.value = course.course_id;
                                option.textContent = course.course_name;
                                courseSelect.appendChild(option);
                            });
                        } else {
                            console.error('Error fetching courses:', data.message);
                        }
                    })
                    .catch(error => console.error('Fetch error (Courses):', error));

                // Fetch lecturers (Administrators and Lecturers only)
                fetch('PHP/Users.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ action: 'fetch' })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            lecturerSelect.innerHTML = '<option value="">-- Select Lecturer --</option>';
                            data.data.forEach(user => {
                                const option = document.createElement('option');
                                option.value = user.user_id;
                                option.textContent = user.name;
                                lecturerSelect.appendChild(option);
                            });
                        } else {
                            console.error('Error fetching lecturers:', data.message);
                        }
                    })
                    .catch(error => console.error('Fetch error (Lecturers):', error));
                    

                // Fetch Rooms (Lecture Room, Lab, Conference Room only)
                fetch('PHP/Resources.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ action: 'fetch_rooms' })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            roomSelect.innerHTML = '<option value="">-- Select Room --</option>';
                            data.data.forEach(resource => {
                                const option = document.createElement('option');
                                option.value = resource.resource_id;
                                option.textContent = resource.resource_name;
                                roomSelect.appendChild(option);
                            });
                        } else {
                            console.error('Error fetching rooms:', data.message);
                        }
                    })
                    .catch(error => console.error('Fetch error (Rooms):', error));
            });
        });
        
        // Add New Schedule
        document.addEventListener("DOMContentLoaded", function () {
            const addScheduleForm = document.getElementById('addScheduleForm');

            addScheduleForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const formData = {
                    action: 'add_schedule',
                    title: document.getElementById('title').value,
                    description: document.getElementById('description').value,
                    course_id: document.getElementById('course_id').value,
                    user_id: document.getElementById('user_id').value,
                    schedule_type: document.getElementById('schedule_type').value,
                    start_date: document.getElementById('start_date').value,
                    start_time: document.getElementById('start_time').value,
                    end_time: document.getElementById('end_time').value,
                    room_id: document.getElementById('room_id').value,
                    recurrence_pattern: document.getElementById('recurrence_pattern').value,
                    recurrence_end_date: document.getElementById('recurrence_end_date').value,
                    created_by: document.getElementById('created_by').value
                };

                try {
                    const response = await fetch('PHP/Schedules.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams(formData).toString()
                    });

                    const result = await response.json();
                    if (result.success) {
                        overlay.style.display = 'none';
                        show('success');
                    } else {
                        alert('Error adding schedule: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while adding the schedule.');
                }
            });
        });

        // Get Data to Edit Schedule Form
        document.addEventListener('click', function (event) {
            const editBtn = event.target.closest('.edit-btn');
            if (editBtn) {
                const scheduleId = editBtn.dataset.scheduleId;

                fetch('PHP/Schedules.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=fetch_schedule&schedule_id=${scheduleId}`,
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data.length > 0) {
                            const schedule = data.data[0];

                            document.getElementById('editScheduleForm').querySelector('#edit_title').value = schedule.title;
                            document.getElementById('editScheduleForm').querySelector('#edit_description').value = schedule.description;
                            document.getElementById('editScheduleForm').querySelector('#course_id').value = schedule.course_name;
                            document.getElementById('editScheduleForm').querySelector('#user_id').value = schedule.user_name;
                            document.getElementById('editScheduleForm').querySelector('#schedule_type').value = schedule.schedule_type;
                            document.getElementById('editScheduleForm').querySelector('#start_date').value = schedule.start_date;
                            document.getElementById('editScheduleForm').querySelector('#start_time').value = schedule.start_time;
                            document.getElementById('editScheduleForm').querySelector('#end_time').value = schedule.end_time;
                            document.getElementById('editScheduleForm').querySelector('#room_id').value = schedule.room_name;
                            document.getElementById('editScheduleForm').querySelector('#recurrence_pattern').value = schedule.recurrence_pattern;
                            document.getElementById('editScheduleForm').querySelector('#recurrence_end_date').value = schedule.recurrence_end_date;
                            document.getElementById('editScheduleForm').querySelector('#created_by').value = schedule.created_by;

                            document.getElementById('editScheduleOverlay').style.display = 'flex';
                        } else {
                            alert('Error fetching schedule details.');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            }
        });

        // Edit Schedule
        document.addEventListener("DOMContentLoaded", function () {
            const updateScheduleForm = document.getElementById('editScheduleForm');
            updateScheduleForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const formData = {
                    action: 'update_description',
                    title: document.getElementById('edit_title').value,
                    description: document.getElementById('edit_description').value
                };

                console.log('Collected Form Data:', formData);

                try {
                    const response = await fetch('PHP/Schedules.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams(formData).toString()
                    });

                    const result = await response.json();
                    if (result.success) {
                        editScheduleOverlay.style.display = 'none';
                        show('success');
                    } else {
                        alert('Error updating description: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while updating the description.');
                }
            });
        });

        // Fetch Courses
        document.addEventListener("DOMContentLoaded", function () {
            const attendanceTableBody = document.querySelector("#coursesTable tbody");

            function fetchCourseDetails() {
                fetch('PHP/Courses.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ action: 'fetch' })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const courses = data.data;
                            attendanceTableBody.innerHTML = "";

                            courses.forEach(course => {
                                const row = document.createElement("tr");

                                row.innerHTML = `
                                    <td data-label="Code">${course.course_code}</td>
                                    <td data-label="Name">${course.course_name}</td>
                                    <td data-label="Description">${course.description}</td>
                                    <td data-label="Faculty">${course.faculty}</td>
                                    <td class="action-buttons">
                                        <button class="edit-course-btn" data-course-id="${course.course_id}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="remove-btn" data-course-id="${course.course_id}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                `;

                                attendanceTableBody.appendChild(row);
                            });
                        } else {
                            console.error('Error fetching course details:', data.message);
                        }
                    })
                    .catch(error => console.error('Fetch error (Course Details):', error));
            }

            fetchCourseDetails();
        });
        
        // Course Add and Update
        document.addEventListener("DOMContentLoaded", function () {
            const coursesForm = document.getElementById('CoursesForm');

            coursesForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const formData = {
                    action: 'add_course',
                    course_code: document.getElementById('course_code').value,
                    course_name: document.getElementById('course_name').value,
                    description: document.getElementById('course_description').value,
                    faculty: document.getElementById('course_faculty').value
                };

                console.log('Collected Form Data:', formData);

                try {
                    const response = await fetch('PHP/Courses.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(formData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        show('success');
                    } else if (result.message.includes('already exists')) {
                        const userConfirmed = confirm(result.message + '\nDo you want to update the existing record?');

                        if (userConfirmed) {
                            const updateFormData = {
                                action: 'update_course',
                                course_code: formData.course_code,
                                course_name: formData.course_name,
                                description: formData.description,
                                faculty: formData.faculty
                            };

                            const updateResponse = await fetch('PHP/Courses.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(updateFormData)
                            });

                            const updateResult = await updateResponse.json();

                            if (updateResult.success) {
                                show('success');
                            } else {
                                alert('Error updating course: ' + updateResult.message);
                            }
                        } else {
                            alert('Course was not updated.');
                        }
                    } else {
                        alert('Error adding course: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while processing the course.');
                }
            });
        });

        // Delete Course
        document.addEventListener('click', function (event) {
            const removeBtn = event.target.closest('.remove-btn');
            if (removeBtn && removeBtn.dataset.courseId) {
                const courseId = removeBtn.dataset.courseId;
                show('confirm');

                const yesButton = document.querySelector('.btns button:first-child');
                const noButton = document.querySelector('.btns button:last-child');

                const yesHandler = () => {
                    fetch('PHP/Courses.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete_course', course_id: courseId })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                show('success');
                            } else {
                                alert('Error deleting course: ' + data.message);
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
        
        // Get Data to Form on Edit Course Button Click
        document.addEventListener('click', function (event) {
            const editBtn = event.target.closest('.edit-course-btn');
            if (editBtn) {
                const courseId = editBtn.dataset.courseId;

                fetch('PHP/Courses.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'fetch_course_by_id', course_id: courseId }),
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data) {
                            const course = data.data;

                            document.getElementById('CoursesForm').querySelector('#course_code').value = course.course_code;
                            document.getElementById('CoursesForm').querySelector('#course_name').value = course.course_name;
                            document.getElementById('CoursesForm').querySelector('#course_description').value = course.description;
                            document.getElementById('CoursesForm').querySelector('#course_faculty').value = course.faculty;

                            document.getElementById('editCourseOverlay').style.display = 'flex';
                        } else {
                            alert('Error fetching course details.');
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            }
        });

        // Fetch Data for Users and Schedules
        document.addEventListener('DOMContentLoaded', function () {
            const scheduleSelect = document.getElementById('scheduleSelect');
            const userSelect = document.getElementById('userSelect');

            // Fetch Schedules
            fetch('PHP/Schedules.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=fetch_schedules_for_attendance'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    scheduleSelect.innerHTML = '<option value="">-- Select Schedule --</option>';
                    data.data.forEach(schedule => {
                        const option = document.createElement('option');
                        option.value = schedule.schedule_id;
                        option.textContent = schedule.title;
                        scheduleSelect.appendChild(option);
                    });
                } else {
                    console.error('Error fetching schedules:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));

            // Fetch active students
            fetch('PHP/Users.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'fetch_active_students' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    userSelect.innerHTML = '<option value="">-- Select Student --</option>';
                    data.data.forEach(student => {
                        const option = document.createElement('option');
                        option.value = student.user_id;
                        option.textContent = student.name;
                        userSelect.appendChild(option);
                    });
                } else {
                    console.error('Error fetching students:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });

        // Attendance Marking
        document.getElementById('AttendanceForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const scheduleId = document.getElementById('scheduleSelect').value;
            const userId = document.getElementById('userSelect').value;

            if (!scheduleId || !userId) {
                alert('Please fill in all fields.');
                return;
            }

            try {
                const response = await fetch('PHP/ScheduleAttendance.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=mark_attendance&schedule_id=${scheduleId}&user_id=${userId}`
                });
                const result = await response.json();

                if (result.success) {
                    show('success');
                } else {
                    alert('Error marking attendance: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while marking attendance.');
            }
        });

        // Fetch Attendance Data
        document.addEventListener("DOMContentLoaded", function () {
            const attendanceTableBody = document.querySelector("#attendanceTable tbody");

            function fetchAttendanceDetails() {
                fetch('PHP/ScheduleAttendance.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=fetch'  
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const class_attendance = data.data;
                            attendanceTableBody.innerHTML = "";

                            class_attendance.forEach(class_attendance => {
                                const row = document.createElement("tr");

                                row.innerHTML = `
                                    <td data-label="Code">${class_attendance.schedule_title}</td>
                                    <td data-label="Name">${class_attendance.student_name}</td>
                                    <td data-label="Description">${class_attendance.attendance_status}</td>
                                    <td data-label="Faculty">${class_attendance.attended_at}</td>
                                `;

                                attendanceTableBody.appendChild(row);
                            });
                        } else {
                            console.error('Error fetching course details:', data.message);
                        }
                    })
                    .catch(error => console.error('Fetch error (Course Details):', error));
            }

            fetchAttendanceDetails();
        });

    </script>
</body>
</html>