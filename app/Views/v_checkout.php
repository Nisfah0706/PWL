<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-lg-6">

        <?= form_open('buy','class="row g-3"') ?>

        <?= form_hidden('username', session()->get('username')) ?>
        <input type="hidden" name="total_harga" id="total_harga">

        <div class="col-12">
            <?= form_label('Nama','nama',['class'=>'form-label']) ?>
            <?= form_input([
                'name'=>'nama',
                'id'=>'nama',
                'class'=>'form-control',
                'value'=>session()->get('username'),
                'readonly'=>true
            ]) ?>
        </div>

        <div class="col-12">
            <?= form_label('Alamat','alamat',['class'=>'form-label']) ?>
            <?= form_input([
                'name'=>'alamat',
                'id'=>'alamat',
                'class'=>'form-control'
            ]) ?>
        </div>

        <div class="col-12">
            <?= form_label('Kelurahan','kelurahan',['class'=>'form-label']) ?>
            <?= form_dropdown('kelurahan',[], '',[
                'id'=>'kelurahan',
                'class'=>'form-control'
            ]) ?>
        </div>

        <div class="col-12">
            <?= form_label('Layanan','layanan',['class'=>'form-label']) ?>
            <?= form_dropdown('layanan',[], '',[
                'id'=>'layanan',
                'class'=>'form-control'
            ]) ?>
        </div>

        <div class="col-12">
            <?= form_label('Ongkir','ongkir',['class'=>'form-label']) ?>
            <?= form_input([
                'name'=>'ongkir',
                'id'=>'ongkir',
                'class'=>'form-control',
                'readonly'=>true
            ]) ?>
        </div>

        <div class="col-12">
            <?= form_label('Voucher Promo','voucher_code',['class'=>'form-label']) ?>
            <?= form_input([
                'name'=>'voucher_code',
                'id'=>'voucher_code',
                'class'=>'form-control',
            ]) ?>
            <small class="text-muted">
                Tersedia: PROMO2025 (10%), PROMO2026 (15%), AKHIRTAHUN (25%)
            </small>
        </div>

        <div class="col-12">
            <?= form_submit('submit','Buat Pesanan',['class'=>'btn btn-primary']) ?>
        </div>

        <?= form_close() ?>

    </div>

    <div class="col-lg-6">

        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                </tr>
            </thead>

            <tbody>

            <?php foreach($items as $item): ?>
            <tr>
                <td><?= $item['name'] ?></td>
                <td><?= number_to_currency($item['price'],'IDR') ?></td>
                <td><?= $item['qty'] ?></td>
                <td><?= number_to_currency($item['price'] * $item['qty'],'IDR') ?></td>
            </tr>
            <?php endforeach; ?>

            <tr>
                <td colspan="2"></td>
                <td>Subtotal</td>
                <td><?= number_to_currency($total,'IDR') ?></td>
            </tr>

            <tr>
                <td colspan="2"></td>
                <td style="color:red">Diskon Member (<?= $persen ?>%)</td>
                <td style="color:red">- <?= number_to_currency($diskon,'IDR') ?></td>
            </tr>

            <tr>
                <td colspan="2"></td>
                <td>Biaya Jasa</td>
                <td id="biaya-jasa">IDR 0</td>
            </tr>

            <tr>
                <td colspan="2"></td>
                <td>Diskon Voucher</td>
                <td id="diskon-voucher">- IDR 0</td>
            </tr>

            <tr>
                <td colspan="2"></td>
                <td>Free Mouse</td>
                <td id="free-mouse">- IDR 0</td>
            </tr>

            <tr>
                <td colspan="2"></td>
                <td><b>Total</b></td>
                <td><b id="total"></b></td>
            </tr>

            </tbody>
        </table>

    </div>
</div>

<?= $this->endSection() ?>


<?= $this->section('script') ?>

<script>
$(function(){

    let subtotal = <?= $total ?>;
    let diskonMember = <?= $diskon ?>;
    let ongkir = 0;

    function formatRupiah(angka){
        return "IDR " + Number(angka).toLocaleString("en-US");
    }

    function hitungTotal(){

        let voucher = $("#voucher_code").val().trim().toUpperCase();
        let afterMember = subtotal - diskonMember;
        let diskonVoucher = 0;

        switch(voucher){
            case "PROMO2025":
                diskonVoucher = afterMember * 0.10;
                break;
            case "PROMO2026":
                diskonVoucher = afterMember * 0.15;
                break;
            case "AKHIRTAHUN":
                diskonVoucher = afterMember * 0.25;
                break;
        }

        let afterVoucher = afterMember - diskonVoucher;

        let biayaJasa = subtotal <= 10000000
            ? subtotal * 0.01
            : subtotal * 0.02;

        let freeMouse = subtotal > 15000000 ? 150000 : 0;

        let grandTotal =
            afterVoucher
            + biayaJasa
            + ongkir
            - freeMouse;

        // render UI
        $("#ongkir").val(ongkir);
        $("#biaya-jasa").text(formatRupiah(biayaJasa));
        $("#diskon-voucher").text("- " + formatRupiah(diskonVoucher));
        $("#free-mouse").text("- " + formatRupiah(freeMouse));
        $("#total").text(formatRupiah(grandTotal));
        $("#total_harga").val(grandTotal);
    }

    hitungTotal();

    $("#voucher_code").on("keyup change", function(){
        hitungTotal();
    });

    $('#kelurahan').select2({
        placeholder:'Cari daerah tujuan',
        minimumInputLength:3,
        ajax:{
            url:'<?= site_url('ajax/destinations') ?>',
            dataType:'json',
            delay:300,
            data:function(params){
                return { q:params.term };
            },
            processResults:function(data){
                return data;
            }
        }
    });

    $("#kelurahan").on("change", function(){

        let tujuan = $(this).val();

        $("#layanan").empty();
        ongkir = 0;
        hitungTotal();

        $.ajax({
            url:"<?= site_url('ajax/costs') ?>",
            dataType:"json",
            data:{
                destination:tujuan
            },
            success:function(data){

                data.forEach(function(item){
                    $("#layanan").append(
                        $("<option>", {
                            value: parseInt(item.cost),
                            text: item.description + " (" + item.service + ") : estimasi " + item.etd,
                        })
                    );
                });

                let first = $("#layanan option:first").val();

                if(first){
                    $("#layanan").val(first).trigger("change");
                }
            }
        });

    });

    $("#layanan").on("change", function(){
        ongkir = parseInt($(this).val()) || 0;
        hitungTotal();
    });

});
</script>

<?= $this->endSection() ?>