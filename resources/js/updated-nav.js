// Updated Navigation Structure for Teacher Dashboard
const NAV = [
    { id: "dashboard", icon: "fa-house", label: "Dashboard", sub: null, sec: "MAIN" },
    { id: "classes", icon: "fa-calendar-days", label: "My Classes", sub: [
        { id: "today", l: "Today's Schedule", icon: "fa-calendar-day" },
        { id: "timetable", l: "Full Timetable", icon: "fa-calendar-week" },
        { id: "substitute", l: "Substitute Requests", icon: "fa-hand-holding-hand" }
    ], sec: "ACADEMIC" },
    { id: "attendance", icon: "fa-check-circle", label: "Attendance", sub: [
        { id: "mark", l: "Mark Attendance", icon: "fa-calendar-check" },
        { id: "history", l: "Attendance History", icon: "fa-clock-rotate-left" },
        { id: "leave-approvals", l: "Leave Approvals", icon: "fa-file-signature" }
    ], sec: "ACADEMIC" },
    { id: "lms", icon: "fa-book-open", label: "LMS", sub: [
        { id: "materials", l: "Study Materials", icon: "fa-file-pdf" },
        { id: "upload-video", l: "Upload Video", icon: "fa-video" },
        { id: "lesson-plans", l: "Lesson Plans", icon: "fa-list-check" },
        { id: "online-links", l: "Online Class Links", icon: "fa-link" }
    ], sec: "ACADEMIC" },
    { id: "assignments", icon: "fa-file-lines", label: "Assignments", sub: [
        { id: "create", l: "Create Assignment", icon: "fa-plus" },
        { id: "pending", l: "Pending Evaluations", icon: "fa-pen-to-square" },
        { id: "graded", l: "Graded Submissions", icon: "fa-check-double" }
    ], sec: "ACADEMIC" },
    { id: "exams", icon: "fa-file-contract", label: "Exams & Results", sub: [
        { id: "qb", l: "My Question Bank", icon: "fa-database" },
        { id: "create-exam", l: "Create Exam", icon: "fa-plus-circle" },
        { id: "results", l: "Exam Results", icon: "fa-square-poll-vertical" },
        { id: "analytics", l: "Performance Analytics", icon: "fa-chart-line" }
    ], sec: "ACADEMIC" },
    { id: "profile", icon: "fa-user", label: "My Profile", sub: [
        { id: "personal", l: "Personal Details", icon: "fa-id-card" },
        { id: "qualifications", l: "Qualifications", icon: "fa-graduation-cap" },
        { id: "salary-slips", l: "Salary Slips", icon: "fa-wallet" },
        { id: "leave-history", l: "Leave History", icon: "fa-calendar-days" }
    ], sec: "PERSONAL" },
];

// Export the updated navigation
export default NAV;
