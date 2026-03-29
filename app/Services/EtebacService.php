<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\ProjectBankAccount;
use Barryvdh\DomPDF\PDF as DomPDF;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class EtebacService
{
    /**
     * Bank type constants
     */
    const BANK_TGR = 'TGR';
    const BANK_SG = 'SG';

    /**
     * Bank code constants
     */
    const BANK_CODE_TGR = '310';
    const BANK_CODE_SG = '022';

    /**
     * SIMT format constants for SG
     */
    const SIMT_LINE_LENGTH = 500;
    const SIMT_RECORD_HEADER = '10';
    const SIMT_RECORD_DETAIL = '04';
    const SIMT_RECORD_FOOTER = '11';
    const SIMT_TRANSFER_TYPE = '32'; // Virement ordinaire
    const SIMT_OPERATION_CODE = '020'; // Code type opération

    /**
     * ETEBAC format constants for TGR
     */
    const ETEBAC_LINE_LENGTH = 319;

    /**
     * Detect bank type from bank code
     *
     * @param string $bankCode
     * @return string
     * @throws \Exception
     */
    public function detectBankType(string $bankCode): string
    {
        return match($bankCode) {
            self::BANK_CODE_TGR => self::BANK_TGR,
            self::BANK_CODE_SG => self::BANK_SG,
            default => throw new \Exception("Code banque non reconnu: {$bankCode}. Codes supportés: 310 (TGR), 022 (SG)"),
        };
    }

    /**
     * Get bank type from bank account
     *
     * @param int $bankAccountId
     * @return string
     */
    public function getBankTypeFromAccount(int $bankAccountId): string
    {
        $bankAccount = ProjectBankAccount::with('bank')->findOrFail($bankAccountId);
        
        if (!$bankAccount->bank) {
            throw new \Exception('Le compte bancaire n\'a pas de banque associée.');
        }

        return $this->detectBankType($bankAccount->bank->bank_code);
    }

    /**
     * Generate ETEBAC file for validated payments with bank transfer method
     *
     * @param int $bankAccountId The bank account ID (donneur d'ordre)
     * @param array $paymentIds Array of payment IDs to include
     * @param string|null $bankType Bank type (TGR or SG) - if null, auto-detected from bank_code
     * @return array File info with path and filename
     */
    public function generateEtebacFile(int $bankAccountId, array $paymentIds, ?string $bankType = null): array
    {
        $bankAccount = ProjectBankAccount::with('bank')->findOrFail($bankAccountId);

        // Auto-detect bank type from bank_code if not provided
        if ($bankType === null) {
            $bankType = $this->detectBankType($bankAccount->bank->bank_code);
        }
        
        $payments = Payment::with([
            'invoices.purchaseOrder.supplier',
            'expenseReports.createdBy',
            'project.projectBankAccount'
        ])
            ->whereIn('id', $paymentIds)
            ->where('payment_status', 'Validé')
            ->where('payment_method', 'Virement')
            ->get();

        if ($payments->isEmpty()) {
            throw new \Exception('Aucun paiement validé avec méthode virement trouvé.');
        }

        $content = $bankType === self::BANK_TGR 
            ? $this->generateTgrFormat($bankAccount, $payments)
            : $this->generateSgFormat($bankAccount, $payments);

        $filename = $this->generateFilename();
        $path = 'etebac/' . $filename;
        
        Storage::disk('local')->put($path, $content);

        return [
            'filename' => $filename,
            'path' => $path,
            'full_path' => Storage::disk('local')->path($path),
            'payments_count' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'content' => base64_encode($content),
        ];
    }

    /**
     * Generate SIMT content for Société Générale (SG)
     * SIMT format uses 500 characters per line with record types 10/04/11
     */
    protected function generateSgFormat(ProjectBankAccount $bankAccount, Collection $payments): string
    {
        $lines = [];
        $totalAmount = 0;
        $paymentCount = 0;

        // Generate unique lot number for this batch
        $lotNumber = $this->generateLotNumber();

        // Header line (10) - SIMT format
        $lines[] = $this->buildSimtHeaderLine($bankAccount, $lotNumber);

        // Payment lines (04) - SIMT format
        foreach ($payments as $payment) {
            // Handle invoice payments
            if ($payment->payment_type === Payment::TYPE_INVOICE || $payment->invoices->isNotEmpty()) {
                foreach ($payment->invoices as $invoice) {
                    $supplier = $invoice->purchaseOrder?->supplier;
                    if (!$supplier) continue;

                    $amount = $invoice->pivot->amount ?? $invoice->total ?? 0;
                    $totalAmount += $amount;
                    $paymentCount++;

                    $lines[] = $this->buildSimtDetailLine(
                        $bankAccount,
                        $supplier->rib ?? '',
                        $supplier->company_name ?? $supplier->trade_name ?? '',
                        $amount,
                        $invoice->invoice_number,
                        $payment->transaction_date
                    );
                }
            }
            
            // Handle expense report payments
            if ($payment->payment_type === Payment::TYPE_EXPENSE_REPORT || $payment->expenseReports->isNotEmpty()) {
                foreach ($payment->expenseReports as $expenseReport) {
                    $collaborator = $expenseReport->createdBy;
                    $beneficiaryName = $collaborator 
                        ? ($collaborator->first_name . ' ' . $collaborator->last_name)
                        : 'FONDATION ZAKOURA';
                    $beneficiaryRib = $collaborator->rib ?? '';
                    
                    $amount = $expenseReport->pivot->amount ?? $expenseReport->total_amount ?? 0;
                    $totalAmount += $amount;
                    $paymentCount++;

                    $lines[] = $this->buildSimtDetailLine(
                        $bankAccount,
                        $beneficiaryRib,
                        $beneficiaryName,
                        $amount,
                        'NDF-' . $expenseReport->id,
                        $payment->transaction_date
                    );
                }
            }
        }

        // Footer line (11) - SIMT format
        $lines[] = $this->buildSimtFooterLine($paymentCount, $totalAmount);

        return implode("\n", $lines);
    }

    /**
     * Generate ETEBAC content for TGR (Trésorerie Générale du Royaume)
     */
    protected function generateTgrFormat(ProjectBankAccount $bankAccount, Collection $payments): string
    {
        $lines = [];
        $totalAmount = 0;
        $paymentCount = 0;

        // Header line (03)
        $lines[] = $this->buildTgrHeaderLine($bankAccount);

        // Payment lines (04)
        foreach ($payments as $payment) {
            // Handle invoice payments
            if ($payment->payment_type === Payment::TYPE_INVOICE || $payment->invoices->isNotEmpty()) {
                foreach ($payment->invoices as $invoice) {
                    $supplier = $invoice->purchaseOrder?->supplier;
                    if (!$supplier) continue;

                    $amount = $invoice->pivot->amount ?? $invoice->total ?? 0;
                    $totalAmount += $amount;
                    $paymentCount++;

                    $lines[] = $this->buildTgrPaymentLine(
                        $supplier,
                        $amount,
                        $invoice->invoice_number,
                        $payment->transaction_date
                    );
                }
            }
            
            // Handle expense report payments
            if ($payment->payment_type === Payment::TYPE_EXPENSE_REPORT || $payment->expenseReports->isNotEmpty()) {
                foreach ($payment->expenseReports as $expenseReport) {
                    $amount = $expenseReport->pivot->amount ?? $expenseReport->total_amount ?? 0;
                    $totalAmount += $amount;
                    $paymentCount++;

                    $lines[] = $this->buildTgrExpenseReportLine(
                        $expenseReport,
                        $amount,
                        $payment->transaction_date
                    );
                }
            }
        }

        // Footer line (08)
        $lines[] = $this->buildTgrFooterLine($paymentCount, $totalAmount);

        return implode("\n", $lines);
    }

    // =========================================================================
    // SIMT FORMAT METHODS FOR SOCIÉTÉ GÉNÉRALE (SG)
    // SIMT uses 500 characters per line with record types 10/04/11
    // Based on official SG Morocco SIMT specification (Avril 2005)
    // =========================================================================

    /**
     * Build SIMT Header Line (Record type 10)
     * Total length: 500 characters
     * 
     * SIMT Header structure per official SG Morocco specification:
     * A1:  Code type enregistrement      Pos 001-002 (2)  = "10"
     * B:   Identifiant de la commande    Pos 003-007 (5)  = "00000"
     * B1:  Identifiant du service        Pos 003-007 (5)  = "00000" (overlaps with B)
     * B2:  Identifiant de la source      Pos 008-013 (6)  = "000000"
     * B3:  Numéro unique du lot          Pos 014-017 (4)  = "0000"
     * C1:  Code participant SIMT         Pos 018-020 (3)  = "000" (will be "022" for SG)
     * D1:  Date d'envoi du fichier       Pos 021-028 (8)  = AAAAMMJJ
     * D2:  Heure d'envoi du fichier      Pos 029-034 (6)  = HHMMSS
     * E1:  Code devise                   Pos 035-037 (3)  = "MAD"
     * E2:  Nb de décimales de la devise  Pos 038-038 (1)  = "2"
     * F1:  Type de virement              Pos 039-040 (2)  = "32"
     * G1:  Zone réservée                 Pos 041-500 (460) = Blancs
     */
    protected function buildSimtHeaderLine(ProjectBankAccount $bankAccount, string $lotNumber): string
    {
        $line = '';
        
        // A1: Code type enregistrement (2 chars) - Position 001-002
        $line .= '10';
        
        // B: Identifiant de la commande (5 chars) - Position 003-007
        $line .= '00000';
        
        // B2: Identifiant de la source (6 chars) - Position 008-013
        $line .= '000000';
        
        // B3: Numéro unique du lot (4 chars) - Position 014-017
        $line .= str_pad(mb_substr($lotNumber, 0, 4), 4, '0', STR_PAD_LEFT);
        
        // C1: Code participant SIMT (3 chars) - Position 018-020 - SG = 022
        $line .= '022';
        
        // D1: Date d'envoi du fichier (8 chars) - Position 021-028 - AAAAMMJJ
        $line .= Carbon::now()->format('Ymd');
        
        // D2: Heure d'envoi du fichier (6 chars) - Position 029-034 - HHMMSS
        $line .= Carbon::now()->format('His');
        
        // E1: Code devise (3 chars) - Position 035-037
        $line .= 'MAD';
        
        // E2: Nombre de décimales (1 char) - Position 038
        $line .= '2';
        
        // F1: Type de virement (2 chars) - Position 039-040 - "32" = virement ordinaire
        $line .= '32';
        
        // G1: Zone réservée (460 chars) - Position 041-500
        $line .= str_pad('', 460, ' ');
        
        // Assert line length
        $this->assertSimtLineLength($line, 'Header');
        
        return $line; // Total: 500 characters
    }

    /**
     * Build SIMT Detail Line (Record type 04)
     * Total length: 500 characters
     * 
     * SIMT Detail structure per official SG Morocco specification:
     * A1:   Code type enregistrement              Pos 001-002 (2)  = "04"
     * A2:   Code type opération                   Pos 003-005 (3)  = "020"
     * B1:   Code établissement Participant émetteur Pos 006-008 (3)  = "022"
     * B2:   Code établissement Participant récepteur Pos 009-011 (3)
     * B3.1: Code lieu compensation émetteur       Pos 012-014 (3)  = "780"
     * B3.2: Code lieu compensation récepteur      Pos 015-017 (3)  = "780"
     * B4:   Référence Interbancaire Opération     Pos 018-043 (26) = Blancs
     * B5:   Code devise                           Pos 044-047 (4)  = "MAD2"
     * B6:   Montant du virement                   Pos 048-063 (16) = Montant en centimes
     * B7:   Date de compensation                  Pos 064-071 (8)  = Blancs
     * B8:   Date de règlement                     Pos 072-079 (8)  = Blancs
     * B9:   Code motif de rejet technique         Pos 080-082 (3)  = Blancs
     * B10:  Zone réservée pour usage futur        Pos 083-100 (18) = Blancs
     * C1:   Sous-code opération                   Pos 101-102 (2)  = "00"
     * C2:   Zone réservée                         Pos 103-104 (2)  = Blancs
     * C3:   Zone réservée                         Pos 105-120 (16) = Blancs
     * C4:   Zone réservée                         Pos 121-136 (16) = Blancs
     * C5:   Nature du compte donneur d'ordre      Pos 137-137 (1)  = "1"
     * C6:   Raison sociale client donneur d'ordre Pos 138-172 (35)
     * C7:   Nom et prénom du bénéficiaire         Pos 173-207 (35)
     * C8:   RIB du client donneur d'ordre         Pos 208-231 (24)
     *       - C9:   code établissement            Pos 208-210 (3)  = "022"
     *       - C9.1: code localité                 Pos 211-213 (3)
     *       - C9.2: Numéro de compte              Pos 214-229 (16)
     *       - C9.3: clé RIB                       Pos 230-231 (2)
     * C10:  RIB du client destinataire            Pos 232-255 (24)
     *       - C10.1: code établissement           Pos 232-234 (3)
     *       - C10.2: code localité                Pos 235-237 (3)
     *       - C10.3: Numéro de compte             Pos 238-253 (16)
     *       - C10.4: clé RIB                      Pos 254-255 (2)
     * C11:  Libellé donneur d'ordre               Pos 256-290 (35)
     * C12:  Libellé bénéficiaire                  Pos 291-325 (35)
     * C13:  Zone réservée                         Pos 326-500 (175) = Blancs
     */
    protected function buildSimtDetailLine(
        ProjectBankAccount $bankAccount,
        string $beneficiaryRib,
        string $beneficiaryName,
        float $amount,
        string $reference,
        $transactionDate
    ): string {
        $line = '';
        
        // Parse RIBs into components
        $donneurRib = $this->parseRib($bankAccount->rib_iban);
        $benefRib = $this->parseRib($beneficiaryRib);
        
        // A1: Code type enregistrement (2 chars) - Position 001-002
        $line .= '04';
        
        // A2: Code type opération (3 chars) - Position 003-005
        $line .= '020';
        
        // B1: Code établissement Participant émetteur (3 chars) - Position 006-008 - SG = 022
        $line .= '022';
        
        // B2: Code établissement Participant récepteur (3 chars) - Position 009-011
        $line .= str_pad(mb_substr($benefRib['bank'], 0, 3), 3, '0', STR_PAD_LEFT);
        
        // B3.1: Code lieu de compensation émetteur (3 chars) - Position 012-014 = "780"
        $line .= '780';
        
        // B3.2: Code lieu de compensation récepteur (3 chars) - Position 015-017 = "780"
        $line .= '780';
        
        // B4: Référence Interbancaire Opération (26 chars) - Position 018-043 = Blancs
        $line .= str_pad('', 26, ' ');
        
        // B5: Code devise (4 chars) - Position 044-047 = "MAD2" (devise + décimales)
        $line .= 'MAD2';
        
        // B6: Montant du virement (16 chars) - Position 048-063 - en centimes, cadré à gauche par des zéros
        $montant = (int)round($amount * 100);
        $line .= str_pad($montant, 16, '0', STR_PAD_LEFT);
        
        // B7: Date de compensation (8 chars) - Position 064-071 = Blancs
        $line .= str_pad('', 8, ' ');
        
        // B8: Date de règlement (8 chars) - Position 072-079 = Blancs
        $line .= str_pad('', 8, ' ');
        
        // B9: Code motif de rejet technique (3 chars) - Position 080-082 = Blancs
        $line .= str_pad('', 3, ' ');
        
        // B10: Zone réservée pour usage futur (18 chars) - Position 083-100 = Blancs
        $line .= str_pad('', 18, ' ');
        
        // C1: Sous-code opération (2 chars) - Position 101-102 = "00"
        $line .= '00';
        
        // C2: Zone réservée (2 chars) - Position 103-104 = Blancs
        $line .= str_pad('', 2, ' ');
        
        // C3: Zone réservée (16 chars) - Position 105-120 = Blancs
        $line .= str_pad('', 16, ' ');
        
        // C4: Zone réservée (16 chars) - Position 121-136 = Blancs
        $line .= str_pad('', 16, ' ');
        
        // C5: Nature du compte du donneur d'ordre (1 char) - Position 137 = "1"
        $line .= '1';
        
        // C6: Raison sociale du client donneur d'ordre (35 chars) - Position 138-172
        $line .= str_pad(mb_substr($this->sanitizeText('FONDATION ZAKOURA'), 0, 35), 35, ' ');
        
        // C7: Nom et prénom du bénéficiaire (35 chars) - Position 173-207
        $line .= str_pad(mb_substr($this->sanitizeText($beneficiaryName), 0, 35), 35, ' ');
        
        // C8: RIB du client donneur d'ordre (24 chars) - Position 208-231
        // C9: code établissement (3 chars) - Position 208-210
        $line .= str_pad(mb_substr($donneurRib['bank'], 0, 3), 3, '0', STR_PAD_LEFT);
        // C9.1: code localité (3 chars) - Position 211-213
        $line .= str_pad(mb_substr($donneurRib['locality'], 0, 3), 3, '0', STR_PAD_LEFT);
        // C9.2: Numéro de compte (16 chars) - Position 214-229
        $line .= str_pad(mb_substr($donneurRib['account'], 0, 16), 16, '0', STR_PAD_LEFT);
        // C9.3: clé RIB (2 chars) - Position 230-231
        $line .= str_pad(mb_substr($donneurRib['key'], 0, 2), 2, '0', STR_PAD_LEFT);
        
        // C10: RIB du client destinataire (24 chars) - Position 232-255
        // C10.1: code établissement (3 chars) - Position 232-234
        $line .= str_pad(mb_substr($benefRib['bank'], 0, 3), 3, '0', STR_PAD_LEFT);
        // C10.2: code localité (3 chars) - Position 235-237
        $line .= str_pad(mb_substr($benefRib['locality'], 0, 3), 3, '0', STR_PAD_LEFT);
        // C10.3: Numéro de compte (16 chars) - Position 238-253
        $line .= str_pad(mb_substr($benefRib['account'], 0, 16), 16, '0', STR_PAD_LEFT);
        // C10.4: clé RIB (2 chars) - Position 254-255
        $line .= str_pad(mb_substr($benefRib['key'], 0, 2), 2, '0', STR_PAD_LEFT);
        
        // C11: Libellé donneur d'ordre (35 chars) - Position 256-290
        $line .= str_pad(mb_substr($this->sanitizeText('VIREMENT ' . $reference), 0, 35), 35, ' ');
        
        // C12: Libellé bénéficiaire (35 chars) - Position 291-325
        $line .= str_pad(mb_substr($this->sanitizeText($reference), 0, 35), 35, ' ');
        
        // C13: Zone réservée (175 chars) - Position 326-500
        $line .= str_pad('', 175, ' ');
        
        // Assert line length
        $this->assertSimtLineLength($line, 'Detail');
        
        return $line; // Total: 500 characters
    }

    /**
     * Build SIMT Footer Line (Record type 11)
     * Total length: 500 characters
     * 
     * SIMT Footer structure (fin de lot):
     * A1: Code type enregistrement              Pos 001-002 (2)  = "11"
     * A2: Nombre total d'enregistrements détail Pos 003-010 (8)
     * A3: Montant total                         Pos 011-028 (18) = en centimes
     * A4: Zone réservée                         Pos 029-500 (472) = Blancs
     */
    protected function buildSimtFooterLine(int $count, float $totalAmount): string
    {
        $line = '';
        
        // A1: Code type enregistrement (2 chars) - Position 001-002
        $line .= '11';
        
        // A2: Nombre total d'enregistrements détail (8 chars) - Position 003-010
        $line .= str_pad($count, 8, '0', STR_PAD_LEFT);
        
        // A3: Montant total en centimes (18 chars) - Position 011-028
        $montant = (int)round($totalAmount * 100);
        $line .= str_pad($montant, 18, '0', STR_PAD_LEFT);
        
        // A4: Zone réservée (472 chars) - Position 029-500
        $line .= str_pad('', 472, ' ');
        
        // Assert line length
        $this->assertSimtLineLength($line, 'Footer');
        
        return $line; // Total: 500 characters
    }

    /**
     * Assert that SIMT line length is exactly 500 characters
     *
     * @param string $line The line to check
     * @param string $type The type of line (Header, Detail, Footer)
     * @throws \Exception if line length is not 500
     */
    protected function assertSimtLineLength(string $line, string $type): void
    {
        $length = strlen($line);
        if ($length !== self::SIMT_LINE_LENGTH) {
            throw new \Exception("SIMT {$type} line length error: expected 500 characters, got {$length}");
        }
    }

    /**
     * Parse RIB into its components
     * Moroccan RIB format: 24 digits
     * - Bank code: 3 digits (positions 1-3)
     * - Locality code: 3 digits (positions 4-6)  
     * - Account number: 16 digits (positions 7-22)
     * - Key: 2 digits (positions 23-24)
     *
     * @param string $rib The RIB to parse
     * @return array Array with keys: bank, locality, account, key
     */
    protected function parseRib(string $rib): array
    {
        $cleanRib = $this->cleanRib($rib);
        
        // Pad to 24 characters if shorter
        $cleanRib = str_pad($cleanRib, 24, '0', STR_PAD_LEFT);
        
        return [
            'bank' => substr($cleanRib, 0, 3),      // 3 digits
            'locality' => substr($cleanRib, 3, 3), // 3 digits
            'account' => substr($cleanRib, 6, 16), // 16 digits
            'key' => substr($cleanRib, 22, 2),     // 2 digits
        ];
    }

    /**
     * Generate unique lot number for SIMT batch
     * Format: Sequence number (4 digits)
     */
    protected function generateLotNumber(): string
    {
        $sequence = $this->getNextSequence();
        return sprintf('%04d', $sequence);
    }

    /**
     * Sanitize text for SIMT format
     * Remove special characters and accents that might cause issues
     */
    protected function sanitizeText(string $text): string
    {
        // Convert to uppercase
        $text = mb_strtoupper($text);
        
        // Replace common accented characters
        $replacements = [
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'À' => 'A', 'Â' => 'A', 'Ä' => 'A',
            'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ô' => 'O', 'Ö' => 'O',
            'Î' => 'I', 'Ï' => 'I',
            'Ç' => 'C',
            'é' => 'E', 'è' => 'E', 'ê' => 'E', 'ë' => 'E',
            'à' => 'A', 'â' => 'A', 'ä' => 'A',
            'ù' => 'U', 'û' => 'U', 'ü' => 'U',
            'ô' => 'O', 'ö' => 'O',
            'î' => 'I', 'ï' => 'I',
            'ç' => 'C',
        ];
        
        $text = strtr($text, $replacements);
        
        // Remove any remaining non-alphanumeric characters except spaces
        $text = preg_replace('/[^A-Z0-9 ]/', '', $text);
        
        return $text;
    }

    // =========================================================================
    // TGR ETEBAC FORMAT METHODS (UNCHANGED)
    // ETEBAC uses 319 characters per line with record types 03/04/08
    // =========================================================================

    /**
     * Build TGR Header Line (Record type 03)
     * Total length: 319 characters
     */
    protected function buildTgrHeaderLine(ProjectBankAccount $bankAccount): string
    {
        $line = '';
        
        // Zone 1: Code enregistrement (2 chars) - Position 1-2
        $line .= '03';
        
        // Zone 2: Zone réservée (16 chars) - Position 3-18
        $line .= str_pad('', 16, ' ');
        
        // Zone 3: Nom/Raison sociale du donneur d'ordre (182 chars) - Position 19-200
        $line .= str_pad(mb_substr('FONDATION ZAKOURA', 0, 182), 182, ' ');
        
        // Zone 4: Identifiant du compte à débiter - RIB (24 chars) - Position 201-224
        $line .= str_pad($this->cleanRib($bankAccount->rib_iban), 24, '0', STR_PAD_LEFT);
        
        // Zone 5: Zone réservée (10 chars) - Position 225-234
        $line .= str_pad('', 10, ' ');
        
        // Zone 6: Code devise (3 chars) - Position 235-237
        $line .= 'MAD';
        
        // Zone 7: Zone réservée (82 chars) - Position 238-319
        $line .= str_pad('', 82, ' ');
        
        return $line; // Total: 319 characters
    }

    /**
     * Build TGR Payment Line (Record type 04)
     * Total length: 319 characters
     */
    protected function buildTgrPaymentLine($supplier, float $amount, string $invoiceNumber, $transactionDate): string
    {
        $line = '';
        
        // Zone 1: Code enregistrement (2 chars) - Position 1-2
        $line .= '04';
        
        // Zone 2: Code opération (2 chars) - Position 3-4 - "VF" per spec
        $line .= '99';
        
        // Zone 3: Zone réservée (7 chars) - Position 5-11
        $line .= str_pad('', 7, ' ');
        
        // Zone 4: RIB Bénéficiaire (24 chars) - Position 12-35
        $line .= str_pad($this->cleanRib($supplier->rib ?? ''), 24, '0', STR_PAD_LEFT);
        
        // Zone 5: Zone réservée (10 chars) - Position 36-45
        $line .= str_pad('', 10, ' ');
        
        // Zone 6: Nom du bénéficiaire (180 chars) - Position 46-225
        $name = mb_substr($supplier->company_name ?? $supplier->trade_name ?? '', 0, 180);
        $line .= str_pad($name, 180, ' ');
        
        // Zone 7: Montant (14 chars) - Position 226-239 - Montant * 100
        $montant = (int)round($amount * 100);
        $line .= str_pad($montant, 14, '0', STR_PAD_LEFT);
        
        // Zone 8: Nombre de décimales (1 char) - Position 240 - Value "2"
        $line .= '2';
        
        // Zone 9: Zone réservée (10 chars) - Position 241-250
        $line .= str_pad('', 10, ' ');
        
        // Zone 10: Motif du virement (58 chars) - Position 251-308
        $motif = mb_substr($invoiceNumber, 0, 58);
        $line .= str_pad($motif, 58, ' ');
        
        // Zone 11: Date génération (8 chars) - Position 309-316 - Format AAAAMMJJ
        $line .= Carbon::parse($transactionDate)->format('Ymd');
        
        // Zone 12: Zone réservée (3 chars) - Position 317-319
        $line .= str_pad('', 3, ' ');

        return $line; // Total: 319 characters
    }

    /**
     * Build TGR Footer Line (Record type 08)
     * Total length: 319 characters
     */
    protected function buildTgrFooterLine(int $count, float $totalAmount): string
    {
        $line = '';
        
        // Zone 1: Code enregistrement (2 chars) - Position 1-2
        $line .= '08';
        
        // Zone 2: Zone réservée (2 chars) - Position 3-4
        $line .= str_pad('', 2, ' ');
        
        // Zone 3: Nombre d'enregistrements (6 chars) - Position 5-10
        $line .= str_pad($count, 6, '0', STR_PAD_LEFT);
        
        // Zone 4: Zone réservée (243 chars) - Position 11-253
        $line .= str_pad('', 243, ' ');
        
        // Zone 5: Montant total (18 chars) - Position 254-271 - Montant * 100
        $montant = (int)round($totalAmount * 100);
        $line .= str_pad($montant, 18, '0', STR_PAD_LEFT);
        
        // Zone 6: Zone réservée (48 chars) - Position 272-319
        $line .= str_pad('', 48, ' ');
        
        return $line; // Total: 319 characters
    }

    /**
     * Build TGR Expense Report Line (Record type 04) for expense reports
     * Total length: 319 characters
     */
    protected function buildTgrExpenseReportLine($expenseReport, float $amount, $transactionDate): string
    {
        // Get beneficiary info - for expense reports, the beneficiary is the collaborator
        $collaborator = $expenseReport->createdBy;
        $beneficiaryName = $collaborator 
            ? ($collaborator->first_name . ' ' . $collaborator->last_name)
            : 'FONDATION ZAKOURA';
        $beneficiaryRib = $collaborator->rib ?? '';
        
        $line = '';
        
        // Zone 1: Code enregistrement (2 chars) - Position 1-2
        $line .= '04';
        
        // Zone 2: Code opération (2 chars) - Position 3-4 - "VF" per spec
        $line .= '99';
        
        // Zone 3: Zone réservée (7 chars) - Position 5-11
        $line .= str_pad('', 7, ' ');
        
        // Zone 4: RIB Bénéficiaire (24 chars) - Position 12-35
        $line .= str_pad($this->cleanRib($beneficiaryRib), 24, '0', STR_PAD_LEFT);
        
        // Zone 5: Zone réservée (10 chars) - Position 36-45
        $line .= str_pad('', 10, ' ');
        
        // Zone 6: Nom du bénéficiaire (180 chars) - Position 46-225
        $name = mb_substr($beneficiaryName, 0, 180);
        $line .= str_pad($name, 180, ' ');
        
        // Zone 7: Montant (14 chars) - Position 226-239 - Montant * 100
        $montant = (int)round($amount * 100);
        $line .= str_pad($montant, 14, '0', STR_PAD_LEFT);
        
        // Zone 8: Nombre de décimales (1 char) - Position 240 - Value "2"
        $line .= '2';
        
        // Zone 9: Zone réservée (10 chars) - Position 241-250
        $line .= str_pad('', 10, ' ');
        
        // Zone 10: Motif du virement (58 chars) - Position 251-308
        $motif = mb_substr('NDF-' . $expenseReport->id, 0, 58);
        $line .= str_pad($motif, 58, ' ');
        
        // Zone 11: Date génération (8 chars) - Position 309-316 - Format AAAAMMJJ
        $line .= Carbon::parse($transactionDate)->format('Ymd');
        
        // Zone 12: Zone réservée (3 chars) - Position 317-319
        $line .= str_pad('', 3, ' ');

        return $line; // Total: 319 characters
    }

    /**
     * Generate Ordre de Virement PDF for TGR
     */
    public function generateOrdreVirementTgrPdf(int $bankAccountId, array $paymentIds, string $bvovFilename = ''): array
    {
        $bankAccount = ProjectBankAccount::with('bank')->findOrFail($bankAccountId);
        
        $payments = Payment::with([
            'invoices.purchaseOrder.supplier',
            'expenseReports.createdBy',
            'project.projectBankAccount'
        ])
            ->whereIn('id', $paymentIds)
            ->where('payment_status', 'Validé')
            ->where('payment_method', 'Virement')
            ->get();

        $data = $this->prepareOrdreVirementData($bankAccount, $payments, 'TGR', $bvovFilename);
        
        /** @var DomPDF $pdf */
        $pdf = app()->make(DomPDF::class);
        $pdf->loadView('pdf.ordre-virement-tgr', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'OV_TGR_' . Carbon::now()->format('Ymd_His') . '.pdf';
        $path = 'ordre-virement/' . $filename;
        
        // Store output once and reuse
        $pdfOutput = $pdf->output();
        
        Storage::disk('local')->put($path, $pdfOutput);

        return [
            'filename' => $filename,
            'path' => $path,
            'full_path' => Storage::disk('local')->path($path),
            'pdf_content' => base64_encode($pdfOutput),
        ];
    }

    /**
     * Generate Ordre de Virement PDF for SG
     */
    public function generateOrdreVirementSgPdf(int $bankAccountId, array $paymentIds, string $bvovFilename = ''): array
    {
        $bankAccount = ProjectBankAccount::with('bank')->findOrFail($bankAccountId);
        
        $payments = Payment::with([
            'invoices.purchaseOrder.supplier',
            'expenseReports.createdBy',
            'project.projectBankAccount'
        ])
            ->whereIn('id', $paymentIds)
            ->where('payment_status', 'Validé')
            ->where('payment_method', 'Virement')
            ->get();

        $data = $this->prepareOrdreVirementData($bankAccount, $payments, 'SG', $bvovFilename);
        
        /** @var DomPDF $pdf */
        $pdf = app()->make(DomPDF::class);
        $pdf->loadView('pdf.ordre-virement-sg', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'OV_SG_' . Carbon::now()->format('Ymd_His') . '.pdf';
        $path = 'ordre-virement/' . $filename;
        
        // Store output once and reuse
        $pdfOutput = $pdf->output();
        
        Storage::disk('local')->put($path, $pdfOutput);

        return [
            'filename' => $filename,
            'path' => $path,
            'full_path' => Storage::disk('local')->path($path),
            'pdf_content' => base64_encode($pdfOutput),
        ];
    }

    /**
     * Prepare data for Ordre de Virement PDF
     */
    protected function prepareOrdreVirementData(ProjectBankAccount $bankAccount, Collection $payments, string $bankType, string $bvovFilename = ''): array
    {
        $virements = [];
        $totalAmount = 0;
        $projectCode = null;
        $transactionDate = null;

        foreach ($payments as $payment) {
            // Get project code and transaction date from first payment
            if ($projectCode === null && $payment->project) {
                $projectCode = $payment->project->project_code ?? 'N/A';
            }
            if ($transactionDate === null && $payment->transaction_date) {
                $transactionDate = $payment->transaction_date;
            }
            
            // Handle invoice payments
            if ($payment->payment_type === Payment::TYPE_INVOICE || $payment->invoices->isNotEmpty()) {
                foreach ($payment->invoices as $invoice) {
                    $supplier = $invoice->purchaseOrder?->supplier;
                    if (!$supplier) continue;

                    $amount = $invoice->pivot->amount ?? $invoice->total ?? 0;
                    $totalAmount += $amount;

                    // Extract bank code from supplier RIB (first 3 digits)
                    $supplierBankCode = $supplier->rib ? substr(preg_replace('/[^0-9]/', '', $supplier->rib), 0, 3) : '';

                    $virements[] = [
                        'beneficiary_name' => $supplier->company_name ?? $supplier->trade_name ?? 'N/A',
                        'beneficiary_rib' => $supplier->rib ?? 'N/A',
                        'reference' => $invoice->invoice_number,
                        'amount' => $amount,
                        'transaction_date' => $payment->transaction_date,
                        'bank_code' => $this->getBankAbbreviation($supplierBankCode),
                        'motif' => $bankType === 'TGR' 
                            ? $invoice->invoice_number . '0000000'
                            : $invoice->invoice_number,
                        'type' => 'invoice',
                    ];
                }
            }
            
            // Handle expense report payments
            if ($payment->payment_type === Payment::TYPE_EXPENSE_REPORT || $payment->expenseReports->isNotEmpty()) {
                foreach ($payment->expenseReports as $expenseReport) {
                    $collaborator = $expenseReport->createdBy;
                    $beneficiaryName = $collaborator 
                        ? ($collaborator->first_name . ' ' . $collaborator->last_name)
                        : 'FONDATION ZAKOURA';
                    $beneficiaryRib = $collaborator->rib ?? '';
                    
                    $amount = $expenseReport->pivot->amount ?? $expenseReport->total_amount ?? 0;
                    $totalAmount += $amount;

                    // Extract bank code from collaborator RIB (first 3 digits)
                    $collaboratorBankCode = $beneficiaryRib ? substr(preg_replace('/[^0-9]/', '', $beneficiaryRib), 0, 3) : '';

                    $virements[] = [
                        'beneficiary_name' => $beneficiaryName,
                        'beneficiary_rib' => $beneficiaryRib ?: 'N/A',
                        'reference' => 'NDF-' . $expenseReport->id,
                        'amount' => $amount,
                        'transaction_date' => $payment->transaction_date,
                        'bank_code' => $this->getBankAbbreviation($collaboratorBankCode),
                        'motif' => $bankType === 'TGR' 
                            ? 'NDF-' . $expenseReport->id . '0000000'
                            : 'NDF-' . $expenseReport->id,
                        'type' => 'expense_report',
                    ];
                }
            }
        }

        // Format transaction date or use current date as fallback
        $formattedDate = $transactionDate 
            ? Carbon::parse($transactionDate)->locale('fr')->isoFormat('D MMMM YYYY')
            : Carbon::now()->locale('fr')->isoFormat('D MMMM YYYY');

        // Extract account number from RIB (16 digits, positions 7-22)
        $ribParts = $this->parseRib($bankAccount->rib_iban);
        $accountNumber = $ribParts['account'];

        return [
            'bank_type' => $bankType,
            'bank_name' => $bankType === 'TGR' ? 'Tresorerie Generale du Royaume' : 'Societe Generale',
            'company_name' => 'FONDATION ZAKOURA',
            'company_rib' => $bankAccount->rib_iban,
            'company_account_number' => $accountNumber,
            'account_title' => $bankAccount->account_title,
            'emission_date' => $formattedDate,
            'virements' => $virements,
            'total_amount' => $totalAmount,
            'total_count' => count($virements),
            'amount_in_letters' => $this->convertAmountToWords($totalAmount),
            'bvov_filename' => $bvovFilename,
            'project_code' => $projectCode,
        ];
    }

    /**
     * Get bank abbreviation from bank code
     */
    protected function getBankAbbreviation(string $bankCode): string
    {
        return match($bankCode) {
            '007' => 'ATW', // Attijariwafa Bank
            '011' => 'BMCE',
            '013' => 'BMCI',
            '021' => 'CDM',
            '022' => 'SG',  // Société Générale
            '190' => 'BP',  // Banque Populaire
            '310' => 'TGR', // Trésorerie Générale
            '350' => 'CIH',
            default => $bankCode,
        };
    }

    /**
     * Convert amount to French words
     */
    protected function convertAmountToWords(float $amount): string
    {
        $units = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf', 'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'];
        $tens = ['', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante', 'quatre-vingt', 'quatre-vingt'];

        $integerPart = (int) $amount;
        $decimalPart = round(($amount - $integerPart) * 100);

        $result = $this->numberToWords($integerPart) . ' dirhams';
        
        if ($decimalPart > 0) {
            $result .= ' et ' . $this->numberToWords((int)$decimalPart) . ' centimes';
        }

        return ucfirst($result);
    }

    /**
     * Convert integer number to French words
     */
    protected function numberToWords(int $number): string
    {
        if ($number == 0) return 'zero';
        if ($number < 0) return 'moins ' . $this->numberToWords(abs($number));

        $units = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf', 'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'];
        $tens = ['', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante', 'quatre-vingt', 'quatre-vingt'];

        $words = '';

        if ($number >= 1000000) {
            $millions = (int) ($number / 1000000);
            $words .= ($millions == 1 ? 'un million' : $this->numberToWords($millions) . ' millions');
            $number %= 1000000;
            if ($number > 0) $words .= ' ';
        }

        if ($number >= 1000) {
            $thousands = (int) ($number / 1000);
            $words .= ($thousands == 1 ? 'mille' : $this->numberToWords($thousands) . ' mille');
            $number %= 1000;
            if ($number > 0) $words .= ' ';
        }

        if ($number >= 100) {
            $hundreds = (int) ($number / 100);
            $words .= ($hundreds == 1 ? 'cent' : $units[$hundreds] . ' cent');
            $number %= 100;
            if ($number > 0) $words .= ' ';
        }

        if ($number >= 20) {
            $tenIndex = (int) ($number / 10);
            $unit = $number % 10;
            
            if ($tenIndex == 7 || $tenIndex == 9) {
                // 70-79 and 90-99
                $words .= $tens[$tenIndex];
                if ($tenIndex == 7 && $unit == 1) {
                    $words .= ' et onze';
                } elseif ($tenIndex == 9 && $unit == 1) {
                    $words .= '-onze';
                } else {
                    $words .= '-' . $units[10 + $unit];
                }
            } else {
                $words .= $tens[$tenIndex];
                if ($unit == 1 && $tenIndex != 8) {
                    $words .= ' et un';
                } elseif ($unit > 0) {
                    $words .= '-' . $units[$unit];
                } elseif ($tenIndex == 8) {
                    $words .= 's'; // quatre-vingts
                }
            }
        } elseif ($number > 0) {
            $words .= $units[$number];
        }

        return $words;
    }

    /**
     * Generate filename following convention: bvov9910030000YYMMJJ000N.unl
     */
    protected function generateFilename(): string
    {
        $date = Carbon::now()->format('ymd');
        $sequence = $this->getNextSequence();
        
        return sprintf('bvov9910030000%s%04d.unl', $date, $sequence);
    }

    /**
     * Get next sequence number for the day
     */
    protected function getNextSequence(): int
    {
        $date = Carbon::now()->format('ymd');
        $pattern = 'etebac/bvov9910030000' . $date . '*.unl';
        $files = Storage::disk('local')->files('etebac');
        
        $todayFiles = array_filter($files, function($file) use ($date) {
            return strpos($file, $date) !== false;
        });

        return count($todayFiles) + 1;
    }

    /**
     * Format amount for ETEBAC (in centimes, no decimals)
     */
    protected function formatAmount(float $amount): string
    {
        return str_pad((int)($amount * 100), 16, '0', STR_PAD_LEFT);
    }

    /**
     * Clean RIB/IBAN (remove spaces and special characters)
     */
    protected function cleanRib(string $rib): string
    {
        return preg_replace('/[^0-9]/', '', $rib);
    }
}
