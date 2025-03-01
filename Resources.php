<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resource Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="Images/Icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/Styles.css">
</head>
<body>
    <?php include 'NavBar.php'?>
    
    <?php include 'PHP/checkAdmin.php'?>

    <?php include 'Message.html'?>

    <header>
        Resource Management
    </header>

    <div class="container">

        <!-- Resource Overview & Dashboard -->
        <section id="overview" class="card">
            <h2>Resource Overview</h2>
            <?php include 'ResourceCharts.html'?>
        </section>

        <!-- Resource Listings & Management -->
        <section id="listings" class="card">
            <h2>Resource Listings & Management</h2>
            <div class="RLM-Top">
                <input type="text" placeholder="Search resources...">
                <button id="openModal">Add New Resource</button>
            </div>
            
            <table id="resourceTable">
                <thead>
                    <tr>
                        <th>Resource Name</th>
                        <th>Category</th>
                        <th>Capacity</th>
                        <th>Features</th>
                        <th>Availability</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>

            <div class="overlay" id="infoOverlay">
                <div class="overlay-content">
                    <h2>Resource Details</h2>
                    <p><strong>Resource Name:</strong> <span id="infoResourceName"></span></p>
                    <p><strong>Category:</strong> <span id="infoCategory"></span></p>
                    <p><strong>Capacity:</strong> <span id="infoCapacity"></span></p>
                    <p><strong>Features:</strong> <span id="infoFeatures"></span></p>
                    <p><strong>Availability:</strong> <span id="infoAvailabilityStatus"></span></p>
                    <p><strong>Location:</strong> <span id="infoLocation"></span></p>
                    <p><strong>Added By:</strong> <span id="infoAddedBy"></span></p>
                    <p><strong>Created At:</strong> <span id="infoCreatedAt"></span></p>
                    <p><strong>Updated At:</strong> <span id="infoUpdatedAt"></span></p>
                    <button id="closeOverlay">Close</button>
                </div>
            </div>
        </section>

        <!-- Booking & Allocation System -->
        <section id="booking" class="card">
            <h2>Booking & Allocation System</h2>
            <div class="BAS">
                <div>
                    <h3>Manual Assignment</h3>
                    <form id="manualAssignmentForm">
                        <label>Resource:</label>
                        <select id="resourceSelect" name="resource" required>
                            <option value="">Select Resource</option>
                        </select>

                        <label>Allocated To:</label>
                        <select id="userSelect" name="user" required>
                            <option value="">Select User</option>
                        </select>

                        <label>Start Time:</label>
                        <input type="datetime-local" name="start_time" required>

                        <label>End Time:</label>
                        <input type="datetime-local" name="end_time" required>

                        <label>Reason:</label>
                        <input type="text" name="reason" required>

                        <label>Approved By:</label>
                        <input type="text" name="approved_by" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" required readonly>

                        <input type="text" id="status" name="status" value="Approved" disabled hidden>

                        <button type="submit">Assign</button>
                    </form>
                </div>

                <!-- Booking Requests -->
                <div>
                    <h3>Booking Requests</h3>
                    <table id="booking-requests">
                        <thead>
                            <tr>
                                <th>Resource</th>
                                <th>Allocated To</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Reason</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
            </div>

            <br>
            
            <!-- All Bookings -->
            <h3>All Bookings & Allocations</h3>

            <br>

            <div class="RLM-Top">
                <input type="text" id="bookingSearchInput" placeholder="Search bookings...">
            </div>
            
            <table id="bookingsTable">
                <thead>
                    <tr>
                        <th>Resource Name</th>
                        <th>Allocated To</th>
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

            <!-- Overlay for All Bookings & Allocations -->
            <div id="bookingDetailsOverlay" class="overlay">
                <div class="overlay-content">
                    <h2>Booking Details</h2>
                    <p><strong>Resource:</strong> <span id="bookingResourceName"></span></p>
                    <p><strong>Allocated To:</strong> <span id="bookingUserName"></span></p>
                    <p><strong>Start Time:</strong> <span id="bookingStartTime"></span></p>
                    <p><strong>End Time:</strong> <span id="bookingEndTime"></span></p>
                    <p><strong>Status:</strong> <span id="bookingStatus"></span></p>
                    <p><strong>Requested At:</strong> <span id="bookingRequestedAt"></span></p>
                    <p><strong>Reason:</strong> <span id="bookingReason"></span></p>
                    <p><strong>Approved By:</strong> <span id="bookingApprovedBy"></span></p>
                    <p><strong>Updated At:</strong> <span id="bookingUpdatedAt"></span></p>
                    <button onclick="closeBookingDetailsOverlay()">Close</button>
                </div>
            </div>
        </section>

        <section id="calendar" class="card">
            <h2>Availability Calendar</h2>
            <?php include 'PHP/AvailabilityCalender.html'?>
        </section>
    </div>

    <!-- Add Resource Overlay -->
    <div class="overlay" id="overlay">
        <div class="overlay-content">
            <button class="close-btn" id="closeModal">&times;</button>
            <h2>Add New Resource</h2>
            <form id="addResourceForm">
                <div class="scrollable-content">
                    <label for="resource_name">Resource Name:</label>
                    <input type="text" id="resource_name" name="resource_name" required>

                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="">-- Select Category --</option>
                        <option value="Lecture Room">Lecture Room</option>
                        <option value="Lab">Lab</option>
                        <option value="Conference Room">Conference Room</option>
                        <option value="Equipment">Equipment</option>
                    </select>

                    <label for="location">Location:</label>
                    <input type="text" id="location" name="location" required>

                    <label for="capacity">Capacity:</label>
                    <input type="number" id="capacity" name="capacity" value="0" required>

                    <label for="availability_status">Availability Status:</label>
                    <select id="availability_status" name="availability_status" required>
                        <option value="">-- Select Availablity --</option>
                        <option value="Available">Available</option>
                        <option value="Booked">Booked</option>
                        <option value="Partially Booked">Partially Booked</option>
                    </select>

                    <label for="features">Features:</label>
                    <textarea id="features" name="features" rows="3"></textarea>

                    <label for="added_by">Added By:</label>
                    <input type="text" id="added_by" name="added_by" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" required readonly>
                </div>
                <div style="margin-top: 15px;">
                    <button type="submit">Submit</button>
                    <button type="button" id="cancelBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Resources Overlay -->
    <div class="overlay" id="editOverlay">
        <div class="overlay-content">
            <button class="close-btn" id="closeEditModal">&times;</button>
            <h2>Edit Resource</h2>
            <form id="editResourceForm">
                <div class="scrollable-content">
                    <label for="editResourceName">Resource Name:</label>
                    <input type="text" id="editResourceName" name="resource_name" required>

                    <label for="editCategory">Category:</label>
                    <select id="editCategory" name="category" required>
                        <option value="">-- Select Category --</option>
                        <option value="Lecture Room">Lecture Room</option>
                        <option value="Lab">Lab</option>
                        <option value="Conference Room">Conference Room</option>
                        <option value="Equipment">Equipment</option>
                    </select>

                    <label for="editLocation">Location:</label>
                    <input type="text" id="editLocation" name="location" required>

                    <label for="editCapacity">Capacity:</label>
                    <input type="number" id="editCapacity" name="capacity" required>

                    <label for="editAvailabilityStatus">Availability Status:</label>
                    <select id="editAvailabilityStatus" name="availability_status" required>
                        <option value="">-- Select Available --</option>
                        <option value="Available">Available</option>
                        <option value="Booked">Booked</option>
                        <option value="Partially Booked">Partially Booked</option>
                    </select>

                    <label for="editFeatures">Features:</label>
                    <textarea id="editFeatures" name="features" rows="3"></textarea>

                    <label for="editAddedBy">Added By:</label>
                    <input type="text" id="editAddedBy" name="added_by"  required readonly>
                </div>
                <div style="margin-top: 15px;">
                    <button type="submit">Update</button>
                    <button type="button" id="cancelEditBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'Footer.html'?>

    <script>

        const openModalBtn = document.getElementById('openModal');
        const closeModalBtn = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const overlay = document.getElementById('overlay');
        const infoOverlay = document.getElementById('infoOverlay');
        const bookingDetailsOverlay = document.getElementById('bookingDetailsOverlay');

        openModalBtn.addEventListener('click', () => {
            overlay.style.display = 'flex';
        });

        closeModalBtn.addEventListener('click', () => {
            overlay.style.display = 'none';
        });

        cancelBtn.addEventListener('click', () => {
            overlay.style.display = 'none';
        });

        // Close Modal on Outside Click
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.style.display = 'none';
            }
        });

        // Close Modal on Outside Click
        infoOverlay.addEventListener('click', (e) => {
            if (e.target === infoOverlay) {
                infoOverlay.style.display = 'none';
            }
        });

        // Close Modal on Outside Click
        bookingDetailsOverlay.addEventListener('click', (e) => {
            if (e.target === bookingDetailsOverlay) {
                bookingDetailsOverlay.style.display = 'none';
            }
        });

        // Add Resource
        const addResourceForm = document.getElementById('addResourceForm');
        addResourceForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = {
                action: 'add',
                resource_name: document.getElementById('resource_name').value,
                category: document.getElementById('category').value,
                location: document.getElementById('location').value,
                capacity: document.getElementById('capacity').value,
                availability_status: document.getElementById('availability_status').value,
                features: document.getElementById('features').value,
                added_by: document.getElementById('added_by').value
            };

            try {
                const response = await fetch('PHP/Resources.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();
                if (result.success) {
                    overlay.style.display = 'none';
                    show('success');
                } else {
                    alert('Error adding resource: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while adding the resource.');
            }
        });

        // Fetch All Resources
        document.addEventListener('DOMContentLoaded', function () {
            const resourceTableBody = document.querySelector('#resourceTable tbody');
            const searchInput = document.querySelector('#listings input[type="text"]');
            const infoOverlay = document.getElementById('infoOverlay');
            const closeOverlayButton = document.getElementById('closeOverlay');
            const editOverlay = document.getElementById('editOverlay');
            const closeEditModalBtn = document.getElementById('closeEditModal');
            const cancelEditBtn = document.getElementById('cancelEditBtn');
            const editResourceForm = document.getElementById('editResourceForm');

            function fetchResources() {
                fetch('PHP/Resources.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'fetch' }),
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            resourceTableBody.innerHTML = '';

                            data.data.forEach(resource => {
                                const row = document.createElement('tr');

                                row.innerHTML = `
                                    <td data-label="Resource">${resource.resource_name}</td>
                                    <td data-label="Category">${resource.category}</td>
                                    <td data-label="Capacity">${resource.capacity}</td>
                                    <td data-label="Feaatures">${resource.features}</td>
                                    <td data-label="Availability">${resource.availability_status}</td>
                                    <td data-label="Location">${resource.location}</td>
                                    <td class="action-buttons">
                                        <button class="edit-btn" data-resource-id="${resource.resource_id}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="remove-btn" data-resource-id="${resource.resource_id}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <button class="info-btn" data-resource-id="${resource.resource_id}">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                    </td>
                                `;

                                resourceTableBody.appendChild(row);
                            });
                        } else {
                            console.error('Error fetching resources:', data.message);
                        }
                    })
                    .catch(error => console.error('Fetch error:', error));
            }

            fetchResources();

            // Handle Search Input
            searchInput.addEventListener('input', async (event) => {
                const searchTerm = event.target.value.trim();

                if (searchTerm.length > 0) {
                    try {
                        const response = await fetch('PHP/Resources.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: 'search', search_term: searchTerm }),
                        });

                        const result = await response.json();

                        if (result.success) {
                            resourceTableBody.innerHTML = '';

                            result.data.forEach(resource => {
                                const row = document.createElement('tr');

                                row.innerHTML = `
                                    <td data-label="Resource">${resource.resource_name}</td>
                                    <td data-label="Category">${resource.category}</td>
                                    <td data-label="Capacity">${resource.capacity}</td>
                                    <td data-label="Feaatures">${resource.features}</td>
                                    <td data-label="Availability">${resource.availability_status}</td>
                                    <td data-label="Location">${resource.location}</td>
                                    <td class="action-buttons">
                                        <button class="edit-btn" data-resource-id="${resource.resource_id}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="remove-btn" data-resource-id="${resource.resource_id}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <button class="info-btn" data-resource-id="${resource.resource_id}">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                    </td>
                                `;

                                resourceTableBody.appendChild(row);
                            });
                        } else {
                            console.error('Error fetching search results:', result.message);
                        }
                    } catch (error) {
                        console.error('Search error:', error);
                    }
                } else {
                    fetchResources();
                }
            });

            // Show Resource Info
            document.addEventListener('click', function (event) {
                if (event.target.closest('.info-btn')) {
                    const resourceId = event.target.closest('.info-btn').dataset.resourceId;

                    fetch('PHP/Resources.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'fetch', resource_id: resourceId }),
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.data.length > 0) {
                                const resource = data.data[0];
                                document.getElementById('infoResourceName').textContent = resource.resource_name;
                                document.getElementById('infoCategory').textContent = resource.category;
                                document.getElementById('infoLocation').textContent = resource.location;
                                document.getElementById('infoCapacity').textContent = resource.capacity;
                                document.getElementById('infoAvailabilityStatus').textContent = resource.availability_status;
                                document.getElementById('infoFeatures').textContent = resource.features;
                                document.getElementById('infoAddedBy').textContent = resource.added_by;
                                document.getElementById('infoCreatedAt').textContent = resource.created_at;
                                document.getElementById('infoUpdatedAt').textContent = resource.updated_at;
                                
                               
                                infoOverlay.style.display = 'flex';
                            } else {
                                show('error');
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));
                }

                if (event.target === closeOverlayButton || event.target.closest('#closeOverlay')) {
                    infoOverlay.style.display = 'none';
                }
            });

            // Delete Resource
            document.addEventListener('click', function (event) {
                const removeBtn = event.target.closest('.remove-btn');
                if (removeBtn) {
                    const resourceId = removeBtn.dataset.resourceId;

                    show('confirm');

                    const yesButton = document.querySelector('.btns button:first-child');
                    const noButton = document.querySelector('.btns button:last-child');

                    const yesHandler = () => {
                        fetch('PHP/Resources.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: 'delete', resource_id: resourceId }),
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    show('success');
                                } else {
                                    alert('Error deleting resource: ' + data.message);
                                }
                            })
                            .catch(error => console.error('Delete error:', error));

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

            // Get Data to Edit Resource Form
            document.addEventListener('click', function (event) {
                if (event.target.closest('.edit-btn')) {
                    const resourceId = event.target.closest('.edit-btn').dataset.resourceId;

                    fetch('PHP/Resources.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'fetch', resource_id: resourceId }),
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.data.length > 0) {
                                const resource = data.data[0];

                                document.getElementById('editResourceName').value = resource.resource_name;
                                document.getElementById('editCategory').value = resource.category;
                                document.getElementById('editLocation').value = resource.location;
                                document.getElementById('editCapacity').value = resource.capacity;
                                document.getElementById('editAvailabilityStatus').value = resource.availability_status;
                                document.getElementById('editFeatures').value = resource.features;
                                document.getElementById('editAddedBy').value = resource.added_by;

                                editResourceForm.dataset.resourceId = resourceId;

                                editOverlay.style.display = 'flex';
                            } else {
                                show('error');
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));
                }
            });

            // Close Edit Modal
            closeEditModalBtn.addEventListener('click', () => {
                editOverlay.style.display = 'none';
            });

            cancelEditBtn.addEventListener('click', () => {
                editOverlay.style.display = 'none';
            });

            // Close Edit Modal on Outside Click
            editOverlay.addEventListener('click', (e) => {
                if (e.target === editOverlay) {
                    editOverlay.style.display = 'none';
                }
            });

            // Edit Resource
            editResourceForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const formData = {
                    action: 'update',
                    resource_id: editResourceForm.dataset.resourceId,
                    resource_name: document.getElementById('editResourceName').value,
                    category: document.getElementById('editCategory').value,
                    location: document.getElementById('editLocation').value,
                    capacity: document.getElementById('editCapacity').value,
                    availability_status: document.getElementById('editAvailabilityStatus').value,
                    features: document.getElementById('editFeatures').value,
                    added_by: document.getElementById('editAddedBy').value
                };

                try {
                    const response = await fetch('PHP/Resources.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(formData)
                    });

                    const result = await response.json();
                    if (result.success) {
                        editOverlay.style.display = 'none';
                        show('success');
                    } else {
                        alert('Error updating resource: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    show('error');
                }
            });
        });

        document.getElementById('capacity').addEventListener('input', function(event) {
            let capacityInput = event.target;
            
            if (capacityInput.value < 0) {
                capacityInput.value = 0;
            }
        });

        document.getElementById('editCapacity').addEventListener('input', function(event) {
            let capacityInput = event.target;
            
            if (capacityInput.value < 0) {
                capacityInput.value = 0;
            }
        });

        // Fetch resources and populate the dropdown
        document.addEventListener('DOMContentLoaded', function () {
            const resourceSelect = document.getElementById('resourceSelect');
            const userSelect = document.getElementById('userSelect');

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

            // Fetch users and populate the dropdown
            fetch('PHP/Users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'fetch' }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.data.forEach(user => {
                            const option = document.createElement('option');
                            option.value = user.user_id;
                            option.textContent = user.name;
                            userSelect.appendChild(option);
                        });
                    } else {
                        console.error('Error fetching users:', data.message);
                    }
                })
                .catch(error => console.error('Fetch error:', error));

            // Manual Resource Assignment Form
            const manualAssignmentForm = document.getElementById('manualAssignmentForm');
            manualAssignmentForm.addEventListener('submit', async (e) => {
                e.preventDefault(); 

                const formData = {
                    resource_id: resourceSelect.value,
                    user_id: userSelect.value,
                    start_time: manualAssignmentForm.elements['start_time'].value,
                    end_time: manualAssignmentForm.elements['end_time'].value,
                    reason: manualAssignmentForm.elements['reason'].value,
                    approved_by: manualAssignmentForm.elements['approved_by'].value,
                    status: manualAssignmentForm.elements['status'].value
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

        document.addEventListener('DOMContentLoaded', function () {
            // Fetch and display pending bookings
            fetchPendingBookings();

            function fetchPendingBookings() {
                fetch('PHP/ResourceBooking.php?action=get_pending_bookings')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const tableBody = document.querySelector('#booking-requests tbody');
                            tableBody.innerHTML = '';

                            data.data.forEach(booking => {
                                const row = `
                                    <tr>
                                        <td data-label="Resource">${booking.resource_name}</td>
                                        <td data-label="Allocated To">${booking.name}</td>
                                        <td data-label="Start Time">${booking.start_time}</td>
                                        <td data-label="End Time">${booking.end_time}</td>
                                        <td data-label="Reason">${booking.reason}</td>
                                        <td class="action-buttons">
                                            <button onclick="updateBookingStatus(${booking.booking_id}, 'Approved')">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                            <button onclick="updateBookingStatus(${booking.booking_id}, 'Rejected')">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                `;
                                tableBody.innerHTML += row;
                            });
                        } else {
                            console.error('Failed to fetch pending bookings:', data.message);
                        }
                    })
                    .catch(error => console.error('Error fetching pending bookings:', error));
            }

            // Update Booking Status
            window.updateBookingStatus = function (bookingId, status) {
                const approvedBy = document.querySelector('input[name="approved_by"]').value;

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

        document.addEventListener('DOMContentLoaded', function () {
            const bookingSearchInput = document.getElementById('bookingSearchInput');
            const bookingsTableBody = document.querySelector('#bookingsTable tbody');

            async function fetchAllBookings(searchTerm = '') {
                try {
                    const response = await fetch(`PHP/ResourceBooking.php?action=get_all_bookings&search_term=${searchTerm}`);
                    const result = await response.json();

                    if (result.success) {
                        const bookings = result.data;
                        bookingsTableBody.innerHTML = '';

                        bookings.forEach(booking => {
                            const row = document.createElement('tr');

                            row.innerHTML = `
                                <td data-label="Resource">${booking.resource_name}</td>
                                <td data-label="Allocated To">${booking.allocated_to}</td>
                                <td data-label="Start Time">${booking.start_time}</td>
                                <td data-label="End Time">${booking.end_time}</td>
                                <td data-label="Reason">${booking.reason}</td>
                                <td data-label="Status">${booking.status}</td>
                                <td class="action-buttons">
                                    <button class="booking-info-btn" data-booking-id="${booking.booking_id}">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                </td>
                            `;
                            bookingsTableBody.appendChild(row);
                        });
                    } else {
                        console.error('Failed to fetch bookings:', result.message);
                    }
                } catch (error) {
                    console.error('Error fetching bookings:', error);
                }
            }

            fetchAllBookings();

            // Handle Search Input
            bookingSearchInput.addEventListener('input', async (event) => {
                const searchTerm = event.target.value.trim();
                fetchAllBookings(searchTerm);
            });

            // Booking Info
            document.addEventListener('click', function (event) {
                if (event.target.closest('.booking-info-btn')) {
                    const bookingId = event.target.closest('.booking-info-btn').dataset.bookingId;

                    fetch(`PHP/ResourceBooking.php?action=get_booking_details&booking_id=${bookingId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.data.length > 0) {
                                const booking = data.data[0];
                                document.getElementById('bookingResourceName').textContent = booking.resource_name;
                                document.getElementById('bookingUserName').textContent = booking.user_name;
                                document.getElementById('bookingStartTime').textContent = booking.start_time;
                                document.getElementById('bookingEndTime').textContent = booking.end_time;
                                document.getElementById('bookingStatus').textContent = booking.status;
                                document.getElementById('bookingRequestedAt').textContent = booking.requested_at;
                                document.getElementById('bookingReason').textContent = booking.reason;
                                document.getElementById('bookingApprovedBy').textContent = booking.approved_by;
                                document.getElementById('bookingUpdatedAt').textContent = booking.updated_at;

                                document.getElementById('bookingDetailsOverlay').style.display = 'flex';
                            } else {
                                show('error');
                            }
                        })
                        .catch(error => console.error('Fetch error:', error));
                }
            });

            // Close Booking Details Overlay
            function closeBookingDetailsOverlay() {
                document.getElementById('bookingDetailsOverlay').style.display = 'none';
            }

            window.closeBookingDetailsOverlay = closeBookingDetailsOverlay;
        });

    </script>
</body>
</html>