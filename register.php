<?php
session_start();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cyb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['csrf_token'], $_POST['username'], $_POST['email'], $_POST['password'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Неверный CSRF токен!");
    }

    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    if (strlen($password) < 6 || !preg_match("#[0-9]+#", $password) || !preg_match("#[a-zA-Z]+#", $password) || !preg_match("/[\'^£$%&*()}{@#~?><>,|=_+!-]/", $password)) {
        echo "Слабый пароль! Придумайте сложнее!";
    } else {
        $checkUser = "SELECT * FROM users WHERE username = '$user'";
        $result = $conn->query($checkUser);

        if ($result->num_rows > 0) {
            echo "Пользователь с таким логином уже существует!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (username, password, email) VALUES ('$user', '$hashed_password', '$email')";

            if ($conn->query($sql) === TRUE) {
                echo "Новый пользователь зарегистрирован успешно!";
            } else {
                echo "Ошибка: " . $sql . "<br>" . $conn->error;
            }
        }
    }
}

$conn->close();
?>

<form action="register.php" method="post">
    Введите имя пользователя: <input type="text" name="username" required><br>
    Введите сложный пароль: <input type="password" name="password" required><br>
    Введите свой Email: <input type="email" name="email" required><br>
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="submit" value="Register">
</form>
