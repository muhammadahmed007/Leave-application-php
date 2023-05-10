<?php 
if(isset($_GET['id']) && $_GET['id'] > 0){
    $sql = "SELECT * FROM `user_list` where `user_id` = '{$_GET['id']}' ";
    $query = $conn->query($sql);
    $data = $query->fetchArray();
}
$_SESSION['formToken']['user-form'] = password_hash(uniqid(),PASSWORD_DEFAULT);
?>
<h1 class="text-center fw-bolder"><?= isset($data['user_id']) ? "Update User Details" : "Add New User" ?></h1>
<hr class="mx-auto opacity-100" style="width:50px;height:3px">
<div class="row">
    <div class="col-lg-5 col-md-6 col-sm-12 mx-auto py-3">
        <div class="card rounded-0 shadow">
            <div class="card-body rounded-0">
                <div class="contain-fluid">
                    <form action="" id="update-form">
                        <input type="hidden" name="formToken" value="<?= $_SESSION['formToken']['user-form'] ?>">
                        <input type="hidden" name="user_id" value="<?= $data['user_id'] ?? "" ?>">
                        <div class="mb-3">
                            <label for="fullname" class="control-label">Fullname</label>
                            <input type="text" id="fullname" autofocus name="fullname" class="form-control rounded-0" value="<?= $data['fullname'] ?? "" ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="control-label">Username</label>
                            <input type="text" id="username" name="username" class="form-control rounded-0" value="<?= $data['username'] ?? "" ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="control-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control rounded-0"<?= !isset($data['user_id']) ? "required" : "" ?>>
                        </div>
                        <div class="mb-3">
                        <label for="type" class="form-label">User Type</label>
                        <select name="type" id="type" class="form-select rounded-0" requried>
                            <option value="0" <?= isset($data['type']) && $data['type'] == 0 ? "selected" : "" ?>>Administrator</option>
                            <option value="1" <?= isset($data['type']) && $data['type'] == 1 ? "selected" : "" ?>>Staff</option>
                        </select>
                    </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select rounded-0" requried>
                                <option value="0" <?= isset($data['status']) && $data['status'] == 0 ? "selected" : "" ?>>Inactive</option>
                                <option value="1" <?= isset($data['status']) && $data['status'] == 1 ? "selected" : "" ?>>Active</option>
                            </select>
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
        $('#update-form').submit(function(e){
            e.preventDefault();
            $('.pop_msg').remove()
            start_loader();
            var _this = $(this)
            var _el = $('<div>')
                _el.addClass('pop_msg')
            _this.find('button').attr('disabled',true)
            _this.find('button[type="submit"]').text('Please wait...')
            $.ajax({
                url:'./LoginRegistration.php?a=save_user',
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
                        _el.addClass('alert alert-success')
                        setTimeout(() => {
                            location.replace('./?page=users');
                        }, 2000);
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
    })
</script>