<?php

/**
 * SmartOSC Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *                                                                                                                                                                                            */
/* * @category   SM
 * @package    SM_Barcode
 * @version    2.0
 * @author     hoadx@smartosc.com
 * @copyright  Copyright (c) 2010-2011 SmartOSC Co. (http://www.smartosc.com)
 */
define("IMG_FORMAT_PNG", 1);
define("IMG_FORMAT_JPEG", 2);
define("IMG_FORMAT_WBMP", 4);
define("IMG_FORMAT_GIF", 8);
/**
 * Holds Color in RGB Format.
 */
class FColor {

    protected $r, $g, $b; // int Hexadecimal Value

    /**
     * Save RGB value into the classes
     *
     * @param int $r
     * @param int $g
     * @param int $b
     */

    public function __construct($r, $g, $b) {
        $this->r = $r;
        $this->g = $g;
        $this->b = $b;
    }

    /**
     * Returns Red Color
     *
     * @return int
     */
    public function r() {
        return $this->r;
    }

    /**
     * Returns Green Color
     *
     * @return int
     */
    public function g() {
        return $this->g;
    }

    /**
     * Returns Blue Color
     *
     * @return int
     */
    public function b() {
        return $this->b;
    }

    /**
     * Returns the int value for PHP color
     *
     * @return int
     */
     public function allocate($im) {
         //return imagecolorallocate($im, $this->r, $this->g, $this->b);
         //get color from image color palette
         $color = imagecolorexact($im, $this->r, $this->g, $this->b);
         if ($color == -1) {
             //color does not exist... test if we have used up room
             if(imagecolorstotal($im)>=255) {
                 //image color palette used up; pick closest assigned color
                 $color = imagecolorclosest($im, $this->r, $this->g, $this->b);
             } else {
                 //image color palette NOT used up; assign new color
                 $color = imagecolorallocate($im, $this->r, $this->g, $this->b);
             }
         }
         return $color;
     }

}

class BarCode {

    protected $maxHeight;
    protected $color1, $color2;
    protected $positionX, $positionY, $res;
    public $lastX, $lastY;
    private $error;

    /**
     * Constructor
     *
     * @param int $maxHeight
     * @param FColor $color1
     * @param FColor $color2
     * @param int $res
     */
    protected function __construct($maxHeight, FColor $color1, FColor $color2, $res) {
        $this->maxHeight = $maxHeight;
        $this->color1 = $color1;
        $this->color2 = $color2;
        $this->res = $res;
        $this->error = 0;
        $this->positionY = 0;
        $this->positionX = 0;
    }

    /**
     * Returns the index in $keys (useful for checksum)
     *
     * @param mixed $var
     * @return mixed
     */
    protected function findIndex($var) {
        return array_search($var, $this->keys);
    }

    /**
     * Returns the code of the char (useful for drawing bars)
     *
     * @param mixed $var
     * @return string
     */
    protected function findCode($var) {
        return $this->code[$this->findIndex($var)];
    }

    /**
     * Draws a Bar of $color depending of the resolution
     *
     * @param ressource $img
     * @param FColor $color
     */
    protected function DrawSingleBar($im, FColor $color) {
        $bar_color = (is_null($color)) ? NULL : $color->allocate($im);
        if (!is_null($bar_color))
            for ($i = 0; $i < $this->res; $i++)
                imageline($im, $this->positionX + $i, $this->positionY, $this->positionX + $i, $this->positionY + $this->maxHeight, $bar_color);
    }

    /**
     * Writes the Error on the picture
     *
     * @param ressource $img
     * @param string $text
     */
    protected function DrawError($im, $text) {
        $text_color = (is_null($this->color1)) ? NULL : $this->color1->allocate($im);
        imagestring($im, 5, 0, $this->error * 15, $text, $text_color);
        $this->error++;
        $this->lastX = (imagefontwidth(5) * strlen($text) > $this->lastX) ? imagefontwidth(5) * strlen($text) : $this->lastX;
        $this->lastY = $this->error * 15;
    }

    /**
     * Moving the pointer right to write a bar
     */
    protected function nextX() {
        $this->positionX+=$this->res;
    }

    public function resetPosition() {
        $this->positionX = 0;
        $this->positionY = 0;
    }

    public function getPositionX() {
        return $this->positionX;
    }

    public function getPositionY() {
        return $this->positionY;
    }

    /**
     * Draws all chars thanks to $code. if $start==1, the line begins by a bar.
     * if $start==2, the line begins by a space.
     *
     * @param ressource $im
     * @param string $code
     * @param int $start
     */
    protected function DrawChar($im, $code, $start=1) {
        $currentColor = ($start == 1) ? $this->color1 : $this->color2;
        $colornumber = $start;
        for ($i = 0; $i < strlen($code); $i++) {
            for ($j = 0; $j < intval($code[$i]) + 1; $j++) {
                $this->DrawSingleBar($im, $currentColor);
                $this->nextX();
            }
            if ($colornumber == 1) {
                $currentColor = $this->color2;
                $colornumber = 2;
            } else {
                $currentColor = $this->color1;
                $colornumber = 1;
            }
        }
    }

    /**
     * Draws the label under the barcode
     *
     * @param ressource $im
     */
    protected function DrawText($im) {
        if ($this->textfont != 0) {
            $xPosition = ($this->positionX / 2) - (strlen($this->text) / 2) * imagefontwidth($this->textfont);
            $text_color = (is_null($this->color1)) ? NULL : $this->color1->allocate($im);
            imagestring($im, $this->textfont, $xPosition, $this->maxHeight, $this->text, $text_color);
            $this->lastY = $this->maxHeight + imagefontheight($this->textfont);
        }
    }

}

class FDrawing {

    private $w, $h;  // int
    private $color;  // Fcolor
    private $filename; // char *
    private $im;  // {object}
    private $barcode = array(); // BarCode *

    /**
     * Constructor
     *
     * @param int $w
     * @param int $h
     * @param string filename
     * @param FColor $color
     */

    public function __construct($w, $h, $filename, Fcolor $color) {
        $this->w = $w;
        $this->h = $h;
        $this->filename = $filename;
        $this->color = $color;
    }

    /**
     * Destructor
     */
    public function __destruct() {
        $this->destroy();
    }

