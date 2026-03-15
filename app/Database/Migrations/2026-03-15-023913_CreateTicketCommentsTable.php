<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTicketCommentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ticket_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tipo' => [ // manual | sistema
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'manual',
            ],
            'accion' => [ // opcional: cambio_estado, cambio_prioridad, etc.
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'estado_anterior' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'estado_nuevo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'autor' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'comentario' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('ticket_id');
        $this->forge->createTable('ticket_comments', true);
    }


    public function down()
    {
        $this->forge->dropTable('ticket_comments', true);
    }
}
