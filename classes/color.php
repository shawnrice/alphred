<?php

namespace Alphred\Color;

class Color {

}



  /**
   * Checks a file to see if it is a png.
   *
   * @since   Taurus 1
   *
   * @param   string  $image  file path to alleged image
   *
   * @return  bool            TRUE is a png, FALSE if not
   */
  public function validateImage( $image ) {
    if ( finfo_file( $this->mime, $image . '.png' ) == 'image/png' )
      return TRUE;
    return FALSE;

  }



  /*****************************************************************************
 * BEGIN COLOR FUNCTIONS
 ****************************************************************************/

  /**
   * Normalizes and validates a color and adds it to the color array
   *
   * @see     prepareIcon()
   * @see     checkHex()
   * @see     validateHex()
   * @see     normalizeHex()
   * @see     rgb()
   * @see     hsv()
   * @see     luminance()
   * @see     brightness()
   * @see     altered()
   * @see     hexToRgb()
   * @see     rgbToHex()
   * @see     rgbToHsv()
   * @see     hsvToRgb()
   * @see     alter()
   * @see     getLuminance()
   * @see     getBrightness()
   *
   * @since   Taurus 1
   *
   * @param   string  $color  a hex color
   *
   * @return  string          a hex normalized and validated hex color
   */
  public function color( $color ) {

    if ( ! in_array( $color, $this->colors ) ) {
      if ( ! $color = $this->checkHex( $color ) )
        return FALSE;
      $this->colors[ $color ][ 'hex' ] = $color;
    }
    return $color;

  }

  /**
   * Prepares the icon arguments for a proper query
   *
   * The color is first normalized. Then, if the `alter` variable
   * has not been set, then it just send the arguments back. Otherwise
   * a check is run to see if the theme background color is
   * the same as the proposed icon color. If not, then it sends back
   * the arguments. If so, then, if the `alter` variable is another
   * hex color, it returns that. If, instead, it is TRUE, then alters
   * the color accordingly so that the icon will best appear on
   * the background.
   *
   * @see     icon()
   * @see     color()
   * @see     checkHex()
   * @see     validateHex()
   * @see     normalizeHex()
   * @see     rgb()
   * @see     hsv()
   * @see     luminance()
   * @see     brightness()
   * @see     altered()
   * @see     hexToRgb()
   * @see     rgbToHex()
   * @see     rgbToHsv()
   * @see     hsvToRgb()
   * @see     alter()
   * @see     getLuminance()
   * @see     getBrightness()
   *
   * @since   Taurus 1
   *
   * @param   array  $args  an associative array of args passed to icon()
   *
   * @return  array         possible altered array of args to load an icon
   */
  public function prepareIcon( $args ) {

    $args[ 'color' ] = $this->color( $args[ 'color' ] );

    if ( $args[ 'alter' ] === FALSE )
      return $args;

    if ( $this->brightness( $args[ 'color' ] ) != $this->background )
      return $args;


    if ( ! is_bool( $args[ 'alter' ] ) ) {
      if ( ! $args[ 'color' ] = $this->color( $args[ 'alter' ] ) )
        $args[ 'color' ] = '000000';

      return $args;
    }
    $args[ 'color' ] = $this->altered( $args[ 'color' ] );
    return $args;

  }


