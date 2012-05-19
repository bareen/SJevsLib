<?php

namespace SJevs\LibBundle;

/*
  @author Sergej Jevsejev
  @url http://sjevsejev.blogspot.com
*/

class Preg
{
    /*
     * @var $preg string Regular expression with "()" to match substring
     * @var $text string Text to search in
     * @return string
     *
     * getOne('/sup(er)str/', 'my superstr')
     * returns: "er"
     */
    function getOne($preg, $text)
    {
        preg_match($preg, $text, $matches);

        if($matches)
            return $matches[1];
        else
            return null;
    }

    /*
    * @var $preg string Regular expression with "()" to match substring
    * @var $text string Text to search in
    * @return array
    *
    * getAll('/([\w]+s)/', 'this is my string');
    * returns: array([0]=>'this', [1]=>'is');
    */
    function getAll($preg, $text)
    {
        preg_match_all($preg, $text, $matches);

        if(empty($matches))
            return null;

        $c = count($matches);
        $c2 = count($matches[1]);
        $output = array();

        for($i=0; $i<$c2; $i++)
        {
            $output[$i] = array();

            for($j=1; $j<$c; $j++)
            {
                $output[$i][] = $matches[$j][$i];
            }
        }

        return $output;

    }

}