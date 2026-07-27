<?php
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    db_exec("DELETE FROM projects WHERE id = ?", [$id]);
    auth_log(auth_user()['id'],'delete','project',$id,'');
    flash_set('success','Project deleted.');
}
redirect('/admin/projects');
