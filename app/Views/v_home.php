<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<!-- Table with stripped rows -->
<div class="row">
    <?php foreach ($products as $key => $item) : ?>         
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <img src="<?= base_url() . "img/" . $item['foto'] ?>" alt="..." width="50%">
                        <h5 class="card-title"><?= $item['nama'] ?><br><?= $item['harga'] ?></h5>
                    </div>
                </div>
            </div> 
    <?php endforeach ?> 
</div>
<!-- End Table with stripped rows -->
<?= $this->endSection() ?>