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
        $user = auth()->user();

        if (! $user) {
            return $this->failUnauthorized('No autenticado');
        }

        if (! $user->inGroup('admin')) {
            return $this->failForbidden('No tienes permisos para ver los tickets.');
        }

        $ticketModel = new TicketModel();

        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('perPage') ?? 10);
        $perPage = max(1, min($perPage, 50));

        $estado    = $this->request->getGet('estado');
        $prioridad = $this->request->getGet('prioridad');

        // Aplica filtros solo si vienen con valor
        if (! empty($estado)) {
            $ticketModel->where('estado', $estado);
        }

        if (! empty($prioridad)) {
            $ticketModel->where('prioridad', $prioridad);
        }

        $tickets = $ticketModel
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage, 'tickets');

        $pager = $ticketModel->pager;

        return $this->respond([
            'data'        => $tickets,
            'currentPage' => $pager->getCurrentPage('tickets'),
            'perPage'     => $pager->getPerPage('tickets'),
            'total'       => $pager->getTotal('tickets'),
            'pageCount'   => $pager->getPageCount('tickets'),
        ]);
    }



    // app/Controllers/Admin/TicketController.php

    public function summary(): ResponseInterface
    {
        $user = auth()->user();

        if (! $user) {
            return $this->failUnauthorized('No autenticado');
        }

        if (! $user->inGroup('admin')) {
            return $this->failForbidden('No tienes permisos para ver el resumen de tickets.');
        }

        $db      = db_connect();
        $builder = $db->table('tickets');

        // Total
        $total = (int) $builder->countAllResults(false); // false para no resetear builder

        // Conteos por estado
        $builder->select('estado, COUNT(*) as total_por_estado')
            ->groupBy('estado');
        $estadoRows = $builder->get()->getResultArray();

        $porEstado = [
            'abierto'      => 0,
            'en progreso'  => 0,
            'cerrado'      => 0,
        ];

        foreach ($estadoRows as $row) {
            $key = $row['estado'];
            if (! isset($porEstado[$key])) {
                $porEstado[$key] = 0;
            }
            $porEstado[$key] = (int) $row['total_por_estado'];
        }

        return $this->respond([
            'total'       => $total,
            'abiertos'    => $porEstado['abierto'] ?? 0,
            'enProgreso'  => $porEstado['en progreso'] ?? 0,
            'cerrados'    => $porEstado['cerrado'] ?? 0,
        ]);
    }


}
