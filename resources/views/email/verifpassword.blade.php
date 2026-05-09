<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trouver Votre Compte</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 500px;
            margin: 50px auto;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .header {
            background-color: #3F51B5;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 24px;
            font-weight: bold;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .content {
            padding: 30px 20px;
            text-align: center;
            font-size: 16px;
        }

        .code {
            font-weight: bold;
        }

        .button {
            margin-top: 20px;
        }

        .button a {
            display: inline-block;
            background-color: #3F51B5;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            padding: 15px;
            font-size: 13px;
            color: #777;
            background-color: #f1f1f1;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">Trouver Votre Compte</div>
        <div class="content">
            <p>Votre code : <span class="code">{{ $details['code'] }}</span> pour vérifier votre mot de passe.</p>
            <div class="button">
                <a href="http://localhost:4201/ModifierMotPasse/{{ $details['id'] }}">Réinitialiser le mot de passe
                </a>
            </div>
        </div>
        <div class="footer">© 2026.</div>
    </div>
</body>

</html>
