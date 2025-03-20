Vue.component('paginate', VuejsPaginate);

const employeeMethods = {
    data() {
        return {
            employees: [],
            currentPage: 1,
            perPage: 5,
            totalPages: 0,
            searchQuery: "",
            isEditing: false,
            passwordFieldType: "password",
            emailError: "",
            birthdateError: "",
            formData: {
                employee_id: null,
                employee_name: "",
                last_name: "",
                email: "",
                birthdate: "",
                department_id: "",
                hiring_date: "",
                role_id: "",
                password: ""
            },
            role_id_employee:"",
            employee_log_name: "",
        };
    },
    methods: {
        get_employee_role() {
            this.role_id_employee= localStorage.getItem("role_id");
            console.log(this.role_id_employee);
        },
        get_employees(page = 1) {
            let offset = (page - 1) * this.perPage;
            let data = { 
                offset: offset, 
                limit: this.perPage,
                search: this.searchQuery
            };
    
            $.get(SITE_URL + "/employees/get_all_employees", data, (resp) => {
                resp = JSON.parse(resp);
                this.employees = resp.employees;                
                this.totalPages = Math.ceil(resp.total / this.perPage);
            });
        },
        searchEmployees() {
            this.currentPage = 1;
            this.get_employees();
        },
        goToPage(page) {
            this.currentPage = page;
            this.get_employees(page);
        },
        prevPage() {
            if (this.currentPage > 1) {
                this.goToPage(this.currentPage - 1);
            }
        },
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.goToPage(this.currentPage + 1);
            }
        },  
        openModal(employee_id = null) {
            if (employee_id) {
                $.get(SITE_URL + "/employees/get_employee_by_id/" + employee_id, (resp) => {
                    resp = JSON.parse(resp);
                    
                    console.log("resp:"+resp.data.employee_name);

                    this.formData = resp.data;
        
                    this.formDataBefore = JSON.parse(JSON.stringify(resp.data));
    
                    this.formData.password = "";
        
                    this.isEditing = true;                    
                
                    $("#employeeModal").modal("show");
                });
            } else {                
                this.formData = {
                    employee_name: "",
                    last_name: "",
                    email: "",
                    birthdate: "",
                    department_id: "",
                    hiring_date: "",
                    role_id: "",
                    password: ""
                };
        
                this.isEditing = false;
                $("#employeeModal").modal("show");
            }
        },     
        togglePassword() {
            this.passwordFieldType = this.passwordFieldType === "password" ? "text" : "password";
        },       
        validateBirthdate() {
            let birthdate = new Date(this.formData.birthdate);
            let today = new Date();
            let age = today.getFullYear() - birthdate.getFullYear();
            if (today < new Date(birthdate.setFullYear(birthdate.getFullYear() + age))) {
                age--;
            }
            this.birthdateError = age < 18 ? "El empleado debe ser mayor de 18 años." : "";
        },  
        saveEmployee() {           
            
            if (this.isEditing) {
                let hasChanges = false;
                let fields = ["employee_name", "last_name", "email", "birthdate", "department_id", "hiring_date", "role_id"];
        
                fields.forEach(field => {
                    if (this.formDataBefore[field] !== this.formData[field]) {
                        hasChanges = true;
                    } else {
                        delete this.formData[field];
                    }
                });
        
                if (!hasChanges && this.formData.password == "") {
                    console.log("No hay cambios en los datos, redirigiendo...");
                    window.location.href = SITE_URL + "/employees";
                    return;
                }
        
                if (this.formData.password === "") {
                    delete this.formData.password;
                }

                if (this.formData.email) {
            
                    $.get(SITE_URL + "/employees/check_email", { email: this.formData.email.trim() }, (resp) => {
                        resp = JSON.parse(resp);
                        this.emailError = resp.exists ? "El correo electrónico ya está registrado." : "";
                    });
                }
        
            }else{
            
                $.get(SITE_URL + "/employees/check_email", { email: this.formData.email.trim() }, (resp) => {
                    resp = JSON.parse(resp);
                    this.emailError = resp.exists ? "El correo electrónico ya está registrado." : "";
                });
            }
            
            if (this.emailError || this.birthdateError) return;

            let url = this.isEditing ? `${SITE_URL}/employees/update/${this.formData.employee_id}` : `${SITE_URL}/employees/store`;
            let method = this.isEditing ? "PUT" : "POST";

            $.ajax({
                url: url,
                method: method,
                data: JSON.stringify(this.formData),
                contentType: "application/json", 
                processData: false,
                success: (resp) => {
                    resp = JSON.parse(resp);        
                    
                    if (resp.success) {
                        $("#employeeModal").modal("hide");
                        this.get_employees(this.currentPage);
                    } else {
                        alert("Error al actualizar el empleado.");
                    }
                }
            });
        },    
        deleteEmployee(id) {
            if (confirm("¿Estás seguro de eliminar este empleado?")) {
                $.ajax({
                    url: SITE_URL + "/employees/delete/" + id,
                    method: "DELETE",
                    success: (resp) => {
                        resp = JSON.parse(resp);
                        if (resp.success) {
                            this.get_employees(this.currentPage);
                        } else {
                            alert("Error: " + resp.message);
                        }
                    }
                });
            }
        },
    },
    mounted() {
        this.get_employee_role ();
        this.get_employees();
    }
};

var app1 = new Vue({
    el: "#employeeApp",
    mixins: [employeeMethods]
});
