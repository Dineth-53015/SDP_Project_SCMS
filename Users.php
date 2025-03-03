<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="Images/Icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/Styles.css">
</head>
<body>
    <?php include 'NavBar.php'?>

    <?php include 'PHP/checkAdmin.php'?>

    <?php include 'Message.html'?>

    <header>
        User Management
    </header>

    <div class="container">

        <section id="overview" class="card">
            <h2>Users Overview</h2>
            <?php include 'UserCharts.html'?>
        </section>

        <div class="card">
            <h2>Users</h2>

            <!-- Pending Approvals Section -->
            <div>
                <h3>Pending Approvals</h3>
                <div class="RLM-Top" style="margin-top: 15px;">
                    <input type="text" id="pendingApprovals" placeholder="Search by name or role..." />
                </div>
                <table id="pendingEnrollmentsTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Phone Number</th>
                            <th>Faculty</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    </tbody>
                </table>
            </div>

            <!-- Suspended Users Section -->
            <div>
                <h3>Suspended Users</h3>
                <div class="RLM-Top" style="margin-top: 15px;">
                    <input type="text" id="suspendedUsers" placeholder="Search by name or role..." />
                </div>
                <table id="suspendedUsersTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Phone Number</th>
                            <th>Faculty</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>

        <!-- Active Users Section -->
        <div class="card">
            <h2>Active User Listings & Management</h2>
            <div class="RLM-Top">
                <input type="text" id="activeUsers" placeholder="Search by name or role..." />
            </div>
            <table id="activeUsersTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Phone Number</th>
                        <th>Faculty</th>
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

        // Fetch Active Users
        document.addEventListener('DOMContentLoaded', function() {
            function fetchActiveUsers(search = '') {
                fetch('PHP/Users.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'fetch_active_users',
                        search: search
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tableBody = document.querySelector('#activeUsersTable tbody');
                        tableBody.innerHTML = '';

                        data.data.forEach(users => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td data-label="Name">${users.name}</td>
                                <td data-label="Email">${users.email}</td>
                                <td data-label="Role">${users.role}</td>
                                <td data-label="Phone Number">${users.phone_number}</td>
                                <td data-label="Faculty">${users.faculty}</td>
                                <td data-label="Status">${users.status}</td>
                                <td class="action-buttons">
                                    <button class="remove-btn" data-user-id="${users.user_id}">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </td>
                            `;
                            tableBody.appendChild(row);
                        });
                    } else {
                        console.error('Failed to fetch active users:', data.message);
                    }
                })
                .catch(error => console.error('Error fetching active users:', error));
            }

            fetchActiveUsers();

            const searchInput = document.getElementById('activeUsers');
            searchInput.addEventListener('input', function() {
                fetchActiveUsers(this.value);
            });

            // Suspend User
            document.addEventListener('click', function(event) {
                if (event.target.closest('.remove-btn')) {
                    const button = event.target.closest('.remove-btn');
                    const userId = button.getAttribute('data-user-id');

                    if (confirm('Are you sure you want to suspend this user?')) {
                        fetch('PHP/Users.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                action: 'suspend_user',
                                user_id: userId,
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                show('success');
                            } else {
                                alert('Failed to suspend user: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error suspending user:', error);
                            alert('An error occurred while suspending the user.');
                        });
                    }
                }
            });
        });

        // Fetch Suspended Users
        document.addEventListener('DOMContentLoaded', function() {
            function fetchSuspendedUsers(search = '') {
                fetch('PHP/Users.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'fetch_suspended_users',
                        search: search
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tableBody = document.querySelector('#suspendedUsersTable tbody');
                        tableBody.innerHTML = '';

                        data.data.forEach(users => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td data-label="Name">${users.name}</td>
                                <td data-label="Email">${users.email}</td>
                                <td data-label="Role">${users.role}</td>
                                <td data-label="Phone Number">${users.phone_number}</td>
                                <td data-label="Faculty">${users.faculty}</td>
                                <td data-label="Status">${users.status}</td>
                                <td class="action-buttons">
                                    <button class="activate-btn" data-user-id="${users.user_id}">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </td>
                            `;
                            tableBody.appendChild(row);
                        });
                    } else {
                        console.error('Failed to fetch suspended users:', data.message);
                    }
                })
                .catch(error => console.error('Error fetching suspended users:', error));
            }

            fetchSuspendedUsers();

            const searchInput = document.getElementById('suspendedUsers');
            searchInput.addEventListener('input', function() {
                fetchSuspendedUsers(this.value);
            });

            // Reactivate User
            document.addEventListener('click', function(event) {
                if (event.target.closest('.activate-btn')) {
                    const button = event.target.closest('.activate-btn');
                    const userId = button.getAttribute('data-user-id');

                    if (confirm('Are you sure you want to activate this user?')) {
                        fetch('PHP/Users.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                action: 'activate_user',
                                user_id: userId,
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                show('success');
                            } else {
                                alert('Failed to activate user: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error activating user:', error);
                            alert('An error occurred while activating the user.');
                        });
                    }
                }
            });
        });

        // Fetch Pending Users
        document.addEventListener('DOMContentLoaded', function() {
            function fetchSuspendedUsers(search = '') {
                fetch('PHP/Users.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'fetch_pending_users',
                        search: search
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tableBody = document.querySelector('#pendingEnrollmentsTable tbody');
                        tableBody.innerHTML = '';

                        data.data.forEach(users => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td data-label="Name">${users.name}</td>
                                <td data-label="Email">${users.email}</td>
                                <td data-label="Role">${users.role}</td>
                                <td data-label="Phone Number">${users.phone_number}</td>
                                <td data-label="Faculty">${users.faculty}</td>
                                <td data-label="Status">${users.status}</td>
                                <td class="action-buttons">
                                    <button class="activate-btn" data-user-id="${users.user_id}">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                    <button class="remove-btn" data-user-id="${users.user_id}">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </td>
                            `;
                            tableBody.appendChild(row);
                        });
                    } else {
                        console.error('Failed to fetch pending users:', data.message);
                    }
                })
                .catch(error => console.error('Error fetching pending users:', error));
            }

            fetchSuspendedUsers();

            const searchInput = document.getElementById('pendingApprovals');
            searchInput.addEventListener('input', function() {
                fetchSuspendedUsers(this.value);
            });
        });

    </script>

</body>
</html>