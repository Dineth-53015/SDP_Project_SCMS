<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCMS</title>
    <link rel="icon" href="Images/Icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/Styles.css">
</head>

<body>

    <!-- Main UI -->
    <div class="index-global-reset index-page-container">
        <div class="index-background-image"></div>
        <header class="index-header">Campus System</header>
        <div class="index-container">
            <h1>Welcome to the Campus Portal</h1>
            <br>
            <button class="index-sign-in-button"  id="openModal">Sign In</button>
        </div>
        <footer class="index-footer">Copyright &copy; 2025 Smart Campus. Designed & Developed by <a href="https://wa.me/+94772957834">Group 04</a></footer>
    </div>

    <!-- Registration Form -->
    <div class="overlay" id="overlay">
        <div class="overlay-content">
            <p class="title">Register </p>
            <p class="message">Signup and get access to the system. </p>
            <form id="RegistrationForm">
                <div class="scrollable-content">
                    <label>
                        <input required placeholder="Name" type="text" class="input" name="name">
                    </label>

                    <label>
                        <input required placeholder="Email" type="email" class="input" name="email">
                    </label>

                    <label>
                        <input required placeholder="Username" type="text" class="input" name="username">
                    </label>

                    <label>
                        <input required placeholder="Passsword" type="password" class="input" name="password">
                    </label>

                    <label>
                        <select class="input" required name="role">
                            <option value="">Role</option>
                            <option value="Student">Student</option>
                            <option value="Lecturer">Lecturer</option>
                            <option value="Administrator">Administrator</option>
                        </select>
                    </label>

                    <label>
                        <input required placeholder="Phone Number" type="text" class="input" name="phone_number">
                    </label>

                    <label>
                        <select class="input" required name="faculty">
                            <option value="">Faculty</option>
                            <option value="Information Technology">Information Technology</option>
                            <option value="Arts">Arts</option>
                            <option value="Education">Education</option>
                            <option value="Graduate Studies">Graduate Studies</option>
                            <option value="Indigenous Medicine">Indigenous Medicine</option>
                            <option value="Education">Education</option>
                            <option value="Law">Law</option>
                            <option value="Management & Finance">Management & Finance</option>
                            <option value="Medicine">Medicine</option>
                            <option value="Science">Science</option>
                            <option value="Technology">Technology</option>
                            <option value="Nursing">Nursing</option>
                            <option value="Staff">Staff</option>
                        </select>
                    </label>
                </div>
                <div>
                    <button type="submit">Register</button>
                </div>
                <p class="signin">Already have an account ? <a id="SignIn">Sign In</a> </p>
            </form>
        </div>
    </div>

    <!-- Sign In Form -->
    <div class="overlay" id="soverlay">
        <div class="overlay-content">
            <p class="title">Sign In</p>
            <p class="message">Signin and get access to the system.</p>
            <form id="SignInForm">
                <div class="scrollable-content">
                    <label>
                        <input required placeholder="Username or Email" type="text" class="input">
                    </label>

                    <label>
                        <input required placeholder="Passsword" type="password" class="input">
                    </label>
                </div>
                <div>
                    <button type="submit">Sign In</button>
                </div>
                <p class="signin">Don't have an account ? <a id="Register">Sign Up</a> </p>
            </form>
        </div>
    </div>

    <!-- OTP Verification Form -->
    <div class="overlay" id="evoverlay">
        <div class="overlay-content OTP">
            <p class="title">OTP Verification Code</p>
            <p class="message">We have sent a verification code to your email address</p>
            <form id="OTPForm">
                <div class="stack">
                    <label>
                    <input required type="text" class="input" maxlength="1" oninput="handleInput(this)" onkeydown="handleBackspace(this, event)">
                    <input required type="text" class="input" maxlength="1" oninput="handleInput(this)" onkeydown="handleBackspace(this, event)">
                    <input required type="text" class="input" maxlength="1" oninput="handleInput(this)" onkeydown="handleBackspace(this, event)">
                    <input required type="text" class="input" maxlength="1" oninput="handleInput(this)" onkeydown="handleBackspace(this, event)">
                    <input required type="text" class="input" maxlength="1" oninput="handleInput(this)" onkeydown="handleBackspace(this, event)">
                    <input required type="text" class="input" maxlength="1" oninput="handleInput(this)" onkeydown="handleBackspace(this, event)">
                    </label>
                </div>
                <div>
                    <button type="submit">Verify</button>
                </div>
            </form>
        </div>
    </div>

    <script>

        const openModalBtn = document.getElementById('openModal');
        openModalBtn.addEventListener('click', () => {
            soverlay.style.display = 'flex';
        });

        const SignInBtn = document.getElementById('SignIn');
        SignInBtn.addEventListener('click', () => {
            overlay.style.display = 'none';
            soverlay.style.display = 'flex';
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.style.display = 'none';
            }
        });

        soverlay.addEventListener('click', (e) => {
            if (e.target === soverlay) {
                soverlay.style.display = 'none';
            }
        });

        const RegisterBtn = document.getElementById('Register');
        RegisterBtn.addEventListener('click', () => {
            overlay.style.display = 'flex';
            soverlay.style.display = 'none';
        });

        function handleInput(input) {
            if (input.value.length > 1) {
                input.value = input.value.slice(0, 1);
            }

            if (input.value && input.nextElementSibling) {
                input.nextElementSibling.focus();
            }
        }

        function handleBackspace(input, event) {
            if (event.key === 'Backspace' && !input.value && input.previousElementSibling) {
                input.previousElementSibling.focus();
            }
        }

        // Send OTP
        document.getElementById('RegistrationForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);

            fetch('PHP/OTPMail.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data === "success") {
                    overlay.style.display = 'none';
                    evoverlay.style.display = 'flex';
                } else {
                    alert("Error sending OTP: " + data);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });

        // Register User
        document.getElementById('OTPForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const otp = Array.from(this.querySelectorAll('input')).map(input => input.value).join('');

            fetch('PHP/Registrations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ otp: otp })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Registration successful! Your account will be reviewed as soon as possible by our staff and be approved.");
                    evoverlay.style.display = 'none';
                } else {
                    alert("OTP verification failed. Please try again.");
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });

        // Check User Credentials and Allow User to Sign In
        document.getElementById('SignInForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const usernameOrEmail = this.querySelector('input[type="text"]').value;
            const password = this.querySelector('input[type="password"]').value;

            fetch('PHP/Users.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'sign_in',
                    usernameOrEmail: usernameOrEmail,
                    password: password
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirectUrl;
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });

    </script>
</body>

</html>