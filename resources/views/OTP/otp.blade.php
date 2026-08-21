<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Verification Code - MediCore</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal&family=Roboto+Slab&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #E7F9FE;
            font-family: 'Roboto Slab', 'Tajawal', sans-serif;
            color: #46707A;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            max-width: 400px;
            background: #FFFFFF;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .logo {
            width: 100px;
            margin-bottom: 1rem;
        }

        h1 {
            font-family: 'Roboto Slab', serif;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #46707A;
        }

        p.welcome {
            font-size: 1.1rem;
            color: #7C8283;
            margin-bottom: 2rem;
        }

        .otp-box {
            font-size: 3rem;
            letter-spacing: 1rem;
            font-weight: 700;
            color: #32B2CF;
            background: #E7F9FE;
            padding: 1rem 2rem;
            border-radius: 12px;
            display: inline-block;
            margin-bottom: 1rem;
            font-family: 'Roboto Slab', serif;
            user-select: all;
        }

        .timer {
            font-size: 1.2rem;
            color: #CF7C32;
            font-weight: 700;
            margin-bottom: 1rem;
            font-family: 'Tajawal', sans-serif;
        }

        .hello-message {
            font-family: 'Tajawal', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #46707A;
        }
    </style>
</head>

<body>
    <div class="container">
        <img src="https://i.ibb.co/gbQYFLvX/Logo.png" alt="MediCore Logo" class="logo" />
        <div class="hello-message">Hello! {{ $first_name }}</div>
        <h1>Welcome to MediCore application</h1>
        <p class="welcome">
            Your best way to protect your health and your children's health
        </p>

        <p>Your verification code is:</p>
        <div class="otp-box" id="otpCode">{{ $code }}</div>

        <div class="timer" id="timer">05:00</div>
        <p style="font-size: 0.95rem; color: #7C8283; margin-bottom: 2rem;">
            This code will expire in one minute.
        </p>

        <p>Please enter this code in the application to complete verification.</p>
    </div>

    <script>
        let timeLeft = 60;
        const timerEl = document.getElementById('timer');

        const countdown = setInterval(() => {
            if (timeLeft <= 0) {
                clearInterval(countdown);
                timerEl.textContent = 'Code expired!';
                timerEl.style.color = '#7C8283';
                return;
            }
            let minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;
            timerEl.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            timeLeft--;
        }, 1000);
    </script>
</body>

</html>