<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon de commande</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body class="">
    <div class=" ">
        
        <!-- Header avec logo et informations -->
        <div class=" p-2">
            <div class="flex items-start justify-between mb-4">
                <!-- Logo à gauche -->
                <div class="w-32">
                    <img src="{{ public_path('Logo Zakoura.png') }}" alt="Logo Fondation Zakoura" class="mx-auto mb-2" style="max-width:120px;max-height:120px;object-fit:contain;">
                    <div class="text-center mt-2 text-gray-600 text-xs font-light tracking-widest">
                        FONDATION<br>ZAKOURA
                    </div>
                </div>
                
                <!-- Informations centrales et droite -->
                <div class="flex-1 w-full">
                    <div class="text-right mb-6">
                        <h1 class="text-xl font-normal mb-2">Bon de Commande N° {{ $po->po_number ?? '260' }}</h1>
                        <div class="border border-gray-400 inline-block p-3 text-left">
                            <p class="text-sm mb-1">Marché N°4/EJPG-FZ/2023</p>
                            <p class="text-sm">Ordre de service N° <span class="font-semibold">48-36</span></p>
                            <div class="text-xs text-gray-600 mt-2 space-y-0.5">
                                <p>Projet: INDI BERRANE/PRESCOLAIRE/2023</p>
                                <p>Périmètre: INDI BERRANE</p>
                                <p>Année: 2026</p>
                                <p>28/05/2025</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Fournisseur -->
                    <div class="border border-gray-400 p-3 mb-4">
                        <p class="font-semibold mb-1">Fournisseur: {{ $po->supplier->company_name ?? 'EDIT CONSULTING' }}</p>
                        <div class="text-xs text-gray-700 space-y-0.5">
                            <p>Adresse: {{ $po->supplier->address ?? '22, Bd AIN SIOIAU, BOURGOGNE, Casablanca' }}</p>
                            <p>Tél.: {{ $po->supplier->phone ?? '05 22 25 60 10' }}</p>
                            <p>E-mail: {{ $po->supplier->email ?? 'editconsulting2003@gmail.com' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Notes légales -->
            <div class="text-xs text-gray-600   pt-3 space-y-1">
                <p> La fondation Zakoura exige de ses fournisseurs le respect des droits des enfants, résultant de la convention de l'OIT sur les droits au travail.</p>
                <p> La Fondation Zakoura sensibilise ses fournisseurs au respect de leur environnement.</p>
            </div>
            
            <!-- Informations destinataire -->
            <div class="grid grid-cols-2 gap-6 mt-4 text-sm">
                <div>
                    <p class="mb-1"><span class="font-semibold">Ville:</span></p>
                    <p class="mb-1"><span class="font-semibold">Destinataire:</span> M. OMAR EL OUALI </p>
                    <p><span class="font-semibold">Date de livraison prévisionnelle:</span> {{ !empty($po->delivery_lead_time_days) ? \Carbon\Carbon::now()->addDays($po->delivery_lead_time_days)->format('d/m/Y') : '29/05/2025' }}</p>
                </div>
                <div>
                    <p class="mb-1"><span class="font-semibold">Ecole:</span></p>
                    <p><span class="font-semibold">Ligne:</span> Equipement de la classe </p>
                </div>
            </div>
            
            <div class="mt-4 text-sm">
                <p class="mb-1"><span class="font-semibold">Objet:</span></p>
                <p class="mb-1"><span class="font-semibold">Observation:</span> {{ $po->purchaseRequest && $po->purchaseRequest->observations ? $po->purchaseRequest->observations : '-' }}</p>
            </div>
        </div>

        <!-- Informations principales (simple et moderne) -->
        <div class="border-t-4 border-blue-900 pt-6 pb-4 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @if(!empty($po->subject))
                <div class="flex flex-col items-start">
                    <span class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-2">Objet</span>
                    <span class="text-base font-semibold text-gray-900">{{ $po->subject }}</span>
                </div>
                @endif
                @if(!empty($po->currency))
                <div class="flex flex-col items-start">
                    <span class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-2">Devise</span>
                    <span class="text-base font-semibold text-gray-900">{{ $po->currency }}</span>
                </div>
                @endif
                @if(!empty($po->payment_method))
                <div class="flex flex-col items-start">
                    <span class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-2">Mode de paiement</span>
                    <span class="text-base font-semibold text-gray-900">{{ $po->payment_method }}</span>
                </div>
                @endif
                @if(!empty($po->delivery_lead_time_days))
                <div class="flex flex-col items-start">
                    <span class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-2">Délai livraison</span>
                    <span class="text-base font-semibold text-gray-900">{{ $po->delivery_lead_time_days }} jours</span>
                </div>
                @endif
                @if(!empty($po->status))
                <div class="flex flex-col items-start">
                    <span class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-2">Statut</span>
                    <span class="inline-block px-3 py-1 border border-blue-900 text-blue-900 text-sm font-semibold">{{ $po->status }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Fournisseur et Destinataire (simple et moderne) -->
        <div class="grid md:grid-cols-2 gap-8 mb-8">
            @if(!empty($po->supplier))
            <div class="border-l-4 border-blue-900 pl-6 py-2">
                <h2 class="text-lg font-bold text-gray-900 mb-4 uppercase tracking-wide">Fournisseur</h2>
                <div class="space-y-2 text-sm">
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Entreprise</span>
                        <div class="font-semibold text-gray-900">{{ $po->supplier->company_name ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Raison sociale</span>
                        <div class="text-gray-700">{{ $po->supplier->trade_name ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Adresse</span>
                        <div class="text-gray-700">{{ $po->supplier->address ?? '-' }}</div>
                        <div class="text-gray-700">{{ $po->supplier->city ?? '-' }}, {{ $po->supplier->country ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Contact</span>
                        <div class="text-gray-700">{{ $po->supplier->phone ?? '-' }}</div>
                        <div class="text-gray-700">{{ $po->supplier->email ?? '-' }}</div>
                    </div>
                </div>
            </div>
            @endif
            @if(!empty($po->issuer))
            <div class="border-l-4 border-blue-900 pl-6 py-2">
                <h2 class="text-lg font-bold text-gray-900 mb-4 uppercase tracking-wide">Destinataire</h2>
                <div class="space-y-2 text-sm">
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Ville</span>
                        <div class="font-semibold text-gray-900">{{ $po->issuer->name ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Email</span>
                        <div class="text-gray-700">{{ $po->issuer->email ?? '-' }}</div>
                    </div>
                    @if(!empty($po->delivery_lead_time_days))
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Date de livraison prévisionnelle</span>
                        <div class="text-gray-700">{{ \Carbon\Carbon::now()->addDays($po->delivery_lead_time_days)->format('d/m/Y') }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Validations (simple et moderne) -->
        @if(isset($po->validated_by_procurement_manager) || isset($po->validated_by_controlling) || isset($po->validated_by_board))
        <div class="border-t-4 border-blue-900 pt-6 pb-4 mb-8">
            <h2 class="text-lg font-bold text-gray-900 mb-6 uppercase tracking-wide">Validations</h2>
            <div class="flex flex-wrap gap-8">
                @if(isset($po->validated_by_procurement_manager))
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full flex items-center justify-center {{ $po->validated_by_procurement_manager ? 'bg-green-500' : 'bg-red-500' }}">
                        @if($po->validated_by_procurement_manager)
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @endif
                    </span>
                    <span class="text-sm text-gray-900 font-semibold">Validé achats</span>
                </div>
                @endif
                @if(isset($po->validated_by_controlling))
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full flex items-center justify-center {{ $po->validated_by_controlling ? 'bg-green-500' : 'bg-red-500' }}">
                        @if($po->validated_by_controlling)
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @endif
                    </span>
                    <span class="text-sm text-gray-900 font-semibold">Validé contrôle</span>
                </div>
                @endif
                @if(isset($po->validated_by_board))
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-full flex items-center justify-center {{ $po->validated_by_board ? 'bg-green-500' : 'bg-red-500' }}">
                        @if($po->validated_by_board)
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @endif
                    </span>
                    <span class="text-sm text-gray-900 font-semibold">Validé direction</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Tableau des produits -->
        {{-- <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-200 border-b-2 border-gray-400">
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Désignation</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Qté</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @php
                            $allProducts = collect();
                            foreach ($po->purchaseRequests as $purchaseRequest) {
                                foreach ($purchaseRequest->products as $productLine) {
                                    $allProducts->push($productLine);
                                }
                            }
                        @endphp
                        @foreach($allProducts as $productLine)
                            @php $product = $productLine->product; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $product?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ $productLine->quantity }}</td>
                            </tr>
                        @endforeach
                       
                    </tbody>
                </table>
            </div>
        </div> --}}

        <!-- Articles du devis -->
        @if(!empty($po->quote) && $po->quote->items && count($po->quote->items))
        <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
            <div class="p-4 border-b border-gray-200 flex items-center gap-6">
                <div>
                    <span class="text-lg font-bold text-blue-900">Articles du devis</span>
                </div>
                <div class="ml-auto flex gap-4">
                    <span class="text-gray-700 text-sm">Numéro devis : <span class="font-bold">{{ $po->quote->quote_number }}</span></span>
                    <span class="text-gray-700 text-sm">Total HT devis : <span class="font-bold">{{ number_format($po->quote->total_amount_ht, 2) }} MAD</span></span>
                    <span class="text-gray-700 text-sm">Total TTC devis : <span class="font-bold">{{ number_format($po->quote->total_amount_ttc, 2) }} MAD</span></span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-blue-100">
                            <th class="px-4 py-3 text-left text-xs font-bold text-blue-900 uppercase tracking-wider">Article</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">Qté</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-blue-900 uppercase tracking-wider">PU Devis</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-blue-900 uppercase tracking-wider">TVA</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-blue-900 uppercase tracking-wider">Total HT</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-blue-900 uppercase tracking-wider">Prix TTC</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($po->quote->items as $item)
                            @php
                                $article = $item->article;
                                $qty = $item->quantity;
                                $pu = $item->unit_price_ht;
                                $tva = $item->tva_rate ?? 0;
                                $totalHt = $qty * $pu;
                                $totalTtc = $totalHt * (1 + $tva / 100);
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $article?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ $qty }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($pu, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ $tva }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($totalHt, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">{{ number_format($totalTtc, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Total -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex justify-end">
                <div class="text-right">
                    <p class="text-gray-600 text-sm mb-1">Total TTC</p>
                    <p class="text-3xl font-bold text-blue-900">{{ number_format($po->total_amount_ttc, 2) }} DH</p>
                </div>
            </div>
        </div>

        <!-- Footer avec dates -->
        <div class="mt-6 text-center text-xs text-gray-500 space-y-1">
            @if(!empty($po->created_at))
            <p>Créé le: {{ \Carbon\Carbon::parse($po->created_at)->format('d/m/Y H:i') }}</p>
            @endif
            @if(!empty($po->updated_at))
            <p>Modifié le: {{ \Carbon\Carbon::parse($po->updated_at)->format('d/m/Y H:i') }}</p>
            @endif
        </div>
    </div>
</body>
</html>