# HamroLabs Academic ERP - Documentation Summary

This document summarizes the content from the following PDF files:
1. **FINAL PRD TECH ROLES.pdf** - Product Requirements Document v3.0 (Production Blueprint)
2. **HamroLabs_SRS_v1.0 - for merge.pdf** - Software Requirements Specification v1.0

---

## 1. Project Overview

**Project Name:** HamroLabs Academic ERP  
**Version:** V3.0 / SRS v1.0  
**Type:** Cloud-Based Multi-Tenant SaaS ERP Platform  
**Target Market:** Loksewa (PSC) preparation institutes, coaching centers, and academic institutions in Nepal  
**Vision:** To become Nepal's most trusted, widely-adopted, and technically superior cloud-based Academic ERP platform  

**Mission:** Eliminate operational chaos in Nepal's Loksewa preparation institutes by delivering a unified, cloud-native platform managing every touchpoint - from student admission to exam results, fee collection to teacher payroll.

**Tagline:** "Empowering Every Institute. Digitally."

---

## 2. Technology Stack

### Backend
| Layer | Technology |
|-------|------------|
| Application Framework | PHP 8.2 + Laravel 11 |
| API Authentication | Laravel Sanctum + JWT (8hr access + 30-day refresh) |
| Report Engine | Python 3.11 (openpyxl, reportlab, weasyprint, pandas) |
| Queue System | Laravel Queues + Redis + Supervisor |
| Cache Layer | Redis |
| SMS Gateway | Sparrow SMS (primary) + Aakash SMS (failover) |
| Email Service | Mailgun (primary) / SendGrid (fallback) |
| File Storage | AWS S3 / Wasabi (S3-compatible) |
| File Security | ClamAV virus scan on upload |

### Frontend
| Layer | Technology |
|-------|------------|
| UI Framework | Bootstrap 5.3 + Alpine.js + Vanilla JS |
| Templating | Laravel Blade |
| PWA Layer | Service Workers + Web App Manifest |
| Typography | Noto Sans + Noto Sans Devanagari |
| Date Handling | bikram-sambat JS library (BS/AD conversion) |
| Charts | Chart.js 4.x |

---

## 3. Architecture

### Multi-Tenancy Model
- **Shared-Infrastructure, Isolated-Data** multi-tenancy
- All institutes (tenants) run on shared application and database
- Strict logical data isolation via non-nullable `tenant_id` foreign key on every table
- Laravel Global Scope enforces data isolation at ORM layer

### Hosting Architecture
| Phase | Infrastructure | Cost |
|-------|---------------|------|
| Phase 1-2 | DigitalOcean (4 vCPU / 8GB RAM / 160GB SSD) | ~$100-150/month |
| Phase 3 | AWS EC2 Auto-Scaling + RDS Multi-AZ | Scale-based |

---

## 4. User Roles (6 Roles)

| Role | Scope | Primary Function |
|------|-------|------------------|
| Super Admin | Platform-wide | Platform management, billing, support (2FA mandatory) |
| Institute Admin | Own tenant | Full institute operations (2FA mandatory) |
| Front Desk | Own tenant | Admissions, fee collection |
| Teacher | Assigned batches | Academic delivery, exams |
| Student | Own record | Learning, exams, fee view |
| Guardian | Linked child only | Monitor child's progress (read-only) |

---

## 5. Core Modules

### Phase 1 Features (Months 1-3)
- [x] Multi-tenant infrastructure with subdomain routing
- [x] JWT Authentication (6-role RBAC)
- [x] Student admission with Nepali fields (citizenship, BS DOB, etc.)
- [x] Student ID card PDF generation (Python reportlab)
- [x] Course and batch management
- [x] Attendance marking (teacher PWA mobile)
- [x] Fee management (items, installments, payment recording)
- [x] PDF receipt generation (Python weasyprint)
- [x] SMS integration (Sparrow + Aakash failover)
- [x] Admin and Student dashboards
- [x] PWA shell with offline capability

### Phase 2 Features (Months 4-6)
- [ ] Timetable builder with drag-and-drop
- [ ] Academic calendar (BS/AD dual display)
- [ ] MCQ exam engine with question bank
- [ ] LMS (study materials, assignments, online classes)
- [ ] Library module
- [ ] Teacher module (salary, subject allocation)
- [ ] Guardian portal
- [ ] Python Excel reports
- [ ] Email integration (Mailgun)
- [ ] eSewa/Khalti payment gateway

### Phase 3 Features (Months 7-12)
- [ ] Video lecture management (HLS streaming)
- [ ] AI weak-subject identification
- [ ] AI performance prediction
- [ ] Multi-branch support
- [ ] Nepali UI localization
- [ ] Bulk data import tools
- [ ] Public REST API
- [ ] Advanced analytics
- [ ] WhatsApp chatbot

