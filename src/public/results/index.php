<?php
$domains = array_filter(glob('*'), 'is_dir');
$code_parsings = [];
if ($domains) {
	foreach ($domains as $domain) {
		$checks = array_filter(glob("$domain/*"), 'is_dir');
		if ($checks) {
			foreach ($checks as $check) {
				$code_parsings[ $domain ][] = explode( '/', $check)[1];
			}
		}

	}
}



?>

<html lang="en">
<head>
	<title>Code Coverage Parsed Results</title>

	<?php require_once __DIR__ . '/../head.php'; ?>

</head>
<body>

<?php require_once __DIR__ . '/../header.php'; ?>

<div class="container">
	<h1 class="mb-3">Previous Code Coverage Parsings</h1>
	<div class="row">
	<?php
	if (count($code_parsings)) {
		foreach ( $code_parsings as $domain => $parsings ) { ?>
			<div class="col-md-6">
				<ul class="list-group">
					<li class="list-group-item list-group-item-primary"><?php echo $domain; ?></li>
					<?php foreach ( $parsings as $timestamp ) { ?>
						<li class="list-group-item">
							<a href="<?php echo "$domain/$timestamp"; ?>">
								<time><?php echo date( 'r', (int) $timestamp ) ?></time>
							</a>
						</li>
					<?php } ?>
				</ul>
			</div>
			<?php
		} // end foreach $code_parsings
	} else { ?>
		<div class="col text-center">
			<p class="my-3">
				<i class="fas fa-frown fa-7x text-warning"></i>
			</p>
			<p class="my-5">
				Sad day... you didn't parse any Code Coverage yet..
			</p>
		</div>
	<?php } ?>
	</div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>

</body>
</html>
