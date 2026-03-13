# Market Fit Analysis: ERP Expansion for Nepal's Training Institute Ecosystem

**Document Version:** 1.0  
**Prepared For:** Hamro Labs Academic ERP Expansion Strategy  
**Date:** March 2026  
**Classification:** Strategic Planning — Product Development

---

## Executive Summary

Hamro Labs Academic ERP currently serves 5,000+ Loksewa preparation institutes in Nepal with a purpose-built, localized SaaS platform. This analysis identifies significant market opportunities in expanding to **Computer Training Institutes**, **Bridge Course Centers**, **Tuition Centers**, and **Skill Training Institutes** — segments that share operational DNA with Loksewa institutes but require specific feature adaptations.

The Nepal post-SEE education market represents a **NPR 2-5 billion annual opportunity** across 8,000+ private training centers. With 400,000+ students transitioning through SEE annually and seeking short-term skill development before +2 enrollment, the demand for institutional management software is acute.

**Key Opportunity:** Current ERP competitors (Vedmarg, Pathami, ProSchool) offer generic school management systems not optimized for the unique workflows of training institutes. Hamro Labs' vertical-specific approach positions it to capture this underserved market.

---

## 1. Current ERP System Analysis

### 1.1 System Overview

**Hamro Labs Academic ERP V3.0** is a cloud-based, multi-tenant SaaS platform built specifically for Nepal's Loksewa preparation ecosystem.

**Tech Stack:**
- **Backend:** PHP 8.2 (Laravel 11), Python 3.11 (Reports/Analytics)
- **Database:** MySQL 8.0, Redis (caching/sessions)
- **Frontend:** Progressive Web App (PWA) — offline-capable, installable
- **Infrastructure:** Multi-tenant architecture, tenant isolation via subdomain

**Database:** 70 tables supporting complete institute lifecycle management

### 1.2 Current Target Market

**Primary Market (MVP Focus):**

| Segment | Characteristics | Est. Count | Priority |
|---------|----------------|------------|----------|
| Small Institutes | 1-3 classrooms, 50-200 students, owner-operated | 3,000+ | Primary |
| Mid-Size Institutes | 4-10 batches, 200-800 students, dedicated staff | 800+ | Primary |
| Multi-Branch | Franchise model, 3+ locations, 1,000+ students | 100+ | Phase 3 |
| Online-Hybrid | Blended delivery, video lectures, national reach | 200+ | Phase 2 |

**Exam Categories Supported:**
- Loksewa (PSC): Kharidar, Nayab Subba, Section Officer, PSC Engineering, Health, Forestry, Agriculture, Education
- Staff Nurse / AHW / Health exam preparation centers
- Banking & Finance exam coaching institutes
- TSC (Teaching Service Commission) preparation centers

**Course Categories in Database:**
```sql
category ENUM('loksewa','health','banking','tsc','general','engineering')
```

### 1.3 Current Modules & Capabilities

**Core Modules (Fully Implemented):**

1. **Multi-Tenant Management**
   - Subdomain-based tenant isolation
   - Tenant-specific branding (logo, colors, tagline)
   - Plan-based feature access (starter/growth/professional/enterprise)
   - Province-based localization support

2. **Student Lifecycle Management**
   - Inquiry tracking with followup system
   - Admission workflow with document upload
   - Roll number auto-generation
   - Guardian/parent linking
   - Bikram Sambat (BS) date of birth support
   - Academic qualification tracking
   - Photo and identity document storage

3. **Course & Batch System**
   - Course creation with code, fee, duration, category
   - Batch management (morning/day/evening shifts)
   - Enrollment tracking (active/completed/dropped/transferred)
   - Max strength capacity limits
   - Room allocation

4. **Attendance System**
   - Daily attendance marking (present/absent/late/leave)
   - Teacher-marked via mobile PWA
   - Attendance locking to prevent retroactive changes
   - Audit trail for attendance modifications
   - Batch-level and student-level attendance reports

5. **Fee Management**
   - Flexible fee items (admission/monthly/exam/material/fine/other)
   - Installment support
   - Late fine calculation (per-day basis)
   - Payment receipt generation (PDF)
   - Fee ledger and student fee summary
   - Outstanding dues tracking
   - Payment transaction history

6. **Teacher Management**
   - Employee ID assignment
   - Subject allocation to batches
   - Monthly salary tracking
   - Leave balance management
   - Teacher-specific portal access

