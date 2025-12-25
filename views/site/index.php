<?php

/** @var yii\web\View $this */
/** @var string $heroImage */
/** @var string $heroTitle */
/** @var string $heroSubtitle */
/** @var app\models\Article[] $popularArticles */
/** @var app\models\Article[] $latestArticles */
/** @var app\models\Category[] $categories */
/** @var app\models\Tag[] $tags */
/** @var string|null $randomMovieInitialImage */

use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Home';
?>
<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-image" style="background-image: url('<?= Html::encode($heroImage) ?>');"></div>
    
    <!-- Overlay для затемнення -->
    <div class="hero-overlay"></div>
    
    <!-- Підпис -->
    <div class="hero-content">
        <h1 class="hero-title"><?= Html::encode($heroTitle) ?></h1>
        <p class="hero-subtitle"><?= Html::encode($heroSubtitle) ?></p>
    </div>
</section>

<div class="site-index">
    <!-- Popular Section -->
    <section class="popular-section">
        <div class="container">
            <h2 class="section-title">Popular Articles</h2>
            
            <?php if (!empty($popularArticles)): ?>
                <div class="row">
                    <?php foreach ($popularArticles as $article): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="popular-card">
                                <?php if ($article->image): ?>
                                    <div class="popular-card-image">
                                        <?= Html::a(
                                            Html::img($article->image, [
                                                'alt' => Html::encode($article->title),
                                                'class' => 'img-fluid'
                                            ]),
                                            ['/article/view', 'slug' => $article->slug],
                                            ['class' => 'popular-image-link']
                                        ) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="popular-card-body">
                                    <div class="popular-card-meta">
                                        <span class="popular-category">
                                            <?= Html::a(
                                                Html::encode($article->category->name ?? 'Uncategorized'),
                                                ['/article/index', 'category_id' => $article->category_id ?? ''],
                                                ['class' => 'text-decoration-none']
                                            ) ?>
                                        </span>
                                        <span class="popular-views">
                                            <i class="bi bi-eye"></i><?= $article->views ?>
                                        </span>
                                    </div>
                                    
                                    <h3 class="popular-card-title">
                                        <?= Html::a(
                                            Html::encode($article->title),
                                            ['/article/view', 'slug' => $article->slug],
                                            ['class' => 'text-decoration-none']
                                        ) ?>
                                    </h3>
                                    
                                    <p class="popular-card-excerpt">
                                        <?= Html::encode($article->getExcerpt(120)) ?>
                                    </p>
                                    
                                    <div class="popular-card-footer">
                                        <small class="text-muted">
                                            By <?= Html::encode($article->author->username ?? 'Unknown') ?>
                                            | <?= date('M j, Y', $article->created_at) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <p class="mb-0">No popular articles yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Categories Section -->
    <section class="categories-section">
        <div class="container">
            <h2 class="section-title">Categories</h2>
            
            <?php if (!empty($categories)): ?>
                <div class="categories-carousel"  data-carousel>
                    <div class="categories-carousel-container">
                        <?php foreach ($categories as $category): ?>
                            <div class="category-card">
                                <?= Html::a(
                                    '<div class="category-card-image">' .
                                        ($category->image 
                                            ? Html::img($category->image, [
                                                'alt' => Html::encode($category->name),
                                                'class' => 'img-fluid'
                                            ])
                                            : '<div class="category-placeholder"><i class="bi bi-folder"></i></div>'
                                        ) .
                                    '</div>' .
                                    '<div class="category-card-label">' . Html::encode($category->name) . '</div>',
                                    ['/article/index', 'category_id' => $category->id],
                                    ['class' => 'category-link']
                                ) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <p class="mb-0">No categories yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Random Movie Section -->
    <section class="random-movie-section">
            <div class="random-movie-wrapper">
                <!-- Left Part: Slogan and Button -->
                <div class="random-movie-left">
                    <div class="random-movie-content">
                        <h2 class="random-movie-slogan">
                            <span class="random-movie-slogan-line1">Feeling lucky?</span>
                            <span class="random-movie-slogan-line2">Discover a movie at random!</span>
                        </h2>
                        <button class="random-movie-btn" id="randomMovieBtn">
                            <span>Show me a movie</span>
                        </button>
                    </div>
                </div>
                
                <!-- Right Part: Image Display -->
                <div class="random-movie-right">
                    <div class="random-movie-image-container" id="randomMovieImageContainer">
                        <!-- Initial State: Question Mark or Initial Image -->
                        <?php if ($randomMovieInitialImage): ?>
                            <div class="random-movie-poster" id="randomMoviePoster">
                                <div class="random-movie-link">
                                    <img src="<?= Html::encode($randomMovieInitialImage) ?>" alt="Initial image" class="random-movie-img">
                                </div>
                            </div>
                            <div class="random-movie-placeholder" id="randomMoviePlaceholder" style="display: none;">
                                <i class="bi bi-question-circle"></i>
                            </div>
                        <?php else: ?>
                            <div class="random-movie-placeholder" id="randomMoviePlaceholder">
                                <i class="bi bi-question-circle"></i>
                            </div>
                            <div class="random-movie-poster" id="randomMoviePoster" style="display: none;">
                                <a href="#" id="randomMovieLink" class="random-movie-link">
                                    <img src="" alt="" id="randomMovieImage" class="random-movie-img">
                                    <div class="random-movie-overlay">
                                        <h3 class="random-movie-title" id="randomMovieTitle"></h3>
                                        <p class="random-movie-excerpt" id="randomMovieExcerpt"></p>
                                    </div>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
    </section>
    
    <!-- Tags Section -->
    <section class="tags-section">
        <div class="container">
            <h2 class="section-title">Tags</h2>
            
            <?php if (!empty($tags)): ?>
                  <div class="tags-container">
                      <?php foreach ($tags as $tag): ?>
                         <div class="tag-card">
                              <?= Html::a(
                                  '#' . Html::encode($tag->name),
                                  ['/article/index', 'tag_id' => $tag->id],
                                  ['class' => 'tag-link']
                              ) ?>
                          </div>
                      <?php endforeach; ?>
                  </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <p class="mb-0">No tags available yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Latest Posts Section -->
    <section class="latest-posts-section">
        <div class="container">
            <h2 class="section-title">Latest Posts</h2>
            
            <?php if (!empty($latestArticles)): ?>
                <div class="latest-posts-grid">
                    <?php foreach ($latestArticles as $article): ?>
                        <div class="latest-post-card">
                            <?= Html::a(
                                '<div class="latest-post-content">
                                    ' . ($article->image ? '<div class="latest-post-image">
                                        ' . Html::img($article->image, [
                                            'alt' => Html::encode($article->title),
                                            'class' => 'img-fluid'
                                        ]) . '
                                    </div>' : '') . '
                                    <div class="latest-post-info">
                                        <div class="latest-post-meta">
                                            <span class="latest-post-category">
                                                ' . Html::encode($article->category->name ?? 'Uncategorized') . '
                                            </span>
                                            <span class="latest-post-date">
                                                ' . date('M j, Y', $article->created_at) . '
                                            </span>
                                        </div>
                                        <h3 class="latest-post-title">' . Html::encode($article->title) . '</h3>
                                        <p class="latest-post-excerpt">' . Html::encode($article->getExcerpt(100)) . '</p>
                                        <div class="latest-post-footer">
                                            <span class="latest-post-author">
                                                By ' . Html::encode($article->author->username ?? 'Unknown') . '
                                            </span>
                                            <span class="latest-post-views">
                                                <i class="bi bi-eye"></i> ' . $article->views . '
                                            </span>
                                        </div>
                                    </div>
                                </div>',
                                ['/article/view', 'slug' => $article->slug],
                                ['class' => 'latest-post-link']
                            ) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <p class="mb-0">No latest articles yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const carousel = document.querySelector('[data-carousel]');
    if (!carousel) return;

    const track = carousel.querySelector('.categories-carousel-container');
    const cards = Array.from(track.children);

    // Клонуємо картки для безкінечного ефекту
    cards.forEach(card => {
        const clone = card.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        track.appendChild(clone);
    });

    let position = 0;
    let speed = 0.26; // швидкість прокрутки (px/frame)

    function animate() {
        position += speed;

        // коли пройшли половину (оригінальні елементи)
        if (position >= track.scrollWidth / 2) {
            position = 0;
        }

        track.style.transform = `translateX(-${position}px)`;
        requestAnimationFrame(animate);
    }

    animate();

    // Пауза при наведенні
    carousel.addEventListener('mouseenter', () => speed = 0);
    carousel.addEventListener('mouseleave', () => speed = 0.3);
});

