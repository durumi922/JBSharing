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

// 리뷰 저장 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['review_content'], $_POST['rating'], $_POST['reservation_id'])) {
    $review_content = $_POST['review_content'];
    $rating = $_POST['rating'];
    $reservation_id = $_POST['reservation_id'];

    // 예약 정보 가져오기
    $sql_reservation = "SELECT * FROM 예약 WHERE id예약='$reservation_id' AND id회원='$id회원' AND 예약상태='체크아웃 완료'";
    $result_reservation = $conn->query($sql_reservation);

    if ($result_reservation->num_rows > 0) {
        $reservation = $result_reservation->fetch_assoc();
        $id숙소 = $reservation['id숙소'];

        // 해당 예약에 대한 리뷰가 이미 존재하는지 확인
        $sql_check_review = "SELECT COUNT(*) AS 리뷰개수 FROM 숙소리뷰 WHERE id예약='$reservation_id'";
        $result_check_review = $conn->query($sql_check_review);
        $row_check_review = $result_check_review->fetch_assoc();
        
        if ($row_check_review['리뷰개수'] == 0) {
            // 해당 숙소의 리뷰 수 확인
            $sql_review_count = "SELECT COUNT(*) AS 리뷰개수 FROM 숙소리뷰 WHERE id숙소='$id숙소'";
            $result_review_count = $conn->query($sql_review_count);
            $row_review_count = $result_review_count->fetch_assoc();
            $리뷰개수 = $row_review_count['리뷰개수'] + 1;

            // 리뷰 ID 생성
            $review_id = $id숙소 . 'R' . $리뷰개수;
            $current_date = date('Y-m-d');

            // 리뷰 저장
            $sql_insert_review = "INSERT INTO 숙소리뷰 (id숙소리뷰, id예약, id숙소, id회원, 별점, 숙소리뷰_내용, 작성일자) 
                                  VALUES ('$review_id', '$reservation_id', '$id숙소', '$id회원', '$rating', '$review_content', '$current_date')";
            if ($conn->query($sql_insert_review) === TRUE) {
                echo "<script>alert('리뷰가 저장되었습니다.');</script>";
            } else {
                echo "<script>alert('리뷰 저장 중 오류가 발생했습니다.');</script>";
            }
        } else {
            echo "<script>alert('해당 예약에 대한 리뷰가 이미 존재합니다.');</script>";
        }
    } else {
        echo "<script>alert('유효하지 않은 요청입니다.');</script>";
    }
}

// 예약 내역 쿼리 (예약 상태가 '숙박 중' 또는 '체크아웃 완료'인 것들만)
$sql_예약내역 = "SELECT 예약.*, 숙소.숙소이름, 숙소.숙소위치
                FROM 예약
                JOIN 숙소 ON 예약.id숙소 = 숙소.id숙소
                WHERE 예약.id회원 = '$id회원' AND (예약.예약상태 = '숙박 중' OR 예약.예약상태 = '체크아웃 완료')";
$result_예약내역 = $conn->query($sql_예약내역);

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>예약 내역</title>
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
        <h1>예약 내역</h1>
        <?php if ($result_예약내역->num_rows > 0): ?>
            <ul>
                <?php while($row = $result_예약내역->fetch_assoc()): ?>
                    <li>
                        <p>숙소 이름: <?php echo htmlspecialchars($row['숙소이름']); ?></p>
                        <p>위치: <?php echo htmlspecialchars($row['숙소위치']); ?></p>
                        <p>체크인 날짜: <?php echo htmlspecialchars($row['체크인_날짜']); ?></p>
                        <p>체크아웃 날짜: <?php echo htmlspecialchars($row['체크아웃_날짜']); ?></p>
                        <p>합계 가격: ₩<?php echo number_format($row['합계가격']); ?></p>
                        <p>숙박 인원: <?php echo htmlspecialchars($row['숙박인원']); ?></p>
                        <?php
                        $reservation_id = $row['id예약'];
                        $sql_check_review = "SELECT COUNT(*) AS 리뷰개수 FROM 숙소리뷰 WHERE id예약='$reservation_id'";
                        $result_check_review = $conn->query($sql_check_review);
                        $row_check_review = $result_check_review->fetch_assoc();
                        if ($row['예약상태'] == '체크아웃 완료' && $row_check_review['리뷰개수'] == 0): ?>
                            <form method="POST" action="reservation_history.php">
                                <h3>리뷰 작성</h3>
                                <textarea name="review_content" required></textarea>
                                <input type="number" name="rating" min="1" max="5" required>
                                <input type="hidden" name="reservation_id" value="<?php echo $row['id예약']; ?>">
                                <button type="submit">리뷰 저장</button>
                            </form>
                        <?php elseif ($row_check_review['리뷰개수'] > 0): ?>
                            <p>리뷰가 이미 작성되었습니다.</p>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>예약 내역이 없습니다.</p>
        <?php endif; ?>
    </div>
    <?php $conn->close(); ?>
</body>
</html>
