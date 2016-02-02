<?php

namespace PhangoApp\PhaSys;
use PhangoApp\PhaModels\Webmodel;
use Symfony\Component\Process\Process;

define('ERROR_UPDATING_TASK', 1);
define('ERROR_FORK', 2);
define('NEED_TASK_ID', 3);

Webmodel::load_model('vendor/phangoapp/phasys/models/logs');

class Daemon {


    /**
    * Init the php script how a daemon
    */
    
    public $pid=0;
    
    public $txt_error='';

    public function init()
    {
        
        $pid = pcntl_fork();
    
        if ($pid==-1)
        {
            //Return message for the 
            
            echo json_encode(array('ERROR' => 1, 'MESSAGE' => 'CANNOT FORK, check php configuration', 'CODE_ERROR' => ERROR_FORK));
            exit(1);
        }
        elseif ($pid)
        {
            echo json_encode(array('PID' => $pid, 'ERROR' => 0, 'MESSAGE' => 'Running tasks...', 'PROGRESS' => 0));
            exit(0);
        }
        else
        {
            
            //Daemonize the element
            
             $sid = posix_setsid();
             $this->pid=getmypid();
             
             $this->log(array('ERROR' => 0, 'MESSAGE' => 'Running daemon...', 'PROGRESS' => 0));
            
        }
    
    }
    
    /**
    * Method for load the daemon script from a normal php script accesible via webserver
    * 
    * With this method you can load a process via http. You need know that the request where Daemon::load is placed die when is called. You need make all needed tasks before.
    */
    
    public function load($command)
    {
        
        $process = new Process($command);
        
        $process->run(function ($type, $buffer) {

            $arr_buffer=json_decode($buffer, true);

            settype($arr_buffer['PID'], 'integer');

            if($arr_buffer['PID']>0)
            {

                //The process is loaded

                //Return 
                
                echo json_encode(array('ERROR' => 0, 'MESSAGE' => 'Running daemon...', 'PROGRESS' => 0));

                die;
                

            }
        });
        
        if(!$process->isSuccessful())
        {
                       
            $this->txt_error=$process->getOutput();
            
            echo json_encode(array('ERROR' => 0, 'MESSAGE' => 'Running daemon...', 'PROGRESS' => 0));

            die;
            
        }
        
        

    }
    
    /**
    * The log messaging, use this method for save the messages of your script
    */

    public function log($arr_data)
    {
        
        Webmodel::$model['log_exec']->insert(['pid' => $this->pid, 'log' => json_encode($arr_data)]);
        
    }

}

?>
