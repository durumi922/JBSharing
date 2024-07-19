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

// 리뷰 데이터 가져오기
$id예약 = $_POST['id예약'];
$id숙소 = $_POST['id숙소'];
$rating = $_POST['rating'];
$review = $_POST['review'];

// id숙소리뷰 생성
$sql_last_review = "SELECT COUNT(*) AS 리뷰수 FROM 숙소리뷰 WHERE id숙소 = '$id숙소'";
$result_last_review = $conn->query($sql_last_review);
$row_last_review = $result_last_review->fetch_assoc();
$리뷰수 = $row_last_review['리뷰수'] + 1;
$id숙소리뷰 = $id숙소 . "R" . $리뷰수;

// 리뷰 작성일자
$작성일자 = date("Y-m-d");

// 리뷰 데이터 삽입
$sql_insert_review = "INSERT INTO 숙소리뷰 (id숙소리뷰, id예약, id숙소, id회원, 별점, 숙소리뷰_내용, 작성일자)
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql_insert_review);
$stmt->bind_param("ssssiss", $id숙소리뷰, $id예약, $id숙소, $id회원, $rating, $review, $작성일자);

if ($stmt->execute()) {
    echo "리뷰가 성공적으로 저장되었습니다.";
} else {
    echo "리뷰 저장에 실패했습니다: " . $conn->error;
}

// 연결 종료
$stmt->close();
$conn->close();

// 리뷰 작성 후 예약 내역 페이지로 리디렉션
header("Location: reservation_history.php");
exit;
?>
