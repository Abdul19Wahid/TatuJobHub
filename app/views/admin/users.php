<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-800 mb-0">User Management</h1>
        <p class="text-muted small mb-0">Manage all registered users on the platform.</p>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('/admin/users') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <input type="text" class="form-control form-control-sm" name="q"
                       placeholder="Search by name or email..."
                       value="<?= e($search) ?>">
            </div>
            <div class="col-auto">
                <?php foreach ([''=>'All Roles','seeker'=>'Job Seekers','employer'=>'Employers','admin'=>'Admins'] as $v=>$l): ?>
                <a href="?role=<?= $v ?><?= $search ? '&q=' . urlencode($search) : '' ?>"
                   class="btn btn-sm <?= $role === $v ? 'btn-primary' : 'btn-light' ?> me-1">
                    <?= $l ?>
                </a>
                <?php endforeach; ?>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-search me-1"></i>Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Users table -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Verified</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">No users found.</td></tr>
                <?php else: ?>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td class="text-muted small"><?= $u['id'] ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-700 flex-shrink-0"
                                 style="width:36px;height:36px;font-size:13px;
                                        background:<?= $u['role']==='admin'?'#FEE2E2':($u['role']==='employer'?'#EDE9FE':'#EBF5FF') ?>;
                                        color:<?= $u['role']==='admin'?'#DC2626':($u['role']==='employer'?'#7C3AED':'#1A56DB') ?>;">
                                <?= strtoupper(substr($u['full_name'],0,1)) ?>
                            </div>
                            <div>
                                <div class="fw-600 small"><?= e($u['full_name']) ?></div>
                                <div class="text-muted" style="font-size:11px;"><?= e($u['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge rounded-pill"
                              style="background:<?= $u['role']==='admin'?'#FEE2E2':($u['role']==='employer'?'#EDE9FE':'#EBF5FF') ?>;
                                     color:<?= $u['role']==='admin'?'#DC2626':($u['role']==='employer'?'#7C3AED':'#1A56DB') ?>;">
                            <?= ucfirst($u['role']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge <?= $u['is_active'] ? 'bg-success' : 'bg-secondary' ?> rounded-pill">
                            <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($u['email_verified']): ?>
                            <i class="bi bi-check-circle-fill text-success"></i>
                        <?php else: ?>
                            <i class="bi bi-clock text-muted"></i>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small"><?= format_date($u['created_at'], 'M d, Y') ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <?php if ($u['role'] !== 'admin'): ?>
                            <form method="POST" action="<?= url('/admin/users/' . $u['id'] . '/toggle') ?>"
                                  data-no-loading class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit"
                                        class="btn btn-sm <?= $u['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                        data-confirm="<?= $u['is_active'] ? 'Deactivate' : 'Activate' ?> this user?"
                                        data-bs-toggle="tooltip"
                                        title="<?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                    <i class="bi bi-<?= $u['is_active'] ? 'pause-circle' : 'play-circle' ?>"></i>
                                </button>
                            </form>
                            <form method="POST" action="<?= url('/admin/users/' . $u['id'] . '/delete') ?>"
                                  data-no-loading class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        data-confirm="Permanently delete <?= e($u['full_name']) ?>? This cannot be undone."
                                        data-bs-toggle="tooltip" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            <?php else: ?>
                            <span class="text-muted small">Protected</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($paging['pages'] > 1): ?>
<nav class="mt-4">
    <ul class="pagination justify-content-center gap-1">
        <?php for ($i = 1; $i <= $paging['pages']; $i++): ?>
        <li class="page-item <?= $i === $paging['current_page'] ? 'active' : '' ?>">
            <a class="page-link border-0"
               href="?page=<?= $i ?><?= $role ? '&role=' . $role : '' ?><?= $search ? '&q=' . urlencode($search) : '' ?>">
                <?= $i ?>
            </a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
