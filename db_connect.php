<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pbl2";

// MySQL 데이터베이스에 연결
$conn = new mysqli($servername, $username, $password, $dbname, 3306, '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock');

// 연결 확인
if ($conn->connect_error) {
    die("연결 실패: " . $conn->connect_error);
} else {
    echo "연결 성공!";
}
?>
