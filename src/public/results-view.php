
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<title>Cover Coverage Parser Results</title>
	<?php require_once 'head.php'; ?>
</head>
<body>

<?php $results_page = true; require_once 'header.php'; ?>

<div class="container">
	<!-- INSERT TABLE -->
</div>

<?php require_once 'footer.php'; ?>

<script>
    $(document).ready(function () {
        $('#parsedResults').DataTable({
            columnDefs: [{
                orderable: false,
                targets: [1, 2]
            }]
        });
        $('.dataTables_length').addClass('bs-select');
    });
</script>

</body>
</html>