    /**
     * Init Image and color background
     */
    public function init() {
        $this->im = imagecreate($this->w, $this->h);
        imagecolorallocate($this->im, $this->color->r(), $this->color->g(), $this->color->b());
    }

    /**
     * @return ressource
     */
    public function get_im() {
        return $this->im;
    }

    public function set_im($im) {
        $this->im = $im;
    }

    /**
     * Add barcode into the drawing array (for future drawing)
     *
     * @param BarCode $barcode
     */
    public function add_barcode(BarCode $barcode) {
        $this->barcode[] = $barcode;
    }

    /**
     * Draw first all forms and after all texts on $im
     */
    public function draw_all() {
        for ($i = 0; $i < count($this->barcode); $i++){
            $this->barcode[$i]->draw($this->im);
        }

    }

    /**
     * Save $im into the file (many format available)
     *
     * @param int $image_style
     * @param int $quality
     */
    public function finish($image_style=IMG_FORMAT_PNG, $quality=100) {
        if ($image_style == constant("IMG_FORMAT_PNG")) {
            if (empty($this->filename))
                return imagepng($this->im);
            else
                return imagepng($this->im, $this->filename);
        }
        elseif ($image_style == constant("IMG_FORMAT_JPEG"))
            return imagejpeg($this->im, $this->filename, $quality);
    }

    /**
     * Free the memory of PHP (called also by destructor)
     */
    public function destroy() {
        try {
            if ($this->im) {
                imagedestroy($this->im);
            } else {
                throw new Exception('Image is not created.');
            }
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }

    public function image_content(){
        return $this->im;
    }

}
class upcb extends BarCode {
    protected $keys = array(), $code = array(), $codeParity = array();
    private $text;
    private $textfont;
    private $book;
    private $newtext;

    /**
     * Constructor
     *
     * @param int $maxHeight
     * @param FColor $color1
     * @param FColor $color2
     * @param int $res
     * @param string $text
     * @param int $textfont
     * @param bool $book
     */
    public function __construct($maxHeight, FColor $color1, FColor $color2, $res, $text, $textfont, $book = false) {
        BarCode::__construct($maxHeight, $color1, $color2, $res);

        $this->keys = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
        // Left-Hand Odd Parity starting with a space
        // Left-Hand Even Parity is the inverse (0=0012) starting with a space
        // Right-Hand is the same of Left-Hand starting with a bar
        $this->code = array(
            "2100", /* 0 */
            "1110", /* 1 */
            "1011", /* 2 */
            "0300", /* 3 */
            "0021", /* 4 */
            "0120", /* 5 */
            "0003", /* 6 */
            "0201", /* 7 */
            "0102", /* 8 */
            "2001" /* 9 */
        );
        // Parity, 0=Odd, 1=Even for manufacturer code. Depending on 1st System Digit
        $this->codeParity = array(
            array(0, 0, 0, 0, 0), /* 0 */
            array(0, 1, 0, 1, 1), /* 1 */
            array(0, 1, 1, 0, 1), /* 2 */
            array(0, 1, 1, 1, 0), /* 3 */
            array(1, 0, 0, 1, 1), /* 4 */
            array(1, 1, 0, 0, 1), /* 5 */
            array(1, 1, 1, 0, 0), /* 6 */
            array(1, 0, 1, 0, 1), /* 7 */
            array(1, 0, 1, 1, 0), /* 8 */
            array(1, 1, 0, 1, 0) /* 9 */
        );
        $this->setText($text);

        $this->textfont = $textfont;
        $this->book = $book;
    }

    /**
     * Saves Text
     *
     * @param string $text
     */
    public function setText($text) {
        $this->text = $text;
    }

    public function getText() {
        return $this->text;
    }

    private function inverse($text, $inverse = 1) {
        if ($inverse == 1)
            $text = strrev($text);
        return $text;
    }

    /**
     * Draws the barcode
     *
     * @param ressource $im
     */
    public function draw($im) {
        $error_stop = false;
        // Checking if all chars are allowed
        for ($i = 0; $i < strlen($this->text); $i++) {
            if (!is_int(array_search($this->text[$i], $this->keys))) {
                $this->DrawError($im, "Char \"" . $this->text[$i] . "\" not allowed.");
                $error_stop = true;
                break;
            }
        }
        if($error_stop) { return false;}

        if ($error_stop == false) {
            if ($this->book == true && strlen($this->text) != 10) {
                $this->DrawError($im, "Must contains 10 chars if ISBN is true.");
                $error_stop = true;
            }

            // If it"s a book, we change the code to the right one
            if ($this->book == true && strlen($this->text) == 10){
                $this->text = "978" . substr($this->text, 0, strlen($this->text) - 1);
            }

            //fix for conversion OFF
            if (is_numeric($this->text)) {
                //Gen barcode
                $text_leng = strlen((string)$this->text);
                if($text_leng != 12){
                    //Check da vao
                    $_SESSION['text_checker']=true;
                    if($text_leng < 12 && $_SESSION['text_checker']==true){ // less than 12 -> add more
                        $number_text_add_more = (12 - strlen($this->text) );
                        $text_add_more = $this->random_number($number_text_add_more);
                        $this->text = $text_add_more . $this->text;
                        $this->text = substr($this->text, 0, 12); //Make sure $this->text is 12 chars
                        // var_dump("111111111");
                        // var_dump(($text_leng < 12));
                        $_SESSION['text_checker'] = false;
                    }
                    if($text_leng > 12 && $_SESSION['text_checker']==false){
                        // var_dump("22222222222");
                        // var_dump(($text_leng < 12));
                        // var_dump($text_leng);

                        echo '<span "sm_xbarcode_error">The Input value is invalid!<br />It should be less than or equal to 12 digits.<br />Suggestion: <b>Please read XBarcode Userguide</b></span>';
                        // echo "<script> var sm_xbarcode_error = document.getElementById('sm_xbarcode_error');alert(sm_xbarcode_error.textContent);</script>";
                        // die;
                        return false;

                    }
                }//end if != 12

            } else
            {//end if $this->text !=12
                echo ($this->text);
                echo "test";
                echo ( "<div id='sm_xbarcode_error'>The Input value is invalid!<br />It contains a string.<br />Suggestion: <b>Please read XBarcode Userguide</b>.</div>");
                // echo "<script> var sm_xbarcode_error = document.getElementById('sm_xbarcode_error');alert(sm_xbarcode_error.innerText);</script>";
                // die;
                return false;
            }

            if ($error_stop == false) {
                // Calculating Checksum
                // Consider the right-most digit of the message to be in an "odd" position,
                // and assign odd/even to each character moving from right to left
                // Odd Position = 3, Even Position = 1
                // Multiply it by the number
                // Add all of that and do 10-(?mod10)

                $odd = true;
                $checksum = 0;
                for ($i = strlen($this->text); $i > 0; $i--) {
                    if ($odd == true) {
                        $multiplier = 3;
                        $odd = false;
                    } else {
                        $multiplier = 1;
                        $odd = true;
                    }
                    $checksum += $this->keys[$this->text[$i - 1]] * $multiplier;
                }
                $checksum = 10 - $checksum % 10;
                $checksum = ($checksum == 10) ? 0 : $checksum;

                // fix Conversion OFF
                if (strlen($this->text) == 12)
                    $this->text .= $this->keys[$checksum];
                // If we have to write text, we move the barcode to the right to have space to put system digit
                $this->positionX = ($this->textfont == 0) ? 0 : 10;
                // Starting Code
                $this->DrawChar($im, "000", 1);
                // Draw Second Code
                $this->DrawChar($im, $this->findCode($this->text[1]), 2);
                // Draw Manufacturer Code
                for ($i = 0; $i < 5; $i++)
                    $this->DrawChar($im, $this->inverse($this->findCode($this->text[$i + 2]), $this->codeParity[$this->text[0]][$i]), 2);
                // Draw Center Guard Bar
                $this->DrawChar($im, "00000", 2);
                // Draw Product Code
                for ($i = 7; $i < 13; $i++) {
                    $this->DrawChar($im, $this->findCode($this->text[$i]), 1);
                }
                // Draw Right Guard Bar
                $this->DrawChar($im, "000", 1);
                $this->lastX = $this->positionX;
                $this->lastY = $this->maxHeight;
                $this->DrawText($im);
            }
        }
    }

