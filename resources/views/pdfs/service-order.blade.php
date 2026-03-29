
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>ORDRE DE SERVICE</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #222; }
        .logo-zone { display: flex; justify-content: space-between; align-items: flex-start; }
        .logo-block { width: 250px; }
        .yellow-block { background: yellow; width: 300px; height: 120px; float: right; margin-top: 0; }
        .title { text-align: center; font-size: 2.2em; font-weight: bold; margin: 30px 0 10px 0; }
        .subtitle { text-align: center; font-size: 1.2em; font-weight: bold; margin-bottom: 20px; }
        .section { margin: 20px 0; }
        .bold { font-weight: bold; }
        .mt-2 { margin-top: 12px; }
        .mt-4 { margin-top: 24px; }
        .mb-2 { margin-bottom: 12px; }
        .mb-4 { margin-bottom: 24px; }
        .signature { margin-top: 60px; text-align: right; }
        .signature .bold { font-size: 1.1em; }
    </style>
</head>
<body>
    <div class="logo-zone">
        <div class="logo-block">
            <img src="{{ public_path('Logo Zakoura.png') }}" alt="Logo Fondation Zakoura" style="max-width:180px; max-height:90px;">
        </div>
    </div>

    <div class="title">
        ORDRE DE SERVICE <span class="highlight">{{ $serviceOrder->service_order_identifier ?? $serviceOrder->id }}</span>
    </div>

    <div class="section" style="font-size:1.1em;">
        <span class="bold">Marché <span class="highlight">{{ $serviceOrder->type ?? 'type' }}</span> :</span>
        (A BC, Cadre, Négocié, a tranche, Alloti, autre)
        n°<span class="highlight">{{ $serviceOrder->calltender?->calltender_id ?? '-' }}</span>
    </div>

    <div class="subtitle">
        Objet : <span class="highlight">{{ $serviceOrder->subject ?? '-' }}</span>
    </div>

    <div class="section">
        Monsieur : <span class="bold">{{ $serviceOrder->supplier?->trade_name ?? 'XXXXXXXXXXXXXX' }}</span>,
        gérant de la société <span class="bold">{{ $serviceOrder->supplier?->company_name ?? 'XXXXXXXXXXXXXX' }}</span>,
        Titulaire du marché N° <span class="highlight">{{ $serviceOrder->calltender?->calltender_id ?? '-' }}</span> ayant pour objet :
        <span class="highlight">« {{ $serviceOrder->subject ?? '-' }} »</span>
        au profit des <span class="highlight">{{ $serviceOrder->beneficiaires ?? 'classes de préscolaires de la Fondation Zakoura en partenariat avec le MENPS' }}</span>,
        est invité à commencer l’exécution des prestations objet du marché mentionné ci-dessus.
    </div>

    <div class="section">
        La date de commencement d’exécution des prestations est fixée au <span class="highlight">{{ $serviceOrder->start_date?->format('d/m/Y') ?? 'Date' }}</span>.
    </div>

    <div class="section">
        Le présent ordre de service sera adressé à la société <span class="highlight">{{ $serviceOrder->supplier?->company_name ?? 'XXXXXXXXXX' }}</span>
        domicilié à <span class="highlight">{{ $serviceOrder->supplier?->address ?? 'Adresse' }}</span>,
        par Monsieur, Mohamed ZAARI, Directeur Generale de la Fondation Zakoura Education.
    </div>

    <div class="signature">
        Fait à Casablanca le <span class="highlight">{{ $serviceOrder->start_date?->format('d/m/Y') ?? 'Date' }}</span><br><br>
        <span class="bold">Mohamed ZAARI</span><br>
        Directeur Général
    </div>

<div style="font-family: Arial, Helvetica, sans-serif; color: #222; margin-top: 40px;">
    <div style="font-size: 1.5em; text-align: center; margin-top: 40px; margin-bottom: 40px;">
        .................................................................................................................................
    </div>
    <div style="text-align: center; font-size: 2em; font-weight: bold; margin-bottom: 40px;">ACCUSE DE RECEPTION</div>
    <div style="font-size: 1.1em; margin: 0 60px 40px 60px;">
        <span class="bold">Monsieur :</span> <span class="highlight">{{ $serviceOrder->supplier?->trade_name ?? 'XXXXXXXXXXXXXX' }}</span>,
        gérant de la société <span class="highlight">{{ $serviceOrder->supplier?->company_name ?? 'XXXXXXXXXXXXXX' }}</span>,
        certifie avoir à la date indiquée ci-après un exemplaire original de l’ordre de service relatif au marché n°<span class="highlight">{{ $serviceOrder->calltender->calltender_id ?? 'XXXXXXXXXXXXXX' }}</span>.
    </div>
    <div style="margin-top: 80px; text-align: right; font-size: 1.1em; margin-right: 60px;">
        Fait à …………………….., le …………………..
    </div>
</div>
</body>
</html>
