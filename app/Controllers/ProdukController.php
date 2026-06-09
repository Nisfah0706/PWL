<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\ProductModel;
use Dompdf\Dompdf;

class ProdukController extends BaseController
{
    protected $productModel; 

    function __construct()
    {
        $this->productModel = new ProductModel();
    }

    public function index()
    {
        return view('produk/index', [
            'products' => $this->productModel->findAll()
        ]);
    }

    public function create()
    {
    $dataFoto = $this->request->getFile('foto');

    $dataForm = [
        'nama' => $this->request->getPost('nama'),
        'harga' => $this->request->getPost('harga'),
        'jumlah' => $this->request->getPost('jumlah') 
    ];

    if ($dataFoto->isValid()) {
        $fileName = $dataFoto->getRandomName(); 
        $dataFoto->move('img/', $fileName);
        
        $dataForm['foto'] = $fileName;
    }

    $this->productModel->insert($dataForm);

        return redirect('produk')->with('success', 'Data Berhasil Ditambah');
    } 

    public function edit($id)
    {
    $dataProduk = $this->productModel->find($id);

    $dataForm = [
        'nama' => $this->request->getPost('nama'),
        'harga' => $this->request->getPost('harga'),
        'jumlah' => $this->request->getPost('jumlah') 
    ];

    if ($this->request->getPost('check') == 1) {
        if ($dataProduk['foto'] != '' and file_exists("img/" . $dataProduk['foto'] . "")) {
            unlink("img/" . $dataProduk['foto']);
        }

        $dataFoto = $this->request->getFile('foto');

        if ($dataFoto->isValid()) {
            $fileName = $dataFoto->getRandomName();
            $dataFoto->move('img/', $fileName);
            
            $dataForm['foto'] = $fileName;
        }
    }

    $this->productModel->update($id, $dataForm);

    return redirect('produk')->with('success', 'Data Berhasil Diubah');
    }

    public function delete($id)
    {
    $dataProduk = $this->productModel->find($id);
    $this->productModel->delete($id);

    return redirect('produk')->with('success', 'Data Berhasil Dihapus');
    }

    public function download()
    {
        // Ambil data produk dari database
        $products = $this->productModel->findAll();

        // Render view menjadi HTML
        $html = view('produk/download_pdf', [
            'products' => $products
        ]);

        // Nama file PDF
        $filename = date('Y-m-d-H-i-s') . '-produk.pdf';

        // Inisialisasi Dompdf
        $dompdf = new Dompdf();

        // Load HTML ke Dompdf
        $dompdf->loadHtml($html);

        // Setting ukuran kertas dan orientasi
        $dompdf->setPaper('A4', 'portrait');

        // Generate PDF
        $dompdf->render();

        // Download / tampilkan PDF
        $dompdf->stream($filename, [
            'Attachment' => true
        ]);
    }

    public function feature()
    {
        return "Masuk ke feature()";
    }
}
