<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedAtToTables extends Migration
{
    public function up()
    {
        $fields = [
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'updated_at'
            ]
        ];

        // user
        $this->forge->addColumn('user', $fields);

        // product
        $this->forge->addColumn('product', $fields);

        // transaction
        $this->forge->addColumn('transaction', $fields);

        // transaction_detail
        $this->forge->addColumn('transaction_detail', $fields);
    }

    public function down()
    {
        // user
        $this->forge->dropColumn('user', 'deleted_at');

        // product
        $this->forge->dropColumn('product', 'deleted_at');

        // transaction
        $this->forge->dropColumn('transaction', 'deleted_at');

        // transaction_detail
        $this->forge->dropColumn('transaction_detail', 'deleted_at');
    }
}