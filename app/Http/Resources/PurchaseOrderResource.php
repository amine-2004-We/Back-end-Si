<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'po_number' => $this->po_number,
            
            'supplier_company_name' => $this->supplier?->company_name ?? null,
            'supplier_id' => $this->supplier_id,
            
            'quote_id' => $this->quote_id,
            'quote_number' => $this->whenLoaded('quote', function() {
                return $this->quote?->quote_number;
            }, $this->quote_id),    
            
            'issuer_name' => $this->issuer?->name ?? null,
            'issuer_id' => $this->issuer_id,
            
            'subject' => $this->subject,
            'issue_date' => $this->issue_date,
            'total_amount_ttc' => $this->total_amount_ttc,
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'delivery_lead_time_days' => $this->delivery_lead_time_days,
            'terms_file_path' => $this->terms_file_path,
            'status' => $this->status,
            'validated_by_procurement_manager' => $this->validated_by_procurement_manager,
            'validated_by_controlling' => $this->validated_by_controlling,
            'validated_by_board' => $this->validated_by_board,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
            'updated_at' => $this->updated_at,
            'products' => $this->whenLoaded('products', function () {
                return $this->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'product_name' => $product->product?->name ?? null,
                        'quantity' => $product->quantity,
                        'unit_price_ttc' => $product->unit_price_ttc,
                        'total_price_ttc' => $product->total_price_ttc,
                    ];
                });
            }, []),
            'quote' => $this->whenLoaded('quote'),
            'supplier' => $this->whenLoaded('supplier'),
            'issuer' => $this->whenLoaded('issuer'),
             'purchase_request_id' => $this->purchase_request_id,
         
    
            

            
            
            
            'purchase_request' => $this->whenLoaded('purchaseRequest', function () {
                $pr = $this->purchaseRequest;
                if (!$pr) return null;
                return [
                    'id' => $pr->id,
                    'reference' => $pr->reference ?? $pr->code ?? null,
                    'subject' => $pr->subject ?? null,
                    'status' => $pr->status,
                ];
            }, null),
        ];
    }
}