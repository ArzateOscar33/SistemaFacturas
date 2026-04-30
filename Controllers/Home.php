<?php



class Home extends Controller
{


    public function __construct()
    {
        parent::__construct();
    }

    /** Vista principal */
    public function index()
    {
        $data['title'] = 'Acceso al sistema';
        $this->views->getView('login', "index", $data);
    }
}
