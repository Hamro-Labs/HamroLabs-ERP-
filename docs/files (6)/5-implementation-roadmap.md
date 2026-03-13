# Implementation Roadmap: Phased Development Plan for ERP Expansion

**Document Version:** 1.0  
**Prepared For:** Hamro Labs Academic ERP Development Team  
**Date:** March 2026  
**Classification:** Project Planning — Development Schedule

---

## Executive Summary

This roadmap outlines the **complete development timeline** for expanding Hamro Labs Academic ERP from a Loksewa-only platform to a multi-vertical training institute management system over **31 weeks** (7.5 months).

**Total Development Effort:** 1,240 developer-hours  
**Target Launch Dates:**
- Phase 1 (Computer Training MVP): **End of Q2 2026** (July 2026)
- Phase 2 (Bridge Course & CTEVT): **End of Q4 2026** (December 2026)
- Phase 3 (Enhancements & Scale): **Q1 2027** (March 2027)

**Team Structure:** 2 full-time developers + 1 QA engineer + 1 product manager

---

## Timeline Overview

```
Q2 2026                  Q3 2026                  Q4 2026         Q1 2027
├─────────────────────┼─────────────────────┼──────────────┼────────┤
│   Phase 1 (14w)     │   Phase 2 (12w)     │  Phase 3 (5w)│        │
│ Computer Training   │ Bridge & CTEVT      │ Enhancements │        │
│                     │                     │              │        │
Week 1-2: Foundation  Week 15-16: Streams   Week 27-28: Jobs        │
Week 3-5: Certificates Week 17-18: Models   Week 29-30: Analytics    │
Week 6-7: Multi-Enrol Week 19-20: CTEVT     Week 31: Polish         │
Week 8-9: Lab Mgmt    Week 21-22: Entrance                          │
Week 10-11: Payments  Week 23-24: Testing                           │
Week 12-13: WhatsApp  Week 25-26: Launch                            │
Week 14: Beta Launch                                                 │
```

---

## Phase 1: Computer Training MVP (14 Weeks)

**Goal:** Launch ERP for computer training institutes with certificate generation, multi-enrollment, and digital payments

**Target Market:** 50 computer training institutes by end of Q2  
**Team:** 2 developers, 1 QA, 1 PM  
**Duration:** April 1 - July 5, 2026 (14 weeks)

---

### Week 1-2: Foundation & Architecture

**Objective:** Build feature flag system and multi-vertical foundation

**Developer 1 Tasks:**
- [ ] Design and implement `tenant_features` table
- [ ] Create feature flag middleware (`tenant.feature:certificates`)
- [ ] Build feature management UI (admin enables/disables features)
- [ ] Add `institute_type` to tenants table
- [ ] Expand `course.category` ENUM (add computer_training, bridge_course, ctevt)

**Developer 2 Tasks:**
- [ ] Set up staging environment for multi-vertical testing
- [ ] Create migration test suite (automated rollback testing)
- [ ] Update API documentation for new endpoints
- [ ] Refactor dashboard to support adaptive modules

**QA Tasks:**
- [ ] Write test cases for feature flag system
- [ ] Test existing Loksewa features (regression testing)
- [ ] Prepare test data (computer training courses, students)

**Deliverable:** Feature flag system live, tenants can be classified by type

**Risk:** Feature flag system bugs could break existing features  
**Mitigation:** Extensive regression testing, feature flags default to "disabled"

---

### Week 3-5: Certificate Generation Module (CRITICAL)

**Objective:** Build end-to-end certificate generation with QR codes

**Developer 1 Tasks:**
- [ ] Create `certificates` and `certificate_templates` tables (migrations 006-007)
- [ ] Build template designer UI (HTML/CSS editor with variable placeholders)
- [ ] Implement template preview (render with sample data)
- [ ] Create QR code generator (using SimpleSoftwareIO/simple-qrcode)
- [ ] Build public verification page (`/verify/{certificate_number}`)

**Developer 2 Tasks:**
- [ ] Implement PDF generation (using Laravel Snappy + wkhtmltopdf)
- [ ] Build batch certificate generator (Laravel Queue job)
- [ ] Create certificate listing page (filter by course, batch, date)
- [ ] Implement certificate download (individual + batch ZIP)
- [ ] Add certificate numbering system (auto-increment with prefix)

**QA Tasks:**
- [ ] Test template rendering with edge cases (long names, special characters)
- [ ] Performance test: Generate 100 certificates (target: <60 seconds)
- [ ] Test QR code scanning (mobile devices)
- [ ] Verify PDF quality (print-ready 300 DPI)

**Deliverable:** Certificate module fully functional, institutes can generate & print certificates

**Success Metric:** Admin can generate 50 certificates in <30 seconds

