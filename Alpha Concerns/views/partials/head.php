<?php
$title    = ($page_title ?? '') ? ($page_title . ' | ' . SITE_NAME) : SITE_NAME . ' — ' . SITE_TAGLINE;
$desc     = $page_description ?? setting('seo_default_description', 'Premium construction and real estate development in Kathmandu, Nepal.');
$ogImage  = $page_og_image ?? setting('seo_default_og_image', '/assets/img/og-default.jpg');
$canon    = $page_canonical ?? url($_SERVER['REQUEST_URI'] ?? '/');
$gaId     = setting('ga4_measurement_id', '');
$gscMeta  = setting('gsc_verification', '');
?>
<!DOCTYPE html>
<html lang="en" data-ga-id="<?= e($gaId) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#003030">
  <title><?= e($title) ?></title>
  <meta name="description" content="<?= e($desc) ?>">
  <link rel="canonical" href="<?= e($canon) ?>">

  <!-- Open Graph -->
  <meta property="og:title" content="<?= e($title) ?>">
  <meta property="og:description" content="<?= e($desc) ?>">
  <meta property="og:image" content="<?= e(url($ogImage)) ?>">
  <meta property="og:url" content="<?= e($canon) ?>">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
  <meta name="twitter:card" content="summary_large_image">

  <?php if ($gscMeta): ?><meta name="google-site-verification" content="<?= e($gscMeta) ?>"><?php endif; ?>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300&family=DM+Sans:wght@300;400;500&family=Syne:wght@600;700&display=swap" rel="stylesheet">

  <!-- Icons (inline-friendly minimal use) -->
  <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">

  <!-- Styles -->
  <link rel="stylesheet" href="<?= asset('assets/css/main.css') ?>">

  <!-- Structured Data: LocalBusiness -->
  <?= jsonld([
      '@context'     => 'https://schema.org',
      '@type'        => 'ConstructionCompany',
      'name'         => setting('company_name', SITE_NAME),
      'url'          => BASE_URL,
      'logo'         => url('/assets/img/logo.jpg'),
      'image'        => url('/assets/img/logo.jpg'),
      'address'      => [
        '@type' => 'PostalAddress',
        'streetAddress'   => 'Maharajgunj 4',
        'addressLocality' => 'Kathmandu',
        'addressRegion'   => 'Bagmati',
        'addressCountry'  => 'NP',
      ],
      'telephone'    => setting('phone_primary',''),
      'email'        => setting('email_primary',''),
      'description'  => setting('description', $desc),
      'openingHours' => 'Su-Fr 10:00-18:00',
      'sameAs'       => array_filter([
        setting('social_facebook') !== '#' ? setting('social_facebook') : null,
        setting('social_instagram') !== '#' ? setting('social_instagram') : null,
        setting('social_linkedin') !== '#' ? setting('social_linkedin') : null,
        setting('social_youtube') !== '#' ? setting('social_youtube') : null,
      ]),
  ]) ?>

  <?= $page_jsonld ?? '' ?>

  <!-- GA4 -->
  <?php if ($gaId): ?>
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($gaId) ?>"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    if (localStorage.getItem('alpha_cookie_choice') === 'decline') { window['ga-disable-<?= e($gaId) ?>'] = true; }
    gtag('js', new Date());
    gtag('config', '<?= e($gaId) ?>', { anonymize_ip: true });
  </script>
  <?php endif; ?>
</head>
<body>
  <a class="skip-link" href="#main">Skip to main content</a>
