<?php
session_start();
if(isset($_POST['path'])&&$_SERVER['REQUEST_METHOD'] === 'POST'){
    $_SESSION['pathToRootOfServer']= trim($_POST['path-to-root-server-folder']);
    echo $_SESSION['pathToRootOfServer'];
    header('Location: actions.php');
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
        <button type="submit" name="path">Ok</button>
    </form>
</body>
</html>