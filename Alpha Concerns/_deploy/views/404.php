<?php partial('head', ['page_title' => 'Page Not Found']); partial('header'); ?>
<main id="main">
  <section class="hero" style="min-height:100vh;">
    <div class="hero__bg"></div>
    <div class="container hero__inner" style="text-align:center;">
      <div class="display" style="font-size: clamp(8rem, 22vw, 16rem); line-height:1; color:rgba(200,146,42,0.3);">404</div>
      <h1 class="display display-lg" style="margin: 1.5rem 0;">Page not found</h1>
      <p class="lede reveal" style="margin: 0 auto 2.5rem;">The page you're looking for doesn't exist or has been moved.</p>
      <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
        <a href="/" class="btn btn-primary">Back Home</a>
        <a href="/projects" class="btn btn-ghost">View Projects</a>
      </div>
    </div>
  </section>
</main>
<?php partial('footer'); ?>
