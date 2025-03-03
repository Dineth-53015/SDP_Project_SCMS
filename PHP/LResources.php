<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="Images/Icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/Styles.css">
</head>
<body>
    <?php include 'NavBar.php'?>

    <?php include 'PHP/checkStudentLecturer.php'?>

    <?php include 'Message.html'?>

    <header>
        Resources
    </header>

    <div class="container">
        <!-- Resource Overview & Dashboard -->
        <section id="overview" class="card">
            <h2>Resource Overview</h2>
            <?php include 'ResourceCharts.html'?>
        </section>

        <!-- Booking & Allocation System -->
        <section id="booking" class="card">
            <h2>Booking & Allocation System</h2>
            <div class="BAS">
                <div>
                    <h3>Manual Assignment</h3>
                    <form id="manualBookingForm">
                        <label>Resource:</label>
                        <select id="resourceSelect" name="resource" required>
                            <option value="">Select Resource</option>
                        </select>

                        <label>Requested By:</label>
                        <input type="text" name="userSelect" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" required readonly>
                        <input type="text" name="user_id" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>" required readonly hidden>

                        <label>Start Time:</label>
                        <input type="datetime-local" name="start_time" required>

                        <label>End Time:</label>
                        <input type="datetime-local" name="end_time" required>

                        <label>Reason:</label>
                        <input type="text" name="reason" required>

                        <input type="text" id="status" name="status" value="Pending" disabled hidden>

                        <button type="submit">Submit</button>
                    </form>
                </div>

                <!-- Bookings Made by User -->
                <div>
                    <h3>Bookings</h3>
                    <table id="bookings">
                        <thead>
                            <tr>
                                <th>Resource</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="calendar" class="card">
            <h2>Availability Calendar</h2>
            <?php include 'PHP/AvailabilityCalender.html'?>
        </section>
    </div>

    <?php include 'Footer.html'?>

    <?php include 'Chats.php'?>

    <script>

        document.addEventListener('DOMContentLoaded', function () {
            const resourceSelect = document.getElementById('resourceSelect');
            const userSelect = document.getElementById('userSelect');

            // Fetch resources and populate the dropdown
            fetch('PHP/Resources.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'fetch' }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.data.forEach(resource => {
                            const option = document.createElement('option');
                            option.value = resource.resource_id;
                            option.textContent = resource.resource_name;
                            resourceSelect.appendChild(option);
                        });
                    } else {
                        console.error('Error fetching resources:', data.message);
                    }
                })
                .catch(error => console.error('Fetch error:', error));

            // Booking
            const manualBookingForm = document.getElementById('manualBookingForm');
            manualBookingForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const formData = {
                    resource_id: resourceSelect.value,
                    user_id: manualBookingForm.elements['user_id'].value,
                    start_time: manualBookingForm.elements['start_time'].value,
                    end_time: manualBookingForm.elements['end_time'].value,
                    reason: manualBookingForm.elements['reason'].value,
                    approved_by: "-",
                    status: manualBookingForm.elements['status'].value
                };

                try {
                    const response = await fetch('PHP/ResourceBooking.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(formData)
                    });

                    const result = await response.json();
                    if (result.success) {
                        show('success');
                    } else {
                        alert('Error assigning resource: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    show('error');
                }
            });
        });

        // Fetch User's Previous Bookings
        document.addEventListener('DOMContentLoaded', function () {
            fetchUserBookings();

            function fetchUserBookings() {
                fetch('PHP/ResourceBooking.php?action=get_user_bookings')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const tableBody = document.querySelector('#bookings tbody');
                            tableBody.innerHTML = '';

                            data.data.forEach(booking => {
                                const row = `
                                    <tr>
                                        <td data-label="Resource">${booking.resource_name}</td>
                                        <td data-label="Start Time">${booking.start_time}</td>
                                        <td data-label="End Time">${booking.end_time}</td>
                                        <td data-label="Reason">${booking.reason}</td>
                                        <td data-label="Status">${booking.status}</td>
                                        <td class="action-buttons">
                                            ${booking.status === 'Cancelled' ? `
                                                <button disabled>
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            ` : `
                                            <button onclick="updateBookingStatus(${booking.booking_id}, 'Cancelled')">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                            `}
                                        </td>
                                    </tr>
                                `;
                                tableBody.innerHTML += row;
                            });
                        } else {
                            console.error('Failed to fetch user bookings:', data.message);
                        }
                    })
                    .catch(error => console.error('Error fetching user bookings:', error));
            }

            // Update Booking Status
            window.updateBookingStatus = function (bookingId, status) {
                const approvedBy = "-";

                fetch('PHP/ResourceBooking.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=update_booking_status&booking_id=${bookingId}&status=${status}&approved_by=${approvedBy}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        show('success');
                    } else {
                        console.error('Failed to update booking status:', data.message);
                    }
                })
                .catch(error => console.error('Error updating booking status:', error));
            };

        });
        
    </script>
</body>
</html>