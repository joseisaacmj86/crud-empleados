<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee extends CI_Model {

    public function __construct() {
        parent::__construct();        
    }

    /**
     * Obtener todos los empleados con información de roles y departamentos.
     */
    public function get_all_employees($limit, $offset, $search = '') {
        $this->db->select('e.*, d.department_name, r.role_name');
        $this->db->from('employees e');
        $this->db->join('departments d', 'e.department_id = d.department_id', 'left');
        $this->db->join('roles r', 'e.role_id = r.role_id', 'left');
    
        // Aplicar filtro de búsqueda si existe
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('e.employee_name', $search);
            $this->db->or_like('e.last_name', $search);
            $this->db->or_like('d.department_name', $search);
            $this->db->group_end();
        }
    
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        return $query->result();
    }
    
    /**
     * contar empleados con búsqueda
     */    
    public function count_all_employees($search = '') {
        $this->db->from('employees e');
        $this->db->join('departments d', 'e.department_id = d.department_id', 'left');
    
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('e.employee_name', $search);
            $this->db->or_like('e.last_name', $search);
            $this->db->or_like('d.department_name', $search);
            $this->db->group_end();
        }
    
        return $this->db->count_all_results();
    }

    /**
     * Obtener un empleado por email
     */
    public function email_exists($email) {
        $this->db->where('email', $email);
        $exists = $this->db->count_all_results('employees') > 0;
        return $exists ? true : false; 
    }

    /**
     * Insertar un nuevo empleado
     */
    public function insert_employee($data) {
        return $this->db->insert('employees', $data);
    }

    /**
     *   Obtener un empleado por ID
     */
    public function get_employee_by_id($id) {
        return $this->db->get_where('employees', ['employee_id' => $id])->row_array();
    }

     /**
     * Actualizar un empleado por su ID
     */
    public function update_employee($id, $data) {
        if (empty($data) || !is_array($data)) {
            return false;
        }
    
        $this->db->where('employee_id', $id);
        $this->db->set($data);
        return $this->db->update('employees');
    }
    

    /**
     * Eliminar un empleado por su ID
     */
    public function delete_employee($id) {
        $this->db->where('employee_id', $id);
        return $this->db->delete('employees');
    }

    /**
     *   Obtener un empleado logueado
     */
    public function get_logged_employee($user_id) {
        $this->db->select("CONCAT(employee_name, ' ', last_name) AS name");
        $this->db->where('employee_id', $user_id);
        $query = $this->db->get('employees');
        return $query->row();
    }
    
}