// Random Movie Section JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const randomMovieBtn = document.getElementById('randomMovieBtn');
    const randomMoviePlaceholder = document.getElementById('randomMoviePlaceholder');
    const randomMoviePoster = document.getElementById('randomMoviePoster');
    
    if (!randomMovieBtn || !randomMoviePoster) return;
    
    let isLoading = false;
    
    randomMovieBtn.addEventListener('click', function() {
        if (isLoading) return;
        
        isLoading = true;
        randomMovieBtn.disabled = true;
        randomMovieBtn.innerHTML = '<span>Loading...</span>';
        
        // Виконуємо AJAX запит
        fetch('<?= Url::to(['site/random-article']) ?>', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.article) {
                // Ховаємо placeholder, якщо він видимий
                if (randomMoviePlaceholder) {
                    randomMoviePlaceholder.style.display = 'none';
                }
                
                // Очищаємо поточний вміст постера
                randomMoviePoster.innerHTML = '';
                
                // Створюємо нову структуру з посиланням та інформацією про статтю
                const link = document.createElement('a');
                link.id = 'randomMovieLink';
                link.href = '<?= Url::to(['article/view', 'slug' => '']) ?>' + data.article.slug;
                link.className = 'random-movie-link';
                
                const img = document.createElement('img');
                img.id = 'randomMovieImage';
                img.className = 'random-movie-img';
                img.src = data.article.image;
                img.alt = data.article.title;
                
                const overlay = document.createElement('div');
                overlay.className = 'random-movie-overlay';
                
                const title = document.createElement('h3');
                title.id = 'randomMovieTitle';
                title.className = 'random-movie-title';
                title.textContent = data.article.title;
                
                const excerpt = document.createElement('p');
                excerpt.id = 'randomMovieExcerpt';
                excerpt.className = 'random-movie-excerpt';
                excerpt.textContent = data.article.excerpt;
                
                overlay.appendChild(title);
                overlay.appendChild(excerpt);
                link.appendChild(img);
                link.appendChild(overlay);
                randomMoviePoster.appendChild(link);
                
                // Показуємо постер з анімацією
                randomMoviePoster.style.display = 'block';
                randomMoviePoster.classList.add('fade-in');
                
                // Видаляємо клас анімації після завершення
                setTimeout(() => {
                    randomMoviePoster.classList.remove('fade-in');
                }, 600);
            } else {
                alert('Sorry, no articles available at the moment.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while loading the article. Please try again.');
        })
        .finally(() => {
            isLoading = false;
            randomMovieBtn.disabled = false;
            randomMovieBtn.innerHTML = '<span>Show me a movie</span>';
        });
    });
});
</script>
