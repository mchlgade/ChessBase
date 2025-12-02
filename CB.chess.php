<?php
// ============================================================================
//  Chess functions for ChessBase
//  Copyright (C) Michael Gade
//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 3 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.

// ============================================================================

function CBdisplayboard($position, $boardsize, $flip, $dark, $lite) {
	$board = CBparse_fen($position);
	$pieces = CBload_pieces($board);
	$img = CBcreate_image($boardsize);

	CBdraw_board($img, $dark, $lite);
	CBadd_pieces($img, $board, $flip, $pieces);
	CBoutput_image($img);
	CBcleanup($img, $pieces);
}

// ===================================================================

function CBparse_fen($fen) {
    $pieces = 'kqrnbpKQRNBP';
    $digits = '12345678';
    $chars = str_split(explode(' ', $fen)[0]);
    $board = array_fill(0, 64, ' ');
    $row = 0;
    $col = 0;
    
    foreach($chars as $chr) {
        if ($row > 7)
            break;
        else
        if ($chr == '/' || $col > 7) {
            $row++;
            $col = 0;
        }
        elseif (strpos($digits, $chr) !== false)
            $col += intval($chr);
        elseif (strpos($pieces, $chr) !== false) {
            $board[$row*8+$col] = $chr;
            $col++;
        }
    }
    return $board;
}

// ===================================================================

function CBis_upper($str)
{
    return $str === strtoupper($str);
}

// ===================================================================

function CBget_piece($p)
{
    $name = (CBis_upper($p) ? 'w' : 'b') . strtolower($p);
    return "./img/" . "$name.png";
}

// ===================================================================

function CBload_pieces($board)
{
    $pieces = 'kqrnbpKQRNBP';
    $sprites = [];
    $chars = array_unique($board);
    
    foreach($chars as $chr) {
        if (strpos($pieces, $chr) !== false) {
            $file = CBget_piece($chr);
            $sprites[$chr] = imagecreatefrompng($file);
        }
    }
    return $sprites;
}

// ===================================================================

function CBcreate_image($size)
{
    if (!function_exists('imagecreatetruecolor')) return false;
    return imagecreatetruecolor($size, $size);
}

// ===================================================================
function CBmovepiece($from, $to) {
	global $currentposition;
	global $currentpgn;
	global $lastfrom;
	global $lastto;
	
	$move = $from . $to;
	$lastfrom = $from;
	$lastto = $to;

	$from = CBgetindex($from);
	$to = CBgetindex($to);
	
	if(($move == 'e1g1') && ($currentposition[$from] == 'K')) {
		CBmovepiece('h1','f1');
		$lastfrom = $move[0] . $move[1];
		$lastto = $move[2] . $move[3];
	}
	if(($move == 'e1c1') && ($currentposition[$from] == 'K')) {
		CBmovepiece('a1','d1');
		$lastfrom = $move[0] . $move[1];
		$lastto = $move[2] . $move[3];
	}
	if(($move == 'e8g8') && ($currentposition[$from] == 'k')) {
		CBmovepiece('h8','f8');
		$lastfrom = $move[0] . $move[1];
		$lastto = $move[2] . $move[3];
	}
	if(($move == 'e8c8') && ($currentposition[$from] == 'k')) {
		CBmovepiece('a8','d8');
		$lastfrom = $move[0] . $move[1];
		$lastto = $move[2] . $move[3];
	}
	
	$old = $currentposition[$to];
	
	$currentposition[$to] = $currentposition[$from];
	$currentposition[$from] = '1';
}

