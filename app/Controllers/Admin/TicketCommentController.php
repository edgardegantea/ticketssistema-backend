<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TicketCommentModel;
use App\Models\TicketModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;


class TicketCommentController extends BaseController
{
    use ResponseTrait;

    protected function ensureAdmin(): ?ResponseInterface
    {
        $user = auth()->user();

        if (! $user) {
            return $this->failUnauthorized('No autenticado');
        }

        if (! $user->inGroup('admin')) {
            return $this->failForbidden('No tienes permisos para esta acción.');
        }

        return null;
    }

    public function index(int $ticketId): ResponseInterface
    {
        if ($resp = $this->ensureAdmin()) {
            return $resp;
        }

        $ticketModel = new TicketModel();
        $ticket      = $ticketModel->find($ticketId);

        if (! $ticket) {
            return $this->failNotFound('Ticket no encontrado');
        }

        $commentModel = new TicketCommentModel();

        $comments = $commentModel
            ->where('ticket_id', $ticketId)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        return $this->respond([
            'ticketId'  => $ticketId,
            'comments'  => $comments,
        ]);
    }

    public function create(int $ticketId): ResponseInterface
    {
        if ($resp = $this->ensureAdmin()) {
            return $resp;
        }

        $ticketModel = new TicketModel();
        $ticket      = $ticketModel->find($ticketId);

        if (! $ticket) {
            return $this->failNotFound('Ticket no encontrado');
        }

        $data = $this->request->getJSON(true);

        $rules = [
            'comentario' => 'required|string',
        ];

        if (! $this->validateData($data, $rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $commentModel = new TicketCommentModel();

        $insertData = [
            'ticket_id'  => $ticketId,
            'autor'      => auth()->user()->email ?? 'admin',
            'comentario' => $data['comentario'],
        ];

        $id = $commentModel->insert($insertData, true);

        $comment = $commentModel->find($id);

        return $this->respondCreated($comment);
    }


    public function update(int $id = null): ResponseInterface
    {
        if ($resp = $this->ensureAdmin()) {
            return $resp;
        }

        $ticketModel = new TicketModel();
        $ticket      = $ticketModel->find($id);

        if (! $ticket) {
            return $this->failNotFound('Ticket no encontrado');
        }

        $data = $this->request->getJSON(true) ?? [];

        $rules = [
            'estado'    => 'permit_empty|string',
            'prioridad' => 'permit_empty|string',
            'titulo'    => 'permit_empty|string',
            'descripcion' => 'permit_empty|string',
        ];

        if (! $this->validateData($data, $rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $changes = [];
        $estadoAnterior = $ticket['estado'] ?? null;
        $estadoNuevo    = $data['estado'] ?? null;

        if (isset($data['estado']) && $data['estado'] !== $ticket['estado']) {
            $changes['estado'] = $data['estado'];
        }

        if (isset($data['prioridad']) && $data['prioridad'] !== $ticket['prioridad']) {
            $changes['prioridad'] = $data['prioridad'];
        }

        // otros campos...
        if (isset($data['titulo'])) {
            $changes['titulo'] = $data['titulo'];
        }
        if (isset($data['descripcion'])) {
            $changes['descripcion'] = $data['descripcion'];
        }

        if (empty($changes)) {
            return $this->respond($ticket); // nada que cambiar
        }

        $ticketModel->update($id, $changes);
        $ticketActualizado = $ticketModel->find($id);

        // Registrar comentario automático si cambió el estado
        if (array_key_exists('estado', $changes)) {
            $commentModel = new TicketCommentModel();

            $commentModel->insert([
                'ticket_id'       => $id,
                'tipo'            => 'sistema',
                'accion'          => 'cambio_estado',
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo'    => $estadoNuevo,
                'autor'           => auth()->user()->email ?? 'admin',
                'comentario'      => "Estado cambiado de {$estadoAnterior} a {$estadoNuevo}",
            ]);
        }

        return $this->respondUpdated($ticketActualizado);
    }
}