    /**
     * Overloaded method for drawing special label
     *
     * @param ressource $im
     */
    protected function DrawText($im) {
        if ($this->textfont != 0) {
            $bar_color = (is_null($this->color1)) ? NULL : $this->color1->allocate($im);
            if (!is_null($bar_color)) {

                $rememberX = $this->positionX;
                $rememberH = $this->maxHeight;

                // We increase the bars
                $this->maxHeight = $this->maxHeight + 9;
                $this->positionX = 10;
                $this->DrawSingleBar($im, $this->color1);
                $this->positionX += $this->res * 2;
                $this->DrawSingleBar($im, $this->color1);
                // Center Guard Bar
                $this->positionX += $this->res * 44;
                $this->DrawSingleBar($im, $this->color1);
                $this->positionX += $this->res * 2;
                $this->DrawSingleBar($im, $this->color1);
                // Last Bars
                $this->positionX += $this->res * 44;
                $this->DrawSingleBar($im, $this->color1);
                $this->positionX += $this->res * 2;
                $this->DrawSingleBar($im, $this->color1);


                $this->positionX = $rememberX;
                $this->maxHeight = $rememberH;

                imagechar($im, $this->textfont, 1, $this->maxHeight - (imagefontheight($this->textfont) / 2), $this->text[0], $bar_color);
                imagestring($im, $this->textfont, 10 + (3 * $this->res + 48 * $this->res) / 2 - imagefontwidth($this->textfont) * (6 / 2), $this->maxHeight + 1, substr($this->text, 1, 6), $bar_color);
                imagestring($im, $this->textfont, 10 + 46 * $this->res + (3 * $this->res + 46 * $this->res) / 2 - imagefontwidth($this->textfont) * (6 / 2), $this->maxHeight + 1, substr($this->text, 7, 6), $bar_color);
            }
            $this->lastY = $this->maxHeight + imagefontheight($this->textfont);
        }
    }


    public function random_number($count){
        $res='';
        if(is_numeric($count) && $count>0){
            for($i=0;$i<$count;$i++){
                $res .= "0";
            }
        }
        return $res;
    }//end function random_number

}

class code39 extends BarCode {

    protected $keys = array(), $code = array();
    private $starting, $ending;
    protected $text;
    protected $textfont;
    private $checksum;

    /**
     * Constructor
     *
     * @param int $maxHeight
     * @param FColor $color1
     * @param FColor $color2
     * @param int $res
     * @param string $text
     * @param int $textfont
     * @param bool $checksum
     */
    public function __construct($maxHeight, FColor $color1, FColor $color2, $res, $text, $textfont, $checksum = false) {
        BarCode::__construct($maxHeight, $color1, $color2, $res);
        $this->starting = $this->ending = 43;
        $this->keys = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "-", ".", " ", "$", "/", "+", "%", "*");
        $this->code = array(// 0 added to add an extra space
            "0001101000", /* 0 */
            "1001000010", /* 1 */
            "0011000010", /* 2 */
            "1011000000", /* 3 */
            "0001100010", /* 4 */
            "1001100000", /* 5 */
            "0011100000", /* 6 */
            "0001001010", /* 7 */
            "1001001000", /* 8 */
            "0011001000", /* 9 */
            "1000010010", /* A */
            "0010010010", /* B */
            "1010010000", /* C */
            "0000110010", /* D */
            "1000110000", /* E */
            "0010110000", /* F */
            "0000011010", /* G */
            "1000011000", /* H */
            "0010011000", /* I */
            "0000111000", /* J */
            "1000000110", /* K */
            "0010000110", /* L */
            "1010000100", /* M */
            "0000100110", /* N */
            "1000100100", /* O */
            "0010100100", /* P */
            "0000001110", /* Q */
            "1000001100", /* R */
            "0010001100", /* S */
            "0000101100", /* T */
            "1100000010", /* U */
            "0110000010", /* V */
            "1110000000", /* W */
            "0100100010", /* X */
            "1100100000", /* Y */
            "0110100000", /* Z */
            "0100001010", /* - */
            "1100001000", /* . */
            "0110001000", /*   */
            "0101010000", /* $ */
            "0101000100", /* / */
            "0100010100", /* + */
            "0001010100", /* % */
            "0100101000" /*                 * */
        );
        $this->setText($text);
        $this->textfont = $textfont;
        $this->checksum = $checksum;
    }

