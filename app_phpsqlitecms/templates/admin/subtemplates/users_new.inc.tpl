<h1><a href="index.php"><?php echo $lang['administration']; ?></a> &raquo; <a href="index.php?mode=users"><?php echo $lang['users']; ?></a> &raquo; <?php echo $lang['create_user_account']; ?></h1>

<?php include('errors.inc.tpl'); ?>

<form action="index.php" method="post" />
 <div>
  <input type="hidden" name="mode" value="users" />
  <p><label for="name"><?php echo $lang['user_name_m']; ?></label><br />
  <input type="text" name="name" id="name" value="" size="25" /></p>
  <p><label for="pw"><?php echo $lang['pw_m']; ?></label><br />
  <input type="password" name="pw" id="pw" value="" size="25" /></p>
  <p><label for="pw_r"><?php echo $lang['pw_conf_m']; ?></label><br />
  <input type="password" name="pw_r" id="pw_r" value="" size="25" /></p>
  <p><input type="submit" name="new_user_submitted" value="<?php echo $lang['submit_button_ok']; ?>" /></p>
 </div>
</form>
