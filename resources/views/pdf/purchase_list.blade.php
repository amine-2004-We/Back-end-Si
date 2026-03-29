<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste d'Achats</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; margin: 0; padding: 0; }
        .container { max-width: 1000px; margin: 0 auto; padding: 16px; }
        .title { font-size: 22px; font-weight: bold; margin-bottom: 18px; letter-spacing: 1px; }
        table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            margin-top: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            overflow: hidden;
        }
        th, td {
            border: 1px solid #e0e0e0;
            padding: 10px 8px;
            text-align: left;
            font-size: 13px;
        }
        th {
            background: #f8f8f8;
            font-size: 14px;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:first-child th:first-child {
            border-top-left-radius: 10px;
        }
        tr:first-child th:last-child {
            border-top-right-radius: 10px;
        }
        tr:last-child td:first-child {
            border-bottom-left-radius: 10px;
        }
        tr:last-child td:last-child {
            border-bottom-right-radius: 10px;
        }
        @media (max-width: 900px) {
            .container { padding: 4px; }
            table, thead, tbody, th, td, tr { font-size: 11px; }
            .title { font-size: 16px; }
        }
        @media (max-width: 600px) {
            .container { padding: 0; }
            table, thead, tbody, th, td, tr { font-size: 9px; }
            .title { font-size: 13px; }
            th, td { padding: 4px 2px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">Modèle : Liste d'Achats</div>
        <div style="margin-bottom: 6px;"><strong>ID Liste d'Achat :</strong> {{ $purchaseList->purchase_list_id }}</div>
        <div style="margin-bottom: 12px;"><strong>Date de création :</strong> {{ $purchaseList->created_at ? $purchaseList->created_at->format('d/m/Y') : '' }}</div>
        <table>
        <thead>
            <tr>
                <th>Référence(s) DA consolidées</th>
                <th>Département émetteur</th>
                <th>Projet / Ligne budgétaire</th>
                <th>Liste des produits consolidés</th>
                <th>Article</th>
                <th>Quantité totale</th>
                <th>Prix estimatif unitaire</th>
                <th>Montant total estimé</th>
                <th>Priorité</th>
                <th>Observations</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    @foreach($purchaseList->requests as $req)
                        {{ $req->code ?? $req->id }}@if(!$loop->last), @endif
                    @endforeach
                </td>
                <td>{{ $purchaseList->department->name ?? '' }}</td>
                <td>
                    @foreach($purchaseList->requests as $req)
                        @if(isset($req->project))
                            {{ $req->project->project_name ?? '' }}
                            @if(isset($req->project->total_budget))
                                ({{ number_format($req->project->total_budget, 0, ',', ' ') }} MAD)
                            @endif
                        @endif
                        @if(!$loop->last), @endif
                    @endforeach
                </td>
                <td>
                    @foreach($purchaseList->items as $item)
                        {{ $item->product->name ?? '' }}@if(!$loop->last), @endif
                    @endforeach
                </td>
                <td>
                    @foreach($purchaseList->items as $item)
                        {{ $item->name }}@if(!$loop->last), @endif
                    @endforeach
                </td>
                <td>
                    {{ $purchaseList->total_quantity_requested }}
                </td>
                <td>
                    @foreach($purchaseList->items as $item)
                        {{ $item->reference_price }}@if(!$loop->last), @endif
                    @endforeach
                </td>
                <td>
                    @foreach($purchaseList->items as $item)
                        {{ number_format($item->pivot->quantity * $item->reference_price, 2) }}@if(!$loop->last), @endif
                    @endforeach
                </td>
                <td>{{ $purchaseList->priority }}</td>
                <td>{{ $purchaseList->observations }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
