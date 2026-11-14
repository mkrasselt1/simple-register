<!DOCTYPE html>
<html lang="<?php echo $lang->getLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__('admin_title'); ?></title>
    <link rel="icon" type="image/svg+xml" href="favicon/favicon.svg">
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico">
    <link rel="stylesheet" href="views/common.css">
    <style>
        .container {
            max-width: 1000px;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
        }
        .card {
            background: #16213e;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            color: white;
        }
        .card h2 {
            margin-bottom: 20px;
            color: #4ecca3;
        }
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .form-group {
            flex: 1;
            min-width: 150px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #4ecca3;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #0f3460;
            border-radius: 5px;
            font-size: 1em;
            background: #1a1a2e;
            color: white;
        }
        .form-group input[type="color"] {
            height: 42px;
            cursor: pointer;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-success {
            background: #4ecca3;
            color: #1a1a2e;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .articles-table {
            width: 100%;
            border-collapse: collapse;
        }
        .articles-table th,
        .articles-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #0f3460;
        }
        .articles-table th {
            background: #0f3460;
            font-weight: 600;
        }
        .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 5px;
            display: inline-block;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .bulk-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .bulk-toolbar button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .bulk-spacer {
            flex: 1;
        }
        .bulk-status {
            color: #ffc107;
            font-weight: 500;
        }
        .bulk-selection {
            display: none;
            padding: 10px;
            background: #0f3460;
            border-radius: 5px;
        }
        .bulk-selection.active {
            display: flex;
        }
        .bulk-selection .bulk-status {
            color: white;
        }
        #bulk-color {
            width: 50px;
            height: 38px;
            border: none;
            background: none;
            cursor: pointer;
        }
        #article-filter {
            padding: 8px 10px;
            border: 1px solid #0f3460;
            border-radius: 5px;
            background: #1a1a2e;
            color: white;
            min-width: 180px;
        }
        .table-wrap {
            overflow-x: auto;
        }
        .articles-table input[type="text"] {
            padding: 8px;
            border: 1px solid #0f3460;
            border-radius: 5px;
            background: #1a1a2e;
            color: white;
            font-size: 1em;
        }
        .articles-table input:invalid {
            border-color: #dc3545;
        }
        .col-select {
            width: 36px;
        }
        .col-select input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .nowrap {
            white-space: nowrap;
        }
        .article-row.dirty td {
            background: rgba(255, 193, 7, 0.12);
        }
        .article-row.selected td {
            background: rgba(78, 204, 163, 0.15);
        }
        .no-articles {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        @media (max-width: 600px) {
            .form-row {
                flex-direction: column;
            }
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚙️ <?php echo $__('admin'); ?></h1>
        <div class="header-links">
            <a href="register.php">🛒 <?php echo $__('register'); ?></a>
            <a href="reports.php">📊 <?php echo $__('reports'); ?></a>
            <a href="index.php">🏠 <?php echo $__('home'); ?></a>
        </div>
    </div>
    
    <div class="container">
        <?php if (!empty($message)): ?>
        <div class="message <?php echo View::escape($messageType); ?>">
            <?php echo View::escape($message); ?>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <h2><?php echo $__('add_new_article'); ?></h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="csrf_token" value="<?php echo View::escape($csrf_token ?? ''); ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name"><?php echo $__('article_name'); ?></label>
                        <input type="text" id="name" name="name" required placeholder="<?php echo View::escape($__('article_name_placeholder')); ?>">
                    </div>
                    <div class="form-group">
                        <label for="price"><?php echo $__('price'); ?> (€)</label>
                        <input type="text" id="price" name="price" inputmode="decimal" pattern="-?\d+(\.\d{1,2})?" required placeholder="2.50">
                    </div>
                    <div class="form-group">
                        <label for="color"><?php echo $lang->get('button_color'); ?></label>
                        <input type="color" id="color" name="color" value="#007bff">
                    </div>
                </div>
                <button type="submit" class="btn-success">➕ <?php echo $__('add_article'); ?></button>
            </form>
        </div>
        
        <div class="card">
            <h2><?php echo $__('manage_articles'); ?></h2>
            <?php if (empty($articles)): ?>
            <div class="no-articles">
                <p><?php echo $__('no_articles_yet'); ?></p>
            </div>
            <?php else: ?>
            <form method="POST" id="bulk-form">
                <input type="hidden" name="csrf_token" value="<?php echo View::escape($csrf_token ?? ''); ?>">
                <div class="bulk-toolbar">
                    <button type="submit" name="action" value="bulk_update" class="btn-primary" id="bulk-save">💾 <?php echo $__('save_all_changes'); ?></button>
                    <span class="bulk-status" id="dirty-status"></span>
                    <span class="bulk-spacer"></span>
                    <input type="search" id="article-filter" placeholder="<?php echo View::escape($__('filter_articles')); ?>" aria-label="<?php echo View::escape($__('filter_articles')); ?>">
                </div>
                <div class="bulk-toolbar bulk-selection" id="bulk-selection">
                    <span class="bulk-status" id="selected-status"></span>
                    <input type="color" id="bulk-color" value="#007bff" aria-label="<?php echo View::escape($__('color')); ?>">
                    <button type="button" class="btn-success" id="bulk-apply-color">🎨 <?php echo $__('apply_color_to_selected'); ?></button>
                    <button type="submit" name="action" value="bulk_delete" class="btn-danger" id="bulk-delete" formnovalidate>🗑️ <?php echo $__('delete_selected'); ?></button>
                </div>
                <div class="table-wrap">
                <table class="articles-table">
                    <thead>
                        <tr>
                            <th class="col-select"><input type="checkbox" id="select-all" title="<?php echo View::escape($__('select_all')); ?>" aria-label="<?php echo View::escape($__('select_all')); ?>"></th>
                            <th><?php echo $__('name'); ?></th>
                            <th><?php echo $__('price'); ?></th>
                            <th><?php echo $__('color'); ?></th>
                            <th><?php echo $__('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article): $aid = View::escape($article->id); ?>
                        <tr class="article-row" data-id="<?php echo $aid; ?>">
                            <td class="col-select">
                                <input type="checkbox" class="row-select" name="selected[]" value="<?php echo $aid; ?>">
                            </td>
                            <td>
                                <input type="text" name="articles[<?php echo $aid; ?>][name]" value="<?php echo View::escape($article->name); ?>" required>
                            </td>
                            <td class="nowrap">
                                <input type="text" name="articles[<?php echo $aid; ?>][price]" value="<?php echo View::escape($article->price); ?>" inputmode="decimal" pattern="-?\d+([.,]\d{1,2})?" required style="width: 100px;"> €
                            </td>
                            <td>
                                <input type="color" name="articles[<?php echo $aid; ?>][color]" value="<?php echo View::escape($article->color ?? '#007bff'); ?>">
                            </td>
                            <td class="actions">
                                <button type="submit" name="delete_id" value="<?php echo $aid; ?>" class="btn-danger" formnovalidate onclick="return confirm(<?php echo View::escape(json_encode($__('are_you_sure'))); ?>)">🗑️ <?php echo $__('delete'); ?></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </form>
            <script>
            (function () {
                var i18n = <?php echo json_encode([
                    'selected' => $__('selected_count'),
                    'unsaved' => $__('unsaved_changes'),
                    'confirmDelete' => $__('confirm_delete_selected'),
                ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                var fmt = function (s, n) { return s.replace('%d', n); };
                var form = document.getElementById('bulk-form');
                var rows = Array.prototype.slice.call(form.querySelectorAll('.article-row'));
                var selectAll = document.getElementById('select-all');
                var submitting = false;

                // Remember original values to detect changes
                rows.forEach(function (row) {
                    row.querySelectorAll('input[name^="articles"]').forEach(function (inp) {
                        inp.dataset.original = inp.value;
                    });
                });

                function isDirty(row) {
                    return Array.prototype.some.call(row.querySelectorAll('input[name^="articles"]'), function (inp) {
                        return inp.value !== inp.dataset.original;
                    });
                }

                function visibleRows() {
                    return rows.filter(function (r) { return !r.hidden; });
                }

                function refresh() {
                    var dirty = 0, selected = 0;
                    rows.forEach(function (row) {
                        var d = isDirty(row);
                        row.classList.toggle('dirty', d);
                        if (d) dirty++;
                        var sel = row.querySelector('.row-select').checked;
                        row.classList.toggle('selected', sel);
                        if (sel) selected++;
                    });
                    document.getElementById('dirty-status').textContent = dirty ? fmt(i18n.unsaved, dirty) : '';
                    document.getElementById('bulk-save').disabled = dirty === 0;
                    document.getElementById('selected-status').textContent = fmt(i18n.selected, selected);
                    document.getElementById('bulk-selection').classList.toggle('active', selected > 0);
                    var vis = visibleRows();
                    var visSel = vis.filter(function (r) { return r.querySelector('.row-select').checked; }).length;
                    selectAll.checked = vis.length > 0 && visSel === vis.length;
                    selectAll.indeterminate = visSel > 0 && visSel < vis.length;
                    return { dirty: dirty, selected: selected };
                }

                form.addEventListener('input', refresh);
                form.addEventListener('change', refresh);

                selectAll.addEventListener('change', function () {
                    visibleRows().forEach(function (r) { r.querySelector('.row-select').checked = selectAll.checked; });
                    refresh();
                });

                document.getElementById('bulk-apply-color').addEventListener('click', function () {
                    var color = document.getElementById('bulk-color').value;
                    rows.forEach(function (r) {
                        if (r.querySelector('.row-select').checked) {
                            r.querySelector('input[type="color"][name^="articles"]').value = color;
                        }
                    });
                    refresh();
                });

                document.getElementById('article-filter').addEventListener('input', function () {
                    var q = this.value.trim().toLowerCase();
                    rows.forEach(function (r) {
                        var name = r.querySelector('input[name$="[name]"]').value.toLowerCase();
                        r.hidden = q !== '' && name.indexOf(q) === -1;
                    });
                    refresh();
                });

                document.getElementById('bulk-delete').addEventListener('click', function (e) {
                    var n = refresh().selected;
                    if (n === 0 || !confirm(fmt(i18n.confirmDelete, n))) {
                        e.preventDefault();
                    }
                });

                form.addEventListener('submit', function () { submitting = true; });
                window.addEventListener('beforeunload', function (e) {
                    if (!submitting && refresh().dirty > 0) {
                        e.preventDefault();
                        e.returnValue = '';
                    }
                });

                refresh();
            })();
            </script>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2><?php echo $lang->get('add_user'); ?></h2>
            <form method="post" class="form-inline">
                <input type="hidden" name="csrf_token" value="<?php echo View::escape($csrf_token ?? ''); ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="username"><?php echo $lang->get('username'); ?>:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password"><?php echo $lang->get('password'); ?>:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password"><?php echo $lang->get('confirm_password'); ?>:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                <button type="submit" name="action" value="add_user" class="btn-success">➕ <?php echo $lang->get('add_user_button'); ?></button>
            </form>
        </div>
        
        <div class="card">
            <h2><?php echo $lang->get('existing_users'); ?></h2>
            <?php if (empty($users)): ?>
            <div class="no-articles">
                <p><?php echo $lang->get('no_users_yet'); ?></p>
            </div>
            <?php else: ?>
            <table class="articles-table">
                <thead>
                    <tr>
                        <th><?php echo $lang->get('username'); ?></th>
                        <th><?php echo $lang->get('permissions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo View::escape($user['username']); ?></td>
                        <td><?php echo View::escape(implode(', ', $user['permissions'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2><?php echo $lang->get('backup_restore'); ?></h2>
            <div class="form-row">
                <div class="form-group">
                    <label><?php echo $lang->get('download_backup'); ?></label>
                    <p style="color: #ccc; margin: 5px 0;">
                        <?php echo $lang->get('download_backup_description'); ?></p>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="backup">
                        <input type="hidden" name="csrf_token" value="<?php echo View::escape($csrf_token ?? ''); ?>">
                        <button type="submit" class="btn-primary">📥 <?php echo $lang->get('download_backup_button'); ?></button>
                    </form>
                </div>
                <div class="form-group">
                    <label for="backup_file"><?php echo $lang->get('upload_restore'); ?></label>
                    <p style="color: #ccc; margin: 5px 0;">
                        <?php echo $lang->get('upload_backup_description'); ?></p>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="restore">
                        <input type="hidden" name="csrf_token" value="<?php echo View::escape($csrf_token ?? ''); ?>">
                        <input type="file" id="backup_file" name="backup_file" accept=".zip" required style="margin-bottom: 10px;">
                        <br>
                        <button type="submit" class="btn-success">📤 <?php echo $lang->get('restore_from_backup'); ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
