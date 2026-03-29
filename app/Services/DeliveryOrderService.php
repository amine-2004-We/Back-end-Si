<?php

namespace App\Services;

use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Repositories\DeliveryOrderRepository;
use Illuminate\Support\Facades\DB;
use Exception;

class DeliveryOrderService
{
    protected $deliveryOrderRepository;

    public function __construct(DeliveryOrderRepository $deliveryOrderRepository)
    {
        $this->deliveryOrderRepository = $deliveryOrderRepository;
    }

    public function createDeliveryOrder(array $data, array $items = []): DeliveryOrder
    {
        return DB::transaction(function () use ($data, $items) {
            
            if (isset($data['items'])) {
                unset($data['items']);
            }

          
            // $data peut contenir delivery_request_id
            //set created by to the auth user
            $data['created_by'] = auth()->user()->id;
            $deliveryOrder = DeliveryOrder::create($data);

       
            $rows = [];
            foreach ($items as $it) {
                $rows[] = [
                    'delivery_order_id' => $deliveryOrder->id,
                    'article_id' => $it['article_id'] ?? null,
                    'expected_quantity' => $it['expected_quantity'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($rows)) {
                DeliveryOrderItem::insert($rows);
            }

        
            return $deliveryOrder->load('items');
        });
    }

    public function getDeliveryOrders(array $params)
    {
        return $this->deliveryOrderRepository->getFilteredAll($params);
    }

   public function updateDeliveryOrder(int $id, array $data): ?DeliveryOrder
    {
        $deliveryOrder = $this->deliveryOrderRepository->findById($id);
        if (!$deliveryOrder) {
            return null;
        }

        return DB::transaction(function () use ($deliveryOrder, $data) {
            
            $itemsData = [];
         
            if (isset($data['items'])) {
                $itemsData = $data['items'];
                
                unset($data['items']); 
            }

           
            $deliveryOrder->update($data);

            
            if (!empty($itemsData) && is_array($itemsData)) {
                
                
                DeliveryOrderItem::where('delivery_order_id', $deliveryOrder->id)->delete();

               
                $rows = [];
                foreach ($itemsData as $it) {
                    $rows[] = [
                        'delivery_order_id' => $deliveryOrder->id,
                        'article_id' => $it['article_id'] ?? null,
                        'expected_quantity' => $it['expected_quantity'] ?? 0,
                        
                        
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                
                if (!empty($rows)) {
                    DeliveryOrderItem::insert($rows);
                }
            }

           
            return $deliveryOrder->load('items');
        });
        
    }

    public function findDeliveryOrderById(int $id): ?DeliveryOrder
    {
        return $this->deliveryOrderRepository->findById($id);
    }

    public function deleteDeliveryOrder(int $id): bool
    {
        $deliveryOrder = $this->deliveryOrderRepository->findById($id);
        if ($deliveryOrder) {
            return $deliveryOrder->delete();
        }
        return false;
    }

    public function restoreDeliveryOrder(int $id): bool
    {
        $deliveryOrder = DeliveryOrder::onlyTrashed()->find($id);
        if ($deliveryOrder) {
            return $deliveryOrder->restore();
        }
        return false;
    }

    public function bulkDeleteDeliveryOrders(array $ids): int
    {
        return $this->deliveryOrderRepository->bulkDelete($ids);
    }
}