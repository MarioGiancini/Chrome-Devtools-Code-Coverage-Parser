<?php

/**
 * Minify a css file or string
 *
 * @param $css
 *
 * @return string
 */
function minify_css( $css ) {
	$css = preg_replace( '#\s+#', ' ', $css );
	$css = preg_replace( '#/\*.*?\*/#s', '', $css );
	$css = str_replace( '; ', ';', $css );
	$css = str_replace( ': ', ':', $css );
	$css = str_replace( ' {', '{', $css );
	$css = str_replace( '{ ', '{', $css );
	$css = str_replace( ', ', ',', $css );
	$css = str_replace( '} ', '}', $css );
	$css = str_replace( ';}', '}', $css );

	return trim( $css );
}

if ( isset( $_POST["submit"] ) ) {

	if ( isset( $_FILES["json"] ) ) {
		// Setup allowed extensions based on request
		$extension_types = [ 'css', 'js' ];
		$extensions      = isset( $_POST['extension'] ) && in_array( $_POST['extension'], $extension_types ) ? $_POST['extension'] : [ 'css' ];

		// Stop if there was an error uploading the file
		if ( $_FILES['json']['error'] > 0 ) {
			die( 'Error getting file. Return Code: ' . $_FILES['json']['error'] . '<a href="/">Try Again?' );
		} else {
			// Get file details and setup a directory for put results
			$domain   = $_POST['domain'];
			$dir_name = "results/$domain/" . time();
			$dir_name_minified = "$dir_name/minified";
			$tmp_name = $_FILES['json']['tmp_name'];
			$files    = []; // place holder to put parsed resources
			$json     = json_decode( file_get_contents( $tmp_name ), true );
			$all_css  = '';
			$totals   = [
				'full_size' => 0,
				'used_size' => 0,
				'used_minified_size' => 0
			];

			// Recursively make directories based on domain and time
			mkdir( $dir_name_minified, 0777, true );

			foreach ( $json as $resource ) {
				$path       = parse_url( $resource['url'], PHP_URL_PATH );
				$path_parts = pathinfo( $path );

				// Parsed resource must have a one of the required file extensions and
				if ( in_array( $path_parts['extension'], $extensions ) ) {
					$css = '';

					foreach ( $resource['ranges'] as $name => $value ) {
						$length = $value['end'] - $value['start'];
						$css    .= substr( $resource['text'], $value['start'], $length ) . PHP_EOL;
					}
					// Minify the used css
					$css_minified = minify_css( $css );
					// Append to combined css
					$all_css .= $css_minified;

					// Replace slash with underscore for new used css files
					$used_file_base          = ltrim(str_replace( '/', '_', $path_parts['dirname'] ), '-');
					$used_file_name          = "$dir_name/$used_file_base-{$path_parts['basename']}";
					$used_file_name_minified = "$dir_name_minified/$used_file_base-minified-{$path_parts['basename']}";

					file_put_contents( $used_file_name, $css, LOCK_EX );
					file_put_contents( $used_file_name_minified, $css_minified, LOCK_EX );

					$files[ $resource['url'] ] = [
						'base_name'          => $path_parts['basename'],
						'used_file'          => $used_file_name,
						'used_minified'      => $used_file_name_minified,
						'full_size'          => $totals['full_size'] += strlen( $resource['text'] ),
						'used_size'          => $totals['used_size'] += strlen( $css ),
						'used_minified_size' => $totals['used_minified_size'] += strlen( $css_minified )
					];

					// If we have a target resource to parse, break once it's found
					if ( $target_css && strpos( $resource['url'], $target_css ) ) {
						break;
					}
				}
			}

			// No show any results
			if ( count($files) ) {

				// Create a results view for the folder
				$table_headers = ['Original File', 'Used CSS File', 'Used Minified CSS File', 'Full Size', 'Used Size', 'Used Minified Size', 'Size Saved'];
				$dom = new DOMDocument();
				$table = $dom->createElement('table');
				$table->setAttribute('id', 'parsedResults');
				$table->setAttribute('class', 'table table-striped table-bordered table-sm');

				$thead = $dom->createElement('thead');

				foreach ($table_headers as $header) {
					$th = $dom->createElement('th');
					$th->appendChild($dom->createTextNode($header));
					$thead->appendChild($th);
				}

				$tbody = $dom->createElement('tbody');
				$total_size_saved = 0;
				foreach ($files as $file => $values) {
					$tr = $dom->createElement('tr');
					// original resource file name
					$file_name = $dom->createElement('td');
					$file_name_a = $dom->createElement('a');
					$file_name_a->setAttribute('href', $file);
					$file_name_a->setAttribute('target', '_blank');
					$file_name_a->appendChild($dom->createTextNode($values['base_name']));
					$file_name->appendChild($file_name_a);
					$tr->appendChild($file_name);
					// created used css file
					$used_file = $dom->createElement('td');
					$used_file->setAttribute('class', 'text-center');
					$used_file_a = $dom->createElement('a');
					$used_file_a->setAttribute('href', '/' . $values['used_file']);
					$used_file_a->setAttribute('target', '_blank');
					$used_file_a->setAttribute('class', 'btn btn-secondary');
					$used_file_a->appendChild($dom->createTextNode('View'));
					$used_file->appendChild($used_file_a);
					$tr->appendChild($used_file);
					// created used css minified file
					$used_minified = $dom->createElement('td');
					$used_minified->setAttribute('class', 'text-center');;
					$used_minified_a = $dom->createElement('a');
					$used_minified_a->setAttribute('href', '/' . $values['used_minified']);
					$used_minified_a->setAttribute('target', '_blank');
					$used_minified_a->setAttribute('class', 'btn btn-info');
					$used_minified_a->appendChild($dom->createTextNode('View'));
					$used_minified->appendChild($used_minified_a);
					$tr->appendChild($used_minified);
					// full size of original file
					$full_size = $dom->createElement('td');
					$full_size->appendChild($dom->createTextNode($values['full_size']));
					$tr->appendChild($full_size);
					// size of just the used css
					$used_size = $dom->createElement('td');
					$used_size->appendChild($dom->createTextNode($values['used_size']));
					$tr->appendChild($used_size);
					// size of just the used css minified
					$used_minified_size = $dom->createElement('td');
					$used_minified_size->appendChild($dom->createTextNode($values['used_minified_size']));
					$tr->appendChild($used_minified_size);
					// File size saving of minified used css
					$size_saved = (int) $values['full_size'] - (int) $values['used_minified_size'];
					$total_size_saved += $size_saved;
					$minified_difference = $dom->createElement('td');
					$minified_difference->appendChild($dom->createTextNode($size_saved));
					$tr->appendChild($minified_difference);
					// Append row
					$tbody->appendChild($tr);
				}

				$table_footers = ['Totals', '', '', $totals['full_size'], $totals['used_size'], $totals['used_minified_size'], $total_size_saved];
				$tfoot = $dom->createElement('tfoot');

				foreach ($table_footers as $footer) {
					$td = $dom->createElement('td');
					$td->appendChild($dom->createTextNode($footer));
					$tfoot->appendChild($td);
				}

				// if we have more than one file lets create the combined minified version
				$all_css_file = '';
				if (count($files) > 1 && isset($_POST['combine'])) {
					$all_css_file = $dir_name . '/all.min.css';
					file_put_contents($all_css_file, $all_css, LOCK_EX);

					$div = $dom->createElement('div');
					$div->setAttribute('class', 'my-4');
					$button = $dom->createElement('a');
					$button->setAttribute('href', "/$all_css_file");
					$button->setAttribute('target', '_blank');
					$button->setAttribute('class', 'btn btn-primary');
					$button->appendChild($dom->createTextNode('Download Combined CSS'));
					$div->appendChild($button);
					$dom->appendChild($div);
				}

				$table->appendChild($thead);
				$table->appendChild($tbody);
				$table->appendChild($tfoot);
				$dom->appendChild($table);

				// Pull in html template and append table
				ob_start();
				include 'results-view.php';
				$template = ob_get_clean();
				$template = str_replace('<!-- INSERT TABLE -->', $dom->saveHTML(), $template);

				// Now write the new file
				file_put_contents( $dir_name . '/index.html', $template, LOCK_EX );

				// All done!
				$results_view = "$dir_name/index.html";

				header('Location: /finished.php?results=' . urlencode($results_view));
				exit();

			} else {
				die( 'Hmm... looks like there was no results. <a href="/">Try Again</a>?' );
			}

		}
	} else {
		die( 'No json file given. <a href="/">Try Again</a>.' );
	}
}

