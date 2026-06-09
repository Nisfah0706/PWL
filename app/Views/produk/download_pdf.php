<h1>Data Produk</h1>

<table border="1" width="100%" cellpadding="5">
    <tr>
        <th>No</th>
        <th>Nama</th>
        <th>Harga</th>
        <th>Jumlah</th>
        <th>Foto</th>
    </tr>

    <?php foreach ($products as $index => $produk) : ?>
    <?php
		    $path = FCPATH . 'img/' . $produk['foto'];
        $base64 = '';
        
        if (file_exists($path)) {
	        $type = pathinfo($path, PATHINFO_EXTENSION);
	        $data = file_get_contents($path);
	        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
				}
    ?>
        <tr>
            <td align="center"><?= $index + 1 ?></td>
            <td><?= $produk['nama'] ?></td>
            <td align="right">Rp <?= number_format($produk['harga'], 2, ",", ".") ?></td>
            <td align="center"><?= $produk['jumlah'] ?></td>
            <td align="center">
                <?php if ($base64) : ?>
                    <img src="<?= $base64 ?>" width="50">
                <?php else : ?>
                    Tidak ada gambar
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
Downloaded on <?= date("Y-m-d H:i:s") ?>