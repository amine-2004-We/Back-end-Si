@php
    $pr = $partialReceipt;
    // Recherche du marché (Calltender) via la chaîne : PartialReceipt -> ProvisionalAcceptance -> Calltender
    $provisional = $pr->provisionalAcceptances->first();
    $market = $provisional && $provisional->calltender ? $provisional->calltender : null;
    $marketNumber = $market ? ($market->calltender_id ?? $market->id ?? '—') : '—';
    $marketSubject = $market ? ($market->subject ?? '') : '';
    $marketText = $marketNumber;
    $logoPath = public_path('Logo Zakoura.png');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Procès-verbal de réception partielle</title>
    <style>
        body { font-family: Calibri, Arial, sans-serif; font-size: 13px; line-height: 1.6; }
        .header-main { text-align: center; margin-bottom: 20px; }
        .zak-logo { width: 180px; margin-bottom: 18px; }
        .pv-title { font-size: 22px; font-weight: bold; margin-bottom: 8px; }
        .market-title { font-size: 18px; font-weight: bold; margin-bottom: 18px; display: inline-block; padding: 2px 8px; }
        .section-title { font-weight: bold; margin-top: 20px; }
        .highlight-text { padding: 0 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
        .footer { margin-top: 40px; font-size: 12px; }
        .intro-section { margin: 20px 0; text-align: justify; }
        ul { list-style-type: none; padding-left: 0; }
        ul li { margin: 10px 0; }
    </style>
</head>
<body>
    <div class="header-main">
        <img src="{{ $logoPath }}" class="zak-logo" alt="Logo Fondation Zakoura" />
        <div class="pv-title">Procès-verbal de réception partielle</div>
        <div class="market-title">Marché n° {{ $marketText }}</div>
    </div>

    <div class="intro-section">
        <p>L'an <span class="highlight-text">{{ $pr->created_at ? $pr->created_at->format('Y') : '____' }}</span> le <span class="highlight-text">{{ $pr->created_at ? $pr->created_at->format('d/m/Y') : 'date' }}</span> nous soussignés</p>
        
        <p>Les membres de l'équipe, commission ci-dessous ont procédé dans le cadre du marché n°<span class="highlight-text">{{ $marketText }}</span> relatifs à l'achat (texte) <span class="highlight-text">des livres et fournitures scolaires destinées aux classes de préscolaire de la Fondation Zakoura Education en partenariat avec le MENPS</span>, à la réception partielle de la commande suivante :</p>
    </div>

   

    <div class="section-title">Commande concernée</div>
    <table>
        <thead>
            <tr>
                <th>N° OS, OL et BC</th>
                <th>Désignation des prestations</th>
                <th>Quantité</th>
            </tr>
        </thead>
        <tbody>
        @php
            $processedOrders = [];
        @endphp
        @foreach($pr->deliveryReceipts as $dr)
            @if($dr->deliveryOrder && !in_array($dr->deliveryOrder->id, $processedOrders))
                @php
                    $processedOrders[] = $dr->deliveryOrder->id;
                    $do = $dr->deliveryOrder;
                    $po = $do->purchaseOrder;
                @endphp
                <tr>
                    <td>
                        <span class="highlight-text">Ordre de service n° {{ $do->order_id ?? '—' }}</span><br>
                        <span class="highlight-text">BC n° {{ $po ? $po->po_number : '—' }}</span><br>
                        <span class="highlight-text">OL n° {{ $do->order_id ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="highlight-text">Texte : {{ $marketSubject ?: 'Livres et fournitures scolaires pour 44 classes à la région de BENI MELLAL' }}</span>
                    </td>
                    <td>
                        <span class="highlight-text">Détails sur le Bon de réception n° {{ $dr->receipt_identifier }}</span>
                    </td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>

    <div class="intro-section" style="margin-top: 30px;">
        <p>Les membres de l'équipe reconnaissent que lesdites prestations ont été réalisées <span class="highlight-text">( possibilité de changement en cas de réception incomplète ou avec réserve ou manque)</span> conformément aux clauses du marché et qu'elles peuvent faire l'objet d'une réception partielle.</p>

        <p style="margin-top: 20px;">Fait à <span class="highlight-text">{{ $pr->deliveryReceipts->first() && $pr->deliveryReceipts->first()->deliveryOrder ? $pr->deliveryReceipts->first()->deliveryOrder->delivery_address : 'ville de réception' }}</span> le <span class="highlight-text">{{ $pr->created_at ? $pr->created_at->format('d/m/Y') : 'date de réception' }}</span></p>
    </div>

    @php
        // Récupérer les membres de la commission de réception via le premier PV provisoire lié
        $provisional = $pr->provisionalAcceptances->first();
        $membres = $provisional && $provisional->committeeMembers ? $provisional->committeeMembers : collect();
        // Destinataire = premier membre
        $destinataire = $membres->first();
        // Responsable N+1 = supérieur hiérarchique du collaborateur lié au destinataire
        $n1 = null;
        if ($destinataire && $destinataire->collaborator && $destinataire->collaborator->superior) {
            $n1 = $destinataire->collaborator->superior;
        }
        // Responsable opération = collaborateur lié au destinataire (s'il existe)
        $responsableOp = $destinataire && $destinataire->collaborator ? $destinataire->collaborator : null;
    @endphp

    <div class="market-title" style="margin-bottom: 1px; padding-left:0px">Membres de la commission de réception</div>
    <ul>
        <li style=" font-size: 18px;">
            M. {{ $destinataire->name ?? '—' }} - Destinataire
        </li>
        <li style=" font-size: 18px;">
            M. {{ $n1 ? ($n1->first_name . ' ' . $n1->last_name) : '—' }} - Responsable N+1
        </li>
        <li style=" font-size: 18px;">
            M. {{ $responsableOp ? ($responsableOp->first_name . ' ' . $responsableOp->last_name) : '—' }} - Responsable Opération
        </li>
    </ul>

    <div class="footer">
        <div><b>Date d'impression :</b> {{ now()->format('d/m/Y H:i') }}</div>
    </div>

</body>
</html>