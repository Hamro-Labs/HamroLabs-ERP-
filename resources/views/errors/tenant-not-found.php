<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Institute Not Found — Hamro Labs ERP</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    :root {
      --green: #006D44;
      --green-d: #004D30;
      --green-light: #8CC63F;
      --navy: #003D2E;
      --text-dark: #1A3C34;
      --text-body: #4A6355;
      --text-light: #7A9488;
      --red: #D32F2F;
      --white: #ffffff;
      --gray-100: #F7F9FC;
      --gray-200: #EDF2F7;
      --gray-300: #E2E8F0;
      --gray-500: #718096;
      --gray-700: #4A5568;
      --gray-900: #1A202C;
      --font: 'Poppins', sans-serif;
    }
    
    * { 
      margin: 0; 
      padding: 0; 
      box-sizing: border-box; 
    }
    
    body { 
      font-family: var(--font);
      background: var(--gray-100);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .error-container {
      text-align: center;
      padding: 40px;
      max-width: 600px;
      margin: 20px;
    }

    .error-icon {
      width: 120px;
      height: 120px;
      background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 30px;
      animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }

    .error-icon i {
      font-size: 48px;
      color: var(--red);
    }

    .error-code {
      font-size: 72px;
      font-weight: 900;
      color: var(--gray-300);
      line-height: 1;
      margin-bottom: 10px;
    }

    .error-title {
      font-size: 28px;
      font-weight: 700;
      color: var(--gray-900);
      margin-bottom: 16px;
    }

    .error-message {
      font-size: 16px;
      color: var(--gray-500);
      line-height: 1.6;
      margin-bottom: 32px;
    }

    .error-message strong {
      color: var(--text-dark);
    }

    .error-subdomain {
      background: var(--white);
      border: 2px dashed var(--gray-300);
      border-radius: 12px;
      padding: 16px 24px;
      display: inline-block;
      margin-bottom: 32px;
      font-family: monospace;
      font-size: 18px;
      color: var(--green);
      font-weight: 600;
    }

    .error-actions {
      display: flex;
      gap: 16px;
      justify-content: center;
      flex-wrap: wrap;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 14px 28px;
      border-radius: 10px;
      font-family: var(--font);
      font-size: 15px;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.2s ease;
      border: none;
    }

    .btn--primary {
      background: var(--green);
      color: var(--white);
    }

    .btn--primary:hover {
      background: var(--green-d);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 109, 68, 0.3);
    }

    .btn--outline {
      background: transparent;
      color: var(--text-dark);
      border: 2px solid var(--gray-300);
    }

    .btn--outline:hover {
      border-color: var(--green);
      color: var(--green);
    }

    .help-text {
      margin-top: 40px;
      padding-top: 30px;
      border-top: 1px solid var(--gray-200);
      font-size: 14px;
      color: var(--gray-500);
    }

    .help-text a {
      color: var(--green);
      text-decoration: none;
      font-weight: 600;
    }

    .help-text a:hover {
      text-decoration: underline;
    }

    /* Decorative elements */
    .bg-shape {
      position: fixed;
      border-radius: 50%;
      opacity: 0.5;
      z-index: -1;
    }

    .bg-shape--1 {
      width: 400px;
      height: 400px;
      background: linear-gradient(135deg, #E8F5E0 0%, #C8E6C9 100%);
      top: -100px;
      left: -100px;
    }

    .bg-shape--2 {
      width: 300px;
      height: 300px;
      background: linear-gradient(135deg, #FDE8E8 0%, #FECACA 100%);
      bottom: -50px;
      right: -50px;
    }

    @media (max-width: 480px) {
      .error-code {
        font-size: 56px;
      }
      .error-title {
        font-size: 22px;
      }
      .error-message {
        font-size: 14px;
      }
      .error-actions {
        flex-direction: column;
      }
      .btn {
        width: 100%;
        justify-content: center;
      }
    }
  </style>
</head>
<body>
  <!-- Decorative background shapes -->
  <div class="bg-shape bg-shape--1"></div>
  <div class="bg-shape bg-shape--2"></div>

  <div class="error-container">
    <div class="error-icon">
      <i class="fa-solid fa-school"></i>
    </div>
    
    <div class="error-code">404</div>
    
    <h1 class="error-title">Institute Not Found</h1>
    
    <p class="error-message">
      <?php if (!empty($subdomain)): ?>
        The institute at <strong><?= htmlspecialchars($subdomain) ?></strong> could not be found.<br>
        The URL may be incorrect or the institute may no longer be active.
      <?php elseif (!empty($tenantName)): ?>
        The institute <strong><?= htmlspecialchars($tenantName) ?></strong> could not be found.<br>
        This institute may have been suspended or deleted.
      <?php else: ?>
        We couldn't find the institute you're looking for.<br>
        The URL may be incorrect or the institute may no longer be active.
      <?php endif; ?>
    </p>

    <?php if (!empty($subdomain)): ?>
      <div class="error-subdomain">
        <i class="fa-solid fa-link"></i>
        <?= htmlspecialchars($subdomain) ?>.hamrolabs.com
      </div>
    <?php endif; ?>

    <div class="error-actions">
      <a href="<?= defined('APP_URL') ? APP_URL : '/erp' ?>" class="btn btn--primary">
        <i class="fa-solid fa-home"></i>
        Go to Homepage
      </a>
      <a href="<?= defined('APP_URL') ? APP_URL : '/erp' ?>/login" class="btn btn--outline">
        <i class="fa-solid fa-right-to-bracket"></i>
        Login
      </a>
    </div>

    <div class="help-text">
      <p>Are you the owner of this institute?</p>
      <p>Contact our <a href="mailto:support@hamrolabs.com">support team</a> for help or 
         <a href="<?= defined('APP_URL') ? APP_URL : '/erp' ?>#contact">request a demo</a> to create a new institute.</p>
    </div>
  </div>
</body>
</html>
