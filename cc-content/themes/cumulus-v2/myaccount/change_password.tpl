<?php View::SetLayout('myaccount'); ?>

<h1><?=Language::GetText('change_password_header')?></h1>

<?php if ($message): ?>
    <div class="message <?=$message_type?>"><?=$message?></div>
<?php endif; ?>


<div class="form">
    <p><?=Language::GetText('change_password_text')?></p>
    <form action="<?=HOST?>/myaccount/change-password/" method="post">
        <label class="<?=(isset($errors['password']) || isset($errors['match'])) ? 'error' : ''?>"><?=Language::GetText('new_password')?>:</label>
        <input class="text" type="password" name="password" />

        <label class="<?=(isset($errors['confirm_password']) || isset($errors['match'])) ? 'error' : ''?>"><?=Language::GetText('confirm_password')?>:</label>
        <input class="text" type="password" name="confirm_password" />

        <input type="hidden" name="submitted" value="TRUE" />
        <input class="button" type="submit" name="button" value="<?=Language::GetText('change_password_button')?>" />
    </form>
</div>