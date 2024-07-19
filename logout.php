<?php
// 세션 시작
session_start();

// 모든 세션 변수 제거
session_unset();

// 세션 파괴
session_destroy();

// 로그아웃 메시지 출력
echo "<p>로그아웃 되었습니다!</p>";
echo '<a href="login_form.php">로그인하기</a>';
?>