// ===================================================================
function CBgetindex($square) {
	switch($square) {
	case 'a8' : $result = 0; break;
	case 'b8' : $result = 1; break;
	case 'c8' : $result = 2; break;
	case 'd8' : $result = 3; break;
	case 'e8' : $result = 4; break;
	case 'f8' : $result = 5; break;
	case 'g8' : $result = 6; break;
	case 'h8' : $result = 7; break;

	case 'a7' : $result = 9; break;
	case 'b7' : $result = 10; break;
	case 'c7' : $result = 11; break;
	case 'd7' : $result = 12; break;
	case 'e7' : $result = 13; break;
	case 'f7' : $result = 14; break;
	case 'g7' : $result = 15; break;
	case 'h7' : $result = 16; break;

	case 'a6' : $result = 18; break;
	case 'b6' : $result = 19; break;
	case 'c6' : $result = 20; break;
	case 'd6' : $result = 21; break;
	case 'e6' : $result = 22; break;
	case 'f6' : $result = 23; break;
	case 'g6' : $result = 24; break;
	case 'h6' : $result = 25; break;

	case 'a5' : $result = 27; break;
	case 'b5' : $result = 28; break;
	case 'c5' : $result = 29; break;
	case 'd5' : $result = 30; break;
	case 'e5' : $result = 31; break;
	case 'f5' : $result = 32; break;
	case 'g5' : $result = 33; break;
	case 'h5' : $result = 34; break;

	case 'a4' : $result = 36; break;
	case 'b4' : $result = 37; break;
	case 'c4' : $result = 38; break;
	case 'd4' : $result = 39; break;
	case 'e4' : $result = 40; break;
	case 'f4' : $result = 41; break;
	case 'g4' : $result = 42; break;
	case 'h4' : $result = 43; break;

	case 'a3' : $result = 45; break;
	case 'b3' : $result = 46; break;
	case 'c3' : $result = 47; break;
	case 'd3' : $result = 48; break;
	case 'e3' : $result = 49; break;
	case 'f3' : $result = 50; break;
	case 'g3' : $result = 51; break;
	case 'h3' : $result = 52; break;

	case 'a2' : $result = 54; break;
	case 'b2' : $result = 55; break;
	case 'c2' : $result = 56; break;
	case 'd2' : $result = 57; break;
	case 'e2' : $result = 58; break;
	case 'f2' : $result = 59; break;
	case 'g2' : $result = 60; break;
	case 'h2' : $result = 61; break;

	case 'a1' : $result = 63; break;
	case 'b1' : $result = 64; break;
	case 'c1' : $result = 65; break;
	case 'd1' : $result = 66; break;
	case 'e1' : $result = 67; break;
	case 'f1' : $result = 68; break;
	case 'g1' : $result = 69; break;
	case 'h1' : $result = 70; break;
	}
	return $result;
}

// ===================================================================

function CBgetsquare($col, $row) {
	global $flip;
	$letter = $col;
	$number = $row;

	if ($flip) {
		$number = $number + 1; 
		switch($letter) {
			case '7' : $letter = 'a'; break;
			case '6' : $letter = 'b'; break;
			case '5' : $letter = 'c'; break;
			case '4' : $letter = 'd'; break;
			case '3' : $letter = 'e'; break;
			case '2' : $letter = 'f'; break;
			case '1' : $letter = 'g'; break;
			case '0' : $letter = 'h'; break;
		}
	} else {
		$number = 8 - $number;
		switch($letter) {
			case '0' : $letter = 'a'; break;
			case '1' : $letter = 'b'; break;
			case '2' : $letter = 'c'; break;
			case '3' : $letter = 'd'; break;
			case '4' : $letter = 'e'; break;
			case '5' : $letter = 'f'; break;
			case '6' : $letter = 'g'; break;
			case '7' : $letter = 'h'; break;
		}
	}
	return $letter . $number;
}

// ===================================================================

function CBgetpgn($moves) {
	global $currentgame;

}

// ===================================================================

function CBhex_rgb($clr)
{
    return array_map(
        function($c) {
            return hexdec(str_pad($c, 2, $c));
        },
        str_split(ltrim($clr, '#'),
        strlen($clr) > 4 ? 2 : 1));
}

