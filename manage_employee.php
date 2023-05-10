<?php 
if(isset($_GET['id']) && $_GET['id'] > 0){
   
    $sql = "SELECT * FROM `employee_list` where `employee_id` = '{$_GET['id']}' ";
    $query = $conn->query($sql);
    $data = $query->fetchArray();
    $sql = "SELECT * FROM `leave_priv_list` where `employee_id` = '{$data['employee_id']}'";
    $qry = $conn->query($sql);
    $lpl_arr = [];
    while($row = $qry->fetchArray(SQLITE3_ASSOC)){
        $lpl_arr[] = $row;
    }
    $lpl_arr_json = json_encode($lpl_arr); 
}

// Generate Manage employee Form Token
$_SESSION['formToken']['employee-form'] = password_hash(uniqid(),PASSWORD_DEFAULT);
?>
<style>
    .tbl-text-field{
        background: transparent !important;
        outline:unset !important;
        border:unset !important;
    }
    button.btn.btn-sm.btn-outline-danger.rem-item {
        padding: 0.35em;
        line-height: .9rem;
        border-radius: 0;
    }
    button.btn.btn-sm.btn-outline-danger.rem-item span {
        font-size: .9rem !important;
    }
</style>
<h1 class="text-center fw-bolder"><?= isset($data['employee_id']) ? "Update Employee Details" : "Add New Employee" ?></h1>
<hr class="mx-auto opacity-100" style="width:50px;height:3px">
<div class="col-lg-12 col-md-12 col-sm-12 col-12 mx-auto">
    <div class="card rounded-0">
        <div class="card-body">
            <div class="container-fluid">
                <form action="" id="employee-form">
                    <input type="hidden" name="formToken" value="<?= $_SESSION['formToken']['employee-form'] ?>">
                    <input type="hidden" name="employee_id" value="<?= $data['employee_id'] ?? '' ?>">
                    <div class="row">
                        <div class="col-lg-7 col-md-6 col-sm-12 col-12">
                            <div class="mb-3">
                                <label for="code" class="text-body-tertiary">Code</label>
                                <input type="text" class="form-control rounded-0" id="code" name="code" required="required" autofocus value="<?= $data['code'] ?? "" ?>">
                            </div>
                            <div class="mb-3">
                                <label for="firstname" class="text-body-tertiary">First name</label>
                                <input type="text" class="form-control rounded-0" id="firstname" name="firstname" required="required" value="<?= $data['firstname'] ?? "" ?>">
                            </div>
                            <div class="mb-3">
                                <label for="middlename" class="text-body-tertiary">Middle name</label>
                                <input type="text" class="form-control rounded-0" id="middlename" name="middlename" required="required"  value="<?= $data['middlename'] ?? "" ?>">
                            </div>
                            <div class="mb-3">
                                <label for="lastname" class="text-body-tertiary">Last name</label>
                                <input type="text" class="form-control rounded-0" id="lastname" name="lastname" required="required"  value="<?= $data['lastname'] ?? "" ?>">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="text-body-tertiary">Email</label>
                                <input type="email" class="form-control rounded-0" id="email" name="email" required="required"  value="<?= $data['email'] ?? "" ?>">
                            </div>
                            <div class="mb-3">
                                <label for="contact" class="text-body-tertiary">Contact</label>
                                <input type="text" class="form-control rounded-0" id="contact" name="contact" required="required"  value="<?= $data['contact'] ?? "" ?>">
                            </div>
                            <div class="mb-3">
                                <label for="department" class="text-body-tertiary">Department</label>
                                <input type="text" class="form-control rounded-0" id="department" name="department" required="required"  value="<?= $data['department'] ?? "" ?>">
                            </div>
                            <div class="mb-3">
                                <label for="designation" class="text-body-tertiary">Designation</label>
                                <input type="text" class="form-control rounded-0" id="designation" name="designation" required="required"  value="<?= $data['designation'] ?? "" ?>">
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select rounded-0" requried>
                                    <option value="1" <?= isset($data['status']) && $data['status'] == 1 ? "selected" : "" ?>>Active</option>
                                    <option value="2" <?= isset($data['status']) && $data['status'] == 2 ? "selected" : "" ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-5 col-md-6 col-sm-12 col-12">
                            <div class="table-responsive mx-0 mt-4 mb-2">
                                <table class="table table-sm table-hover table-bordered table-striped" id="leave_priv_tbl">
                                    <colgroup>
                                        <col width="10%">
                                        <col width="55%">
                                        <col width="35%">
                                    </colgroup>
                                        
                                        <tr class="bg-secondary bg-opacity-75 text-light">
                                            <th colspan="3" class="text-center">Leave Privileges</th>
                                        </tr>
                                        <tr class="bg-secondary bg-opacity-50 text-light">
                                            <th></th>
                                            <th class="text-center">Name</th>
                                            <th class="text-center">Credits</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div class="col-lg-5 col-md-6 col-sm-10 mx-auto">
                                <button class="btn btn-outline-secondary rounded-pill w-100 btn-sm d-flex align-items-center justify-content-center" type="button" id="add_leave_priv"><span class="material-symbols-outlined">add</span> Add Item</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-footer">
            <div class="row justify-content-evenly">
                <button class="btn col-lg-4 col-md-5 col-sm-12 col-12 btn-primary rounded-0" form="employee-form">Save</button>
                <a class="btn col-lg-4 col-md-5 col-sm-12 col-12 btn-secondary rounded-0" href='./?page=employee'>Cancel</a>
            </div>
        </div>
    </div>
