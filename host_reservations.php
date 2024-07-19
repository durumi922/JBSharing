<?php
// 세션 시작
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['is_host']) || !$_SESSION['is_host']) {
    echo "<script>alert('호스트만 접근할 수 있습니다.'); window.location.href='mypage.php';</script>";
    exit();
}

// 데이터베이스 연결 정보
$servername = "localhost";
$username = "root";
$password = ""; // XAMPP에서 설정한 비밀번호
$dbname = "pbl2";
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id숙소 = $_GET['id'] ?? '';
if (empty($id숙소)) {
    echo "<script>alert('숙소 ID가 제공되지 않았습니다.'); window.location.href='host.php';</script>";
    exit();
}

// 예약 상태 변경 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reservation_id'], $_POST['status'])) {
    $reservation_id = $_POST['reservation_id'];
    $status = $_POST['status'];

    $update_sql = "UPDATE 예약 SET 예약상태 = ? WHERE id예약 = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ss", $status, $reservation_id);
    $stmt->execute();
    $stmt->close();
}

// 숙소 이름 가져오기
$sql = "SELECT 숙소이름 FROM 숙소 WHERE id숙소 = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id숙소);
$stmt->execute();
$stmt->bind_result($숙소이름);
$stmt->fetch();
$stmt->close();

// 숙소 예약 목록 가져오기
$sql = "SELECT 예약.*, 회원_게스트.이름 AS 게스트이름, 회원_게스트.이메일 AS 게스트이메일
        FROM 예약
        LEFT JOIN 회원_게스트 ON 예약.id회원 = 회원_게스트.id회원
        WHERE 예약.id숙소 = ? AND (예약.예약상태 = '예약 완료' OR 예약.예약상태 = '숙박 중')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id숙소);
$stmt->execute();
$result = $stmt->get_result();
$예약들 = [];
while ($row = $result->fetch_assoc()) {
    $예약들[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($숙소이름); ?> - 예약 현황</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4NgSiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous"/>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="mypage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <img src="main_icon2.png" alt="Logo">
            <i class="fa-solid fa-house"></i>
        </div>
        <ul class="nav-btn">
            <li><a href="host.php">숙소 목록</a></li>
            <li><a href="homepage.php">홈으로 이동</a></li>
        </ul>
    </div>
    <div class="banner">
        <img src="logo2color.png" alt="Banner Image">
    </div>
    <div class="content">
        <h1><?php echo htmlspecialchars($숙소이름); ?> - 예약 현황</h1>
        <div class="reservations">
            <?php if (count($예약들) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>게스트 이름</th>
                            <th>이메일</th>
                            <th>체크인 날짜</th>
                            <th>체크아웃 날짜</th>
                            <th>예약 상태</th>
                            <th>상태 변경</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($예약들 as $예약): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($예약['게스트이름']); ?></td>
                                <td><?php echo htmlspecialchars($예약['게스트이메일']); ?></td>
                                <td><?php echo htmlspecialchars($예약['체크인_날짜']); ?></td>
                                <td><?php echo htmlspecialchars($예약['체크아웃_날짜']); ?></td>
                                <td><?php echo htmlspecialchars($예약['예약상태']); ?></td>
                                <td>
                                    <form method="POST" action="host_reservations.php?id=<?php echo $id숙소; ?>">
                                        <input type="hidden" name="reservation_id" value="<?php echo $예약['id예약']; ?>">
                                        <select name="status">
                                            <option value="숙박 중" <?php echo $예약['예약상태'] == '숙박 중' ? 'selected' : ''; ?>>숙박 중</option>
                                            <option value="체크아웃 완료" <?php echo $예약['예약상태'] == '체크아웃 완료' ? 'selected' : ''; ?>>체크아웃 완료</option>
                                        </select>
                                        <button type="submit">변경</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>현재 예약된 내역이 없습니다.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
