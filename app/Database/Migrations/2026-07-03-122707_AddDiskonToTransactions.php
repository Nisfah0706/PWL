<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDiskonToTransactions extends Migration
{
    public function up()
    {
        $this->forge->addColumn('transaction', [
            'diskon' => [
                'type' => 'DOUBLE',
                'default' => 0,
                'null' => false,
                'after' => 'total_harga'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('transaction', 'diskon');
    }
}