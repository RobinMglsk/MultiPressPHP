<?php

require_once('../../src/MultiPressPHP.php');
$secrets = (include '../secret.php');

$departments = [0 => 'prepress', 1 => 'offset', 2 => 'digitaal', 3 => 'afwerking', 4 => 'werkderden'];


if(isset($_GET['exec'])){
    $mp = new MultiPressPHP($secrets['user'], $secrets['password'], $secrets['host'], $secrets['port'], false);
    $department = (isset($_GET['department']) && array_key_exists($_GET['department'], $departments)) ? $_GET['department'] : 2;
    $response  = $mp->planning_get_lines(null, null, $department);
}

if(isset($_GET['details'])){
    $mp = new MultiPressPHP($secrets['user'], $secrets['password'], $secrets['host'], $secrets['port'], false);
    $responseDetails  = $mp->planning_get_details($_GET['id']);
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
        <h1>Get planning</h1><hr/>
        <?php if(isset($response)): ?>
            <a class="btn" href="./example_03.php">Back</a>
            <table class="u-full-width">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Machine</th>
                        <th>Time</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($response as $item): ?>
                    <tr>
                        <td><a href="?id=<?= $item['id'] ?>&details=1"><?= $item['description'] ?></a></td>
                        <td><?= $item['machine']['name'] ?></td>
                        <td><?= date('H:i:s', MultiPressPHP::convertToTime($item['time'])) ?></td>
                        <td><?= $item['date'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>            
        <?php elseif(isset($responseDetails)): ?>
            <a class="btn" href="./example_03.php">Back</a>
            <table class="u-full-width">
                <tbody>
                    <?php foreach($responseDetails['job'] as $label => $value): ?>
                    <tr>
                        <th><?= ucfirst(str_replace('_',' ',$label)); ?></th>
                        <td><?= $value ?></td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <tr>
                        <th>Production time</th>
                        <td><?= date('H:i:s', MultiPressPHP::convertToTime($responseDetails['production_time'])) ?></td>
                    </tr>
                    <tr>
                        <th>Calculated time</th>
                        <td><?= date('H:i:s', MultiPressPHP::convertToTime($responseDetails['calculated_time'])) ?></td>
                    </tr>
                    
                </tbody>
            </table>
        <?php else: ?>
            <form action="" method="get">
                <label for="department">Department</label>
                <select class="u-full-width" id="department" name="department" required>
                    <?php foreach($departments as $key => $department): ?>
                        <option value="<?= $key ?>"><?= $department ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="hidden" name="exec" value="1">
                <button class="btn">Submit</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>