    /**
     * Saves Text
     *
     * @param string $text
     */
    public function setText($text) {
        $this->text = strtoupper($text); // Only Capital Letters are Allowed
    }

    /**
     * Draws the barcode
     *
     * @param ressource $im
     */
    public function draw($im) {
        $error_stop = false;

        // Checking if all chars are allowed
        for ($i = 0; $i < strlen($this->text); $i++) {
            if (!is_int(array_search($this->text[$i], $this->keys))) {
                $this->DrawError($im, "Char \"" . $this->text[$i] . "\" not allowed.");
                $error_stop = true;
            }
        }
        if ($error_stop == false) {
            // The * is not allowed
            if (is_int(strpos($this->text, "*"))) {
                $this->DrawError($im, "Char \"*\" not allowed.");
                $error_stop = true;
            }
            if ($error_stop == false) {
                // Starting *
                $this->DrawChar($im, $this->code[$this->starting], 1);
                // Chars
                for ($i = 0; $i < strlen($this->text); $i++)
                    $this->DrawChar($im, $this->findCode($this->text[$i]), 1);
                // Checksum (rarely used)
                if ($this->checksum == true) {
                    $checksum = 0;
                    for ($i = 0; $i < strlen($this->text); $i++)
                        $checksum += $this->findIndex($this->text[$i]);
                    $this->DrawChar($im, $this->code[$checksum % 43], 1);
                }
                // Ending *
                $this->DrawChar($im, $this->code[$this->ending], 1);
                $this->lastX = $this->positionX;
                $this->lastY = $this->maxHeight;
                $this->DrawText($im);
            }
        }
    }

}

class ean13 extends BarCode {
    protected $keys = array(), $code = array(), $codeParity = array();
    private $text;
    private $textfont;
    private $book;
    private $newtext;

    /**
     * Constructor
     *
     * @param int $maxHeight
     * @param FColor $color1
     * @param FColor $color2
     * @param int $res
     * @param string $text
     * @param int $textfont
     * @param bool $book
     */
    public function __construct($maxHeight, FColor $color1, FColor $color2, $res, $text, $textfont, $book = false) {
        BarCode::__construct($maxHeight, $color1, $color2, $res);

        $this->keys = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
        // Left-Hand Odd Parity starting with a space
        // Left-Hand Even Parity is the inverse (0=0012) starting with a space
        // Right-Hand is the same of Left-Hand starting with a bar
        $this->code = array(
            "2100", /* 0 */
            "1110", /* 1 */
            "1011", /* 2 */
            "0300", /* 3 */
            "0021", /* 4 */
            "0120", /* 5 */
            "0003", /* 6 */
            "0201", /* 7 */
            "0102", /* 8 */
            "2001" /* 9 */
        );
        // Parity, 0=Odd, 1=Even for manufacturer code. Depending on 1st System Digit
        $this->codeParity = array(
            array(0, 0, 0, 0, 0), /* 0 */
            array(0, 1, 0, 1, 1), /* 1 */
            array(0, 1, 1, 0, 1), /* 2 */
            array(0, 1, 1, 1, 0), /* 3 */
            array(1, 0, 0, 1, 1), /* 4 */
            array(1, 1, 0, 0, 1), /* 5 */
            array(1, 1, 1, 0, 0), /* 6 */
            array(1, 0, 1, 0, 1), /* 7 */
            array(1, 0, 1, 1, 0), /* 8 */
            array(1, 1, 0, 1, 0) /* 9 */
        );
        $this->setText($text);

        $this->textfont = $textfont;
        $this->book = $book;
    }

    /**
     * Saves Text
     *
     * @param string $text
     */
    public function setText($text) {
        $this->text = $text;
    }

    public function getText() {
        return $this->text;
    }

    private function inverse($text, $inverse = 1) {
        if ($inverse == 1)
            $text = strrev($text);
        return $text;
    }

