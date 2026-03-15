<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TicketModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class TicketController extends BaseController
{
    use ResponseTrait;


    public function index(): ResponseInterface
    {
        if ($resp = $this->ensureAdmin()) {
            return $resp;
        }

        $ticketModel = new TicketModel();

        $page = (int)($this->request->getGet('page') ?? 1);
        $perPage = (int)($this->request->getGet('perPage') ?? 10);
        $perPage = max(1, min($perPage, 50));

        $estado = $this->request->getGet('estado');
        $prioridad = $this->request->getGet('prioridad');
        $showDeleted = $this->request->getGet('showDeleted');
        $search = $this->request->getGet('search');
        $sortBy = $this->request->getGet('sortBy') ?? 'created_at';
        $sortDir = strtolower($this->request->getGet('sortDir') ?? 'desc');

        // Sanitizar campos permitidos para ordenar
        $allowedSortFields = ['id', 'titulo', 'estado', 'prioridad', 'created_at', 'creado_por'];
        if (!in_array($sortBy, $allowedSortFields, true)) {
            $sortBy = 'created_at';
        }

        $sortDir = $sortDir === 'asc' ? 'ASC' : 'DESC'; // default DESC [web:474][web:475][web:476]

        if (!empty($estado)) {
            $ticketModel->where('estado', $estado);
        }

        if (!empty($prioridad)) {
            $ticketModel->where('prioridad', $prioridad);
        }

        if ($showDeleted === 'all') {
            $ticketModel->withDeleted();
        } elseif ($showDeleted === 'only') {
            $ticketModel->onlyDeleted();
        }

        if (!empty($search)) {
            $ticketModel
                ->groupStart()
                ->like('titulo', $search)
                ->orLike('descripcion', $search)
                ->orLike('creado_por', $search)
                ->groupEnd();
        }

        $tickets = $ticketModel
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage, 'tickets', $page);

        $pager = $ticketModel->pager;

        return $this->respond([
            'data' => $tickets,
            'currentPage' => $pager->getCurrentPage('tickets'),
            'perPage' => $pager->getPerPage('tickets'),
            'total' => $pager->getTotal('tickets'),
            'pageCount' => $pager->getPageCount('tickets'),
            'sortBy' => $sortBy,
            'sortDir' => strtolower($sortDir),
        ]);
    }


    // app/Controllers/Admin/TicketController.php

    public function summary(): ResponseInterface
    {
        $user = auth()->user();

        if (!$user) {
            return $this->failUnauthorized('No autenticado');
        }

        if (!$user->inGroup('admin')) {
            return $this->failForbidden('No tienes permisos para ver el resumen de tickets.');
        }

        $db = db_connect();
        $builder = $db->table('tickets');

        // Total
        $total = (int)$builder->countAllResults(false); // false para no resetear builder

        // Conteos por estado
        $builder->select('estado, COUNT(*) as total_por_estado')
            ->groupBy('estado');
        $estadoRows = $builder->get()->getResultArray();

        $porEstado = [
            'abierto' => 0,
            'en progreso' => 0,
            'cerrado' => 0,
        ];

        foreach ($estadoRows as $row) {
            $key = $row['estado'];
            if (!isset($porEstado[$key])) {
                $porEstado[$key] = 0;
            }
            $porEstado[$key] = (int)$row['total_por_estado'];
        }

        return $this->respond([
            'total' => $total,
            'abiertos' => $porEstado['abierto'] ?? 0,
            'enProgreso' => $porEstado['en progreso'] ?? 0,
            'cerrados' => $porEstado['cerrado'] ?? 0,
        ]);
    }


    protected function ensureAdmin(): ?ResponseInterface
    {
        $user = auth()->user();

        if (!$user) {
            return $this->failUnauthorized('No autenticado');
        }

        if (!$user->inGroup('admin')) {
            return $this->failForbidden('No tienes permisos para esta acción.');
        }

        return null;
    }


    public function show(int $id): ResponseInterface
    {
        if ($resp = $this->ensureAdmin()) {
            return $resp;
        }

        $ticketModel = new TicketModel();
        $ticket = $ticketModel->find($id);

        if (!$ticket) {
            return $this->failNotFound('Ticket no encontrado');
        }

        return $this->respond($ticket);
    }


    public function create(): ResponseInterface
    {
        if ($resp = $this->ensureAdmin()) {
            return $resp;
        }

        $data = $this->request->getJSON(true);

        $rules = [
            'titulo' => 'required|max_length[255]',
            'descripcion' => 'permit_empty|string',
            'estado' => 'permit_empty|in_list[abierto,en progreso,cerrado]',
            'prioridad' => 'permit_empty|in_list[alta,media,baja]',
        ];

        if (!$this->validateData($data, $rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $ticketModel = new TicketModel();

        $insertData = [
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? null,
            'estado' => $data['estado'] ?? 'abierto',
            'prioridad' => $data['prioridad'] ?? 'media',
            'creado_por' => auth()->user()->email ?? 'admin',
        ];

        $id = $ticketModel->insert($insertData, true);

        $ticket = $ticketModel->find($id);

        return $this->respondCreated($ticket);
    }

    public function update(int $id = null): ResponseInterface
    {
        if ($resp = $this->ensureAdmin()) {
            return $resp;
        }

        $ticketModel = new TicketModel();
        $ticket = $ticketModel->find($id);

        if (!$ticket) {
            return $this->failNotFound('Ticket no encontrado');
        }

        $data = $this->request->getJSON(true) ?? [];

        $rules = [
            'estado' => 'permit_empty|string',
            'prioridad' => 'permit_empty|string',
        ];

        if (!$this->validateData($data, $rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $changes = [];

        if (isset($data['estado']) && $data['estado'] !== $ticket['estado']) {
            $changes['estado'] = $data['estado'];
        }

        if (isset($data['prioridad']) && $data['prioridad'] !== $ticket['prioridad']) {
            $changes['prioridad'] = $data['prioridad'];
        }

        if (empty($changes)) {
            return $this->respond($ticket);
        }

        $ticketModel->update($id, $changes);

        $ticketActualizado = $ticketModel->find($id);

        // aquí ya tienes lo de registrar comentario de cambio de estado

        return $this->respondUpdated($ticketActualizado);
    }


    public function delete(int $id = null): ResponseInterface
    {
        if ($resp = $this->ensureAdmin()) {
            return $resp;
        }

        $ticketModel = new TicketModel();
        $ticket = $ticketModel->find($id);

        if (!$ticket) {
            return $this->failNotFound('Ticket no encontrado o ya eliminado');
        }

        $ticketModel->delete($id);

        if ($ticketModel->db->affectedRows() === 0) {
            return $this->failServerError('No se pudo eliminar el ticket');
        }

        return $this->respondDeleted([
            'id' => $id,
            'message' => 'Ticket eliminado',
        ]);
    }


    public function bulkDelete(): ResponseInterface
    {
        if ($resp = $this->ensureAdmin()) {
            return $resp;
        }

        $data = $this->request->getJSON(true) ?? [];

        if (empty($data['ids']) || !is_array($data['ids'])) {
            return $this->failValidationErrors(['ids' => 'Debes enviar un arreglo de IDs']);
        }

        // limpiar y filtrar ids
        $ids = array_values(array_unique(array_filter(array_map('intval', $data['ids']))));

        if (empty($ids)) {
            return $this->failValidationErrors(['ids' => 'No hay IDs válidos para eliminar']);
        }

        $ticketModel = new TicketModel();

        // si usas soft deletes, esto marcará deleted_at
        $ticketModel
            ->whereIn('id', $ids)
            ->delete();

        return $this->respondDeleted([
            'ids' => $ids,
            'message' => 'Tickets eliminados',
        ]);
    }


}
