<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Ordre de Virement - Societe Generale</title>
    <style>
        @page {
            margin: 40px 50px;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.5;
        }
        .date-header {
            text-align: right;
            margin-bottom: 30px;
            font-weight: bold;
        }
        .bank-address {
            text-align: right;
            margin-bottom: 30px;
            line-height: 1.4;
        }
        .project-code {
            margin-bottom: 25px;
        }
        .project-code-label {
            text-decoration: underline;
        }
        .salutation {
            margin-bottom: 20px;
        }
        .body-text {
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .virements-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .virements-table th {
            border: 1px solid #000;
            padding: 8px 5px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            background-color: #fff;
        }
        .virements-table td {
            border: 1px solid #000;
            padding: 6px 5px;
            text-align: center;
            font-size: 10px;
        }
        .virements-table .amount {
            text-align: right;
        }
        .virements-table .total-row td {
            font-weight: bold;
            border: 1px solid #000;
        }
        .somme-lettres {
            margin-bottom: 15px;
        }
        .somme-lettres-label {
            text-decoration: underline;
        }
        .closing {
            margin-bottom: 40px;
        }
        .signatures-table {
            width: 100%;
            margin-top: 30px;
        }
        .signatures-table td {
            width: 33%;
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }
        .signature-function {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="date-header">
        Casablanca, Le&nbsp;&nbsp;&nbsp;&nbsp;{{ $emission_date }}
    </div>

    <div class="bank-address">
        Societe Generale<br>
        Agence Twin Center<br>
        Casablanca
    </div>

    <div class="project-code">
        <span class="project-code-label">Code Projet</span> : {{ $project_code ?? 'N/A' }}
    </div>

    <div class="salutation">
        Monsieur,
    </div>

    <div class="body-text">
        Par le debit de notre compte en vos livres N° : <strong>{{ $company_rib }} {{ $account_title ?? '' }}</strong><br>
        Nous vous prions de bien vouloir effectuer l'ordre de virement suivant :
    </div>

    <table class="virements-table">
        <thead>
            <tr>
                <th style="width: 22%;">Raison Sociale/ Noms et<br>Prenoms</th>
                <th style="width: 22%;">N° compte a crediter</th>
                <th style="width: 12%;">Banque</th>
                <th style="width: 15%;">Montant</th>
                <th style="width: 20%;">Objet</th>
            </tr>
        </thead>
        <tbody>
            @foreach($virements as $virement)
            <tr>
                <td>{{ $virement['beneficiary_name'] }}</td>
                <td>{{ $virement['beneficiary_rib'] }}</td>
                <td>{{ $virement['bank_code'] ?? '' }}</td>
                <td class="amount">{{ number_format($virement['amount'], 2, ',', ' ') }}</td>
                <td>{{ $virement['reference'] }}</td>
            </tr>
            @endforeach
            @for($i = count($virements); $i < 8; $i++)
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            @endfor
            <tr class="total-row">
                <td colspan="2"></td>
                <td><strong>TOTAL</strong></td>
                <td class="amount">{{ number_format($total_amount, 2, ',', ' ') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="somme-lettres">
        <span class="somme-lettres-label">Somme en lettres</span> : {{ $amount_in_letters }}
    </div>

    <div class="closing">
        Veuillez agreer, Monsieur, l'expression de nos sinceres salutations.
    </div>

    <table class="signatures-table">
        <tr>
            <td>
                <div class="signature-function">Fonction</div>
                <div class="signature-name">Nom signataire</div>
            </td>
            <td>
                <div class="signature-function">Fonction</div>
                <div class="signature-name">Nom signataire</div>
            </td>
            <td>
                <div class="signature-function">Fonction</div>
                <div class="signature-name">Nom signataire</div>
            </td>
        </tr>
    </table>
</body>
</html>