7. **Examination System**
   - Online MCQ exam engine
   - Question bank management (teacher submission + admin approval)
   - Exam scheduling for batches
   - Auto-evaluation and ranking
   - Exam attempt tracking
   - Result analytics

8. **Assignment & Homework**
   - Assignment creation with due dates and max marks
   - File attachment support
   - Student submission tracking
   - Late submission flagging
   - Grading and feedback system

9. **Study Materials**
   - Category-based material organization
   - Batch-specific or course-wide materials
   - PDF/video/document uploads
   - Access control and permissions
   - Material download tracking
   - Student favorites

10. **Communication System**
    - SMS automation (via Sparrow/Aakash gateways)
    - Email automation
    - Template-based messaging
    - Automated notifications (fee due, exam, attendance)
    - Notice board system
    - Individual and broadcast messaging

11. **Timetable Management**
    - Slot-based scheduling
    - Batch and teacher timetables
    - Room allocation visualization

12. **Library Management**
    - Book catalog
    - Issue/return tracking
    - Student borrowing limits

13. **Reporting & Analytics**
    - Python-powered Excel/PDF report engine
    - Financial reports (collections, outstanding dues)
    - Academic reports (attendance, results)
    - Monthly target tracking
    - Custom report generation

14. **Platform Administration**
    - Super Admin dashboard
    - Tenant subscription management
    - Platform-wide announcements
    - Support ticket system
    - Audit logging
    - Failed login tracking

### 1.4 Current Workflow (Loksewa Institute Typical Flow)

```
1. INQUIRY → Student expresses interest (walk-in, phone, Facebook)
   ↓
2. FOLLOWUP → Front desk logs inquiry, schedules counseling
   ↓
3. ADMISSION → Student pays admission fee, provides documents
   ↓
4. COURSE ENROLLMENT → Student selects Loksewa category (e.g., Nayab Subba)
   ↓
5. BATCH ALLOCATION → Assigned to morning/day/evening batch
   ↓
6. ATTENDANCE → Daily marking by teacher
   ↓
7. FEE COLLECTION → Monthly installments, late fine tracking
   ↓
8. STUDY MATERIALS → Access to PDF notes, video lectures
   ↓
9. MOCK EXAMS → Online MCQ tests with ranking
   ↓
10. COMPLETION → Course duration ends, student sits for PSC exam
```

### 1.5 System Strengths (Competitive Advantages)

**Nepal-Specific Localization:**
- Bikram Sambat (BS) calendar native support throughout system
- Nepali language UI elements
- Province/district/municipality dropdowns
- Citizenship number, PAN number fields
- SMS Unicode support for Nepali messages via NTC/Ncell gateways

**Mobile-First Design:**
- Offline-capable PWA for unreliable 3G/4G connectivity
- Teacher attendance marking works offline, syncs when online
- Installable on mobile home screen (no app store required)

**Loksewa-Optimized Features:**
- Exam categories embedded (Kharidar, Nayab Subba, Section Officer, etc.)
- Question bank for MCQ-heavy preparation
- Ranking and percentile tracking (competitive exam focus)
- Study material permissions (batch-based access control)

**Cost-Effective Infrastructure:**
- Multi-tenant shared infrastructure reduces per-institute cost
- Python report engine far superior to PHP-only alternatives
- Redis caching for performance on budget hosting

### 1.6 Current Limitations for Expansion

**Workflow Assumptions:**
1. **Exam-Centric Model:** System assumes students are preparing for a final government exam (PSC, TSC, Banking). No support for skill-based learning outcomes or competency tracking.

2. **Long-Term Enrollment:** Designed for 3-6 month courses. No optimization for ultra-short courses (1-2 weeks bridge courses) or year-long tuition.

3. **No Certificate Management:** Loksewa students don't receive institute certificates (they sit for PSC exams). Missing automated certificate generation, which is critical for computer training and skill institutes.

4. **No Lab/Practical Session Tracking:** Assumes classroom theory. No support for computer lab sessions, practical hours, or hands-on training tracking.

5. **Limited Course Flexibility:** Fixed fee structure doesn't accommodate modular courses (e.g., "Basic Computer + Tally + Photoshop" package deals).

6. **No CTEVT Integration:** Skill training centers require CTEVT registration forms and compliance. System lacks export formats for government-mandated documentation.

