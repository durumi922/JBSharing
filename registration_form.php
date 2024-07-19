<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입</title>
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
            width: 400px; /* 회원가입 폼의 너비를 늘림 */
        }
        .logo {
            width: auto;
            height: 100px;
        }
        h2 {
            color: #333;
            font-size: 2em;
            font-weight: bold;
        }
        label {
            display: block;
            width: 100%;
            text-align: left;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-weight: bold;
            box-sizing: border-box;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
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
        }
        .button:hover {
            background-color: #f2e6ff;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="./mian_icon3.png" alt="Header Image" class="logo">
        <h2>회원가입</h2>
        <form action="register_user.php" method="post">
            <div class="form-group">
                <input type="text" placeholder="이름" name="name" id="name" required>
            </div>
            <div class="form-group">
                <label for="birth_date">생년월일</label>
                <input type="text" name="birth_date" id="birth_date" pattern="\d{6}" placeholder="YYMMDD" required>
            </div>
            <div class="form-group">
                <label for="phone">전화번호</label>
                <input type="text" name="phone" id="phone" required>
            </div>
            <div class="form-group">
                <label for="email">이메일</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">비밀번호</label>
                <input type="password" name="password" id="password" required>
            </div>
            <input type="submit" value="가입하기" class="button">
        </form>
        <p>이미 회원이신가요? <a href="login_form.php">로그인</a></p>
    </div>
</body>
</html>
