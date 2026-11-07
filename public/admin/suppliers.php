<?php
if (!defined('ROOT')) define('ROOT', dirname(dirname(dirname(__FILE__))));
/**
 * Pharmacy ERP - Suppliers | Developed by Shouvik
 */
require_once ROOT.'/config/app.php'; require_once ROOT.'/views/layout_admin.php'; requireAdmin();
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
    verifyCsrf(); $act=post('action'); $id=postInt('id');
    $name=post('name'); $contact=post('contact'); $phone=post('phone'); $email=post('email'); $addr=post('address'); $gst=post('gst_no'); $dl=post('dl_no');
    if(!$name)$errors[]='Name required.'; if(!$phone)$errors[]='Phone required.';
    if(empty($errors)){
        $d=['name'=>$name,'contact'=>$contact,'phone'=>$phone,'email'=>$email,'address'=>$addr,'gst_no'=>$gst,'dl_no'=>$dl,'updated_at'=>date('Y-m-d H:i:s')];
        if($act==='edit'&&$id){
            $db->update('suppliers',fn($s)=>$s['id']===$id,$d);
            setFlash('success','Updated.');
        } else {
            $d['created_at']=date('Y-m-d H:i:s');
            $sid=$db->insert('suppliers',$d);
            setFlash('success','"'.$name.'" added.');
        }
        header('Location: index.php?p=suppliers');exit;
    }
}
if(get('action')==='delete'&&getInt('id')){
    $sid=getInt('id');
    $db->delete('suppliers',fn($s)=>$s['id']===$sid);
    setFlash('success','Deleted.'); header('Location: index.php?p=suppliers');exit;
}
$sups=$db->table('suppliers'); usort($sups,fn($a,$b)=>strcasecmp($a['name'],$b['name']));
$edit=null; if(get('action')==='edit'&&getInt('id')) $edit=$db->findOne('suppliers',fn($s)=>$s['id']===getInt('id'));
adminHeader('Suppliers','suppliers');
?>
<div class="page-hdr">
  <div><div class="page-title">Suppliers</div><div class="page-sub"><?=count($sups)?> suppliers</div></div>
  <button class="btn btn-primary" onclick="openModal('sModal')">+ Add Supplier</button>
</div>
<div class="card"><div class="card-body p0">
  <?php if(empty($sups)):?><div class="empty-state"><p>No suppliers.</p></div><?php else:?>
  <div class="table-wrap"><table class="tbl">
    <thead><tr><th>#</th><th>Company</th><th>Contact</th><th>Phone</th><th>Email</th><th>GST</th><th>DL No.</th><th>Batches</th><th></th></tr></thead>
    <tbody>
    <?php foreach($sups as $s):
      $bc=$db->count('batches',fn($b)=>($b['supplier_id']??null)==$s['id']);
    ?>
    <tr>
      <td class="text-muted text-sm"><?=e($s['id'])?></td>
      <td><div class="fw-600"><?=e($s['name'])?></div><?php if(!empty($s['address'])):?><div class="text-xs text-muted"><?=e($s['address'])?></div><?php endif;?></td>
      <td class="text-sm"><?=e($s['contact']??'—')?></td>
      <td><?=e($s['phone'])?></td>
      <td class="text-sm"><?=e($s['email']??'—')?></td>
      <td><code class="mono"><?=e($s['gst_no']??'—')?></code></td>
      <td class="text-sm"><?=e($s['dl_no']??'—')?></td>
      <td class="tc"><span class="chip chip-blue"><?=$bc?></span></td>
      <td><div class="flex gap-1">
        <a href="index.php?p=suppliers&action=edit&id=<?=$s['id']?>" class="btn btn-ghost btn-sm">Edit</a>
        <a href="index.php?p=suppliers&action=delete&id=<?=$s['id']?>" class="btn btn-danger btn-sm" data-confirm="Delete supplier?">Delete</a>
      </div></td>
    </tr>
    <?php endforeach;?>
    </tbody>
  </table></div>
  <?php endif;?>
</div></div>
<div class="modal-overlay <?=($edit||!empty($errors))?'open':''?>" id="sModal">
  <div class="modal"><div class="modal-hdr"><span class="modal-title"><?=$edit?'Edit':'Add'?> Supplier</span><button class="modal-x" onclick="closeModal('sModal')">x</button></div>
    <form method="POST"><div class="modal-body">
      <?=csrfField()?><input type="hidden" name="action" value="<?=$edit?'edit':'add'?>">
      <?php if($edit):?><input type="hidden" name="id" value="<?=$edit['id']?>"><?php endif;?>
      <?php foreach($errors as $er):?><div class="alert alert-danger"><span class="alert-body"><?=e($er)?></span></div><?php endforeach;?>
      <div class="form-row-2">
        <div class="form-group"><label class="form-label">Company Name <span class="req">*</span></label><input class="form-control" type="text" name="name" value="<?=e($edit['name']??post('name'))?>" required autofocus></div>
        <div class="form-group"><label class="form-label">Contact Person</label><input class="form-control" type="text" name="contact" value="<?=e($edit['contact']??post('contact'))?>"></div>
      </div>
      <div class="form-row-2">
        <div class="form-group"><label class="form-label">Phone <span class="req">*</span></label><input class="form-control" type="tel" name="phone" value="<?=e($edit['phone']??post('phone'))?>" required></div>
        <div class="form-group"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="<?=e($edit['email']??post('email'))?>"></div>
      </div>
      <div class="form-row-2">
        <div class="form-group"><label class="form-label">GST Number</label><input class="form-control" type="text" name="gst_no" value="<?=e($edit['gst_no']??post('gst_no'))?>" placeholder="15-digit GSTIN"></div>
        <div class="form-group"><label class="form-label">Drug Licence (DL) Number</label><input class="form-control" type="text" name="dl_no" value="<?=e($edit['dl_no']??post('dl_no'))?>" placeholder="e.g. MH-XX-12345"><div class="form-hint">Not required but recommended for invoices.</div></div>
      </div>
      <div class="form-group"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2"><?=e($edit['address']??post('address'))?></textarea></div>
    </div>
    <div class="modal-foot"><button type="button" class="btn btn-ghost" onclick="closeModal('sModal')">Cancel</button><button type="submit" class="btn btn-primary"><?=$edit?'Save':'Add'?></button></div>
    </form></div>
</div>
<?php if($edit):?><script>openModal('sModal');</script><?php endif;?>

<?php adminFooter();?>
