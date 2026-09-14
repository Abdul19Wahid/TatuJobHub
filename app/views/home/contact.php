<div class="container py-5" style="max-width:600px;">
    <h1 class="fw-800 mb-4">Contact Us</h1>
    <form method="POST" action="<?= url('/contact') ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label">Your Name</label>
            <input type="text" class="form-control" name="name" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" name="email" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea class="form-control" name="message" rows="5" required></textarea>
        </div>
        <button class="btn btn-primary px-4">Send Message</button>
    </form>
</div>