    /**
     * Draws the barcode
     *
     * @param ressource $im
     */
    public function draw($im) {

        $error_stop = false;
        // Checking if all chars are allowed
        for ($i = 0; $i < strlen($this->text); $i++) {
            if (!is_int(array_search($this->text[$i], $this->keys))) {
                $this->DrawError($im, "Char \"" . $this->text[$i] . "\" not allowed.");
                $error_stop = true;
                break;
            }
        }
        if($error_stop) { return false;}

        if ($error_stop == false) {
            if ($this->book == true && strlen($this->text) != 10) {
                $this->DrawError($im, "Must contains 10 chars if ISBN is true.");
                $error_stop = true;
            }

            // If it"s a book, we change the code to the right one
            if ($this->book == true && strlen($this->text) == 10){
                $this->text = "978" . substr($this->text, 0, strlen($this->text) - 1);
            }

            //fix for conversion OFF

            if (is_numeric($this->text)) {
                //Gen barcode
                $text_leng = strlen((string)$this->text);
                    if($text_leng != 12){
                        //Check da vao
                        $_SESSION['text_checker']=true;
                        if($text_leng < 12 && $_SESSION['text_checker']==true){ // less than 12 -> add more
                            $number_text_add_more = (12 - strlen($this->text) );
                            $text_add_more = $this->random_number($number_text_add_more);
                            $this->text = $text_add_more . $this->text;
                            $this->text = substr($this->text, 0, 12); //Make sure $this->text is 12 chars
                            // var_dump("111111111");
                            // var_dump(($text_leng < 12));
                            $_SESSION['text_checker'] = false;
                        }
                        if($text_leng > 12 && $_SESSION['text_checker']==false){
                            // var_dump("22222222222");
                            // var_dump(($text_leng < 12));
                            // var_dump($text_leng);

                            echo '<span "sm_xbarcode_error">The Input value is invalid!<br />It should be less than or equal to 12 digits.<br />Suggestion: <b>Please read XBarcode Userguide</b></span>';
                            // echo "<script> var sm_xbarcode_error = document.getElementById('sm_xbarcode_error');alert(sm_xbarcode_error.textContent);</script>";
                            // die;
                            return false;

                        }
                    }//end if != 12

            } else
                {//end if $this->text !=12
                    echo ( "<div id='sm_xbarcode_error'>The Input value is invalid!<br />It contains a string.<br />Suggestion: <b>Please read XBarcode Userguide</b>.</div>");
                    // echo "<script> var sm_xbarcode_error = document.getElementById('sm_xbarcode_error');alert(sm_xbarcode_error.innerText);</script>";
                    // die;
                    return false;
                }


            if ($error_stop == false) {
                // Calculating Checksum
                // Consider the right-most digit of the message to be in an "odd" position,
                // and assign odd/even to each character moving from right to left
                // Odd Position = 3, Even Position = 1
                // Multiply it by the number
                // Add all of that and do 10-(?mod10)
                $odd = true;
                $checksum = 0;
                for ($i = strlen($this->text); $i > 0; $i--) {
                    if ($odd == true) {
                        $multiplier = 3;
                        $odd = false;
                    } else {
                        $multiplier = 1;
                        $odd = true;
                    }
                    $checksum += $this->keys[$this->text[$i - 1]] * $multiplier;
                }
                $checksum = 10 - $checksum % 10;
                $checksum = ($checksum == 10) ? 0 : $checksum;

                // fix Conversion OFF
                if (strlen($this->text) == 12)
                    $this->text .= $this->keys[$checksum];
                // If we have to write text, we move the barcode to the right to have space to put system digit
                $this->positionX = ($this->textfont == 0) ? 0 : 10;
                // Starting Code
                $this->DrawChar($im, "000", 1);
                // Draw Second Code
                $this->DrawChar($im, $this->findCode($this->text[1]), 2);
                // Draw Manufacturer Code
                for ($i = 0; $i < 5; $i++)
                    $this->DrawChar($im, $this->inverse($this->findCode($this->text[$i + 2]), $this->codeParity[$this->text[0]][$i]), 2);
                // Draw Center Guard Bar
                $this->DrawChar($im, "00000", 2);
                // Draw Product Code
                for ($i = 7; $i < 13; $i++) {
                    $this->DrawChar($im, $this->findCode($this->text[$i]), 1);
                }
                // Draw Right Guard Bar
                $this->DrawChar($im, "000", 1);
                $this->lastX = $this->positionX;
                $this->lastY = $this->maxHeight;
                $this->DrawText($im);
            }
        }
    }

    /**
     * Overloaded method for drawing special label
     *
     * @param ressource $im
     */
    protected function DrawText($im) {
        if ($this->textfont != 0) {
            $bar_color = (is_null($this->color1)) ? NULL : $this->color1->allocate($im);
            if (!is_null($bar_color)) {
                $rememberX = $this->positionX;
                $rememberH = $this->maxHeight;
                // We increase the bars
                $this->maxHeight = $this->maxHeight + 9;
                $this->positionX = 10;
                $this->DrawSingleBar($im, $this->color1);
                $this->positionX += $this->res * 2;
                $this->DrawSingleBar($im, $this->color1);
                // Center Guard Bar
                $this->positionX += $this->res * 44;
                $this->DrawSingleBar($im, $this->color1);
                $this->positionX += $this->res * 2;
                $this->DrawSingleBar($im, $this->color1);
                // Last Bars
                $this->positionX += $this->res * 44;
                $this->DrawSingleBar($im, $this->color1);
                $this->positionX += $this->res * 2;
                $this->DrawSingleBar($im, $this->color1);

                $this->positionX = $rememberX;
                $this->maxHeight = $rememberH;
                imagechar($im, $this->textfont, 1, $this->maxHeight - (imagefontheight($this->textfont) / 2), $this->text[0], $bar_color);
                imagestring($im, $this->textfont, 10 + (3 * $this->res + 48 * $this->res) / 2 - imagefontwidth($this->textfont) * (6 / 2), $this->maxHeight + 1, substr($this->text, 1, 6), $bar_color);
                imagestring($im, $this->textfont, 10 + 46 * $this->res + (3 * $this->res + 46 * $this->res) / 2 - imagefontwidth($this->textfont) * (6 / 2), $this->maxHeight + 1, substr($this->text, 7, 6), $bar_color);
            }
            $this->lastY = $this->maxHeight + imagefontheight($this->textfont);
        }
    }


    public function random_number($count){
        $res='';
        if(is_numeric($count) && $count>0){
            for($i=0;$i<$count;$i++){
                $res .= "0";
            }
        }
        return $res;
    }//end function random_number

}

class i25 extends BarCode {

    protected $keys = array(), $code = array();
    protected $text;
    protected $textfont;
    private $checksum;

    /**
     * Constructor
     *
     * @param int $maxHeight
     * @param FColor $color1
     * @param FColor $color2
     * @param int $res
     * @param string $text
     * @param int $textfont
     * @param bool $checksum
     */
    public function __construct($maxHeight, FColor $color1, FColor $color2, $res, $text, $textfont, $checksum = false) {
        BarCode::__construct($maxHeight, $color1, $color2, $res);
        $this->keys = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
        $this->code = array(
            "00110", /* 0 */
            "10001", /* 1 */
            "01001", /* 2 */
            "11000", /* 3 */
            "00101", /* 4 */
            "10100", /* 5 */
            "01100", /* 6 */
            "00011", /* 7 */
            "10010", /* 8 */
            "01010" /* 9 */
        );
        $this->setText($text);
        $this->textfont = $textfont;
        $this->checksum = $checksum;
    }

    /**
     * Saves Text
     *
     * @param string $text
     */
    public function setText($text) {
        $this->text = $text;
    }

