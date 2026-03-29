<?php

namespace App\Repositories;
use App\Models\DeliveryOrder;
use Illuminate\Pagination\LengthAwarePaginator;

class DeliveryOrderRepository
{

    public function getFilteredAll(array $params): LengthAwarePaginator
    {
         if (!empty($params['withTrashed']) && $params['withTrashed'] == 'true') {
            $query = DeliveryOrder::onlyTrashed();
        } else {
            $query = DeliveryOrder::query();
        }

        if (!empty($params['search'])) {
            $searchTerm = $params['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'ilike', '%' . $searchTerm . '%')
                  ->orWhere('description', 'ilike', '%' . $searchTerm . '%');
            });
        }
        if (!empty($params['filter']) && is_array($params['filter'])) {
            foreach ($params['filter'] as $key => $value) {
                $query->where($key, $value);
            }
        }

        if (!empty($params['sort_by'])) {
            $direction = !empty($params['sort_direction']) && in_array(strtolower($params['sort_direction']), ['asc', 'desc'])
                ? $params['sort_direction']
                : 'asc';
            $query->orderBy($params['sort_by'], $direction);
        }
         else{
            $query->orderBy('created_at', 'desc'); 
        }

        $perPage = !empty($params['per_page']) ? (int) $params['per_page'] : 10;

        return $query->paginate($perPage);
    }
    public function create(array $data): DeliveryOrder
    {
        // Le champ delivery_request_id peut maintenant être inclus dans $data
        return DeliveryOrder::create($data);
    }

    public function findById(int $id): ?DeliveryOrder
    {
        return DeliveryOrder::find($id);
    }
    public function update(int $id, array $data): ?DeliveryOrder
    {
        $deliveryOrder = DeliveryOrder::withTrashed()->find($id);
        if ($deliveryOrder) {
            $deliveryOrder->update($data);
        }
        return $deliveryOrder;
    }

    public function delete(int $id): bool
    {
        $deliveryOrder = DeliveryOrder::withoutTrashed()->find($id);
        if ($deliveryOrder) {
            return $deliveryOrder->delete();
        }
        return false;
    }

    public function restore(int $id): bool
    {
        $deliveryOrder = DeliveryOrder::onlyTrashed()->find($id);
        if ($deliveryOrder) {
            return $deliveryOrder->restore();
        }
        return false;
    }

    public function bulkDelete(array $ids): int
    {
        return DeliveryOrder::whereIn('id', $ids)->delete();
    }

    


}