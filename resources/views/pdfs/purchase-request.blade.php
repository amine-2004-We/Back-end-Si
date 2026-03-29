<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande d'achat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body class="">
    <div class="p-8 bg-white text-gray-900 text-[13px]">
        <!-- Header avec logo et infos principales -->
        <div class="flex items-start justify-between mb-6">
            <div class="w-32">
                <img src="{{ public_path('Logo Zakoura.png') }}" alt="Logo Fondation Zakoura" class="mx-auto mb-2" style="max-width:120px;max-height:120px;object-fit:contain;">
                <div class="text-center mt-2 text-gray-600 text-xs font-light tracking-widest">
                    FONDATION<br>ZAKOURA
                </div>
            </div>
            <div class="flex-1 w-full">
                <div class="flex flex-col md:flex-row md:justify-between items-start md:items-center mb-2">
                    <div class="text-xs text-gray-700">
                        <span>{{ now()->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="text-center flex-1">
                        <span class="text-md">Demandes d'achats | SI Zakoura</span>
                    </div>
                </div>
                <div class="border border-gray-700 p-3 mb-2">
                    <div class="text-lg font-semibold mb-1">Détails demande N° {{ $purchaseRequest->code ?? $purchaseRequest->id }}</div>
                    <div class="text-base font-bold mb-1">Intitulé : {{ $purchaseRequest->project?->project_name ?? '-' }}</div>
                    <div class="text-xs">
                        Codification partenaire : Direction provincial de {{ $purchaseRequest->department?->name ?? '-' }}<br>
                        Code Projet : {{ $purchaseRequest->project?->project_code ?? '-' }}<br>
                        Type programme : {{ $purchaseRequest->project?->program?->label ?? '-' }}<br>
                        Année : {{ $purchaseRequest->created_at?->format('Y') ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-row justify-between mb-4">
            <div>
                <span class="font-semibold">Ville :</span> {{ $purchaseRequest->project?->project_name ?? '-' }}<br>
                <span class="font-semibold">Destinataire :</span> {{ $purchaseRequest->user?->collaborator?->superior?->first_name ?? $purchaseRequest->user?->name ?? '-' }}
            </div>
            <div>
                <span class="font-semibold">Catégorie</span><br>
                Équipements de restauration
            </div>
            <div>
                <span class="font-semibold">Ecole :</span>
            </div>
        </div>
        <!-- Observations -->
        <div class="mb-6">
            <span class="font-semibold">Observation :</span> {{ $purchaseRequest->observations ?? '-' }}
        </div>
        <!-- Tableau des articles -->
        <div class="mb-8">
            <h2 class="text-lg font-bold text-blue-900 mb-2">Articles demandés</h2>
            <table class="w-full border border-gray-300 rounded-md shadow-sm">
                <thead class="bg-gray-200 text-gray-800 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="border px-2 py-2 text-xs">Catégorie</th>
                        <th class="border px-2 py-2 text-xs">Article</th>
                        <th class="border px-2 py-2 text-xs">Quantité</th>
                        <th class="border px-2 py-2 text-xs">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($purchaseRequest->products as $line)
                    <tr class="bg-white hover:bg-gray-100">
                        <td class="border px-2 py-1 text-sm">
                            {{ $line->category?->name ?? $line->product?->category?->name ?? '-' }}
                        </td>
                        <td class="border px-2 py-1 text-sm">{{ $line->product?->name ?? '-' }}</td>
                        <td class="border px-2 py-1 text-sm text-center">{{ $line->quantity }}</td>
                        <td class="border px-2 py-1 text-sm">
                            @php
                                $status = $purchaseRequest->status;
                                $status_fr = match($status) {
                                    'draft' => 'Brouillon',
                                    'pending' => 'À valider',
                                    'approved' => 'Validée',
                                    'rejected' => 'Rejetée',
                                    default => $status
                                };
                            @endphp
                            {{ $status_fr }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Tableau projet style image -->
        @if($purchaseRequest->project && $purchaseRequest->project->budgetLines)
        <div class="mb-8">
            <table class="w-full border border-gray-300 rounded-md shadow-sm">
                <thead class="bg-gray-200 text-gray-800 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="border px-2 py-2 text-xs">Projet</th>
                        <th class="border px-2 py-2 text-xs">Budget Projet</th>
                        <th class="border px-2 py-2 text-xs">Rubrique budgétaire</th>
                        <th class="border px-2 py-2 text-xs">Ligne budgétaire</th>
                        <!-- <th class="border px-2 py-2 text-xs">Prorata (%)</th> -->
                        <th class="border px-2 py-2 text-xs">Reliquat</th>
                    </tr>
                </thead>
                 <tbody class="divide-y divide-gray-100">
                @foreach($purchaseRequest->project->budgetLines as $budgetLine)
                    <tr>
                        {{-- Nom du Projet --}}
                        <td class="border px-2 py-1 text-sm">
                            {{ $purchaseRequest->project->project_name ?? '-' }}
                        </td>

                        {{-- Code du Projet --}}
                        <td class="border px-2 py-1 text-sm">
                            {{ $purchaseRequest->project->project_code ?? '-' }}
                        </td>

                        {{-- Rubrique budgétaire (BudgetCategory via relation 'category') --}}
                        <td class="border px-2 py-1 text-sm">
                            {{ $budgetLine->category->label ?? '-' }}
                        </td>

                        {{-- Ligne budgétaire (BudgetLine label) --}}
                        <td class="border px-2 py-1 text-sm">
                            {{ $budgetLine->label ?? '-' }}
                        </td>

                        <!-- {{-- Montant Total (depuis pivot) --}}
                        <td class="border px-2 py-1 text-sm">
                            {{ number_format($budgetLine->pivot->total_amount ?? 0, 2) }}
                        </td>

                        {{-- Reliquat (depuis pivot) --}} -->
                        <td class="border px-2 py-1 text-sm">
                            {{ number_format($budgetLine->pivot->reliquate_amount ?? 0, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            </table>
        </div>
        @endif
        <!-- Footer dates -->
        <div class="mt-6 text-center text-xs text-gray-500 space-y-1">
            @if(!empty($purchaseRequest->created_at))
            <p>Créé le: {{ $purchaseRequest->created_at->format('d/m/Y H:i') }}</p>
            @endif
            @if(!empty($purchaseRequest->updated_at))
            <p>Modifié le: {{ $purchaseRequest->updated_at->format('d/m/Y H:i') }}</p>
            @endif
        </div>
    </div>
</body>
</html>
