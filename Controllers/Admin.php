<?php

class Admin extends Controller
{
    public function __construct()
    {
        session_start();

        if (empty($_SESSION['activo'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        parent::__construct();
    }

    public function index()
    {
        $data['title'] = 'Dashboard';
        $data['active'] = 'dashboard';

        $this->views->getView('Admin', 'index', $data);
    }

    public function clientes()
    {
        requireVista('clientes');
        $data['title'] = 'Clientes';
        $data['active'] = 'clientes';


        $this->views->getView('Admin/clientes', 'clientes', $data);
    }
    public function usuarios()
    {

        requireVista('usuarios');
        $data['title'] = 'Usuarios';
        $data['active'] = 'usuarios';


        $this->views->getView('Admin/usuarios', 'index', $data);
    }
    public function configuracion()
    {

        requireVista('configuracion');
        $data['title'] = 'Configuración';
        $data['active'] = 'configuracion';

        $this->views->getView('Admin/configuracion', 'index', $data);
    }



    public function facturas()
    {
        requireVista('facturas');
        $data['title'] = 'Facturas';
        $data['active'] = 'facturas';

        $this->views->getView('Admin/facturas', 'index', $data);
    }

    public function bitacora()
    {
        requireVista('bitacora');
        $data['title'] = 'Bitácora / Auditoría';
        $data['active'] = 'bitacora';

        $this->views->getView('Admin/bitacora', 'index', $data);
    }

    public function reportes()
    {
        requireVista('reportes');
        $data['title'] = 'Reportes';
        $data['active'] = 'reportes';

        $this->views->getView('Admin/reportes', 'index', $data);
    }

    public function roles()
    {
        requireVista('roles');

        $data['title'] = 'Roles y permisos';
        $data['active'] = 'roles';

        $this->views->getView('Admin/roles', 'roles', $data);
    }

    public function folios()
    {
        requireVista('folios');
        $data['title'] = 'Folios y series';
        $data['active'] = 'folios';

        $this->views->getView('Admin/folios', 'folios', $data);
    }

    public function errores()
    {
        requireVista('errores');
        $data['title'] = 'Errores del sistema';
        $data['active'] = 'errores';

        $this->views->getView('Admin/errores', 'errores', $data);
    }

    public function perfil()
    {
        $data['title'] = 'Mi perfil';
        $data['active'] = 'perfil';

        $this->views->getView('Admin/perfil', 'perfil', $data);
    }





    public function resumen()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode([
                'ok' => false,
                'msg' => 'Método no permitido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $data = $this->model->resumenDashboard();

            echo json_encode([
                'ok' => true,
                'data' => $data
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'ok' => false,
                'msg' => 'Error al obtener el resumen del dashboard.',
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
