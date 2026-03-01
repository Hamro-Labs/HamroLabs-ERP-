<?php
/**
 * Support & Help Center - Institute Admin
 * Contains: YouTube tutorials, contact details, FAQ with 50 articles
 */
require_once __DIR__ . '/../../../config/config.php';
requirePermission('dashboard.view');

$pageTitle = "Support & Help Center";
$roleCSS = "ia-dashboard-new.css";
$wrapperClass = "app-layout";
$user = getCurrentUser();
$tenantName = $_SESSION['tenant_name'] ?? 'Institute';
$activePage = 'support';

$isSPA = isset($_GET['spa']) && $_GET['spa'] === 'true';

if (!$isSPA) {
    include VIEWS_PATH . '/layouts/header.php';
    include __DIR__ . '/layouts/sidebar.php';
}
?>

<?php if (!$isSPA): ?>
<div class="main">
    <?php include __DIR__ . '/layouts/header.php'; ?>

    <div class="content" id="mainContent">
<?php endif; ?>
        <style>
            /* Support Page Styles - Brand Compliant & Responsive */
            .support-wrapper {
                padding: 20px;
                max-width: 1400px;
                margin: 0 auto;
            }
            
            /* Hero Header */
            .support-hero {
                background: linear-gradient(135deg, var(--green) 0%, #00a884 100%);
                border-radius: 16px;
                padding: 40px 30px;
                margin-bottom: 24px;
                text-align: center;
                color: white;
                position: relative;
                overflow: hidden;
            }
            .support-hero::before {
                content: '';
                position: absolute;
                top: -50%;
                right: -10%;
                width: 300px;
                height: 300px;
                background: rgba(255,255,255,0.1);
                border-radius: 50%;
            }
            .support-hero h1 {
                font-size: clamp(1.5rem, 4vw, 2.5rem);
                margin-bottom: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 12px;
                position: relative;
                z-index: 1;
            }
            .support-hero p {
                font-size: clamp(0.9rem, 2vw, 1.1rem);
                opacity: 0.95;
                max-width: 600px;
                margin: 0 auto;
                position: relative;
                z-index: 1;
            }
            
            /* Quick Help Grid */
            .quick-help-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
                gap: 16px;
                margin-bottom: 24px;
            }
            .quick-help-card {
                background: white;
                border-radius: 12px;
                padding: 24px 16px;
                text-align: center;
                border: 1px solid var(--card-border);
                cursor: pointer;
                transition: all 0.3s ease;
                text-decoration: none;
                color: inherit;
            }
            .quick-help-card:hover {
                border-color: var(--green);
                transform: translateY(-4px);
                box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            }
            .quick-help-card i {
                font-size: 2rem;
                color: var(--green);
                margin-bottom: 12px;
                display: block;
            }
            .quick-help-card.whatsapp i {
                color: #25d366;
            }
            .quick-help-card.youtube i {
                color: #ff0000;
            }
            .quick-help-card h3 {
                font-size: 0.95rem;
                color: var(--text-dark);
                margin: 0;
                font-weight: 600;
            }
            
            /* Support Cards Grid */
            .support-cards-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
                gap: 24px;
                margin-bottom: 24px;
            }
            .support-card {
                background: white;
                border-radius: 12px;
                padding: 24px;
                border: 1px solid var(--card-border);
                box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            }
            .support-card h2 {
                color: var(--text-dark);
                font-size: 1.2rem;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                gap: 10px;
                padding-bottom: 16px;
                border-bottom: 2px solid var(--bg);
            }
            .support-card h2 i {
                color: var(--green);
                font-size: 1.4rem;
            }
            
            /* YouTube Links */
            .youtube-list {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .youtube-item {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 12px;
                background: #f8f9fa;
                border-radius: 8px;
                text-decoration: none;
                color: var(--text-dark);
                transition: all 0.2s;
            }
            .youtube-item:hover {
                background: #ff0000;
                color: white;
            }
            .youtube-item:hover i {
                color: white;
            }
            .youtube-item i {
                font-size: 24px;
                color: #ff0000;
                transition: color 0.2s;
            }
            .youtube-item div {
                flex: 1;
                min-width: 0;
            }
            .youtube-item strong {
                display: block;
                font-size: 0.95rem;
                margin-bottom: 2px;
            }
            .youtube-item span {
                font-size: 0.8rem;
                opacity: 0.8;
            }
            
            /* Contact Items */
            .contact-list {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }
            .contact-item {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 14px;
                background: #f8f9fa;
                border-radius: 10px;
                transition: background 0.2s;
            }
            .contact-item:hover {
                background: #e9ecef;
            }
            .contact-item i {
                width: 40px;
                height: 40px;
                background: var(--green);
                color: white;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1rem;
                flex-shrink: 0;
            }
            .contact-item div {
                flex: 1;
                min-width: 0;
            }
            .contact-item .label {
                font-size: 0.75rem;
                color: var(--text-light);
                margin-bottom: 2px;
            }
            .contact-item a, .contact-item strong {
                color: var(--green);
                text-decoration: none;
                font-weight: 600;
                font-size: 0.95rem;
            }
            .contact-item a:hover {
                text-decoration: underline;
            }
            
            /* FAQ Section */
            .faq-wrapper {
                background: white;
                border-radius: 12px;
                padding: 24px;
                border: 1px solid var(--card-border);
                box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            }
            .faq-wrapper h2 {
                color: var(--text-dark);
                font-size: 1.2rem;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .faq-wrapper h2 i {
                color: var(--green);
            }
            
            /* Search Box */
            .faq-search-box {
                position: relative;
                margin-bottom: 24px;
            }
            .faq-search-box i {
                position: absolute;
                left: 16px;
                top: 50%;
                transform: translateY(-50%);
                color: var(--text-light);
            }
            .faq-search-box input {
                width: 100%;
                padding: 14px 16px 14px 48px;
                border: 2px solid var(--card-border);
                border-radius: 10px;
                font-size: 1rem;
                outline: none;
                transition: border-color 0.2s;
                background: white;
            }
            .faq-search-box input:focus {
                border-color: var(--green);
            }
            
            /* FAQ Items */
            .faq-container {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }
            .faq-item {
                border: 1px solid var(--card-border);
                border-radius: 10px;
                overflow: hidden;
                transition: box-shadow 0.2s;
            }
            .faq-item:hover {
                box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            }
            .faq-question {
                padding: 16px 20px;
                background: #f8f9fa;
                cursor: pointer;
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-weight: 600;
                color: var(--text-dark);
                transition: background 0.2s;
            }
            .faq-question:hover {
                background: #e9ecef;
            }
            .faq-question.active {
                background: var(--green);
                color: white;
            }
            .faq-question i {
                transition: transform 0.3s;
                font-size: 0.9rem;
            }
            .faq-question.active i {
                transform: rotate(180deg);
            }
            .faq-category {
                display: inline-block;
                padding: 4px 12px;
                background: rgba(0, 158, 126, 0.1);
                color: var(--green);
                border-radius: 20px;
                font-size: 0.7rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 8px;
            }
            .faq-answer {
                display: none;
                padding: 20px;
                background: white;
                color: var(--text-light);
                line-height: 1.7;
                border-top: 1px solid var(--card-border);
            }
            .faq-answer.active {
                display: block;
            }
            .faq-answer strong {
                color: var(--text-dark);
            }
            .faq-answer ol, .faq-answer ul {
                margin: 12px 0;
                padding-left: 24px;
            }
            .faq-answer li {
                margin: 8px 0;
            }
            
            /* Responsive Breakpoints */
            @media (max-width: 1024px) {
                .support-wrapper {
                    padding: 16px;
                }
                .support-cards-grid {
                    grid-template-columns: 1fr;
                }
            }
            
            @media (max-width: 768px) {
                .support-hero {
                    padding: 30px 20px;
                }
                .quick-help-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
                .quick-help-card {
                    padding: 20px 12px;
                }
                .quick-help-card i {
                    font-size: 1.5rem;
                }
                .quick-help-card h3 {
                    font-size: 0.85rem;
                }
                .support-card {
                    padding: 20px;
                }
                .youtube-item {
                    padding: 10px;
                }
                .youtube-item strong {
                    font-size: 0.9rem;
                }
                .contact-item {
                    padding: 12px;
                }
                .faq-question {
                    padding: 14px 16px;
                    font-size: 0.95rem;
                }
                .faq-answer {
                    padding: 16px;
                }
            }
            
            @media (max-width: 480px) {
                .support-wrapper {
                    padding: 12px;
                }
                .support-hero {
                    padding: 24px 16px;
                    border-radius: 12px;
                }
                .quick-help-grid {
                    grid-template-columns: repeat(2, 1fr);
                    gap: 10px;
                }
                .quick-help-card {
                    padding: 16px 10px;
                }
                .youtube-item i {
                    font-size: 20px;
                }
                .contact-item i {
                    width: 36px;
                    height: 36px;
                    font-size: 0.9rem;
                }
                .faq-search-box input {
                    padding: 12px 12px 12px 44px;
                    font-size: 0.95rem;
                }
            }
            
            /* Print Styles */
            @media print {
                .sidebar, .top-header, .quick-help-grid {
                    display: none !important;
                }
                .main {
                    margin-left: 0 !important;
                }
                .support-hero {
                    background: #f5f5f5 !important;
                    color: black !important;
                    -webkit-print-color-adjust: exact;
                }
            }
        </style>

        <div class="support-wrapper">
            <!-- Hero Header -->
            <div class="support-hero">
                <h1><i class="fa-solid fa-headset"></i> Support & Help Center</h1>
                <p>Find answers, watch tutorials, or get in touch with our support team. We're here to help you make the most of Hamro ERP.</p>
            </div>

            <!-- Quick Help Cards -->
            <div class="quick-help-grid">
                <a href="#tutorials" class="quick-help-card youtube">
                    <i class="fa-brands fa-youtube"></i>
                    <h3>Video Tutorials</h3>
                </a>
                <a href="#faq" class="quick-help-card">
                    <i class="fa-solid fa-circle-question"></i>
                    <h3>FAQs</h3>
                </a>
                <a href="#contact" class="quick-help-card">
                    <i class="fa-solid fa-phone"></i>
                    <h3>Contact Us</h3>
                </a>
                <a href="https://wa.me/9779800000000" target="_blank" class="quick-help-card whatsapp">
                    <i class="fa-brands fa-whatsapp"></i>
                    <h3>WhatsApp Chat</h3>
                </a>
            </div>

            <!-- Support Cards Grid -->
            <div class="support-cards-grid">
                <!-- YouTube Tutorials -->
                <div class="support-card" id="tutorials">
                    <h2><i class="fa-brands fa-youtube"></i> Video Tutorials</h2>
                    <div class="youtube-list">
                        <a href="https://youtube.com/@hamrolabs" target="_blank" class="youtube-item">
                            <i class="fa-brands fa-youtube"></i>
                            <div>
                                <strong>HamroLabs Official Channel</strong>
                                <span>Subscribe for latest updates</span>
                            </div>
                        </a>
                        <a href="https://youtube.com/watch?v=intro-erp" target="_blank" class="youtube-item">
                            <i class="fa-brands fa-youtube"></i>
                            <div>
                                <strong>ERP Introduction</strong>
                                <span>Getting started guide</span>
                            </div>
                        </a>
                        <a href="https://youtube.com/watch?v=student-management" target="_blank" class="youtube-item">
                            <i class="fa-brands fa-youtube"></i>
                            <div>
                                <strong>Student Management</strong>
                                <span>Complete walkthrough</span>
                            </div>
                        </a>
                        <a href="https://youtube.com/watch?v=fee-management" target="_blank" class="youtube-item">
                            <i class="fa-brands fa-youtube"></i>
                            <div>
                                <strong>Fee Management</strong>
                                <span>Collection & reports</span>
                            </div>
                        </a>
                        <a href="https://youtube.com/watch?v=attendance-system" target="_blank" class="youtube-item">
                            <i class="fa-brands fa-youtube"></i>
                            <div>
                                <strong>Attendance System</strong>
                                <span>Setup & daily operations</span>
                            </div>
                        </a>
                        <a href="https://youtube.com/watch?v=exam-results" target="_blank" class="youtube-item">
                            <i class="fa-brands fa-youtube"></i>
                            <div>
                                <strong>Exams & Results</strong>
                                <span>Management guide</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="support-card" id="contact">
                    <h2><i class="fa-solid fa-address-card"></i> Contact Us</h2>
                    <div class="contact-list">
                        <div class="contact-item">
                            <i class="fa-solid fa-phone"></i>
                            <div>
                                <div class="label">Support Hotline</div>
                                <a href="tel:+9779800000000">+977 980-000-0000</a>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fa-solid fa-phone"></i>
                            <div>
                                <div class="label">Sales Inquiry</div>
                                <a href="tel:+9779800000001">+977 980-000-0001</a>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fa-solid fa-envelope"></i>
                            <div>
                                <div class="label">Email Support</div>
                                <a href="mailto:support@hamrolabs.com">support@hamrolabs.com</a>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fa-brands fa-whatsapp"></i>
                            <div>
                                <div class="label">WhatsApp</div>
                                <a href="https://wa.me/9779800000000" target="_blank">+977 980-000-0000</a>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fa-solid fa-location-dot"></i>
                            <div>
                                <div class="label">Office Address</div>
                                <strong>Kathmandu, Nepal</strong>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fa-solid fa-clock"></i>
                            <div>
                                <div class="label">Working Hours</div>
                                <strong>Sun-Fri: 9:00 AM - 6:00 PM</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="faq-wrapper" id="faq">
                <h2><i class="fa-solid fa-circle-question"></i> Frequently Asked Questions (50 Articles)</h2>
                
                <div class="faq-search-box">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="faqSearch" placeholder="Search for answers..." onkeyup="searchFAQ()">
                </div>

                <div class="faq-container" id="faqContainer">
                    <!-- Getting Started -->
                    <div class="faq-item" data-category="getting-started">
                        <span class="faq-category">Getting Started</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            1. How do I log in to the ERP system for the first time?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Open your web browser and go to your institute's ERP URL</li>
                                <li>Enter your email address provided by the administrator</li>
                                <li>Enter your temporary password</li>
                                <li>Click the "Login" button</li>
                                <li>On first login, you'll be prompted to change your password</li>
                                <li>Create a strong password with at least 8 characters</li>
                                <li>Confirm the new password and save</li>
                            </ol>
                            <p><strong>Tip:</strong> If you forget your password, click "Forgot Password" on the login page.</p>
                        </div>
                    </div>

                    <div class="faq-item" data-category="getting-started">
                        <span class="faq-category">Getting Started</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            2. How do I reset my password?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to the login page</li>
                                <li>Click on "Forgot Password" link below the login button</li>
                                <li>Enter your registered email address</li>
                                <li>Click "Send Reset Link"</li>
                                <li>Check your email inbox for the password reset link</li>
                                <li>Click the link in the email (valid for 1 hour)</li>
                                <li>Enter your new password twice</li>
                                <li>Click "Reset Password" to complete</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="getting-started">
                        <span class="faq-category">Getting Started</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            3. How do I update my profile information?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Click on your profile icon in the top right corner</li>
                                <li>Select "My Account" from the dropdown menu</li>
                                <li>Click on the "Edit Profile" button</li>
                                <li>Update your name, phone number, and other details</li>
                                <li>Click "Choose File" to upload a profile photo</li>
                                <li>Click "Save Changes" to update your information</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Student Management -->
                    <div class="faq-item" data-category="students">
                        <span class="faq-category">Student Management</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            4. How do I add a new student to the system?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Students" from the sidebar menu</li>
                                <li>Click the "+ Add Student" button</li>
                                <li>Fill in the student's personal information (name, DOB, gender)</li>
                                <li>Enter contact details (phone, email, address)</li>
                                <li>Select the batch/course the student is enrolling in</li>
                                <li>Upload student's photo (optional)</li>
                                <li>Enter parent's/guardian's information</li>
                                <li>Click "Save & Continue" to add the student</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="students">
                        <span class="faq-category">Student Management</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            5. How do I edit student information?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Students" in the sidebar</li>
                                <li>Use the search box to find the student</li>
                                <li>Click on the student's name to open their profile</li>
                                <li>Click the "Edit" button in the top right</li>
                                <li>Update the required information</li>
                                <li>Click "Save Changes" to update the record</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="students">
                        <span class="faq-category">Student Management</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            6. How do I promote students to the next grade?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Students" → "Bulk Actions"</li>
                                <li>Select "Promote Students" option</li>
                                <li>Choose the source batch/grade</li>
                                <li>Select students to promote (use checkboxes)</li>
                                <li>Choose the destination batch/grade</li>
                                <li>Set the promotion date</li>
                                <li>Click "Promote Selected Students"</li>
                                <li>Review and confirm the promotion</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="students">
                        <span class="faq-category">Student Management</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            7. How do I issue a student ID card?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Students" and find the student</li>
                                <li>Click on the student's profile</li>
                                <li>Click the "ID Card" button</li>
                                <li>Verify the information displayed</li>
                                <li>Select ID card template (if multiple available)</li>
                                <li>Click "Generate ID Card"</li>
                                <li>Preview the ID card</li>
                                <li>Click "Print" or "Download PDF"</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Fee Management -->
                    <div class="faq-item" data-category="fees">
                        <span class="faq-category">Fee Management</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            8. How do I collect fees from a student?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Fees" → "Collect Fee" from the sidebar</li>
                                <li>Search for the student by name or ID</li>
                                <li>Select the student from the results</li>
                                <li>View the fee structure and pending amount</li>
                                <li>Enter the amount being paid</li>
                                <li>Select payment mode (Cash, Card, Bank Transfer, etc.)</li>
                                <li>Enter transaction reference (if applicable)</li>
                                <li>Click "Collect Fee" to complete the transaction</li>
                                <li>Print or email the receipt</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="fees">
                        <span class="faq-category">Fee Management</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            9. How do I set up fee structure for a batch?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Fees" → "Fee Structure"</li>
                                <li>Click "+ Create Fee Structure"</li>
                                <li>Select the batch/course</li>
                                <li>Enter the academic year</li>
                                <li>Add fee components (Tuition, Admission, Exam, etc.)</li>
                                <li>Enter amount for each component</li>
                                <li>Set due dates for each fee type</li>
                                <li>Add any applicable discounts</li>
                                <li>Click "Save Fee Structure"</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="fees">
                        <span class="faq-category">Fee Management</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            10. How do I generate fee reports?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Fees" → "Reports"</li>
                                <li>Select report type (Collection, Pending, Summary)</li>
                                <li>Choose date range for the report</li>
                                <li>Select batch/course (optional)</li>
                                <li>Click "Generate Report"</li>
                                <li>View the report on screen</li>
                                <li>Click "Export" to download as Excel/PDF</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="fees">
                        <span class="faq-category">Fee Management</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            11. How do I apply a discount to a student's fee?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Students" and find the student</li>
                                <li>Open the student's profile</li>
                                <li>Click on "Fee Details" tab</li>
                                <li>Click "Apply Discount" button</li>
                                <li>Select discount type (Percentage or Fixed Amount)</li>
                                <li>Enter discount value</li>
                                <li>Select which fee components the discount applies to</li>
                                <li>Add remarks/reason for discount</li>
                                <li>Click "Apply Discount"</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Attendance -->
                    <div class="faq-item" data-category="attendance">
                        <span class="faq-category">Attendance</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            12. How do I mark daily attendance?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Attendance" → "Daily Attendance"</li>
                                <li>Select the batch/class</li>
                                <li>Select the date (defaults to today)</li>
                                <li>View the list of all students in the batch</li>
                                <li>Mark Present (P), Absent (A), or Late (L) for each student</li>
                                <li>Use "Mark All Present" for quick marking</li>
                                <li>Add remarks for absent students (optional)</li>
                                <li>Click "Save Attendance" to record</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="attendance">
                        <span class="faq-category">Attendance</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            13. How do I view attendance reports?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Attendance" → "Reports"</li>
                                <li>Select report type (Daily, Monthly, Student-wise)</li>
                                <li>Choose date range</li>
                                <li>Select batch/student</li>
                                <li>Click "Generate Report"</li>
                                <li>View attendance statistics and percentages</li>
                                <li>Export as PDF or Excel if needed</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Exams -->
                    <div class="faq-item" data-category="exams">
                        <span class="faq-category">Exams & Results</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            14. How do I create a new exam?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Exams" → "Create Exam"</li>
                                <li>Enter exam name (e.g., "First Terminal Exam 2024")</li>
                                <li>Select exam type (Terminal, Final, Unit Test, etc.)</li>
                                <li>Choose the batch/classes</li>
                                <li>Set exam start and end dates</li>
                                <li>Add subjects and their exam dates/times</li>
                                <li>Enter maximum marks for each subject</li>
                                <li>Click "Create Exam" to save</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="exams">
                        <span class="faq-category">Exams & Results</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            15. How do I enter marks for students?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Exams" → "Enter Marks"</li>
                                <li>Select the exam from the dropdown</li>
                                <li>Choose the subject</li>
                                <li>Select the batch/class</li>
                                <li>Enter marks for each student</li>
                                <li>Enter practical marks if applicable</li>
                                <li>Add remarks for individual students if needed</li>
                                <li>Click "Save Marks"</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="exams">
                        <span class="faq-category">Exams & Results</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            16. How do I generate report cards?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Exams" → "Report Cards"</li>
                                <li>Select the exam</li>
                                <li>Choose the batch/class</li>
                                <li>Click "Generate Report Cards"</li>
                                <li>Preview the report cards</li>
                                <li>Select students (or all) to generate</li>
                                <li>Choose report card template</li>
                                <li>Click "Print" or "Download PDF"</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Batches & Courses -->
                    <div class="faq-item" data-category="batches">
                        <span class="faq-category">Batches & Courses</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            17. How do I create a new batch?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Batches" → "All Batches"</li>
                                <li>Click "+ Create Batch" button</li>
                                <li>Enter batch name (e.g., "Grade 10 - A")</li>
                                <li>Select the course/class</li>
                                <li>Set academic year</li>
                                <li>Set start and end dates</li>
                                <li>Select class teacher</li>
                                <li>Set maximum student capacity</li>
                                <li>Click "Create Batch"</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="batches">
                        <span class="faq-category">Batches & Courses</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            18. How do I assign subjects to a batch?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Batches" → "Subject Allocation"</li>
                                <li>Select the batch</li>
                                <li>Click "Add Subject"</li>
                                <li>Select subject from the list</li>
                                <li>Assign teacher for the subject</li>
                                <li>Set number of periods per week</li>
                                <li>Add more subjects as needed</li>
                                <li>Click "Save Allocation"</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Staff Management -->
                    <div class="faq-item" data-category="staff">
                        <span class="faq-category">Staff Management</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            19. How do I add a new teacher/staff member?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Staff" → "All Staff"</li>
                                <li>Click "+ Add Staff" button</li>
                                <li>Enter personal details (name, DOB, gender)</li>
                                <li>Add contact information</li>
                                <li>Select role (Teacher, Admin, Accountant, etc.)</li>
                                <li>Enter qualification and experience</li>
                                <li>Set salary details</li>
                                <li>Upload photo and documents</li>
                                <li>Click "Save Staff"</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="staff">
                        <span class="faq-category">Staff Management</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            20. How do I assign a class teacher to a batch?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Batches" → "All Batches"</li>
                                <li>Find and click on the batch</li>
                                <li>Click "Edit" button</li>
                                <li>Find "Class Teacher" field</li>
                                <li>Select teacher from dropdown</li>
                                <li>Click "Save Changes"</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Timetable -->
                    <div class="faq-item" data-category="timetable">
                        <span class="faq-category">Timetable</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            21. How do I create a class timetable?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Timetable" → "Create Timetable"</li>
                                <li>Select the batch/class</li>
                                <li>Set academic year and effective date</li>
                                <li>Click on a time slot in the grid</li>
                                <li>Select subject from dropdown</li>
                                <li>Select teacher (auto-filters based on subject)</li>
                                <li>Set period duration</li>
                                <li>Repeat for all days and periods</li>
                                <li>Click "Save Timetable"</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="timetable">
                        <span class="faq-category">Timetable</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            22. How do I view/print class timetable?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Timetable" → "View Timetable"</li>
                                <li>Select the batch/class</li>
                                <li>The timetable grid will display</li>
                                <li>Click "Print" button for printing</li>
                                <li>Or click "Download PDF" to save</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Library -->
                    <div class="faq-item" data-category="library">
                        <span class="faq-category">Library</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            23. How do I add books to the library?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Library" → "Add Books"</li>
                                <li>Enter book title</li>
                                <li>Enter author name(s)</li>
                                <li>Add ISBN number</li>
                                <li>Select category/genre</li>
                                <li>Enter publisher and publication year</li>
                                <li>Add number of copies</li>
                                <li>Set rack/location in library</li>
                                <li>Click "Add Book"</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="library">
                        <span class="faq-category">Library</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            24. How do I issue a book to a student?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Library" → "Issue Book"</li>
                                <li>Scan or enter book ISBN/ID</li>
                                <li>Search and select student</li>
                                <li>Set issue date (defaults to today)</li>
                                <li>Set due date (auto-calculated based on rules)</li>
                                <li>Add remarks if needed</li>
                                <li>Click "Issue Book"</li>
                                <li>Print issue slip if required</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Transport -->
                    <div class="faq-item" data-category="transport">
                        <span class="faq-category">Transport</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            25. How do I add a transport route?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Transport" → "Routes"</li>
                                <li>Click "+ Add Route"</li>
                                <li>Enter route name/number</li>
                                <li>Add start and end points</li>
                                <li>Add stops with timings</li>
                                <li>Assign vehicle</li>
                                <li>Assign driver</li>
                                <li>Set route fees</li>
                                <li>Click "Save Route"</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Communications -->
                    <div class="faq-item" data-category="communications">
                        <span class="faq-category">Communications</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            26. How do I send SMS to parents?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Communications" → "SMS"</li>
                                <li>Select recipients (All, Batch, or Individual)</li>
                                <li>If batch, select the batch name</li>
                                <li>Choose SMS template or type custom message</li>
                                <li>Preview the message</li>
                                <li>Click "Send SMS"</li>
                                <li>Confirm recipient count</li>
                                <li>Click "Confirm Send"</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="communications">
                        <span class="faq-category">Communications</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            27. How do I send emails to parents?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Communications" → "Email"</li>
                                <li>Select recipients</li>
                                <li>Enter email subject</li>
                                <li>Type message or use template</li>
                                <li>Attach files if needed</li>
                                <li>Click "Preview" to check</li>
                                <li>Click "Send Email"</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Reports -->
                    <div class="faq-item" data-category="reports">
                        <span class="faq-category">Reports</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            28. How do I generate admission/enquiry reports?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Inquiries" → "Reports"</li>
                                <li>Select report type</li>
                                <li>Choose date range</li>
                                <li>Select course/batch (optional)</li>
                                <li>Select enquiry status</li>
                                <li>Click "Generate Report"</li>
                                <li>Export as needed</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="reports">
                        <span class="faq-category">Reports</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            29. How do I view the institute dashboard statistics?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Click on "Dashboard" in the sidebar</li>
                                <li>View overview cards (Students, Staff, Fees, Attendance)</li>
                                <li>Scroll down for charts and graphs</li>
                                <li>Use date filters to change period</li>
                                <li>Click on any stat to see details</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="faq-item" data-category="settings">
                        <span class="faq-category">Settings</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            30. How do I change institute settings?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Settings" → "Institute Profile"</li>
                                <li>Update institute name</li>
                                <li>Update address and contact details</li>
                                <li>Upload/change institute logo</li>
                                <li>Set branding colors</li>
                                <li>Update registration numbers</li>
                                <li>Click "Save Changes"</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="settings">
                        <span class="faq-category">Settings</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            31. How do I manage user roles and permissions?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Settings" → "User Management"</li>
                                <li>Click on a user to edit</li>
                                <li>Select role (Admin, Teacher, Accountant, etc.)</li>
                                <li>Set specific permissions</li>
                                <li>Click "Save" to apply changes</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="settings">
                        <span class="faq-category">Settings</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            32. How do I configure SMS/Email settings?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Settings" → "Email Settings"</li>
                                <li>Enter SMTP server details</li>
                                <li>Enter username and password</li>
                                <li>Test the configuration</li>
                                <li>For SMS, go to "SMS Settings"</li>
                                <li>Enter SMS gateway API details</li>
                                <li>Save settings</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Academic Calendar -->
                    <div class="faq-item" data-category="academic">
                        <span class="faq-category">Academic Calendar</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            33. How do I add events to the academic calendar?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Academic Calendar"</li>
                                <li>Click on the date or "+ Add Event"</li>
                                <li>Enter event name</li>
                                <li>Select event type (Holiday, Exam, Event, etc.)</li>
                                <li>Set start and end dates</li>
                                <li>Add description</li>
                                <li>Select batches affected</li>
                                <li>Click "Save Event"</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Leave Management -->
                    <div class="faq-item" data-category="leave">
                        <span class="faq-category">Leave Management</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            34. How do I apply for leave?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Leave" → "Apply Leave"</li>
                                <li>Select leave type (Casual, Medical, etc.)</li>
                                <li>Select start and end dates</li>
                                <li>Enter reason for leave</li>
                                <li>Upload supporting document (if required)</li>
                                <li>Click "Submit Application"</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="leave">
                        <span class="faq-category">Leave Management</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            35. How do I approve/reject leave applications?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Leave" → "Pending Approvals"</li>
                                <li>View list of pending leave requests</li>
                                <li>Click on a request to view details</li>
                                <li>Review the application</li>
                                <li>Click "Approve" or "Reject"</li>
                                <li>Add remarks (optional)</li>
                                <li>Confirm the action</li>
                            </ol>
                        </div>
                    </div>

                    <!-- More Common Questions -->
                    <div class="faq-item" data-category="general">
                        <span class="faq-category">General</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            36. How do I backup my data?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Settings" → "Backup & Restore"</li>
                                <li>Click "Create Backup"</li>
                                <li>Select data to backup (All or specific modules)</li>
                                <li>Click "Start Backup"</li>
                                <li>Wait for backup to complete</li>
                                <li>Download the backup file</li>
                                <li>Store in a secure location</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="general">
                        <span class="faq-category">General</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            37. How do I restore data from a backup?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Settings" → "Backup & Restore"</li>
                                <li>Click "Restore" tab</li>
                                <li>Upload backup file</li>
                                <li>Verify backup details</li>
                                <li>Click "Start Restore"</li>
                                <li>Confirm the action (warning: current data may be overwritten)</li>
                                <li>Wait for restoration to complete</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="general">
                        <span class="faq-category">General</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            38. How do I change my password?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Click on your profile icon</li>
                                <li>Select "My Account"</li>
                                <li>Click "Change Password"</li>
                                <li>Enter current password</li>
                                <li>Enter new password</li>
                                <li>Confirm new password</li>
                                <li>Click "Update Password"</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="general">
                        <span class="faq-category">General</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            39. How do I enable two-factor authentication?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Settings" → "Security"</li>
                                <li>Find "Two-Factor Authentication"</li>
                                <li>Click "Enable"</li>
                                <li>Scan QR code with authenticator app</li>
                                <li>Enter verification code from app</li>
                                <li>Save backup codes</li>
                                <li>2FA is now enabled</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="general">
                        <span class="faq-category">General</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            40. How do I view system logs?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Settings" → "System Logs"</li>
                                <li>Select log type (Activity, Error, Login)</li>
                                <li>Choose date range</li>
                                <li>Click "View Logs"</li>
                                <li>Use filters to search specific events</li>
                                <li>Export logs if needed</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="students">
                        <span class="faq-category">Student Management</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            41. How do I transfer a student to another batch?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Students" and find the student</li>
                                <li>Open student profile</li>
                                <li>Click "Transfer" button</li>
                                <li>Select new batch/course</li>
                                <li>Set transfer date</li>
                                <li>Add reason for transfer</li>
                                <li>Click "Confirm Transfer"</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="students">
                        <span class="faq-category">Student Management</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            42. How do I issue a transfer certificate (TC)?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Students" → "Issue TC"</li>
                                <li>Search and select student</li>
                                <li>Verify student details</li>
                                <li>Enter TC details (reason, date, etc.)</li>
                                <li>Clear any pending dues</li>
                                <li>Click "Generate TC"</li>
                                <li>Print or download the certificate</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="fees">
                        <span class="faq-category">Fee Management</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            43. How do I handle partial fee payments?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Fees" → "Collect Fee"</li>
                                <li>Select the student</li>
                                <li>View total pending amount</li>
                                <li>Enter the partial amount being paid</li>
                                <li>System will show remaining balance</li>
                                <li>Complete the payment</li>
                                <li>Receipt will show paid and pending amounts</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="fees">
                        <span class="faq-category">Fee Management</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            44. How do I add a new fee category?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Fees" → "Fee Categories"</li>
                                <li>Click "+ Add Category"</li>
                                <li>Enter category name (e.g., "Library Fee")</li>
                                <li>Add description</li>
                                <li>Set if it's mandatory or optional</li>
                                <li>Click "Save Category"</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="exams">
                        <span class="faq-category">Exams & Results</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            45. How do I create an exam timetable?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Exams" → "Exam Timetable"</li>
                                <li>Select the exam</li>
                                <li>Click "Add Schedule" for each subject</li>
                                <li>Select subject</li>
                                <li>Set exam date and time</li>
                                <li>Enter duration</li>
                                <li>Add room/venue</li>
                                <li>Save each schedule</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="exams">
                        <span class="faq-category">Exams & Results</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            46. How do I calculate grades automatically?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Exams" → "Grading System"</li>
                                <li>Click "+ Add Grade Range"</li>
                                <li>Define grade ranges (A: 90-100, B: 80-89, etc.)</li>
                                <li>Set grade points for each</li>
                                <li>Save the grading system</li>
                                <li>When entering marks, grades auto-calculate</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="staff">
                        <span class="faq-category">Staff Management</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            47. How do I record staff attendance?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Staff" → "Attendance"</li>
                                <li>Select date</li>
                                <li>Mark Present/Absent for each staff</li>
                                <li>Add late remarks if needed</li>
                                <li>Click "Save Attendance"</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="staff">
                        <span class="faq-category">Staff Management</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            48. How do I process staff salary?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Staff" → "Payroll"</li>
                                <li>Select month and year</li>
                                <li>Click "Generate Payroll"</li>
                                <li>Review salary calculations</li>
                                <li>Adjust for leaves/deductions if needed</li>
                                <li>Click "Process Payroll"</li>
                                <li>Generate payslips</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="inquiries">
                        <span class="faq-category">Inquiries</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            49. How do I record a new admission inquiry?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Go to "Inquiries" → "Add Inquiry"</li>
                                <li>Enter student/parent name</li>
                                <li>Add contact details</li>
                                <li>Select course/program interested</li>
                                <li>Add source of inquiry</li>
                                <li>Enter expected joining date</li>
                                <li>Add notes/remarks</li>
                                <li>Click "Save Inquiry"</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item" data-category="inquiries">
                        <span class="faq-category">Inquiries</span>
                        <div class="faq-question" onclick="toggleFAQ(this)">
                            50. How do I convert an inquiry to admission?
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <strong>Step-by-step guide:</strong>
                            <ol>
                                <li>Navigate to "Inquiries" → "All Inquiries"</li>
                                <li>Find and click on the inquiry</li>
                                <li>Click "Convert to Admission"</li>
                                <li>System will redirect to student admission form</li>
                                <li>Pre-filled data from inquiry will appear</li>
                                <li>Complete remaining fields</li>
                                <li>Collect admission fee</li>
                                <li>Click "Complete Admission"</li>
                            </ol>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <script>
            // Toggle FAQ answer
            function toggleFAQ(element) {
                const answer = element.nextElementSibling;
                const isActive = element.classList.contains('active');
                
                // Close all others
                document.querySelectorAll('.faq-question').forEach(q => q.classList.remove('active'));
                document.querySelectorAll('.faq-answer').forEach(a => a.classList.remove('active'));
                
                // Toggle current
                if (!isActive) {
                    element.classList.add('active');
                    answer.classList.add('active');
                }
            }

            // Search FAQ
            function searchFAQ() {
                const searchTerm = document.getElementById('faqSearch').value.toLowerCase();
                const faqItems = document.querySelectorAll('.faq-item');
                
                faqItems.forEach(item => {
                    const question = item.querySelector('.faq-question').textContent.toLowerCase();
                    const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
                    
                    if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            }

            // Scroll to section
            function scrollToSection(id) {
                document.getElementById(id).scrollIntoView({ behavior: 'smooth' });
            }
        </script>
<?php if (!$isSPA): ?>
    </div>
</div>

<?php $v = time(); ?>
<script src="<?php echo APP_URL; ?>/public/assets/js/pwa-handler.js?v=<?php echo $v; ?>"></script>
</body>
</html>
<?php endif; ?>