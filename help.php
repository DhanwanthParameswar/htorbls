<?php
include "bootstrap.php";
session_start();
$loggedIn = isset($_SESSION['user_id']) && isset($_SESSION['user_username']);
$siteName = function_exists('demo_page_title') ? SITE_TITLE : 'Library System';
$pageTitle = function_exists('demo_page_title') ? demo_page_title('Help/Support') : 'Help/Support';
?>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <style type="text/css">
      iframe {
          aspect-ratio: 16 / 9;
          width: 100% !important;
      }
    </style>
  </head>
  <body style="width: 100%; min-height: 100vh; display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; padding: 15px; background: #F4CABC;">
<?php if (function_exists("demo_render_banner")) demo_render_banner(); ?>
      <div class="container text-center" style="width: 1000px; background: #fff; border-radius: 10px; overflow: hidden; padding: 77px 55px 33px 55px; box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -moz-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -webkit-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -o-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1); -ms-box-shadow: 0 5px 10px 0px rgba(0, 0, 0, 0.1);">
        <h1 class="text-center pb-2 display-4">Help/Support</h1>
        <?php if (defined('DEMO_MODE') && DEMO_MODE) { ?>
        <p class="text-start">Welcome to the <strong><?= htmlspecialchars($siteName) ?></strong>. This is a public sandbox built to showcase a small-library management workflow. Sign in with the demo account shown on the login page (<code>demo</code> / <code>DemoPass123</code>). Any records you add are sample data only and are cleared automatically about once per day.</p>
        <p class="text-start">Use <strong>New Entry</strong> to check books out, <strong>Book ID Tool</strong> to look up or return a copy, and <strong>Book List</strong> to browse the catalog. Patron names and contact details in the database are fictional placeholders, not real people. If you want a walkthrough of the workflow, watch the training video below.</p>
        <p class="text-start">If the application shows an error box similar to the example below, note what you were doing and refresh the page. Because this is a demo environment, persistent errors usually clear after the next daily reset.</p>
        <?php } else { ?>
        <p class="text-start">Welcome to the <?= htmlspecialchars($siteName) ?>. Use the tabs on the home page to check books out, look up titles, or add new books to the catalog. Contact your library administrator if you need access or run into a problem using the system. You can also watch the training video below for an overview.</p>
        <p class="text-start">If you see an error box like the example below, contact your administrator with a short description of what you were trying to do.</p>
        <?php } ?>
        <table class="xdebug-error xe-warning mb-4" style="margin-left: auto; margin-right: auto;" dir="ltr" border="1" cellspacing="0" cellpadding="1">
<tbody><tr><th align="left" bgcolor="#f57900" colspan="5"><span style="background-color: #cc0000; color: #fce94f; font-size: x-large;">( ! )</span> Example Error Box</th></tr>
<tr><th align="left" bgcolor="#e9b96e" colspan="5">Call Stack</th></tr>
<tr><th align="center" bgcolor="#eeeeec">#</th><th align="left" bgcolor="#eeeeec">Time</th><th align="left" bgcolor="#eeeeec">Memory</th><th align="left" bgcolor="#eeeeec">Function</th><th align="left" bgcolor="#eeeeec">Location</th></tr>
<tr><td bgcolor="#eeeeec" align="center">-</td><td bgcolor="#eeeeec" align="center">-</td><td bgcolor="#eeeeec" align="right">-</td><td bgcolor="#eeeeec">-</td><td bgcolor="#eeeeec">-</td></tr>
</tbody></table>
        <iframe class="mb-4" src="https://www.youtube.com/embed/EM5wSDQus4A?si=CV0zMUlSYVaOxjpe" title="Training video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
        <hr>
        <?php if ($loggedIn) { ?>
        <button onclick="window.location.href='./index.php';" id="returnhome" name="returnhome" class="btn btn-orange">Return Home</button>
        <?php } else { ?>
        <button onclick="window.location.href='./login.php';" class="btn btn-orange">Back to Login</button>
        <?php } ?>
      </div>
  </body>
</html>
