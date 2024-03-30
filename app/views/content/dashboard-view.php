<div class="container is-fluid">
	<h1 class="title">Home</h1>
	<div class="columns is-flex is-justify-content-center">
		<figure class="image is-128x128">
			<?php
			if (is_file("./app/views/photos/" . $_SESSION['photo'])) {
				echo '<img class="is-rounded" src="' . APP_URL . 'app/views/photos/' . $_SESSION['photo'] . '">';
			} else {
				echo '<img class="is-rounded" src="' . APP_URL . 'app/views/photos/default.png">';
			}
			?>
		</figure>
	</div>
	<div class="columns is-flex is-justify-content-center">
		<h2 class="subtitle">¡Bienvenido <?php echo $_SESSION['name'] . " " . $_SESSION['lastName']; ?>!</h2>
	</div>
</div>>
</div>