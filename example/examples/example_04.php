<?php

use RobinMglsk\MultiPressPHP;

require_once('../../src/MultiPressPHP/MultiPressPHP.php');
$secrets = (include '../secret.php');


$mp = new MultiPressPHP($secrets['user'], $secrets['password'], $secrets['host'], $secrets['port'], false);
$employees  = $mp->employee_list();


if(isset($_GET['details'])){
    $mp = new MultiPressPHP($secrets['user'], $secrets['password'], $secrets['host'], $secrets['port'], false);
    $details  = $mp->employee_details($_GET['id']);


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
        <h1>Get employees</h1><hr/>
             
        <?php if(isset($details)): ?>
            <a class="btn" href="./example_04.php">Back</a>
            <table class="u-full-width">
                <tbody>
                    <?php foreach($details as $label => $value): ?>
                    <tr>
                        <th><?= ucfirst(str_replace('_',' ',$label)); ?></th>
                        <td><?= $value ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <table class="u-full-width">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Department</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($employees as $employee): ?>
                    <tr>
                        <td><a href="?id=<?= $employee['employee_number'] ?>&details=1"><?= $employee['name'] ?></a></td>
                        <td><?= $employee['department'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>          
        <?php endif; ?>
    </div>
</body>
</html>