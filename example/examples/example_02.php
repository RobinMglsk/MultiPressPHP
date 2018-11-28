<?php

require_once('../../src/MultiPressPHP.php');
$secrets = (include '../secret.php');

if(isset($_GET['exec']) && $_GET['exec'] == 1){

   


}else{

    $mp = new MultiPressPHP($secrets['user'], $secrets['password'], $secrets['host'], $secrets['port'], false);
    $paperName  = $mp->get_paper_by_id(1867);

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MultiPressPHP - Examples</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/skeleton/2.0.4/skeleton.css" />
</head>
<body>
    <div class="container">
        <h1>Paper by id</h1><hr/>
        <?php var_dump($paperName) ?>
    </div>
</body>
</html>