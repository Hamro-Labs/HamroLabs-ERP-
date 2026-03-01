<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Management Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <?php if (isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark'): ?>
        <link href="css/dark-theme.css" rel="stylesheet" />
    <?php endif; ?>

</head>
<style>
@media print {
    .sb-topnav, #layoutSidenav_nav, .sb-nav-fixed #layoutSidenav #layoutSidenav_nav, footer, .no-print {
        display: none !important;
    }
    body.sb-nav-fixed {
        padding-top: 0 !important;
    }
    #layoutSidenav {
        display: block !important;
    }
    #layoutSidenav_content {
        margin-left: 0 !important;
        padding: 0 !important;
        display: block !important;
        width: 100% !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
    }
}
</style>

<body class="sb-nav-fixed">


    <?php include 'includes/top-navbar.php'; ?>


    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <?php include 'includes/sidebar.php' ?>
        </div>
        <div id="layoutSidenav_content">
            <main>