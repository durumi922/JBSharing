<?php
// 오류 보고 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 데이터베이스 연결 정보
$servername = "localhost";
$username = "root";
$password = ""; // XAMPP에서 설정한 비밀번호
$dbname = "pbl2";
$port = 3306; // XAMPP에서 사용하는 MySQL 포트

// 사용자 입력 값 가져오기
$email = isset($_POST['email']) ? $_POST['email'] : '';
$name = isset($_POST['name']) ? $_POST['name'] : '';
$birth_year = isset($_POST['birth_year']) ? $_POST['birth_year'] : '';
$birth_month = isset($_POST['birth_month']) ? $_POST['birth_month'] : '';
$birth_day = isset($_POST['birth_day']) ? $_POST['birth_day'] : '';
$birthdate = $birth_year . '-' . $birth_month . '-' . $birth_day;
$phone = isset($_POST['phone']) ? $_POST['phone'] : '';

// 데이터베이스 연결
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// 연결 확인
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 사용자 정보 확인
$sql = "SELECT 비밀번호 FROM 회원_게스트 WHERE 이메일='$email' AND 이름='$name' AND 생년월일='$birthdate' AND 전화번호='$phone'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>비밀번호 찾기 결과</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e0e0ff;
            text-align: center;
            padding: 50px;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px #000;
            display: inline-block;
            max-width: 500px;
        }
        .logo {
            width: 100px;
            height: 100px;
        }
        .header-img {
            width: 100%;
            height: auto;
            margin-bottom: 20px;
        }
        h2 {
            color: #333;
        }
        .button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 10px;
        }
        .button-secondary {
            background-color: #f44336;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="main_icon4.png" alt="Header Image" class="header-img">
        <h2>비밀번호 찾기 결과</h2>
        <?php
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $password = $row['비밀번호'];
            echo "<p>비밀번호는 '$password' 입니다.</p>";
            echo '<a href="login_form.php"><button class="button">로그인 하러가기</button></a>';
        } else {
            echo "<p>일치하는 사용자가 없습니다.</p>";
            echo '<a href="forgot_password.php"><button class="button button-secondary">다시 시도하기</button></a>';
        }
        ?>
    </div>
</body>
</html>

<?php
// 연결 종료
$conn->close();
?>
