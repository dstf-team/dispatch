<?php

$git  = 'D:\\xampp\\Pgitux\\cmd\\git.exe';
$repo = 'D:\\xampp\\htdocs\\dstf';

$command = '"' . $git . '" -C "' . $repo . '" pull origin main 2>&1';

$output = shell_exec($command);