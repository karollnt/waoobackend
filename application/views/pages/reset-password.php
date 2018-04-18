<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Reset password</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" type="text/css" media="screen" href="main.css" />
  <script src="main.js"></script>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</head>
<body>
  <div class="container jumbotron">
    <div class="text-center">
    <img src="http://www.waootechnology.com/wp-content/uploads/2017/10/waoo-technology-main-logo.png">
      <h2 class="d-none">Waoo</h2>
    </div>
    <?php
    if (isset($token)) {
    ?>
    <h3>Restablecer clave</h3>
    <form action="http://localhost/waoobackend/usuarios/updatePassword" method="post">
      <div>
        <label>
          <span>Nueva clave</span><br>
          <input type="password" name="password">
        </label>
      </div>
      <div>
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        <input type="submit" value="Actualizar" class="btn btn-primary">
      </div>
    </form>
    <?php
      } else if (isset($response)) {
        echo "<p>{$response}</p>";
      }
    ?>
  </div>
</body>
</html>