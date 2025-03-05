<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="Images/Icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/Styles.css">
</head>
<body>
    <?php include 'NavBar.php'?>

    <?php include 'PHP/checkStudent.php'?>

    <?php include 'Message.html'?>
    
    <header>
        Tasks
    </header>

    <div class="container">

        <!-- Pending Tasks Section -->
        <div class="card">
            <header>Pending Tasks</header>
            <div class="data-grid" id="tasks-without-submissions-container">
                
            </div>
        </div>

        <!-- All Tasks Section -->
        <div class="card">
            <header>All Tasks</header>
            <div class="data-grid"  id="tasks-container">
                
            </div>
        </div>

        <!-- Submit Overlay -->
        <div class="overlay" id="overlay">
            <div class="overlay-content">
                <button class="close-btn" id="closeModal">&times;</button>
                <h2 id="submissionTitle">Submission</h2>
                <form id="submissionForm">
                    <div class="scrollable-content">
                        <label for="title">Title:</label>
                        <input type="text" id="title" name="title" required readonly>

                        <label for="studentNameInput">Student:</label>
                        <input type="text" id="studentName" name="studentName" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" required readonly>
                        <input type="hidden" id="studentID" name="studentID" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>">

                        <label for="submissionFiles">Attach Files & Resources:</label>
                        <div class="file-upload-container">
                            <label for="submissionFiles" class="file-upload-label">Select Files</label>
                            <input type="file" id="submissionFiles" name="files" multiple hidden/>
                            <span id="selectedFiles" class="file-upload-info">No files chosen</span>
                        </div>
                    </div>
                    <div style="margin-top: 15px;">
                        <button type="submit">Submit</button>
                        <button type="button" id="cancelBtn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <?php include 'PHP/TasksCalendar.php'?>
        </div>
    </div>
            
    <?php include 'Footer.html'?>
    
    <?php include 'Chats.php'?>
    
    <script>

        let currentTaskID = null;


        document.addEventListener('DOMContentLoaded', function () {
            const overlay = document.getElementById('overlay');
            const closeModal = document.getElementById('closeModal');
            const cancelBtn = document.getElementById('cancelBtn');
            const submissionTitle = document.getElementById('submissionTitle');
            const submissionTitleInput = document.getElementById('title');
            const tasksContainer = document.getElementById('tasks-container');
            const tasksWithoutSubmissionsContainer = document.getElementById('tasks-without-submissions-container');
            const studentID = document.getElementById('studentID').value;
            const userId = <?php echo json_encode($_SESSION['user_id']); ?>;


            // Check Submission
            function showOverlay(taskTitle, taskID) {
                currentTaskID = taskID;
                fetch('PHP/TaskSubmissions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=check_submission&task_id=${taskID}&student_id=${studentID}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.submitted) {
                            submissionTitle.textContent = `Already Submitted for: ${taskTitle}`;
                        } else {
                            submissionTitle.textContent = `Not Submitted for: ${taskTitle}`;
                        }
                    } else {
                        submissionTitle.textContent = `Submission for: ${taskTitle}`;
                        console.error('Error fetching submission status:', data.message);
                    }
                })
                .catch(error => {
                    submissionTitle.textContent = `Submission for: ${taskTitle}`;
                    console.error('Error:', error);
                });

                submissionTitleInput.value = taskTitle;
                overlay.style.display = 'flex';
            }

            function hideOverlay() {
                overlay.style.display = 'none';
            }

            closeModal.addEventListener('click', hideOverlay);
            cancelBtn.addEventListener('click', hideOverlay);

            // Fetch Tasks for User
            fetch('PHP/Tasks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=fetch_related_tasks&user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                console.log('Data received:', data);
                if (data.success) {
                    data.data.forEach(task => {
                        // Check if the user has submitted for this task
                        fetch('PHP/TaskSubmissions.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `action=check_submission&task_id=${task.task_id}&student_id=${studentID}`
                        })
                        .then(response => response.json())
                        .then(submissionData => {
                            const isSubmitted = submissionData.success && submissionData.submitted;

                            const taskBox = document.createElement('div');
                            taskBox.className = 'data-box';
                            taskBox.innerHTML = `
                                <div class="data-row">
                                    <div class="data-label">Title:</div>
                                    <div class="data-value">${task.title}</div>
                                </div>
                                <div class="data-row">
                                    <div class="data-label">Description:</div>
                                    <div class="data-value">${task.description}</div>
                                </div>
                                <div class="data-row">
                                    <div class="data-label">Type:</div>
                                    <div class="data-value">${task.task_type}</div>
                                </div>
                                <div class="data-row">
                                    <div class="data-label">File:</div>
                                    <div class="data-value"><a href="${task.file_path}" download>Download</a></div>
                                </div>
                                <div class="data-row">
                                    <div class="data-label">Course:</div>
                                    <div class="data-value">${task.course_name}</div>
                                </div>
                                <div class="data-row">
                                    <div class="data-label">Deadline:</div>
                                    <div class="data-value">${task.deadline}</div>
                                </div>
                                <div class="data-row">
                                    <div class="data-label">Status:</div>
                                    <div class="data-value">
                                        ${new Date(task.deadline) > new Date() ? 'Open' : 'Deadline Exceeded'}
                                    </div>
                                </div>
                                ${new Date(task.deadline) > new Date() ? `
                                <div class="data-row-btn">
                                    <button class="submit-btn" data-task-id="${task.task_id}" data-task-title="${task.title}">
                                        <i class="fas fa-clipboard-list"></i>
                                    </button>
                                </div>
                                ` : ''}
                            `;

                            if (!isSubmitted) {
                                tasksWithoutSubmissionsContainer.appendChild(taskBox);
                            }
                            tasksContainer.appendChild(taskBox.cloneNode(true));

                            const submitButtons = document.querySelectorAll('.submit-btn');
                    submitButtons.forEach(button => {
                        button.addEventListener('click', function () {
                            const taskTitle = this.getAttribute('data-task-title');
                            const taskID = this.getAttribute('data-task-id');
                            showOverlay(taskTitle, taskID);
                        });
                    });
                        })
                        .catch(error => console.error('Error checking submission:', error));
                    });
                } else {
                    alert('Failed to fetch tasks: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });

        document.getElementById('submissionFiles').addEventListener('change', function () {
            const fileNames = this.files.length > 0
                ? Array.from(this.files).map(file => file.name).join(', ')
                : 'No files chosen';
            document.getElementById('selectedFiles').textContent = fileNames;
        });

        // Save Submission
        document.getElementById('submissionForm').addEventListener('submit', function (e) {
            e.preventDefault();

            if (!currentTaskID) {
                alert('No task selected.');
                return;
            }

            const studentID = document.getElementById('studentID').value;
            const files = document.getElementById('submissionFiles').files;

            if (files.length === 0) {
                alert('Please select a file to upload.');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'submit_task');
            formData.append('task_id', currentTaskID);
            formData.append('student_id', studentID);

            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
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
                    alert('Submission failed: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting the task.');
            });
        });
    </script>

</body>
</html>