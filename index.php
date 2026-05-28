<?php

require_once 'src/PostCreator.php';

$GEMINI_API_KEY = "";
$PEXELS_API_KEY = "";

$postCreator = new PostCreator($GEMINI_API_KEY, $PEXELS_API_KEY, __DIR__ . '/' . 'topicos.json');
$postCreator->run();
