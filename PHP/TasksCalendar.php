<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Calendar</title>
</head>

<body>
    <div class="calendar-container" id="task-calendar-container">
        <div class="calendar">
            
        </div>
    </div>

    <script>

        document.addEventListener('DOMContentLoaded', function () {
            const userId = <?php echo json_encode($_SESSION['user_id']); ?>;

            fetch('PHP/Tasks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=fetch_related_tasks&user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                console.log('Received Data:', data);
                if (data.success) {
                    const tasks = data.data;
                    const calendarContainer = document.getElementById('task-calendar-container');
                    const currentDate = new Date();
                    const daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

                    const calendarDiv = document.createElement('div');
                    calendarDiv.className = 'calendar';
                    calendarContainer.appendChild(calendarDiv);

                    const weekHeader = document.createElement('div');
                    weekHeader.className = 'header';
                    weekHeader.textContent = 'Week';
                    calendarDiv.appendChild(weekHeader);

                    daysOfWeek.forEach(day => {
                        const dayHeader = document.createElement('div');
                        dayHeader.className = 'header';
                        dayHeader.textContent = day;
                        calendarDiv.appendChild(dayHeader);
                    });

                    for (let week = 0; week < 4; week++) {
                        const weekStart = new Date(currentDate);
                        weekStart.setDate(currentDate.getDate() + (week * 7) - currentDate.getDay() + 1);

                        const weekHeaderDiv = document.createElement('div');
                        weekHeaderDiv.className = 'week';
                        weekHeaderDiv.textContent = `Week ${week + 1}`;
                        calendarDiv.appendChild(weekHeaderDiv);

                        for (let day = 0; day < 7; day++) {
                            const currentDay = new Date(weekStart);
                            currentDay.setDate(weekStart.getDate() + day);
                            const dateString = currentDay.toISOString().split('T')[0];

                            const tasksForDay = tasks.filter(task => {
                                const taskDeadline = new Date(task.deadline).toISOString().split('T')[0];
                                return taskDeadline === dateString;
                            });

                            const dayContainer = document.createElement('div');
                            if (tasksForDay.length > 0) {
                                dayContainer.className = 'available';
                                const ul = document.createElement('ul');
                                ul.className = 'tasks';
                                tasksForDay.forEach(task => {
                                    const liTitle = document.createElement('li');
                                    liTitle.textContent = task.title;
                                    const liDeadline = document.createElement('li');
                                    liDeadline.textContent = `Deadline: ${new Date(task.deadline).toLocaleTimeString()}`;
                                    liDeadline.style.color = '#f44336';
                                    ul.appendChild(liTitle);
                                    ul.appendChild(liDeadline);
                                });
                                dayContainer.appendChild(ul);
                            } else {
                                dayContainer.className = 'unavailable';
                                const ul = document.createElement('ul');
                                ul.className = 'tasks';
                                const li = document.createElement('li');
                                li.textContent = 'No Tasks';
                                ul.appendChild(li);
                                dayContainer.appendChild(ul);
                            }
                            calendarDiv.appendChild(dayContainer);
                        }
                    }
                } else {
                    console.error('Failed to fetch tasks:', data.message);
                }
            })
            .catch(error => console.error('Error fetching tasks:', error));
        });

    </script>
</body>

</html>