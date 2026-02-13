<section class="hero">
    <h1>Nucleus — мобильные устройства нового поколения</h1>
    <p>Мы создаем удобные смартфоны, сервис и цифровую экосистему для работы, творчества и повседневной жизни. Наша цель — дать пользователям технологичный продукт без лишней сложности.</p>
</section>

<section>
    <h2>О компании</h2>
    <div class="grid">
        <article class="card">
            <h3>Качество</h3>
            <p>Каждая модель проходит многоуровневое тестирование: автономность, камеры, стабильность и безопасность.</p>
        </article>
        <article class="card">
            <h3>Поддержка</h3>
            <p>Онлайн-помощь 24/7, быстрая гарантийная диагностика и удобные способы обмена устройства.</p>
        </article>
        <article class="card">
            <h3>Инновации</h3>
            <p>Мы внедряем AI-функции в камеру и систему, чтобы смартфон подстраивался под ваш стиль использования.</p>
        </article>
    </div>
</section>

<section>
    <h2>Новости компании</h2>
    <div class="grid">
        <?php foreach ($news as $item): ?>
            <article class="card">
                <h3><?= htmlspecialchars($item['title']) ?></h3>
                <p><?= htmlspecialchars($item['text']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section>
    <h2>Популярные модели</h2>
    <div class="grid phones">
        <?php foreach ($phones as $phone): ?>
            <article class="card">
                <div class="product-image-frame"><img class="product-image" src="<?= htmlspecialchars($phone['image'] ?: 'https://placehold.co/640x480?text=Phone') ?>" alt="<?= htmlspecialchars($phone['name']) ?>" loading="lazy"></div>
                <h3><?= htmlspecialchars($phone['name']) ?></h3>
                <p><?= htmlspecialchars($phone['description']) ?></p>
                <strong><?= number_format((float)$phone['price'], 0, '.', ' ') ?> ₽</strong>
            </article>
        <?php endforeach; ?>
    </div>
</section>
