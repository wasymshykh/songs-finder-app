
<div class="container mt-4">
    <div class="row">
        <div class="col-lg-6 offset-lg-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-0 text-center font-weight-bold">Login</h5>
                </div>
                <div class="card-body row">
                
                    <div class="col-md-12">
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
                        <?php if(isset($error) && !empty($error)): ?>
                            <div class="alert alert-danger">
                                <?=$error?>
                            </div>
                        <?php endif; ?>

                        <form action="<?=href('login')?>" class="row" method="post">                            
                            <div class="col-md-12">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" placeholder="Email" class="form-control" value="<?=$_POST['email']??''?>" required>
                            </div>
                            <div class="col-md-12 mt-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" id="password" placeholder="Password" class="form-control" value="<?=$_POST['password']??''?>" required>
                            </div>

                            <div class="col-md-12 mt-4">
                                <div class="submit-loader-btn">
                                    <div class="loader-icon">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </div>
                                    <button type="submit" class="loader-btn btn btn-primary">Login</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-12 mt-2">
                        <hr>
                        <p>new to platform? <a href="<?=href('register')?>" class="text-white">create an account</a></p>
                        <p>forgot password? <a href="<?=href('forgot')?>" class="text-white">reset it</a></p>
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
            setTimeout(function () { t.submit(); }, <?=$settings->fetch('login_submit_timeout')?>);
        }
    });

</script>

