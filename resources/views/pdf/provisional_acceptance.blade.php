<!DOCTYPE html>
@php

    $logoPath = public_path('Logo Zakoura.png');
@endphp
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Procès-verbal de réception provisoire</title>
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
        h2 { 
            text-align: center; 
            margin: 20px 0;
            font-size: 16px;
        }
        h3 {
            text-align: center;
            font-size: 14px;
            margin: 10px 0;
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0;
        }
        .table th, .table td { 
            border: 1px solid #000; 
            padding: 8px;
            text-align: left;
        }
        .table th {
           
            font-weight: bold;
        }
        p {
            text-align: justify;
            margin: 15px 0;
        }
        .zak-logo { width: 180px; margin-bottom: 18px; }

        .signatures {
            display: table;
            width: 100%;
            margin-top: 50px;
        }
        .signature-block {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ $logoPath }}" alt="Header"   class="zak-logo">
    </div>

    <h2>Procès-verbal de réception provisoire</h2>
    
    <h3>Achat des livres et fournitures scolaires pour les classes de préscolaires de la FZ<br>
    (Marché n° {{ $pv->callTender->calltender_id ?? '4/FLFZ-MEN/2024' }})</h3>

    <p>
        Ce jour le <strong>{{ \Carbon\Carbon::parse($pv->date)->format('d/m/Y') ?? '.....................' }}</strong>, 
        il a été procédé à la réception provisoire (Texte) des livres et fournitures scolaires désignées aux 
        <strong>{{ $pv->nb_classes ?? '238' }} classes de préscolaire</strong> de la fondation Zakoura Education 
        en partenariat avec le ministère de l'Éducation Nationale, du Préscolaire et des Sports (MENPS) à la région 
        de {{ $pv->region ?? "L'Oriental" }} du Maroc.
    </p>

    <table class="table">
        <thead>
            <tr>
                <th>Désignation et caractéristiques (article et marque)</th>
                <th style="width: 150px;">Quantité globale Reçus</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->article->name }}  {{ $item->article->brand }}</td>
                    <td style="text-align: center;">{{ $item->quantity_received }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>
       Reçu de la société <strong>{{ $pv->calltender->supplier ?? 'XXXXXXXXX' }}</strong>
dans le cadre du marché lié à l'appel d'offre ouvert n°
<strong>{{ $pv->calltender->calltender_id ?? 'XXXXXXXX' }}</strong>
du <strong>{{ \Carbon\Carbon::parse($pv->calltender->signature_date)->format('d/m/Y') ?? 'Date' }}</strong>
    </p>

    <p>
        Les livres et fournitures scolaires sont conformes aux spécifications du cahier des prescriptions spéciales du marché 
        et aucune anomalie n'a été signalée.
    </p>

    {{-- <p>
        De ce fait, nous prononçons, ce jour la réception provisoire conformément à l'article n° 
        <strong>{{ $pv->article_number ?? 'XXX' }}</strong> du marché lié à l'appel d'offre ouvert 
        n°<strong>{{ $pv->calltender->calltender_id ?? 'XXXXXXXX' }}</strong> du 
        <strong>{{ $pv->calltender && $pv->calltender->signature_date ? \Carbon\Carbon::parse($pv->calltender->signature_date)->format('d/m/Y') : 'Date' }}</strong>.
    </p> --}}

    <div class="footer">
        <p><strong>Fait à Casablanca, le {{ \Carbon\Carbon::parse($pv->date)->format('d/m/Y') ?? 'Date' }}</strong></p>
    </div>

    <div class="signatures">
        <div class="signature-block">
            <p><strong>Titulaire du marché</strong></p>
            <p style="margin-top: 10px;">{{ $pv->calltender->supplier ?? 'LIBRAIRIE IQRAE' }}</p>
        </div>
        <div class="signature-block">
            <p><strong>Maître d'ouvrage</strong></p>
            <p style="margin-top: 10px;">FONDATION ZAKOURA EDUCATION</p>
        </div>
    </div>
</body>
</html>