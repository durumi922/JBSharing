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

// 사용자가 작성한 모든 리뷰를 가져오는 쿼리
$sql_리뷰내역 = "SELECT 숙소리뷰.*, 숙소.숙소이름, 숙소.숙소위치
               FROM 숙소리뷰
               JOIN 숙소 ON 숙소리뷰.id숙소 = 숙소.id숙소
               WHERE 숙소리뷰.id회원 = '$id회원'";
$result_리뷰내역 = $conn->query($sql_리뷰내역);

// 디버깅 메시지: 쿼리 결과 확인
if (!$result_리뷰내역) {
    die("Query failed: " . $conn->error);
}

// 연결 종료
$conn->close();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>리뷰 내역</title>
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
        <h1>리뷰 내역</h1>
        <?php if ($result_리뷰내역->num_rows > 0): ?>
            <ul>
                <?php while($row = $result_리뷰내역->fetch_assoc()): ?>
                    <li>
                        <p>숙소 이름: <?php echo htmlspecialchars($row['숙소이름']); ?></p>
                        <p>위치: <?php echo htmlspecialchars($row['숙소위치']); ?></p>
                        <p>작성일자: <?php echo htmlspecialchars($row['작성일자']); ?></p>
                        <p>별점: <?php echo htmlspecialchars($row['별점']); ?></p>
                        <p>리뷰 내용: <?php echo nl2br(htmlspecialchars($row['숙소리뷰_내용'])); ?></p>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>작성된 리뷰가 없습니다.</p>
        <?php endif; ?>
    </div>
</body>
</html>
