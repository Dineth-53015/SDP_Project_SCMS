<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="Images/Icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/Styles.css">
</head>
<body>
    <?php include 'NavBar.php'?>

    <?php include 'PHP/checkStudent.php'?>
    
    <?php include 'Message.html'?>
    
    <header>
        Event Registration
    </header>

    <div class="container">
        <div class="card">
            <header>Registered Events</header>
            <div class="data-grid" id="registered-events-container">
                
            </div>
        </div>

        <div class="card">
            <header>All Events</header>
            <div class="data-grid" id="all-events-container">
                
            </div>
        </div>
    </div>
            
    <?php include 'Footer.html'?>
    
    <?php include 'Chats.php'?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const registeredEventsContainer = document.getElementById('registered-events-container');
            const allEventsContainer = document.getElementById('all-events-container');
            const userId = <?php echo json_encode($_SESSION['user_id']); ?>;

            fetch('PHP/EventRegistrations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=fetch_registrations&user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                console.log('Registered events data received:', data);
                if (data.success) {
                    data.data.forEach(event => {
                        const eventBox = document.createElement('div');
                        eventBox.className = 'data-box';
                        eventBox.innerHTML = `
                            <div class="data-row">
                                <div class="data-label">Event Title:</div>
                                <div class="data-value">${event.event_title}</div>
                            </div>
                            <div class="data-row">
                                <div class="data-label">User Name:</div>
                                <div class="data-value">${event.user_name}</div>
                            </div>
                            <div class="data-row">
                                <div class="data-label">Status:</div>
                                <div class="data-value">${event.registration_status}</div>
                            </div>
                            <div class="data-row">
                                <div class="data-label">Registered At:</div>
                                <div class="data-value">${event.registered_at}</div>
                            </div>
                        `;
                        registeredEventsContainer.appendChild(eventBox);
                    });
                } else {
                    console.error('Failed to fetch registered events:', data.message);
                }
            })
            .catch(error => console.error('Error fetching registered events:', error));

            fetch('PHP/Events.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=fetch_events`
            })
            .then(response => response.json())
            .then(data => {
                console.log('All events data received:', data);
                if (data.success) {
                    data.data.forEach(event => {
                        const eventBox = document.createElement('div');
                        eventBox.className = 'data-box';

                        fetch('PHP/EventRegistrations.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `action=check_registration&event_id=${event.event_id}&user_id=${userId}`
                        })
                        .then(response => response.json())
                        .then(registrationData => {
                            const isRegistered = registrationData.success && registrationData.registered;

                            fetch('PHP/Events.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `action=fetch_event&event_id=${event.event_id}`
                            })
                            .then(response => response.json())
                            .then(eventData => {
                                if (!eventData.success) {
                                    console.error('Failed to fetch event details:', eventData.message);
                                    return;
                                }

                                const maxParticipants = eventData.data.max_participants;

                                fetch('PHP/EventRegistrations.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: `action=count_registrations&event_id=${event.event_id}`
                                })
                                .then(response => response.json())
                                .then(registrationCountData => {
                                    if (!registrationCountData.success) {
                                        console.error('Failed to fetch registration count:', registrationCountData.message);
                                        return;
                                    }

                                    const registeredCount = registrationCountData.count;
                                    const availableSpots = maxParticipants - registeredCount;

                                    eventBox.innerHTML = `
                                        <div class="data-row">
                                            <div class="data-label">Event Title:</div>
                                            <div class="data-value">${event.title}</div>
                                        </div>
                                        <div class="data-row">
                                            <div class="data-label">Description:</div>
                                            <div class="data-value">${event.description}</div>
                                        </div>
                                        <div class="data-row">
                                            <div class="data-label">Category:</div>
                                            <div class="data-value">${event.category}</div>
                                        </div>
                                        <div class="data-row">
                                            <div class="data-label">Date:</div>
                                            <div class="data-value">${event.event_date}</div>
                                        </div>
                                        <div class="data-row">
                                            <div class="data-label">Venue:</div>
                                            <div class="data-value">${event.venue}</div>
                                        </div>
                                        <div class="data-row">
                                            <div class="data-label">Available Spots:</div>
                                            <div class="data-value">${availableSpots}</div>
                                        </div>
                                        <div class="data-row-btn">
                                            <button class="register-btn" data-event-id="${event.event_id}" ${isRegistered ? 'disabled' : ''}>
                                                <i class="fas fa-calendar-check"></i>
                                                ${isRegistered ? 'Registered' : 'Register'}
                                            </button>
                                        </div>
                                    `;

                                    const registerButton = eventBox.querySelector('.register-btn');
                                    if (!isRegistered) {
                                        registerButton.addEventListener('click', function () {
                                            registerForEvent(event.event_id, userId);
                                        });
                                    }

                                    allEventsContainer.appendChild(eventBox);
                                })
                                .catch(error => console.error('Error fetching registration count:', error));
                            })
                            .catch(error => console.error('Error fetching event details:', error));
                        })
                        .catch(error => console.error('Error checking registration:', error));
                    });
                } else {
                    alert('Failed to fetch events: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));

            function registerForEvent(eventId, userId) {
                fetch('PHP/Events.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=fetch_event&event_id=${eventId}`
                })
                .then(response => response.json())
                .then(eventData => {
                    if (!eventData.success) {
                        alert('Failed to fetch event details: ' + eventData.message);
                        return;
                    }

                    const maxParticipants = eventData.data.max_participants;

                    fetch('PHP/EventRegistrations.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `action=count_registrations&event_id=${eventId}`
                    })
                    .then(response => response.json())
                    .then(registrationData => {
                        if (!registrationData.success) {
                            alert('Failed to fetch registration count: ' + registrationData.message);
                            return;
                        }

                        const registeredCount = registrationData.count;

                        if (registeredCount >= maxParticipants) {
                            alert('Registration full: No available spots for this event.');
                            return;
                        }

                        fetch('PHP/EventRegistrations.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `action=register&event_id=${eventId}&user_id=${userId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                show('success');
                            } else {
                                alert('Registration failed: ' + data.message);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    })
                    .catch(error => console.error('Error fetching registration count:', error));
                })
                .catch(error => console.error('Error fetching event details:', error));
            }
        });
    </script>
</body>
</html>