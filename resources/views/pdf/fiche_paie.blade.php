<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Fiche de paie</title>
    <style>
        body {
            font-family: Arial;
        }

        .container {
            padding: 20px;
        }

        h2 {
            text-align: center;
        }

        .info {
            margin-top: 20px;
        }

        .info p {
            font-size: 16px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Fiche de Paie</h2>

        <div class="info">
            <p><strong>Nom :</strong> {{ $nom }}</p>
            <p><strong>Prénom :</strong> {{ $prenom }}</p>
            <p><strong>date naissance :</strong> {{ $date_naissance }}</p>
            <p><strong>Nombre d'heures :</strong> {{ $heures }}</p>
            <p><strong>Salaire :</strong> {{ $salaire }} DT</p>
            <p><strong>Date génération :</strong> {{ $date }}</p>

        </div>
    </div>
</body>

</html>
