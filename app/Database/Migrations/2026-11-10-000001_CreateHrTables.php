<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHrTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'INTEGER', 'unsigned' => true, 'auto_increment' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'email'         => ['type' => 'VARCHAR', 'constraint' => 150],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'          => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'employee'],
            'position'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'is_active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('employees');

        $this->forge->addField([
            'id'          => ['type' => 'INTEGER', 'unsigned' => true, 'auto_increment' => true],
            'employee_id' => ['type' => 'INTEGER', 'unsigned' => true],
            'work_date'   => ['type' => 'DATE'],
            'check_in'    => ['type' => 'DATETIME'],
            'check_out'   => ['type' => 'DATETIME', 'null' => true],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 10],
            'note'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        // Satu karyawan hanya boleh punya satu absensi per hari
        $this->forge->addUniqueKey(['employee_id', 'work_date']);
        $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('attendances');

        $this->forge->addField([
            'id'          => ['type' => 'INTEGER', 'unsigned' => true, 'auto_increment' => true],
            'employee_id' => ['type' => 'INTEGER', 'unsigned' => true],
            'type'        => ['type' => 'VARCHAR', 'constraint' => 10],
            'start_date'  => ['type' => 'DATE'],
            'end_date'    => ['type' => 'DATE'],
            'days'        => ['type' => 'INTEGER', 'unsigned' => true],
            'reason'      => ['type' => 'VARCHAR', 'constraint' => 500],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'pending'],
            'reviewed_by' => ['type' => 'INTEGER', 'unsigned' => true, 'null' => true],
            'reviewed_at' => ['type' => 'DATETIME', 'null' => true],
            'review_note' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['employee_id', 'status']);
        $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('leave_requests');
    }

    public function down(): void
    {
        $this->forge->dropTable('leave_requests', true);
        $this->forge->dropTable('attendances', true);
        $this->forge->dropTable('employees', true);
    }
}
