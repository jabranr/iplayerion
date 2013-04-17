<!doctype HTML>
<html>
<head>
	<meta charset="utf-8">
		<title>RMUSIC-767</title>
	<link href="../src/main.css" rel="stylesheet">
</head>
<body>

	<div class="wrapper">

		<form class="scenarios" name="scenario_one" method="GET" action="<?php echo htmlentities($_SERVER['PHP_SELF']); if (isset($_SERVER['QUERY_STRING'])) echo '?' . $_SERVER['QUERY_STRING']; ?>" entype="text/plain">
			<label for="q">Search episode:</label>
			<input placeholder="Type a brand name.." type="text" name="q" id="q" size="50" value="<?php if(isset($_GET['q']) && $_GET['q']) echo htmlentities($_GET['q']); ?>">
			<input type="submit" value="Search" class="bttn">
		</form>
		
		<?php

		if (isset($_GET['q']) && $_GET['q']) :

			require_once('../src/api.php');
			$bbcapi->query( htmlentities($_GET['q']) );
			$data = $bbcapi->get_data();
			$media = $data->media;
		?>

		<div class="results">

			<?php

			echo '<h3>Search results for "' . htmlentities($_GET['q']) .'"</h3>';

			foreach ($media as $brand) :

			?>

			<div id="brand-'<? echo $brand->id; ?>" class="brand">
				<?php if ($brand->brand_title) : ?>
					<div class="brand-title">
						<h3 class="brand-title">
							<?php echo $brand->brand_title; ?>
						</h3>
					</div>
				<?php endif; ?>

				<?php if ($brand->original_title) : ?>
					<div class="brand-episode-title">
						Available episode: 
						<a title="Listen to this episode" href="<?php echo $data->websiteurl . $brand->my_url; ?>">
							<?php echo $brand->original_title; ?>
						</a>
					</div>
				<?php endif; ?>


				<?php if ($brand->synopsis) : ?>
					<div class="brand-synopsis">
						<?php
						$img = $brand->my_image_base_url ? $bbcapi->get_image_uri($brand->my_image_base_url, $brand->id) : ''; 
						if ($img)
							echo '<img src="' . $img . '" class="thumbnail" alt="' . $brand->original_title . '">';
						echo $brand->synopsis;
						?>
					</div>
				<?php 

				endif;

				if ( $brand->duration < 3540 ) {
					$duration = gmdate('i', $brand->duration) . ' Minutes';
				}
				else 	{
					$mins = (gmdate('i', $brand->duration) % 60 == 0) ? '' : gmdate('i', $brand->duration) . ' Minutes';
					$duration = gmdate('G', $brand->duration) . ' Hours ' . $mins;
				}
				
				?>
				
				<div class="brand-duration">
					<?php echo $brand->masterbrand_title ?> &ndash; <?php echo $duration; ?>
				</div>
				
				<div class="brand-until-time">
					Available until: <?php echo gmdate('jS F Y', strtotime($brand->available_until)); ?>
				</div>

				<?php
					foreach ($brand->categories as $category) {
						echo '<div class="brand-categories">' . $category->title . '</div>';
					}
				?>
			</div>
			<?php endforeach; ?>
		</div>

		<?php elseif ( isset($data) && !$data->count ) : ?>
		
		<p class="error results">
			<strong>Sorry, there are no episodes available for your searched brand. Please try later.</strong>
		</p>

			<?php elseif ( array_key_exists('q', $_GET) && empty($_GET['q']) ) : ?>
		
		<p class="error results">
			<strong>Type a keyword for search results.</strong>
		</p>

		<?php endif; ?>

	</div>

</body>
</html>
