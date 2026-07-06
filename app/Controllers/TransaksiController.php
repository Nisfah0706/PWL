<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\RajaOngkirService;

use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;

class TransaksiController extends BaseController
{
    protected $cart;
    protected $transactionModel;
    protected $transactionDetailModel;

    public function __construct()
    {
        helper(['number', 'form', 'diskon']);

        $this->cart = service('cart');
        $this->transactionModel = new TransactionModel();
        $this->transactionDetailModel = new TransactionDetailModel();
    }

    public function index()
    {  
        $data = [
            'items' => $this->cart->contents(),
            'total' => $this->cart->total()  
        ];

        return view('v_keranjang', $data);
    }

    public function cart_add()
    {
	    $this->cart->insert([
	        'id'      => $this->request->getPost('id'),
	        'qty'     => 1,
	        'price'   => $this->request->getPost('harga'),
	        'name'    => $this->request->getPost('nama'),
	        'options' => [
	            'foto' => $this->request->getPost('foto')
	        ]
	    ]);
	
	    session()->setFlashdata(
	        'success',
	        'Produk berhasil ditambahkan ke keranjang. 
	        <a href="' . base_url('keranjang') . '">Lihat</a>'
	    );
	
	    return redirect()->to(base_url('/'));
    } 

    public function cart_edit()
    {
        $i = 1;
        foreach ($this->cart->contents() as $item) {
            $qty = $this->request->getPost('qty' . $i++);

            $this->cart->update([
                'rowid' => $item['rowid'],
                'qty'   => $qty
            ]);
        }

        session()->setFlashdata(
            'success',
            'Keranjang berhasil diperbarui'
        );

        return redirect()->to(base_url('keranjang'));
    }

    public function cart_delete($rowid)
    {
        $this->cart->remove($rowid);

        session()->setFlashdata(
            'success',
            'Produk berhasil dihapus dari keranjang'
        );

        return redirect()->to(base_url('keranjang'));
    }

    public function cart_clear()
    {
        $this->cart->destroy();

        session()->setFlashdata(
            'success',
            'Keranjang berhasil dikosongkan'
        );

        return redirect()->to(base_url('keranjang'));
    }

    public function checkout()
    {  
        $total = $this->cart->total();

        $hasilDiskon = hitung_diskon($total);

        $data = [
            'items'   => $this->cart->contents(),
            'total'   => $total,
            'diskon'  => $hasilDiskon['diskon'],
            'persen'  => $hasilDiskon['persen'] 
        ];

        return view('v_checkout', $data);
    }

    public function destinations()
{
    $search = $this->request->getGet('q');

    $service = new RajaOngkirService();
    $response = $service->getDestination($search);

    $results = [];

    foreach ($response['data'] as $item) {
        $results[] = [
            'id'   => $item['id'],
            'text' => $item['label']
        ];
    }

    return $this->response->setJSON([
        'results' => $results
    ]);
}

    public function costs()
{
    $origin = '64999';
    $destination = $this->request->getGet('destination');
    $weight = '1000';
    $courier = 'jne';

    $service = new RajaOngkirService();
    $response = $service->getCost($origin, $destination, $weight, $courier);

    $results = [];
    $data = $response['data'] ?? [];

    foreach ($data as $item) {

        $results[] = [
            'service'     => $item['service'],
            'description' => $item['description'],
            'cost'        => is_array($item['cost']) ? $item['cost']['value'] : $item['cost'],
            'etd'         => $item['etd']
        ];
    }

    return $this->response->setJSON($results);
}

    public function buy()
{
    $cartItems = $this->cart->contents();

    if (empty($cartItems)) {
        return redirect()->back();
    }

    $db = \Config\Database::connect();
    $db->transStart();

    // ======================
    // SUBTOTAL
    // ======================
    $subtotal = 0;
    foreach ($cartItems as $item) {
        $subtotal += $item['qty'] * $item['price'];
    }

    // ======================
    // DISKON KUIS
    // ======================
    $hasilDiskon = hitung_diskon($subtotal);
    $persenDiskon = $hasilDiskon['persen'] ?? 0;
    $diskon = $hasilDiskon['diskon'] ?? 0;

    // ======================
    // VOUCHER (FIXED SAFE)
    // ======================
    $voucherCode = strtoupper(trim($this->request->getPost('voucher_code') ?? ''));

    $diskonVoucher = 0;
    if (!empty($voucherCode)) {
        $diskonVoucher = hitung_diskon_voucher($subtotal, $voucherCode) ?? 0;
    }

    // ======================
    // BIAYA TAMBAHAN
    // ======================
    $biayaJasa = hitung_biaya_jasa($subtotal) ?? 0;
    $freeMouse = hitung_free_mouse($subtotal) ?? 0;

    // ======================
    // ONGKIR
    // ======================
    $ongkir = (int) $this->request->getPost('ongkir');

    // ======================
    // TOTAL HITUNGAN FINAL
    // ======================
    $subtotalAkhir = $subtotal + $biayaJasa - $diskonVoucher - $freeMouse;
    $grandTotal = $subtotalAkhir + $ongkir;

    // ======================
    // TRANSACTION DATA
    // ======================
    $transaction = [
        'username'       => $this->request->getPost('username'),
        'alamat'         => $this->request->getPost('alamat'),
        'ongkir'         => $ongkir,
        'diskon'         => $diskon,
        'biaya_jasa'     => $biayaJasa,
        'voucher_code'   => $voucherCode,
        'diskon_voucher' => $diskonVoucher,
        'free_mouse'     => $freeMouse,
        'total_harga'    => $grandTotal,
        'status'         => 0,
    ];

    // ======================
    // INSERT TRANSACTION
    // ======================
    if (!$this->transactionModel->insert($transaction)) {
        $db->transRollback();
        return redirect()->back()->with('error', 'Gagal membuat transaksi');
    }

    $transactionId = $this->transactionModel->getInsertID();

    // ======================
    // DETAIL TRANSACTION
    // ======================
    foreach ($cartItems as $item) {

        $subtotalItem = $item['qty'] * $item['price'];
        $diskonItem = ($persenDiskon / 100) * $subtotalItem;

        $this->transactionDetailModel->insert([
            'transaction_id' => $transactionId,
            'product_id'     => $item['id'],
            'jumlah'         => $item['qty'],
            'diskon'         => $diskonItem,
            'subtotal_harga' => $subtotalItem
        ]);
    }

    $db->transComplete();

    if (!$db->transStatus()) {
        return redirect()->back()->with('error', 'Gagal membuat transaksi');
    }

    $this->cart->destroy();

    return redirect()->to(base_url());
}

    public function history()
    {
        $username = session()->get('username'); 
 
        $transactions = $this->transactionModel->where('username', $username)->findAll();
        $transactionIds = array_column($transactions, 'id');

        $products = $this->transactionDetailModel->getProductsByTransactionIds($transactionIds);

        $data = [
            'username'      => $username,
            'transactions'  => $transactions,
            'products'      => $products
        ]; 

        return view('v_history', $data);
    }

}
