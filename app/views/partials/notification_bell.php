<?php
/**
 * Notification Bell — include this inside your navbar's right-side nav links
 * Only renders when a user is logged in.
 *
 * Usage (in your navbar.php):
 *   <?php require_once VIEW_PATH . '/partials/notification_bell.php'; ?>
 */
if (!Session::isLoggedIn()) return;
?>

<li class="nav-item dropdown me-2" id="notifDropdownWrapper">
    <a class="nav-link position-relative p-2"
       href="#"
       role="button"
       id="notifBell"
       data-bs-toggle="dropdown"
       aria-expanded="false"
       aria-label="Notifications">

        <i class="bi bi-bell-fill fs-5"></i>

        <!-- Unread badge — hidden when count is 0 -->
        <span id="notifBadge"
              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
              style="font-size:.6rem; display:none;">
            0
        </span>
    </a>

    <!-- Dropdown panel -->
    <div class="dropdown-menu dropdown-menu-end shadow-sm p-0"
         id="notifPanel"
         style="width:min(360px, calc(100vw - 24px)); max-height:480px; overflow:hidden;">

        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom bg-light">
            <span class="fw-600 small">Notifications</span>
            <button id="btnMarkAllRead"
                    class="btn btn-link btn-sm text-muted text-decoration-none p-0"
                    style="font-size:.8rem;">
                Mark all read
            </button>
        </div>

        <!-- Scrollable list -->
        <div id="notifList" style="max-height:380px; overflow-y:auto;">
            <div id="notifEmpty" class="text-center text-muted py-4 small" style="display:none;">
                <i class="bi bi-bell-slash d-block fs-4 mb-1"></i>
                No notifications
            </div>
        </div>

        <!-- Footer -->
        <div class="border-top text-center py-2 bg-light">
            <a href="<?= url('/notifications') ?>"
               class="text-primary small text-decoration-none fw-500">
                View all notifications
            </a>
        </div>
    </div>
</li>
