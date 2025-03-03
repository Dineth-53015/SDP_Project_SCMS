<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stacked Notifications Overlay</title>
    <style>
        .notif-panel-container {
            position: fixed;
            top: 70px;
            right: 50px;
            z-index: 1000;
            width: 300px;
            max-height: 70vh;
            overflow-y: auto;
            display: none;
            flex-direction: column;
            gap: 10px;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.9);
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-family: Arial, sans-serif;
        }

        .notif-item {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            padding: 10px;
            position: relative;
            margin: 0;
            margin-bottom: 5px;
        }

        .notif-timestamp {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 12px;
            color: #666;
            margin: 0;
        }

        .notif-content {
            padding: 2px;
            margin: 0;
            +
        }

        .notif-title {
            font-size: 14px;
            margin-bottom: 4px;
            color: #333;
            margin-top: 15px;
        }

        .notif-message {
            font-size: 13px;
            color: #555;
            margin: 5px 0px;
        }

        #addNotifBtn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #addNotifBtn:hover {
            background-color: #0056b3;
        }

        @media (max-width: 768px) {
            .notif-panel-container {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 90%;
                max-width: 300px;
                height: 70%;
                height: 700px;
                right: auto;
                margin: 0 auto;
            }
        }
    </style>
</head>

<body>
    <div class="notif-panel-container" id="notifPanelContainer">
        
    </div>

    <script>
        
        // Fetch and Display Notifications
        document.addEventListener('DOMContentLoaded', function () {
            const userId = <?php echo json_encode($_SESSION['user_id']); ?>;

            function addNotification(title, message, createdAt) {
                const date = new Date(createdAt);
                const timeString = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                const dateString = date.toISOString().split('T')[0];

                const notifItem = document.createElement('div');
                notifItem.classList.add('notif-item');

                const timestamp = document.createElement('span');
                timestamp.classList.add('notif-timestamp');
                timestamp.textContent = `${timeString} | ${dateString}`;
                notifItem.appendChild(timestamp);

                const content = document.createElement('div');
                content.classList.add('notif-content');
                content.innerHTML = `
                <h3 class="notif-title">${title}</h3>
                <p class="notif-message">${message}</p>
            `;
                notifItem.appendChild(content);

                const panel = document.getElementById('notifPanelContainer');
                panel.prepend(notifItem);
            }

            fetch('PHP/Notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=fetch_notifications&user_id=${userId}`
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Notifications data received:', data);
                    if (data.success) {
                        data.data.forEach(notification => {
                            addNotification(notification.title, notification.message, notification.created_at);
                        });
                    } else {
                        console.error('Failed to fetch notifications:', data.message);
                    }
                })
                .catch(error => console.error('Error fetching notifications:', error));
        });

    </script>
</body>

</html>