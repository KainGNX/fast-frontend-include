<?php 
include(__DIR__ . '/../src/view/Context.php');

$globalIncludes = [
    'js' => [
        'js/global1.js',
        'js/global2.js'
    ],
    'css' => [
        'css/global1.css',
        'css/global2.css'
    ]
];

$viewContext = new \FastFrontend\View\Context('index', $globalIncludes);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Fast Frontend Include Example</title>
    <?php
        echo $viewContext->js();
        echo $viewContext->css();
    ?>
</head>
<body>
    <h1>Fast Frontend Include Example</h1>
</body>
</html>