<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="Images/Icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/Styles.css">
</head>
<body>
    <?php include 'NavBar.php'?>

    <?php include 'PHP/checkAdmin.php'?>

    <?php include 'Message.html'?>

    <header>
        Course Management
    </header>

    <div class="container">

        <section id="overview" class="card">
            <h2>Courses Overview</h2>
            <?php include 'CourseCharts.html'?>
        </section>

        <div class="card">
            <h2>Course Enrollments</h2>
            <div class="BAS">
                <div>
                    <h3>Course Enrollment</h3>
                    <form id="EnrollmentForm">
                        <label>Course:</label>
                        <select id="courseSelect" name="course" required>
                            <option value="">-- Select Course --</option>
                        </select>

                        <label>Student:</label>
                        <select id="userSelect" name="user" required>
                            <option value="">-- Select Student --</option>
                        </select>

                        <button type="submit">Enroll</button>
                    </form>
                </div>
                <div>
                    <h3>Pending Enrollments</h3>
                    <div class="RLM-Top" style="margin-top: 15px;">
                        <input type="text" id="pendingEnrollments" placeholder="Search by student name or course..." />
                    </div>
                    <table id="pendingEnrollmentsTable">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Student</th>
                                <th>Enrollment Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>

            <br>

            <h3>Dropped Enrollments</h3>
            <div class="RLM-Top">
                <input type="text" id="droppedEnrollments" placeholder="Search by student name or course..." />
            </div>
            <table id="droppedEnrollmentsTable">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Student</th>
                        <th>Enrollment Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>Active Course Enrollments Listings & Management</h2>
            <div class="RLM-Top">
                <input type="text" id="activeEnrollments" placeholder="Search by student name or course..." />
            </div>
            <table id="activeEnrollmentsTable">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Student</th>
                        <th>Enrollment Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'Footer.html'?>

    <script>

        document.addEventListener('DOMContentLoaded', function() {
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
                    const courseSelect = document.getElementById('courseSelect');
                    data.data.forEach(course => {
                        const option = document.createElement('option');
                        option.value = course.course_id;
                        option.textContent = course.course_name;
                        courseSelect.appendChild(option);
                    });
                } else {
                    console.error('Failed to fetch courses:', data.message);
                }
            })
            .catch(error => console.error('Error fetching courses:', error));

            // Fetch students
            fetch('PHP/Users.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'fetch_students' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const userSelect = document.getElementById('userSelect');
                    data.data.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.user_id;
                        option.textContent = user.name;
                        userSelect.appendChild(option);
                    });
                } else {
                    console.error('Failed to fetch students:', data.message);
                }
            })
            .catch(error => console.error('Error fetching students:', error));

            // Handle enrollment form submission
            const enrollmentForm = document.getElementById('EnrollmentForm');
            enrollmentForm.addEventListener('submit', function(event) {
                event.preventDefault();

                const courseId = document.getElementById('courseSelect').value;
                const studentId = document.getElementById('userSelect').value;

                if (!courseId || !studentId) {
                    alert('Please select both a course and a student.');
                    return;
                }

                fetch('PHP/Enrollments.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'enroll',
                        course_id: courseId,
                        student_id: studentId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        show('success');
                    } else {
                        alert('Enrollment failed: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error during enrollment:', error);
                    alert('An error occurred during enrollment.');
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Fetch and display active enrollments
            function fetchActiveEnrollments(search = '') {
                fetch('PHP/Enrollments.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'fetch_active_enrollments',
                        search: search
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tableBody = document.querySelector('#activeEnrollmentsTable tbody');
                        tableBody.innerHTML = '';

                        data.data.forEach(enrollment => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td data-label="Course">${enrollment.course_name}</td>
                                <td data-label="Student">${enrollment.name}</td>
                                <td data-label="Enrollment Date">${enrollment.enrollment_date}</td>
                                <td data-label="Status">${enrollment.status}</td>
                                <td class="action-buttons">
                                    <button class="remove-btn" data-course-id="${enrollment.course_id}" data-student-id="${enrollment.student_id}">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </td>
                            `;
                            tableBody.appendChild(row);
                        });
                    } else {
                        console.error('Failed to fetch active enrollments:', data.message);
                    }
                })
                .catch(error => console.error('Error fetching active enrollments:', error));
            }

            fetchActiveEnrollments();

            // Handle search input
            const searchInput = document.getElementById('activeEnrollments');
            searchInput.addEventListener('input', function() {
                fetchActiveEnrollments(this.value);
            });

            // Event delegation for the "Drop" button
            document.addEventListener('click', function(event) {
                if (event.target.closest('.remove-btn')) {
                    const button = event.target.closest('.remove-btn');
                    const courseId = button.getAttribute('data-course-id');
                    const studentId = button.getAttribute('data-student-id');

                    if (confirm('Are you sure you want to drop this enrollment?')) {
                        fetch('PHP/Enrollments.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                action: 'drop_enrollment',
                                course_id: courseId,
                                student_id: studentId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                show('success');
                            } else {
                                alert('Failed to drop enrollment: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error dropping enrollment:', error);
                            alert('An error occurred while dropping the enrollment.');
                        });
                    }
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Fetch and display dropped enrollments
            function fetchDroppedEnrollments(search = '') {
                fetch('PHP/Enrollments.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'fetch_dropped_enrollments',
                        search: search
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tableBody = document.querySelector('#droppedEnrollmentsTable tbody');
                        tableBody.innerHTML = '';

                        data.data.forEach(enrollment => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td data-label="Course">${enrollment.course_name}</td>
                                <td data-label="Student">${enrollment.name}</td>
                                <td data-label="Enrollment Date">${enrollment.enrollment_date}</td>
                                <td data-label="Status">${enrollment.status}</td>
                                <td class="action-buttons">
                                    <button class="activate-btn" data-course-id="${enrollment.course_id}" data-student-id="${enrollment.student_id}">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </td>
                            `;
                            tableBody.appendChild(row);
                        });
                    } else {
                        console.error('Failed to fetch active enrollments:', data.message);
                    }
                })
                .catch(error => console.error('Error fetching active enrollments:', error));
            }

            fetchDroppedEnrollments();

            const searchInput = document.getElementById('droppedEnrollments');
            searchInput.addEventListener('input', function() {
                fetchDroppedEnrollments(this.value);
            });

            // Reactivate enrollments
            document.addEventListener('click', function(event) {
                if (event.target.closest('.activate-btn')) {
                    const button = event.target.closest('.activate-btn');
                    const courseId = button.getAttribute('data-course-id');
                    const studentId = button.getAttribute('data-student-id');

                    if (confirm('Are you sure you want to activate this enrollment?')) {
                        fetch('PHP/Enrollments.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                action: 'reactivate_enrollment',
                                course_id: courseId,
                                student_id: studentId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                show('success');
                            } else {
                                alert('Failed to activate enrollment: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error activating enrollment:', error);
                            alert('An error occurred while activating the enrollment.');
                        });
                    }
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Fetch and display active enrollments
            function fetchPendingEnrollments(search = '') {
                fetch('PHP/Enrollments.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'fetch_pending_enrollments',
                        search: search
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tableBody = document.querySelector('#pendingEnrollmentsTable tbody');
                        tableBody.innerHTML = '';

                        data.data.forEach(enrollment => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td data-label="Course">${enrollment.course_name}</td>
                                <td data-label="Student">${enrollment.name}</td>
                                <td data-label="Enrollment Date">${enrollment.enrollment_date}</td>
                                <td data-label="Status">${enrollment.status}</td>
                                <td class="action-buttons">
                                    <button class="activate-btn" data-course-id="${enrollment.course_id}" data-student-id="${enrollment.student_id}">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                    <button class="remove-btn" data-course-id="${enrollment.course_id}" data-student-id="${enrollment.student_id}">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </td>
                            `;
                            tableBody.appendChild(row);
                        });
                    } else {
                        console.error('Failed to fetch pending enrollments:', data.message);
                    }
                })
                .catch(error => console.error('Error fetching pending enrollments:', error));
            }

            fetchPendingEnrollments();

            // Handle search input
            const searchInput = document.getElementById('pendingEnrollments');
            searchInput.addEventListener('input', function() {
                fetchPendingEnrollments(this.value);
            });
        });

    </script>
</body>
</html>