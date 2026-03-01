<?php
/**
 * Student Portal - Fee View
 */
?>

<div class="pg fu">
    <div class="bc"><a href="#" onclick="goNav('overview')">Home</a> <span class="bc-sep">&rsaquo;</span> <span class="bc-cur">My Fees</span></div>
    <div class="pg-head">
        <div class="pg-left"><div class="pg-ico"><i class="fa-solid fa-wallet"></i></div><div><div class="pg-title">My Fee Account</div><div class="pg-sub">View your payment history and outstanding dues</div></div></div>
    </div>

    <!-- Balance Summary -->
    <div id="studentBalanceSummary" style="margin-bottom:25px;"></div>

    <!-- Tabs -->
    <div class="tab-wrap mb">
        <div class="tabs">
            <button class="tab-btn active" onclick="_switchFeeTab('outstanding')">Outstanding Dues</button>
            <button class="tab-btn" onclick="_switchFeeTab('history')">Payment History</button>
        </div>
    </div>

    <div id="studentFeeContent">
        <div class="pg-loading"><i class="fa-solid fa-circle-notch fa-spin"></i><span>Loading...</span></div>
    </div>
</div>

<script>
    // Note: This script will be part of ia-student.js (to be created/updated)
    // For now, it's a placeholder for the logic
</script>
