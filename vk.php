<?php
/**
 * Vk.com video script
 * - it searches for the movie
 * - it gets embed code
 *
 *   @author Sergej Jevsejev
 *   @url http://sjevsejev.blogspot.com
 */

namespace SJevs\VideoBundle;

use SJevs\LibBundle\Grabber;

class Vk
{
    private $gb; // Grabber https://github.com/sjevs/SJevsLib/blob/master/Grabber.php

    private $phoneLast4Digits; /// sometimes required to confirm your identity

    function __construct($login, $password, $phoneLast4Digits)
    {
        $this->$phoneLast4Digits = $phoneLast4Digits;

        $this->gb = new Grabber();

        $data = $this->gb->post('http://login.vk.com/?act=login',
            array(
                'act' => 'login',
                'q' => '',
                'al_frame' => '1',
                'expire' => '',
                'captcha_sid' => '',
                'captcha_key' => '',
                'from_host' => 'vkontakte.ru',
                'email' => $login,
                'pass' => $password
            ));

        $sid = substr($data, strpos($data, "setCookieEx('sid', '") + 20, 60);
        $this->gb->setCookie('remixsid=' . $sid);
    }

    /*
     * Search video library by title
     * @var $title string
     * @return array List of videos
     */
    public function search($title)
    {
        $html = $this->gb->get('http://vk.com/video?q='.htmlentities(urlencode($title)).'&section=search');

        if(!$this->checkResponse($html))
            return '<error>Unable to login</error>';

        $items = $this->gb->preg()->getAll(
            "/\[([-]?[\d]*), ([\d]*), '([^']*)', '([^']*)', '[^']*', '[^']*', [\d]*, [\d]*, [\d]*, '([^']*)'/",
            $html);

        if(empty($items)) return array();

        $output = array();
        foreach($items as $item)
        {
            $output[] = array(
                'id1' => $item[0],
                'id2' => $item[1],
                'imageLink' => str_replace('\/','/', $item[2]),
                'title' => $item[3],
                'duration' => $item[4],
            );
        }

        return $output;
    }

    /*
     * Returns iframe code to play video
     * Note: some videos are unable to embed because of particular video settings
     *
     * @var $id1 integer Id1
     * @var $id2 integer Id2
     * @return string
     */
    public function play($id1, $id2)
    {
        // getting hash
        $hash = $this->gb->getPregOne('http://vk.com/video'.$id1.'_'.$id2, "/hash2[^0-9a-f]*([0-9a-f]*)/");

        $link = 'http://vk.com/video_ext.php?oid='.$id1.'&id='.$id2.'&hash='.$hash.'&hd=1';

        return $link;
    }

    /*
     * Checks if window return account confirmation window
     * If does, the sends phone's last 4 digits
     *
     * @var $html string HTML to chack
     * @return boolean
     */
    public function checkResponse($html)
    {
        $check = $this->gb->preg()->getOne('/Проверка безопасности/', $html);

        if(empty($check))
        {
            return true;
        }

        // try to confirm
        $postData = $this->gb->preg()->getOne('/var params = {([^}]*)};\s*ajax.post\(\'login.php\'/', $html);

        $data = explode(',', $postData);

        $array = array();
        foreach($data as $d)
        {
            $d = explode(':',$d);
            $d[0] = trim($d[0]);
            $array[$d[0]] = trim(str_replace('\'', '', $d[1]));
        }
        $array['code'] = $this->phoneLast4Digits;

        // login again
        $check = $this->gb->preg()->getOne('/Проверка безопасности/',
            $this->gb->post('http://vk.com/login.php', $array)
        );

        return (empty($check)) ? true : false;
    }
}