<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'start_date_coverage' => [
                'type' => 'DATETIME',
            ],
            'end_date_coverage' => [
                'type' => 'DATETIME',
            ],
            'coverage' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'price' => [
                'type' => 'DECIMAL',
                'constraint' => 16,
            ],
            'type' => [
                'type' => 'INTEGER',
                'constraint' => 1,
            ],
            'is_risk_banjir' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'is_risk_gempa' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('customers');
    }

    public function down()
    {
        $this->forge->dropTable('customers');
    }
}
