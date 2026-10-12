<?php
// Simpan dengan nama deploy-helper.php di public root atau root folder
// Hapus file ini setelah selesai digunakan demi keamanan!

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] ?? 'prod';
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] ?? '0';

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();

// Jalankan Cache Clear
echo "<h2>Running Cache Clear...</h2>";
$application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
$application->setAutoExit(false);

$input = new \Symfony\Component\Console\Input\ArrayInput(['command' => 'cache:clear']);
$output = new \Symfony\Component\Console\Output\BufferedOutput();
$application->run($input, $output);
echo "<pre>" . $output->fetch() . "</pre>";

echo "<h3 style='color:green;'>Selesai! Jangan lupa hapus file deploy-helper.php ini.</h3>";
