<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Моё избранное — КиноМир</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">🎬 КиноМир</a>
        <a class="btn btn-outline-light btn-sm" href="index.php">На главную</a>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4 fw-bold">⭐ Мой список избранного</h2>

    <div class="row row-cols-1 row-cols-md-2 g-4">
        <?php if (!empty($watchlistMovies)): ?>
            <?php foreach ($watchlistMovies as $m): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm movie-card <?= $m['is_watched'] ? 'watched-card' : '' ?>">
                        <div class="row g-0">

                            <!-- ✅ FIX 1: добавлен alt -->
                            <div class="col-md-4">
                                <img 
                                    src="<?= htmlspecialchars($m['poster_url']) ?>" 
                                    class="img-fluid rounded-start h-100" 
                                    style="object-fit: cover;"
                                    alt="Постер фильма <?= htmlspecialchars($m['title']) ?>"
                                >
                            </div>

                            <div class="col-md-8">
                                <div class="card-body d-flex flex-column h-100">

                                    <div class="d-flex justify-content-between">
                                        <h5 class="card-title fw-bold">
                                            <?= htmlspecialchars($m['title']) ?>
                                        </h5>

                                        <?php if($m['is_watched']): ?>
                                            <span class="badge bg-success">Просмотрено</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="description-container mb-3">
                                        <p class="card-text text-secondary small description-text" id="desc-<?= $m['id'] ?>">
                                            <?= htmlspecialchars($m['description']) ?>
                                        </p>

                                        <!-- ✅ FIX 2: span → доступная кнопка -->
                                        <button 
                                            class="toggle-btn btn btn-link p-0"
                                            onclick="toggleDescription(<?= $m['id'] ?>, this)"
                                            onkeydown="if(event.key==='Enter'){toggleDescription(<?= $m['id'] ?>, this)}"
                                            aria-label="Показать или скрыть описание"
                                        >
                                            показать больше
                                        </button>
                                    </div>

                                    <div class="mt-auto d-flex gap-2">

                                        <!-- ✅ FIX 3: доступный текст для screen reader -->
                                        <a 
                                            href="index.php?route=toggle_watched&id=<?= $m['id'] ?>" 
                                            class="btn <?= $m['is_watched'] ? 'btn-secondary' : 'btn-outline-success' ?> btn-sm flex-grow-1"
                                            aria-label="<?= $m['is_watched'] ? 'Отметить как не просмотрено' : 'Отметить как просмотрено' ?>"
                                        >
                                            <?= $m['is_watched'] ? '🔄 Смотреть снова' : '✅ Просмотрено' ?>
                                        </a>

                                        <a 
                                            href="index.php?route=remove_watchlist&id=<?= $m['id'] ?>" 
                                            class="btn btn-outline-danger btn-sm"
                                            aria-label="Удалить фильм из избранного"
                                        >
                                            Удалить
                                        </a>

                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php else: ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted">Ваш список пуст. Самое время что-нибудь добавить!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="js/script.js"></script>
</body>
</html>