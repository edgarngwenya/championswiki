<?php /* Smarty version 2.6.19, created on 2013-07-03 15:52:10
         compiled from searchable_list.tpl */ ?>
<div>
	<ul>
	<?php $_from = $this->_tpl_vars['articles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i'] => $this->_tpl_vars['m']):
?>
		<li><a href="<?php echo $this->_tpl_vars['m']['page_link']; ?>
"><?php echo $this->_tpl_vars['m']['title']; ?>
</a></li>
	<?php endforeach; else: ?>
		No media articles were found.
	<?php endif; unset($_from); ?>
	</ul>
</div>