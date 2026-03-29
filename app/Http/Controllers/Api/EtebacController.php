<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\EtebacService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Exception;

class EtebacController extends Controller
{
    protected EtebacService $etebacService;

    public function __construct(EtebacService $etebacService)
    {
        $this->etebacService = $etebacService;
    }

    /**
     * Generate ETEBAC file for a single payment (row-level action)
     * Auto-detects bank type from the payment's project bank account
     */
    public function generateForPayment(int $paymentId): JsonResponse
    {
        try {
            $payment = Payment::with('project.projectBankAccount.bank')->findOrFail($paymentId);

            // Validate payment status and method
            if ($payment->payment_status->value !== 'Validé') {
                return response()->json([
                    'message' => 'Le paiement doit être validé pour générer un fichier ETEBAC.'
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($payment->payment_method->value !== 'Virement') {
                return response()->json([
                    'message' => 'Le paiement doit être un virement pour générer un fichier ETEBAC.'
                ], Response::HTTP_BAD_REQUEST);
            }

            $bankAccount = $payment->project?->projectBankAccount;
            if (!$bankAccount) {
                return response()->json([
                    'message' => 'Le projet du paiement n\'a pas de compte bancaire associé.'
                ], Response::HTTP_BAD_REQUEST);
            }

            $bankType = $this->etebacService->getBankTypeFromAccount($bankAccount->id);

            // Generate ETEBAC file
            $etebacResult = $this->etebacService->generateEtebacFile(
                $bankAccount->id,
                [$paymentId]
            );

            // Generate PDF with ETEBAC filename
            $pdfResult = $bankType === EtebacService::BANK_TGR
                ? $this->etebacService->generateOrdreVirementTgrPdf($bankAccount->id, [$paymentId], $etebacResult['filename'])
                : $this->etebacService->generateOrdreVirementSgPdf($bankAccount->id, [$paymentId], $etebacResult['filename']);

            return response()->json([
                'message' => "Fichiers ETEBAC {$bankType} générés avec succès.",
                'data' => [
                    'bank_type' => $bankType,
                    'etebac' => $etebacResult,
                    'pdf' => $pdfResult,
                ],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la génération des fichiers ETEBAC: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Generate ETEBAC file (auto-detects bank type from bank_code: 310=TGR, 022=SG)
     */
    public function generateEtebac(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'bank_account_id' => 'required|integer|exists:project_bank_accounts,id',
                'payment_ids' => 'required|array|min:1',
                'payment_ids.*' => 'integer|exists:payments,id',
            ]);

            // Bank type is auto-detected from bank_code
            $result = $this->etebacService->generateEtebacFile(
                $validated['bank_account_id'],
                $validated['payment_ids']
            );

            $bankType = $this->etebacService->getBankTypeFromAccount($validated['bank_account_id']);

            return response()->json([
                'message' => "Fichier ETEBAC {$bankType} généré avec succès.",
                'data' => array_merge($result, ['bank_type' => $bankType]),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la génération du fichier ETEBAC: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Generate ETEBAC file for SG (Société Générale) - Legacy endpoint
     */
    public function generateEtebacSg(Request $request): JsonResponse
    {
        return $this->generateEtebac($request);
    }

    /**
     * Generate ETEBAC file for TGR (Trésorerie Générale du Royaume) - Legacy endpoint
     */
    public function generateEtebacTgr(Request $request): JsonResponse
    {
        return $this->generateEtebac($request);
    }

    /**
     * Generate Ordre de Virement PDF (auto-detects bank type from bank_code: 310=TGR, 022=SG)
     */
    public function generateOrdreVirement(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'bank_account_id' => 'required|integer|exists:project_bank_accounts,id',
                'payment_ids' => 'required|array|min:1',
                'payment_ids.*' => 'integer|exists:payments,id',
            ]);

            $bankType = $this->etebacService->getBankTypeFromAccount($validated['bank_account_id']);

            $result = $bankType === EtebacService::BANK_TGR
                ? $this->etebacService->generateOrdreVirementTgrPdf($validated['bank_account_id'], $validated['payment_ids'])
                : $this->etebacService->generateOrdreVirementSgPdf($validated['bank_account_id'], $validated['payment_ids']);

            return response()->json([
                'message' => "Ordre de virement {$bankType} généré avec succès.",
                'data' => array_merge($result, ['bank_type' => $bankType]),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la génération de l\'ordre de virement: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Generate Ordre de Virement PDF for TGR - Legacy endpoint
     */
    public function generateOrdreVirementTgr(Request $request): JsonResponse
    {
        return $this->generateOrdreVirement($request);
    }

    /**
     * Generate Ordre de Virement PDF for SG - Legacy endpoint
     */
    public function generateOrdreVirementSg(Request $request): JsonResponse
    {
        return $this->generateOrdreVirement($request);
    }

    /**
     * Download ETEBAC file
     */
    public function downloadEtebac(string $filename)
    {
        try {
            $path = 'etebac/' . $filename;
            
            if (!Storage::disk('local')->exists($path)) {
                return response()->json([
                    'message' => 'Fichier non trouvé.'
                ], Response::HTTP_NOT_FOUND);
            }

            return Storage::disk('local')->download($path, $filename, [
                'Content-Type' => 'text/plain',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du téléchargement: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Download Ordre de Virement PDF
     */
    public function downloadOrdreVirement(string $filename)
    {
        try {
            $path = 'ordre-virement/' . $filename;
            
            if (!Storage::disk('local')->exists($path)) {
                return response()->json([
                    'message' => 'Fichier non trouvé.'
                ], Response::HTTP_NOT_FOUND);
            }

            return Storage::disk('local')->download($path, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du téléchargement: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Generate all files (ETEBAC + PDF) for a batch of payments
     * Bank type is auto-detected from bank_code: 310=TGR, 022=SG
     */
    public function generateAll(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'bank_account_id' => 'required|integer|exists:project_bank_accounts,id',
                'payment_ids' => 'required|array|min:1',
                'payment_ids.*' => 'integer|exists:payments,id',
            ]);

            $bankAccountId = $validated['bank_account_id'];
            $paymentIds = $validated['payment_ids'];

            // Auto-detect bank type from bank_code
            $bankType = $this->etebacService->getBankTypeFromAccount($bankAccountId);

            // Generate ETEBAC file
            $etebacResult = $this->etebacService->generateEtebacFile(
                $bankAccountId,
                $paymentIds
            );

            // Generate PDF
            $pdfResult = $bankType === EtebacService::BANK_TGR
                ? $this->etebacService->generateOrdreVirementTgrPdf($bankAccountId, $paymentIds)
                : $this->etebacService->generateOrdreVirementSgPdf($bankAccountId, $paymentIds);

            return response()->json([
                'message' => "Fichiers {$bankType} générés avec succès.",
                'data' => [
                    'bank_type' => $bankType,
                    'etebac' => $etebacResult,
                    'pdf' => $pdfResult,
                ],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la génération des fichiers: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
