<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table            = 'customers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'start_date_coverage',
        'end_date_coverage',
        'coverage',
        'price',
        'type',
        'is_risk_banjir',
        'is_risk_gempa',
        'created_at',
        'updated_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'is_risk_banjir' => 'bool',
        'is_risk_gempa'  => 'bool',
        'price'          => 'float',
        'type'           => 'integer'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    private const COVERAGETYPES = [
        'Comprehensive' => 1,
        'Total Loss Only' => 2,
    ];

    public function getCoverages()
    {
        return self::COVERAGETYPES;
    }

    public function getCoverageType($type)
    {
        return array_search($type, self::COVERAGETYPES);
    }
}
