<!DOCTYPE html>
@php $logoPath = public_path('Logo Zakoura.png'); @endphp
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande de Livraison #{{ $deliveryRequest->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .zak-logo {
            width: 180px;
            margin-bottom: 18px;
        }
        h2 {
            text-align: center;
            margin: 20px 0 10px 0;
            font-size: 16px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 24px;
            margin-bottom: 24px;
        }
        .info-label {
            font-weight: bold;
        }
        .section-title {
            font-weight: bold;
            margin-top: 20px;
        }
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 10px;
            border-radius: 8px;
            overflow: hidden;
            border: 1.5px solid #d1d5db;
        }
        .table th, .table td {
            border: none;
            padding: 8px 10px;
            text-align: left;
        }
        .table th {
            background: #f8fafc;
            color: #222;
            font-weight: bold;
        }
        .table tr:nth-child(even) td {
            background: #f4f6f8;
        }
        .table tr:last-child td {
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }
        .table tr:first-child th {
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .footer {
            margin-top: 40px;
            font-size: 11px;
            color: #888;
            text-align: center;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .signature-block {
            width: 45%;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ $logoPath }}" alt="Logo Zakoura" class="zak-logo">
        <h2>Demande de Livraison</h2>
        <div style="margin-bottom: 10px; color: #888;">#{{ $deliveryRequest->code }}</div>
    </div>

    

    <div class="footer">
        Généré le {{ now()->format('d/m/Y H:i') }}<br>
        Document généré par Fondation Zakoura - Demande de Livraison n° {{ $deliveryRequest->code }}
        <div style="margin-top: 10px; font-size: 12px; color: #444;">
            <strong>Note :</strong> Ce document est généré automatiquement et doit être validé par le service logistique. Pour toute question, contactez le service achats.<br>
            <span style="font-size:11px;">Merci de vérifier la conformité des articles à la réception.</span>
        </div>
    </div>

    <div class="info-grid">
        <div><span class="info-label">Date de la demande :</span> {{ $deliveryRequest->request_date }}</div>
        <div><span class="info-label">Objet :</span> {{ $deliveryRequest->request_purpose }}</div>
        <div><span class="info-label">Lieu de livraison :</span> {{ $deliveryRequest->delivery_location }}</div>
        <div><span class="info-label">Date de livraison :</span> {{ $deliveryRequest->delivery_date }}</div>
        <div><span class="info-label">Statut :</span> {{ $deliveryRequest->status }}</div>
        <div><span class="info-label">Observations :</span> {{ $deliveryRequest->observations }}</div>
        <div><span class="info-label">Contact bénéficiaire :</span> {{ $deliveryRequest->recipient_contact }}</div>
        <div><span class="info-label">Créé par :</span> {{ $deliveryRequest->creator?->name }}</div>
        <div><span class="info-label">Supérieur hiérarchique :</span> {{ $deliveryRequest->creator?->collaborator?->superior?->user?->name }}</div>
        <div><span class="info-label">Bon de commande :</span> {{ $deliveryRequest->purchaseOrder?->po_number }}</div>
        <div><span class="info-label">Demande d'achat :</span> {{ $deliveryRequest->purchaseRequest?->code }}</div>
        <div><span class="info-label">Devis :</span> {{ $deliveryRequest->purchaseOrder?->quote?->quote_number }}</div>
    </div>

    <div>
        <span class="section-title">Articles demandés</span>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Nom de l'article</th>
                    <th>Produit associé</th>
                    <th>Quantité demandée</th>
                </tr>
            </thead>
            <tbody>
            @foreach($deliveryRequest->items as $i => $item)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $item->article?->name }}</td>
                    <td>{{ $item->article?->product?->name }}</td>
                    <td>{{ $item->quantity_requested }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <!-- Ajoutez ici d'autres sections pour les relations supplémentaires si besoin -->

</body>
</html>
