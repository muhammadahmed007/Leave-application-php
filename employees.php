<?php 
$_SESSION['formToken']['employees'] = password_hash(uniqid(),PASSWORD_DEFAULT);
$from = $_GET['from'] ?? date("Y-m-d");
$to = $_GET['to'] ?? date("Y-m-t");

?>
<h1 class="text-center fw-bolder">List of Employees</h1>
<hr class="mx-auto opacity-100" style="width:50px;height:3px">
<div class="col-lg-10 col-md-11 col-sm-12 mx-auto py-3">
    <div class="card rounded-0 shadow">
        <div class="card-body rounded-0">
            <div class="container-fluid">
                <div class="row justify-content-end mb-3">
                    <div class="col-auto">
                        <a class="btn btn-sm btn-primary rounded-0 d-flex align-items-center" href="./?page=manage_employee"><i class="material-symbols-outlined">add</i> Add New</a>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover table-striped">
                        <colgroup>
                            <col width="5%">
                            <col width="20%">
                            <col width="15%">
                            <col width="25%">
                            <col width="15%">
                            <col width="15%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Date Added</th>
                                <th class="text-center">Code</th>
                                <th class="text-center">Employee</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            $employees_sql = "SELECT `employee_id`, `code`, `date_created`, `status`, (`lastname` || ' ' ||  `firstname` ||  ' ' || `middlename`) as `fullname` FROM `employee_list` ORDER BY (`lastname` || ' ' ||  `firstname` ||  ' ' || `middlename`) asc";
                              
                            $employees_qry = $conn->query($employees_sql);
                            while($row = $employees_qry->fetchArray()):
                                $date_created = new DateTime($row['date_created'], new DateTimeZone('UTC'));$date_created->setTimezone(new DateTimeZone('Asia/Manila'));
                            ?>
                            <tr>
                                <td class="text-center"><?= $i++; ?></td>
                                <td><?= $date_created->format('Y-m-d g:i A') ?></td>
                                <td><?= $row['code'] ?></td>
                                <td><?= $row['fullname'] ?></td>
                                <td class="text-center">
                                    <?php 
                                        switch($row['status']){
                                            case 1:
                                                echo "<span class='badge bg-success border rounded-pill px-3'>Active</span>";
                                                break;
                                            case 2:
                                                echo "<span class='badge bg-secondary border rounded-pill px-3'>Inactive</span>";
                                                break;
                                        }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a class="btn btn-sm btn-outline-dark rounded-0 view_data" href="./?page=view_employee&id=<?= $row['employee_id'] ?>" data-id='<?= $row['employee_id'] ?>' title="View Employee"><span class="material-symbols-outlined">subject</span></a>
                                        <?php if($_SESSION['type'] == 1): ?>
                                        <a class="btn btn-sm btn-outline-primary rounded-0 edit_data" href="./?page=manage_employee&id=<?= $row['employee_id'] ?>" data-id='<?= $row['employee_id'] ?>' title="Edit Employee"><span class="material-symbols-outlined">edit</span></a>
                                        <button class="btn btn-sm btn-outline-danger rounded-0 delete_data" type="button" data-id='<?= $row['employee_id'] ?>' title="Delete Employee"><span class="material-symbols-outlined">delete</span></button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if(!$employees_qry->fetchArray()): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No data found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        $('.delete_data').on('click', function(e){
            e.preventDefault()
            var id = $(this).attr('data-id');
            start_loader()
            var _conf = confirm(`Are you sure to delete this employee data? This action cannot be undone`);
            if(_conf === true){
                $.ajax({
                    url:'Master.php?a=delete_employee',
                    method:'POST',
                    data: {
                        token: '<?= $_SESSION['formToken']['employees'] ?>',
                        id: id
                    },
                    dataType:'json',
                    error: err=>{
                        console.error(err)
                        alert("An error occurred.")
                        end_loader()
                    },
                    success:function(resp){
                        if(resp.status == 'success'){
                            location.reload()
                        }else{
                            console.error(resp)
                            alert(resp.msg)
                        }
                        end_loader()
                    }
                })
            }else{
                end_loader()
            }
        })
        $('#filter').click(function(e){
            e.preventDefault()
            var from = $('#date_from').val()
            var to = $('#date_to').val()
            location.replace(`./?page=employees&from=${from}&to=${to}`)
        })
    })
</script>
