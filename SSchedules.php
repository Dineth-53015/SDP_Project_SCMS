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
        Schedules
    </header>

    <div class="container">

        <!-- Schedules Available to Student Based on Course Enrollment and Status of Enrollment -->
        <div class="card">
            <header>Your Schedules</header>
            <div class="data-grid" id="schedules-container"></div>
        </div>
    </div>
            
    <?php include 'Footer.html'?>
    
    <?php include 'Chats.php'?>
    
    <script>

        // Fetch Schedules
        document.addEventListener('DOMContentLoaded', function () {
            const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
            const schedulesContainer = document.getElementById('schedules-container');

            fetch('PHP/Schedules.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=fetch_related_schedules&user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.data.forEach(schedule => {
                        const scheduleBox = document.createElement('div');
                        scheduleBox.className = 'data-box';
                        scheduleBox.innerHTML = `
                            <div class="data-row">
                                <div class="data-label">Title:</div>
                                <div class="data-value">${schedule.title}</div>
                            </div>
                            <div class="data-row">
                                <div class="data-label">Description:</div>
                                <div class="data-value">${schedule.description}</div>
                            </div>
                            <div class="data-row">
                                <div class="data-label">Course:</div>
                                <div class="data-value">${schedule.course_name}</div>
                            </div>
                            <div class="data-row">
                                <div class="data-label">Start Date:</div>
                                <div class="data-value">${schedule.start_date}</div>
                            </div>
                            <div class="data-row">
                                <div class="data-label">Start Time:</div>
                                <div class="data-value">${schedule.start_time}</div>
                            </div>
                            <div class="data-row">
                                <div class="data-label">End Time:</div>
                                <div class="data-value">${schedule.end_time}</div>
                            </div>
                            <div class="data-row">
                                <div class="data-label">Room:</div>
                                <div class="data-value">${schedule.room_id}</div>
                            </div>
                            <div class="data-row">
                                <div class="data-label">Recurring:</div>
                                <div class="data-value">${schedule.is_recurring ? 'Yes' : 'No'}</div>
                            </div>
                            ${schedule.is_recurring ? `
                            <div class="data-row">
                                <div class="data-label">Recurrence Pattern:</div>
                                <div class="data-value">${schedule.recurrence_pattern}</div>
                            </div>
                            <div class="data-row">
                                <div class="data-label">Recurrence End Date:</div>
                                <div class="data-value">${schedule.recurrence_end_date}</div>
                            </div>
                            ` : ''}
                            <div class="data-row">
                                <div class="data-label">Attendance:</div>
                                <div class="data-value">${schedule.attendance == '1' ? 'Attended' : schedule.attendance == '0' ? 'Not Attended' : 'Not Present'}</div>
                            </div>
                        `;

                        schedulesContainer.appendChild(scheduleBox);
                    });
                } else {
                    alert('Failed to fetch schedules: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });

    </script>

</body>
</html>