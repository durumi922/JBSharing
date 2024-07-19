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

// 호스트인지 확인
$id회원 = $row['id회원'];
$sql = "SELECT COUNT(*) AS count FROM 호스트 WHERE id회원 = '$id회원'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$isHost = $row['count'] > 0;

// 세션에 호스트 여부 저장
$_SESSION['is_host'] = $isHost;

// 연결 종료
$conn->close();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>마이페이지</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4NgSiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous"/>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="mypage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <img src="main_icon2.png" alt="Logo">
            <i class="fa-solid fa-house"></i>
        </div>
        <ul class="nav-btn">
            <?php if ($isHost): ?>
                <li><a href="host.php">호스트 모드로 전환</a></li>
            <?php else: ?>
                <li><a href="accommodation.php">숙소 등록 시작하기</a></li>
            <?php endif; ?>
            <li><a href="homepage.php">홈으로 이동</a></li>
        </ul>
    </div>
    <div class="banner">
        <img src="logo2color.png" alt="Banner Image">
    </div>
    <div class="side-bar">
        <ul>
            <li>
                <a href="./homepage.php" class="item">
                    <span class="fa-solid fa-house side-bar-icon"></span>
                    <span class="side-bar-text">숙소 보러가기</span>
                </a>
            </li>
            <li>
                <a href="./profile.php" class="item">
                    <span class="fa-solid fa-user side-bar-icon"></span>
                    <span class="side-bar-text">프로필</span>
                </a>
            </li>
            <li>
                <a href="./reservation_status.php" class="item">
                    <span class="fa-solid fa-calendar-days side-bar-icon"></span>
                    <span class="side-bar-text">예약 현황</span>
                </a>
            </li>
            <li>
                <a href="./reservation_history.php" class="item">
                    <span class="fa-solid fa-book side-bar-icon"></span>
                    <span class="side-bar-text">예약 내역</span>
                </a>
            </li>
            <li>
                <a href="./review_history.php" class="item">
                    <span class="fa-solid fa-pen-to-square side-bar-icon"></span>
                    <span class="side-bar-text">남긴 리뷰</span>
                </a>
            </li>
        </ul>
    </div>
    <div class="main-content">
        <img src="main_icon.png" alt="Content Image" class="content-image">
        <div class="content-text">
            <h1>Welcome to My Page</h1>
            
        </div>
    </div>
    <script>
        window.onscroll = function() {
            var sideBar = document.querySelector('.side-bar');
            if (window.pageYOffset > 0) {
                sideBar.style.top = (10 + window.pageYOffset) + 'px';
            } else {
                sideBar.style.top = '10px';
            }
        }
    </script>
</body>
</html>
