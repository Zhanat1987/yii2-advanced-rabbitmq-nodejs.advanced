<?php

namespace common\components;

use Yii;

class ConsoleRunner
{

    private $_yiiCommand;

    public function __construct()
    {
        $this->_yiiCommand = dirname(dirname($_SERVER['DOCUMENT_ROOT'])) . DIRECTORY_SEPARATOR . 'yii ';
    }

    public function run($cmd)
    {
        $cmd = $this->_yiiCommand . $cmd;
        if ($this->isWindows()) {
            $descriptorspec = array(
                0 => array("pipe", "r"),
                1 => array("pipe", "w"),
            );
            $p = proc_open('start /b ' . $cmd, $descriptorspec, $pipes);
            $s = proc_get_status($p);
            proc_close($p);
        } else {
            pclose(popen($cmd . ' /dev/null &', 'r'));
        }
        return true;
    }

    protected function isWindows()
    {
        if (PHP_OS == 'WINNT' || PHP_OS == 'WIN32')
            return true;
        else
            return false;
    }

    public static function isRunningOnWindows()
    {
        return DIRECTORY_SEPARATOR == '\\';
    }

    /*
     * запустить фоновый процесс, выполнить и удалить
     */
    public function startStopKillProcess()
    {
        $cmd = 'C:\xampp\htdocs\yii.kz\protected\yiic subscribe4';
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
        );
        $p = proc_open('start /b ' . $cmd, $descriptorspec, $pipes);
        $s = proc_get_status($p);
        proc_close($p);
        Yii::app()->cache->set('process', $s['pid']);
        Yii::app()->cache->set('process2', $p);
    }

    /*
     * subscribe5
     */
    public function subscribe5()
    {
        $cmd = 'start /b C:\xampp\htdocs\yii.kz\protected\yiic subscribe5';
        pclose(popen($cmd, 'r'));
    }

    /*
     * subscribe6
     */
    public function subscribe6()
    {
        $cmd = 'start /b C:\xampp\htdocs\yii.kz\protected\yiic subscribe6';
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
        );
        $p = proc_open($cmd, $descriptorspec, $pipes);
        $s = proc_get_status($p);
        proc_close($p);
    }

    /*
     * RabbitMQ
     */
    public function RabbitMQ($action)
    {
        $cmd = 'start /b C:\xampp\htdocs\yii.kz\protected\yiic rabbitmq ' . $action;
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
        );
        $p = proc_open($cmd, $descriptorspec, $pipes);
        $s = proc_get_status($p);
        proc_close($p);
    }

    /*
     * Tasks
     */
    public function Tasks($action)
    {
        $cmd = 'start /b C:\xampp\htdocs\yii.kz\protected\yiic tasks ' . $action;
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
        );
        $p = proc_open($cmd, $descriptorspec, $pipes);
        $s = proc_get_status($p);
        proc_close($p);
    }

} 