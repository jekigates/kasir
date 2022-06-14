<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="./assets/img/favicons/apple-touch-icon.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="./assets/img/favicons/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="./assets/img/favicons/favicon-16x16.png" />
    <link rel="manifest" href="./assets/img/favicons/site.webmanifest" />

    <!-- Stylesheets -->
    <link rel="stylesheet" href="./assets/css/main.css" />
  </head>
  <style>
    .page-index {
      width: 100vw;
      height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      background-color: var(--background);
    }

    .form-login {
      width: 20rem;
    }
  </style>
  <body>
    <div class="page-index">
      <a href="index.php" style="margin-bottom: 4rem">
        <img src="./assets/img/logo.png" alt="" width="125" height="40" />
      </a>
      <div class="form-login">
        <form action="crud.php?cmd=login" method="POST">
          <input type="text" id="username" name="username" placeholder="Username" autocomplete="off" style="margin-bottom: 1rem" />
          <input type="password" id="password" name="password" placeholder="Password" autocomplete="off" style="margin-bottom: 1rem" />
          <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
      </div>
    </div>
  </body>
</html>
