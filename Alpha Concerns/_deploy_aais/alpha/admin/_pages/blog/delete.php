<?php
$id = (int)($_GET['id'] ?? 0);
if ($id) { db_exec("DELETE FROM blog_posts WHERE id = ?", [$id]); flash_set('success','Post deleted.'); }
redirect('/admin/blog');
