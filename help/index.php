<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html
	xmlns="http://www.w3.org/1999/xhtml">
<!-- InstanceBegin template="/Templates/template.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>User Guide</title>
<link href="style.css" rel="stylesheet" type="text/css" />

</head>
<body>

	<div class="container">
		<div class="sidebar1">
			<div class="top-bit">
				<img class="image-spacer" src="../../../themes/media/logo.png" width="65"
					height="79" alt="VALS semester of code" /> <span class="mini-title"><br />
					<br /> <br /> <br /> VALS<br /> Semester of Code&nbsp;</span> <br />
			</div>

			<?php include 'content.php';?>

		</div>
		<!-- end .sidebar1 -->
		<div class="content">
			<br />
			<?php include($file);?>
			<div style="text-align: center;">
				<?php 
				if ($prev >= 0){?>
				<button onclick="location.href='?id=<?php echo $prev;?>';">Prev</button>
				&nbsp;&nbsp;
				<?php }
				if ($next){?>
				<button onclick="location.href='?id=<?php echo $next;?>';">Next</button>
				<?php }?>
				<br/><br/>
			</div>
		</div>
	</div>
	<!-- end .container -->
</body>
</html>
