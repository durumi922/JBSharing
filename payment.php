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

$conn = new mysqli($servername, $username, $password, $dbname, $port);

// 연결 확인
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 예약 정보 가져오기
$id예약 = $_GET['id예약'];
$합계가격 = $_GET['합계가격'];

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $결제수단 = $_POST['결제수단'];
    $결제날짜 = date('Y-m-d');

    // 결제 정보 삽입
    $sql = "INSERT INTO 결제 (id예약, 결제금액, 결제수단, 결제날짜) 
            VALUES ('$id예약', $합계가격, '$결제수단', '$결제날짜')";
    if (!$conn->query($sql)) {
        die("Query failed: " . $conn->error);
    }

    // 예약 상태 업데이트
    $sql = "UPDATE 예약 SET 예약상태 = '예약 완료' WHERE id예약 = '$id예약'";
    if (!$conn->query($sql)) {
        die("Query failed: " . $conn->error);
    }

    echo "<script>alert('예약이 완료되었습니다.'); window.location.href='mypage.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>결제 페이지</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <img src="main_icon2.png" alt="Logo">
            <i class="fa-solid fa-house"></i>
        </div>
        <ul class="nav-btn">
            <li><a href="homepage.php">홈페이지</a></li>
            <li><a href="mypage.php">마이페이지</a></li>
            <li><a href="logout.php">로그아웃</a></li>
        </ul>
    </div>
    <h1>결제 페이지</h1>
    <form action="payment.php?id예약=<?php echo $id예약; ?>&합계가격=<?php echo $합계가격; ?>" method="post">
        <p>결제 금액: ₩<?php echo number_format($합계가격); ?></p>
        <label for="결제수단">결제 수단</label>
        <select id="결제수단" name="결제수단" required>
            <option value="계좌이체">계좌이체</option>
            <option value="카카오페이">카카오페이</option>
            <option value="네이버페이">네이버페이</option>
            <option value="카드">카드</option>
        </select>
        <button type="submit">결제하기</button>
    </form>
</body>
</html>
