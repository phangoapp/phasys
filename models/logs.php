<?php

use PhangoApp\PhaModels\Webmodel;
use PhangoApp\PhaModels\CoreFields;

$log=new Webmodel('log_exec');

$log->register('father_pid', new CoreFields\IntegerField(), true);

$log->components['father_pid']->indexed=true;

$log->register('pid', new CoreFields\IntegerField(), true);

$log->register('command', new CoreFields\CharField());

$log->register('log', new CoreFields\SerializeField(new CoreFields\TextField()), true);

?>
