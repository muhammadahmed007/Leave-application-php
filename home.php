<h1 class="text-center fw-bolder">Welcome to Leave Application System</h1>
<hr class="mx-auto opacity-100" style="width:50px;height:3px">
<?php if($_SESSION['type'] != 0): ?>
<?php 
include("Master.php");  
?>
<div class="row">
    <div class="col-lg-5 col-md-6 col-sm-12 mx-auto py-3">
        <div class="card rounded-0 shadow">
            <div class="card-body rounded-0">
                <div class="contain-fluid">
                    <div class="dash-box">
                        <div class="dash-box-title">Total Pending Applications</div>
                        <div class="dash-box-icon"><span class="material-symbols-outlined">pending</span></div>
                        <div class="dash-box-text"><?= number_format($master->total_pending()) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
