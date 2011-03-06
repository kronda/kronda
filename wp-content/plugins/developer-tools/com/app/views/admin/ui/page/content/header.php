<div class='wrap'>
	<div class='icon32' id='icon-tools'><br></div>
	<h2 id="developer-tools-nav"><?php 
	 foreach( $view['tabs'] as $title => $id )
	 {
	 	print '<a class="nav-tab';
    if( $id == $view['active'] ) print ' nav-tab-active';
		print ' '.$id;
		print '">'.$title.'</a>';
	 }
	?></h2>
	<div id="developer_tools">
		<div class="metabox-holder">
			<div class="postbox-container">
				<div class="meta-box-sortables">
