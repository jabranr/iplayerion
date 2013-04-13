	<?php

$data;
	if ( isset($_GET['q']) && $_GET['q'] )	{
		$endpoint = 'http://www.bbc.co.uk/iplayer/ion/searchextended/';
		$data = json_decode(file_get_contents($endpoint . 'search_availability/iplayer/service_type/radio/format/json/q/' . htmlentities($_GET['q'])));

		$pagination = $data->pagination;
		$media = $data->blocklist;
	}

	?>

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
		
		<?php if ( isset( $data ) && ( $data->count ) )	: ?>

		<div class="results">
			<?php

			echo '<h3>Search results for "' . htmlentities($_GET['q']) .'"</h3>';

			foreach ($media as $brand) {
			echo '<ul id="brand-' . $brand->id . '">';
				if ($brand->brand_title)
					echo '<li class="brand-title"><h3><a title="Listen to this series" href="//bbc.co.uk' . $brand->my_series_url . '">' . $brand->brand_title . '</a></h3></li>';
				if ($brand->original_title)
					echo '<li class="brand-episode-title">Available episode: <a title="Listen to this episode" href="//bbc.co.uk' . $brand->my_url . '">' . $brand->original_title . '</a></li>';
				echo '<li class="brand-synopsis">' . $brand->synopsis . '</li>';

				if ( $brand->duration < 3540 )	{
					$duration = gmdate('i', $brand->duration) . ' Minutes';
				}
				else 	{
					$mins = (gmdate('i', $brand->duration) % 60 == 0) ? '' : gmdate('i', $brand->duration) . ' Minutes';
					$duration = gmdate('G', $brand->duration) . ' Hours ' . $mins;
				}

				echo '<li class="brand-duration">' . $brand->masterbrand_title . ' &ndash; ' . $duration . '</li>';
				echo '<li class="brand-until-time">' . ' Available until: ' . gmdate('jS F Y', strtotime($brand->available_until)) . '</li>';
					foreach ($brand->categories as $category) {
						echo '<li class="brand-categories">' . $category->title . '</li>';
					}
			echo '</ul>';
			}


			?>
		</div>

		<?php elseif ((isset($data) && !$data->count)) : ?>
		
		<p>
			<strong>Sorry, there are no episodes available for your searched brand. Please try later.</strong>
		</p>

		<?php endif; ?>

	</div>

</body>
</html>
