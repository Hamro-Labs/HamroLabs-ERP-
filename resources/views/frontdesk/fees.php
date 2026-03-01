<?php
/**
 * Front Desk Portal - Fee Collection View
 */
?>

<div id="frontDeskFeeContainer">
    <div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Initializing Front Desk Collection module...</span></div>
</div>

<script src="<?php echo base_url('assets/js/ia-fees.js'); ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const page = new URLSearchParams(window.location.search).get('page');
        if (page === 'fee-fee-coll') {
            renderFeeRecord();
        } else if (page === 'fee-fee-out') {
            renderFeeOutstanding();
        }
    });
</script>