---

## 6. Database Overview

### Core Tables
- `tenants` - Root record per institute
- `users` - Authentication for all 6 roles
- `students` - Full Nepali admission form data
- `guardians` - Linked to students
- `courses` - Course catalogue with Loksewa categories
- `batches` - Course batches with enrollment
- `batch_students` - Student enrollment junction
- `teachers` - Teacher profiles and employment
- `batch_subject_allocations` - Teacher→subject→batch
- `timetable_slots` - Weekly timetable
- `attendance` - Per-student attendance
- `fee_items` - Fee catalogue
- `fee_records` - Installment billing with receipts
- `exams` - Exam configuration
- `questions` - Question bank
- `exam_questions` - Exam-question junction
- `exam_attempts` - Student exam attempts
- `assignments` - Homework/assignments
- `assignment_submissions` - Student submissions
- `study_materials` - LMS materials library
- `library_books` - Book catalogue
- `library_issues` - Book lending
- `notifications` - In-app notifications
- `sms_logs` - SMS audit trail
- `audit_logs` - Admin action audit trail

---

## 7. Security Features

- HTTPS with TLS 1.3 minimum
- JWT: 8-hour access + 30-day refresh with rotation
- Passwords: bcrypt hashed (cost factor 12+)
- SQL Injection: Laravel Eloquent ORM exclusively
- XSS Prevention: Server-side sanitization + CSP headers
- File uploads: MIME validation + ClamAV scan
- Rate limiting: 5 failed attempts per IP per 15 minutes
- 2FA: OTP SMS mandatory for Super Admin and Institute Admin
- Tenant isolation: Global Scope + middleware
- Student PII encryption: AES-256 for citizenship, national ID, address
- Document storage: Private S3 bucket with 15-minute signed URLs
- Admin audit logs: Immutable, full action tracking

---

## 8. Subscription Plans

| Feature | Starter | Growth | Professional | Enterprise |
|---------|---------|--------|--------------|------------|
| Student Limit | Up to 150 | Up to 500 | Up to 1,500 | Unlimited |
| Admin Accounts | 1 | 3 | 10 | Unlimited |
| Fee Management | ✅ | ✅ | ✅ | ✅ + eSewa/Khalti |
| Attendance Tracking | ✅ | ✅ | ✅ | ✅ + Biometric |
| Exam/Mock Test | ❌ | ✅ | ✅ | ✅ + AI |
| LMS | ❌ | ✅ | ✅ | ✅ + Video |
| Library Module | ❌ | ✅ | ✅ | ✅ |
| Python Excel Reports | ❌ | ❌ | ✅ | ✅ |
| SMS/Month | 500 | 2,000 | 5,000 | Custom |
| Guardian Portal | ❌ | ✅ | ✅ | ✅ |
| API Access | ❌ | ❌ | ✅ | ✅ + Dedicated |
| White-label | ❌ | ❌ | Add-on | ✅ |
| Uptime SLA | 99.5% | 99.5% | 99.7% | 99.9% |

---

## 9. Key Features for Nepal Context

- ✅ Nepal-specific design (Loksewa, PSC, TSC exam flows)
- ✅ Multi-tenant cloud (shared infrastructure)
- ✅ PWA mobile-first (works offline, installable)
- ✅ Nepali language support (Devanagari Unicode)
- ✅ BS/AD dual calendar support throughout
- ✅ Online mock exams (MCQ engine with auto-evaluation)
- ✅ SMS/Email automation (dual-gateway)
- ✅ Python report engine (professional Excel/PDF)
- ✅ First-mover in Nepal's Loksewa ERP vertical

---

## 10. Development Team Estimates

### Phase 1 (Months 1-3)
| Role | Count | Responsibility |
|------|-------|----------------|
| Senior PHP/Laravel Developer | 1 | Architecture, auth, multi-tenant core |
| Junior PHP Developer | 1 | Fee, attendance, student CRUD |
| Frontend Developer | 1 | PWA shell, Bootstrap UI, dashboards |
| Python Developer | 0.5 (part-time) | PDF receipts, ID cards, bridge setup |
| QA / DevOps | 1 (shared) | Server setup, CI/CD, testing |

**Target:** 5-10 beta institutes live by end of Phase 1

### Phase 2 (Months 4-6)
| Role | Count |
|------|-------|
| Senior PHP/Laravel Developer | 1 |
| Junior PHP Developer | 1 |
| Python Developer | 1 |
| Frontend Developer | 1 |
| QA Engineer | 1 |

