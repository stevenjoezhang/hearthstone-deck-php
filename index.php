<?php
	@$deckstring = $_GET["code"] ? str_replace(" ", "+", $_GET["code"]) : "AAEBAaoIBJsDoxTCrgLBiQMN/gXiDP8P5hausAKlvgL4vwL5vwKW7wKm7wKMhQPzigP2igMA";
	@$name = $_GET["name"] ? $_GET["name"] : "炉石传说卡组";
	@$lang = $_GET["lang"] ? $_GET["lang"] : "zhCN";
	
	function parse_deckstring($deckstring) {
		$binary = base64_decode($deckstring);
		$hex = bin2hex($binary);
		$arr = str_split($hex, 2);
		return array_map("str2int", $arr);
	}
	function str2int($str) {
		return hexdec($str);
	}
	function read_varint(&$data) {
		$shift = 0;
		$result = 0;
		while (true) {
			$c = array_shift($data);
			$result |= ($c & 0x7f) << $shift;
			$shift += 7;
			if (!($c & 0x80)) {
				break;
			}
		}
		return $result;
	}
	function parse_deck($data) {
		$reserve = read_varint($data);
		if ($reserve != 0) {
			printf("Invalid deckstring");
			die;
		}
		$version = read_varint($data);
		if ($version != 1) {
			printf("Unsupported deckstring version %s", $version);
			die;
		}
		$format = read_varint($data);
		$heroes = [];
		$num_heroes = read_varint($data);
		for ($i = 0; $i < $num_heroes; $i++) {
			$heroes[] = read_varint($data);
		}
		$cards = [];
		$num_cards_x1 = read_varint($data);
		for ($i = 0; $i < $num_cards_x1; $i++) {
			$card_id = read_varint($data);
			$cards[] = [$card_id, 1];
		}
		$num_cards_x2 = read_varint($data);
		for ($i = 0; $i < $num_cards_x2; $i++) {
			$card_id = read_varint($data);
			$cards[] = [$card_id, 2];
		}
		$num_cards_xn = read_varint($data);
		for ($i = 0; $i < $num_cards_xn; $i++) {
			$card_id = read_varint($data);
			$count = read_varint($data);
			$cards[] = [$card_id, $count];
		}
		return [$cards, $heroes, $format];
	}
	$deck = parse_deck(parse_deckstring($deckstring));
	$max_cost = 0;
	$mysqli = new mysqli("127.0.0.1", "username", "password", "hearthstone");
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	foreach ($deck[0] as &$card) {
		$dbfId = $card[0];
		if ($result = $mysqli->query("select * from `hearthstone` where `dbfId` = ".$dbfId)) {
			$row = $result->fetch_assoc();
			$card[] = $row;
			if ($row["cost"] > $max_cost) {
				$max_cost = $row["cost"];
			}
		}
		$result->close();
	}
	if ($result = $mysqli->query("select * from `hearthstone` where `dbfId` = ".$deck[1][0])) {
		$row = $result->fetch_assoc();
	}
	$result->close();
	$mysqli->close();
	$deck_cards_ordered = [];
	$rarity_tags = ["FREE", "COMMON", "RARE", "EPIC", "LEGENDARY"];
	for ($i = 0; $i <= $max_cost; $i++) {
		foreach($rarity_tags as $t) {
			foreach($deck[0] as $x) {
				if ($x[2]["cost"] == $i && $x[2]["rarity"] == $t) {
					$deck_cards_ordered[] = $x;
				}
			}
		}
	}
	$deck[0] = $deck_cards_ordered;
	#print_r($deck);

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="user-scalable=no, viewport-fit=cover"/>
<title><?php echo $name; ?></title>
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="apple-touch-icon" sizes="180x180" href="/images/favicon.jpg"/>
<link rel="stylesheet" href="css/style.css"/>
<link rel="stylesheet" href="css/theme.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/HubSpot/pace@0.7.8/themes/black/pace-theme-center-circle.css"/>
<script src="https://cdn.jsdelivr.net/gh/HubSpot/pace@0.7.8/pace.min.js"></script>  
</head>
<body>
<div id="main">
	<section class="section-decklist">
		<div class="hs-decklist-container">
			<div class="hs-decklist-hero">
				<div class="hs-decklist-hero-frame">
					<img src="img/CustomDeck_phone-Recovered.png" class="hero-frame"/>
					<img src="https://art.hearthstonejson.com/v1/512x/<?php echo $row["id"]; ?>.jpg" class="hero-image"/>
				</div>
				<div class="hs-decklist-title">
					<input id="deck-title-input" data-deckcode="<?php echo $deckstring; ?>" type="text" class="mdc-textfield__input" value="<?php echo $name; ?>" maxlength="30"/>
				</div>
			</div>
			<ul class="mdc-list mdc-list--two-line mdc-list--avatar-list hs-decklist">
			<?php
				foreach ($deck_cards_ordered as $card) {
			?>
				<li class="mdc-list-item deck-entry deck-entry-with<?php if ($card[1] == 1 && $card[2]["rarity"] != "LEGENDARY") { echo "out"; } ?>-amount">
					<div class="hs-tile-img">
						<img src="https://art.hearthstonejson.com/v1/tiles/<?php echo $card[2]["id"]; ?>.png">
					</div>
					<div class="hs-tile-shade"></div>
					<div class="hs-tile-borders"></div>
					<div class="hs-tile-mana"></div>
						<div class="hs-tile-info">
							<span class="hs-tile-info-left mdc-list-item__start-detail" role="presentation"><?php echo $card[2]["cost"]; ?></span>
							<span class="hs-tile-info-middle mdc-list-item__text">
								<span><?php echo $card[2][$lang]; ?></span>
							</span>
							<span class="hs-tile-info-right mdc-list-item__end-detail" aria-label="Amount" title="Amount" role="presentation">
							<?php
								if ($card[1] == 1 && $card[2]["rarity"] == "LEGENDARY") {
									echo '<img src="img/star.png"/>';
								}
								if ($card[1] != 1) {
									echo $card[1];
								}
							?>
							</span>
						</div>
					<div class="preview-card">
						<?php
							if ($lang == "zhCN") {
								$purify_name = str_replace([" ", "'", ",", "!", ":", "-"], "", $card[2]["enUS"]);
								echo '<img src="http://hearthstone.nos.netease.com/1/hscards/'.$card[2]["cardClass"].'__'.$card[2]["id"].'_zhCN_'.$purify_name.'.png"/>';
							}
							else echo '<img src="https://art.hearthstonejson.com/v1/render/latest/'.$lang.'/512x/'.$card[2]["id"].'.png"/>';
						?>
					</div>
				</li>
			<?php
				}
			?>
			</ul>
		</div>
	</section>
</div>
<script src="js/deck.js"></script>
</body>
</html>
