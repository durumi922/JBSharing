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

// 데이터베이스 연결
$servername = "localhost";
$username = "root";
$password = ""; // XAMPP에서 설정한 비밀번호
$dbname = "pbl2";
$port = 3306; // XAMPP에서 사용하는 MySQL 포트

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 폼 데이터 가져오기
$id숙소 = $_POST['id숙소'];
$체크인날짜 = $_POST['체크인날짜'];
$체크아웃날짜 = $_POST['체크아웃날짜'];
$인원수 = $_POST['인원수'];
$결제금액 = $_POST['결제금액'];
$결제수단 = $_POST['결제수단'];

// 세션에 저장된 사용자 이메일을 사용하여 사용자 정보 가져오기
$email = $_SESSION['email'];
$sql = "SELECT id회원 FROM 회원_게스트 WHERE 이메일='$email'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $id회원 = $row['id회원'];
} else {
    echo "사용자 정보를 불러올 수 없습니다.";
    exit();
}

// 새로운 예약 ID 생성
$sql = "SELECT MAX(id예약) AS max_id FROM 예약";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$max_id = $row['max_id'];
$new_id = 'B' . str_pad((int)substr($max_id, 1) + 1, 4, '0', STR_PAD_LEFT);

// 예약 테이블에 삽입
$sql = "INSERT INTO 예약 (id예약, id회원, id숙소, 체크인_날짜, 체크아웃_날짜, 예약상태, 합계가격, 숙박인원) 
        VALUES ('$new_id', '$id회원', '$id숙소', '$체크인날짜', '$체크아웃날짜', '예약 완료', $결제금액, $인원수)";
if (!$conn->query($sql)) {
    die("Query failed: " . $conn->error);
}

// 결제 테이블에 삽입
$sql = "INSERT INTO 결제 (id예약, 결제금액, 결제수단, 결제날짜) 
        VALUES ('$new_id', $결제금액, '$결제수단', NOW())";
if (!$conn->query($sql)) {
    die("Query failed: " . $conn->error);
}

echo "<script>alert('예약이 완료되었습니다.'); window.location.href='mypage.php';</script>";

$conn->close();
?>
