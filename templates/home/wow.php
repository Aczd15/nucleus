<section class="hero wow-hero">
    <h1>WOW Space by Nucleus</h1>
    <p>Иммерсивная зона, где технологии можно не просто посмотреть, а почувствовать: скорость, камера, звук, стабильность и экосистема.</p>
    <a class="nav-link nav-link-accent" href="<?= htmlspecialchars(url('/catalog')) ?>">Перейти в каталог</a>
</section>

<section>
    <h2>Что вас удивит</h2>
    <div class="grid wow-grid">
        <?php foreach ($highlights as $item): ?>
            <article class="card wow-card">
                <h3><?= htmlspecialchars($item['title']) ?></h3>
                <p><?= htmlspecialchars($item['text']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>