7. **Single-Stream Focus:** Designed for one primary category (Loksewa). Bridge courses require parallel streams (Science, Management, Humanities) with different syllabi.

8. **No Entrance Exam Preparation Features:** Bridge courses focus heavily on entrance exam prep for prestigious +2 colleges. Missing model question management and college-specific preparation tools.

---

## 2. Target Institute Segments: Operational Analysis

### 2.1 Institute Type Comparison Matrix

| Feature | Loksewa Institute | Computer Training | Bridge Course | Tuition Center | Skill Training (CTEVT) |
|---------|-------------------|-------------------|---------------|----------------|------------------------|
| **Typical Duration** | 3-6 months | 1-6 months | 2-3 months | Ongoing (monthly) | 1-12 months |
| **Peak Season** | Year-round (PSC schedule-driven) | Post-SEE surge (April-June) | Post-SEE (April-June) | Academic year-round | Year-round |
| **Student Volume** | 50-500 per batch cycle | 100-300 per cycle | 500-1,000 (seasonal spike) | 20-100 ongoing | 50-200 per cycle |
| **Fee Collection** | Upfront + installments | Package fees, often upfront | Lump sum at admission | Monthly recurring | Government-subsidized + student portion |
| **Primary Output** | Exam readiness (PSC pass rate) | Certificates, skill badges | +2 entrance exam success | School grade improvement | CTEVT certificates, job placement |
| **Attendance Criticality** | Medium (mock exam focus) | High (practical hours mandatory) | Very High (entrance exam discipline) | Medium (parent-driven) | Critical (government reporting) |
| **Curriculum Type** | Exam syllabus-based | Modular, skill-based | Subject-based (PCM, BBS) | School textbook-aligned | Government-standardized (160/390/780 hrs) |
| **Teacher Expertise** | Exam pattern specialists | Industry practitioners | Academic subject masters | School teachers, tutors | CTEVT-certified trainers |
| **Assessment Method** | MCQ mock tests, ranking | Practical evaluations, projects | Written tests, model questions | Monthly tests, assignments | Competency-based, practical exams |
| **Certificate Need** | None (PSC provides) | **Critical** (job requirement) | None (entrance is goal) | Optional (merit certificates) | **Mandatory** (CTEVT-issued) |
| **Compliance** | Minimal (private coaching) | Tax compliance (PAN/VAT) | Minimal | Minimal | **Heavy** (CTEVT registration, renewals, reporting) |

### 2.2 Computer Training Institutes

**Market Context:**  
Computer literacy is now a baseline requirement for government jobs (Computer Operator positions), banking sector employment, and administrative roles. The demand surge post-SEE is driven by students using the 3-month gap to acquire marketable skills.

**Typical Institute Profile:**
- **Location:** Urban hubs (Kathmandu, Pokhara, Chitwan, Biratnagar, Butwal)
- **Infrastructure:** 10-30 workstations in computer lab(s)
- **Staff:** 2-5 instructors (often outsourced for specialized courses)
- **Student Capacity:** 100-300 students per quarter across multiple batches
- **Fee Range:** NPR 4,000 (1-month Basic Computer) to NPR 50,000 (6-month Diploma)

**Course Categories:**

| Course Type | Duration | Fee (NPR) | Target Audience |
|-------------|----------|-----------|-----------------|
| **Basic Computer** (MS Office, Internet, Email) | 1-2 months | 4,000-8,000 | SEE graduates, general public |
| **Tally Prime** (Accounting) | 1.5-2 months | 12,000-18,000 | Commerce students, accountants |
| **Graphic Design** (Photoshop, Illustrator) | 2-3 months | 15,000-25,000 | Creative professionals |
| **Web Development** (HTML, CSS, JavaScript) | 3-4 months | 25,000-40,000 | Tech career aspirants |
| **Digital Marketing** (SEO, Social Media) | 1-2 months | 15,000-20,000 | Entrepreneurs, marketers |
| **Python Programming** | 3-6 months | 30,000-50,000 | Engineering students |
| **Complete Package** (Office + Tally + Design) | 6 months | 35,000-50,000 | SEE graduates (comprehensive skill) |

**Operational Characteristics:**

1. **Modular Course Structure:** Students often enroll in multiple courses sequentially or parallel. Package deals are common (e.g., "Office + Tally combo").