?>

<html lang="en">
<head>
	<title>Code Coverage Parser</title>

	<?php require_once 'head.php'; ?>

</head>
<body>

<?php require_once 'header.php'; ?>

<div class="container">

	<h1>Submit A Code Coverage Export</h1>
	<p class="mb-3">
		This will parse through all the used code and create new minified files for each resource.
		Optionally you can combined all used css together for one minified file.
	</p>

	<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" enctype="multipart/form-data" id="parser">
		<div class="form-group">
			<label for="domain">Domain Of Used Code Coverage</label>
			<input type="text" class="form-control" name="domain" id="domain" aria-describedby="domainHelp" placeholder="example.com"required>
			<small id="domainHelp" class="form-text text-muted">Where did you export Code Coverage From?</small>
			<div class="valid-feedback">
				Looks good!
			</div>
		</div>
		<div class="form-group">
			<label for="json">Coverage Export File</label>
			<input type="file" class="form-control-file" name="json" id="json" required>
		</div>
		<div class="form-check">
			<input type="checkbox" class="form-check-input" name="combine" id="combine" aria-describedby="domainHelp" checked>
			<label for="combine">Combined All Used CSS</label>
			<small id="combineHelp">(This will create one minified file from all used css.)</small>
		</div>
		<div class="form-group">
			<label for="target">Get Used From Specific File Only</label>
			<input type="text" class="form-control" name="target" id="target" aria-describedby="targetHelp" placeholder="/asset/css/some-styles.css">
			<small id="targetHelp" class="form-text text-muted">This will find the first instance of the matching file and stop.</small>
		</div>
		<button type="submit" class="btn btn-primary" name="submit" value="Submit" onclick="">
			<i class="fas fa-file-search"></i>
			Submit
		</button>
	</form>
</div>

<?php require_once 'footer.php'; ?>

</body>
</html>
