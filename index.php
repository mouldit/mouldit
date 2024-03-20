<?php
session_start();
if(isset($_POST['paths'])&&$_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST['path-to-root-server-folder']))$_SESSION['pathToRootOfServer']= trim($_POST['path-to-root-server-folder']);
    if(isset($_POST['path-to-root-client-folder']))$_SESSION['pathToRootOfClient']= trim($_POST['path-to-root-client-folder']);
    echo 'from index '.$_SESSION['pathToRootOfClient'];
    header('Location: configurations.php');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Mouldit code generator</title>
</head>
<body>
    <form action="<?php $_SERVER['PHP_SELF']?>" method="post">
        <label for="path-to-root-server-folder">
            Enter path of server root folder
        </label>
        <input id="path-to-root-server-folder" name="path-to-root-server-folder">
        <label for="path-to-root-client-folder">
            Enter path of client root folder
        </label>
        <input id="path-to-root-client-folder" name="path-to-root-client-folder">
        <button type="submit" name="paths">Ok</button>
    </form>
</body>
</html>