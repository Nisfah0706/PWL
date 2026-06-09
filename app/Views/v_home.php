<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
if (session()->getFlashData('success')) {
?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashData('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php
}
?>
<!-- Table with stripped rows -->
<div class="row">
    <?php foreach ($products as $key => $item) : ?>         
            <div class="col-lg-6">
                <?= form_open('keranjang') ?>
                <?= form_hidden([
                    'id'    => $item['id'],
                    'nama'  => $item['nama'],
                    'harga' => $item['harga'],
                    'foto'  => $item['foto']]) ?>

                <div class="card">
                    <div class="card-body">
                        <img src="<?= base_url() . "img/" . $item['foto'] ?>" alt="..." width="50%">
                        <h5 class="card-title"><?= $item['nama'] ?><br><?= number_to_currency($item['harga'], 'IDR') ?></h5>
                        <button type="submit" class="btn btn-info rounded-pill">Beli</button>
                    </div>
                </div>
                <?= form_close() ?>
            </div> 
    <?php endforeach ?> 
</div>
<!-- End Table with stripped rows -->
<?= $this->endSection() ?>