</div>
<?php if(!isset($data['employee_id'])): ?>
<script>
    window.addEventListener('load', async () => {
        await add_leave_priv('Vacation Leave', 0)
        await add_leave_priv('Sick Leave', 0)
        await add_leave_priv('Emergency Leave', 0)
    })
</script>
<?php else: ?>
    <script>
    const lvl_arr = JSON.parse('<?= $lpl_arr_json ?>') || {}
    console.log(lvl_arr)
    window.addEventListener('load', () => {
        if(lvl_arr.length > 0){
            lvl_arr.forEach(data=>{
                add_leave_priv(data.name, data.credits, data.leave_priv_id)
            })
        }
    })
</script>
<?php endif; ?>
<script>
    const rowItem = document.createElement('tr')
          rowItem.innerHTML = `
            <td class="text-center"><button tabindex="-1" class="btn btn-sm btn-outline-danger rem-item" type="button"><span class="material-symbols-outlined">close</span></button></td>
            <td><input type="hidden" name="leave_priv_id[]" required/>
            <input type="text" class="tbl-text-field" name="leave_priv_name[]" required/></td>
            <td><input type="number" step="any" class="tbl-text-field text-end" name="leave_priv_credits[]" required/></td>
          `;
    const leave_priv_tbl = document.getElementById('leave_priv_tbl')
    function add_leave_priv(name="", credit="", id=""){
        var item = rowItem.cloneNode(true)
        leave_priv_tbl.querySelector('tbody').appendChild(item)
        item.querySelector('input[name="leave_priv_id[]"]').value = id
        item.querySelector('input[name="leave_priv_name[]"]').value = name
        item.querySelector('input[name="leave_priv_credits[]"]').value = credit
        item.querySelector('.rem-item').addEventListener('click', e=>{
            e.preventDefault()
            if(confirm(`Are you sure to remove this item`) === true){
                item.remove()
            }
        })
    }
    $(function(){
        $('#add_leave_priv').click(function(){
            add_leave_priv()
        })
        $('#employee-form').submit(function(e){
            e.preventDefault();
            $('.pop_msg').remove()
            start_loader()
            var _this = $(this)
            var _el = $('<div>')
                _el.addClass('pop_msg')
            _this.find('button').attr('disabled',true)
            $.ajax({
                url:'./Master.php?a=save_employee',
                method:'POST',
                data:$(this).serialize(),
                dataType:'JSON',
                error:err=>{
                    console.log(err)
                    _el.addClass('alert alert-danger')
                    _el.text("An error occurred.")
                    _this.prepend(_el)
                    _el.show('slow')
                    _this.find('button').attr('disabled',false)
                    end_loader()
                },
                success:function(resp){
                    if(resp.status == 'success'){
                        if('<?= $data['employee_id'] ?? '' ?>' == ''){
                            location.replace("./?page=employees");
                        }else{
                            location.replace("./?page=view_employee&id=<?= $data['employee_id'] ?? '' ?>");
                        }
                    }else{
                        _el.addClass('alert alert-danger')
                    }
                    _el.text(resp.msg)

                    _el.hide()
                    _this.prepend(_el)
                    _el.show('slow')
                    _this.find('button').attr('disabled',false)
                    end_loader()
                }
            })
        })
    })
</script>