<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="Images/Icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/Styles.css">
</head>
<body>
    <?php include 'NavBar.php'?>

    <?php include 'Message.html'?>

    <header>
        Account Details
    </header>

    <div class="container">

        <!-- Personal Details Section -->
        <section id="Account" class="card">
            <div class="BAS">
                <div>
                    <label>Name:</label>
                    <input type="text" id="name" name="reason" readonly>

                    <label>Email:</label>
                    <input type="text" id="email" name="reason" readonly>

                    <label>Username:</label>
                    <input type="text" id="username" name="username">

                    <label>Password:</label>
                    <input type="password" id="password" name="password" placeholder="••••••••••••">

                    <label>Role:</label>
                    <input type="text" id="role" name="role" readonly>

                </div>

                <div>
                    <label>Phone Number:</label>
                    <input type="text" id="phone_number" name="phone_number" readonly>

                    <label>Faculty:</label>
                    <input type="text" id="faculty" name="faculty" readonly>

                    <label>Status:</label>
                    <input type="text" id="status" name="status" readonly>

                    <label>Created At:</label>
                    <input type="text" id="created_at" name="created_at" readonly>
                </div>
            </div>
            <button type="submit">Save</button>
        </section>

        <!-- Notification Preference Section -->
        <section id="NotificationPreference" class="card">
            <h3>Notification Preference</h3>
            <br>
            <div class="BAS">
                <div class="notipre" id="inApp" style="border-color: #ff7e5f;">
                    <label>In App</label>
                </div>
                <div class="notipre"id="emailNoti">
                    <label>Email</label>
                </div>
                <div class="notipre" id="smsNoti">
                    <label>SMS</label>
                </div>
            </div>
        </section>
    </div>

    <?php include 'Footer.html'?>

    <!-- Include Chat Section if Only Logged In User's Role is Student or Lecturer -->
    <?php
        if ($role === 'Student' || $role === 'Lecturer') {
        include 'Chats.php';
    }
    ?>

    <script>

        // Fetch Logged In User Data
        document.addEventListener('DOMContentLoaded', function() {
            const userId = <?php echo $_SESSION['user_id']; ?>;

            fetch('PHP/Users.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'fetch_user_details',
                    user_id: userId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const user = data.data;
                    document.getElementById('name').value = user.name;
                    document.getElementById('email').value = user.email;
                    document.getElementById('username').value = user.username;
                    document.getElementById('password').value = '';
                    document.getElementById('role').value = user.role;
                    document.getElementById('phone_number').value = user.phone_number;
                    document.getElementById('faculty').value = user.faculty;
                    document.getElementById('status').value = user.status;
                    document.getElementById('created_at').value = user.created_at;
                } else {
                    alert('Failed to fetch user details: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });

            // Save Updated Username or Password
            document.querySelector('button[type="submit"]').addEventListener('click', function(event) {
                event.preventDefault();

                const newUsername = document.getElementById('username').value;
                const newPassword = document.getElementById('password').value;

                fetch('PHP/Users.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update_user_details',
                        user_id: userId,
                        username: newUsername,
                        password: newPassword
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        show('success');
                    } else {
                        alert('Failed to update user details: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const userId = <?php echo $_SESSION['user_id']; ?>;

            // Fetch current notification preferences
            fetch('PHP/Users.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'fetch_notification_preferences',
                    user_id: userId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const preferences = data.data;
                    document.getElementById('emailNoti').classList.toggle('active', preferences.email);
                    document.getElementById('smsNoti').classList.toggle('active', preferences.sms);
                } else {
                    alert('Failed to fetch notification preferences: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });

            document.getElementById('inApp').addEventListener('click', function() {
                alert('In App notifications cannot be turned off.');
            });

            document.getElementById('emailNoti').addEventListener('click', function() {
                toggleNotificationPreference(userId, 'email');
            });

            document.getElementById('smsNoti').addEventListener('click', function() {
                toggleNotificationPreference(userId, 'sms');
            });

            // Change Notification Preference
            function toggleNotificationPreference(userId, type) {
                fetch('PHP/Users.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'toggle_notification_preference',
                        user_id: userId,
                        type: type
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const div = document.getElementById(`${type}Noti`);
                        div.classList.toggle('active', data.newValue);
                        show('success');
                    } else {
                        alert('Failed to update notification preference: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        });
    </script>
</body>
</html>