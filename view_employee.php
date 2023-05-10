<?php 
if(isset($_GET['id']) && $_GET['id'] > 0){
    $sql = "SELECT *, (`lastname` || ' ' ||  `firstname` ||  ' ' || `lastname`) as `fullname` FROM `employee_list` where `employee_id` = '{$_GET['id']}' ";
    $query = $conn->query($sql);
    $data = $query->fetchArray();
}else{
    throw new ErrorException("This page requires a valid ID.");
}
$_SESSION['formToken']['employeeDetails'] = password_hash(uniqid(), PASSWORD_DEFAULT);
?>
<h1 class="text-center fw-bolder">Employee Details</h1>
<hr class="mx-auto opacity-100" style="width:50px;height:3px">
<div class="col-lg-8 col-md-10 col-sm-12 mx-auto py-3">
    <div class="card rounded-0 shadow">
        <div class="card-body rounded-0">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <div class="col-auto flex-shrink-1 flex-grow-1">
                        <h2><b><?= $data['code'] ?? "" ?> - <?= $data['fullname'] ?? "" ?></b></h2>
                    </div>
                    <div class="col-auto">
                        <?php if(isset($data['status'])):
                            switch($data['status']){
                                case 1:
                                    echo "<span class='badge bg-success border rounded-pill px-3'>Active</span>";
                                    break;
                                case 2:
                                    echo "<span class='badge bg-secondary border rounded-pill px-3'>Inactive</span>";
                                    break;
                            }
                        endif;
                        ?>
                    </div>
                </div>
                <hr class="mx-auto border-primary opacity-100" style="width:50px;height:3px">
                <div class="row">
                    <div class="col-lg-7 col-md-6 col-sm-12 col-12">
                        <dl>
                            <dt class="text-body-tertiary">Email:</dt>
                            <dd class="ps-4 h5 fw-lighter"><?= $data['email'] ?? "" ?></dd>
                            <dt class="text-body-tertiary">Contact #:</dt>
                            <dd class="ps-4 h5 fw-lighter py-3"><?= $data['contact'] ?? "" ?></dd>
                            <dt class="text-body-tertiary">Department:</dt>
                            <dd class="ps-4 h5 fw-lighter"><?= $data['department'] ?? "" ?></dd>
                            <dt class="text-body-tertiary">Designation:</dt>
                            <dd class="ps-4 h5 fw-lighter"><?= $data['designation'] ?? "" ?></dd>
                        </dl>
                    </div>
                    <div class="col-lg-5 col-md-6 col-sm-12 col-12">
                        <div class="table-responsive mx-0 mt-4 mb-2">
                            <table class="table table-sm table-hover table-bordered table-striped" id="leave_priv_tbl">
                                <colgroup>
                                    <col width="55%">
                                    <col width="15%">
                                    <col width="15%">
                                    <col width="15%">
                                </colgroup>
                                    
                                    <tr class="bg-secondary bg-opacity-75 text-light">
                                        <th colspan="4" class="text-center">Leave Privileges</th>
                                    </tr>
                                    <tr class="bg-secondary bg-opacity-50 text-light">
                                        <th class="text-center">Name</th>
                                        <th class="text-center">Credits</th>
                                        <th class="text-center">Used</th>
                                        <th class="text-center">Available</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if(isset($data['employee_id'])):
                                    $sql = "SELECT * FROM `leave_priv_list` where `employee_id` = '{$data['employee_id']}'";
                                    $qry = $conn->query($sql);
                                    while($row = $qry->fetchArray()):
                                        $used_qry = $conn->query("SELECT * FROM `application_list` where `leave_priv_id` = '{$row['leave_priv_id']}' and strftime('%Y') = '". date('Y')."' and `status` = 1 ");
                                        $used = 0;
                                        while($urow = $used_qry->fetchArray(SQLITE3_ASSOC)){
                                            $used += (((strtotime($urow['to']) - strtotime($urow['from'])) / (60 * 60 * 24)) + 1) / $urow['type'];
                                        }
                                        $row['used'] = $used;
                                        $row['available'] = $row['credits'] - $used;
                                    ?>
                                    <tr>
                                        <td><b><?= $row['name'] ?></b></td>
                                        <td class="text-end"><b><?= number_format($row['credits']) ?></b></td>
                                        <td class="text-end"><b><?= number_format($row['used']) ?></b></td>
                                        <td class="text-end"><b><?= number_format($row['available']) ?></b></td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <a href="./?page=employees" class="btn btn btn-secondary rounded-0">Back to List</a>
                    <a href="./?page=manage_employee&id=<?= $data['employee_id'] ?? "" ?>" class="btn btn btn-primary rounded-0">Edit</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        $('#update_status').click(function(e){
            e.preventDefault()
            start_loader()
            var _conf = confirm(`Are you sure you want update the status of this employee?`)
            if(_conf === true){
                $.ajax({
                    url:"./Master.php?a=update_employee_status",
                    method:'POST',
                    data:{
                        formToken:'<?= $_SESSION['formToken']['employeeDetails'] ?>',
                        employee_id:'<?= $data['employee_id'] ?? '' ?>',
                        status: $(this).attr('data-status')
                    },
                    dataType:'json',
                    error: err=>{
                        end_loader()
                        console.alert(err)
                        alert(`An error occurred while updating the employee status`);
                    },
                    success: function(resp){
                        if(resp.status == 'success'){
                            location.reload()
                        }else{
                            if(!!resp.msg){
                                alert(resp.msg);
                            }
                            end_loader()
                            console.error(resp)
                        }
                    }
                })
            }
            end_loader()
        })
        $('#wallet-form').submit(function(e){
            e.preventDefault();
            $('.pop_msg').remove()
            var _this = $(this)
            var _el = $('<div>')
                _el.addClass('pop_msg')
            _this.find('button').attr('disabled',true)
            _this.find('button[type="submit"]').text('Loging in...')
            $.ajax({
                url:'./Master.php?a=save_settings',
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
                    _this.find('button[type="submit"]').text('Save')
                },
                success:function(resp){
                    if(resp.status == 'success'){
                        _el.addClass('alert alert-success')
                        setTimeout(() => {
                            _el.remove()
                        }, 2000);
                    }else{
                        _el.addClass('alert alert-danger')
                    }
                    _el.text(resp.msg)

                    _el.hide()
                    _this.prepend(_el)
                    _el.show('slow')
                    _this.find('button').attr('disabled',false)
                    _this.find('button[type="submit"]').text('Save')
                }
            })
        })
    })
</script>
