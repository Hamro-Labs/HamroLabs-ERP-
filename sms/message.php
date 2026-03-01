<?php
// Handle both 'message' and 'error' session variables
$show_toast = false;
$message = '';
$message_type = 'bg-success';

if (isset($_SESSION['error'])) {
    $show_toast = true;
    $message = $_SESSION['error'];
    $message_type = 'bg-danger';
    unset($_SESSION['error']);
} elseif (isset($_SESSION['message'])) {
    $show_toast = true;
    $message = $_SESSION['message'];
    
    // Check for explicit message type
    if (isset($_SESSION['message_type']) && $_SESSION['message_type'] === 'error') {
        $message_type = 'bg-danger';
        unset($_SESSION['message_type']);
    } else {
        // Auto-detect message type: success (green) or error (red)
        $lower_msg = strtolower($message);
        if (str_contains($lower_msg, 'invalid') || str_contains($lower_msg, 'wrong') || 
            str_contains($lower_msg, 'fail') || str_contains($lower_msg, 'error') ||
            str_contains($lower_msg, 'required') || str_contains($lower_msg, 'cannot') ||
            str_contains($lower_msg, 'already')) {
            $message_type = 'bg-danger';
        }
    }
    unset($_SESSION['message']);
}

if ($show_toast):
?>
    
    <!-- Toast Container -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="liveToast" class="toast align-items-center text-white <?= $message_type ?> border-0" 
             role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
            <div class="d-flex ">
                <div class="toast-body">
                    <strong><?= $message_type == 'bg-success' ? 'Success!' : 'Error!' ?></strong> <?= htmlspecialchars($message); ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
      document.addEventListener("DOMContentLoaded", function () {
        var toastEl = document.getElementById('liveToast');
        if (toastEl) {
            var toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
      });
    </script>

<?php
endif;
?>
