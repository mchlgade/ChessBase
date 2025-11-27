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

function CBdisplayboard($position, $boardsize, $reversed, $dark, $lite) {
	$board = CBparse_fen($position);
	$pieces = CBload_pieces($board);
	$img = CBcreate_image($boardsize);

	CBdraw_board($img, $dark, $lite);
	CBadd_pieces($img, $board, $reversed, $pieces);
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
    $size = imagesx($img)/8;
    for ($row=0; $row<8; $row++) {
        for ($col=0; $col<8; $col++) {
            $hex   = (($row + $col) % 2 === 1) ? $dark : $lite ;
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

function CBadd_pieces($img, $board, $reversed, $sprites)
{
    $sq_size = imagesx($img)/8;

    for ($i=0; $i<64; $i++) {
        $p = $board[$i];
        if ($p == ' ')
            continue;

        $col   = $i % 8;
        $row   = ($i - $col) / 8;

        if ($reversed) {
            $col = 7 - $col;
            $row = 7 - $row;
        }

        $x = $col * $sq_size;
        $y = $row * $sq_size;
        $piece = $sprites[$p];

        if (!empty($piece)) {
            $p_size = imagesx($piece);
            imagecopyresampled($img, $piece, $x, $y, 0, 0, $sq_size, $sq_size, $p_size, $p_size);
        }
    }
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