    /**
     * Draws the barcode
     *
     * @param ressource $im
     */
    public function draw($im) {
        $error_stop = false;

        // Checking if all chars are allowed
        for ($i = 0; $i < strlen($this->text); $i++) {
            if (!is_int(array_search($this->text[$i], $this->keys))) {
                $this->DrawError($im, "Char \"" . $this->text[$i] . "\" not allowed.");
                $error_stop = true;
                break;
            }
        }
        if ($error_stop == false) {
            // Must be even
            if (strlen($this->text) % 2 != 0 && $this->checksum == false) {
                $this->DrawError($im, "i25 must be even if checksum is false.");
                $error_stop = true;
            } elseif (strlen($this->text) % 2 == 0 && $this->checksum == true) {
                $this->DrawError($im, "i25 must be odd if checksum is true.");
                $error_stop = true;
            }
            if ($error_stop == false) {
                // We calculate checksum first
                // Odd Number has a weight of 1, even number has a weight of 3
                // Multiply to position
                // Sum all of that and mod 10
                if ($this->checksum == true) {
                    $checksum = 0;
                    for ($i = 1; $i <= strlen($this->text); $i++) {
                        $multiplier = (intval($this->text[$i - 1]) % 2 != 0) ? 1 : 3;
                        $checksum += $i * $multiplier;
                    }
                    $this->text .= $this->keys[$checksum % 10];
                }
                // Starting Code
                $this->DrawChar($im, "0000", 1);
                // Chars
                for ($i = 0; $i < strlen($this->text); $i+=2) {
                    $temp_bar = "";
                    for ($j = 0; $j < strlen($this->findCode($this->text[$i])); $j++) {
                        $temp_bar .= substr($this->findCode($this->text[$i]), $j, 1);
                        $temp_bar .= substr($this->findCode($this->text[$i + 1]), $j, 1);
                    }
                    $this->DrawChar($im, $temp_bar, 1);
                }
                // Ending Code
                $this->DrawChar($im, "100", 1);
                $this->lastX = $this->positionX;
                $this->lastY = $this->maxHeight;
                $this->DrawText($im);
            }
        }
    }

}

/**
 * code128.php
 * --------------------------------------------------------------------
 *
 * Sub-Class - Code 128, A, B, C
 *
 * # Code C Working properly only on PHP4 or PHP5.0.3+ due to bug :
 * http://bugs.php.net/bug.php?id=28862
 *
 * --------------------------------------------------------------------
 * Revision History
 * V1.00	17 jun	2004	Jean-Sebastien Goupil
 * --------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://other.lookstrike.com/barcode/
 */
class code128 extends BarCode {

    protected $keys = array(), $keysA = array(), $keysB = array(), $keysC = array(), $code = array();
    private $starting, $ending, $starting_text;
    protected $text;
    protected $textfont;
    private $currentCode;

