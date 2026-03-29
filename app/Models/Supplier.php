<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
   
    protected $table = 'suppliers';
    use SoftDeletes;

    /**
     *
     * @var array
     */
       protected $fillable = [
        'company_name',
        'trade_name',
        'supplier_type', 
        'business_sector',
        'address',
        'city',
        'country',
        'phone',
        'email',
        'rib',
        'contact_person',
        'contact_person_role',
        'legal_status',
        'tax_id',
        'commercial_register_number',
        'supporting_documents',
        'bank_account_id',
    ];

    /**
     *
     * @var array
     */
    protected $casts = [
        'supporting_documents' => 'array',
    ];

    /**
     * Get the contact people for this supplier.
     */
    public function contactPeople()
    {
        return $this->hasMany(ContactPerson::class, 'supplier_id');
    }

  
}
