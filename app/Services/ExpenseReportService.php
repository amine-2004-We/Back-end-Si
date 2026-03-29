<?php

namespace App\Services;

use App\Models\ExpenseReport;
use App\Models\ExpenseLine;
use App\Repositories\ExpenseReportRepository;

class ExpenseReportService
{
    public ExpenseReportRepository $repository;

    public function __construct(ExpenseReportRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array $data
     * @param array $lines
     * @return ExpenseReport
     */
    public function createWithLines(array $data, array $lines): ExpenseReport
    {
        $report = $this->repository->create($data);

        foreach ($lines as $line) {
            $line['amount_manager'] = $line['manager_amount'] ?? $line['amount'] ?? 0;
            $line['amount_finance'] = $line['finance_amount'] ?? $line['amount'] ?? 0;

            unset($line['manager_amount'], $line['finance_amount']);

            $report->expenseLines()->create($line);
        }


        return $report;
    }


    /**
     * Changer le statut
     */
    public function changeStatus(ExpenseReport $report, string $status): ExpenseReport
    {
        $report->update(['status' => $status]);
        return $report;
    }
}
