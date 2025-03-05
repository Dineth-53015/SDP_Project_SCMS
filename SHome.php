<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="Images/Icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/Styles.css">
</head>
<body>
    <?php include 'NavBar.php'?>

    <?php include 'Message.html'?>

    <header>
        Hi There, <?php echo htmlspecialchars($name); ?>!
    </header>

    <div class="container">

        <section id="overview" class="card">
            <h2>Resources Overview</h2>
            <?php include 'ResourceCharts.html'?>
        </section>

        <section id="overview" class="card">
            <h2>Schedules Overview</h2>
            <?php include 'PHP/ScheduleCalender.html'?>
        </section>
    
        <section id="overview" class="card">
            <h2>Tasks</h2>
            <?php include 'PHP/TasksCalendar.php'?>
        </section>

    </div>

    <?php include 'Footer.html'?>
    
</body>
</html>