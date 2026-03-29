<?php

namespace App\Services;

use App\Models\PurchaseList;
use App\Models\Quote;
use App\Repositories\QuoteRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseListService
{
    public function create(array $data): PurchaseList
    {
        $year = Carbon::now()->year;
        // Use database transaction with locking to prevent race conditions
        return DB::transaction(function () use ($data, $year) {
            // Lock the last row to ensure sequential ID generation
            $lastPurchaseList = PurchaseList::withoutTrashed()
                ->where('purchase_list_id', 'like', "LA-{$year}-%")
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();
            
            if ($lastPurchaseList) {
                preg_match('/LA-\d{4}-(\d+)/', $lastPurchaseList->purchase_list_id, $matches);
                $number = str_pad((int)$matches[1] + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $number = '0001';
            }
            
            $data['purchase_list_id'] = "LA-{$year}-{$number}";

            return PurchaseList::create($data);
        });
    }
}
