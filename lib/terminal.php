<?php

# Useful functions for PHP CLI.
class Terminal
{
	static protected $colors = array(
		'DARK_GRAY'    => "[1;30m",
		'LIGHT_RED'    => "[1;31m",
		'LIGHT_GREEN'  => "[1;32m",
		'YELLOW'       => "[1;33m",
		'LIGHT_BLUE'   => "[1;34m",
		'MAGENTA'      => "[1;35m",
		'LIGHT_CYAN'   => "[1;36m",
		'WHITE'        => "[1;37m",
		'NORMAL'       => "[0m",
		'BLACK'        => "[0;30m",
		'RED'          => "[0;31m",
		'GREEN'        => "[0;32m",
		'BROWN'        => "[0;33m",
		'BLUE'         => "[0;34m",
		'LIGHT_PURPLE' => "[0;35m",
		'CYAN'         => "[0;36m",
		'LIGHT_GRAY'   => "[0;37m",
		'BOLD'         => "[1m",
		'UNDERSCORE'   => "[4m",
		'REVERSE'      => "[7m",
	);
	
	# Colorizes text for PHP.
	static function colorize($text, $color='NORMAL')
	{
		$tag = isset(self::$colors[$color]) ? self::$colors[$color] : self::$colors['NORMAL'];
    return chr(27).$tag.$text.chr(27).self::$colors['NORMAL'];
	}
}

?>
