<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

    <h5>Profil Information</h5><br>
    <div class="row mb-2">
        <div class="col-3">Username</div>
        <div class="col-1">:</div>
        <div class="col-8">
            <?= $username; ?>
            <span class="badge bg-danger"><?= $role; ?></span>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-3">Email</div>
        <div class="col-1">:</div>
        <div class="col-8">
            <?= $email; ?>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-3">Login Time</div>
        <div class="col-1">:</div>
        <div class="col-8">
            <?= $login_time; ?>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-3">Status</div>
        <div class="col-1">:</div>
        <div class="col-8">
            <span class="badge bg-success">
                <?= $status ? 'Sudah Login' : 'Belum Login'; ?>
            </span>
        </div>
    </div>
<?= $this->endSection() ?>