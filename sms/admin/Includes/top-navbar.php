<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <!-- Navbar Brand-->
    <a class="navbar-brand ps-3" href="index.html">Store Management</a>
    <!-- Sidebar Toggle-->
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i
            class="fas fa-bars"></i></button>
    <!-- Navbar Search-->
    <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
        <div class="input-group">
            <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..."
                aria-describedby="btnNavbarSearch" />
            <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
        </div>
    </form>
    <!-- Navbar-->
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        
        <!-- Notifications Dropdown -->
        <?php
        // Fetch unread count
        $notif_count_query = "SELECT COUNT(*) as count FROM notifications WHERE is_read = 0";
        $notif_count_run = mysqli_query($conn, $notif_count_query);
        $notif_count = 0;
        if($notif_count_run) {
            $data = mysqli_fetch_assoc($notif_count_run);
            $notif_count = $data['count'];
        }

        // Fetch latest notifications
        $notif_list_query = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5";
        $notif_list_run = mysqli_query($conn, $notif_list_query);
        ?>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdownNotif" href="#" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <?php if($notif_count > 0): ?>
                    <span class="badge bg-danger rounded-pill" style="font-size: 0.7rem; position: absolute; top: 0; right: 0;"><?php echo $notif_count; ?></span>
                <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownNotif" style="min-width: 300px;">
                <li><h6 class="dropdown-header">Notifications</h6></li>
                <li><hr class="dropdown-divider" /></li>
                
                <?php
                if($notif_list_run && mysqli_num_rows($notif_list_run) > 0) {
                    while($row = mysqli_fetch_assoc($notif_list_run)) {
                        $bg_class = $row['is_read'] == 0 ? 'bg-light' : '';
                        $date_time = date('M d, H:i', strtotime($row['created_at']));
                        ?>
                        <li>
                            <a class="dropdown-item <?php echo $bg_class; ?>" href="mark_notification_read.php?id=<?php echo $row['id']; ?>">
                                <div class="small text-muted"><?php echo $date_time; ?></div>
                                <span class="fw-bold"><?php echo htmlspecialchars($row['message']); ?></span>
                            </a>
                        </li>
                        <?php
                    }
                } else {
                    echo '<li><a class="dropdown-item" href="#">No new notifications</a></li>';
                }
                ?>
                <li><hr class="dropdown-divider" /></li>
                <li><a class="dropdown-item text-center small text-muted" href="#!">View all notifications</a></li>
            </ul>
        </li>

        <!-- User Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown"
                aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="#!">Settings</a></li>
                <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                <li>
                    <hr class="dropdown-divider" />
                </li>
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-primary text-white">
            <i class="fas fa-bell me-2"></i>
            <strong class="me-auto">New Notification</strong>
            <small id="toastTime"></small>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <div id="toastMessage"></div>
            <div class="mt-2 pt-2 border-top">
                <a id="toastLink" href="#" class="btn btn-primary btn-sm">View Details</a>
            </div>
        </div>
    </div>
</div>

