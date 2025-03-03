<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="Images/Icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/Styles.css">
</head>
<body>
    <?php include 'NavBar.php'?>

    <?php include 'PHP/checkLecturer.php'?>
    
    <?php include 'Message.html'?>
    
    <header>
        Task Management
    </header>

    <div class="container">

        <section id="overview" class="card">
            <h2>Tasks Overview</h2>
            <?php include 'TaskCharts.html'?>
        </section>

        <div class="card">
            <h2>Tasks</h2>

            <!-- Tasks Section -->
            <div class="BAS">
                <div>
                    <h3>Task Details</h3>
                    <form id="TaskForm">
                        <label>Title:</label>
                        <input type="text" id="title" name="title" required>

                        <label>Description:</label>
                        <input type="text" id="description" name="description" required>

                        <label>Type:</label>
                        <select id="type" name="type" required>
                            <option value="">-- Select Task Type --</option>
                            <option value="Quiz">Quiz</option>
                            <option value="Tutorial">Tutorial</option>
                            <option value="Assignment">Assignment</option>
                            <option value="Other">Other</option>
                        </select>

                        <label>Course:</label>
                        <select id="course" name="course" required>
                            <option value="">-- Select Course --</option>
                        </select>

                        <label>Created By:</label>
                        <input type="text" id="created_by" name="created_by" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" required readonly>

                        <label>Deadline:</label>
                        <input type="datetime-local" id="deadline" name="deadline" required>

                        <label>Attachments:</label>
                        <div class="file-upload-container">
                            <label for="task_file" class="file-upload-label">Select Files</label>
                            <input type="file" id="task_file" name="task_file[]" multiple />
                            <span id="selectedFiles" class="file-upload-info">No files chosen</span>
                        </div>

                        <button type="submit">Submit</button>
                    </form>
                </div>
                <div>
                    <h3>All Tasks</h3>
                    <br>
                    <div class="RLM-Top">
                        <input type="text" id="taskSearch" placeholder="Search tasks by title, course, or creator..." />
                    </div>
                    <table id="tasksTable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Course</th>
                                <th>Created By</th>
                                <th>Deadline</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Task Submissions Section -->
        <div class="card">
            <h2>Task Submissions</h2>

            <div class="RLM-Top">
                <input type="text" id="submissionSearch" placeholder="Search submissions by user or course..." />
                <select id="submissionFilter" name="type" required>
                    <option value="">Filter By Grading</option>
                    <option value="Not Graded">Not Graded</option>
                    <option value="Distinction">Distinction</option>
                    <option value="Merit">Merit</option>
                    <option value="Pass">Pass</option>
                </select>
            </div>

            <table id="tasksSubmissionsTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Student</th>
                        <th>Submission</th>
                        <th>Submission Time</th>
                        <th>Grade</th>
                        <th>Graded By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>
        </div>

        <!-- Grading Overlay -->
        <div class="overlay" id="overlay">
            <div class="overlay-content">
                <button class="close-btn" id="closeModal">&times;</button>
                <h2>Update Grade</h2>
                <form id="GradingForm">
                    <div class="scrollable-content">
                        <label for="utitle">Title:</label>
                        <input type="text" id="utitle" name="utitle" required readonly>

                        <label for="student">Student:</label>
                        <input type="text" id="student" name="student" required readonly>

                        <label for="submittedFile">Submission:</label>
                        <div class="file-upload-container">
                            <span id="submittedFile" class="submittedFile" style="margin-top: 10px;">No files chosen</span>
                        </div>
                        <br>
                        <label for="submitted_time">Submitted Time:</label>
                        <input type="text" id="submitted_time" name="submitted_time" required readonly>

                        <label for="grade">Grade:</label>
                        <select id="grade" name="grade" required>
                            <option value="">Grading</option>
                            <option value="Not Graded">Pending</option>
                            <option value="Distinction">Distinction</option>
                            <option value="Merit">Merit</option>
                            <option value="Pass">Pass</option>
                        </select>

                        <label>Graded By:</label>
                        <input type="text" id="graded_by_name" name="graded_by_name" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" required readonly>
                        <input type="hidden" id="graded_by" name="graded_by" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>">
                    </div>
                    <div style="margin-top: 15px;">
                        <button type="submit">Submit</button>
                        <button type="button" id="cancelBtn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include 'Footer.html'?>
    
    <?php include 'Chats.php'?>
    
    <script>

        document.getElementById('task_file').addEventListener('change', function () {
            const fileNames = this.files.length > 0
                ? Array.from(this.files).map(file => file.name).join(', ')
                : 'No files chosen';
            document.getElementById('selectedFiles').textContent = fileNames;
        });

        // Fetch Courses
        document.addEventListener("DOMContentLoaded", function () {
            const courseSelect = document.getElementById('course');

            function fetchAndPopulateCourses() {
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
            }
            fetchAndPopulateCourses();
        });


        // Add or Update Task
        document.addEventListener("DOMContentLoaded", function () {
            const taskForm = document.getElementById('TaskForm');

            taskForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const formData = new FormData(taskForm);
                const taskId = taskForm.dataset.taskId;

                if (taskId) {
                    formData.append('action', 'update_task');
                    formData.append('task_id', taskId);
                } else {
                    formData.append('action', 'add_task');
                }

                try {
                    const response = await fetch('PHP/Tasks.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        taskForm.reset();
                        taskForm.querySelector('button[type="submit"]').textContent = 'Submit';
                        delete taskForm.dataset.taskId;

                        show('success');
                    } else if (result.message.includes('already exists')) {
                        const userConfirmed = confirm(result.message + '\nDo you want to update the existing task?');

                        if (userConfirmed) {
                            formData.append('action', 'update_task');
                            formData.append('task_id', result.existing_task.task_id);

                            const updateResponse = await fetch('PHP/Tasks.php', {
                                method: 'POST',
                                body: formData
                            });

                            const updateResult = await updateResponse.json();

                            if (updateResult.success) {
                                taskForm.reset();
                                taskForm.querySelector('button[type="submit"]').textContent = 'Submit';
                                delete taskForm.dataset.taskId;

                                show('success');
                            } else {
                                alert('Error updating task: ' + updateResult.message);
                            }
                        } else {
                            alert('Task was not updated.');
                        }
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while processing the task.');
                }
            });
        });

        // Fetch Tasks
        document.addEventListener("DOMContentLoaded", function () {
            const taskSearch = document.getElementById('taskSearch');
            const tasksTableBody = document.querySelector('#tasksTable tbody');

            function fetchAndDisplayTasks(search = '') {
                const formData = new URLSearchParams();
                formData.append('action', 'fetch_tasks');
                formData.append('search', search);

                console.log('Sending payload:', formData.toString());

                fetch('PHP/Tasks.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData.toString()
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        tasksTableBody.innerHTML = '';
                        data.data.forEach(task => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td data-label="Title">${task.title}</td>
                                <td data-label="Type">${task.task_type}</td>
                                <td data-label="Course">${task.course_name}</td>
                                <td data-label="Created By">${task.created_by}</td>
                                <td data-label="Deadline">${task.deadline}</td>
                                <td class="action-buttons">
                                    <button class="edit-btn" data-task-id="${task.task_id}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="remove-btn" data-task-id="${task.task_id}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            `;
                            tasksTableBody.appendChild(row);
                        });
                    } else {
                        console.error('Error fetching tasks:', data.message);
                    }
                })
                .catch(error => console.error('Fetch error (Tasks):', error));
            }

            fetchAndDisplayTasks();

            taskSearch.addEventListener('input', function () {
                const searchTerm = this.value.trim();
                fetchAndDisplayTasks(searchTerm);
            });

            // Delete Task
            document.addEventListener('click', function (event) {
                const removeBtn = event.target.closest('.remove-btn');
                if (removeBtn) {
                    const taskId = removeBtn.dataset.taskId;
                    show('confirm');
                    const yesButton = document.querySelector('.btns button:first-child');
                    const noButton = document.querySelector('.btns button:last-child');

                    const yesHandler = () => {
                        const formData = new URLSearchParams();
                        formData.append('action', 'delete_task');
                        formData.append('task_id', taskId);

                        fetch('PHP/Tasks.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: formData.toString()
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                show('success');
                            } else {
                                alert('Error deleting task: ' + data.message);
                            }
                        })
                        .catch(error => console.error('Delete error:', error));

                        hide();
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

        // Fetch Task Details on Edit Button Click
        document.addEventListener('click', function (event) {
            const editBtn = event.target.closest('.edit-btn');
            if (editBtn) {
                const taskId = editBtn.dataset.taskId;

                const formData = new URLSearchParams();
                formData.append('action', 'fetch_task');
                formData.append('task_id', taskId);

                fetch('PHP/Tasks.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData.toString()
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const task = data.data;
                        document.getElementById('title').value = task.title;
                        document.getElementById('description').value = task.description;
                        document.getElementById('type').value = task.task_type;
                        document.getElementById('course').value = task.course_id;
                        document.getElementById('created_by').value = task.created_by;
                        document.getElementById('deadline').value = task.deadline.replace(' ', 'T');

                        const selectedFiles = document.getElementById('selectedFiles');
                        if (task.file_path && task.file_path !== '-') {
                            const fileName = task.file_path.split('/').pop();
                            selectedFiles.innerHTML = `
                                <span>${fileName}</span>
                                <a href="${task.file_path}" download>Download</a>
                            `;
                        } else {
                            selectedFiles.textContent = 'No files chosen';
                        }
                    } else {
                        alert('Error fetching task details: ' + data.message);
                    }
                })
                .catch(error => console.error('Fetch error (Task Details):', error));
            }
        });

        // Fetch Submissions
        document.addEventListener("DOMContentLoaded", function () {
            const submissionSearch = document.getElementById('submissionSearch');
            const submissionFilter = document.getElementById('submissionFilter');
            const submissionsTableBody = document.querySelector('#tasksSubmissionsTable tbody');

            function fetchAndDisplaySubmissions(search = '', filter = '') {
                const formData = new URLSearchParams();
                formData.append('action', 'fetch_submissions');
                formData.append('search', search);
                formData.append('filter', filter);

                fetch('PHP/TaskSubmissions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData.toString()
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        submissionsTableBody.innerHTML = '';
                        data.data.forEach(submission => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td data-label="Title">${submission.task_title}</td>
                                <td data-label="Student">${submission.student_name}</td>
                                <td data-label="Submission">
                                    <a href="${submission.submission}" download>Download</a>
                                </td>
                                <td data-label="Submission Time">${submission.submitted_at}</td>
                                <td data-label="Grade">${submission.grade || 'Not Graded'}</td>
                                <td data-label="Graded By">${submission.graded_by_name || 'Not Graded'}</td>
                                <td class="action-buttons">
                                    <button class="grade-btn" data-submission-id="${submission.submission_id}">
                                        <i class="fas fa-file-alt"></i>
                                    </button>
                                </td>
                            `;
                            submissionsTableBody.appendChild(row);
                        });
                    } else {
                        console.error('Error fetching submissions:', data.message);
                    }
                })
                .catch(error => console.error('Fetch error (Submissions):', error));
            }

            fetchAndDisplaySubmissions();

            submissionSearch.addEventListener('input', function () {
                const searchTerm = this.value.trim();
                const filterValue = submissionFilter.value;
                fetchAndDisplaySubmissions(searchTerm, filterValue);
            });

            submissionFilter.addEventListener('change', function () {
                const filterValue = this.value;
                const searchTerm = submissionSearch.value.trim();
                fetchAndDisplaySubmissions(searchTerm, filterValue);
            });
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.style.display = 'none';
            }
        });

        // Fetch Submission Info
        document.addEventListener("DOMContentLoaded", function () {
            const overlay = document.getElementById('overlay');
            const closeModal = document.getElementById('closeModal');
            const cancelBtn = document.getElementById('cancelBtn');
            const gradingForm = document.getElementById('GradingForm');
            const submissionsTableBody = document.querySelector('#tasksSubmissionsTable tbody');

            function showOverlay() {
                overlay.style.display = 'flex';
            }

            function hideOverlay() {
                overlay.style.display = 'none';
            }

            closeModal.addEventListener('click', hideOverlay);
            cancelBtn.addEventListener('click', hideOverlay);

            submissionsTableBody.addEventListener('click', function (event) {
                const gradeBtn = event.target.closest('.grade-btn');
                if (gradeBtn) {
                    const submissionId = gradeBtn.dataset.submissionId;

                    fetch('PHP/TaskSubmissions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `action=fetch_submission&submission_id=${submissionId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const submission = data.data;

                            document.getElementById('utitle').value = submission.task_title;
                            document.getElementById('student').value = submission.student_name;
                            const submittedFileElement = document.getElementById('submittedFile');
                            const fileName = submission.submission.split('/').pop();
                            submittedFileElement.innerHTML = `<a href="${submission.submission}" download>${fileName}</a>`;
                            document.getElementById('submitted_time').value = submission.submitted_at;
                            document.getElementById('grade').value = submission.grade || '';

                            showOverlay();
                        } else {
                            alert('Error fetching submission details: ' + data.message);
                        }
                    })
                    .catch(error => console.error('Fetch error (Submission Details):', error));
                }
            });

            // Update Grade
            GradingForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(GradingForm);
                formData.append('action', 'update_grade');

                fetch('PHP/TaskSubmissions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        overlay.style.display = 'none';
                        show('success');
                    } else {
                        alert('Error updating grade: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });

        GradingForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const submissionId = document.querySelector('.grade-btn').dataset.submissionId;

            const formData = new FormData(GradingForm);
            formData.append('action', 'update_grade');
            formData.append('submission_id', submissionId);

            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }

            fetch('PHP/TaskSubmissions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    overlay.style.display = 'none';
                    show('success');
                } else {
                    alert('Error updating grade: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });

    </script>

</body>
</html>