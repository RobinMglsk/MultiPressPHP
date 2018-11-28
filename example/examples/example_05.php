<?php

require_once('../../src/MultiPressPHP.php');
$secrets = (include '../secret.php');


$mp = new MultiPressPHP($secrets['user'], $secrets['password'], $secrets['host'], $secrets['port'], false);
$employees  = $mp->employee_list();


if(isset($_GET['exec'])){
    $mp = new MultiPressPHP($secrets['user'], $secrets['password'], $secrets['host'], $secrets['port'], false);
    $worksheets  = $mp->employee_worksheets($_GET['employee']);
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
        <h1>Get worksheets</h1><hr/>
             
        <?php if(isset($worksheets)): ?>
            <a class="btn" href="./example_05.php">Back</a>
            <table class="u-full-width">
                <thead>
                    <tr>
                        <th>Job</th>
                        <th>Client</th>
                        <th>Operation</th>
                        <th>Start</th>
                        <th>Stop</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($worksheets as $worksheet): ?>
                    <tr>
                        <td><?= $worksheet['job_number'] ?> - <?= $worksheet['description'] ?></td>
                        <td><?= $worksheet['company'] ?></td>
                        <td><?= $worksheet['operation'] ?></td>
                        <td><?= date("H:i", $worksheet['start_time']/1000) ?></td>
                        <td><?= date("H:i", $worksheet['stop_time']/1000) ?></td>
                        <td><?= date("H:i:s", MultiPressPHP::convertToTime($worksheet['time_production'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <form action="" method="get">

                <label for="employee">Date</label>
                <input class="u-full-width" type="text" value="<?= MultiPressPHP::convertToDate(strtotime('today')) ?>" disabled>

                <label for="employee">Employee</label>
                <select class="u-full-width" id="employee" name="employee" required>
                    <?php foreach($employees as $key => $employee): ?>
                        <option value="<?= $employee['employee_number']?>"><?= $employee['name'] ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="hidden" name="exec" value="1">
                <button class="btn">Submit</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>