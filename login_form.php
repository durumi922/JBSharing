<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e0e0ff;
            text-align: center;
            padding: 50px;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px #000;
            display: inline-block;
            width: 400px; /* 로그인 폼의 너비를 늘림 */
        }
        .logo {
            width: 100px;
            height: 100px;
            border-radius: 50%; /* 로고를 원형으로 */
        }
        h2 {
            color: #333;
        }
        input[type="email"],
        input[type="password"] {
            width: calc(100% - 22px); /* 입력란의 너비를 컨테이너에 맞춤 */
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .button {
            background-color: #fff;
            border: 2px solid #800080;
            color: #800080;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
            border-radius: 10px;
            display: block;
            margin: 0 auto;
            width: calc(100% - 22px); /* 버튼의 너비를 컨테이너에 맞춤 */

        }
        .button:hover {
            background-color: #f2e6ff;
        }
            /* width: calc(100% - 22px); /* 버튼의 너비를 컨테이너에 맞춤 */
        
            .button-secondary {
            background-color: #800080;
            border: 2px solid #fff;
            color: #fff;
            text-align: center;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            border-radius: 10px;
            width: 50%; /* 버튼의 너비를 넓힘 */
            margin-top: 10px;
            transition: background-color 0.3s; /* 색상 전환 효과 추가 */
        }
        .button-secondary:hover {
            background-color: #f2f2f2; /* 커서가 올라갔을 때 색상 변경 */
            color: #800080; /* 텍스트 색상 변경 */
        }
        .additional-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .additional-buttons a {
            text-decoration: none;
            color: white;
            width: 48%; /* 링크의 너비를 버튼과 동일하게 설정 */
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="main_icon.png" alt="Logo" class="logo">
        <h2>로그인</h2>
        <form action="login.php" method="post">
            <input type="email" placeholder="이메일" name="email" required><br><br>
            <input type="password" placeholder="비밀번호" name="password" required><br><br>
            <input type="submit" value="로그인" class="button">
        </form>
        <div class="additional-buttons">
            <a href="forgot_password.php"><button class="button-secondary">비밀번호 찾기</button></a>
            <a href="registration_form.php"><button class="button-secondary">회원가입</button></a>
        </div>
    </div>
</body>
</html>