// ===================================================================

function CBdraw_board($img, $dark, $lite)
{
	global $lastfrom;
	global $lastto;
	global $highlite;
	global $action;
	global $select;
	$size = imagesx($img)/8;
	
	for ($row=0; $row<8; $row++) {
		for ($col=0; $col<8; $col++) {
			$hex   = (($row + $col) % 2 === 1) ? $dark : $lite ;
            		
            		$this_sq = CBgetsquare($col, $row);
            		if($this_sq == $lastfrom) $hex = $highlite;
            		if($this_sq == $lastto) $hex = $highlite;
            		if($this_sq == $select) $hex = $action;
            		
            		$rgb   = CBhex_rgb($hex);
            $color = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);

            $x1 = $col * $size;
            $y1 = $row * $size;
            $x2 = $x1 + $size - 1;
            $y2 = $y1 + $size - 1;

            imagefilledrectangle($img, $x1, $y1, $x2, $y2, $color);
        }
    }
}

// ===================================================================

function CBadd_pieces($img, $board, $flip, $sprites)
{
    global $movecolour;
    global $step;
    global $maxstep;
    global $currentmap;
    global $map;
    global $select;
    
    $sq_size = imagesx($img)/8;

    for ($i=0; $i<64; $i++) {
        $p = $board[$i];
        if ($p == ' ')
            continue;

        $col   = $i % 8;
        $row   = ($i - $col) / 8;

        if ($flip) {
            $col = 7 - $col;
            $row = 7 - $row;
        }

        $x = $col * $sq_size;
        $y = $row * $sq_size;
        $piece = $sprites[$p];
		
	$this_sq = CBgetsquare($col, $row);
	$x1 = $x + $sq_size;
	$y1 = $y + $sq_size;
	
	if($this_sq == $select) {
		$map .=  '<b>'. $this_sq. '</b> : (' .$x. ',' .$y. '),(' .$x1. ',' .$y1. ')</br> ';
		if($flip) {
			$currentmap .= '<area shape="rect" coords="'.$x.','.$y.','.$x1.','.$y1.'" href="?function=games&flip=1&step='. $maxstep . '">';
		} else {
			$currentmap .= '<area shape="rect" coords="'.$x.','.$y.','.$x1.','.$y1.'" href="?function=games&flip=0&step='. $maxstep . '">';
		}	
	}
	
	
	
	if( (CBis_upper($p)) && (!$select) && ($step == $maxstep)) {
		if($movecolour == 'White') {
			$map .=  '<b>'. $this_sq. '</b> : (' .$x. ',' .$y. '),(' .$x1. ',' .$y1. ')</br> ';
			$currentmap .= '<area shape="rect" coords="'.$x.','.$y.','.$x1.','.$y1.'" href="?function=games&select='.$this_sq.'">';
		}
	} 
	if((!CBis_upper($p)) && (!$select) && ($step == $maxstep)) {
		if($movecolour == 'Black') {
			$map .=  '<b>'. $this_sq. '</b> : (' .$x. ',' .$y. '),(' .$x1. ',' .$y1. ') </br>';
			$currentmap .= '<area shape="rect" coords="'.$x.','.$y.','.$x1.','.$y1.'" href="?function=games&select='.$this_sq.'">';
		}
	} 
		
        if (!empty($piece)) {
            $p_size = imagesx($piece);
            imagecopyresampled($img, $piece, $x, $y, 0, 0, $sq_size, $sq_size, $p_size, $p_size);
        }
    }
    $currentmap .= '</map>';
}

// ===================================================================

function CBoutput_image($img)
{
	imagetruecolortopalette($img, false, 8);
	imagepng( $img, null, 9);
}

// ===================================================================

function CBcleanup($img, $sprites)
{
    imagedestroy($img);
    foreach($sprites as $sprite) {
        imagedestroy($sprite);
    }
}

// ===================================================================

