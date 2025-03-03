<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="Images/Icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/Styles.css">
</head>
<body>
    <?php include 'NavBar.php'?>

    <?php include 'PHP/checkStudent.php'?>

    <?php include 'Message.html'?>
    
    <header>
        Courses
    </header>

    <div class="container">
        <div class="card">
            <header>Enrolled Courses</header>
            <div class="data-grid" id="enrolled-courses-container">
                
            </div>
        </div>

        <div class="card">
            <header>All Courses</header>
            <div class="data-grid" id="courses-container">
                
            </div>
        </div>
    </div>
            
    <?php include 'Footer.html'?>
    
    <?php include 'Chats.php'?>
    
    <script>

        document.addEventListener('DOMContentLoaded', function () {
            const enrolledCoursesContainer = document.getElementById('enrolled-courses-container');
            const coursesContainer = document.getElementById('courses-container');
            const studentId = <?php echo json_encode($_SESSION['user_id']); ?>;

            fetch('PHP/Enrollments.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'fetch_enrolled_courses',
                    student_id: studentId
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Enrolled courses data received:', data);
                if (data.success) {
                    data.data.forEach(course => {
                        const courseBox = document.createElement('div');
                        courseBox.className = 'data-box';
                        courseBox.innerHTML = `
                            <div class="data-row">
                                <div class="data-label">Course Code:</div>
                                <div class="data-value">${course.course_code}</div>
                            </div>
                            <div class="data-row">
                                <div class="data-label">Course Name:</div>
                                <div class="data-value">${course.course_name}</div>
                            </div>
                            <div class="data-row">
                                <div class="data-label">Description:</div>
                                <div class="data-value">${course.description}</div>
                            </div>
                            <div class="data-row">
                                <div class="data-label">Faculty:</div>
                                <div class="data-value">${course.faculty}</div>
                            </div>
                            <div class="data-row">
                                <div class="data-label">Enrollment Date:</div>
                                <div class="data-value">${course.enrollment_date}</div>
                            </div>
                            <div class="data-row">
                                <div class="data-label">Status:</div>
                                <div class="data-value">${course.status}</div>
                            </div>
                        `;
                        enrolledCoursesContainer.appendChild(courseBox);
                    });
                } else {
                    console.error('Failed to fetch enrolled courses:', data.message);
                }
            })
            .catch(error => console.error('Error fetching enrolled courses:', error));

            fetch('PHP/Courses.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'fetch' })
            })
            .then(response => response.json())
            .then(data => {
                console.log('All courses data received:', data);
                if (data.success) {
                    data.data.forEach(course => {
                        const courseBox = document.createElement('div');
                        courseBox.className = 'data-box';

                        fetch('PHP/Enrollments.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                action: 'check_enrollment',
                                course_id: course.course_id,
                                student_id: studentId
                            })
                        })
                        .then(response => response.json())
                        .then(enrollmentData => {
                            const isEnrolled = enrollmentData.success && enrollmentData.enrolled;

                            courseBox.innerHTML = `
                                <div class="data-row">
                                    <div class="data-label">Course Code:</div>
                                    <div class="data-value">${course.course_code}</div>
                                </div>
                                <div class="data-row">
                                    <div class="data-label">Course Name:</div>
                                    <div class="data-value">${course.course_name}</div>
                                </div>
                                <div class="data-row">
                                    <div class="data-label">Description:</div>
                                    <div class="data-value">${course.description}</div>
                                </div>
                                <div class="data-row">
                                    <div class="data-label">Faculty:</div>
                                    <div class="data-value">${course.faculty}</div>
                                </div>
                                <div class="data-row">
                                    <div class="data-label">Created At:</div>
                                    <div class="data-value">${course.created_at}</div>
                                </div>
                                <div class="data-row-btn">
                                    <button class="enroll-btn" data-course-id="${course.course_id}" ${isEnrolled ? 'disabled' : ''}>
                                        <i class="fas fa-graduation-cap"></i>
                                        ${isEnrolled ? 'Enrolled' : 'Enroll'}
                                    </button>
                                </div>
                            `;

                            const enrollButton = courseBox.querySelector('.enroll-btn');
                            if (!isEnrolled) {
                                enrollButton.addEventListener('click', function () {
                                    enrollCourse(course.course_id, studentId);
                                });
                            }

                            coursesContainer.appendChild(courseBox);
                        })
                        .catch(error => console.error('Error checking enrollment:', error));
                    });
                } else {
                    alert('Failed to fetch courses: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));

            function enrollCourse(courseId, studentId) {
                fetch('PHP/Enrollments.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'enrollnew',
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
                .catch(error => console.error('Error:', error));
            }
        });
        
    </script>
</body>
</html>