<?php if (session('success')): ?>
    <div class="alert success"><?= esc(session('success')) ?></div>
<?php endif ?>
<?php if (session('error')): ?>
    <div class="alert danger"><?= esc(session('error')) ?></div>
<?php endif ?>
<?php if (session('errors')): ?>
    <div class="alert danger">
        <ul><?php foreach ((array) session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach ?></ul>
    </div>
<?php endif ?>
