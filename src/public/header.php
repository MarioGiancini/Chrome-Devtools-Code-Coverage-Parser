<?php
/**
 * Basic Header Template to include at top of <body>
 */
global $results_page;
$results_page = $results_page ?? strpos($_SERVER['REQUEST_URI'], '/results') !== false;
?>
<header class="mb-3">
	<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
		<a class="navbar-brand" href="/">Code Coverage Parser</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown"
		        aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarNavDropdown">
			<ul class="navbar-nav">
				<li class="nav-item<?php echo !$results_page ? ' active' : ''; ?>">
					<a class="nav-link" href="/">
						Submit Coverage
						<?php echo !$results_page ? '<span class="sr-only">(current)</span>' : ''; ?>
					</a>
				</li>
				<li class="nav-item<?php echo $results_page ? ' active' : ''; ?>">
					<a class="nav-link" href="/results">
						Previous Results
						<?php echo $results_page ? '<span class="sr-only">(current)</span>' : ''; ?>
					</a>
				</li>
			</ul>
		</div>
	</nav>
</header>
