<?php

namespace Helper;

class View extends \Template {

	/**
	 * Internal function used by make_clickable
	 * @param  array  $matches
	 * @return string
	 */
	protected function _make_url_clickable_cb($matches) {
		$ret = '';
		$url = $matches[2];

		if(empty($url))
			return $matches[0];
		// removed trailing [.,;:] from URL
		if(in_array(substr($url,-1),array('.',',',';',':')) === true) {
			$ret = substr($url,-1);
			$url = substr($url,0,strlen($url)-1);
		}
		return $matches[1] . "<a href=\"$url\" rel=\"nofollow\" target=\"_blank\">$url</a>".$ret;
	}

	/**
	 * Internal function used by make_clickable
	 * @param  array $m
	 * @return string
	 */
	protected function _make_web_ftp_clickable_cb($m) {
		$s = '';
		$d = $m[2];

		if (empty($d))
			return $m[0];

		// removed trailing [,;:] from URL
		if(in_array(substr($d,-1),array('.',',',';',':')) === true) {
			$s = substr($d,-1);
			$d = substr($d,0,strlen($d)-1);
		}
		return $m[1] . "<a href=\"http://$d\" rel=\"nofollow\" target=\"_blank\">$d</a>".$s;
	}

	/**
	 * Internal function used by make_clickable
	 * @param  array $m
	 * @return string
	 */
	protected function _make_email_clickable_cb($m) {
		$email = $m[2].'@'.$m[3];
		return $m[1]."<a href=\"mailto:$email\">$email</a>";
	}

	/**
	 * Converts recognized URLs and email addresses into HTML hyperlinks
	 * @param  string $s
	 * @return string
	 */
	public function make_clickable($s) {
		$s = ' '.$s;
		// in testing, using arrays here was found to be faster
		$s = preg_replace_callback('#([\s>])([\w]+?://[\w\\x80-\\xff\#!$%&~/.\-;:=,?@\[\]+]*)#is', array($this, '_make_url_clickable_cb'), $s);
		$s = preg_replace_callback('#([\s>])((www|ftp)\.[\w\\x80-\\xff\#!$%&~/.\-;:=,?@\[\]+]*)#is', array($this, '_make_web_ftp_clickable_cb'), $s);
		$s = preg_replace_callback('#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', array($this, '_make_email_clickable_cb'), $s);

		// this one is not in an array because we need it to run last, for cleanup of accidental links within links
		$s = preg_replace("#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i", "$1$3</a>",$s);
		$s = trim($s);
		return $s;
	}


	/**
	 * Get a human-readable file size
	 * @param  int $filesize
	 * @return string
	 */
	public function formatFilesize($filesize) {
		if($filesize > 1073741824) {
			return round($filesize / 1073741824, 2) . " GB";
		} elseif($filesize > 1048576) {
			return round($filesize / 1048576, 2) . " MB";
		} elseif($filesize > 1024) {
			return round($filesize / 1024, 2) . " KB";
		} else {
			return $filesize . " bytes";
		}
	}


	/**
	 * Get a Gravatar URL from email address and size, uses global Gravatar configuration
	 * @param  string  $email
	 * @param  integer $size
	 * @return string
	 */
	function gravatar($email, $size = 80) {
		$f3 = \Base::instance();
		$rating = $f3->get("gravatar.rating") ? $f3->get("gravatar.rating") : "pg";
		$default = $f3->get("gravatar.default") ? $f3->get("gravatar.default") : "mm";
		return "//gravatar.com/avatar/" . md5(strtolower($email)) .
				"?s=" . intval($size) .
				"&d=" . urlencode($default) .
				"&r=" . urlencode($rating);
	}

	/**
	 * Convert a UTC timestamp to local time
	 * @param  int $timestamp
	 * @return int
	 */
	function utc2local($timestamp = null) {
		if(!$timestamp) {
			$timestamp = time();
		}

		$f3 = \Base::instance();

		if($f3->exists("site.timeoffset")) {
			$offset = $f3->get("site.timeoffset");
		} else {
			$tz = $f3->get("site.timezone");
			$dtzLocal = new \DateTimeZone($tz);
			$dtLocal = new \DateTime("now", $dtzLocal);
			$offset = $dtzLocal->getOffset($dtLocal);
			$f3->set("site.timeoffset", $offset);
		}

		return $timestamp + $offset;
	}

}