    /**
     * Constructor
     *
     * @param int $maxHeight
     * @param FColor $color1
     * @param FColor $color2
     * @param int $res
     * @param string $text
     * @param int $textfont
     * @param char $start
     */
    public function __construct($maxHeight, FColor $color1, FColor $color2, $res, $text, $textfont, $start = "B") {
        BarCode::__construct($maxHeight, $color1, $color2, $res);
        if ($start == "A")
            $this->starting = 103;
        elseif ($start == "B")
            $this->starting = 104;
        elseif ($start == "C")
            $this->starting = 105;
        $this->ending = 106;
        $this->currentCode = $start;
        /* CODE 128 A */
        $this->keysA = array(" ", "!", "\"", "#", "$", "%", "&", "\"", "(", ")", "*", "+", ",", "-", ".", "/", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9", ":", ";", "<", "=", ">", "?", "@", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "[", "\\", "]", "^", "_", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", chr(128), chr(129));

        /* CODE 128 B */
        $this->keysB = array(" ", "!", "\"", "#", "$", "%", "&", "\"", "(", ")", "*", "+", ",", "-", ".", "/", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9", ":", ";", "<", "=", ">", "?", "@", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "[", "\\", "]", "^", "_", "`", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "{", "|", "}", "~", "", "", "", "", chr(128), "", chr(130));

        /* CODE 128 C */
        $this->keysC = array();
        for ($i = 0; $i <= 99; $i++)
            $this->keysC[] = sprintf("%02d", $i);
        $this->keysC[] = chr(129);
        $this->keysC[] = chr(130);

        $this->code = array(
            "101111", /* 00 */
            "111011", /* 01 */
            "111110", /* 02 */
            "010112", /* 03 */
            "010211", /* 04 */
            "020111", /* 05 */
            "011102", /* 06 */
            "011201", /* 07 */
            "021101", /* 08 */
            "110102", /* 09 */
            "110201", /* 10 */
            "120101", /* 11 */
            "001121", /* 12 */
            "011021", /* 13 */
            "011120", /* 14 */
            "002111", /* 15 */
            "012011", /* 16 */
            "012110", /* 17 */
            "112100", /* 18 */
            "110021", /* 19 */
            "110120", /* 20 */
            "102101", /* 21 */
            "112001", /* 22 */
            "201020", /* 23 */
            "200111", /* 24 */
            "210011", /* 25 */
            "210110", /* 26 */
            "201101", /* 27 */
            "211001", /* 28 */
            "211100", /* 29 */
            "101012", /* 30 */
            "101210", /* 31 */
            "121010", /* 32 */
            "000212", /* 33 */
            "020012", /* 34 */
            "020210", /* 35 */
            "001202", /* 36 */
            "021002", /* 37 */
            "021200", /* 38 */
            "100202", /* 39 */
            "120002", /* 40 */
            "120200", /* 41 */
            "001022", /* 42 */
            "001220", /* 43 */
            "021020", /* 44 */
            "002012", /* 45 */
            "002210", /* 46 */
            "022010", /* 47 */
            "202010", /* 48 */
            "100220", /* 49 */
            "120020", /* 50 */
            "102002", /* 51 */
            "102200", /* 52 */
            "102020", /* 53 */
            "200012", /* 54 */
            "200210", /* 55 */
            "220010", /* 56 */
            "201002", /* 57 */
            "201200", /* 58 */
            "221000", /* 59 */
            "203000", /* 60 */
            "110300", /* 61 */
            "320000", /* 62 */
            "000113", /* 63 */
            "000311", /* 64 */
            "010013", /* 65 */
            "010310", /* 66 */
            "030011", /* 67 */
            "030110", /* 68 */
            "001103", /* 69 */
            "001301", /* 70 */
            "011003", /* 71 */
            "011300", /* 72 */
            "031001", /* 73 */
            "031100", /* 74 */
            "130100", /* 75 */
            "110003", /* 76 */
            "302000", /* 77 */
            "130001", /* 78 */
            "023000", /* 79 */
            "000131", /* 80 */
            "010031", /* 81 */
            "010130", /* 82 */
            "003101", /* 83 */
            "013001", /* 84 */
            "013100", /* 85 */
            "300101", /* 86 */
            "310001", /* 87 */
            "310100", /* 88 */
            "101030", /* 89 */
            "103010", /* 90 */
            "301010", /* 91 */
            "000032", /* 92 */
            "000230", /* 93 */
            "020030", /* 94 */
            "003002", /* 95 */
            "003200", /* 96 */
            "300002", /* 97 */
            "300200", /* 98 */
            "002030", /* 99 */
            "003020", /* 100 */
            "200030", /* 101 */
            "300020", /* 102 */
            "100301", /* 103 */
            "100103", /* 104 */
            "100121", /* 105 */
            "122000" /* STOP */
        );
        $this->setText($text);
        $this->textfont = $textfont;
        $this->usingCode($start);
        $this->starting_text = $start;
    }

    /**
     * Saves Text
     *
     * @param string $text
     */
    public function setText($text) {
        $this->text = $text;
    }

    private function usingCode($code) {
        if ($code == "A")
            $this->keys = $this->keysA;
        elseif ($code == "B")
            $this->keys = $this->keysB;
        elseif ($code == "C")
            $this->keys = $this->keysC;
        $this->currentCode = $code;
    }

    /**
     * Draws the barcode
     *
     * @param ressource $im
     */
    public function draw($im) {
        $error_stop = false;

        $this->usingCode($this->starting_text);
        // Checking if all chars are allowed
        for ($i = 0; $i < strlen($this->text); $i++) {
            if ($this->currentCode == "C") {
                if (isset($this->text[$i + 1]) && $this->check_int($this->text[$i + 1])) {
                    $value_test = array_search($this->text[$i] . $this->text[$i + 1], $this->keys);
                    $i++;
                } else {
                    $this->DrawError($im, "With Code C, you must provide always pair of two integers.");
                    $error_stop = true;
                }
            }
            else
                $value_test = array_search($this->text[$i], $this->keys);
            if (!is_int($value_test)) {
                $this->DrawError($im, "Char \"" . $this->text[$i] . "\" not allowed.");
                $error_stop = true;
            }
            if ($this->findIndex($this->text[$i]) == 99 && $this->currentCode != "C")
                $this->usingCode("C");
            elseif ($this->findIndex($this->text[$i]) == 100 && $this->currentCode != "B")
                $this->usingCode("B");
            elseif ($this->findIndex($this->text[$i]) == 101 && $this->currentCode != "A")
                $this->usingCode("A");
        }
        if ($error_stop == false) {
            // The START-A, START-B, START-C, STOP are not allowed
            if (is_int(strpos($this->text, chr(135))) || is_int(strpos($this->text, chr(136))) || is_int(strpos($this->text, chr(137))) || is_int(strpos($this->text, chr(138)))) {
                $this->DrawError($im, "Chars START-A, START-B, START-C, STOP not allowed.");
                $error_stop = true;
            }
            if ($error_stop == false) {
                $this->usingCode($this->starting_text);
                // Starting Code
                $this->DrawChar($im, $this->code[$this->starting], 1);
                // Chars
                for ($i = 0; $i < strlen($this->text); $i++) {
                    if ($this->currentCode == "C") {
                        $this->DrawChar($im, $this->findCode($this->text[$i] . $this->text[$i + 1]), 1);
                        $i++;
                    }
                    else
                        $this->DrawChar($im, $this->findCode($this->text[$i]), 1);
                    if ($this->findIndex($this->text[$i]) == 99 && $this->currentCode != "C")
                        $this->usingCode("C");
                    elseif ($this->findIndex($this->text[$i]) == 100 && $this->currentCode != "B")
                        $this->usingCode("B");
                    elseif ($this->findIndex($this->text[$i]) == 101 && $this->currentCode != "A")
                        $this->usingCode("A");
                }
                // Checksum
                // First Char (START)
                // + Starting with the first data character following the start character,
                // take the value of the character (between 0 and 102, inclusive) multiply
                // it by its character position (1) and add that to the running checksum.
                // Modulated 103
                if ($this->starting == 103)
                    $this->usingCode("A");
                elseif ($this->starting == 104)
                    $this->usingCode("B");
                elseif ($this->starting == 105)
                    $this->usingCode("C");
                $checksum = 0;
                $checksum += $this->starting;
                for ($position = 1, $i = 0; $i < strlen($this->text); $position++, $i++) {
                    if ($this->currentCode == "C") {
                        $checksum += intval($this->text[$i] . $this->text[$i + 1]) * $position;
                        $i++;
                    }
                    else
                        $checksum += $this->findIndex($this->text[$i]) * $position;

                    if ($this->findIndex($this->text[$i]) == 99 && $this->currentCode != "C")
                        $this->usingCode("C");
                    elseif ($this->findIndex($this->text[$i]) == 100 && $this->currentCode != "B")
                        $this->usingCode("B");
                    elseif ($this->findIndex($this->text[$i]) == 101 && $this->currentCode != "A")
                        $this->usingCode("A");
                }
                $this->DrawChar($im, $this->code[$checksum % 103], 1);
                // Ending Code
                $this->DrawChar($im, $this->code[$this->ending], 1);
                // Draw a Final Bar
                $this->DrawChar($im, "1", 1);
                $this->lastX = $this->positionX;
                $this->lastY = $this->maxHeight;
                // Removing Special Code
                //$this->text = preg_replace(chr(128), "", $this->text);
                //$this->text = preg_replace(chr(129), "", $this->text);
                //$this->text = preg_replace(chr(130), "", $this->text);
                $this->DrawText($im);
            }
        }
    }

    private function check_int($var) {
        if (intval($var) >= 0 || intval($var) <= 9)
            return true;
        else
            return false;
    }

}

class SM_Barcode_Helper_Abstract extends Mage_Core_Helper_Abstract {
    const PRODUCT = 'XBarcodeRMA';
    public function __construct() {
        if (!Mage::helper('barcode/license')->checkLicense(SM_Barcode_Helper_Abstract::PRODUCT, Mage::getStoreConfig('barcode/general/key'))) {
//            ob_start();
//      echo ("<div style=\"padding: 10px; background-attachment:scroll;background-color:#FDFAA4;background-image:none;background-origin:initial;background-position:0 0;background-repeat:repeat repeat;border-bottom-color:#988753;border-bottom-style:solid;border-bottom-width:1px;bottom:0;display:block;height:200px;opacity:0.85;position:fixed;right:0;width:300px;z-index:100;\">
//      The XBarcode/RMA extension has been disabled or your license key is invalid!<br/><br/>Please click <a href=\"" . Mage::helper("adminhtml")->getUrl("adminhtml/system_config/edit/section/barcode") . "\">here</a> to enter your license key.</div>");
        }
    }

    public function isEnable() {
        return Mage::getStoreConfig("barcode/general/enabled");
    }

    public function canShowOnInvoice() {
        return Mage::getStoreConfig("barcode/order/invoice_enabled");
    }

    public function canShowOnPackingslip() {
        return Mage::getStoreConfig("barcode/order/packingslip_enabled");
    }

    public function getCompabilityMode() {
        return Mage::getStoreConfig("barcode/general/compability");
    }

    public function jsonEncode($valueToEncode, $cycleCheck = false, $options = array()) {
        $json = Zend_Json::encode($valueToEncode, $cycleCheck, $options);
        $inline = Mage::getSingleton("core/translate_inline");
        if ($inline->isAllowed()) {
            $inline->setIsJson(true);
            $inline->processResponseBody($json);
            $inline->setIsJson(false);
        }

        return $json;
    }

    public function jsonDecode($encodedValue, $objectDecodeType = Zend_Json::TYPE_ARRAY) {
        return Zend_Json::decode($encodedValue, $objectDecodeType);
    }


}


class Simpleimage {

    var $image;
    var $image_type;

    function __construct($filename = null){
        if(!empty($filename)){
            $this->load($filename);
        }
    }

    function load($filename) {
        $image_info = getimagesize($filename);
        $this->image_type = $image_info[2];
        if( $this->image_type == IMAGETYPE_JPEG ) {
            $this->image = imagecreatefromjpeg($filename);
        } elseif( $this->image_type == IMAGETYPE_GIF ) {
            $this->image = imagecreatefromgif($filename);
        } elseif( $this->image_type == IMAGETYPE_PNG ) {
            $this->image = imagecreatefrompng($filename);
        } else {
            throw new Exception("The file you're trying to open is not supported");
        }

    }

    function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
        if( $image_type == IMAGETYPE_JPEG ) {
            imagejpeg($this->image,$filename,$compression);
        } elseif( $image_type == IMAGETYPE_GIF ) {
            imagegif($this->image,$filename);
        } elseif( $image_type == IMAGETYPE_PNG ) {
            imagepng($this->image,$filename);
        }
        if( $permissions != null) {
            chmod($filename,$permissions);
        }
    }

    function output($image_type=IMAGETYPE_JPEG, $quality = 80) {
        if( $image_type == IMAGETYPE_JPEG ) {
            header("Content-type: image/jpeg");
            imagejpeg($this->image, null, $quality);
        } elseif( $image_type == IMAGETYPE_GIF ) {
            header("Content-type: image/gif");
            imagegif($this->image);
        } elseif( $image_type == IMAGETYPE_PNG ) {
            header("Content-type: image/png");
            imagepng($this->image);
        }
    }

    function getWidth() {
        return imagesx($this->image);
    }

    function getHeight() {
        return imagesy($this->image);
    }

    function resizeToHeight($height) {
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width,$height);
    }

    function resizeToWidth($width) {
        $ratio = $width / $this->getWidth();
        $height = $this->getHeight() * $ratio;
        $this->resize($width,$height);
    }

    function square($size){
        $new_image = imagecreatetruecolor($size, $size);

        if($this->getWidth() > $this->getHeight()){
            $this->resizeToHeight($size);

            imagecolortransparent($new_image, imagecolorallocate($new_image, 0, 0, 0));
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            imagecopy($new_image, $this->image, 0, 0, ($this->getWidth() - $size) / 2, 0, $size, $size);
        } else {
            $this->resizeToWidth($size);

            imagecolortransparent($new_image, imagecolorallocate($new_image, 0, 0, 0));
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            imagecopy($new_image, $this->image, 0, 0, 0, ($this->getHeight() - $size) / 2, $size, $size);
        }

        $this->image = $new_image;
    }

    function scale($scale) {
        $width = $this->getWidth() * $scale/100;
        $height = $this->getHeight() * $scale/100;
        $this->resize($width,$height);
    }

    function resize($width,$height) {
         $new_image = imagecreate($width, $height);
//        $new_image = imagecreatetruecolor($width, $height);

//        imagecolortransparent($new_image, imagecolorallocate($new_image, 0, 0, 0));
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);

        imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        $this->image = $new_image;
    }
    function cut($x, $y, $width, $height){
        $new_image = imagecreatetruecolor($width, $height);

        imagecolortransparent($new_image, imagecolorallocate($new_image, 0, 0, 0));
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);

        imagecopy($new_image, $this->image, 0, 0, $x, $y, $width, $height);

        $this->image = $new_image;
    }
    function maxarea($width, $height = null){
        $height = $height ? $height : $width;

        if($this->getWidth() > $width){
            $this->resizeToWidth($width);
        }
        if($this->getHeight() > $height){
            $this->resizeToheight($height);
        }
    }

    function cutFromCenter($width, $height){

        if($width < $this->getWidth() && $width > $height){
            $this->resizeToWidth($width);
        }
        if($height < $this->getHeight() && $width < $height){
            $this->resizeToHeight($height);
        }

        $x = ($this->getWidth() / 2) - ($width / 2);
        $y = ($this->getHeight() / 2) - ($height / 2);

        return $this->cut($x, $y, $width, $height);
    }

    function maxareafill($width, $height, $red = 0, $green = 0, $blue = 0){
        $this->maxarea($width, $height);
        $new_image = imagecreatetruecolor($width, $height);
        $color_fill = imagecolorallocate($new_image, $red, $green, $blue);
        imagefill($new_image, 0, 0, $color_fill);
        imagecopyresampled($new_image, $this->image, floor(($width - $this->getWidth())/2), floor(($height-$this->getHeight())/2), 0, 0, $this->getWidth(), $this->getHeight(), $this->getWidth(), $this->getHeight());
        $this->image = $new_image;
    }

}