2. **Lab Session Management:** Classes are 60-90 minutes, conducted in computer labs. Each student assigned to a specific workstation. Practical hours tracking is critical (students need X hours of hands-on practice for certificate validity).

3. **Batch Scheduling Complexity:** Multiple batches running simultaneously across different courses. A single lab room might host "Tally Morning Batch" and "Photoshop Evening Batch" back-to-back.

4. **Assessment:** Practical exams (e.g., "Create a company balance sheet in Tally," "Design a poster in Photoshop"). Grading based on project completion, not MCQ tests.

5. **Certificate Issuance:** **Critical pain point.** Institutes manually create certificates in MS Word, typing each student's name, course name, grade, dates. High error rate, time-consuming. Students expect certificates within 1 week of course completion.

6. **Job Placement Tracking:** Some institutes advertise job placement. Tracking which students got placed and in which companies is a secondary feature need.

**Key Pain Points:**
- ❌ **No lab/workstation allocation system**
- ❌ **No practical hours tracking (only class attendance)**
- ❌ **No project/assignment submission system**
- ❌ **No automated certificate generation** (biggest complaint from institute owners)
- ❌ **No modular course package management** (e.g., student enrolls in 3 courses, one fee structure)
- ❌ **No skill/competency tracking** (e.g., "Can use Pivot Tables in Excel")

### 2.3 Bridge Course Institutes

