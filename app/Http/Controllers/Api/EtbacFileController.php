<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEtbacFileRequest;
use App\Http\Requests\UpdateEtbacFileRequest;
use App\Models\EtbacFile;
use App\Models\TransferOrder;
use App\Models\ProjectBankAccount;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EtbacFileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of EtbacFiles.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);
            
            $query = EtbacFile::with([
                'bankAccount',
                'beneficiary',
                'transferOrders' => function($query) {
                    $query->with(['debitedBankAccount', 'beneficiaryBankAccount']);
                }
            ]);

            if ($request->has('search') && !empty($request->get('search'))) {
                $search = $request->get('search');
                $query->where('etbac_code', 'LIKE', "%{$search}%");
            }

            if ($request->has('status') && !empty($request->get('status'))) {
                $query->where('status', $request->get('status'));
            }

            if ($request->has('bank_account_id') && !empty($request->get('bank_account_id'))) {
                $query->where('bank_account_id', $request->get('bank_account_id'));
            }

            if ($request->has('date_from') && !empty($request->get('date_from'))) {
                $query->whereDate('issue_date', '>=', $request->get('date_from'));
            }

            if ($request->has('date_to') && !empty($request->get('date_to'))) {
                $query->whereDate('issue_date', '<=', $request->get('date_to'));
            }

            $etbacFiles = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => $etbacFiles
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des fichiers ETBAC',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created EtbacFile.
     */
    public function store(StoreEtbacFileRequest $request): JsonResponse
    {
        try {

            $etbacFile = EtbacFile::create($request->validated());

            if ($request->has('transfer_order_ids') && !empty($request->transfer_order_ids)) {
                $etbacFile->attachTransferOrders($request->transfer_order_ids);
            }

            $etbacFile->load([
                'bankAccount',
                'beneficiary',
                'transferOrders' => function($query) {
                    $query->with(['debitedBankAccount', 'beneficiaryBankAccount']);
                }
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fichier ETBAC créé avec succès',
                'data' => $etbacFile
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur du création du fichier ETBAC',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified EtbacFile.
     */
    public function show(EtbacFile $etbacFile): JsonResponse
    {
        $etbacFile->load([
            'bankAccount',
            'beneficiary',
            'transferOrders' => function($query) {
                $query->with(['debitedBankAccount', 'beneficiaryBankAccount', 'invoice', 'expenseReport']);
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $etbacFile
        ]);
    }

    /**
     * Update the specified EtbacFile.
     */
    public function update(UpdateEtbacFileRequest $request, EtbacFile $etbacFile): JsonResponse
    {
        try {
            $etbacFile->update($request->validated());

            $etbacFile->load([
                'bankAccount',
                'beneficiary',
                'transferOrders' => function($query) {
                    $query->with(['debitedBankAccount', 'beneficiaryBankAccount']);
                }
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fichier ETBAC mis à jour avec succès',
                'data' => $etbacFile
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du fichier ETBAC',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified EtbacFile.
     */
    public function destroy(EtbacFile $etbacFile): JsonResponse
    {
        try {

            $etbacFile->transferOrders()->update(['etbac_file_id' => null]);
            $etbacFile->delete();

            return response()->json([
                'success' => true,
                'message' => 'Fichier ETBAC supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du fichier ETBAC',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Attach transfer orders to EtbacFile.
     */
    public function attachTransferOrders(Request $request, EtbacFile $etbacFile): JsonResponse
    {
        try {
            $request->validate([
                'transfer_order_ids' => 'required|array',
                'transfer_order_ids.*' => 'exists:transfer_orders,id'
            ]);

            $eligibleOrders = TransferOrder::whereIn('id', $request->transfer_order_ids)
                ->eligibleForEtbac()
                ->get();

            if ($eligibleOrders->count() !== count($request->transfer_order_ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Certains ordres de virement ne sont pas éligibles (non validés ou déjà associés)'
                ], 422);
            }

            $etbacFile->attachTransferOrders($request->transfer_order_ids);

            $etbacFile->load([
                'bankAccount',
                'beneficiary',
                'transferOrders' => function($query) {
                    $query->with(['debitedBankAccount', 'beneficiaryBankAccount']);
                }
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ordres de virement associés avec succès',
                'data' => $etbacFile
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'association des ordres de virement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detach transfer orders from EtbacFile.
     */
    public function detachTransferOrders(Request $request, EtbacFile $etbacFile): JsonResponse
    {
        try {
            $request->validate([
                'transfer_order_ids' => 'required|array',
                'transfer_order_ids.*' => 'exists:transfer_orders,id'
            ]);

            $etbacFile->detachTransferOrders($request->transfer_order_ids);

            $etbacFile->load([
                'bankAccount',
                'beneficiary',
                'transferOrders' => function($query) {
                    $query->with(['debitedBankAccount', 'beneficiaryBankAccount']);
                }
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ordres de virement détachés avec succès',
                'data' => $etbacFile
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du détachement des ordres de virement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get eligible transfer orders for EtbacFile.
     */
    public function getEligibleTransferOrders(): JsonResponse
    {
        try {
            $eligibleOrders = TransferOrder::eligibleForEtbac()
                ->with(['debitedBankAccount', 'beneficiaryBankAccount'])
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'transfer_number' => $order->transfer_number,
                        'beneficiary_name' => $order->beneficiary_name,
                        'amount' => $order->amount,
                        'issue_date' => $order->issue_date,
                        'debited_bank_account' => $order->debitedBankAccount?->account_holder_name,
                        'beneficiary_bank_account' => $order->beneficiaryBankAccount?->account_holder_name,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $eligibleOrders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des ordres de virement éligibles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get options for EtbacFile.
     */
    public function options(): JsonResponse
    {
        try {
            $options = [
                'statuses' => EtbacFile::STATUSES,
                'bank_accounts' => [],
                'invoices' => []
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

            if (class_exists(Invoice::class)) {
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
            }

            return response()->json([
                'success' => true,
                'data' => $options
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [
                    'statuses' => EtbacFile::STATUSES,
                    'bank_accounts' => [],
                    'invoices' => []
                ]
            ]);
        }
    }
}