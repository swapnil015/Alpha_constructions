<?php
$page_title       = $post['title'];
$page_description = $post['seo_description'] ?: excerpt($post['body'], 30);
$page_og_image    = $post['og_image'] ?: $post['featured_image'];

$jsonldData = [
    '@context'=>'https://schema.org','@type'=>'Article',
    'headline'=>$post['title'],
    'datePublished'=>$post['published_at'],
    'author'=>['@type'=>'Organization','name'=>$post['author_name'] ?: SITE_NAME],
    'publisher'=>['@type'=>'Organization','name'=>SITE_NAME,'url'=>BASE_URL],
];
$page_jsonld = jsonld($jsonldData);
if (!empty($post['faq_schema'])) {
    $faqs = json_decode($post['faq_schema'], true) ?: [];
    if ($faqs) {
        $page_jsonld .= jsonld([
            '@context'=>'https://schema.org','@type'=>'FAQPage',
            'mainEntity'=>array_map(fn($f)=>['@type'=>'Question','name'=>$f['q'],'acceptedAnswer'=>['@type'=>'Answer','text'=>$f['a']]], $faqs)
        ]);
    }
}

partial('head', compact('page_title','page_description','page_og_image','page_jsonld'));
partial('header');

$related = db_all("SELECT * FROM blog_posts WHERE status='published' AND id != ? AND category = ? ORDER BY published_at DESC LIMIT 3",
                  [$post['id'], $post['category']]);
?>
<main id="main">
  <article>
    <section class="hero" style="min-height: 60vh;">
      <div class="hero__bg"></div>
      <div class="container hero__inner">
        <div class="eyebrow"><?= e($post['category']) ?></div>
        <h1 class="hero__title" data-split-words style="font-size: clamp(2rem, 5vw, 4rem);"><?= e($post['title']) ?></h1>
        <p class="hero__sub reveal"><?= fmt_date($post['published_at']) ?> · <?= read_time($post['body']) ?> min read · <?= e($post['author_name']) ?></p>
      </div>
    </section>

    <section class="section section--cream">
      <div class="container-md" style="max-width: 760px; color: var(--color-text-secondary); font-size: 1.05rem; line-height: 1.8;">
        <?= $post['body'] ?>
      </div>
    </section>

    <section class="section section--surface" style="padding-block: 3rem;">
      <div class="container-md" style="max-width: 760px;">
        <div class="eyebrow" style="margin-bottom:1rem;">Share</div>
        <div style="display:flex; gap:1rem;">
          <a class="btn btn-ghost" href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode(url('/blog/' . $post['slug'])) ?>" target="_blank" rel="noopener">Facebook</a>
          <a class="btn btn-ghost" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode(url('/blog/' . $post['slug'])) ?>" target="_blank" rel="noopener">LinkedIn</a>
          <a class="btn btn-ghost" href="https://wa.me/?text=<?= rawurlencode($post['title'] . ' — ' . url('/blog/' . $post['slug'])) ?>" target="_blank" rel="noopener">WhatsApp</a>
        </div>
      </div>
    </section>

    <?php if ($related): ?>
    <section class="section section--cream">
      <div class="container">
        <div style="margin-bottom:3rem;">
          <div class="eyebrow reveal">Related</div>
          <h2 class="display display-md reveal" style="margin-top:1rem;">Continue reading</h2>
        </div>
        <div class="cards-grid" style="margin-top:0;">
          <?php foreach ($related as $r): ?>
            <a href="/blog/<?= e($r['slug']) ?>" class="service-card reveal" style="aspect-ratio: 4/5; display:flex; flex-direction:column; justify-content:flex-end;">
              <div class="eyebrow" style="margin-bottom:1rem;"><?= e($r['category']) ?></div>
              <h3 style="font-family:var(--font-display); font-weight:400; font-size:1.35rem; color:var(--color-text-primary); line-height:1.2;"><?= e($r['title']) ?></h3>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>
  </article>
</main>
<?php partial('footer'); ?>
