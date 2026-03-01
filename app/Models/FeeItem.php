<?php
/**
 * FeeItem Model
 */

namespace App\Models;

class FeeItem {
    protected $table = 'fee_items';
    private $db;
    
    public function __construct() {
        if (class_exists('\Illuminate\Support\Facades\DB') && \Illuminate\Support\Facades\DB::getFacadeRoot()) {
            $this->db = \Illuminate\Support\Facades\DB::connection()->getPdo();
        } elseif (function_exists('getDBConnection')) {
            $this->db = getDBConnection();
        }
    }
    
    /**
     * Find fee item by ID
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Get active fee items for a course
     */
    public function getByCourse($courseId, $tenantId) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE course_id = :course_id 
            AND tenant_id = :tenant_id 
            AND is_active = 1 
            AND deleted_at IS NULL
        ");
        $stmt->execute(['course_id' => $courseId, 'tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Create new fee item
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} 
            (tenant_id, course_id, name, type, amount, installments, late_fine_per_day, is_active)
            VALUES 
            (:tenant_id, :course_id, :name, :type, :amount, :installments, :late_fine_per_day, :is_active)
        ");
        
        $stmt->execute([
            'tenant_id' => $data['tenant_id'],
            'course_id' => $data['course_id'],
            'name' => $data['name'],
            'type' => $data['type'] ?? 'monthly',
            'amount' => $data['amount'],
            'installments' => $data['installments'] ?? 1,
            'late_fine_per_day' => $data['late_fine_per_day'] ?? 0.00,
            'is_active' => $data['is_active'] ?? 1
        ]);
        
        return $this->find($this->db->lastInsertId());
    }
    
    /**
     * Update fee item
     */
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[$key] = $value;
        }
        
        if (empty($fields)) return false;
        
        $stmt = $this->db->prepare("UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id");
        $stmt->execute($params);
        
        return $this->find($id);
    }
}
