<?php
function convert_text($text){
        $text = preg_replace("@{year}@u",date("Y"),$text);
        $text = preg_replace("@{month}@u",date("n"),$text);
        $text = preg_replace("@{day}@u",date("j"),$text);
        $text = preg_replace("@{hour}@u",date("G"),$text);
        $text = preg_replace("@{minute}@u",date("i"),$text);
        $text = preg_replace("@{second}@u",date("s"),$text);    
        return $text;
}