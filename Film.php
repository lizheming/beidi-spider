<?php 
include "PHPQuery.php";

class Film {
	public static $baseUrl = "http://202.204.105.243:8080/netService/";
	public static $elements = array("title", "finish", "album", "description", "urls", "date", "director", "actors", "rate", "category", "format", "views", "prev", "next");

	public function __construct($id) {
		$this->url = self::$baseUrl."filmShow.do?filmID=".$id;
		$this->parse();
	}

	public function parse() {
		phpQuery::newDocumentFile($this->url);

		$this->title = pq("table[bgcolor='#C0C0C0'] tr:first td")->text();
		$this->getFinish( $this->title );
		$this->album = self::$baseUrl.pq("td[rowspan=7] img")->attr("src");
		$this->description = $this->getDescrip();
		$this->urls = $this->getUrls();
		$this->getInfo();
		$this->navigator();
	}

	private function getFinish( $title ) {
		$this->finish = strpos($title, "更新") === false || strpos($title, "完结") !== false;
	}

	private function getInfo() {
		$elements = array_slice(self::$elements, 5, 7);
		foreach( pq("table[bgcolor='#C0C0C0'] tr:not(:first)") as $i => $row ) {
			if( $i===count($elements) ) break;
			foreach( pq("td", $row) as $j => $col ) {
				if( $j != 1 ) continue;
				$val = trim(pq($col)->text(), "  　\r\n\t");

				switch($i) {
					case 0: $this->{$elements[$i]} = strtotime($val); break;
					case 3: 
						preg_match("/([\d\.]*?)\//", $val, $rate);
						$this->{$elements[$i]} = isset($rate[1]) ? (float)$rate[1] : 0;
						break;
					case 6: $this->{$elements[$i]} = (int) $val; break;
					default: $this->{$elements[$i]} = $val; break;
				}
				break;
			}
		}
	}

	private function getDescrip() {
		$des = pq(".con")->html();
		$des = preg_replace("/\[注：建议用迅雷下载.+$/i", "", $des);
		$des = str_replace("剧情简介：", "", $des);
		$des = trim($des, "  　\t\r\n<br><b></b>");
		return strpos($des, "<br>") ? $des : preg_replace("/[\r\n]+?\s*[\r\n]+?/", "<br>", $des);		
	}

	private function getUrls() {
		$urls = array();
		foreach( pq(".jc a") as $url ) {
			$url = pq($url);
			$urls[ trim($url->text()) ] = $url->attr("href");
		}
		return $urls;
	}

	private function navigator() {
		$navigator = array_slice(self::$elements, -2);
		foreach(pq(".table-shangxia td:not(:last)") as $i => $row) {
			if( $i === count($navigator) ) break;

			$anchor = pq("a", $row);
			if( count($anchor) != 1 ) {
				$this->{ $navigator[$i] } = 0;
				continue;
			}
			preg_match( "/filmID=(\d+)/i", $anchor->attr("href"), $item );
			$this->{ $navigator[$i] } = (int) $item[1];
		}
	}

	public function __toString() {
		$film = array();
		foreach(self::$elements as $ele) $film[$ele] = $this->{$ele};
		return json_encode($film, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); 
	}
}