**Risk:** PDF generation performance issues  
**Mitigation:** Use Laravel queues, generate PDFs in background

---

### Week 6-7: Multi-Enrollment System

**Objective:** Allow students to enroll in multiple courses simultaneously

**Developer 1 Tasks:**
- [ ] Remove unique constraint from `enrollments` table (migration 011)
- [ ] Add `course_id` to enrollments (for direct course reference)
- [ ] Migrate existing enrollment data (populate course_id from batch)
- [ ] Update enrollment creation API (accept multiple course IDs)
- [ ] Build multi-select UI for course enrollment

**Developer 2 Tasks:**
- [ ] Update fee calculation logic (sum fees across all enrollments)
- [ ] Modify student dashboard (show all enrolled courses)
- [ ] Update attendance marking (filter by enrollment/course)
- [ ] Create enrollment history view (track enrollment lifecycle)
- [ ] Add enrollment status transitions (active → completed → certificate issued)

**QA Tasks:**
- [ ] Test student enrolling in 5 courses simultaneously
- [ ] Verify fee calculation (3 courses = sum of all 3 fees)
- [ ] Test attendance marking for each enrollment separately
- [ ] Edge case: Student drops one course, others remain active

**Deliverable:** Students can enroll in multiple courses, fees calculated correctly

**Risk:** Breaking change — existing single-enrollment code may fail  
**Mitigation:** Thorough testing, gradual rollout (enable for computer training institutes only)

---

### Week 8-9: Lab & Practical Session Management

**Objective:** Track lab sessions, workstation assignments, and hours

**Developer 1 Tasks:**
- [ ] Create `lab_rooms` and `workstation_assignments` tables (migrations 009-010)
- [ ] Build lab room setup UI (define labs, workstation count)
- [ ] Implement workstation assignment logic (assign student to PC)
- [ ] Add session type to attendance (theory/practical dropdown)
- [ ] Implement hour tracking (record 1.0, 1.5, 2.0 hours per session)

**Developer 2 Tasks:**
- [ ] Add `required_theory_hours` and `required_practical_hours` to courses
- [ ] Build student hour dashboard ("320/390 hours completed, 82%")
- [ ] Create hour-based reports (students below 80% threshold)
- [ ] Implement hour calculation API (theory vs practical breakdown)
- [ ] Add lab utilization report (which PCs are in use)

**QA Tasks:**
- [ ] Test workstation assignment (30 students, 30 PCs, no double-booking)
- [ ] Verify hour calculation (10 sessions × 1.5 hours = 15 hours)
- [ ] Test edge case: Student reassigned to different workstation mid-course

**Deliverable:** Lab management system functional, hour tracking accurate

---

### Week 10-11: Digital Payment Integration

**Objective:** Integrate eSewa and Khalti for online fee payments

**Developer 1 Tasks:**
- [ ] eSewa API integration (initiate payment, verify transaction)
- [ ] Build eSewa redirect flow (student → eSewa → success/failure callback)
- [ ] Implement payment verification webhook
- [ ] Auto-update fee records on successful payment
- [ ] Generate digital payment receipts (PDF with transaction ID)

**Developer 2 Tasks:**
- [ ] Khalti API integration (same flow as eSewa)
- [ ] Build unified payment gateway UI (student selects eSewa or Khalti)
- [ ] Add payment transaction log (track all payment attempts)
- [ ] Implement payment reconciliation report (compare eSewa statements vs ERP)
- [ ] Add payment gateway toggle (admin enables/disables gateways)

**QA Tasks:**
- [ ] Test payment flow end-to-end (initiate → pay → auto-update fee)
- [ ] Test failure scenarios (payment declined, timeout, webhook fails)
- [ ] Verify transaction IDs are unique and stored correctly
- [ ] Test payment reconciliation (manual payment vs gateway payment)

**Deliverable:** eSewa and Khalti integration live, students can pay online

**Risk:** Payment gateway downtime, webhook failures  
**Mitigation:** Retry logic, manual reconciliation fallback

---

### Week 12-13: WhatsApp Integration

**Objective:** Send notifications via WhatsApp Business API

**Developer 1 Tasks:**
- [ ] Choose WhatsApp provider (360dialog or Twilio)
- [ ] Set up WhatsApp Business Account
- [ ] Implement WhatsApp message service (send text messages)
- [ ] Build template system (WhatsApp requires pre-approved templates)
- [ ] Create notification routing (SMS vs WhatsApp preference per student)

**Developer 2 Tasks:**
- [ ] Update notification system (support WhatsApp as channel)
- [ ] Implement document sharing (send PDF study materials via WhatsApp)
- [ ] Add WhatsApp delivery tracking (sent, delivered, read status)
- [ ] Build WhatsApp notification settings (admin enables/disables)
- [ ] Create WhatsApp cost calculator (track API usage)

