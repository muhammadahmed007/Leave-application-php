<?php 
$_SESSION['formToken']['applications'] = password_hash(uniqid(),PASSWORD_DEFAULT);
$from = $_GET['from'] ?? date("Y-m-d");
$to = $_GET['to'] ?? date("Y-m-t");

?>
<h1 class="text-center fw-bolder">List of Applications</h1>
<hr class="mx-auto opacity-100" style="width:50px;height:3px">
<div class="col-lg-10 col-md-11 col-sm-12 mx-auto py-3">
    <div class="card rounded-0 shadow">
        <div class="card-body rounded-0">
            <div class="container-fluid">
                <div class="row justify-content-end mb-3">
                    <div class="col-auto">
                        <a class="btn btn-sm btn-primary rounded-0 d-flex align-items-center" href="./?page=manage_application"><i class="material-symbols-outlined">add</i> Add New</a>
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
                                <th class="text-center">Employee</th>
                                <th class="text-center">Details</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            $applications_sql = "SELECT  `application_list`.*, (`employee_list`.`lastname` || ' ' ||  `employee_list`.`firstname` ||  ' ' || `employee_list`.`middlename`) as `fullname`, `leave_priv_list`.`name` as `leave_name`, `application_list`.`type` as `leave_type` FROM `application_list` inner join `employee_list` on `employee_list`.employee_id = `application_list`.employee_id inner join `leave_priv_list` on leave_priv_list.leave_priv_id = `application_list`.leave_priv_id ORDER BY (`employee_list`.`lastname` || ' ' ||  `employee_list`.`firstname` ||  ' ' || `employee_list`.`middlename`) asc";
                              
                            $applications_qry = $conn->query($applications_sql);
                            while($row = $applications_qry->fetchArray()):
                                $date_created = new DateTime($row['date_created'], new DateTimeZone('UTC'));$date_created->setTimezone(new DateTimeZone('Asia/Manila'));

                                $from = new DateTime($row['from'], new DateTimeZone('UTC'));$from->setTimezone(new DateTimeZone('Asia/Manila'));

                                $to = new DateTime($row['to'], new DateTimeZone('UTC'));$to->setTimezone(new DateTimeZone('Asia/Manila'));
                                $dividen = $row['type'] == 2 ? 2 : 1;
                            ?>
                            <tr>
                                <td class="text-center"><?= $i++; ?></td>
                                <td><?= $date_created->format('Y-m-d g:i A') ?></td>
                                <td><?= $row['fullname'] ?></td>
                                <td>
                                    <dl class="d-flex lh-1 my-0">
                                        <dt class="col-auto pe-1">Name:</dt>
                                        <dd><?= $row['leave_name'] ?></dd>
                                    </dl>
                                    <dl class="d-flex lh-1 my-0">
                                        <dt class="col-auto pe-1">Date:</dt>
                                        <dd>
                                            <?php if(strtotime($row['from']) == strtotime($row['to'])): ?>
                                                <?= date("M d, Y", strtotime($row['from'])) ?>
                                            <?php else: ?>
                                                <?= date("M d, Y", strtotime($row['from'])) ?> - <?= date("M d, Y", strtotime($row['to'])) ?>
                                            <?php endif; ?>
                                        </dd>
                                    </dl>
                                    <dl class="d-flex lh-1 my-0">
                                        <dt class="col-auto pe-1">Type:</dt>
                                        <dd><?= $row['leave_type'] == 1 ? "Whole Day" : "Half Day" ?></dd>
                                    </dl>
                                    <dl class="d-flex lh-1 my-0">
                                        <dt class="col-auto pe-1">Day(s):</dt>
                                        <dd><?= (((strtotime($row['to']) - strtotime($row['from'])) / (60 * 60 * 24)) + 1) / $dividen ?></dd>
                                    </dl>
                                    <dl class="d-flex lh-1 my-0">
                                        <dt class="col-auto pe-1">Reason:</dt>
                                        <dd><?= $row['remarks'] ?></dd>
                                    </dl>
                                </td>
                                <td class="text-center">
                                    <?php 
                                        switch($row['status']){
                                            case 1:
                                                echo "<span class='badge bg-success border rounded-pill px-3'>Approved</span>";
                                                break;
                                            case 2:
                                                echo "<span class='badge bg-danger border rounded-pill px-3'>Denied</span>";
                                                break;
                                            default:
                                                echo "<span class='badge bg-light text-dark border rounded-pill px-3'>Pending</span>";
                                                break;
                                        }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a class="btn btn-sm btn-outline-primary rounded-0 edit_data" href="./?page=manage_application&id=<?= $row['application_id'] ?>" data-id='<?= $row['application_id'] ?>' title="Edit Application"><span class="material-symbols-outlined">edit</span></a>
                                        <button class="btn btn-sm btn-outline-danger rounded-0 delete_data" type="button" data-id='<?= $row['application_id'] ?>' title="Delete Application"><span class="material-symbols-outlined">delete</span></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if(!$applications_qry->fetchArray()): ?>
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
            var _conf = confirm(`Are you sure to delete this application data? This action cannot be undone`);
            if(_conf === true){
                $.ajax({
                    url:'Master.php?a=delete_application',
                    method:'POST',
                    data: {
                        token: '<?= $_SESSION['formToken']['applications'] ?>',
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
            location.replace(`./?page=applications&from=${from}&to=${to}`)
        })
    })
</script>
