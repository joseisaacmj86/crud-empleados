<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Employees extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Employee');
        $this->load->helper('url');         
    }

    
    public function index() {
        $data = [];
        $this->load->view('employees/view', $data);
    }

    /**
     * Listar todos los empleados
     */
    public function get_all_employees() {
        $limit = $this->input->get('limit') ? : 5;
        $offset = $this->input->get('offset') ? : 0;
        $search = $this->input->get('search') ? : '';
    
        $data['employees'] = $this->Employee->get_all_employees($limit, $offset, $search);
        $data['total'] = $this->Employee->count_all_employees($search);
    
        echo json_encode($data);
    }

    /**
     * Mostrar formulario para crear empleado
     */
    public function create() {
        $this->load->view('employees/create');
    }

    /**
     *  comprovar si el email existe
     */
    public function check_email() {
        $email = $this->input->get('email');
        $exists = $this->Employee->email_exists($email);
        
        echo json_encode(["exists" => $exists]);
    }
    
    /**
     * Mostrar formulario para crear
     */
    public function store() {
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if ($this->Employee->email_exists($data['email'])) {
            echo json_encode(["success" => false, "message" => "El email ya está registrado."]);
            return;
        }
    
        // Validar edad mínima
        $birthdate = new DateTime($data['birthdate']);
        $today = new DateTime();
        $age = $today->diff($birthdate)->y;
        if ($age < 18) {
            echo json_encode(["success" => false, "message" => "El empleado debe ser mayor de 18 años."]);
            return;
        }
    
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT); // Hashear la contraseña
        $inserted = $this->Employee->insert_employee($data);
    
        echo json_encode(["success" => $inserted]);
    }

    /**
     * traer empleado por ID
     */
    public function get_employee_by_id ($id) {
        $employee = $this->Employee->get_employee_by_id($id);
        echo json_encode(['success' => true, 'data' => $employee]);
    }

    /**
     * Actualizar empleado
     */
    public function update($id) {
        $this->check_permission();
        $data = json_decode(file_get_contents("php://input"), true);
    
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
    
        $success = $this->Employee->update_employee($id, $data);
        echo json_encode(['success' => $success]);
    }

    /**
     * Eliminar empleado
     */
    public function delete($id) {
        $this->check_permission();
        $success = $this->Employee->delete_employee($id);
        echo json_encode(['success' => $success]);
    }
    

}
