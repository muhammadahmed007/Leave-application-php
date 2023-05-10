<?php 
if(isset($_GET['id']) && $_GET['id'] > 0){
    $sql = "SELECT * FROM `application_list` where `application_id` = '{$_GET['id']}' ";
    $query = $conn->query($sql);
    $data = $query->fetchArray();
    $sql = "SELECT * FROM `leave_priv_list` where `employee_id` = '{$data['employee_id']}'";
    $qry = $conn->query($sql);
    $lpl = [];
    while($row = $qry->fetchArray(SQLITE3_ASSOC)){
        $used_qry = $conn->query("SELECT * FROM `application_list` where `leave_priv_id` = '{$row['leave_priv_id']}' and strftime('%Y') = '". date('Y')."' and `status` = 1 ");
        $used = 0;
        while($urow = $used_qry->fetchArray(SQLITE3_ASSOC)){
            $used += (((strtotime($urow['to']) - strtotime($urow['from'])) / (60 * 60 * 24)) + 1) / $urow['type'];
        }
        $row['available'] = $row['credits'] - $used;
        $lpl[] = $row;
    }
    $from = new DateTime($data['from'], new DateTimeZone('UTC'));
    $from->setTimezone(new DateTimeZone('Asia/Manila'));
    $from = $from->format("Y-m-d");
    $to = new DateTime($data['to'], new DateTimeZone('UTC'));
    $to->setTimezone(new DateTimeZone('Asia/Manila'));
    $to = $to->format("Y-m-d");
    $dividen = $data['type'] == 2 ? 2 : 1;
    $days = (((strtotime($to) - strtotime($from)) / (60 * 60 * 24)) + 1) / $dividen;
}
$_SESSION['formToken']['application-form'] = password_hash(uniqid(),PASSWORD_DEFAULT);
?>
<style>
    select[readonly]{
        user-select: none;
        cursor: not-allowed;
        pointer-events: none;
    }