### Phase 3 (Months 7-12)
| Role | Count |
|------|-------|
| Senior PHP Developer | 1 |
| Python/ML Developer | 1 |
| DevOps Engineer | 1 |
| Frontend Developer | 1 |
| QA Engineer | 1 |

**Target:** 100+ paying institutes by Month 12

---

## 11. Project Folder Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── SuperAdmin/
│   │   ├── Admin/
│   │   ├── FrontDesk/
│   │   ├── Teacher/
│   │   ├── Student/
│   │   ├── Guardian/
│   │   └── Api/
│   ├── Middleware/
│   │   ├── IdentifyTenant.php
│   │   ├── EnsureTenantActive.php
│   │   ├── RoleMiddleware.php
│   │   └── CheckPlanFeature.php
│   └── Requests/
├── Models/
├── Traits/
│   └── BelongsToTenant.php
├── Services/
├── Jobs/
└── Policies/
database/
├── migrations/
└── seeders/
resources/
├── views/
│   ├── layouts/ (6 role-specific layouts)
│   ├── super-admin/
│   └── admin/
└── js/
routes/
├── web.php
└── api.php
config/
├── sms.php
└── hamrolabs.php
```

---

## 12. Important Implementation Notes

### Golden Rules (from SRS)
- Use Laravel conventions - don't fight the framework
- Use Eloquent ORM exclusively - no raw SQL
- Use Queue for all background tasks
- Use Form Requests for validation
- Use Policies for authorization
- Services layer: all business logic in app/Services/

### Migration Order (Must Follow)
1. tenants → 2. users → 3. courses → 4. batches → 5. students → 6. guardians → 7. fee_items → 8. fee_records → 9. attendances → 10. teachers → 11. batch_subject_allocations → 12. timetable_slots → 13. exams → 14. questions → 15. exam_questions → 16. exam_attempts → 17. assignments → 18. assignment_submissions → 19. study_materials → 20. library_books → 21. library_issues → 22. notifications → 23. sms_logs → 24. audit_logs

### Key Middleware
- **IdentifyTenant.php** - Reads subdomain → sets tenant context
- **EnsureTenantActive.php** - Blocks suspended/expired tenants
- **RoleMiddleware.php** - Blocks wrong roles from wrong routes
- **CheckPlanFeature.php** - Blocks features not in subscription plan

---

## 13. Conflict Resolution (V1 vs V2)

| Area | V1 | V2 | V3 Resolution |
|------|----|----|---------------|
| Database Strategy | Separate schema per tenant | Shared tables + tenant_id | Shared tables with tenant_id |
| Frontend Framework | Vue.js + Bootstrap | Bootstrap 5.3 + Vanilla JS | Bootstrap 5.3 + Alpine.js |
| JWT Token Expiry | 24-hour access | 8-hour + 30-day refresh | 8-hour + 30-day refresh |
| User Roles Count | 6 (Guardian distinct) | 5 (Guardian merged) | 6 (Guardian distinct) |
| LMS Video | Zoom/Google Meet | Jitsi Meet | Configurable (Jitsi default) |
| Report Generation | PHP DomPDF | Python engine | Python for complex, PHP for receipts |
| Hosting | DigitalOcean OR AWS | AWS Lightsail OR DigitalOcean | DigitalOcean Phase 1-2, AWS Phase 3 |
| Backup Frequency | Daily | Every 6 hours | Every 6 hours |
| Performance Target | <2s on 4G | <3s on 3G | <3s on 3G |

---

## 14. Non-Functional Requirements

### Performance
- Page load: <3s on 3G, <1s repeat visit (PWA cache)
- API response (p95): <500ms
- Report generation: <30 seconds
- SMS delivery: <10 seconds
- Exam result calculation: <5 seconds

### Availability
- Starter/Growth: 99.5% uptime
- Professional: 99.7% uptime
- Enterprise: 99.9% uptime with 4-hour response SLA

### Scalability
- 10 institutes: Single Droplet
- 100 institutes: 2× Droplets + Load Balancer
- 1,000 institutes: AWS Auto-Scaling + RDS Multi-AZ

### Usability
- Mobile-first: 375px minimum viewport
- Touch targets: 44×44px minimum
- Offline capability via PWA
- Nepali language (Devanagari) support

---

## 15. Document Information

| Document | Version | Date |
|----------|---------|------|
| PRD V1.0 | Business Scope | 2025 |
| PRD V2.0 | Technical Architecture | 2025 |
| PRD V3.0 | Merged & Finalized | 2025 |
| SRS v1.0 | This Document | 2025 |

**Prepared By:** Hamro Labs Product & Engineering Team  
**Classification:** Confidential — Internal Development Use  
**Base Documents:** PRD V3.0 + Project Setup Guide v1.0

---

*End of Documentation Summary*
