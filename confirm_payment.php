<?php
// 오류 보고 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 세션 시작
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login_form.php");
    exit();
}

// 폼 데이터 가져오기
$id숙소 = $_POST['id숙소'];
$체크인날짜 = $_POST['체크인날짜'];
$체크아웃날짜 = $_POST['체크아웃날짜'];
$인원수 = $_POST['인원수'];
$결제금액 = $_POST['결제금액'];
$결제수단 = $_POST['결제수단'];
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>결제 확인</title>
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
    <div class="content">
        <h1>결제 확인</h1>
        <p>체크인 날짜: <?php echo $체크인날짜; ?></p>
        <p>체크아웃 날짜: <?php echo $체크아웃날짜; ?></p>
        <p>숙박 인원: <?php echo $인원수; ?></p>
        <p>결제 금액: ₩<?php echo number_format($결제금액); ?></p>
        <p>결제 수단: <?php echo $결제수단; ?></p>
        <form action="reservation_process.php" method="post">
            <input type="hidden" name="id숙소" value="<?php echo $id숙소; ?>">
            <input type="hidden" name="체크인날짜" value="<?php echo $체크인날짜; ?>">
            <input type="hidden" name="체크아웃날짜" value="<?php echo $체크아웃날짜; ?>">
            <input type="hidden" name="인원수" value="<?php echo $인원수; ?>">
            <input type="hidden" name="결제금액" value="<?php echo $결제금액; ?>">
            <input type="hidden" name="결제수단" value="<?php echo $결제수단; ?>">
            <button type="submit">결제하기</button>
        </form>
    </div>
</body>
</html>
