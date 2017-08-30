<?php

/**
 * Created by PhpStorm.
 * User: home
 * Date: 2/5/2016
 * Time: 10:13 PM
 */
class TimeHelper
{
    public static function time_elapsed_string($ptime)
    {
        $etime = time() - $ptime;

        if ($etime < 1) {
            $etime*=-1;
            //return '0 seconds '.$etime;
        }

        $a = array(365 * 24 * 60 * 60 => 'year',
            30 * 24 * 60 * 60 => 'month',
            24 * 60 * 60 => 'day',
            60 * 60 => 'hour',
            60 => 'minute',
            1 => 'second'
        );
        $a_plural = array('year' => 'years',
            'month' => 'months',
            'day' => 'days',
            'hour' => 'hours',
            'minute' => 'minutes',
            'second' => 'seconds'
        );

        foreach ($a as $secs => $str) {
            $d = $etime / $secs;
            if ($d >= 1) {
                $r = round($d);
                return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str) . ' ago';
            }
        }
    }

    public static function getFullTime($timestamp)
    {
        return date("D M j G:i:s T Y",$timestamp);
    }
    public static function getFullTimeBlog($timestamp)
    {
        return date("D, j M'y",$timestamp);
    }
}