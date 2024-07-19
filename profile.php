<?php
// 세션 시작
session_start();

// 로그인 여부 확인
if (!isset($_SESSION['email'])) {
    header('Location: login_form.php');
    exit;
}

// 데이터베이스 연결 정보
$servername = "localhost";
$username = "root";
$password = ""; // XAMPP에서 설정한 비밀번호
$dbname = "pbl2";
$port = 3306; // XAMPP에서 사용하는 MySQL 포트

// 데이터베이스 연결
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// 연결 확인
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 세션에 저장된 사용자 이메일을 사용하여 사용자 정보 가져오기
$email = $_SESSION['email'];
$sql = "SELECT * FROM 회원_게스트 WHERE 이메일='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "사용자 정보를 불러올 수 없습니다.";
    exit;
}

// 연결 종료
$conn->close();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>프로필</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous"/>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="profile.css">
    <script>
        function enableEdit() {
            document.getElementById('editBtn').style.display = 'none';
            document.getElementById('saveBtn').style.display = 'block';

            var fields = document.getElementsByClassName('editable');
            for (var i = 0; i < fields.length; i++) {
                fields[i].removeAttribute('readonly');
                fields[i].classList.add('edit-mode');
            }
        }
    </script>
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <img src="main_icon2.png" alt="Logo">
            <i class="fa-solid fa-house"></i>
        </div>
        <ul class="nav-btn">
            <li><a href="host.php">호스트 모드로 전환</a></li>
            <li><a href="homepage.php">홈으로 이동</a></li>
        </ul>
    </div>
    <div class="banner">
        <img src="logo2.png" alt="Banner Image">
        <div class="banner-text">
            호스트 <?php echo htmlspecialchars($row['이름']); ?> 님의 프로필
        </div>
    </div>
    <div class="content">
        <div class="container">
            <form method="post" action="update_profile.php">
                <div class="profile">
                    <img src="main_icon.png" alt="Profile Picture">
                </div>
                <div class="user-info">
                    <p><input type="text" name="이름" value="<?php echo htmlspecialchars($row['이름']); ?>" class="editable" readonly></p>
                    <p><input type="date" name="생년월일" value="<?php echo htmlspecialchars($row['생년월일']); ?>" class="editable" readonly></p>
                    <p><input type="text" name="전화번호" value="<?php echo htmlspecialchars($row['전화번호']); ?>" class="editable" readonly></p>
                    <p><input type="email" name="이메일" value="<?php echo htmlspecialchars($row['이메일']); ?>" class="editable" readonly></p>
                    <p><input type="text" name="가입날짜" value="<?php echo htmlspecialchars($row['가입날짜']); ?>" class="editable" readonly></p>
                </div>
                <div class="buttons">
                    <button type="button" id="editBtn" class="button" onclick="enableEdit()">수정</button>
                    <button type="submit" id="saveBtn" class="button button-secondary" style="display:none;">프로필 저장</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
