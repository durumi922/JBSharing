<?php
// 세션 시작
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login_form.php");
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

// 세션에 저장된 사용자 이메일을 사용하여 사용자 정보 가져오기
$email = $_SESSION['email'];
$sql = "SELECT * FROM 회원_게스트 WHERE 이메일='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user_row = $result->fetch_assoc();
} else {
    echo "사용자 정보를 불러올 수 없습니다.";
    exit;
}

// 호스트인지 확인
$id회원 = $user_row['id회원'];
$sql = "SELECT id호스트 FROM 호스트 WHERE id회원 = '$id회원'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$isHost = $row ? true : false;
$id호스트 = $row['id호스트'] ?? '';

// 호스트가 등록한 숙소 목록 가져오기
$숙소들 = [];
if ($isHost) {
    $sql = "SELECT 숙소.*, GROUP_CONCAT(숙소사진.이미지주소경로) AS 이미지주소경로
            FROM 숙소
            LEFT JOIN 호스트_숙소 ON 숙소.id숙소 = 호스트_숙소.id숙소
            LEFT JOIN 숙소사진 ON 숙소.id숙소 = 숙소사진.id숙소
            WHERE 호스트_숙소.id호스트 = '$id호스트'
            GROUP BY 숙소.id숙소";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $숙소들[] = $row;
        }
    }
}

// 연결 종료
$conn->close();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>호스트 페이지</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwVCh9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous"/>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="mypage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .accommodations {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 40px auto;
            max-width: 1000px;
        }
        .accommodation {
            border: 1px solid #cb9bfb;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s;
            padding: 10px;
            margin-bottom: 20px;
        }
        .accommodation img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .accommodation h3 {
            margin: 0 0 10px;
            font-size: 1.2em;
            font-family: 'GmarketSansMedium';
        }
        .accommodation p {
            margin: 5px 0;
            color: #555;
            font-size: 14px;
        }
        .accommodation a {
            text-decoration: none;
            color: inherit;
        }
        .accommodation:hover {
            transform: translateY(-10px);
        }
        .register-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
        }
        .register-button:hover {
            background-color: #45a049;
        }
    </style>
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
    <div class="banner">
        <img src="logo2color.png" alt="Banner Image">
    </div>
    <div class="content">
        <h1>등록된 숙소 목록</h1>
        <div class="accommodations">
            <?php if (count($숙소들) > 0): ?>
                <?php foreach ($숙소들 as $숙소): ?>
                    <div class="accommodation">
                        <a href="host_reservations.php?id=<?php echo $숙소['id숙소']; ?>">
                            <?php if (!empty($숙소['이미지주소경로'])): ?>
                                <?php
                                $이미지주소경로들 = explode(',', $숙소['이미지주소경로']);
                                ?>
                                <img src="<?php echo $이미지주소경로들[0]; ?>" alt="<?php echo $숙소['숙소이름']; ?>">
                            <?php endif; ?>
                            <h3><?php echo $숙소['숙소이름']; ?></h3>
                            <p><?php echo $숙소['숙소위치']; ?></p>
                            <p>가격: <?php echo number_format($숙소['가격_1박']); ?>원</p>
                            <p>수용 인원: <?php echo $숙소['수용가능인원']; ?>명</p>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>등록된 숙소가 없습니다.</p>
            <?php endif; ?>
        </div>
        <a href="accommodation.php" class="register-button">숙소 등록하기</a>
    </div>
</body>
</html>