**Market Context:**  
The SEE-to-+2 transition is a high-stakes academic bottleneck. Prestigious +2 colleges (St. Xavier's, Budhanilkantha, DAV) receive 5,000+ applications for 200 seats. Entrance exam scores determine both admission and scholarship eligibility. Bridge courses act as "academic bootcamps" to help students compete.

**Typical Institute Profile:**
- **Seasonality:** **Ultra-seasonal.** 90% of revenue comes in April-June (post-SEE results). Near-zero enrollment July-March.
- **Student Volume:** 500-1,500 students in a 3-month window, then dormant
- **Batch Size:** Large batches (40-60 students) to maximize instructor efficiency
- **Fee Structure:** Upfront package fee (NPR 15,000-30,000 for 10-12 week program)
- **Infrastructure:** 3-8 classrooms, rapid scaling in peak season

**Course Streams:**

| Stream | Target Students | Syllabus Focus | Fee (NPR) |
|--------|----------------|----------------|-----------|
| **Science Bridge** | Future Science students | Physics, Chemistry, Biology, Advanced Math | 25,000-30,000 |
| **Management Bridge** | Future BBS students | Accountancy, Economics, Business Math | 18,000-25,000 |
| **Entrance Preparation** (College-Specific) | Top-tier college aspirants | Past papers, model questions, competitive drills | 15,000-20,000 |

**Operational Characteristics:**

1. **Stream-Based Curriculum:** Science bridge students study PCM/PCB (Physics, Chemistry, Math, Biology). Management students study different subjects. Requires separate syllabi and teaching schedules.

2. **Entrance Exam Focus:** Primary goal is not "passing" but ranking in competitive entrance exams. Institutes track which students got into which colleges and at what scholarship level (full, 75%, 50%).

3. **Intensive Daily Schedule:** Classes often run 6-8 hours/day, Monday-Saturday. Mock exams every weekend. High-pressure environment.

4. **Result Tracking:** **Critical.** Parents choose institutes based on previous year success rate ("85% of our students got into Xavier's"). Institute needs to track:
   - Student's entrance exam score
   - College admitted to
   - Scholarship level received

5. **Subject Allocation:** Different teachers for Physics, Chemistry, Math, Biology. Need teacher-subject-batch allocation system.

6. **Model Question Management:** Institutes maintain libraries of previous entrance exam questions organized by college and year. Students practice these repeatedly.

**Key Pain Points:**
- ❌ **No multi-stream support** (Science vs Management require different syllabi)
- ❌ **No entrance exam result tracking** (which college, scholarship level)
- ❌ **No model question bank system** (different from general question bank)
- ❌ **No subject-teacher allocation** (current system is batch-teacher only)
- ❌ **No seasonal revenue forecasting** (90% revenue in 3 months creates cash flow planning issues)
- ❌ **No scholarship tier tracking**

### 2.4 Tuition Centers (Home Tutoring / Coaching Classes)

**Market Context:**  
Small-scale tutoring operations run by individual teachers or retired educators. Focus on school-level students (Grade 6-10) needing help with weak subjects (Math, Science, English, Nepali). More informal than institutes, but still need basic management tools.

**Typical Profile:**
- **Scale:** Very small (10-50 students at a time)
- **Location:** Teacher's home, rented room, or community space
- **Staff:** 1-3 teachers (often the owner teaches)
- **Fee:** Monthly recurring (NPR 1,500-5,000 per subject)
- **Infrastructure:** Single room, whiteboard, basic furniture

**Course Structure:**
- **Subject-wise enrollment:** Student enrolls in "Math tuition" or "Science tuition," not full courses
- **Grade-based batching:** Grade 8 Math batch, Grade 10 Science batch
- **Flexible timing:** Evening classes (4-7 PM) after school hours
- **Parent-driven:** Parents pay, monitor progress closely

**Operational Characteristics:**

1. **Subject-Wise Fee Collection:** Unlike course-based institutes, tuition centers charge per subject per month. A student might attend "Math + Science" and pay for both.

2. **Monthly Recurring Revenue:** Parents pay monthly, not upfront packages. High dropout risk if student's school grades don't improve.

3. **Low Administrative Overhead:** Owners want minimal software complexity. Key needs: attendance, monthly fee tracking, SMS reminders.

4. **Parent Communication:** Heavy emphasis on keeping parents informed (monthly progress reports, exam result SMS).

5. **Seasonal Slowdown:** Enrollment drops during school vacations, spikes before board exams.

**Key Pain Points:**
- ❌ **No subject-based enrollment** (current system is course-based)
- ❌ **No monthly recurring fee automation** (current system assumes package fees)
- ❌ **No grade-level tracking** (Grade 6, 7, 8... needs to be structured)
- ❌ **No parent report card generation** (monthly progress reports)
- Current system is **over-engineered** for tuition centers (too many features they don't need)

**Product Strategy Note:**  
Tuition centers may need a **simplified "Lite" version** of the ERP, not the full platform. Consider a separate pricing tier with limited modules.

### 2.5 Skill Training Institutes (CTEVT-Affiliated)

**Market Context:**  
The Council for Technical Education and Vocational Training (CTEVT) governs vocational skill training in Nepal. Institutes affiliated with CTEVT offer standardized short-term courses (e.g., Caregiver, Cook, Mason, Electrician) and diploma programs (e.g., Diploma in Computer Engineering). These courses are **government-recognized** and often required for foreign employment (Middle East, Malaysia labor migration).

**Typical Profile:**
- **Affiliation:** Must be CTEVT-registered and renewed annually
- **Courses:** 160-hour, 390-hour, or 780-hour programs (government-standardized)
- **Students:** 50-200 per year across multiple trades
- **Fee:** Partially subsidized by government, partially paid by students
- **Infrastructure:** Workshops, labs, practical training areas

**Course Examples:**

| Trade | Duration (Hours) | CTEVT Level | Typical Fee (NPR) |
|-------|------------------|-------------|-------------------|
| Caregiver | 390 | Short-term | 15,000-25,000 |
| Cook / Baker | 780 | Short-term | 20,000-30,000 |
| Electrician | 780 | Diploma | 40,000-60,000 |
| Plumber | 390 | Short-term | 15,000-20,000 |
| Mason | 390 | Short-term | 12,000-18,000 |
| Computer Operator | 390 | Short-term | 15,000-25,000 |
| Diploma in Civil Engineering | 3,000+ | Diploma (3 years) | 80,000-120,000 |

**Operational Characteristics:**

1. **Hour-Based Curriculum:** CTEVT mandates specific hours of instruction (e.g., 390 hours = ~3 months). Institutes must track and report total hours delivered, not just days attended.

2. **Competency-Based Assessment:** Students are evaluated on practical skills (e.g., "Can wire a circuit board," "Can bake a cake to standard"). Not traditional exam scores.

3. **Government Reporting:** CTEVT requires:
   - Student registration forms (specific format)
   - Attendance records (minimum 80% required for certificate)
   - Instructor qualifications documentation
   - Annual renewal forms

4. **Certificate Issuance:** CTEVT issues the official certificate, but institute must submit student completion data in government-approved format.

5. **Job Placement Tracking:** Many CTEVT courses are employment-focused (migration, local jobs). Institutes track placement rates for marketing.

**Key Pain Points:**
- ❌ **No hour-based tracking** (current system tracks days, not hours per course)
- ❌ **No CTEVT form export** (registration, renewal, completion forms must be manually filled)
- ❌ **No competency-based grading** (current system is exam-score based)
- ❌ **No government compliance module** (annual renewals, instructor qualification storage)
- ❌ **No migration documentation support** (students need specific certificates for foreign employment)

---

## 3. Student Lifecycle Comparison

### 3.1 Universal Lifecycle Stages (Common Across All Institutes)

These stages are **already well-supported** in the current Hamro Labs ERP:

1. **Inquiry/Lead Management** ✅
   - Walk-in, phone, social media lead capture
   - Followup scheduling and tracking
   - Conversion funnel analytics

2. **Admission** ✅
   - Student registration form
   - Document upload (photo, ID proof)
   - Admission fee payment
   - Roll number generation

3. **Batch Allocation** ✅
   - Assign student to specific batch
   - Shift selection (morning/day/evening)
   - Capacity limit enforcement

4. **Attendance Tracking** ✅
   - Daily marking by teacher
   - Absent/Present/Late/Leave status
   - Attendance percentage calculation

5. **Fee Collection** ✅
   - Payment recording
   - Receipt generation
   - Outstanding dues tracking
   - Late fine calculation

6. **Communication** ✅
   - SMS/Email notifications
   - Automated reminders (fee due, exam, etc.)
   - Notice board announcements

7. **Completion/Exit** ⚠️ Partial
   - Current system tracks enrollment status (completed/dropped)
   - **Missing:** Certificate generation (critical gap)

### 3.2 Segment-Specific Lifecycle Requirements

#### Computer Training Institutes

**Additional Stages:**
- **Course Selection** → Student may enroll in multiple courses (Basic + Tally + Design). Need **modular enrollment** support.
- **Lab Session Allocation** → Assign student to specific workstation/PC for duration of course.
- **Practical Hours Tracking** → Government-recognized certificates require proof of X hours of hands-on practice.
- **Project Submission** → Final project (e.g., "Build a website") submission and grading.
- **Certificate Generation** → **Automated certificate with QR code**, student name, course name, grade, dates.
- **Post-Completion Job Tracking** → (Optional) Track which students got employed, where.

#### Bridge Course Institutes

**Additional Stages:**
- **Stream Selection** → Science / Management / Humanities — determines subject set.
- **Entrance Exam Result Tracking** → After course completion, record which college admitted student and at what scholarship level.
- **Model Question Practice Tracking** → Track which model question sets student has completed (college-wise, year-wise).
- **Weekly Mock Exam** → Frequent exams (every weekend), ranking within batch.
- **College Application Support** → Some institutes help with application forms (track application status).

#### Tuition Centers

**Additional Stages:**
- **Subject Enrollment** → Student enrolls in specific subjects (Math, Science), not full courses.
- **Monthly Progress Reporting** → Generate parent report cards showing attendance, test scores, weak areas.
- **School Exam Correlation** → Track student's school exam results to measure tuition effectiveness.

#### Skill Training (CTEVT)

**Additional Stages:**
- **CTEVT Registration** → Submit student data to CTEVT in government-mandated format.
- **Hour-Based Progress** → Track total instructional hours delivered per student (not just days).
- **Competency Assessment** → Record practical skill evaluations (Pass/Fail per competency unit).
- **CTEVT Certificate Coordination** → Submit completion data to CTEVT, receive certificate numbers, issue to students.
- **Job Placement** → Record employer name, salary, job role (for foreign employment documentation).

---

## 4. Market Opportunity Assessment

### 4.1 Total Addressable Market (TAM)

**Nepal Private Training Institute Universe:**

| Segment | Estimated Count | Avg. Students/Year | Market Size (NPR/year) |
|---------|----------------|--------------------|-----------------------|
| Loksewa Preparation | 5,000 | 150 | Already Captured |
| Computer Training | 2,000 | 200 | 400M-800M |
| Bridge Course | 800 | 600 | 240M-480M |
| Tuition Centers | 5,000+ | 40 | 200M-400M (low ARPU) |
| CTEVT Skill Training | 500 | 100 | 50M-100M |
| **Total Expansion Opportunity** | **8,300** | | **890M - 1.78B NPR** |

**Current Hamro Labs Revenue (Assumed):**
- 50 paying institutes × NPR 30,000/year = NPR 1.5M ARR
- **Target:** 500 institutes in 2 years = NPR 15M ARR
- **Expansion markets could add 30-50% additional revenue**

### 4.2 Serviceable Addressable Market (SAM)

**Realistic Targets (Urban + Semi-Urban, Tech-Ready Institutes):**

| Segment | SAM (Reachable in 2 years) | Conversion Potential | Priority |
|---------|----------------------------|----------------------|----------|
| Computer Training | 500 institutes | 25% (125 paid customers) | **HIGH** |
| Bridge Course | 200 institutes | 20% (40 paid customers) | **MEDIUM** |
| CTEVT Skill Training | 100 institutes | 30% (30 paid customers) | **MEDIUM** |
| Tuition Centers | 300 institutes | 15% (45 paid customers) | **LOW** (low ARPU, high support cost) |

**Revenue Projection:**
- Computer Training: 125 × NPR 25,000 = NPR 3.125M
- Bridge Course: 40 × NPR 20,000 = NPR 0.8M
- CTEVT Skill: 30 × NPR 30,000 = NPR 0.9M
- **Total Expansion Revenue Potential:** NPR 4.825M in Year 2

### 4.3 Competitive Landscape

**Current Nepal ERP Providers:**

| Competitor | Positioning | Strengths | Weaknesses | Market Share (Est.) |
|------------|-------------|-----------|------------|---------------------|
| **Vedmarg** | Generic school ERP | Established brand, marketing | Generic (not vertical-specific), expensive | 15-20% |
| **Pathami** | School + Institute ERP | BS calendar integration, local support | Limited features, UI/UX dated | 10-15% |
| **ProSchool** | School-focused | Mobile app, parent portal | School-centric, poor fit for training institutes | 8-12% |
| **Genius ERP** | International (India) | Feature-rich, mature product | Not localized, no BS calendar, expensive | 5-8% |
| **Manual/Excel** | — | Free, familiar | Error-prone, no automation, not scalable | **60%+ of market** |

**Hamro Labs Competitive Advantages:**
1. ✅ **Vertical-Specific:** Built for Nepal training institutes, not generic schools
2. ✅ **BS Calendar Native:** Only ERP with Bikram Sambat deeply integrated
3. ✅ **Mobile-First PWA:** Offline capability for Nepal's connectivity reality
4. ✅ **Affordable:** Multi-tenant SaaS pricing (NPR 20,000-40,000/year vs competitors' NPR 60,000+)
5. ✅ **Python Report Engine:** Professional Excel/PDF exports (competitors use basic PHP reports)
6. ✅ **Loksewa Credibility:** Success in Loksewa market = trust for expansion

**Competitive Gaps to Address:**
- ❌ No competitors offer **automated certificate generation** (opportunity!)
- ❌ No competitors have **CTEVT compliance module** (blue ocean)
- ❌ No competitors track **practical hours / lab sessions** (computer training gap)

### 4.4 Market Timing & Trends

**Favorable Macro Trends:**

1. **Digital Transformation Post-COVID:** Institutes that went manual during COVID now seeking cloud solutions. Market education is done.

2. **Government Push for Tech Adoption:** Nepal government encouraging digital invoicing (VAT compliance), making software adoption a necessity.

3. **Mobile Penetration:** 90%+ of Nepali adults have smartphones. Mobile-first ERPs have advantage over desktop-only systems.

4. **Youth Tech Literacy:** Post-SEE students are digital natives. Parents expect institutes to use modern tools.

5. **Competition for Students:** Institutes compete heavily for enrollments. Modern ERP = professional brand image.

**Risks & Challenges:**

1. **Price Sensitivity:** Training institutes operate on thin margins. Pricing must be aggressive (NPR 1,500-3,000/month max).

2. **Seasonal Cash Flow:** Bridge course institutes have 90% revenue in 3 months. May struggle with annual subscription payments. Need flexible payment plans.

3. **Reluctance to Change:** Owners aged 40+ may resist shifting from manual registers. Need strong onboarding/training.

4. **Internet Reliability:** Semi-urban areas still have poor connectivity. Offline-capable PWA is critical.

---

## 5. Strategic Recommendations

### 5.1 Expansion Prioritization

**Phase 1 (Q2-Q3 2026): Computer Training Institutes**

**Rationale:**
- ✅ Largest addressable market (2,000 institutes)
- ✅ Year-round enrollment (less seasonal risk)
- ✅ High pain point: Certificate generation (easy win)
- ✅ Students tech-savvy (faster adoption)
- ✅ Modular expansion (can reuse most existing features)

**Phase 2 (Q4 2026 - Q1 2027): Bridge Course & CTEVT Skill Training**

**Rationale:**
- Bridge courses are seasonal (align launch with Jan-Feb marketing for April intake)
- CTEVT institutes have compliance needs (government reporting) = sticky customers
- Both segments need curriculum management (build once, serve both)

**Phase 3 (Q2 2027+): Tuition Centers + Multi-Branch Franchises**

**Rationale:**
- Tuition centers require simplified "Lite" version (separate product line)
- Multi-branch franchises need enterprise features (later stage)

### 5.2 Product Positioning

**Current:** "Nepal's #1 Loksewa Preparation Institute ERP"

**Expanded:** "Nepal's Complete Training Institute Management Platform — From Loksewa to Computer Training to Skill Development"

**Messaging Pillars:**
1. **Built for Nepal:** BS calendar, Nepali language, local payment gateways (eSewa/Khalti)
2. **Mobile-First:** Works offline, accessible on any device
3. **All-in-One:** Inquiry to Certificate — no need for Excel or manual registers
4. **Affordable:** Starting NPR 1,500/month (vs competitors at NPR 5,000+)

### 5.3 Go-to-Market Strategy

**Channel Strategy:**

1. **Direct Sales (Urban Markets):**
   - Kathmandu, Pokhara, Chitwan: Field sales team
   - Target: Mid-size institutes (200+ students)
   - Close rate: 15-20%

2. **Partner Channel (Regional Markets):**
   - Computer hardware dealers (sell PCs to institutes, bundle ERP)
   - CTEVT consultants (help institutes with registration, offer ERP as value-add)
   - Accounting software resellers (Tally dealers)

3. **Digital Marketing:**
   - Facebook ads targeting "Institute Owner" interest groups
   - YouTube tutorials on institute management (build authority)
   - SEO for "computer training institute software Nepal"

4. **Freemium / Trial:**
   - 30-day free trial (no credit card)
   - Free tier: Up to 20 students (convert tuition centers to paid)

**Pricing Strategy:**

| Plan | Target | Price (NPR/month) | Features |
|------|--------|-------------------|----------|
| **Starter** | Small institutes (<100 students) | 1,500 | Core modules, 1 admin user |
| **Growth** | Mid-size (100-300 students) | 2,500 | + SMS automation, custom reports |
| **Professional** | Large institutes (300+ students) | 4,000 | + Multi-branch, API access |
| **Enterprise** | Franchises (500+ students) | Custom | + Dedicated support, custom integrations |

**Customer Acquisition Cost (CAC) Target:** NPR 8,000 per customer  
**Lifetime Value (LTV) Target:** NPR 60,000 (24-month retention)  
**LTV:CAC Ratio:** 7.5:1 (healthy SaaS metric)

---

## 6. Conclusion

The expansion of Hamro Labs Academic ERP from Loksewa-only to a **multi-vertical training institute platform** represents a strategic opportunity to capture an underserved market worth **NPR 890M - 1.78B annually** across 8,300+ institutes.

**Key Success Factors:**
1. ✅ **Modular Architecture:** Build features that serve multiple segments (certificate generation helps both computer training and CTEVT)
2. ✅ **Vertical-Specific Customization:** Don't build a generic school ERP — tailor workflows to each institute type
3. ✅ **Nepal-First Design:** BS calendar, Nepali localization, offline capability = sustainable competitive moat
4. ✅ **Phased Rollout:** Computer training first (largest market, easiest fit), then bridge/CTEVT, then tuition centers

**Next Steps:**
1. Conduct **Feature Gap Analysis** (detailed in next document)
2. Design **Database Schema Upgrades** for multi-vertical support
3. Build **Implementation Roadmap** with clear milestones
4. Pilot with 5-10 computer training institutes (beta testing)
5. Iterate based on feedback, then scale marketing

**Expected Outcome:**  
Achieve **200+ total paid customers across all verticals by end of 2027**, generating **NPR 18-22M in ARR**, establishing Hamro Labs as Nepal's dominant training institute software platform.

---

**Document End**
