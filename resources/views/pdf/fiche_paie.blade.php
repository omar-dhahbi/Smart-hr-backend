<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Fiche de paie</title>

    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            background: #fff;
            margin: auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
        }

        /* HEADER */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .logo img {
            height: 60px;
        }

        .title {
            text-align: right;
        }

        .title h2 {
            margin: 0;
            color: #0d6efd;
        }

        .title p {
            margin: 5px 0 0;
            color: #666;
            font-size: 14px;
        }

        /* GRID INFO */
        .info-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .info-box {
            width: 48%;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
        }

        .info-box strong {
            display: block;
            color: #555;
            font-size: 13px;
        }

        .info-box span {
            font-size: 15px;
            font-weight: bold;
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table thead {
            background: #0d6efd;
            color: white;
        }

        table th,
        table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        /* TOTAL */
        .total {
            margin-top: 20px;
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
        }

        /* FOOTER */
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 13px;
            color: #888;
        }
        .tampon {
            margin-top: 40px;
            display: flex;
            justify-content: flex-end;
        }

        .tampon-box {
            width: 180px;
            height: 180px;
            border: 2px dashed #0d6efd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-weight: bold;
            color: #0d6efd;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="{{ public_path('images/logo.jpeg') }}" alt="Logo entreprise">
            </div>

            <div class="title">
                <h2>Fiche de Paie</h2>
            </div>
        </div>

        <div class="info-grid">

            <div class="info-box">
                <strong>Nom</strong>
                <span>{{ $nom }}</span>
            </div>

            <div class="info-box">
                <strong>Prénom</strong>
                <span>{{ $prenom }}</span>
            </div>

            <div class="info-box">
                <strong>Date de naissance</strong>
                <span>{{ $date_naissance }}</span>
            </div>

            <div class="info-box">
                <strong>Date génération</strong>
                <span>{{ $date }}</span>
            </div>

        </div>
        <table>
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th>Valeur</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Nombre d'heures travaillées</td>
                    <td>{{ $heures }} h</td>
                </tr>
                <tr>
                    <td>Salaire brut</td>
                    <td>{{ $salaire }} DT</td>
                </tr>
            </tbody>
        </table>

        <!-- TOTAL -->
        <div class="total">
            Total à payer : {{ $salaire }} DT
        </div>

        <div class="tampon">
            <div class="tampon-box">
                Cachet & Signature<br>
                RH / Entreprise
            </div>
        </div>

    </div>

</body>

</html>
