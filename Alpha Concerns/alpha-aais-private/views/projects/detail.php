<?php
$page_title       = $project['title'];
$page_description = $project['seo_description'] ?: $project['short_description'];
$page_og_image    = $project['og_image'] ?: $project['hero_image'];

$page_jsonld = jsonld([
    '@context'=>'https://schema.org',
    '@type'=>'RealEstateListing',
    'name'=>$project['title'],
    'description'=>$project['short_description'],
    'image'=>url($project['hero_image']),
    'url'=>url('/projects/' . $project['slug']),
    'address'=>['@type'=>'PostalAddress','addressLocality'=>$project['location']],
]);

partial('head', compact('page_title','page_description','page_og_image','page_jsonld'));
partial('header');

$specs    = json_decode($project['key_specs']  ?? '[]', true) ?: [];
$amenities = json_decode($project['amenities'] ?? '[]', true) ?: [];
$gallery   = array_filter($images ?? [], fn($i) => $i['type'] === 'gallery');
$plans     = array_filter($images ?? [], fn($i) => $i['type'] === 'floor_plan');
?>
<main id="main">
  <section class="hero" style="min-height: 80vh;">
    <div class="hero__bg" style="background-image: linear-gradient(135deg,rgba(20,18,14,0.5),rgba(20,18,14,0.85)), url('<?= e($project['hero_image']) ?>'); background-size:cover;"></div>
    <div class="container hero__inner">
      <div class="eyebrow"><?= e($project['type']) ?> · <?= e($project['status']) ?></div>
      <h1 class="hero__title" data-split-words><?= e($project['title']) ?></h1>
      <p class="hero__sub reveal"><?= e($project['location']) ?></p>
    </div>
  </section>

  <section class="section section--cream">
    <div class="container split">
      <div>
        <div class="eyebrow reveal">About the project</div>
        <h2 class="display display-md reveal" style="margin: 1rem 0 1.5rem;">Overview</h2>
        <div class="reveal"><?= $project['full_description'] ?: '<p>' . e($project['short_description']) . '</p>' ?></div>
      </div>
      <div>
        <?php if ($specs): ?>
        <div class="service-card reveal">
          <div class="eyebrow" style="margin-bottom:1.25rem;">Key Specifications</div>
          <?php foreach ($specs as $k => $v): ?>
            <div style="display:flex; justify-content:space-between; padding:.85rem 0; border-bottom:1px solid var(--color-border); font-size:.9rem;">
              <span style="color:var(--color-text-muted); text-transform:capitalize;"><?= e(str_replace('_',' ',$k)) ?></span>
              <span style="color:var(--color-text-primary); font-weight:500; text-align:right;"><?= e(is_array($v)?implode(', ',$v):$v) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <a href="/contact?subject=<?= rawurlencode('Project Enquiry - ' . $project['title']) ?>" class="btn btn-primary reveal" style="margin-top:1.5rem; width:100%; justify-content:center;">Enquire about this project</a>
      </div>
    </div>
  </section>

  <?php if ($amenities): ?>
  <section class="section section--surface">
    <div class="container">
      <div style="text-align:center; max-width:640px; margin:0 auto 3rem;">
        <div class="eyebrow reveal">Amenities</div>
        <h2 class="display display-lg reveal" style="margin-top:1rem;">What's included</h2>
      </div>
      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
        <?php foreach ($amenities as $a): ?>
          <div class="reveal" style="display:flex; align-items:center; gap:.85rem; padding:1rem 1.25rem; background:var(--color-primary); border-left:2px solid var(--color-accent);">
            <span style="color:var(--color-accent);">✓</span> <?= e($a) ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($gallery): ?>
  <section class="section section--cream">
    <div class="container">
      <div style="text-align:center; margin-bottom:3rem;">
        <div class="eyebrow reveal">Gallery</div>
      </div>
      <div class="projects-grid">
        <?php foreach ($gallery as $img): ?>
        <div class="project-card reveal" style="aspect-ratio:1;">
          <div class="project-card__img" style="background-image:url('<?= e($img['image_path']) ?>');"></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($plans): ?>
  <section class="section section--surface">
    <div class="container">
      <div style="text-align:center; margin-bottom:3rem;">
        <div class="eyebrow reveal">Floor Plans</div>
      </div>
      <div class="cards-grid" style="margin-top:0;">
        <?php foreach ($plans as $img): ?>
        <div class="reveal"><img src="<?= e($img['image_path']) ?>" alt="<?= e($img['alt_text']) ?>" style="background:var(--color-primary); padding:1rem;"></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>
</main>
<?php partial('footer'); ?>
