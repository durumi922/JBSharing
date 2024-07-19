<?php
// 오류 보고 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 데이터베이스 연결 정보
$servername = "localhost";
$username = "root";
$password = ""; // XAMPP에서 설정한 비밀번호
$dbname = "pbl2";
$port = 3306; // XAMPP에서 사용하는 MySQL 포트

// 사용자 입력 값 가져오기
$email = isset($_POST['email']) ? $_POST['email'] : '';

// 데이터베이스 연결
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// 연결 확인
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 사용자 정보 확인
$sql = "SELECT * FROM 회원_게스트 WHERE 이메일='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // 비밀번호 재설정 링크 생성 (실제로는 이메일로 전송해야 함)
    $reset_link = "http://localhost:8080/pbl2/reset_password.php?email=" . $email;
    echo "<p>비밀번호 재설정 링크가 생성되었습니다.</p>";
    echo '<a href="' . $reset_link . '"><button>비밀번호 재설정하기</button></a>';
} else {
    echo "<p>일치하는 사용자가 없습니다.</p>";
    echo '<a href="forgot_password.php"><button>다시 시도하기</button></a>';
}

// 연결 종료
$conn->close();
?>
