<?php

namespace App\Services;

use App\Http\Requests\UpdateQuoteAttachmentRequest;
use App\Models\Quote;
use App\Models\User;
use App\Repositories\QuoteRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuoteService
{
    protected QuoteRepository $quoteRepository;

    public function __construct(QuoteRepository $quoteRepository)
    {
        $this->quoteRepository = $quoteRepository;
    }

    public function getAll(Request $request): mixed
    {
        $filters = $request->only([
            'purchase_request_id',
            'supplier_id',
            'status',
            'from_date',
            'to_date',
            'search',
            'is_active',
            'quote_number',
            'per_page',
        ]);

        return $this->quoteRepository->withFilters($filters);
    }

    public function getAllWithoutPagination(): mixed
    {
        return $this->quoteRepository->all();
    }

    public function show(int $id): mixed
    {
        return $this->quoteRepository->find($id);
    }

      public function create(array $data): Quote
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $quote = $this->quoteRepository->create($data);

            if (!empty($items)) {
                $this->quoteRepository->syncItems($quote, $items);
            }

            $this->recomputeTotals($quote, $data);

            return $quote->load(['items', 'supplier', 'purchaseList']);
        });
    }

    public function update(array $data, Quote $quote): Quote
    {
        return DB::transaction(function () use ($data, $quote) {
            $quote->fill($data);
            $quote->save();

            if (array_key_exists('items', $data) && is_array($data['items'])) {
                $incoming = collect($data['items'])
                    ->filter(fn ($it) => isset($it['article_id']))
                    ->mapWithKeys(function ($it) {
                        $aid = (int) $it['article_id'];
                        return [$aid => [
                            'article_id'    => $aid,
                            'quantity'      => (float) ($it['quantity'] ?? 0),
                            'unit_price_ht' => (float) ($it['unit_price_ht'] ?? 0),
                            'tva_rate'     => isset($it['tva_rate']) ? (float) $it['tva_rate'] : 0,
                        ]];
                    });

                $existing = $quote->items()->withTrashed()->get()->keyBy('article_id');

                foreach ($existing as $aid => $item) {
                    if ($incoming->has($aid)) {
                        $vals = $incoming->get($aid);
                        $item->quantity      = $vals['quantity'];
                        $item->unit_price_ht = $vals['unit_price_ht'];
                        $item->tva_rate      = $vals['tva_rate'];

                        if (method_exists($item, 'trashed') && $item->trashed()) {
                            $item->restore();
                        }
                        $item->save();

                        $incoming->forget($aid);
                    } else {
                        if (method_exists($item, 'forceDelete')) {
                            $item->forceDelete();
                        } else {
                            $item->delete();
                        }
                    }
                }

                foreach ($incoming as $vals) {
                    $quote->items()->create($vals);
                }
            }

            $vatPercent = null;
            if (array_key_exists('vat_rate', $data) && $data['vat_rate'] !== null) {
                $vr = (float) $data['vat_rate'];
                $vatPercent = ($vr <= 1) ? $vr * 100 : $vr;
            }
            if (array_key_exists('items', $data) || $vatPercent !== null) {
                $quote->recomputeTotals($vatPercent);
                $quote->save();
            }

            return $quote;
        });
    }


    public function delete($quote)
    {
        return $this->quoteRepository->delete($quote);
    }

    public function bulkDestroy($quotes): int
    {
        foreach ($quotes as $quote) {
            if ($quote->attachment_path) {
                Storage::disk('public')->delete($quote->attachment_path);
            }
        }
        return $this->quoteRepository->bulkDelete($quotes->pluck('id')->toArray());
    }

    public function restore(int $id): Quote
    {
        try {
            return $this->quoteRepository->restore($id)->load(['items', 'supplier', 'purchaseList']);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }

    protected function recomputeTotals(Quote $quote, array $data = []): void
    {
        $ht = (float) $quote->items()->selectRaw('COALESCE(SUM(quantity * unit_price_ht), 0) as ht')->value('ht');

        if (array_key_exists('vat_rate', $data) && $data['vat_rate'] !== null) {
            $vatAmount = round($ht * (float)$data['vat_rate'], 2);
        } elseif (array_key_exists('vat_amount', $data) && $data['vat_amount'] !== null) {
            $vatAmount = (float)$data['vat_amount'];
        } else {
            $vatAmount = (float) ($quote->vat_amount ?? 0);
        }

        $ttc = round($ht + $vatAmount, 2);

        $quote->update([
            'total_amount_ht'  => $ht,
            'vat_amount'       => $vatAmount,
            'total_amount_ttc' => $ttc,
        ]);
    }

    public function findByIds(array $ids)
    {
        return Quote::whereIn('id', $ids)->get();
    }

    public function updateAttachment( UpdateQuoteAttachmentRequest $request,Quote $quote): void
    {
        try{
            $quote->update([
                'attachment_path' => $request->file('attachment')->store('quotes', 'public'),
            ]);
        }catch (Exception $e){
            abort(409, $e->getMessage());
        }
    }


    // private function notifyUser(User $user, int $quoteId)
    // {
    //     return;
    // }
}
