<?php
/**
 * FeeSettings Model
 */

namespace App\Models;

class FeeSettings {
    protected $table = 'fee_settings';
    protected $db;
    
    public function __construct() {
        if (class_exists('\Illuminate\Support\Facades\DB') && \Illuminate\Support\Facades\DB::getFacadeRoot()) {
            $this->db = \Illuminate\Support\Facades\DB::connection()->getPdo();
        } elseif (function_exists('getDBConnection')) {
            $this->db = getDBConnection();
        }
    }
    
    /**
     * Get settings for a tenant
     */
    public function getByTenant($tenantId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? $result : null;
    }
    
    /**
     * Create default settings for a tenant
     */
    public function createDefault($tenantId) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (tenant_id, invoice_prefix, receipt_prefix, next_invoice_number, next_receipt_number) VALUES (?, 'INV', 'RCP', 1, 1)");
        $stmt->execute([$tenantId]);
        return $this->getByTenant($tenantId);
    }
    
    /**
     * Update settings
     */
    public function update($tenantId, $data) {
        // Normally, this is a placeholder. Implementing basic dynamic update for demonstration.
        // It assumes $data holds the fields.
        $fields = [];
        $values = [];
        foreach($data as $key => $val) {
            $fields[] = "$key = ?";
            $values[] = $val;
        }
        $values[] = $tenantId;
        
        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE tenant_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute($values);

        return $this->getByTenant($tenantId);
    }

    /**
     * Increment next invoice/receipt number atomatically
     */
    public function incrementNumber($tenantId, $type = 'invoice') {
        $column = $type === 'invoice' ? 'next_invoice_number' : 'next_receipt_number';
        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$column} = {$column} + 1 WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        return true;
    }
}
