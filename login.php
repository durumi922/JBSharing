<?php
// 오류 보고 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 세션 시작
session_start();

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

// 사용자 입력 값 가져오기
$email = $_POST['email'];
$user_password = $_POST['password'];

// 사용자 정보 확인
$sql = "SELECT * FROM 회원_게스트 WHERE 이메일='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stored_password = $row['비밀번호'];
    
    if ($user_password === $stored_password) {
        // 세션에 사용자 정보 저장
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $row['이름'];
        echo "<script>alert('로그인 성공!'); window.location.href='homepage.php';</script>";

    } else {
        echo "<script>alert('비밀번호가 잘못되었습니다.'); window.location.href='login_form.php';</script>";
    }
} else {
    echo "<script>alert('일치하는 사용자가 없습니다.'); window.location.href='registration_form.php';</script>";
}


// 연결 종료
$conn->close();
?>
