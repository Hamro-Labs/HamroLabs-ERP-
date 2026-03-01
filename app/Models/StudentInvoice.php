<?php
/**
 * StudentInvoice Model
 */

namespace App\Models;

class StudentInvoice {
    protected $table = 'student_invoices';
    private $db;
    
    public function __construct() {
        if (class_exists('\Illuminate\Support\Facades\DB') && \Illuminate\Support\Facades\DB::getFacadeRoot()) {
            $this->db = \Illuminate\Support\Facades\DB::connection()->getPdo();
        } elseif (function_exists('getDBConnection')) {
            $this->db = getDBConnection();
        }
    }
    
    /**
     * Find invoice by ID
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Get student invoices
     */
    public function getByStudent($studentId, $tenantId) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE student_id = :student_id 
            AND tenant_id = :tenant_id 
            ORDER BY invoice_date DESC
        ");
        $stmt->execute(['student_id' => $studentId, 'tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Create new invoice
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} 
            (invoice_number, tenant_id, student_id, batch_id, academic_year, invoice_date, due_date, total_amount, status, notes)
            VALUES 
            (:invoice_number, :tenant_id, :student_id, :batch_id, :academic_year, :invoice_date, :due_date, :total_amount, :status, :notes)
        ");
        
        $stmt->execute([
            'invoice_number' => $data['invoice_number'],
            'tenant_id' => $data['tenant_id'],
            'student_id' => $data['student_id'],
            'batch_id' => $data['batch_id'] ?? null,
            'academic_year' => $data['academic_year'] ?? null,
            'invoice_date' => $data['invoice_date'] ?? date('Y-m-d'),
            'due_date' => $data['due_date'],
            'total_amount' => $data['total_amount'],
            'status' => $data['status'] ?? 'draft',
            'notes' => $data['notes'] ?? null
        ]);
        
        return $this->find($this->db->lastInsertId());
    }
    
    /**
     * Update invoice status or paid amount
     */
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[$key] = $value;
        }
        
        if (empty($fields)) return false;
        
        $stmt = $this->db->prepare("UPDATE {$this->table} SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->execute($params);
        
        return $this->find($id);
    }
}
