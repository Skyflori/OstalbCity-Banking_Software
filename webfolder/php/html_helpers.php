<?php 
	/* 
		HTML Shortcuts
	*/
	
	function html_err($dismissable, $msg){
		if($dismissable)
		return 
		'<div class="alert alert-danger alert-dismissible fade show" role="alert">
		  <strong>Fehler</strong> '.$msg.'
		  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>';
		else
			return '<div class="alert alert-danger" role="alert"><strong>Fehler</strong> '.$msg.'</div>';
	}

	function html_wrn($dismissable, $msg){
		if($dismissable)
		return 
		'<div class="alert alert-warning alert-dismissible fade show" role="alert">
		  <strong>Achtung</strong> '.$msg.'
		  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>';
		else
			return '<div class="alert alert-warning" role="alert"><strong>Achtung</strong> '.$msg.'</div>';
	}
?>