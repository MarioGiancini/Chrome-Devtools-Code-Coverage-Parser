<?php
$results_view = $_REQUEST['results'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Finish Parsing Code Coverage!</title>

	<?php require_once 'head.php'; ?>
</head>
<body>

<?php require_once 'header.php'; ?>

<div class="container">
	<h1>Fi Fi Fi File's Done </h1>
	<p><i class="fas fa-file fa-7x text-success"></i> &nbsp; <i class="fas fa-check fa-7x text-success"></i></p>
	<p>Hurray, you finished parsing!</p>
	<p><a class="btn btn-primary"   href="<?php echo $results_view ? $results_view : '/'; ?>">View Results</a></p>
	<audio src="audio/fifififiles-done.wav" id="filesDone" preload="auto" allow="autoplay"></audio>
</div>

<?php require_once 'footer.php'; ?>

<script type="text/javascript">
    const filesDone = document.getElementById('filesDone');
    // Override browsers disabling autoplay
    document.body.onclick = function() {
        filesDone.play();
    };
	setTimeout(function() {
        filesDone.click();
	}, 100);
</script>
</body>
</html>
