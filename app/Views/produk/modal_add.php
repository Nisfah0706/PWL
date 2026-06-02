<!-- Add Modal Begin -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
	        <div class="modal-header">
	            <h5 class="modal-title">Tambah Data</h5>
	            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	        </div>
	        <?= form_open_multipart(base_url('produk')); ?>
	        <?= csrf_field(); ?>
	
	        <div class="modal-body">
	            <div class="mb-3">
	                <?= form_label('Nama', 'nama'); ?>
	                <?= form_input([
	                    'name'        => 'nama',
	                    'id'          => 'nama',
	                    'class'       => 'form-control',
	                    'placeholder' => 'Nama Barang',
	                    'required'    => true
	                ]); ?>
	            </div>
	
	            <div class="mb-3">
	                <?= form_label('Harga', 'harga'); ?>
	                <?= form_input([
	                    'name'        => 'harga',
	                    'id'          => 'harga',
	                    'class'       => 'form-control',
	                    'placeholder' => 'Harga Barang',
	                    'required'    => true
	                ]); ?>
	            </div>
	
	            <div class="mb-3">
	                <?= form_label('Jumlah', 'jumlah'); ?>
	                <?= form_input([
	                    'type'        => 'number',
	                    'name'        => 'jumlah',
	                    'id'          => 'jumlah',
	                    'class'       => 'form-control',
	                    'placeholder' => 'Jumlah Barang',
	                    'required'    => true
	                ]); ?>
	            </div>
	
	            <div class="mb-3">
	                <?= form_label('Foto', 'foto'); ?>
	                <?= form_upload([
	                    'name'  => 'foto',
	                    'id'    => 'foto',
	                    'class' => 'form-control'
	                ]); ?>
	            </div>
	        </div>
	
	        <div class="modal-footer">
	            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
	            Close
	            </button>
	
	            <?= form_submit('submit', 'Simpan', ['class' => 'btn btn-primary']); ?>
	        </div>
	
	        <?= form_close(); ?>
        </div>
    </div>
</div>
<!-- Add Modal End -->