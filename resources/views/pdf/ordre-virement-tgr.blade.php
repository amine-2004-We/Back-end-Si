<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Ordre de Virement - TGR</title>
    <style>
        @page {
            margin: 60px 50px;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
            line-height: 1.6;
        }
        .date-header {
            text-align: right;
            margin-bottom: 40px;
            font-weight: bold;
        }
        .recipient {
            text-align: center;
            margin-bottom: 40px;
        }
        .recipient-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .objet {
            margin-bottom: 30px;
        }
        .objet-label {
            text-decoration: underline;
            font-weight: bold;
        }
        .body-text {
            text-align: justify;
            margin-bottom: 25px;
            line-height: 1.8;
        }
        .file-info {
            margin-bottom: 8px;
        }
        .signature-table {
            width: 100%;
            margin-top: 60px;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            border: 1px solid #000;
            height: 120px;
            vertical-align: top;
            padding: 10px;
        }
        .signature-label {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="date-header">
        Casablanca le, {{ $emission_date }}
    </div>

    <div class="recipient">
        <div style="font-weight: bold; margin-bottom: 15px;">A</div>
        <div class="recipient-title">Madame Le Chef de l'Agence Bancaire de Casa Bourgogne</div>
        <div class="recipient-title">Tresorerie generale du royaume</div>
    </div>

    <div class="objet">
        <span class="objet-label">Objet</span> : Ordre de Virement de masse
    </div>

    <div class="body-text">
        Par le debit de notre compte numero : <strong>{{ $company_account_number }}</strong> tenu dans vos livres, nous vous prions de bien vouloir virer la somme globale de <strong>{{ number_format($total_amount, 2, ',', ' ') }} DH</strong> (en lettre: <strong>{{ $amount_in_letters }}</strong>) au profit des beneficiaires detailles dans le fichier :
    </div>

    <div class="file-info">
        <strong>Bvov:</strong> {{ $bvov_filename }}
    </div>

    <div class="file-info">
        <strong>Nombre de virements :</strong> {{ $total_count }}
    </div>

    <div class="file-info">
        <strong>Envoye par mail le :</strong> {{ $emission_date }}
    </div>

    <table class="signature-table">
        <tr>
            <td>
                <span class="signature-label">Signature</span>
            </td>
            <td>
                <span class="signature-label">Signature</span>
            </td>
        </tr>
    </table>
</body>
</html>
