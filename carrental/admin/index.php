<?php
session_start();
include('includes/config.php');

if (isset($_POST['login'])) {
    $email = $_POST['username'];
    $password = md5($_POST['password']);
    $sql = "SELECT UserName, Password FROM admin WHERE UserName=:email and Password=:password";
    $query = $dbh->prepare($sql);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    
    if ($query->rowCount() > 0) {
        $_SESSION['alogin'] = $_POST['username'];
        echo "<script type='text/javascript'> document.location = 'dashboard.php'; </script>";
    } else {
        $error = "Invalid username or password"; // Clear error message
    }
}
?>
<!doctype html>
<html lang="en" class="no-js">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Car Rental Portal | Admin Login</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        .login-page {
            background-image: url('img/login-bg.jpg');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-content {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .form-content:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }

        h1 {
            color: #007bff;
            margin-bottom: 20px;
            text-align: center;
            font-size: 2rem; /* Increased font size */
            font-weight: bold;
        }

        .form-control {
            border-radius: 25px;
            box-shadow: none;
            border: 1px solid #ced4da;
            transition: border-color 0.3s ease;
            height: 50px;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        .btn-primary {
            background-color: #007bff;
            border-radius: 25px;
            padding: 12px;
            font-size: 16px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        .btn-back {
            background-color: #6c757d; /* Gray color */
            color: white;
            border-radius: 25px;
            padding: 12px;
            font-size: 16px;
            text-align: center;
            display: block;
            margin-top: 10px; /* Add some space */
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-back:hover {
            background-color: #5a6268; /* Darker gray */
            transform: translateY(-2px);
        }

        .text-uppercase {
            letter-spacing: 0.1em;
            font-weight: bold;
        }

        .password-container {
            position: relative;
            margin-bottom: 20px;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #007bff;
            font-size: 20px;
            user-select: none;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .form-content {
                padding: 20px;
                margin: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="login-page">
        <div class="form-content">
            <h1 class="text-uppercase">Admin | Sign in 🔑</h1>

            <?php if (isset($error)) { ?>
                <div class="error-message"><?php echo htmlentities($error); ?></div>
            <?php } ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username" class="text-uppercase">Username 📧</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="form-group password-container">
                    <label for="password" class="text-uppercase">Password 🔒</label>
                    <input type="password" name="password" class="form-control" required>
                    <span toggle="#password" class="toggle-password" onclick="togglePassword()">🐧</span>
                </div>

                <button type="submit" name="login" class="btn btn-primary btn-block text-uppercase">Login 🚀</button>
            </form>

            <a href="../index.php" class="btn btn-back text-uppercase">Back to Home 🏠</a>
        </div>
    </div>

    <script>
        let isPasswordVisible = false;

        function togglePassword() {
            const passwordInput = document.querySelector('input[name="password"]');
            const toggleEye = document.querySelector('.toggle-password');
            if (isPasswordVisible) {
                passwordInput.type = "password";
                toggleEye.innerHTML = "🐧"; // Penguin emoji for hide
            } else {
                passwordInput.type = "text";
                toggleEye.innerHTML = "🙈"; // Monkey emoji for show
            }
            isPasswordVisible = !isPasswordVisible;
        }
    </script>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