**QA Tasks:**
- [ ] Test message delivery (send to 10 phone numbers)
- [ ] Verify delivery status tracking
- [ ] Test document sending (PDF, JPEG)
- [ ] Test template approval process (submit, get approved, use)

**Deliverable:** WhatsApp messaging functional, notifications sent via WhatsApp

**Risk:** WhatsApp API cost (NPR 0.50-2 per message)  
**Mitigation:** Make WhatsApp optional, offer as premium add-on

---

### Week 14: Beta Testing & Launch Preparation

**Objective:** Test with 10 beta institutes, fix bugs, prepare for launch

**All Team Tasks:**
- [ ] Onboard 10 computer training institutes for beta testing
- [ ] Provide training (2-hour session per institute)
- [ ] Collect feedback (survey, interviews)
- [ ] Fix critical bugs
- [ ] Prepare marketing materials (landing page, demo video)
- [ ] Write user documentation (help center articles)
- [ ] Plan public launch announcement

**Beta Institute Selection Criteria:**
- Mix of small (50 students) and medium (200 students) institutes
- Geographic diversity (Kathmandu, Pokhara, Chitwan)
- Tech-savvy owners (can provide quality feedback)

**Deliverable:** 10 beta institutes using the system, bugs fixed, ready for public launch

**Success Metric:** 8/10 beta institutes report "very satisfied" or "satisfied"

---

## Phase 2: Bridge Course & CTEVT Expansion (12 Weeks)

**Goal:** Add support for bridge course and CTEVT skill training institutes

**Target Market:** 40 bridge course + 30 CTEVT institutes  
**Duration:** July 8 - September 25, 2026 (12 weeks)

---

### Week 15-16: Stream Management (Bridge Courses)

**Developer 1 Tasks:**
- [ ] Create `streams` table (Science, Management, Humanities)
- [ ] Add `stream_id` to batches table
- [ ] Build stream setup UI (define streams, assign to batches)
- [ ] Update batch creation workflow (select stream)
- [ ] Implement stream-based student filtering

**Developer 2 Tasks:**
- [ ] Update dashboard (show stream breakdown)
- [ ] Create stream-wise reports (enrollment by stream)
- [ ] Implement stream-specific syllabus/curriculum
- [ ] Add stream to student enrollment record
- [ ] Build stream comparison analytics

**Deliverable:** Bridge course institutes can create Science/Management streams

---

### Week 17-18: Model Question Bank

**Developer 1 Tasks:**
- [ ] Create `model_questions` and `student_question_attempts` tables
- [ ] Build question entry UI (college, year, subject, question text, answer)
- [ ] Implement question bank browsing (filter by college, year, subject)
- [ ] Create student practice mode (answer questions, track attempts)
- [ ] Add question difficulty tagging (easy/medium/hard)

**Developer 2 Tasks:**
- [ ] Build bulk question import (Excel/CSV upload)
- [ ] Implement question search (keyword search across question text)
- [ ] Create student practice analytics (% correct, weak subjects)
- [ ] Add question categorization (MCQ vs descriptive)
- [ ] Build question sharing (share model sets between batches)

**Deliverable:** Bridge course institutes can manage model question banks

---

### Week 19-20: CTEVT Compliance Module

**Developer 1 Tasks:**
- [ ] Create `ctevt_courses`, `competency_units`, `student_competencies` tables
- [ ] Build CTEVT course setup UI (define competency units, hours)
- [ ] Implement competency grading (teacher marks student as competent/not competent)
- [ ] Add competency progress dashboard (student view)
- [ ] Build competency completion tracking (100% required for certificate)

**Developer 2 Tasks:**
- [ ] Implement CTEVT registration form export (Excel format)
- [ ] Build monthly hour report (CTEVT format)
- [ ] Create annual renewal checklist (automated reminders)
- [ ] Add CTEVT code management (link courses to govt codes)
- [ ] Implement CTEVT compliance alerts (< 80% attendance flagged)

**Deliverable:** CTEVT institutes can track competencies and generate compliance reports

---

### Week 21-22: Entrance Result Tracking

**Developer 1 Tasks:**
- [ ] Create `entrance_results` table
- [ ] Build entrance result entry UI (college, score, scholarship %)
- [ ] Implement result analytics (success rate by college)
- [ ] Create student success stories (admitted to top colleges)
- [ ] Add scholarship tier breakdown (100%, 75%, 50%, 25%, 0%)

**Developer 2 Tasks:**
- [ ] Build result comparison (year-over-year improvement)
- [ ] Implement marketing export (generate testimonial list)
- [ ] Create college-wise success report (which colleges students got into)
- [ ] Add result prediction (based on mock exam scores)
- [ ] Build result dashboard for institute admin

