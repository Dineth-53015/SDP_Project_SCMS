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

    <?php include 'PHP/checkLecturer.php'?>

    <?php include 'Message.html'?>

    <header>
        Hi There, <?php echo htmlspecialchars($name); ?>!
    </header>

    <div class="container">

        <section id="overview" class="card">
            <h2>Schedules Overview</h2>
            <?php include 'ScheduleCharts.html'?>
        </section>

        <section id="overview" class="card">
            <h2>Tasks Overview</h2>
            <?php include 'TaskCharts.html'?>
        </section>

    </div>

    <?php include 'Chats.php'?>

    <?php include 'Footer.html'?>
    
</body>
</html>