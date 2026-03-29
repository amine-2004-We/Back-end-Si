<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransferOrderRequest;
use App\Http\Requests\UpdateTransferOrderRequest;
use App\Models\TransferOrder;
use App\Models\Invoice; 
use App\Models\ExpenseReport; 
use App\Models\ProjectBankAccount; 
use App\Models\EtbacFile;
use Illuminate\Http\Request; 
use Illuminate\Http\JsonResponse;

class TransferOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of TransferOrders.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);
            
            $query = TransferOrder::with([
                'debitedBankAccount', 
                'beneficiaryBankAccount', 
                'invoice', 
                'expenseReport',
                'etbacFile'
            ]);

            if ($request->has('search') && !empty($request->get('search'))) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('transfer_number', 'LIKE', "%{$search}%")
                      ->orWhere('beneficiary_name', 'LIKE', "%{$search}%")
                      ->orWhereHas('etbacFile', function($etbacQuery) use ($search) {
                          $etbacQuery->where('etbac_code', 'LIKE', "%{$search}%");
                      })
                      ->orWhereHas('debitedBankAccount', function($accountQuery) use ($search) {
                          $accountQuery->where('account_holder_name', 'LIKE', "%{$search}%")
                                      ->orWhere('rib_iban', 'LIKE', "%{$search}%");
                      })
                      ->orWhereHas('beneficiaryBankAccount', function($accountQuery) use ($search) {
                          $accountQuery->where('account_holder_name', 'LIKE', "%{$search}%")
                                      ->orWhere('rib_iban', 'LIKE', "%{$search}%");
                      });
                });
            }

            if ($request->has('etbac_file_id') && !empty($request->get('etbac_file_id'))) {
                $etbacFileId = $request->get('etbac_file_id');
                if ($etbacFileId === 'without') {
                    $query->whereNull('etbac_file_id');
                } else {
                    $query->where('etbac_file_id', $etbacFileId);
                }
            }

            if ($request->has('status') && !empty($request->get('status'))) {
                $query->where('status', $request->get('status'));
            }

            if ($request->has('debited_bank_account_id') && !empty($request->get('debited_bank_account_id'))) {
                $query->where('debited_bank_account_id', $request->get('debited_bank_account_id'));
            }

            if ($request->has('beneficiary_bank_account_id') && !empty($request->get('beneficiary_bank_account_id'))) {
                $query->where('beneficiary_bank_account_id', $request->get('beneficiary_bank_account_id'));
            }

            if ($request->has('motif_type') && !empty($request->get('motif_type'))) {
                $query->where('motif_type', $request->get('motif_type'));
            }

            if ($request->has('date_from') && !empty($request->get('date_from'))) {
                $query->whereDate('issue_date', '>=', $request->get('date_from'));
            }

            if ($request->has('date_to') && !empty($request->get('date_to'))) {
                $query->whereDate('issue_date', '<=', $request->get('date_to'));
            }

            if ($request->has('amount_min') && !empty($request->get('amount_min'))) {
                $query->where('amount', '>=', $request->get('amount_min'));
            }

            if ($request->has('amount_max') && !empty($request->get('amount_max'))) {
                $query->where('amount', '<=', $request->get('amount_max'));
            }

            $transferOrders = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => $transferOrders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des ordres de virement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created TransferOrder.
     */
    public function store(StoreTransferOrderRequest $request): JsonResponse
    {
        $year = now()->year;
        $count = TransferOrder::withTrashed()->count() + 1; 
        $generatedId = "VIRM-$year-" . str_pad($count, 5, '0', STR_PAD_LEFT);
        
        while (TransferOrder::where('transfer_number', $generatedId)->exists()) {
            $count++;
            $generatedId = "VIRM-$year-" . str_pad($count, 5, '0', STR_PAD_LEFT);
        }
        
        $data = $request->validated();
        $data['transfer_number'] = $generatedId;
        
        $transferOrder = TransferOrder::create($data);
        $transferOrder->load([
            'debitedBankAccount', 
            'beneficiaryBankAccount', 
            'invoice', 
            'expenseReport',
            'etbacFile'
        ]);
        
        return response()->json($transferOrder, 201);
    }

    /**
     * Display a specific TransferOrder.
     */
    public function show(TransferOrder $transferOrder): JsonResponse
    {
        $transferOrder->load([
            'debitedBankAccount', 
            'beneficiaryBankAccount', 
            'invoice', 
            'expenseReport',
            'etbacFile'
        ]);
        return response()->json($transferOrder);
    }

    /**
     * Update a TransferOrder.
     */
    public function update(UpdateTransferOrderRequest $request, TransferOrder $transferOrder): JsonResponse 
    {
        $transferOrder->update($request->validated());
        $transferOrder->load([
            'debitedBankAccount', 
            'beneficiaryBankAccount', 
            'invoice', 
            'expenseReport',
            'etbacFile'
        ]);

        return response()->json($transferOrder);
    }

    /**
     * Delete a TransferOrder.
     */
    public function destroy(TransferOrder $transferOrder): JsonResponse
    {
        $transferOrder->delete();

        return response()->json(['message' => 'TransferOrder supprimé']);
    }

    /**
     * options for a TransferOrder.
     */
    public function options(): JsonResponse
    {
        try {
            $options = [
                'statuses' => [
                    'en_attente' => 'En attente',
                    'envoye' => 'Envoyé',
                    'valide' => 'Validé',
                    'rejete' => 'Rejeté',
                ],
                'motif_types' => [
                    ['value' => 'facture', 'label' => 'Facture'],
                    ['value' => 'depense', 'label' => 'Note de Frais'],
                ],
                'bank_accounts' => [], 
                'invoices' => [],
                'expense_reports' => [],
                'etbac_files' => []
            ];
    
            if (class_exists(ProjectBankAccount::class)) {
                try {
                    $options['bank_accounts'] = ProjectBankAccount::with(['bank'])
                        ->select('id', 'rib_iban', 'account_holder_name', 'bank_id', 'agency')
                        ->get()
                        ->map(function ($account) {
                            $bankName = $account->bank ? $account->bank->name : 'Banque inconnue';
                            $label = $account->account_holder_name . ' - ' . $bankName;
                            
                            if ($account->rib_iban) {
                                $label .= ' (' . $account->rib_iban . ')';
                            }
                            
                            if ($account->agency) {
                                $label .= ' - Agence: ' . $account->agency;
                            }
                            
                            return [
                                'id' => $account->id,
                                'label' => $label,
                                'value' => $account->id,
                            ];
                        })->toArray();
                } catch (\Exception $e) {
                    $options['bank_accounts'] = [];
                }
            }
    
            try {
                $options['invoices'] = Invoice::select('id', 'invoice_number')
                    ->get()
                    ->map(function ($invoice) {
                        return [
                            'id' => $invoice->id,
                            'label' => $invoice->invoice_number,
                            'value' => $invoice->id,
                        ];
                    })->toArray();
            } catch (\Exception $e) { 
                $options['invoices'] = [];
            }
    
            try {
                $options['expense_reports'] = ExpenseReport::select('id')
                    ->get()
                    ->map(function ($report) {
                        return [
                            'id' => $report->id,
                            'label' => 'NDF #' . $report->id,
                            'value' => $report->id,
                        ];
                    })->toArray();
            } catch (\Exception $e) {
                $options['expense_reports'] = [];
            }

            try {
                $options['etbac_files'] = EtbacFile::select('id', 'etbac_code')
                    ->get()
                    ->map(function ($etbacFile) {
                        return [
                            'id' => $etbacFile->id,
                            'label' => $etbacFile->etbac_code,
                            'value' => $etbacFile->id,
                        ];
                    })->toArray();
            } catch (\Exception $e) {
                $options['etbac_files'] = [];
            }
    
            return response()->json([
                'success' => true,
                'data' => $options
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [
                    'statuses' => [
                        'en_attente' => 'En attente',
                        'envoye' => 'Envoyé',
                        'valide' => 'Validé',
                        'rejete' => 'Rejeté',
                    ],
                    'motif_types' => [
                        ['value' => 'facture', 'label' => 'Facture'],
                        ['value' => 'depense', 'label' => 'Note de Frais'],
                    ],
                    'bank_accounts' => [], 
                    'invoices' => [],
                    'expense_reports' => [],
                    'etbac_files' => []
                ]
            ]);
        }
    }

    /**
     * Bulk delete transfer orders
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:transfer_orders,id',
        ]);

        TransferOrder::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transfer orders deleted successfully'
        ]);
    }

    /** POST /transfer-orders/{id}/send */
    public function send($id): JsonResponse
    {
        $transferOrder = TransferOrder::find($id);
        
        if (!$transferOrder) {
            return response()->json([
                'message' => 'Ordre de virement non trouvé.'
            ], 404);
        }

        if ($transferOrder->status !== 'en_attente') {
            return response()->json([
                'message' => 'Seuls les ordres en attente peuvent être envoyés.'
            ], 422);
        }

        $transferOrder->update(['status' => 'envoye']);
        
        $updatedOrder = TransferOrder::with([
            'debitedBankAccount', 
            'beneficiaryBankAccount', 
            'invoice', 
            'expenseReport',
            'etbacFile'
        ])->find($transferOrder->id);

        return response()->json($updatedOrder);
    }

    /** POST /transfer-orders/{id}/validate */
    public function validateOrder($id): JsonResponse  
    {
        $transferOrder = TransferOrder::find($id);
        
        if (!$transferOrder) {
            return response()->json([
                'message' => 'Ordre de virement non trouvé.'
            ], 404);
        }

        if ($transferOrder->status !== 'envoye') {
            return response()->json([
                'message' => 'Seuls les ordres envoyés peuvent être validés.'
            ], 422);
        }

        $transferOrder->update(['status' => 'valide']);
        
        $updatedOrder = TransferOrder::with([
            'debitedBankAccount', 
            'beneficiaryBankAccount', 
            'invoice', 
            'expenseReport',
            'etbacFile'
        ])->find($transferOrder->id);

        return response()->json($updatedOrder);
    }

    /** POST /transfer-orders/{id}/reject */
    public function reject($id): JsonResponse
    {
        $transferOrder = TransferOrder::find($id);
        
        if (!$transferOrder) {
            return response()->json([
                'message' => 'Ordre de virement non trouvé.'
            ], 404);
        }

        if ($transferOrder->status !== 'envoye') {
            return response()->json([
                'message' => 'Seuls les ordres envoyés peuvent être rejetés.'
            ], 422);
        }

        $transferOrder->update(['status' => 'rejete']);
        
        $updatedOrder = TransferOrder::with([
            'debitedBankAccount', 
            'beneficiaryBankAccount', 
            'invoice', 
            'expenseReport',
            'etbacFile'
        ])->find($transferOrder->id);

        return response()->json($updatedOrder);
    }
}