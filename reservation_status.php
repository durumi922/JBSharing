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
    $id회원 = $row['id회원'];
} else {
    echo "사용자 정보를 불러올 수 없습니다.";
    exit;
}

// 예약 현황 쿼리 (예약 상태가 '예약 완료'인 것들만)
$sql_예약현황 = "SELECT 예약.*, 숙소.숙소이름, 숙소.숙소위치
                FROM 예약
                JOIN 숙소 ON 예약.id숙소 = 숙소.id숙소
                WHERE 예약.id회원 = '$id회원' AND 예약.예약상태 = '예약 완료'";
$result_예약현황 = $conn->query($sql_예약현황);

// 연결 종료
$conn->close();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>예약 현황</title>
    <link rel="stylesheet" href="mypage.css">
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <img src="main_icon2.png" alt="Logo">
            <i class="fa-solid fa-house"></i>
        </div>
        <ul class="nav-btn">
            <li><a href="mypage.php">마이페이지</a></li>
            <li><a href="homepage.php">홈으로 이동</a></li>
        </ul>
    </div>
    <div class="content">
        <h1>예약 현황</h1>
        <?php if ($result_예약현황->num_rows > 0): ?>
            <ul>
                <?php while($row = $result_예약현황->fetch_assoc()): ?>
                    <li>
                        <p>숙소 이름: <?php echo htmlspecialchars($row['숙소이름']); ?></p>
                        <p>위치: <?php echo htmlspecialchars($row['숙소위치']); ?></p>
                        <p>체크인 날짜: <?php echo htmlspecialchars($row['체크인_날짜']); ?></p>
                        <p>체크아웃 날짜: <?php echo htmlspecialchars($row['체크아웃_날짜']); ?></p>
                        <p>합계 가격: ₩<?php echo number_format($row['합계가격']); ?></p>
                        <p>숙박 인원: <?php echo htmlspecialchars($row['숙박인원']); ?></p>
                        <p>예약 상태: <?php echo htmlspecialchars($row['예약상태']); ?></p>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>예약 현황이 없습니다.</p>
        <?php endif; ?>
    </div>
</body>
</html>
