<?php

use PhangoApp\PhaModels\Webmodel;
use PhangoApp\PhaModels\CoreFields;

$log=new Webmodel('log_exec');

$log->register('pid', new CoreFields\IntegerField(), true);

$log->register('log', new CoreFields\TextField(), true);

?>
