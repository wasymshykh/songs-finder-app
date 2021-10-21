
<?php if(isset($error) && !empty($error)): ?>
    <div class="alert alert-danger">
        <?=$error?>
    </div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach($errors as $error):?>
        <li><?=$error?></li>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php if(isset($success) && !empty($success)): ?>
    <div class="alert alert-success">
        <?=$success?>
    </div>
<?php endif; ?>

<div class="container mt-4">
    <div class="row">

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-0 text-center">Profile Settings</h5>
                </div>
                <div class="card-body row">
                
                    <div class="col-md-12">

                        <form action="<?=href('settings')?>" class="row" method="post">
                            <input type="hidden" name="fpr" value="">

                            <div class="col-md-12">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" name="name" id="name" placeholder="Name" class="form-control" value="<?=$_POST['name']??$logged_user['user_name']?>" required>
                            </div>
                            <div class="col-md-12 mt-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" placeholder="Email" class="form-control" value="<?=$_POST['email']??$logged_user['user_email']?>" required>
                            </div>

                            <div class="col-md-12 mt-4">
                                <div class="submit-loader-btn">
                                    <div class="loader-icon">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </div>
                                    <button type="submit" class="loader-btn btn btn-primary"><i class="fas fa-save"></i> Save</button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-4 offset-lg-1">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-0 text-center">Change Password</h5>
                </div>
                <div class="card-body row">
                
                    <div class="col-md-12">
                        <form action="<?=href('settings')?>" class="row" method="post">
                            <input type="hidden" name="fpa" value="">

                            <div class="col-md-12">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" id="password" placeholder="Password" class="form-control" value="<?=$_POST['password']??''?>" required>
                            </div>
                            <div class="col-md-12 mt-3">
                                <label for="repassword" class="form-label">Confirm Password</label>
                                <input type="password" name="repassword" id="repassword" placeholder="Retype Password" class="form-control" value="<?=$_POST['repassword']??''?>" required>
                            </div>

                            <div class="col-md-12 mt-4">
                                <div class="submit-loader-btn">
                                    <div class="loader-icon">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </div>
                                    <button type="submit" class="loader-btn btn btn-primary"><i class="fas fa-save"></i> Save</button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<script>

    const form_state = { submit: false };

    function active_loader (button) {
        button.blur().parent().addClass('active');
    }

    function disable_fields (form) {
        $(form).find('input').attr('readonly', 'true');
    }

    $('form').submit(function (e) {
        if (!form_state.submit) {
            e.preventDefault();
            
            var t = $(e.target);
            disable_fields (t)
            active_loader(t.find('button[type="submit"]'));
            
            form_state.submit = true;
            setTimeout(function () { t.submit(); }, <?=$settings->fetch('register_submit_timeout')?>);
        }
    });

</script>