</style>
<h1 class="text-center fw-bolder"><?= isset($data['application_id']) ? "Update Application Details" : "Add New Application" ?></h1>
<hr class="mx-auto opacity-100" style="width:50px;height:3px">
<div class="row">
    <div class="col-lg-5 col-md-6 col-sm-12 mx-auto py-3">
        <div class="card rounded-0 shadow">
            <div class="card-body rounded-0">
                <div class="contain-fluid">
                    <form action="" id="update-form">
                        <input type="hidden" name="formToken" value="<?= $_SESSION['formToken']['application-form'] ?>">
                        <input type="hidden" name="application_id" value="<?= $data['application_id'] ?? "" ?>">
                        <div class="mb-3">
                            <label for="employee_id" class="form-label">Employee</label>
                            <select name="employee_id" id="employee_id" class="form-select rounded-0" requried <?= isset($data['employee_id']) ? "readonly" : "" ?>>
                                <option <?= !isset($data['employee_id']) ? "selected" : "" ?> disabled>Please Select Here</option>
                                <?php 
                                $query = $conn->query("SELECT `employee_id`, (`code` || ' - ' || `lastname` || ', ' || `firstname` ||  ' ' || `middlename`) as `employee` FROM `employee_list` where `status` = 1 ".(isset($data['employee_id']) ? " OR `employee_id` = '{$data['employee_id']}'" : '')." order by (`lastname` || ', ' || `firstname` ||  ' ' || `middlename`)  asc");
                                while($row = $query->fetchArray(SQLITE3_ASSOC)):
                                ?>
                                <option value="<?= $row['employee_id'] ?>" <?= isset($data['employee_id']) && $data['employee_id'] == $row['employee_id'] ? "selected" : "" ?>><?= $row['employee'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div id="other-dets" class="<?= !isset($data['employee_id']) ? 'd-none' : '' ?>">
                            <div class="mb-3">
                                <label for="leave_priv_id" class="form-label">Leave Type</label>
                                <select name="leave_priv_id" id="leave_priv_id" class="form-select rounded-0" requried <?= isset($data['leave_priv_id']) ? "readonly" : "" ?>>
                                    <option disabled>Please Select Here</option>
                                    <?php foreach($lpl as $row): ?>
                                        <option value="<?= $row['leave_priv_id'] ?>" <?= isset($data['leave_priv_id']) && $data['leave_priv_id'] == $row['leave_priv_id'] ? "selected" : "" ?> data-available="<?= $row['available'] ?>"><?= $row['name'] ?></option>
                                        <?php 
                                            if(isset($data['leave_priv_id']) && $data['leave_priv_id'] == $row['leave_priv_id']){
                                                $available = $row['available'] ;
                                            }
                                        ?>
                                    <?php endforeach; ?>
                                </select>
                                <div class=""><b>Available Credites:</b> <span id="avail_credits"><?= $available ?? 0 ?></span></div>
                            </div>
                            <div class="mb-3">
                                <label for="from" class="control-label">Date From</label>
                                <input type="date" id="from"  name="from" class="form-control rounded-0" value="<?= $data['from'] ?? "" ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="to" class="control-label">Date To</label>
                                <input type="date" id="to" name="to" class="form-control rounded-0" value="<?= $data['to'] ?? "" ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="type" class="form-label">Type</label>
                                <select name="type" id="type" class="form-select rounded-0" requried>
                                    <option value="1" <?= isset($data['type']) && $data['type'] == 1 ? "selected" : "" ?>>Whole Day</option>
                                    <option value="2" <?= isset($data['type']) && $data['type'] == 2 ? "selected" : "" ?>>Half Day</option>
                                </select>
                                <div class=""><b>Day(s):</b> <span id="days"><?= $days ?? 0 ?></span></div>
                            </div>
                            <div class="mb-3">
                                <label for="remarks" class="control-label">Reason</label>
                                <textarea type="remarks" id="remarks" name="remarks" class="form-control rounded-0"><?= $data['remarks'] ?? "" ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select rounded-0" requried>
                                    <option value="0" <?= isset($data['status']) && $data['status'] == 0 ? "selected" : "" ?>>Pending</option>
                                    <option value="1" <?= isset($data['status']) && $data['status'] == 1 ? "selected" : "" ?>>Approved</option>
                                    <option value="2" <?= isset($data['status']) && $data['status'] == 2 ? "selected" : "" ?>>Denied</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 d-flex w-100 justify-content-center align-items-center">
                            <button class="btn btn-sm btn-primary rounded-0 my-1">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        $('#employee_id').change(function(){
            start_loader()
            $.ajax({
                url:"Master.php?a=get_leave_privs",
                method:'POST',
                data:{employee_id: $('#employee_id').val()},
                dataType:'JSON',
                error:err=>{
                    alert('An error occurred!')
                    console.log(err)
                    end_loader()
                },
                success:function(resp){
                    if(resp.length > 0){
                        $('#leave_priv_id').html('')
                            var opt = $('<option>')
                            opt.attr('disabled',true)
                            opt.attr('selected',true)
                            opt.text('Please Select here.')
                            $('#leave_priv_id').append(opt)
                        resp.forEach(data => {
                            var opt = $('<option>')
                            opt.attr('data-available', data.available || 0)
                            opt.attr('value', data.leave_priv_id)
                            opt.text(data.name)
                            $('#leave_priv_id').append(opt)
                        })
                        if($('#other-dets').hasClass('d-none'))
                            $('#other-dets').removeClass('d-none');
                        $('#leave_priv_id').change(function(e){
                            e.preventDefault()
                            var leave_priv_id = $('#leave_priv_id').val()
                            var available = $(`#leave_priv_id option[value="${leave_priv_id}"]`).attr('data-available')
                            $('#avail_credits').text(available)
                        })
                    }else{
                        alert('An error occurred!')
                        console.log(resp)
                    }
                    end_loader()
                }
            })
        })
        $('#update-form').submit(function(e){
            e.preventDefault();
            $('.pop_msg').remove()
            start_loader();
            var available = $('#avail_credits').text()
            var days = $('#days').text()
            if(parseFloat(available) < parseFloat(days)){
                alert("Cannot proceed with the application because leave duration is longer than the employee's available credit(s).")
                end_loader();
                return false;
            }
            var _this = $(this)
            var _el = $('<div>')
                _el.addClass('pop_msg')
            _this.find('button').attr('disabled',true)
            _this.find('button[type="submit"]').text('Please wait...')
            $.ajax({
                url:'./Master.php?a=save_application',
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
                    end_loader()
                },
                success:function(resp){
                    if(resp.status == 'success'){
                            location.replace('./?page=applications');
                    }else{
                        _el.addClass('alert alert-danger')
                    }
                    _el.text(resp.msg)

                    _el.hide()
                    _this.prepend(_el)
                    _el.show('slow')
                    $('html, body').scrollTop(0)
                    _this.find('button').attr('disabled',false)
                    _this.find('button[type="submit"]').text('Save')
                    end_loader()
                }
            })
        })

        $('#from, #to, #type').change(function(){
            var from = $('#from').val()
            var to = $('#to').val()
            var type = $('#type').val()
            if(from != "" && to != ""){
                from = new Date(from);
                to = new Date(to);
                diff = (to - from)
                day = (diff / (1000 * 60 * 60 * 24) + 1) / type
                console.log(diff, day)
                $('#days').text(day)
            }
        })
    })
</script>