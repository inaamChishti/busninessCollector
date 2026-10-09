<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyLead extends Model
{
    protected $table = 'company_leads';

    protected $fillable = [
        'company_number', 'company_name', 'company_status', 'company_type',
        'address_line_1', 'address_line_2', 'locality', 'postal_code', 'country',
        'sic_codes', 'date_of_creation', 'category', 'search_location',
        'website', 'email', 'source', 'fetched_at',
    ];

    protected $casts = [
        'date_of_creation' => 'date',
        'fetched_at'       => 'datetime',
    ];

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address_line_1,
            $this->address_line_2,
            $this->locality,
            $this->postal_code,
        ])->filter()->implode(', ');
    }
}
