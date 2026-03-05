<?php
/**
 * User Model
 * Authentication and user management
 */

namespace App\Models;

class User {
    protected $table = 'users';
    protected $db;
    
    public function __construct() {
        if (class_exists('\Illuminate\Support\Facades\DB') && \Illuminate\Support\Facades\DB::getFacadeRoot()) {
            $this->db = \Illuminate\Support\Facades\DB::connection()->getPdo();
        } elseif (function_exists('getDBConnection')) {
            $this->db = getDBConnection();
        }
    }
    
    /**
     * Get all users
     */
    public function all() {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find user by ID
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? $result : null;
    }
    
    /**
     * Find user by email
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? $result : null;
    }
    
    /**
     * Create new user
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} (tenant_id, role, email, password_hash, name, phone, status, two_fa_enabled) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $data['tenant_id'] ?? null,
            $data['role'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            $data['name'],
            $data['phone'] ?? null,
            $data['status'] ?? 'active',
            $data['two_fa_enabled'] ?? 0
        ]);
        
        $userId = $this->db->lastInsertId();
        // MARIADB 12 FIX: Do NOT read newly inserted row within the same transaction.
        // Build result array from input data instead to avoid "Record has changed since last read" error.
        return [
            'id' => (int)$userId,
            'tenant_id' => $data['tenant_id'] ?? null,
            'role' => $data['role'],
            'email' => $data['email'],
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'] ?? 'active',
        ];
    }
    
    /**
     * Update user
     */
    public function update($id, $data) {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            unset($data['password']);
        }
        
        $fields = [];
        $values = [];
        foreach($data as $key => $val) {
            $fields[] = "$key = ?";
            $values[] = $val;
        }
        $values[] = $id;
        
        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute($values);
        
        return $this->find($id);
    }
    
    /**
     * Get users by role
     */
    public function getByRole($role) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE role = ? ORDER BY name");
        $stmt->execute([$role]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get users by tenant
     */
    public function getByTenant($tenantId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE tenant_id = ? ORDER BY name");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Verify password
     */
    public function verifyPassword($email, $password) {
        $user = $this->findByEmail($email);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        
        return null;
    }
    
    /**
     * Update last login
     */
    public function updateLastLogin($id) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET last_login_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Record Failed Login Attempt
     */
    public function recordFailedLogin($userId, $ipAddress) {
        $stmt = $this->db->prepare("INSERT INTO failed_logins (user_id, ip_address) VALUES (?, ?)");
        return $stmt->execute([$userId, $ipAddress]);
    }
}
