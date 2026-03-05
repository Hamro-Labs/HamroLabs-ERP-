# HamroLabs Academic ERP - Redis & Distributed Caching Architecture Strategy

## Executive Summary

This document presents a comprehensive Redis and distributed caching implementation strategy for the HamroLabs Academic ERP system. The strategy is designed to optimize system throughput, reduce database load by 60-80%, enable horizontal scalability to support 1,000+ tenants, and maintain sub-millisecond response times across distributed environments.

**Target Performance Metrics:**
- Page load time (first visit): < 3 seconds on Nepal 3G
- API response time (p95): < 500ms (target: < 200ms with caching)
- Database query reduction: 60-80% for hot data paths
- Cache hit rate target: > 85%
- Horizontal scaling capability: 10 → 1,000+ tenants

---

## Table of Contents

1. [Cache Layer Topology Design](#1-cache-layer-topology-design)
2. [Data Partitioning Strategies](#2-data-partitioning-strategies)
3. [Eviction Policies](#3-eviction-policies)
4. [Cache Invalidation Mechanisms](#4-cache-invalidation-mechanisms)
5. [Serialization Protocols](#5-serialization-protocols)
6. [Cluster Configuration & Auto-Failover](#6-cluster-configuration--auto-failover)
7. [Memory Optimization Techniques](#7-memory-optimization-techniques)
8. [Integration Patterns](#8-integration-patterns)
9. [Hot Data Pattern Identification](#9-hot-data-pattern-identification)
10. [TTL Management](#10-ttl-management)
11. [Cache Warming Procedures](#11-cache-warming-procedures)
12. [Observability Framework](#12-observability-framework)
13. [Security Implementation](#13-security-implementation)
14. [Circuit Breakers](#14-circuit-breakers)
15. [Consistency Models](#15-consistency-models)
16. [Concurrency Handling](#16-concurrency-handling)
17. [Cache Stampede Mitigation](#17-cache-stampede-mitigation)
18. [Capacity Planning](#18-capacity-planning)
19. [Implementation Roadmap](#19-implementation-roadmap)

---

## 1. Cache Layer Topology Design

### 1.1 Multi-Tier Cache Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           CLIENT LAYER                                       │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │   Browser   │  │  PWA App    │  │  Mobile App │  │  API Client │         │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘         │
└─────────────────────────────────────────────────────────────────────────────┘
                                       │
┌─────────────────────────────────────────────────────────────────────────────┐
│                         CDN / EDGE LAYER (Cloudflare)                        │
│  • Static asset caching                                                      │
│  • DDoS protection                                                           │
│  • Nepal edge nodes for <50ms latency                                        │
└─────────────────────────────────────────────────────────────────────────────┘
                                       │
┌─────────────────────────────────────────────────────────────────────────────┐
│                      APPLICATION LAYER (PHP/Laravel)                         │
│  ┌─────────────────────────────────────────────────────────────────────┐    │
│  │  Local In-Memory Cache (APCu) - L1                                  │    │
│  │  • Request-scoped data                                              │    │
│  │  • Tenant context objects                                           │    │
│  │  • User session fragments                                           │    │
│  │  • TTL: 0-60 seconds                                                │    │
│  └─────────────────────────────────────────────────────────────────────┘    │
│                                      │                                      │
│  ┌─────────────────────────────────────────────────────────────────────┐    │
│  │  Laravel Application Cache - L2                                     │    │
│  │  • Config data, feature flags                                       │    │
│  │  • Route cache, view cache                                          │    │
│  └─────────────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────────────┘
                                       │
┌─────────────────────────────────────────────────────────────────────────────┐
│                      REDIS CLUSTER LAYER - L3                                │
│  ┌─────────────────────────────────────────────────────────────────────┐    │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐                  │    │
│  │  │ Redis Node  │  │ Redis Node  │  │ Redis Node  │  (Master nodes)  │    │
│  │  │  (Master)   │  │  (Master)   │  │  (Master)   │                  │    │
│  │  │  Port 6379  │  │  Port 6380  │  │  Port 6381  │                  │    │
│  │  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘                  │    │
│  │         │                │                │                         │    │
│  │  ┌──────┴──────┐  ┌──────┴──────┐  ┌──────┴──────┐                  │    │
│  │  │ Redis Node  │  │ Redis Node  │  │ Redis Node  │  (Replica nodes) │    │
│  │  │  (Replica)  │  │  (Replica)  │  │  (Replica)  │                  │    │
│  │  └─────────────┘  └─────────────┘  └─────────────┘                  │    │
│  └─────────────────────────────────────────────────────────────────────┘    │
│                              │                                              │
│  Data Shards: 0-5460      5461-10922      10923-16383                       │
└─────────────────────────────────────────────────────────────────────────────┘
                                       │
┌─────────────────────────────────────────────────────────────────────────────┐
│                      PERSISTENCE LAYER                                       │
│  ┌─────────────────────────────────────────────────────────────────────┐    │
│  │  MySQL 8.0 (Primary)                                                │    │
│  │  • Write operations                                                 │    │
│  │  • Transactional data                                               │    │
│  │  • Complex queries                                                  │    │
│  └─────────────────────────────────────────────────────────────────────┘    │
│  ┌─────────────────────────────────────────────────────────────────────┐    │
│  │  MySQL Read Replica (Optional Phase 3)                              │    │
│  │  • Read-heavy reporting queries                                     │    │
│  └─────────────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 1.2 Redis Instance Configuration

| Instance | Port | Purpose | Max Memory | Persistence |
|----------|------|---------|------------|-------------|
| Redis Cache | 6379 | Application data cache | 4GB | RDB every 15 min |
| Redis Session | 6380 | User session storage | 2GB | AOF + RDB |
| Redis Queue | 6381 | Laravel job queues | 2GB | AOF every sec |
| Redis Pub/Sub | 6382 | Real-time events | 512MB | No persistence |

### 1.3 Multi-Tenancy Key Isolation

All Redis keys MUST be prefixed with tenant identifier to prevent cross-tenant data leakage:

```
Key Format: {tenant}:{category}:{entity}:{identifier}

Examples:
- tenant:42:student:profile:12345
- tenant:42:dashboard:stats:daily
- tenant:42:attendance:batch:7:date:2026-03-05
- tenant:42:fee:summary:student:12345
```

---

## 2. Data Partitioning Strategies
n
### 2.1 Logical Partitioning by Data Type

```php
// app/Services/Cache/CachePartitioner.php

class CachePartitioner
{
    // Partition 1: Hot Entity Cache (Frequently accessed single records)
    const PARTITION_HOT = 'hot';
    
    // Partition 2: List Cache (Collections, paginated results)
    const PARTITION_LIST = 'list';
    
    // Partition 3: Aggregate Cache (Dashboard stats, reports)
    const PARTITION_AGGREGATE = 'agg';
    
    // Partition 4: Session Cache (User sessions, temporary data)
    const PARTITION_SESSION = 'sess';
    
    // Partition 5: Rate Limiting (API throttling)
    const PARTITION_RATE_LIMIT = 'ratelimit';
    
    // Partition 6: Distributed Locks (Prevent race conditions)
    const PARTITION_LOCK = 'lock';

    public static function key(int $tenantId, string $partition, string $entity, $id): string
    {
        return "tenant:{$tenantId}:{$partition}:{$entity}:{$id}";
    }
}
```

### 2.2 Hash Slot Partitioning (Redis Cluster)

For Redis Cluster deployment, use hash tags to ensure related keys are stored on the same node:

```
{tenant:42}:student:profile:12345
{tenant:42}:student:attendance:12345
{tenant:42}:dashboard:stats
```

This ensures that all data for tenant 42 is co-located, reducing cross-node operations.

### 2.3 Database Sharding Strategy

| Data Category | Sharding Key | Rationale |
|---------------|--------------|-----------|
| Student Data | tenant_id + student_id | Even distribution, data locality |
| Attendance | tenant_id + batch_id + date | Time-series partitioning |
| Fee Records | tenant_id + student_id | Financial data isolation |
| Exam Attempts | tenant_id + exam_id | Exam-time load balancing |

---

## 3. Eviction Policies

### 3.1 Tiered Eviction Strategy

```
┌─────────────────────────────────────────────────────────┐
│                 REDIS MEMORY POOL                        │
│  ┌─────────────────────────────────────────────────┐    │
│  │  VOLATILE-LRU  (70% of memory)                  │    │
│  │  • Keys WITH expiry set                         │    │
│  │  • Least Recently Used evicted first            │    │
│  │  • Application data, stats                      │    │
│  └─────────────────────────────────────────────────┘    │
│  ┌─────────────────────────────────────────────────┐    │
│  │  ALLKEYS-LRU  (20% of memory)                   │    │
│  │  • All keys considered for eviction             │    │
│  │  • Session data, temporary caches               │    │
│  └─────────────────────────────────────────────────┘    │
│  ┌─────────────────────────────────────────────────┐    │
│  │  NO-EVICTION  (10% of memory)                   │    │
│  │  • Critical data only                           │    │
│  │  • Rate limiting counters                       │    │
│  │  • Write fails if full (protected)              │    │
│  └─────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────┘
```

### 3.2 Per-Category TTL and Eviction

| Data Category | Max Memory % | Eviction Policy | Max TTL |
|---------------|--------------|-----------------|---------|
| Student Profiles | 25% | volatile-lru | 1 hour |
| Attendance Records | 20% | volatile-lru | 4 hours |
| Fee Summaries | 15% | volatile-lru | 30 minutes |
| Dashboard Stats | 10% | volatile-lru | 5 minutes |
| Session Data | 15% | allkeys-lru | 8 hours |
| Rate Limiters | 5% | noeviction | 1 hour |
| Query Results | 10% | volatile-lru | 10 minutes |

### 3.3 Configuration

```conf
# redis.conf - Cache Instance
maxmemory 4gb
maxmemory-policy volatile-lru
maxmemory-samples 10

# redis.conf - Session Instance  
maxmemory 2gb
maxmemory-policy allkeys-lru

# redis.conf - Queue Instance
maxmemory 2gb
maxmemory-policy noeviction
```

---

## 4. Cache Invalidation Mechanisms

### 4.1 Invalidation Strategy Matrix

| Data Type | Write Pattern | Invalidation Strategy |
|-----------|---------------|----------------------|
| Student Profile | Infrequent updates | Tag-based + Event-driven |
| Attendance | High write, daily | Time-window + Batch invalidate |
| Fee Records | Transactional | Immediate + Transaction rollback |
| Dashboard Stats | Aggregated | TTL-based + Manual refresh |
| Exam Questions | Read-heavy | Version-based |
| Timetable | Scheduled updates | Time-based + Event-driven |

### 4.2 Tag-Based Invalidation

```php
// app/Services/Cache/CacheInvalidator.php

class CacheInvalidator
{
    private $redis;
    
    public function __construct($redis)
    {
        $this->redis = $redis;
    }
    
    /**
     * Tag-based invalidation for related data
     */
    public function invalidateByTag(string $tag): void
    {
        // Get all keys with this tag
        $keys = $this->redis->sMembers("tags:{$tag}");
        
        if (!empty($keys)) {
            // Delete all tagged keys
            $this->redis->del(...$keys);
            // Remove the tag set
            $this->redis->del("tags:{$tag}");
        }
    }
    
    /**
     * Add a tag to a cache key
     */
    public function tagKey(string $key, array $tags): void
    {
        foreach ($tags as $tag) {
            $this->redis->sAdd("tags:{$tag}", $key);
            // Set expiry on tag set slightly longer than keys
            $this->redis->expire("tags:{$tag}", 86400); // 24 hours
        }
    }
    
    /**
     * Invalidate all data for a tenant
     */
    public function invalidateTenant(int $tenantId): void
    {
        $pattern = "tenant:{$tenantId}:*";
        $keys = $this->redis->scan($pattern);
        
        // Batch delete in groups of 100
        foreach (array_chunk($keys, 100) as $batch) {
            $this->redis->del(...$batch);
        }
    }
    
    /**
     * Invalidate cascade for related entities
     */
    public function invalidateStudentCascade(int $tenantId, int $studentId): void
    {
        $tags = [
            "tenant:{$tenantId}:student:{$studentId}",
            "tenant:{$tenantId}:fees:student:{$studentId}",
            "tenant:{$tenantId}:attendance:student:{$studentId}",
            "tenant:{$tenantId}:dashboard:stats",
        ];
        
        foreach ($tags as $tag) {
            $this->invalidateByTag($tag);
        }
    }
}
```

### 4.3 Event-Driven Invalidation

```php
// app/Events/CacheInvalidationEvent.php

class CacheInvalidationEvent
{
    public const STUDENT_UPDATED = 'cache.student.updated';
    public const FEE_PAID = 'cache.fee.paid';
    public const ATTENDANCE_MARKED = 'cache.attendance.marked';
    public const EXAM_COMPLETED = 'cache.exam.completed';
    public const TENANT_CONFIG_CHANGED = 'cache.tenant.config';
}

// Event listener implementation
class CacheInvalidationListener
{
    public function handle($event, $data)
    {
        $invalidator = new CacheInvalidator($this->redis);
        
        switch ($event) {
            case CacheInvalidationEvent::STUDENT_UPDATED:
                $invalidator->invalidateStudentCascade(
                    $data['tenant_id'], 
                    $data['student_id']
                );
                break;
                
            case CacheInvalidationEvent::FEE_PAID:
                $invalidator->invalidateByTag(
                    "tenant:{$data['tenant_id']}:fees:student:{$data['student_id']}"
                );
                $invalidator->invalidateByTag(
                    "tenant:{$data['tenant_id']}:dashboard:stats"
                );
                break;
                
            case CacheInvalidationEvent::ATTENDANCE_MARKED:
                // Only invalidate specific date range
                $invalidator->invalidateByTag(
                    "tenant:{$data['tenant_id']}:attendance:date:{$data['date']}"
                );
                break;
        }
    }
}
```

### 4.4 Write-Through vs Write-Behind

| Operation | Strategy | Rationale |
|-----------|----------|-----------|
| Student Create | Write-Through | Immediate consistency required |
| Fee Payment | Write-Through | Financial transaction integrity |
| Attendance Bulk | Write-Behind | Performance, eventual consistency OK |
| Dashboard Stats | Write-Behind | Aggregated data, stale OK |
| Session Updates | Write-Through | Always need fresh session data |

---

## 5. Serialization Protocols

### 5.1 Protocol Selection Matrix

| Data Type | Serializer | Compression | Rationale |
|-----------|------------|-------------|-----------|
| Small Objects (<1KB) | JSON | None | Human readable, fast parse |
| Medium Objects (1KB-100KB) | MessagePack | LZ4 | Balance speed/size |
| Large Objects (>100KB) | MessagePack | LZ4/GZIP | Size optimization |
| Binary Data | Raw Binary | None | Direct storage |

### 5.2 Implementation

```php
// app/Services/Cache/CacheSerializer.php

class CacheSerializer
{
    const FORMAT_JSON = 'json';
    const FORMAT_MSGPACK = 'msgpack';
    const FORMAT_IGBINARY = 'igbinary';
    
    private $compressThreshold = 1024; // 1KB
    
    public function serialize($data, string $format = self::FORMAT_MSGPACK): string
    {
        $serialized = match($format) {
            self::FORMAT_MSGPACK => msgpack_pack($data),
            self::FORMAT_IGBINARY => igbinary_serialize($data),
            default => json_encode($data),
        };
        
        // Compress if exceeds threshold
        if (strlen($serialized) > $this->compressThreshold) {
            $compressed = lz4_compress($serialized);
            if ($compressed !== false && strlen($compressed) < strlen($serialized)) {
                return 'C:' . $compressed; // C: prefix indicates compressed
            }
        }
        
        return 'R:' . $serialized; // R: prefix indicates raw
    }
    
    public function unserialize(string $data)
    {
        $prefix = substr($data, 0, 2);
        $payload = substr($data, 2);
        
        if ($prefix === 'C:') {
            $payload = lz4_uncompress($payload);
        }
        
        // Auto-detect format
        if ($payload[0] === '{' || $payload[0] === '[') {
            return json_decode($payload, true);
        }
        
        // Try msgpack first (most common)
        $result = @msgpack_unpack($payload);
        if ($result !== false) {
            return $result;
        }
        
        // Fallback to igbinary
        return igbinary_unserialize($payload);
    }
}
```

### 5.3 Data Compression Ratios

| Data Type | Original Size | Compressed | Ratio |
|-----------|---------------|------------|-------|
| Student JSON | 2.5 KB | 1.1 KB | 56% |
| Attendance Array | 15 KB | 4.2 KB | 72% |
| Dashboard Stats | 8 KB | 2.1 KB | 74% |
| Exam Questions | 50 KB | 12 KB | 76% |

---

## 6. Cluster Configuration & Auto-Failover

### 6.1 Redis Sentinel Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    Redis Sentinel Cluster                        │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐             │
│  │  Sentinel 1 │  │  Sentinel 2 │  │  Sentinel 3 │             │
│  │  Port 26379 │  │  Port 26380 │  │  Port 26381 │             │
│  │   (Leader)  │  │             │  │             │             │
│  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘             │
│         │                │                │                     │
│         └────────────────┼────────────────┘                     │
│                          │                                      │
│                    Monitoring & Failover                        │
└─────────────────────────────────────────────────────────────────┘
                          │
┌─────────────────────────────────────────────────────────────────┐
│                     Redis Master-Replica                         │
│  ┌─────────────┐         ┌─────────────┐                       │
│  │   Master    │◄────────│  Replica 1  │                       │
│  │   (6379)    │  sync   │   (6380)    │                       │
│  │   WRITE     │         │    READ     │                       │
│  └──────┬──────┘         └─────────────┘                       │
│         │                                                        │
│         └──────────────►┌─────────────┐                       │
│                sync     │  Replica 2  │                       │
│                         │   (6381)    │                       │
│                         │    READ     │                       │
│                         └─────────────┘                       │
└─────────────────────────────────────────────────────────────────┘
```

### 6.2 Sentinel Configuration

```conf
# sentinel.conf
port 26379
daemonize yes
pidfile /var/run/redis-sentinel.pid
logfile /var/log/redis/sentinel.log
dir /var/lib/redis

# Monitor the master
sentinel monitor hamrolabs-cache 127.0.0.1 6379 2
sentinel auth-pass hamrolabs-cache YOUR_STRONG_PASSWORD
sentinel down-after-milliseconds hamrolabs-cache 5000
sentinel parallel-syncs hamrolabs-cache 1
sentinel failover-timeout hamrolabs-cache 60000
sentinel notification-script hamrolabs-cache /etc/redis/notify.sh
```

### 6.3 Laravel Configuration

```php
// config/database.php

'redis' => [
    'client' => env('REDIS_CLIENT', 'predis'),
    
    'options' => [
        'cluster' => 'redis',
        'prefix' => env('REDIS_PREFIX', 'hl_'),
        'parameters' => [
            'password' => env('REDIS_PASSWORD'),
            'scheme' => 'tls', // Enable TLS in production
        ],
    ],
    
    'clusters' => [
        'cache' => [
            'tcp://redis-cache-1:6379?timeout=3&read_timeout=3',
            'tcp://redis-cache-2:6379?timeout=3&read_timeout=3',
            'tcp://redis-cache-3:6379?timeout=3&read_timeout=3',
        ],
        'session' => [
            'tcp://redis-session-1:6380?timeout=3&read_timeout=3',
            'tcp://redis-session-2:6380?timeout=3&read_timeout=3',
        ],
    ],
    
    'sentinel' => [
        'tcp://sentinel-1:26379',
        'tcp://sentinel-2:26380',
        'tcp://sentinel-3:26381',
    ],
],
```

### 6.4 Failover Procedures

```php
// app/Services/Cache/FailoverManager.php

class FailoverManager
{
    private $redis;
    private $fallbackCache;
    private $circuitBreaker;
    
    public function executeWithFailover(callable $operation, $fallback = null)
    {
        // Check circuit breaker state
        if ($this->circuitBreaker->isOpen()) {
            return $fallback;
        }
        
        try {
            $result = $operation($this->redis);
            $this->circuitBreaker->recordSuccess();
            return $result;
            
        } catch (RedisException $e) {
            $this->circuitBreaker->recordFailure();
            
            // Log the failure
            Log::error('Redis operation failed', [
                'error' => $e->getMessage(),
                'circuit_state' => $this->circuitBreaker->getState(),
            ]);
            
            // Try fallback cache (APCu or File)
            if ($this->fallbackCache) {
                return $this->fallbackCache->get($fallback);
            }
            
            // Return default fallback
            return $fallback;
        }
    }
}
```

---

## 7. Memory Optimization Techniques

### 7.1 Memory Usage by Data Type

| Data Type | Memory/Entry | 100K Entries | Optimization |
|-----------|--------------|--------------|--------------|
| String (JSON) | 2.5 KB | 250 MB | Use Hashes |
| Hash (HSET) | 1.8 KB | 180 MB | ziplist encoding |
| List | 0.5 KB | 50 MB | Quicklist |
| Sorted Set | 2.0 KB | 200 MB | Skip list opt |
| Bitmap | 12 bytes | 12 KB | Bit operations |

### 7.2 Hash Optimization for Student Data

```php
// Instead of storing full JSON as string:
// SET tenant:42:student:12345 '{"name":"...","email":"...",...}'

// Use Redis Hashes for field-level access:
// HSET tenant:42:student:12345 name "John Doe" email "john@example.com"

class StudentCacheOptimizer
{
    public function cacheStudentProfile(int $tenantId, array $student): void
    {
        $key = "tenant:{$tenantId}:hot:student:{$student['id']}";
        
        // Map to hash fields
        $fields = [
            'id' => $student['id'],
            'roll_no' => $student['roll_no'],
            'full_name' => $student['full_name'],
            'email' => $student['email'] ?? '',
            'phone' => $student['phone'] ?? '',
            'batch_id' => $student['batch_id'] ?? '',
            'status' => $student['status'],
            'photo_url' => $student['photo_url'] ?? '',
            'cached_at' => time(),
        ];
        
        // Use HMSET for atomic update
        Redis::hmset($key, $fields);
        Redis::expire($key, 3600); // 1 hour TTL
        
        // Also add to index for batch lookups
        Redis::sadd("tenant:{$tenantId}:idx:batch:{$student['batch_id']}", $student['id']);
    }
    
    public function getStudentProfile(int $tenantId, int $studentId): ?array
    {
        $key = "tenant:{$tenantId}:hot:student:{$studentId}";
        $data = Redis::hgetall($key);
        
        return empty($data) ? null : $data;
    }
    
    public function getStudentsByBatch(int $tenantId, int $batchId): array
    {
        $indexKey = "tenant:{$tenantId}:idx:batch:{$batchId}";
        $studentIds = Redis::smembers($indexKey);
        
        $students = [];
        foreach ($studentIds as $id) {
            $student = $this->getStudentProfile($tenantId, $id);
            if ($student) {
                $students[] = $student;
            }
        }
        
        return $students;
    }
}
```

### 7.3 Bitmap Optimization for Attendance

```php
// Store attendance as bitmaps - massive space savings
// 1 bit per student per day vs full record storage

class AttendanceBitmapCache
{
    /**
     * Mark attendance using bitmap
     * Bit position = student_id % 10000 (within batch)
     */
    public function markAttendance(
        int $tenantId, 
        int $batchId, 
        string $date, 
        int $studentId,
        bool $present
    ): void {
        $key = "tenant:{$tenantId}:agg:attendance:batch:{$batchId}:date:{$date}";
        $bitPosition = $studentId % 10000;
        
        Redis::setbit($key, $bitPosition, $present ? 1 : 0);
        Redis::expire($key, 86400 * 30); // 30 days
    }
    
    /**
     * Get attendance count for a batch on a date
     */
    public function getAttendanceCount(int $tenantId, int $batchId, string $date): int
    {
        $key = "tenant:{$tenantId}:agg:attendance:batch:{$batchId}:date:{$date}";
        return Redis::bitcount($key);
    }
    
    /**
     * Check if student was present
     */
    public function wasPresent(int $tenantId, int $batchId, string $date, int $studentId): bool
    {
        $key = "tenant:{$tenantId}:agg:attendance:batch:{$batchId}:date:{$date}";
        $bitPosition = $studentId % 10000;
        
        return Redis::getbit($key, $bitPosition) === 1;
    }
    
    /**
     * Get monthly attendance percentage
     */
    public function getMonthlyAttendanceRate(
        int $tenantId, 
        int $batchId, 
        int $studentId,
        string $yearMonth
    ): float {
        $present = 0;
        $total = 0;
        
        for ($day = 1; $day <= 31; $day++) {
            $date = "{$yearMonth}-" . str_pad($day, 2, '0', STR_PAD_LEFT);
            $key = "tenant:{$tenantId}:agg:attendance:batch:{$batchId}:date:{$date}";
            
            if (Redis::exists($key)) {
                $total++;
                if ($this->wasPresent($tenantId, $batchId, $date, $studentId)) {
                    $present++;
                }
            }
        }
        
        return $total > 0 ? ($present / $total) * 100 : 0;
    }
}
```

---

## 8. Integration Patterns

### 8.1 Repository Pattern with Cache

```php
// app/Repositories/CachedStudentRepository.php

class CachedStudentRepository
{
    private $db;
    private $cache;
    private $invalidator;
    
    public function __construct(
        Database $db, 
        Redis $cache,
        CacheInvalidator $invalidator
    ) {
        $this->db = $db;
        $this->cache = $cache;
        $this->invalidator = $invalidator;
    }
    
    /**
     * Cache-aside pattern (Lazy Loading)
     */
    public function find(int $tenantId, int $studentId): ?array
    {
        $cacheKey = CachePartitioner::key($tenantId, 'hot', 'student', $studentId);
        
        // 1. Try cache first
        $cached = $this->cache->hgetall($cacheKey);
        if (!empty($cached)) {
            Metrics::increment('cache.hit', ['entity' => 'student']);
            return $cached;
        }
        
        // 2. Cache miss - fetch from database
        Metrics::increment('cache.miss', ['entity' => 'student']);
        
        $student = $this->db->table('students')
            ->where('tenant_id', $tenantId)
            ->where('id', $studentId)
            ->first();
        
        if ($student) {
            // 3. Store in cache
            $this->cacheStudent($tenantId, $student);
        }
        
        return $student;
    }
    
    /**
     * Write-through pattern
     */
    public function update(int $tenantId, int $studentId, array $data): bool
    {
        // 1. Update database
        $updated = $this->db->table('students')
            ->where('tenant_id', $tenantId)
            ->where('id', $studentId)
            ->update($data);
        
        if ($updated) {
            // 2. Update cache immediately
            $cacheKey = CachePartitioner::key($tenantId, 'hot', 'student', $studentId);
            $this->cache->hmset($cacheKey, $data);
            
            // 3. Invalidate related caches
            $this->invalidator->invalidateByTag(
                "tenant:{$tenantId}:student:{$studentId}"
            );
        }
        
        return $updated;
    }
    
    /**
     * Write-behind pattern for bulk operations
     */
    public function bulkUpdateAsync(int $tenantId, array $updates): void
    {
        // 1. Queue cache updates
        foreach ($updates as $update) {
            CacheUpdateJob::dispatch([
                'tenant_id' => $tenantId,
                'student_id' => $update['id'],
                'data' => $update['data'],
            ]);
        }
        
        // 2. Database update happens async in job
    }
    
    private function cacheStudent(int $tenantId, array $student): void
    {
        $cacheKey = CachePartitioner::key($tenantId, 'hot', 'student', $student['id']);
        
        $this->cache->hmset($cacheKey, [
            'id' => $student['id'],
            'roll_no' => $student['roll_no'],
            'full_name' => $student['full_name'],
            'email' => $student['email'] ?? null,
            'phone' => $student['phone'] ?? null,
            'batch_id' => $student['batch_id'],
            'status' => $student['status'],
        ]);
        
        $this->cache->expire($cacheKey, 3600);
    }
}
```

### 8.2 Service Layer Integration

```php
// app/Services/StudentService.php (Updated with caching)

class StudentService
{
    private $repository;
    private $cache;
    private $serializer;
    
    public function __construct(
        CachedStudentRepository $repository,
        Redis $cache,
        CacheSerializer $serializer
    ) {
        $this->repository = $repository;
        $this->cache = $cache;
        $this->serializer = $serializer;
    }
    
    public function getStudentWithDetails(int $tenantId, int $studentId): array
    {
        $cacheKey = CachePartitioner::key($tenantId, 'agg', 'student_details', $studentId);
        
        // Try cache
        $cached = $this->cache->get($cacheKey);
        if ($cached) {
            return $this->serializer->unserialize($cached);
        }
        
        // Fetch from DB with joins
        $student = $this->repository->findWithRelations($tenantId, $studentId, [
            'batch',
            'course',
            'fee_summary',
            'attendance_stats',
        ]);
        
        // Serialize and cache
        $serialized = $this->serializer->serialize($student);
        $this->cache->setex($cacheKey, 600, $serialized); // 10 min TTL
        
        return $student;
    }
    
    public function searchStudents(int $tenantId, array $filters): array
    {
        // Generate cache key from filters
        $filterHash = md5(json_encode($filters));
        $cacheKey = CachePartitioner::key($tenantId, 'list', 'student_search', $filterHash);
        
        $cached = $this->cache->get($cacheKey);
        if ($cached) {
            return $this->serializer->unserialize($cached);
        }
        
        // Perform search
        $results = $this->repository->search($tenantId, $filters);
        
        // Cache with shorter TTL for search results
        $serialized = $this->serializer->serialize($results);
        $this->cache->setex($cacheKey, 300, $serialized); // 5 min TTL
        
        return $results;
    }
}
```

---

## 9. Hot Data Pattern Identification

### 9.1 Access Pattern Analysis

```sql
-- Query to identify hot data from API logs
SELECT 
    endpoint,
    COUNT(*) as request_count,
    AVG(response_time) as avg_response_time,
    PERCENTILE_CONT(0.95) WITHIN GROUP (ORDER BY response_time) as p95_response_time,
    COUNT(DISTINCT user_id) as unique_users
FROM api_logs
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAYS)
GROUP BY endpoint
HAVING request_count > 1000
ORDER BY request_count DESC;
```

### 9.2 Identified Hot Data Patterns

| Endpoint | Requests/Day | Cache Priority | Strategy |
|----------|--------------|----------------|----------|
| GET /api/student/dashboard | 50,000 | Critical | Aggressive caching, 5min TTL |
| GET /api/attendance/today | 30,000 | Critical | Bitmap storage, real-time |
| GET /api/fees/summary | 25,000 | High | Write-through, 30min TTL |
| GET /api/timetable/week | 20,000 | High | Weekly prefetch |
| GET /api/exams/active | 15,000 | Medium | 1 hour TTL |
| GET /api/materials/list | 10,000 | Medium | 2 hour TTL |

### 9.3 Predictive Cache Warming

```php
// app/Services/Cache/PredictiveCacheWarmer.php

class PredictiveCacheWarmer
{
    private $repository;
    private $cache;
    
    /**
     * Warm cache based on predicted access patterns
     */
    public function warmForTenant(int $tenantId): void
    {
        $startTime = microtime(true);
        $keysWarmed = 0;
        
        // 1. Warm active students (enrolled in last 30 days)
        $activeStudents = $this->repository->getActiveStudents($tenantId, 30);
        foreach ($activeStudents as $student) {
            $this->warmStudentData($tenantId, $student['id']);
            $keysWarmed++;
        }
        
        // 2. Warm today's attendance data
        $today = date('Y-m-d');
        $batches = $this->repository->getActiveBatches($tenantId);
        foreach ($batches as $batch) {
            $this->warmAttendanceData($tenantId, $batch['id'], $today);
            $keysWarmed++;
        }
        
        // 3. Warm dashboard statistics
        $this->warmDashboardStats($tenantId);
        $keysWarmed += 5; // Multiple stat keys
        
        // 4. Warm fee summaries for students with upcoming dues
        $upcomingDueStudents = $this->repository->getStudentsWithUpcomingDues($tenantId, 7);
        foreach ($upcomingDueStudents as $student) {
            $this->warmFeeSummary($tenantId, $student['id']);
            $keysWarmed++;
        }
        
        $duration = microtime(true) - $startTime;
        
        Log::info('Cache warming completed', [
            'tenant_id' => $tenantId,
            'keys_warmed' => $keysWarmed,
            'duration_ms' => round($duration * 1000, 2),
        ]);
        
        Metrics::gauge('cache.warmed_keys', $keysWarmed, ['tenant' => $tenantId]);
    }
    
    /**
     * Schedule warming during low-traffic hours
     */
    public function scheduleWarming(): void
    {
        // Run at 4 AM Nepal time (low traffic)
        $schedule->command('cache:warm-tenants')
                 ->dailyAt('04:00')
                 ->timezone('Asia/Kathmandu')
                 ->withoutOverlapping();
    }
}
```

---

## 10. TTL Management

### 10.1 Dynamic TTL Strategy

```php
// app/Services/Cache/TTLManager.php

class TTLManager
{
    // Base TTL values (seconds)
    const BASE_TTL = [
        'student_profile' => 3600,        // 1 hour
        'attendance_daily' => 14400,      // 4 hours
        'fee_summary' => 1800,            // 30 minutes
        'dashboard_stats' => 300,         // 5 minutes
        'timetable_weekly' => 86400,      // 24 hours
        'exam_questions' => 7200,         // 2 hours
        'study_materials' => 3600,        // 1 hour
        'search_results' => 300,          // 5 minutes
    ];
    
    /**
     * Calculate dynamic TTL based on data characteristics
     */
    public function calculateTTL(string $dataType, array $context = []): int
    {
        $baseTTL = self::BASE_TTL[$dataType] ?? 300;
        
        // Adjust based on tenant activity
        if (isset($context['tenant_tier'])) {
            $baseTTL = $this->adjustForTier($baseTTL, $context['tenant_tier']);
        }
        
        // Adjust based on time of day
        $baseTTL = $this->adjustForTimeOfDay($baseTTL);
        
        // Adjust based on data volatility
        if (isset($context['volatility'])) {
            $baseTTL = $this->adjustForVolatility($baseTTL, $context['volatility']);
        }
        
        // Add jitter to prevent thundering herd
        $jitter = random_int(0, (int)($baseTTL * 0.1));
        
        return $baseTTL + $jitter;
    }
    
    private function adjustForTier(int $ttl, string $tier): int
    {
        return match($tier) {
            'enterprise' => (int)($ttl * 1.5), // Longer cache for premium
            'professional' => (int)($ttl * 1.2),
            'starter' => (int)($ttl * 0.8),    // Shorter for cost optimization
            default => $ttl,
        };
    }
    
    private function adjustForTimeOfDay(int $ttl): int
    {
        $hour = (int)date('H');
        
        // Peak hours (9 AM - 5 PM Nepal time): shorter TTL for freshness
        if ($hour >= 9 && $hour <= 17) {
            return (int)($ttl * 0.7);
        }
        
        // Off-peak: longer TTL acceptable
        if ($hour >= 22 || $hour <= 5) {
            return (int)($ttl * 2);
        }
        
        return $ttl;
    }
    
    private function adjustForVolatility(int $ttl, string $volatility): int
    {
        return match($volatility) {
            'high' => (int)($ttl * 0.3),    // Very short for frequently changing
            'medium' => $ttl,
            'low' => (int)($ttl * 3),      // Long cache for stable data
            default => $ttl,
        };
    }
}
```

### 10.2 Sliding Window TTL

```php
/**
 * Extend TTL on cache hit (sliding window)
 */
public function getWithSlidingTTL(string $key, int $baseTTL): ?array
{
    $value = Redis::get($key);
    
    if ($value !== null) {
        // Extend TTL on each access
        Redis::expire($key, $baseTTL);
        return json_decode($value, true);
    }
    
    return null;
}
```

---

## 11. Cache Warming Procedures

### 11.1 Startup Warming

```php
// app/Console/Commands/CacheWarmCommand.php

class CacheWarmCommand extends Command
{
    protected $signature = 'cache:warm 
                            {--tenant= : Specific tenant ID}
                            {--type=all : Type of data to warm (students|attendance|fees|all)}
                            {--priority=normal : Priority level (critical|high|normal)}';
    
    public function handle()
    {
        $tenantId = $this->option('tenant');
        $type = $this->option('type');
        $priority = $this->option('priority');
        
        $warmer = new CacheWarmer();
        
        if ($tenantId) {
            $this->info("Warming cache for tenant {$tenantId}...");
            $warmer->warmTenant($tenantId, $type);
        } else {
            // Warm all active tenants
            $tenants = Tenant::where('status', 'active')->pluck('id');
            
            $progress = $this->output->createProgressBar(count($tenants));
            
            foreach ($tenants as $id) {
                $warmer->warmTenant($id, $type);
                $progress->advance();
            }
            
            $progress->finish();
        }
        
        $this->info('Cache warming completed.');
        
        // Report metrics
        $metrics = $warmer->getMetrics();
        $this->table(
            ['Metric', 'Value'],
            [
                ['Keys Warmed', $metrics['keys_warmed']],
                ['Keys Failed', $metrics['keys_failed']],
                ['Total Time', $metrics['duration'] . 's'],
                ['Memory Used', $this->formatBytes($metrics['memory_used'])],
            ]
        );
        
        return 0;
    }
}
```

### 11.2 Selective Warming by Priority

```php
class CacheWarmer
{
    public function warmTenant(int $tenantId, string $type = 'all'): void
    {
        $priorities = [
            'critical' => [
                'dashboard_stats',
                'active_students',
                'todays_attendance',
            ],
            'high' => [
                'fee_summaries',
                'batch_lists',
                'timetable_current',
            ],
            'normal' => [
                'student_profiles',
                'exam_data',
                'study_materials',
            ],
        ];
        
        foreach ($priorities as $priority => $dataTypes) {
            if ($type !== 'all' && !in_array($type, $dataTypes)) {
                continue;
            }
            
            foreach ($dataTypes as $dataType) {
                try {
                    $this->warmDataType($tenantId, $dataType);
                } catch (Exception $e) {
                    Log::error("Failed to warm {$dataType}", [
                        'tenant_id' => $tenantId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}
```

---

## 12. Observability Framework

### 12.1 Metrics Collection

```php
// app/Services/Cache/CacheMetrics.php

class CacheMetrics
{
    private $redis;
    
    public function recordHit(string $key): void
    {
        $this->redis->incr('metrics:cache:hits');
        $this->redis->hincrby('metrics:cache:hits:by_key', $this->extractKeyType($key), 1);
        
        // Prometheus-compatible metric
        Metrics::counter('cache_operations_total', [
            'operation' => 'hit',
            'key_type' => $this->extractKeyType($key),
        ])->inc();
    }
    
    public function recordMiss(string $key): void
    {
        $this->redis->incr('metrics:cache:misses');
        $this->redis->hincrby('metrics:cache:misses:by_key', $this->extractKeyType($key), 1);
        
        Metrics::counter('cache_operations_total', [
            'operation' => 'miss',
            'key_type' => $this->extractKeyType($key),
        ])->inc();
    }
    
    public function recordLatency(string $operation, float $milliseconds): void
    {
        $this->redis->lpush('metrics:cache:latency:' . $operation, $milliseconds);
        $this->redis->ltrim('metrics:cache:latency:' . $operation, 0, 999); // Keep last 1000
        
        Metrics::histogram('cache_operation_duration_seconds')
            ->observe($milliseconds / 1000, ['operation' => $operation]);
    }
    
    public function getHitRate(): float
    {
        $hits = (int)$this->redis->get('metrics:cache:hits');
        $misses = (int)$this->redis->get('metrics:cache:misses');
        $total = $hits + $misses;
        
        return $total > 0 ? ($hits / $total) * 100 : 0;
    }
    
    public function getStats(): array
    {
        $info = $this->redis->info();
        
        return [
            'memory_used' => $info['used_memory_human'],
            'memory_peak' => $info['used_memory_peak_human'],
            'connected_clients' => $info['connected_clients'],
            'total_commands' => $info['total_commands_processed'],
            'hits' => $info['keyspace_hits'],
            'misses' => $info['keyspace_misses'],
            'hit_rate' => $this->calculateHitRate($info),
            'evicted_keys' => $info['evicted_keys'],
            'expired_keys' => $info['expired_keys'],
            'keys_count' => array_sum(array_map(function ($db) {
                return $db['keys'];
            }, $info['Keyspace'])),
        ];
    }
}
```

### 12.2 Health Checks

```php
// app/Services/Cache/CacheHealthCheck.php

class CacheHealthCheck
{
    public function check(): HealthStatus
    {
        $checks = [
            'connectivity' => $this->checkConnectivity(),
            'latency' => $this->checkLatency(),
            'memory' => $this->checkMemoryUsage(),
            'hit_rate' => $this->checkHitRate(),
            'replication' => $this->checkReplication(),
        ];
        
        $healthy = !in_array(false, $checks, true);
        
        return new HealthStatus(
            status: $healthy ? 'healthy' : 'degraded',
            checks: $checks,
            timestamp: now(),
        );
    }
    
    private function checkLatency(): bool
    {
        $start = microtime(true);
        Redis::ping();
        $latency = (microtime(true) - $start) * 1000;
        
        return $latency < 10; // Alert if > 10ms
    }
    
    private function checkMemoryUsage(): bool
    {
        $info = Redis::info('memory');
        $used = $info['used_memory'];
        $max = $info['maxmemory'];
        
        if ($max == 0) return true; // No limit set
        
        $usagePercent = ($used / $max) * 100;
        return $usagePercent < 90; // Alert if > 90%
    }
    
    private function checkHitRate(): bool
    {
        $metrics = new CacheMetrics();
        $hitRate = $metrics->getHitRate();
        
        return $hitRate > 70; // Alert if < 70%
    }
}
```

### 12.3 Alerting Rules

```yaml
# prometheus-alerts.yml
groups:
  - name: redis_cache_alerts
    rules:
      - alert: RedisHighMemoryUsage
        expr: redis_memory_used_bytes / redis_memory_max_bytes > 0.9
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "Redis memory usage is above 90%"
          
      - alert: RedisLowHitRate
        expr: rate(redis_keyspace_hits_total[5m]) / (rate(redis_keyspace_hits_total[5m]) + rate(redis_keyspace_misses_total[5m])) < 0.7
        for: 10m
        labels:
          severity: warning
        annotations:
          summary: "Redis cache hit rate is below 70%"
          
      - alert: RedisHighLatency
        expr: histogram_quantile(0.99, rate(redis_command_duration_seconds_bucket[5m])) > 0.01
        for: 5m
        labels:
          severity: critical
        annotations:
          summary: "Redis 99th percentile latency is above 10ms"
          
      - alert: RedisDisconnected
        expr: up{job="redis"} == 0
        for: 1m
        labels:
          severity: critical
        annotations:
          summary: "Redis instance is down"
```

---

## 13. Security Implementation

### 13.1 Redis Security Configuration

```conf
# redis.conf - Security Hardening

# Require password authentication
requirepass YOUR_STRONG_PASSWORD_HERE

# Rename dangerous commands
rename-command FLUSHDB ""
rename-command FLUSHALL ""
rename-command CONFIG "CONFIG_6f3a9e2b"
rename-command DEBUG ""
rename-command SHUTDOWN "SHUTDOWN_SECURE"

# Bind to specific interfaces only
bind 127.0.0.1 10.0.0.0/8

# Enable TLS for production
port 0
tls-port 6379
tls-cert-file /etc/redis/redis.crt
tls-key-file /etc/redis/redis.key
tls-ca-cert-file /etc/redis/ca.crt
tls-protocols "TLSv1.2 TLSv1.3"

# Client output buffer limits
client-output-buffer-limit normal 0 0 0
client-output-buffer-limit replica 256mb 64mb 60
client-output-buffer-limit pubsub 32mb 8mb 60

# Enable ACL (Redis 6+)
user default on >password ~* &* +@all
user readonly on >ro_password ~* &* +@read
user app on >app_password ~tenant:* &* +@all -@admin
```

### 13.2 Encryption at Rest and in Transit

```php
// app/Services/Cache/SecureCache.php

class SecureCache
{
    private $encryptionKey;
    private $cipher = 'AES-256-GCM';
    
    public function setEncrypted(string $key, $data, int $ttl = 3600): void
    {
        $iv = random_bytes(16);
        $tag = '';
        
        $encrypted = openssl_encrypt(
            json_encode($data),
            $this->cipher,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            16
        );
        
        $payload = base64_encode($iv . $tag . $encrypted);
        
        Redis::setex($key, $ttl, $payload);
    }
    
    public function getEncrypted(string $key): ?array
    {
        $payload = Redis::get($key);
        
        if (!$payload) {
            return null;
        }
        
        $data = base64_decode($payload);
        $iv = substr($data, 0, 16);
        $tag = substr($data, 16, 16);
        $encrypted = substr($data, 32);
        
        $decrypted = openssl_decrypt(
            $encrypted,
            $this->cipher,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        return $decrypted ? json_decode($decrypted, true) : null;
    }
}
```

### 13.3 Tenant Data Isolation Verification

```php
// app/Middleware/CacheSecurityMiddleware.php

class CacheSecurityMiddleware
{
    public function handle($request, $next)
    {
        $tenantId = $request->attributes->get('tenant_id');
        
        // Verify all cache operations use tenant prefix
        $originalRedis = Redis::connection();
        
        Redis::macro('tenantAwareCall', function ($method, $args) use ($tenantId) {
            // Check if first argument is a key
            if (isset($args[0]) && is_string($args[0])) {
                $key = $args[0];
                
                // Ensure key starts with tenant prefix
                if (!str_starts_with($key, "tenant:{$tenantId}:")) {
                    throw new SecurityException(
                        "Cache key '{$key}' does not have required tenant:{$tenantId} prefix"
                    );
                }
            }
            
            return $this->connection()->{$method}(...$args);
        });
        
        return $next($request);
    }
}
```

---

## 14. Circuit Breakers

### 14.1 Circuit Breaker Implementation

```php
// app/Services/Cache/CircuitBreaker.php

class CircuitBreaker
{
    const STATE_CLOSED = 'closed';       // Normal operation
    const STATE_OPEN = 'open';           // Failing, rejecting requests
    const STATE_HALF_OPEN = 'half_open'; // Testing if recovered
    
    private $failureThreshold = 5;
    private $successThreshold = 3;
    private $timeout = 30; // seconds
    
    public function execute(callable $operation, callable $fallback = null)
    {
        $state = $this->getState();
        
        switch ($state) {
            case self::STATE_OPEN:
                if ($this->shouldAttemptReset()) {
                    $this->setState(self::STATE_HALF_OPEN);
                } else {
                    return $fallback ? $fallback() : null;
                }
                break;
                
            case self::STATE_HALF_OPEN:
                return $this->attemptOperation($operation, $fallback, true);
                
            case self::STATE_CLOSED:
            default:
                return $this->attemptOperation($operation, $fallback, false);
        }
    }
    
    private function attemptOperation(callable $operation, callable $fallback, bool $isHalfOpen)
    {
        try {
            $result = $operation();
            $this->recordSuccess($isHalfOpen);
            return $result;
            
        } catch (Exception $e) {
            $this->recordFailure($isHalfOpen);
            
            if ($fallback) {
                return $fallback();
            }
            
            throw $e;
        }
    }
    
    private function recordFailure(bool $isHalfOpen): void
    {
        Redis::incr('circuit_breaker:failures');
        
        $failures = Redis::get('circuit_breaker:failures');
        
        if ($failures >= $this->failureThreshold) {
            $this->setState(self::STATE_OPEN);
            Redis::setex('circuit_breaker:opened_at', 3600, time());
        }
        
        if ($isHalfOpen) {
            // Back to open on failure in half-open state
            $this->setState(self::STATE_OPEN);
        }
    }
    
    private function recordSuccess(bool $isHalfOpen): void
    {
        if ($isHalfOpen) {
            Redis::incr('circuit_breaker:successes');
            $successes = Redis::get('circuit_breaker:successes');
            
            if ($successes >= $this->successThreshold) {
                $this->setState(self::STATE_CLOSED);
                Redis::del('circuit_breaker:failures');
                Redis::del('circuit_breaker:successes');
            }
        } else {
            // Reset failures on success in closed state
            Redis::del('circuit_breaker:failures');
        }
    }
    
    private function getState(): string
    {
        return Redis::get('circuit_breaker:state') ?? self::STATE_CLOSED;
    }
    
    private function setState(string $state): void
    {
        Redis::set('circuit_breaker:state', $state);
        
        Log::warning("Circuit breaker state changed to: {$state}");
        
        // Alert on state change
        if ($state === self::STATE_OPEN) {
            Alert::send('Redis cache circuit breaker opened');
        }
    }
    
    private function shouldAttemptReset(): bool
    {
        $openedAt = Redis::get('circuit_breaker:opened_at');
        return $openedAt && (time() - $openedAt) > $this->timeout;
    }
}
```

### 14.2 Fallback Chain

```php
// app/Services/Cache/CacheWithFallback.php

class CacheWithFallback
{
    private $redis;
    private $apcu;
    private $fileCache;
    private $circuitBreaker;
    
    public function get(string $key)
    {
        return $this->circuitBreaker->execute(
            function() use ($key) {
                return $this->redis->get($key);
            },
            function() use ($key) {
                // Fallback to APCu
                $value = $this->apcu->get($key);
                if ($value !== false) {
                    return $value;
                }
                
                // Fallback to file cache
                return $this->fileCache->get($key);
            }
        );
    }
    
    public function getFromDatabaseFallback(string $cacheKey, callable $dbQuery)
    {
        return $this->circuitBreaker->execute(
            function() use ($cacheKey) {
                return $this->redis->get($cacheKey);
            },
            function() use ($dbQuery, $cacheKey) {
                // Direct database query
                $result = $dbQuery();
                
                // Async cache repopulation
                dispatch(new RepopulateCacheJob($cacheKey, $result));
                
                return $result;
            }
        );
    }
}
```

---

## 15. Consistency Models

### 15.1 Consistency Strategy Matrix

| Data Type | Consistency Model | Implementation |
|-----------|-------------------|----------------|
| Financial Records | Strong | Write-through + 2PC |
| Student Profiles | Eventual | Write-behind + invalidation |
| Attendance | Eventual | Bitmap + background sync |
| Dashboard Stats | Eventual | TTL-based refresh |
| Session Data | Strong | Write-through |
| Exam Questions | Read-heavy | Cache-aside + versioning |

### 15.2 Version-Based Consistency

```php
// app/Services/Cache/VersionedCache.php

class VersionedCache
{
    /**
     * Write with version check (optimistic locking)
     */
    public function setWithVersion(string $key, $data, int $expectedVersion): bool
    {
        $lua = <<<'LUA'
            local key = KEYS[1]
            local data = ARGV[1]
            local expectedVersion = tonumber(ARGV[2])
            local newVersion = expectedVersion + 1
            local ttl = tonumber(ARGV[3])
            
            local current = redis.call('hget', key, 'version')
            current = tonumber(current) or 0
            
            if current ~= expectedVersion then
                return -1 -- Version conflict
            end
            
            redis.call('hset', key, 'data', data)
            redis.call('hset', key, 'version', newVersion)
            redis.call('expire', key, ttl)
            
            return newVersion
        LUA;
        
        $result = Redis::eval($lua, 1, $key, 
            json_encode($data), 
            $expectedVersion, 
            3600
        );
        
        if ($result == -1) {
            throw new ConcurrentModificationException('Version conflict detected');
        }
        
        return true;
    }
    
    /**
     * Read with version
     */
    public function getWithVersion(string $key): ?array
    {
        $data = Redis::hgetall($key);
        
        if (empty($data)) {
            return null;
        }
        
        return [
            'data' => json_decode($data['data'], true),
            'version' => (int)$data['version'],
        ];
    }
}
```

### 15.3 Distributed Transaction Support

```php
// app/Services/Cache/DistributedTransaction.php

class DistributedTransaction
{
    private $redis;
    
    public function begin(): string
    {
        $txId = uniqid('tx_', true);
        Redis::hset("tx:{$txId}", 'status', 'pending');
        Redis::expire("tx:{$txId}", 300); // 5 min timeout
        
        return $txId;
    }
    
    public function prepare(string $txId, array $keys): bool
    {
        // Lock all keys involved
        foreach ($keys as $key) {
            $lockKey = "tx:{$txId}:lock:{$key}";
            if (!Redis::set($lockKey, $txId, 'NX', 'EX', 60)) {
                // Rollback locks
                $this->rollback($txId);
                return false;
            }
        }
        
        Redis::hset("tx:{$txId}", 'status', 'prepared');
        return true;
    }
    
    public function commit(string $txId): void
    {
        $status = Redis::hget("tx:{$txId}", 'status');
        
        if ($status !== 'prepared') {
            throw new TransactionException('Transaction not in prepared state');
        }
        
        Redis::hset("tx:{$txId}", 'status', 'committed');
        
        // Release locks
        $locks = Redis::keys("tx:{$txId}:lock:*");
        foreach ($locks as $lock) {
            Redis::del($lock);
        }
    }
    
    public function rollback(string $txId): void
    {
        // Restore original values from transaction log
        $changes = Redis::hgetall("tx:{$txId}:changes");
        
        foreach ($changes as $key => $originalValue) {
            Redis::set($key, $originalValue);
        }
        
        // Release locks
        $locks = Redis::keys("tx:{$txId}:lock:*");
        foreach ($locks as $lock) {
            Redis::del($lock);
        }
        
        Redis::hset("tx:{$txId}", 'status', 'rolledback');
    }
}
```

---

## 16. Concurrency Handling

### 16.1 Distributed Locking

```php
// app/Services/Cache/DistributedLock.php

class DistributedLock
{
    private $redis;
    
    /**
     * Acquire distributed lock with auto-expiry
     */
    public function acquire(string $resource, int $ttl = 30): ?string
    {
        $token = uniqid('lock_', true);
        $key = "lock:{$resource}";
        
        // Try to acquire lock
        if (Redis::set($key, $token, 'NX', 'EX', $ttl)) {
            return $token;
        }
        
        return null;
    }
    
    /**
     * Release lock safely (only if we own it)
     */
    public function release(string $resource, string $token): bool
    {
        $key = "lock:{$resource}";
        
        $lua = <<<'LUA'
            if redis.call('get', KEYS[1]) == ARGV[1] then
                return redis.call('del', KEYS[1])
            else
                return 0
            end
        LUA;
        
        return Redis::eval($lua, 1, $key, $token) === 1;
    }
    
    /**
     * Execute with lock
     */
    public function withLock(string $resource, callable $callback, int $ttl = 30, int $retries = 3)
    {
        $token = null;
        $attempts = 0;
        
        while ($token === null && $attempts < $retries) {
            $token = $this->acquire($resource, $ttl);
            if ($token === null) {
                usleep(100000); // 100ms
                $attempts++;
            }
        }
        
        if ($token === null) {
            throw new LockAcquisitionException("Could not acquire lock for {$resource}");
        }
        
        try {
            return $callback();
        } finally {
            $this->release($resource, $token);
        }
    }
}
```

### 16.2 Redlock Algorithm for Multi-Instance

```php
// app/Services/Cache/Redlock.php

class Redlock
{
    private $redisInstances;
    private $quorum;
    
    public function __construct(array $redisInstances)
    {
        $this->redisInstances = $redisInstances;
        $this->quorum = (int)(count($redisInstances) / 2) + 1;
    }
    
    public function lock(string $resource, int $ttl): ?array
    {
        $token = uniqid('redlock_', true);
        $key = "redlock:{$resource}";
        
        $lockedInstances = 0;
        $startTime = microtime(true) * 1000;
        
        foreach ($this->redisInstances as $redis) {
            if ($this->lockInstance($redis, $key, $token, $ttl)) {
                $lockedInstances++;
            }
        }
        
        $elapsed = (microtime(true) * 1000) - $startTime;
        $validity = $ttl - $elapsed - 10; // 10ms drift margin
        
        if ($lockedInstances >= $this->quorum && $validity > 0) {
            return [
                'token' => $token,
                'validity' => $validity,
            ];
        }
        
        // Failed to acquire quorum, release all locks
        foreach ($this->redisInstances as $redis) {
            $this->unlockInstance($redis, $key, $token);
        }
        
        return null;
    }
    
    private function lockInstance($redis, string $key, string $token, int $ttl): bool
    {
        return $redis->set($key, $token, ['NX', 'PX' => $ttl]);
    }
}
```

---

## 17. Cache Stampede Mitigation

### 17.1 Probabilistic Early Expiration

```php
// app/Services/Cache/ProbabilisticCache.php

class ProbabilisticCache
{
    private $beta = 1.0; // Tuning parameter
    
    /**
     * Get with probabilistic early expiration
     * Prevents stampede by having different clients refresh at different times
     */
    public function get(string $key, callable $fetcher, int $ttl)
    {
        $value = Redis::get($key);
        $ttlRemaining = Redis::ttl($key);
        
        if ($value === null) {
            // Cache miss - fetch and store
            return $this->fetchAndStore($key, $fetcher, $ttl);
        }
        
        // Calculate probability of early refresh
        if ($ttlRemaining > 0) {
            $delta = time() - (Redis::object('idletime', $key) ?? 0);
            $expiry = time() + $ttlRemaining;
            
            // Probability increases as expiration approaches
            $probability = exp($this->beta * $delta / $ttl);
            
            if (mt_rand() / mt_getrandmax() < $probability) {
                // This client will refresh
                dispatch(new RefreshCacheJob($key, $fetcher, $ttl));
            }
        }
        
        return json_decode($value, true);
    }
    
    private function fetchAndStore(string $key, callable $fetcher, int $ttl)
    {
        // Acquire lock to prevent multiple concurrent fetches
        $lock = new DistributedLock();
        $token = $lock->acquire("fetch:{$key}", 10);
        
        if ($token === null) {
            // Another process is fetching, wait and retry
            usleep(100000); // 100ms
            return $this->get($key, $fetcher, $ttl);
        }
        
        try {
            $value = $fetcher();
            Redis::setex($key, $ttl, json_encode($value));
            return $value;
        } finally {
            $lock->release("fetch:{$key}", $token);
        }
    }
}
```

### 17.2 Per-Key Mutex (Dog-Piling Prevention)

```php
// app/Services/Cache/MutexCache.php

class MutexCache
{
    /**
     * Cache with mutex to prevent stampede
     */
    public function remember(string $key, int $ttl, callable $callback)
    {
        $value = Redis::get($key);
        
        if ($value !== null) {
            return json_decode($value, true);
        }
        
        $mutexKey = "mutex:{$key}";
        $mutexValue = uniqid('mutex_', true);
        
        // Try to acquire mutex
        if (!Redis::set($mutexKey, $mutexValue, 'NX', 'EX', 30)) {
            // Someone else is generating, wait
            $attempts = 0;
            while ($attempts < 50) { // Max 5 seconds
                usleep(100000); // 100ms
                
                $value = Redis::get($key);
                if ($value !== null) {
                    return json_decode($value, true);
                }
                
                $attempts++;
            }
            
            // Timeout - proceed with fetch anyway
        }
        
        try {
            // Double-check after acquiring mutex
            $value = Redis::get($key);
            if ($value !== null) {
                return json_decode($value, true);
            }
            
            // Generate value
            $value = $callback();
            Redis::setex($key, $ttl, json_encode($value));
            
            return $value;
            
        } finally {
            // Release mutex (only if we own it)
            $lua = <<<'LUA'
                if redis.call('get', KEYS[1]) == ARGV[1] then
                    return redis.call('del', KEYS[1])
                end
                return 0
            LUA;
            
            Redis::eval($lua, 1, $mutexKey, $mutexValue);
        }
    }
}
```

### 17.3 Cache Warming with Background Refresh

```php
// app/Services/Cache/BackgroundRefreshCache.php

class BackgroundRefreshCache
{
    /**
     * Get value with background refresh near expiration
     */
    public function getWithBackgroundRefresh(
        string $key, 
        callable $fetcher, 
        int $ttl,
        float $refreshThreshold = 0.8
    ) {
        $value = Redis::get($key);
        $remainingTtl = Redis::ttl($key);
        
        if ($value === null) {
            // Cold cache - synchronous fetch
            $value = $fetcher();
            Redis::setex($key, $ttl, json_encode($value));
            return $value;
        }
        
        // Check if we should refresh in background
        if ($remainingTtl < ($ttl * (1 - $refreshThreshold))) {
            // Dispatch background refresh
            RefreshCacheJob::dispatch($key, $fetcher, $ttl)
                ->delay(now()->addSeconds(rand(0, 60))); // Staggered refresh
        }
        
        return json_decode($value, true);
    }
}
```

---

## 18. Capacity Planning

### 18.1 Traffic Projections

| Metric | Phase 1 (M1-3) | Phase 2 (M4-6) | Phase 3 (M7-12) | Year 2 |
|--------|----------------|----------------|-----------------|--------|
| Tenants | 10 | 50 | 100 | 500 |
| Active Users | 500 | 5,000 | 20,000 | 100,000 |
| Concurrent Users | 50 | 500 | 2,000 | 10,000 |
| API Requests/Day | 50,000 | 500,000 | 2,000,000 | 10,000,000 |
| Cache Operations/Day | 150,000 | 1,500,000 | 6,000,000 | 30,000,000 |

### 18.2 Memory Requirements Calculation

```
Per-Student Memory (Hot Data):
- Profile (Hash): ~500 bytes
- Fee Summary: ~200 bytes
- Attendance (bitmap): ~1250 bytes/month
- Dashboard widgets: ~300 bytes
Total per active student: ~2.25 KB

Phase 1 (500 students):
500 × 2.25 KB = 1.125 MB

Phase 2 (5,000 students):
5,000 × 2.25 KB = 11.25 MB

Phase 3 (20,000 students):
20,000 × 2.25 KB = 45 MB
+ Session storage: 2,000 sessions × 10 KB = 20 MB
+ Queue data: ~10 MB
Total: ~75 MB (with 4GB provisioned)

Year 2 (100,000 students):
100,000 × 2.25 KB = 225 MB
+ Session storage: 10,000 × 10 KB = 100 MB
+ Queue data: ~50 MB
+ Aggregations/Reports: ~200 MB
Total: ~575 MB (with 8GB provisioned)
```

### 18.3 Infrastructure Sizing

| Phase | Redis Instance | Memory | Connection Limit | Network |
|-------|----------------|--------|------------------|---------|
| Phase 1 | DigitalOcean Managed Redis | 2 GB | 10,000 | 1 Gbps |
| Phase 2 | DigitalOcean Managed Redis | 4 GB | 20,000 | 1 Gbps |
| Phase 3 | AWS ElastiCache (cluster) | 16 GB | 65,000 | 10 Gbps |
| Year 2 | AWS ElastiCache (cluster) | 64 GB | 65,000 | 10 Gbps |

### 18.4 Cost Projections

| Phase | Monthly Cost | Notes |
|-------|--------------|-------|
| Phase 1 | ~$15 | DigitalOcean 2GB |
| Phase 2 | ~$60 | DigitalOcean 4GB |
| Phase 3 | ~$200 | AWS ElastiCache (3 nodes) |
| Year 2 | ~$600 | AWS ElastiCache (6 nodes) |

---

## 19. Implementation Roadmap

### 19.1 Phase 1: Foundation (Weeks 1-2)

**Week 1: Infrastructure Setup**
- [ ] Provision Redis instance (DigitalOcean Managed Redis 2GB)
- [ ] Configure Redis security (AUTH, TLS, command renaming)
- [ ] Set up Redis Sentinel for monitoring
- [ ] Configure Laravel Redis connection
- [ ] Implement basic CacheService wrapper

**Week 2: Core Implementation**
- [ ] Implement CachePartitioner with tenant isolation
- [ ] Create CachedStudentRepository
- [ ] Implement tag-based invalidation
- [ ] Add cache metrics collection
- [ ] Write unit tests for cache layer

**Deliverables:**
- Redis connection with tenant-isolated keys
- Student profile caching (read operations)
- Basic invalidation on student updates
- Health check endpoint

### 19.2 Phase 2: Dashboard & Stats (Weeks 3-4)

**Week 3: Dashboard Optimization**
- [ ] Implement dashboard stats caching (5-min TTL)
- [ ] Create bitmap-based attendance storage
- [ ] Add cache warming for daily stats
- [ ] Implement circuit breaker
- [ ] Set up monitoring and alerting

**Week 4: Advanced Features**
- [ ] Implement cache stampede protection
- [ ] Add distributed locking for critical operations
- [ ] Create fee summary caching
- [ ] Implement TTL manager with dynamic adjustment
- [ ] Performance testing and optimization

**Deliverables:**
- <500ms dashboard load time
- 60%+ cache hit rate
- Automatic failover to database
- Complete observability dashboard

### 19.3 Phase 3: Scale & Optimize (Weeks 5-6)

**Week 5: Cluster Preparation**
- [ ] Configure Redis Cluster (3 master + 3 replica)
- [ ] Implement consistent hashing for key distribution
- [ ] Add Redis Pub/Sub for real-time events
- [ ] Create automated cache warming jobs
- [ ] Implement advanced compression

**Week 6: Production Hardening**
- [ ] Security audit and penetration testing
- [ ] Load testing (500+ concurrent users)
- [ ] Disaster recovery procedures
- [ ] Documentation and runbooks
- [ ] Production deployment

**Deliverables:**
- Redis Cluster with auto-failover
- 85%+ cache hit rate
- <200ms API response times
- Complete operational documentation

### 19.4 Migration Strategy

```php
// Gradual rollout with feature flags

class CacheMigration
{
    public function getStudent(int $tenantId, int $studentId, bool $useCache = false)
    {
        if ($useCache && FeatureFlag::isEnabled('cache_student_profiles')) {
            return $this->cachedRepository->find($tenantId, $studentId);
        }
        
        return $this->dbRepository->find($tenantId, $studentId);
    }
}

// Rollout phases:
// 1. 0% - Shadow mode: read from both, compare results, log differences
// 2. 5% - Canary: enable for test tenant only
// 3. 25% - Partial rollout: enabled for starter plan tenants
// 4. 50% - Gradual increase
// 5. 100% - Full rollout with rollback capability
```

---

## Appendix A: Configuration Reference

### A.1 Environment Variables

```env
# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_secure_password
REDIS_PORT=6379
REDIS_PREFIX=hl_

# Cache-specific Redis
REDIS_CACHE_HOST=redis-cache.internal
REDIS_CACHE_PORT=6379

# Session Redis
REDIS_SESSION_HOST=redis-session.internal
REDIS_SESSION_PORT=6380

# Queue Redis
REDIS_QUEUE_HOST=redis-queue.internal
REDIS_QUEUE_PORT=6381

# Cache TTL Defaults
CACHE_TTL_STUDENT_PROFILE=3600
CACHE_TTL_DASHBOARD_STATS=300
CACHE_TTL_ATTENDANCE=14400
CACHE_TTL_FEE_SUMMARY=1800

# Circuit Breaker
CIRCUIT_BREAKER_FAILURE_THRESHOLD=5
CIRCUIT_BREAKER_TIMEOUT=30

# Feature Flags
FEATURE_CACHE_STUDENT_PROFILES=true
FEATURE_CACHE_DASHBOARD=true
FEATURE_CACHE_ATTENDANCE=false
```

### A.2 Laravel Cache Configuration

```php
// config/cache.php

'redis' => [
    'driver' => 'redis',
    'connection' => 'cache',
    'lock_connection' => 'default',
],

'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],
],

// config/database.php

'redis' => [
    'client' => env('REDIS_CLIENT', 'predis'),
    
    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix' => env('REDIS_PREFIX', 'hl_'),
    ],
    
    'clusters' => [
        'cache' => [
            [
                'host' => env('REDIS_CACHE_HOST', '127.0.0.1'),
                'password' => env('REDIS_PASSWORD', null),
                'port' => env('REDIS_CACHE_PORT', 6379),
                'database' => 0,
            ],
        ],
        'session' => [
            [
                'host' => env('REDIS_SESSION_HOST', '127.0.0.1'),
                'password' => env('REDIS_PASSWORD', null),
                'port' => env('REDIS_SESSION_PORT', 6380),
                'database' => 1,
            ],
        ],
    ],
],
```

---

## Appendix B: Monitoring Checklist

### B.1 Daily Checks

- [ ] Cache hit rate > 70%
- [ ] Redis memory usage < 80%
- [ ] No connection errors in logs
- [ ] Average latency < 5ms
- [ ] Circuit breaker status: CLOSED

### B.2 Weekly Checks

- [ ] Eviction rate analysis
- [ ] Keyspace size trends
- [ ] Memory fragmentation ratio
- [ ] Replication lag (if clustered)
- [ ] Hit rate by tenant distribution

### B.3 Monthly Checks

- [ ] Capacity planning review
- [ ] TTL effectiveness analysis
- [ ] Cache warming effectiveness
- [ ] Security audit
- [ ] Failover procedure test

---

## Document Information

- **Version:** 1.0
- **Author:** HamroLabs Engineering Team
- **Date:** March 2026
- **Review Cycle:** Quarterly
- **Status:** Draft - Pending Technical Review

---

**Next Steps:**
1. Technical team review and feedback
2. Proof-of-concept implementation for student profile caching
3. Performance baseline measurement
4. Staged rollout plan execution