**Deliverable:** Bridge course institutes can track entrance exam results

---

### Week 23-24: Integration Testing

**All Team Tasks:**
- [ ] End-to-end testing (all modules working together)
- [ ] Performance testing (1,000 students, 100 batches)
- [ ] Security testing (penetration testing, SQL injection)
- [ ] Mobile testing (PWA on Android/iOS)
- [ ] User acceptance testing (10 institutes per segment)

**Deliverable:** All Phase 2 features tested and stable

---

### Week 25-26: Phase 2 Launch

**All Team Tasks:**
- [ ] Onboard 40 bridge course institutes
- [ ] Onboard 30 CTEVT institutes
- [ ] Training sessions (webinars, video tutorials)
- [ ] Marketing campaign (Facebook ads, Google ads)
- [ ] Monitor system performance (server load, errors)

**Deliverable:** 70 new institutes onboarded across bridge course and CTEVT segments

---

## Phase 3: Enhancements & Scale (5 Weeks)

**Goal:** Polish, enhancements, and prepare for scale

**Duration:** October 1 - November 5, 2026 (5 weeks)

### Week 27-28: Job Placement Tracking

**Developer 1 Tasks:**
- [ ] Create `job_placements` table (student, company, role, salary)
- [ ] Build job placement entry UI
- [ ] Implement placement analytics (placement rate, avg salary)
- [ ] Create company tracking (which companies hire most)

**Deliverable:** Computer training and CTEVT institutes can track job placements

---

### Week 29-30: Advanced Analytics & Video Streaming

**Developer 1 Tasks:**
- [ ] Build teacher performance dashboard (student feedback, pass rates)
- [ ] Implement course popularity analytics (enrollment trends)
- [ ] Create financial forecasting (revenue projections)

**Developer 2 Tasks:**
- [ ] Integrate video streaming (Vimeo or Cloudflare Stream)
- [ ] Build video player (embedded in study materials)
- [ ] Add video progress tracking (watch time, completion rate)

**Deliverable:** Enhanced analytics and video streaming

---

### Week 31: Polish & Launch Preparation

**All Team Tasks:**
- [ ] Bug fixes from user feedback
- [ ] UI/UX improvements
- [ ] Performance optimization
- [ ] Documentation updates
- [ ] Prepare for 200+ institute scale

**Deliverable:** System polished and ready for scale

---

## Resource Allocation

### Team Structure

**Development Team:**
- **Senior Developer 1:** Backend, database, API
- **Senior Developer 2:** Frontend, UI/UX, integrations
- **QA Engineer:** Testing, automation, bug tracking
- **Product Manager:** Requirements, roadmap, stakeholder communication

**Total Cost (Estimated):**
- Developers: 2 × NPR 80,000/month × 8 months = NPR 1,280,000
- QA: 1 × NPR 50,000/month × 8 months = NPR 400,000
- PM: 1 × NPR 60,000/month × 8 months = NPR 480,000
- **Total:** NPR 2,160,000 (~USD 16,000)

---

## Risk Management

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| **Feature Creep** | High | High | Strict MVP definition, no new features in Phase 1 |
| **Payment Gateway Issues** | Medium | High | Manual payment fallback, retry logic |
| **Database Migration Failure** | Low | Critical | Extensive testing, rollback plan, backup strategy |
| **Team Attrition** | Medium | Medium | Knowledge sharing, documentation, overlap |
| **Market Adoption Slower Than Expected** | Medium | Medium | Aggressive marketing, flexible pricing |

---

## Success Metrics

### Phase 1 (Computer Training)
- ✅ 50 paid computer training institutes by end of Q2
- ✅ Certificate module usage: 90%+ adoption
- ✅ Digital payment adoption: 60%+ of institutes
- ✅ Customer satisfaction: 8/10 average rating

### Phase 2 (Bridge & CTEVT)
- ✅ 40 bridge course institutes
- ✅ 30 CTEVT institutes
- ✅ System uptime: 99.5%+
- ✅ Average response time: <2 seconds

### Phase 3 (Scale)
- ✅ 200+ total institutes across all segments
- ✅ NPR 18-22M ARR
- ✅ <10% annual churn rate

---

## Conclusion

This 31-week roadmap transforms Hamro Labs from a Loksewa-only ERP to Nepal's leading multi-vertical training institute platform. The phased approach balances speed-to-market with quality, ensuring each segment launch is polished and market-ready.

**Next Steps:**
1. ✅ Approve roadmap
2. ✅ Hire/allocate development team
3. ✅ Set up project management tools (Jira, GitHub)
4. ✅ Begin Phase 1 Week 1 (Foundation)

---

**Document End**
