<?php
// Start Session and Get Necessary Details
session_start();

$loggedIn = isset($_SESSION['user_id']) && isset($_SESSION['name']) && isset($_SESSION['role']);

if (!$loggedIn) {
    header('Location: index.php');
    exit;
}

$name = $_SESSION['name'];
$role = $_SESSION['role'];

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Campus Management System</title>
    <script src="https://cdn.jsdelivr.net/gh/cferdinandi/smooth-scroll/dist/smooth-scroll.polyfills.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="Images/Icon.ico" type="image/x-icon">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lobster&display=swap');
        * {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
        }

        #navi {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 0;
            position: relative;
            z-index: 1;
            background-color: transparent;
        }

        #navi .logo {
            flex: 1;
            text-align: left;
            margin-left: 5%;
        }

        #navi .logo h1 {
            font-family: 'Lobster', cursive;
            cursor: pointer;
            background-image: linear-gradient(135deg, #ff7e5f, #feb47b);
            color: transparent;
            -webkit-background-clip: text;
        }

        #navi .menu {
            flex: 4;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #navi .menu ul {
            display: flex;
            list-style: none;
            padding: 0;
            width: 100%;
            justify-content: center;
        }

        #navi .menu ul li {
            margin: 0 40px;
        }

        #navi .menu ul li a {
            text-decoration: none;
            color: #606163;
        }

        #navi .menu ul li a:hover {
            color: #FF5841;
        }

        #navi .icons {
            flex: 1;
            text-align: right;
            margin-right: 5%;
        }

        #navi .icons ul {
            display: flex;
            list-style: none;
            padding: 0;
            justify-content: flex-end;
        }

        #navi .icons ul li {
            margin-right: 20px;
        }

        #navi .icons ul li:last-child {
            margin-right: 0;
        }

        #navi .icons ul li a {
            text-decoration: none;
            margin: 0 10px;
        }

        #navi .icons ul li a i {
            font-size: 20px;
            background-image: linear-gradient(135deg, #ff7e5f, #feb47b);
            color: transparent;
            -webkit-background-clip: text;
        }

        #navi .icons ul li a i:hover {
            opacity: 0.7;
        }

        #sideNav {
            width: 250px;
            height: 100vh;
            overflow-y: auto;
            position: fixed;
            left: 0;
            top: 0;
            background: #f9f9f9;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 2;
            transition: 0.5s;
            border-radius: 0 40px 0 0;
            justify-content: center;
        }

        #sideNav::-webkit-scrollbar {
            display: none;
        }

        #sideNav h1 {
            font-family: 'Lobster', cursive;
            cursor: pointer;
            margin: 80px 0px 40px 0px;
            background-image: linear-gradient(135deg, #ff7e5f, #feb47b);
            color: transparent;
            -webkit-background-clip: text;
            text-align: center; 
        }

        #sideNav ul {
            list-style: none;
            padding: 0;
        }

        #sideNav ul li {
            margin-bottom: 20px;
        }

        #sideNav ul li a {
            text-decoration: none;
            border-radius: 15px;
            color: #565654;
            font-size: 18px;
            display: block;
            align-items: center;
            padding: 10px 30px;
            transition: all 0.3s ease;
            margin: 10px;
            position: relative;
        }

        #sideNav ul li a i {
            margin-right: 30px;
            width: 10px;
        }

        #sideNav ul li a:hover {
            color: #FF5841;
        }

        #sideNav ul li a:hover .fas {
            color: #FF5841;
        }

        .separator {
            border: none;
            border-top: 1px solid #a8a8a6;
            margin: 10px 10px;
        }

        #sideNav ul li a .fas {
            color: #565654;
        }

        #menuBtn {
            width: 0px;
            position: fixed;
            left: 68px;
            top: 42px;
            z-index: 2;
            cursor: pointer;
        }

        @media only screen and (max-width:1200px) {
            #menuBtn {
                width: 30px;
                top: 40px;
                left: 30px;
            }

            #navi {
                display: none;
            }
        }

        @media only screen and (min-width: 1200px) {
            #menuBtn {
                display: none;
            }

            #sideNav {
                display: none;
            }
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #e8e8e8;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #ff7e5f, #feb47b);
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <nav id="navi">
        <div class="logo">
            <a href="index.php" style="text-decoration: none;">
                <h1>SCMS</h1>
            </a>
        </div>

        <div class="menu">
            <ul>
                <?php if ($role === 'Administrator'): ?>
                    <li><a href="Home.php">Home</a></li>
                    <li><a href="Resources.php">Resources</a></li>
                    <li><a href="Schedules.php">Schedules</a></li>
                    <li><a href="Events.php">Events</a></li>
                    <li><a href="Courses.php">Courses</a></li>
                    <li><a href="Users.php">Users</a></li>
                <?php elseif ($role === 'Lecturer'): ?>
                    <li><a href="LHome.php">Home</a></li>
                    <li><a href="Tasks.php">Tasks</a></li>
                    <li><a href="LResources.php">Resources</a></li>
                    <li><a href="Schedules.php">Schedules</a></li>
                    <li><a href="Events.php">Events</a></li>
                <?php elseif ($role === 'Student'): ?>
                    <li><a href="SHome.php">Home</a></li>
                    <li><a href="CourseEnrollment.php">Courses</a></li>
                    <li><a href="SSchedules.php">Schedules</a></li>
                    <li><a href="TaskSubmission.php">Tasks</a></li>
                    <li><a href="EventRegistration.php">Events</a></li>
                    <li><a href="LResources.php">Resources</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="icons">
            <ul>
                <?php if ($role === 'Student'): ?>
                    <li><a href="#" id="notifIcon"><i class="fas fa-bell"></i></a></li>
                <?php endif; ?>
                <li><a href="Settings.php"><i class="fas fa-cog"></i></a></li>
                <?php if ($loggedIn): ?>
                    <li><a href="?logout=true"><i class="fas fa-sign-out-alt"></i></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <nav id="sideNav">
        <ul>
            <a href="index.php" style="text-decoration: none;">
                <h1>SCMS</h1>
            </a>
            <hr class="separator">
            <?php if ($role === 'Administrator'): ?>
                <li><a href="Home.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="Resources.php"><i class="fas fa-building"></i> Resources</a></li>
                <li><a href="Schedules.php"><i class="fas fa-calendar-alt"></i> Schedules</a></li>
                <li><a href="Events.php"><i class="fas fa-calendar-check"></i> Events</a></li>
                <li><a href="Courses.php"><i class="fas fa-graduation-cap"></i> Courses</a></li>
                <li><a href="Users.php"><i class="fas fa-users"></i> Users</a></li>
            <?php elseif ($role === 'Lecturer'): ?>
                <li><a href="LHome.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="Tasks.php"><i class="fas fa-tasks"></i> Tasks</a></li>
                <li><a href="LResources.php"><i class="fas fa-building"></i> Resources</a></li>
                <li><a href="Schedules.php"><i class="fas fa-calendar-alt"></i> Schedules</a></li>
                <li><a href="Events.php"><i class="fas fa-calendar-check"></i> Events</a></li>
            <?php elseif ($role === 'Student'): ?>
                <li><a href="SHome.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="CourseEnrollment.php"><i class="fas fa-graduation-cap"></i> Courses</a></li>
                <li><a href="SSchedules.php"><i class="fas fa-calendar-alt"></i> Schedules</a></li>
                <li><a href="TaskSubmission.php"><i class="fas fa-tasks"></i> Tasks</a></li>
                <li><a href="EventRegistration.php"><i class="fas fa-calendar-check"></i> Events</a></li>
                <li><a href="LResources.php"><i class="fas fa-building"></i> Resources</a></li>
            <?php endif; ?>
            <hr class="separator">
            <?php if ($role === 'Student'): ?>
                <li><a href="#" id="notifIcon"><i class="fas fa-bell"></i>Notifications</a></li>
            <?php endif; ?>
            <li><a href="Settings.php"><i class="fas fa-cog"></i>Settings</a></li>
            <hr class="separator">
            <?php if ($loggedIn): ?>
                <li><a href="?logout=true"><i class="fas fa-sign-out-alt"></i> Sign Out</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <i class="fas fa-bars" id="menuBtn"></i>
    
    <?php include 'Notifications.php'?>

    <script>
        var menuBtn = document.getElementById("menuBtn")
        var sideNav = document.getElementById("sideNav")
        sideNav.style.left = "-250px";
        menuBtn.onclick = function () {
            if (sideNav.style.left == "-250px") {
                sideNav.style.left = "0";
            }
            else {
                sideNav.style.left = "-250px";
            }
        }
        var scroll = new SmoothScroll('a[href*="#"]');

        document.getElementById('notifIcon').addEventListener('click', function(event) {
            event.preventDefault();
            var notifPanel = document.getElementById('notifPanelContainer');
            if (notifPanel.style.display === 'none' || notifPanel.style.display === '') {
                notifPanel.style.display = 'block';
            } else {
                notifPanel.style.display = 'none';
            }
        });
        
    </script>
</body>
</html>