/*****************************************************************************
 * BEGIN GETTER FUNCTIONS  (well, we set when not already set)
 ****************************************************************************/

  /**
   * Returns the RGB of a color, and it sets it if necessary
   *
   * @see     color()
   * @see     prepareIcon()
   * @see     checkHex()
   * @see     validateHex()
   * @see     normalizeHex()
   * @see     hsv()
   * @see     luminance()
   * @see     brightness()
   * @see     altered()
   * @see     hexToRgb()
   * @see     rgbToHex()
   * @see     rgbToHsv()
   * @see     hsvToRgb()
   * @see     alter()
   * @see     getLuminance()
   * @see     getBrightness()
   *
   * @since   Taurus 1
   *
   * @param   string  $color  hex color
   *
   * @return  array           associative array of RGB values
   */
  public function rgb( $color ) {

    if ( ! isset( $this->colors[ $color ][ 'rgb' ] ) ) {
      $this->colors[ $color ][ 'rgb' ] = $this->hexToRgb( $color );
    }
    return $this->colors[ $color ][ 'rgb' ];

  }



  /**
   * Returns the HSV of a color, and it sets it if necessary
   *
   * @link [https://en.wikipedia.org/wiki/HSL_and_HSV] [color forumulas]
   *
   * @see     color()
   * @see     prepareIcon()
   * @see     checkHex()
   * @see     validateHex()
   * @see     normalizeHex()
   * @see     rgb()
   * @see     luminance()
   * @see     brightness()
   * @see     altered()
   * @see     hexToRgb()
   * @see     rgbToHex()
   * @see     rgbToHsv()
   * @see     hsvToRgb()
   * @see     alter()
   * @see     getLuminance()
   * @see     getBrightness()
   *
   * @since   Taurus 1
   *
   * @param   string  $color  hex color
   *
   * @return  array          associative array of HSV values
   */
  public function hsv( $color ) {

    if ( ! isset( $this->colors[ $color ][ 'hsv' ] ) ) {
      if ( ! isset( $this->colors[ $color ][ 'rgb' ] ) ) {
        $this->rgb( $color );
      }
      $this->colors[ $color ][ 'hsv' ] = $this->rgbToHsv( $this->rgb( $color ) );
    }
    return $this->colors[ $color ][ 'hsv' ];

  }


  /**
   * Retrieves the altered color of the original
   *
   * @see     color()
   * @see     prepareIcon()
   * @see     checkHex()
   * @see     validateHex()
   * @see     normalizeHex()
   * @see     rgb()
   * @see     hsv()
   * @see     luminance()
   * @see     brightness()
   * @see     hexToRgb()
   * @see     rgbToHex()
   * @see     rgbToHsv()
   * @see     hsvToRgb()
   * @see     alter()
   * @see     getLuminance()
   * @see     getBrightness()
   *
   * @since   Taurus 1
   *
   * @param   string  $color  a hex color
   *
   * @return  string          a hex color (lighter or darker than the original)
   */
  public function altered( $color ) {

    if ( ! isset( $this->colors[ $color ][ 'altered' ] ) ) {
      if ( ! $this->colors[ $color ][ 'altered' ] = $this->cached( $color ) )
        $this->colors[ $color ][ 'altered' ] = $this->alter( $color );
    }
    return $this->colors[ $color ][ 'altered' ];

  }



  /**
   * Retrieves the luminance of a hex color
   *
   * @see     color()
   * @see     prepareIcon()
   * @see     checkHex()
   * @see     validateHex()
   * @see     normalizeHex()
   * @see     rgb()
   * @see     hsv()
   * @see     brightness()
   * @see     altered()
   * @see     hexToRgb()
   * @see     rgbToHex()
   * @see     rgbToHsv()
   * @see     hsvToRgb()
   * @see     alter()
   * @see     getLuminance()
   * @see     getBrightness()
   *
   * @since Taurus 1
   *
   * @param   string  $color  a hex color
   *
   * @return  float           the luminance between 0 and 1
   */
  public function luminance( $color ) {

    if ( ! isset( $this->colors[ $color ][ 'luminance' ] ) ) {
      $this->colors[ $color ][ 'luminance' ] = $this->getLuminance( $color );
    }
    return $this->colors[ $color ][ 'luminance' ];

  }


  /**
   * Queries whether and image is 'light' or 'dark'
   *
   * @see     color()
   * @see     prepareIcon()
   * @see     checkHex()
   * @see     validateHex()
   * @see     normalizeHex()
   * @see     rgb()
   * @see     hsv()
   * @see     luminance()
   * @see     altered()
   * @see     hexToRgb()
   * @see     rgbToHex()
   * @see     rgbToHsv()
   * @see     hsvToRgb()
   * @see     alter()
   * @see     getLuminance()
   * @see     getBrightness()
   *
   * @since   Taurus 1
   *
   * @param   string  $color  a hex color
   *
   * @return  string          'light' or 'dark'
   */
  private function brightness( $color ) {

    if ( ! isset( $this->colors[ $color ][ 'brightness' ] ) ) {
      $this->colors[ $color ][ 'brightness' ] = $this->getBrightness( $color );
    }
    return $this->colors[ $color ][ 'brightness' ];

  }


  /*****************************************************************************
 * BEGIN CONVERSION FUNCTIONS
 ****************************************************************************/

  /**
   * Converts a Hex color to an RGB Color
   *
   * @see     color()
   * @see     prepareicon()
   * @see     checkhex()
   * @see     validatehex()
   * @see     normalizehex()
   * @see     rgb()
   * @see     hsv()
   * @see     luminance()
   * @see     brightness()
   * @see     altered()
   * @see     rgbtohex()
   * @see     rgbtohsv()
   * @see     hsvtorgb()
   * @see     alter()
   * @see     getluminance()
   * @see     getBrightness()
   *
   * @since   Taurus 1
   *
   * @param   string  $color A hex color
   * @return  array          An array of RGB values
   */
  public function hexToRgb( $hex ) {

    $r = hexdec( substr( $hex, 0, 2 ) );
    $g = hexdec( substr( $hex, 2, 2 ) );
    $b = hexdec( substr( $hex, 4, 2 ) );
    return [ 'r' => $r, 'g' => $g, 'b' => $b ];

  }


  /**
   * Converts an RGB color to a Hex color
   *
   * @see     color()
   * @see     prepareIcon()
   * @see     checkHex()
   * @see     validateHex()
   * @see     normalizeHex()
   * @see     rgb()
   * @see     hsv()
   * @see     luminance()
   * @see     brightness()
   * @see     altered()
   * @see     hexToRgb()
   * @see     rgbToHsv()
   * @see     hsvToRgb()
   * @see     alter()
   * @see     getLuminance()
   * @see     getBrightness()
   *
   * @since   Taurus 1
   *
   * @param   array  $rgb an associative array of RGB values
   *
   * @return  string      a hex color
   */
  public function rgbToHex( $rgb ) {

    $hex .= str_pad( dechex( $rgb[ 'r' ] ), 2, '0', STR_PAD_LEFT );
    $hex .= str_pad( dechex( $rgb[ 'g' ] ), 2, '0', STR_PAD_LEFT );
    $hex .= str_pad( dechex( $rgb[ 'b' ] ), 2, '0', STR_PAD_LEFT );
    return $hex;

  }


  /**
   * Converts RGB color to HSV color
   *
   * @link [https://en.wikipedia.org/wiki/HSL_and_HSV] [color forumulas]
   *
   * @see     color()
   * @see     prepareIcon()
   * @see     checkHex()
   * @see     validateHex()
   * @see     normalizeHex()
   * @see     rgb()
   * @see     hsv()
   * @see     luminance()
   * @see     brightness()
   * @see     altered()
   * @see     hexToRgb()
   * @see     rgbToHex()
   * @see     hsvToRgb()
   * @see     alter()
   * @see     getLuminance()
   * @see     getBrightness()
   *
   * @since   Taurus 1
   *
   * @param   array $rgb associative array of rgb values
   *
   * @return  array     an associate array of hsv values
   */
  public function rgbToHsv( $rgb ) {

    $r = $rgb[ 'r' ];
    $g = $rgb[ 'g' ];
    $b = $rgb[ 'b' ];


    $min = min( $r, $g, $b );
    $max = max( $r, $g, $b );
    $chroma = $max - $min;

    //if $chroma is 0, then s is 0 by definition, and h is undefined but 0 by convention.
    if ( $chroma == 0 ) {
      return [ 'h' => 0, 's' => 0, 'v' => $max / 255 ];
    }

    if ( $r == $max ) {
      $h = ( $g - $b ) / $chroma;

      if ( $h < 0.0 )
        $h += 6.0;

    } else if ( $g == $max ) {
        $h = ( ( $b - $r ) / $chroma ) + 2.0;
    } else {  //$b == $max
      $h = ( ( $r - $g ) / $chroma ) + 4.0;
    }

    $h *= 60.0;
    $s = $chroma / $max;
    $v = $max / 255;

    return [ 'h' => $h, 's' => $s, 'v' => $v ];

  }

  /**
   * Convert HSV color to RGB
   *
   * @link    [https://en.wikipedia.org/wiki/HSL_and_HSV] [color forumulas]
   *
   * @see     color()
   * @see     prepareIcon()
   * @see     checkHex()
   * @see     validateHex()
   * @see     normalizeHex()
   * @see     rgb()
   * @see     hsv()
   * @see     luminance()
   * @see     brightness()
   * @see     altered()
   * @see     hexToRgb()
   * @see     rgbToHex()
   * @see     rgbToHsv()
   * @see     alter()
   * @see     getLuminance()
   * @see     getBrightness()
   *
   * @since   Taurus 1
   *
   * @param   array $hsv associative array of hsv values ( 0 <= h < 360, 0 <= s <= 1, 0 <= v <= 1)
   *
   * @return  array  An array of RGB values
   */
  public function hsvToRgb( $hsv ) {

    $h = $hsv[ 'h' ];
    $s = $hsv[ 's' ];
    $v = $hsv[ 'v' ];

    $chroma = $s * $v;
    $h /= 60.0;
    $x = $chroma * ( 1.0 - abs( ( fmod( $h, 2.0 ) ) - 1.0 ) );
    $min = $v - $chroma;

    if ( $h < 1.0 ) {
      $r = $chroma;
      $g = $x;
    } else if ( $h < 2.0 ) {
      $r = $x;
      $g = $chroma;
    } else if ( $h < 3.0 ) {
      $g = $chroma;
      $b = $x;
    } else if ( $h < 4.0 ) {
      $g= $x;
      $b = $chroma;
    } else if ( $h < 5.0 ) {
      $r = $x;
      $b = $chroma;
    } else if ( $h <= 6.0 ) {
      $r = $chroma;
      $b = $x;
    }

    $r = round( ( $r + $min ) * 255 );
    $g = round( ( $g + $min ) * 255 );
    $b = round( ( $b + $min ) * 255 );

    return [ 'r' => $r, 'g' => $g, 'b' => $b ];

  }


  /**
   * Gets the luminance of a color between 0 and 1
   *
   * @link    https://en.wikipedia.org/wiki/Luminance_(relative)
   * @link    https://en.wikipedia.org/wiki/Luma_(video)
   * @link    https://en.wikipedia.org/wiki/CCIR_601
   *
   * @see     color()
   * @see     prepareIcon()
   * @see     checkHex()
   * @see     validateHex()
   * @see     normalizeHex()
   * @see     rgb()
   * @see     hsv()
   * @see     luminance()
   * @see     brightness()
   * @see     altered()
   * @see     hexToRgb()
   * @see     rgbToHex()
   * @see     rgbToHsv()
   * @see     hsvToRgb()
   * @see     alter()
   * @see     getBrightness()
   *
   * @since   Taurus 1
   *
   * @param   mixed  $color a hex color (string) or an associative array of RGB values
   *
   * @return  float         Luminance on a scale of 0 to 1
   */
  public function getLuminance( $color ) {

    if ( ! is_array( $color ) )
      $rgb = $this->rgb( $color );
    else
      $rgb = $color;

    return ( 0.299 * $rgb[ 'r' ] + 0.587 * $rgb[ 'g' ] + 0.114 * $rgb[ 'b' ] ) / 255;

  }


  /**
   * Determines whether a color is 'light' or 'dark'
   *
   * @see     color()
   * @see     prepareIcon()
   * @see     checkHex()
   * @see     validateHex()
   * @see     normalizeHex()
   * @see     rgb()
   * @see     hsv()
   * @see     luminance()
   * @see     brightness()
   * @see     altered()
   * @see     hexToRgb()
   * @see     rgbToHex()
   * @see     rgbToHsv()
   * @see     hsvToRgb()
   * @see     alter()
   * @see     getLuminance()
   *
   * @since   Taurus 1
   *
   * @param   string  $color  a hex color
   *
   * @return  string          either 'light' or 'dark'
   */
  public function getBrightness( $color ) {

    if ( isset( $this->colors[ $color ][ 'brightness' ] ) )
      return $this->colors[ $color ][ 'brightness' ];

    if ( $this->luminance( $color ) > .5 )
      $this->colors[ $color ][ 'brightness' ] = 'light';
    else
      $this->colors[ $color ][ 'brightness' ] = 'dark';

    return $this->colors[ $color ][ 'brightness' ];

  }


  /**
   * Either lightens or darkens an image
   *
   * The function starts with a hex color and converts it into
   * an RGB color space and then to an HSV color space. The V(alue)
   * in HSV is set between 0 (black) and 1 (white), which is a
   * measure of 'brightness' where 0.5 is neutral. Thus, we retain
   * the hue and saturation and keep the relative brightness of the
   * color by pushing it on the other side of neutral but at the
   * same distance from neutral. E.g.: 0.7 becomes 0.3; 0.12 becomes
   * 0.88; 0.0 becomes 1.0; and 0.5 becomes 0.5.
   *
   * @see     color()
   * @see     prepareIcon()
   * @see     checkHex()
   * @see     validateHex()
   * @see     normalizeHex()
   * @see     rgb()
   * @see     hsv()
   * @see     luminance()
   * @see     brightness()
   * @see     altered()
   * @see     hexToRgb()
   * @see     rgbToHex()
   * @see     rgbToHsv()
   * @see     hsvToRgb()
   * @see     getLuminance()
   * @see     getBrightness()
   *
   * @since   Taurus 1
   *
   * @param   string  $color  a hex color
   *
   * @return  string          a hex color
   */
  public function alter( $color ) {

    $hsv = $this->hsv( $color );
    $hsv[ 'v' ] = 1 - $hsv[ 'v' ];
    $rgb = $this->hsvToRgb( $hsv );
    $this->colors[ $color ][ 'altered' ] = $this->rgbToHex( $rgb );
    $altered = $this->color( $this->colors[ $color ][ 'altered' ] );

    $this->cache( $color ); // Cache the conversion

    return $this->colors[ $color ][ 'altered' ];

  }

 /*****************************************************************************
 * END CONVERSION FUNCTIONS
 ****************************************************************************/

 /*****************************************************************************
 * BEGIN VALIDATION / NORMALIZATION FUNCTIONS
 ****************************************************************************/

  /**
   * Checks to see if a color is a valid hex and normalizes the hex color
   *
   * @see     color()
   * @see     prepareIcon()
   * @see     validateHex()
   * @see     normalizeHex()
   * @see     rgb()
   * @see     hsv()
   * @see     luminance()
   * @see     brightness()
   * @see     altered()
   * @see     hexToRgb()
   * @see     rgbToHex()
   * @see     rgbToHsv()
   * @see     hsvToRgb()
   * @see     alter()
   * @see     getLuminance()
   * @see     getBrightness()
   *
   * @since   Taurus 1
   *
   * @param   string  $color A hex color
   *
   * @return  mixed       FALSE on non-hex or hex color (normalized) to six characters and lowercased
   */
  public function checkHex( $hex ) {

    return $this->validateHex( $this->normalizeHex( $hex ) );

  }


  /**
   * Normalizes all hex colors to six, lowercase characters
   *
   * @see     color()
   * @see     prepareIcon()
   * @see     checkHex()
   * @see     validateHex()
   * @see     rgb()
   * @see     hsv()
   * @see     luminance()
   * @see     brightness()
   * @see     altered()
   * @see     hexToRgb()
   * @see     rgbToHex()
   * @see     rgbToHsv()
   * @see     hsvToRgb()
   * @see     alter()
   * @see     getLuminance()
   * @see     getBrightness()
   *
   * @since   Taurus 1
   *
   * @param   string  $hex a hex color
   *
   * @return  string      a normalized hex color
   */
  public function normalizeHex( $hex ) {

    $hex = strtolower( str_replace( '#', '', $hex ) );
    if ( strlen( $hex ) == 3 )
      $hex = preg_replace( "/(.)(.)(.)/", "\\1\\1\\2\\2\\3\\3", $hex );
    return $hex;

  }

  /**
   * Validates a hex color
   *
   * @see     color()
   * @see     prepareIcon()
   * @see     checkHex()
   * @see     normalizeHex()
   * @see     rgb()
   * @see     hsv()
   * @see     luminance()
   * @see     brightness()
   * @see     altered()
   * @see     hexToRgb()
   * @see     rgbToHex()
   * @see     rgbToHsv()
   * @see     hsvToRgb()
   * @see     alter()
   * @see     getLuminance()
   * @see     getBrightness()
   *
   * @since   Taurus 1
   *
   * @param   string  $hex a hex color
   *
   * @return  mixed   FALSE on failure, the hex value on success
   */
  public function validateHex( $hex ) {

    if ( strlen( $hex ) != 3 && strlen( $hex ) != 6 )
      return FALSE; // Not a valid hex value
    if ( ! preg_match( "/([0-9a-f]{3}|[0-9a-f]{6})/", $hex ) )
      return FALSE; // Not a valid hex value
    return $hex;

  }


/*****************************************************************************
 * END VALIDATION / NORMALIZATION FUNCTIONS
 ****************************************************************************/
/*****************************************************************************
 * END COLOR FUNCTIONS
 ****************************************************************************/
