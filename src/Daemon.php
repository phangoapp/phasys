<?php

namespace PhangoApp\PhaSys;
use PhangoApp\PhaRouter\Routes;
use PhangoApp\PhaModels\Webmodel;
use PhangoApp\PhaUtils\Utils;
use Symfony\Component\Process\Process;

define('ERROR_UPDATING_TASK', 1);
define('ERROR_FORK', 2);
define('NEED_TASK_ID', 3);
define('ERROR_SCRIPT', 4);

Webmodel::load_model('vendor/phangoapp/phasys/models/logs');

class Daemon {


    /**
    * Init the php script how a daemon
    */
    
    public $pid=0;
    
    public $father_pid=0;
    
    public $txt_error='';

    public function init()
    {
        
        $pid = pcntl_fork();
    
        if ($pid==-1)
        {
            //Return message for the 
            
            //echo json_encode(array('ERROR' => 1, 'MESSAGE' => 'CANNOT FORK, check php configuration', 'CODE_ERROR' => ERROR_FORK));
            exit(1);
        }
        elseif ($pid)
        {
            //echo json_encode(array('PID' => $pid, 'ERROR' => 0, 'MESSAGE' => 'Running tasks...', 'PROGRESS' => 0));
            exit(0);
        }
        else
        {
            
            //Daemonize the element
             $this->father_pid=posix_getppid();
            
             $sid = posix_setsid();
             $this->pid=getmypid();
             echo $this->father_pid;
             $this->log(array('ERROR' => 0, 'MESSAGE' => 'Running daemon...', 'PROGRESS' => 0));
            
        }
    
    }
    
    /**
    * Method for load the daemon script from a normal php script accesible via webserver
    * 
    * With this method you can load a process via http. You need know that the request where Daemon::load is placed die when is called. You need make all needed tasks before.
    * 
    * @warning Please check your command for no undesiredable effects.
    * 
    */
    
    public function load($command, $stdout_file='/dev/null')
    {
        /*
        exec($command.' > '.$stdout_file.' 2>&1', $output, $return_var);
        
        if($return_var==0)
        {
            
            echo json_encode(array('father_pid' => $pid, 'msg' => 'Running tasks...'));
            
        }*/
        
        /*foreach($output as $line)
        {
        
            echo $line;
             
        }*/
        
        //By disgrace, only compatible with *nix like OSes.
        
        $command.=' > '.$stdout_file.' 2>&1 &';
        
        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
            1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
            2 => array("pipe", "w") // stderr is a file to write to
        );

        //$pipes=[];
        
        $process=proc_open($command, $descriptorspec, $pipes, Routes::$base_path);
        
        if($process)
        {
        
            //while(true)
            //{
                    
                $text=stream_get_contents($pipes[1]);
                
                $arr_msg=json_decode(trim($text));
                
                $status=proc_get_status($process);
                
                echo json_encode(array('ERROR' => 0, 'MESSAGE' => 'Running daemon...', 'FATHER_PID' => $status['pid']));
                
                if(!$status['running'])
                {
                    
                    fclose($pipes[0]);
                    
                    //Close 
                    proc_close($process);
                    
                    die;
                    
                }
                
                fclose($pipes[0]);
                    
                    //Close 
                proc_close($process);
                
                die;
                
            //}
            
        }
        else
        {
            
            //echo 'Cannot execute the command';
            echo json_encode(array('ERROR' => 1, 'MESSAGE' => 'Cannot execute the command', 'CODE_ERROR' => ERROR_SCRIPT));
            
        }

        /*
        $process = new Process($command);
        
        $process->run(function ($type, $buffer) {
            
            $arr_buffer=json_decode($buffer, true);
            
            settype($arr_buffer['PID'], 'integer');

            if($arr_buffer['PID']>0)
            {

                //The process is loaded

                //Return 
                
                $this->log(array('ERROR' => 0, 'MESSAGE' => 'Running daemon...', 'PROGRESS' => 0));
                
                echo json_encode(array('ERROR' => 0, 'MESSAGE' => 'Running daemon...', 'PROGRESS' => 0));

                die;
                

            }
            else
            {
                echo json_encode(array('ERROR' => 0, 'MESSAGE' => 'ERROR EXECUTING DAEMON', 'PROGRESS' => 0)); 
            
                $this->log(array('ERROR' => 0, 'MESSAGE' => 'ERROR EXECUTING DAEMON:', 'PROGRESS' => 0));

                die;
                
            }
        });
        
        if(!$process->isSuccessful())
        {

            $this->txt_error=$process->getOutput();
            
            echo json_encode(array('ERROR' => 0, 'MESSAGE' => 'ERROR EXEGUTING DAEMON: '.$this->txt_error, 'PROGRESS' => 0));

            $this->log(array('ERROR' => 0, 'MESSAGE' => 'ERROR EXEGUTING DAEMON: '.$this->txt_error, 'PROGRESS' => 0));

            die;
            
        }*/

    }
    
    /**
    * The log messaging, use this method for save the messages of your script
    */

    public function log($arr_data)
    {
        
        Webmodel::$model['log_exec']->fields_to_update=['father_pid', 'pid', 'log'];
        
        Webmodel::$model['log_exec']->insert(['father_pid' => $this->father_pid, 'pid' => $this->pid, 'log' => $arr_data]);
        
    }

}

?>
