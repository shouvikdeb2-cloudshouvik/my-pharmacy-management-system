<?php
/**
 * Pharmacy ERP - Unified User Management
 * Manages: Admin/Staff users, Supplier portal users, Patient/Customer accounts
 * Developed by Shouvik
 */
if (!defined('ROOT')) define('ROOT', dirname(dirname(dirname(__FILE__))));
require_once ROOT.'/config/app.php';
require_once ROOT.'/views/layout_admin.php';
requireAdmin();

$errors = [];

/* ══════════════════════════════════════════
   STAFF / ADMIN USERS
══════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $act = post('action');

    // ── Add/Edit staff user ──────────────────
    if (in_array($act, ['add_staff','edit_staff'])) {
        $id    = postInt('id');
        $name  = post('name');
        $uname = post('username');
        $email = post('email');
        $pw    = post('password');
        $role  = post('role','staff');

        if (!$name)  $errors[] = 'Name required.';
        if (!$email) $errors[] = 'Email required.';
        if ($act === 'add_staff' && !$pw) $errors[] = 'Password required.';
        if ($pw && strlen($pw) < 6) $errors[] = 'Password min 6 characters.';
        if ($email && $db->findOne('users', fn($u) => strtolower($u['email']??'') === strtolower($email) && ($u['id']??0) != $id))
            $errors[] = 'Email already in use.';

        if (empty($errors)) {
            $d = ['name'=>$name,'username'=>$uname,'email'=>$email,'role'=>$role];
            if ($pw) $d['password'] = password_hash($pw, PASSWORD_BCRYPT);
            if ($act === 'edit_staff' && $id) {
                $d['updated_at'] = date('Y-m-d H:i:s');
                // Prevent demoting last admin
                if ($role !== 'admin') {
                    $adminCount = $db->count('users', fn($u) => ($u['role']??'') === 'admin' && ($u['id']??0) != $id && ($u['active']??true));
                    if ($adminCount < 1) { $errors[] = 'Cannot change role — must have at least one admin.'; goto render; }
                }
                $db->update('users', fn($u) => $u['id'] === $id, $d);
                setFlash('success', 'User updated.');
            } else {
                $d['active'] = 1; $d['created_at'] = date('Y-m-d H:i:s');
                $db->insert('users', $d);

                // ── Send credentials email to new staff member ──
                if (!empty($email) && $pw) {
                    $cfg = $db->findOne('settings', fn($x) => true) ?? [];
                    $loginUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on'?'https':'http').'://'.$_SERVER['HTTP_HOST'].str_replace('index.php','',($_SERVER['SCRIPT_NAME']??'/'));
                    $subject = storeName() . ' — Your Staff Account Credentials';
                    $body  = "Hello {$name},\n\n";
                    $body .= "Your staff account has been created at " . storeName() . ".\n\n";
                    $body .= "Login Details:\n";
                    $body .= "  URL      : {$loginUrl}\n";
                    $body .= "  Username : {$uname}\n";
                    $body .= "  Password : {$pw}\n";
                    $body .= "  Role     : " . ucfirst($role) . "\n\n";
                    $body .= "Please log in and change your password from Settings.\n\n";
                    $body .= "— " . storeName() . " Admin Team";
                    try {
                        $htmlBody = mailTemplate("Your Staff Account — " . storeName(),
                            "<p>Hello <strong>" . htmlspecialchars($name) . "</strong>,</p>" .
                            "<p>Your staff account has been created at <strong>" . storeName() . "</strong>.</p>" .
                            "<table style='border-collapse:collapse;margin:16px 0'>" .
                            "<tr><td style='padding:6px 14px 6px 0;color:#666;font-size:.88rem'>URL</td><td style='padding:6px 0;font-weight:600'>" . htmlspecialchars($loginUrl) . "</td></tr>" .
                            "<tr><td style='padding:6px 14px 6px 0;color:#666;font-size:.88rem'>Username</td><td style='padding:6px 0;font-weight:600;font-family:monospace'>" . htmlspecialchars($uname) . "</td></tr>" .
                            "<tr><td style='padding:6px 14px 6px 0;color:#666;font-size:.88rem'>Password</td><td style='padding:6px 0;font-weight:600;font-family:monospace'>" . htmlspecialchars($pw) . "</td></tr>" .
                            "<tr><td style='padding:6px 14px 6px 0;color:#666;font-size:.88rem'>Role</td><td style='padding:6px 0;font-weight:600'>" . htmlspecialchars(ucfirst($role)) . "</td></tr>" .
                            "</table>" .
                            "<p style='color:#666;font-size:.88rem'>Please log in and change your password from Settings immediately.</p>"
                        );
                        $ok = sendMail($email, $subject, $htmlBody);
                        if ($ok) setFlash('success', "User added. Credentials sent to {$email}.");
                        else setFlash('success', "User added. (Email could not be sent — check SMTP settings.)");
                    } catch (Exception $e) {
                        setFlash('success', "User added. (Email not sent: " . $e->getMessage() . ")");
                    }
                } else {
                    setFlash('success', 'User added.');
                }
            }
            header('Location: index.php?p=users'); exit;
        }
    }

}

// ── GET actions ──────────────────────────────
// Toggle staff active
if (get('action') === 'toggle_staff' && getInt('id')) {
    $uid = getInt('id');
    if ($uid !== (int)$_SESSION['admin_id']) {
        $u = $db->findOne('users', fn($u) => $u['id'] === $uid);
        if ($u) {
            // Prevent disabling last admin
            if (($u['role']??'') === 'admin' && ($u['active']??true)) {
                $activeAdmins = $db->count('users', fn($u2) => ($u2['role']??'') === 'admin' && ($u2['active']??true));
                if ($activeAdmins <= 1) { setFlash('danger', 'Cannot disable the last admin account.'); header('Location: index.php?p=users'); exit; }
            }
            $db->update('users', fn($u2) => $u2['id'] === $uid, ['active' => !($u['active']??true)]);
            setFlash('success', 'Status updated.');
        }
    }
    header('Location: index.php?p=users'); exit;
}
// Delete staff (never delete yourself or last admin)
if (get('action') === 'del_staff' && getInt('id')) {
    $uid = getInt('id');
    if ($uid !== (int)$_SESSION['admin_id']) {
        $u = $db->findOne('users', fn($u) => $u['id'] === $uid);
        if ($u && ($u['role']??'') === 'admin') {
            $adminCount = $db->count('users', fn($u2) => ($u2['role']??'') === 'admin');
            if ($adminCount <= 1) { setFlash('danger', 'Cannot delete the last admin account.'); header('Location: index.php?p=users'); exit; }
        }
        $db->delete('users', fn($u) => $u['id'] === $uid);
        setFlash('success', 'User deleted.');
    }
    header('Location: index.php?p=users'); exit;
}

// Load data
$staffUsers = $db->table('users');

$editStaff = null;
if (get('action') === 'edit_staff' && getInt('id')) $editStaff = $db->findOne('users', fn($u) => $u['id'] === getInt('id'));

render:
adminHeader('User Management', 'users');
?>
<div class="page-hdr">
  <div><div class="page-title">User Management</div>
    <div class="page-sub"><?=count($staffUsers)?> staff / admin users</div>
  </div>
  <div class="page-actions">
    <button class="btn btn-primary" onclick="openModal('uModal')">+ Add Staff / Admin</button>
  </div>
</div>

<!-- ─── STAFF/ADMIN TABLE ─── -->
<div class="card"><div class="card-body p0">
  <div class="table-wrap"><table class="tbl">
    <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Last Login</th><th>Status</th><th style="width:180px"></th></tr></thead>
    <tbody>
    <?php foreach($staffUsers as $u): $isMe=($u['id']==(int)$_SESSION['admin_id']); $isAdmin=($u['role']??'')==='admin'; ?>
    <tr>
      <td class="fw-600"><?=e($u['name']??'')?><?php if($isMe):?> <span class="chip chip-blue" style="font-size:.6rem">You</span><?php endif;?></td>
      <td class="mono"><?=e($u['username']??'—')?></td>
      <td class="text-sm"><?=e($u['email']??'—')?></td>
      <td><span class="chip <?=$isAdmin?'chip-purple':'chip-blue'?>"><?=e($u['role']??'staff')?></span></td>
      <td class="text-sm text-muted"><?=dateTimeF($u['last_login']??'')?></td>
      <td><?=($u['active']??true)?'<span class="chip chip-green">Active</span>':'<span class="chip chip-red">Inactive</span>'?></td>
      <td>
        <div class="flex gap-1" style="flex-wrap:nowrap">
          <a href="index.php?p=users&action=edit_staff&id=<?=$u['id']?>" class="btn btn-ghost btn-sm">Edit</a>
          <?php if(!$isMe): ?>
          <a href="index.php?p=users&action=toggle_staff&id=<?=$u['id']?>" class="btn btn-ghost btn-sm" style="min-width:62px;text-align:center"><?=($u['active']??true)?'Disable':'Enable'?></a>
          <a href="index.php?p=users&action=del_staff&id=<?=$u['id']?>" class="btn btn-danger btn-sm" data-confirm="Delete &quot;<?=e($u['name'])?>?&quot;">Delete</a>
          <?php endif; ?>
        </div>
      </td>
    </tr>
    <?php endforeach;?>
    </tbody>
  </table></div>
</div></div>

<!-- ═══ STAFF MODAL ═══ -->
<div class="modal-overlay <?=($editStaff||!empty($errors))?'open':''?>" id="uModal">
  <div class="modal"><div class="modal-hdr">
    <span class="modal-title"><?=$editStaff?'Edit Staff User':'Add Staff / Admin'?></span>
    <button class="modal-x" onclick="closeModal('uModal')">&#x2715;</button>
  </div>
  <form method="POST"><div class="modal-body">
    <?=csrfField()?>
    <input type="hidden" name="action" value="<?=$editStaff?'edit_staff':'add_staff'?>">
    <?php if($editStaff):?><input type="hidden" name="id" value="<?=$editStaff['id']?>"><?php endif;?>
    <?php foreach($errors as $er):?><div class="alert alert-danger"><span class="alert-body"><?=e($er)?></span></div><?php endforeach;?>
    <div class="form-row-2">
      <div class="form-group"><label class="form-label">Full Name <span class="req">*</span></label><input class="form-control" type="text" name="name" value="<?=e($editStaff['name']??post('name'))?>" required autofocus></div>
      <div class="form-group"><label class="form-label">Username</label><input class="form-control" type="text" name="username" value="<?=e($editStaff['username']??post('username'))?>"></div>
    </div>
    <div class="form-row-2">
      <div class="form-group"><label class="form-label">Email <span class="req">*</span></label><input class="form-control" type="email" name="email" value="<?=e($editStaff['email']??post('email'))?>" required></div>
      <div class="form-group"><label class="form-label">Role</label>
        <select class="form-control" name="role">
          <option value="staff" <?=($editStaff['role']??'staff')==='staff'?'selected':''?>>Staff &mdash; Limited access</option>
          <option value="admin" <?=($editStaff['role']??'')==='admin'?'selected':''?>>Admin &mdash; Full access</option>
        </select>
      </div>
    </div>
    <div class="form-group"><label class="form-label">Password <?=$editStaff?'(leave blank to keep)':'<span class="req">*</span>'?></label>
      <input class="form-control" type="password" name="password" placeholder="<?=$editStaff?'Leave blank to keep current':'Min 6 characters'?>" <?=$editStaff?'':'required'?>>
    </div>
    <?php if(!$editStaff):?><div class="alert alert-info"><span class="alert-body"><strong>Staff</strong> can access: Sales, Batches, Medicines, Customers, Expiry &amp; Returns.<br><strong>Admin</strong> has full access to all sections.</span></div><?php endif;?>
  </div>
  <div class="modal-foot">
    <button type="button" class="btn btn-ghost" onclick="closeModal('uModal')">Cancel</button>
    <button type="submit" class="btn btn-primary"><?=$editStaff?'Save Changes':'Add User'?></button>
  </div>
  </form></div>
</div>

<?php if($editStaff):?><script>openModal('uModal');</script><?php endif;?>
<?php adminFooter();?>