<script>
    let lastNotifId = 0;
    let notificationAudioEnabled = false;
    
    // Enable audio on first user interaction
    document.addEventListener('click', function enableAudio() {
        notificationAudioEnabled = true;
        // Play a silent sound to unlock audio context
        let audio = new Audio('assets/notification.mp3');
        audio.play().catch(() => {});
        document.removeEventListener('click', enableAudio);
    }, { once: true });
    
    // Function to check for new notifications
    function checkNotifications() {
        fetch('get_notifications.php?last_id=' + lastNotifId)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.notifications.length > 0) {
                    data.notifications.forEach(notif => {
                        // Update last ID
                        if (notif.id > lastNotifId) lastNotifId = notif.id;
                        
                        // Set header based on type
                        const header = document.querySelector('#liveToast .toast-header');
                        const icon = header.querySelector('i');
                        const title = header.querySelector('strong');
                        
                        if (notif.type === 'rent_request') {
                            header.className = 'toast-header bg-warning text-dark';
                            icon.className = 'fas fa-clipboard-list me-2';
                            title.innerText = 'New Rent Request';
                        } else {
                            header.className = 'toast-header bg-primary text-white';
                            icon.className = 'fas fa-bell me-2';
                            title.innerText = 'System Notification';
                        }

                        // Show toast
                        document.getElementById('toastMessage').innerHTML = `<strong>Details:</strong> <br>${notif.message}`;
                        document.getElementById('toastTime').innerText = 'Just now';
                        document.getElementById('toastLink').href = 'mark_notification_read.php?id=' + notif.id;
                        
                        const toastElement = document.getElementById('liveToast');
                        const toast = new bootstrap.Toast(toastElement, { autohide: 5000 });
                        toast.show();
                        
                        // Play notification sound (only if user has interacted with page)
                        if (notificationAudioEnabled) {
                            let audio = new Audio('assets/notification.mp3');
                            audio.play().catch(e => {
                                console.warn("Audio play failed:", e);
                            });
                        }
                        

                        // Refresh the page or update the dropdown count without reloading if possible
                        // For now, let's just update the badge count
                        updateNotificationCount();
                    });
                }
            })
            .catch(error => console.error('Error fetching notifications:', error));
    }

    function updateNotificationCount() {
        // This is a simple way to reload the count. 
        // More advanced: fetch the current count and update the DOM element navbarDropdownNotif span.badge
        const badge = document.querySelector('#navbarDropdownNotif .badge');
        if (badge) {
            let count = parseInt(badge.innerText) || 0;
            badge.innerText = count + 1;
            badge.style.display = 'inline';
        } else {
            // If badge doesn't exist, we might need to create it
            const link = document.getElementById('navbarDropdownNotif');
            const newBadge = document.createElement('span');
            newBadge.className = 'badge bg-danger rounded-pill';
            newBadge.style = 'font-size: 0.7rem; position: absolute; top: 0; right: 0;';
            newBadge.innerText = '1';
            link.appendChild(newBadge);
        }
    }

    window.addEventListener("DOMContentLoaded", (event) => {
        // Show toast to inform user about enabling sound
        const soundToast = document.createElement('div');
        soundToast.className = 'toast show';
        soundToast.setAttribute('role', 'alert');
        soundToast.setAttribute('aria-live', 'assertive');
        soundToast.setAttribute('aria-atomic', 'true');
        soundToast.style.cssText = 'position: fixed; bottom: 20px; left: 20px; z-index: 1100;';
        soundToast.innerHTML = `
            <div class="toast-header bg-info text-white">
                <i class="fas fa-volume-up me-2"></i>
                <strong class="me-auto">Enable Sound</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Click anywhere to enable notification sounds
            </div>
        `;
        document.body.appendChild(soundToast);
        setTimeout(() => {
            soundToast.classList.remove('show');
            setTimeout(() => soundToast.remove(), 300);
        }, 5000);
        
        // Initial check to set the baseline ID so we don't toast old unread notifications
        fetch('get_notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.notifications.length > 0) {
                    lastNotifId = data.notifications[data.notifications.length - 1].id;
                }
                
                // Start polling every 10 seconds
                setInterval(checkNotifications, 10000);
            });

        // Toggle the side navigation
        const sidebarToggle = document.body.querySelector("#sidebarToggle");
        if (sidebarToggle) {
            if (localStorage.getItem("sb|sidebar-toggle") === "true") {
                document.body.classList.toggle("sb-sidenav-toggled");
            }
            sidebarToggle.addEventListener("click", (event) => {
                event.preventDefault();
                document.body.classList.toggle("sb-sidenav-toggled");
                localStorage.setItem(
                    "sb|sidebar-toggle",
                    document.body.classList.contains("sb-sidenav-toggled")
                );
            });
        }
    });

</script>