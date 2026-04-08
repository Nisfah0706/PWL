<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/produk', 'ProdukController::index');
$routes->get('/keranjang', 'TransaksiController::index');

$routes->post('produk', 'ProdukController::feature');
$routes->put('produk/1', 'ProdukController::feature');
$routes->delete('produk/1', 'ProdukController::feature');