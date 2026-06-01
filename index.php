<?php

$target = rtrim($_SERVER['REQUEST_URI'] ?? '/', '/').'/public/';

header('Location: '.$target, true, 302);
exit;
