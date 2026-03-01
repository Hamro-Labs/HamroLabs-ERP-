<?php
/**
 * Tenant Model
 * Root record for multi-tenancy
 */

namespace App\Models;

require_once base_path('config.php');

class Tenant {
    protected $table = 'tenants';
    protected $primaryKey = 'id';
    
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    /**
     * Get all tenants
     */
    public function all() {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
    
    /**
     * Find tenant by ID
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Find tenant by subdomain
     */
    public function findBySubdomain($subdomain) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE subdomain = :subdomain LIMIT 1");
        $stmt->execute(['subdomain' => $subdomain]);
        return $stmt->fetch();
    }
    
    /**
     * Create new tenant
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (name, nepali_name, subdomain, brand_color, tagline, phone, address, province, plan, status, student_limit, sms_credits, trial_ends_at)
            VALUES (:name, :nepali_name, :subdomain, :brand_color, :tagline, :phone, :address, :province, :plan, :status, :student_limit, :sms_credits, :trial_ends_at)
        ");
        
        $stmt->execute([
            'name' => $data['name'],
            'nepali_name' => $data['nepali_name'] ?? null,
            'subdomain' => $data['subdomain'],
            'brand_color' => $data['brand_color'] ?? '#009E7E',
            'tagline' => $data['tagline'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'province' => $data['province'] ?? null,
            'plan' => $data['plan'] ?? 'starter',
            'status' => $data['status'] ?? 'trial',
            'student_limit' => $data['student_limit'] ?? 100,
            'sms_credits' => $data['sms_credits'] ?? 500,
            'trial_ends_at' => $data['trial_ends_at'] ?? date('Y-m-d H:i:s', strtotime('+60 days'))
        ]);
        
        return $this->find($this->db->lastInsertId());
    }
    
    /**
     * Update tenant
     */
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[$key] = $value;
        }
        
        $stmt = $this->db->prepare("UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id");
        $stmt->execute($params);
        
        return $this->find($id);
    }
    
    /**
     * Delete tenant (soft delete)
     */
    public function delete($id) {
        return $this->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
    }
    
    /**
     * Get active tenants count
     */
    public function countActive() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'active'");
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get tenants by status
     */
    public function getByStatus($status) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE status = :status ORDER BY created_at DESC");
        $stmt->execute(['status' => $status]);
        return $stmt->fetchAll();
    }
}
