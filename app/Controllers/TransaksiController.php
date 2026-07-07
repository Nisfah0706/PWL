<?php

namespace App\Controllers;

use App\Controllers\BaseController;
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
        return view('v_keranjang', [
            'items' => $this->cart->contents(),
            'total' => $this->cart->total()
        ]);
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

        return redirect()->to(base_url('/'));
    }

    public function checkout()
    {
        $total = $this->cart->total();
        $hasilDiskon = hitung_diskon($total);

        return view('v_checkout', [
            'items'  => $this->cart->contents(),
            'total'  => $total,
            'diskon' => $hasilDiskon['diskon'],
            'persen' => $hasilDiskon['persen']
        ]);
    }

    public function destinations()
    {
        $search = $this->request->getGet('q');

        $service = new RajaOngkirService();
        $response = $service->getDestination($search);

        $results = [];

        foreach (($response['data'] ?? []) as $item) {
            $results[] = [
                'id'   => $item['id'],
                'text' => $item['label']
            ];
        }

        return $this->response->setJSON(['results' => $results]);
    }

    public function costs()
    {
        $destination = $this->request->getGet('destination');

        $service = new RajaOngkirService();
        $response = $service->getCost('64999', $destination, '1000', 'jne');

        $results = [];

        foreach (($response['data'] ?? []) as $item) {
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

        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['qty'] * $item['price'];
        }

        $hasilDiskon = hitung_diskon($subtotal);
        $persenDiskon = $hasilDiskon['persen'] ?? 0;
        $diskon = $hasilDiskon['diskon'] ?? 0;

        $voucherCode = strtoupper(trim($this->request->getPost('voucher_code') ?? ''));

        $diskonVoucher = 0;
        if ($voucherCode !== '') {
            $diskonVoucher = hitung_diskon_voucher($subtotal, $voucherCode) ?? 0;
        }

        $biayaJasa = hitung_biaya_jasa($subtotal) ?? 0;
        $freeMouse = hitung_free_mouse($subtotal) ?? 0;
        $ongkir = (int) ($this->request->getPost('ongkir') ?? 0);

        $subtotalAkhir = $subtotal + $biayaJasa - $diskonVoucher - $freeMouse;
        $grandTotal = $subtotalAkhir + $ongkir;

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

        $transactions = $this->transactionModel
            ->where('username', $username)
            ->findAll();

        $transactionIds = array_column($transactions, 'id');

        $products = $this->transactionDetailModel
            ->getProductsByTransactionIds($transactionIds);

        return view('v_history', [
            'username'     => $username,
            'transactions' => $transactions,
            'products'     => $products
        ]);
    }
}