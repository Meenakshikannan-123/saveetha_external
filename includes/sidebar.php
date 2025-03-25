<!-- Side bar -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

<div class="app-brand demo">
    <a href="dashboard.php" class="app-brand-link">
        <span class="app-brand-logo demo">
            <img src="../assets/img/favicon/favicon.ico" alt="" width=30px; height=30px; >
        </span>
        <span class="app-brand-text demo menu-text fw-bolder ms-2" style="font-size:1.5rem !important;font-weight: 600 !important;">SIMATS</span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
        <i class="bx bx-chevron-left bx-sm align-middle"></i>
    </a>
</div>

<div class="menu-inner-shadow"></div>

<ul class="menu-inner py-1">

    <!-- Dashboard -->
    <li class="menu-item active">
        <a href="index.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-home-circle"></i>
            <div data-i18n="Analytics">Dashboard</div>
        </a>
    </li>

    <!-- courses -->
    <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Exam</span>
    </li>
    <li class="menu-item">
        <a href="#" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-dock-top"></i>
            <div data-i18n="Account Settings">Exam</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item">
                <a href="create_course.php" class="menu-link">
                    <div data-i18n="Account">Create Exam</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="view_course.php" class="menu-link">
                    <div data-i18n="Notifications">View Exam</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="exam_approve.php" class="menu-link">
                    <div data-i18n="Connections">Exam Approve</div>
                </a>
            </li>
        </ul>
    </li>
    <li class="menu-item">
        <a href="#" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-box"></i>
            <div data-i18n="Account Settings">Subjects</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item">
                <a href="add_subject.php" class="menu-link">
                    <div data-i18n="Account">Add Subject</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="manage_subject.php" class="menu-link">
                    <div data-i18n="Notifications">Manage Subject</div>
                </a>
            </li>
           
        </ul>
    </li>
    
    <!-- Question Paper -->
    <li class="menu-header small text-uppercase"><span class="menu-header-text">Question Paper</span></li>

    <!-- Question Paper -->
    <li class="menu-item">
        <a href="add_question.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-table"></i>
            <div data-i18n="Tables">Question Bank</div>
        </a>
    </li>

    <!-- <li class="menu-item">
        <a href="manage_question.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-crown"></i>
            <div data-i18n="Boxicons">Manage Questions</div>
        </a>
    </li> -->


    <!-- Attendance -->
    <li class="menu-header small text-uppercase"><span class="menu-header-text">Attendance</span></li>
    <!-- Cards -->
   
    <!-- Attendance -->
    <li class="menu-item">
        <a href="javascript:void(0)" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-box"></i>
            <div data-i18n="User interface">Attendance</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item">
                <a href="ui-accordion.html" class="menu-link">
                    <div data-i18n="Accordion">Attendance Marking</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="ui-accordion.html" class="menu-link">
                    <div data-i18n="Accordion">Grade</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="ui-alerts.html" class="menu-link">
                    <div data-i18n="Alerts">OD Approval</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="ui-badges.html" class="menu-link">
                    <div data-i18n="Badges">Course Attendance</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="ui-buttons.html" class="menu-link">
                    <div data-i18n="Buttons">Student Attendance</div>
                </a>
            </li>

        </ul>
    </li>

    
    <!-- 360 View -->
    <li class="menu-header small text-uppercase"><span class="menu-header-text">Student Details</span></li>
    <li class="menu-item">
        <a href="icons-boxicons.html" class="menu-link">
            <i class="menu-icon tf-icons bx bx-crown"></i>
            <div data-i18n="Boxicons">Student 360° View</div>
        </a>
        
    </li>
    

</ul>
</aside>
<!-- Side bar end -->