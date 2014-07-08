<?php

require_once __DIR__.'/../vendor/autoload.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Application();
$app['debug'] = true;
$app['pdo'] = $app->share(function() {
    $dir = __DIR__ . '/../app.db';
    $pdo = new PDO("sqlite://{$dir}", null, null);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
});

$app->register(new Silex\Provider\TwigServiceProvider(), ['twig.path' => __DIR__.'/../view']);

Resque::setBackend("localhost:6379");

$app->put('/update/{$job_id}', function(Request $request, $job_id) use ($app) {
    $name = $request->get('name');
    if(! $name) {
        $app->abort('400', 'Invalid parameters, bad payload! :\\');
    }

    $statement = $app['pdo']->prepare("UPDATE jobs SET name = :name, status = 'complete' WHERE job_id = :job_id");
    $statement->execute(['name' => $name, 'job_id' => $job_id]);
    
    return new Response("Completed {$job_id}", 200);
});

$app->post("/new", function(Request $request) use ($app) {
    $name = $request->get('name');

    if(! $name) {
        $app->abort(400, "Invalid parameters, try again. :(");
    }

    $job = Resque::enqueue('default', 'Greet', ["name" => $name], true);

    $statement = $app['pdo']->prepare("INSERT INTO jobs (job_id, name, status) VALUES (?, ?, ?)");
    $statement->execute([$job, 'not yet processed', 'queued']);

    return $app->redirect("/job/{$job}");
});

$app->get('/job/{job_id}', function($job_id) use ($app) {
    $job = new Resque_Job_Status($job_id);
    $status = $job->get();

    switch($status) {
        case Resque_Job_Status::STATUS_WAITING: 
            $data = ['job_id' => $job_id, 'status' => 'waiting'];
            break;
        case Resque_Job_Status::STATUS_RUNNING: 
            $data = ['job_id' => $job_id, 'status' => 'running'];
            break;
        case Resque_Job_Status::STATUS_FAILED: 
            $data = ['job_id' => $job_id, 'status' => 'failed'];
            break;
        case Resque_Job_Status::STATUS_COMPLETE: 
            $data = ['job_id' => $job_id, 'status' => 'complete'];
            break;
        default:
            $data = [];
            break;
    }

    return $app['twig']->render('status.twig', ['job' => $data]);
});

$app->get("/new", function() use ($app) {
    return $app['twig']->render('new.twig', []);
});

$app->get('/', function() use ($app) {
    // Brevity people!
    $jobs = $app['pdo']->query('SELECT * FROM jobs')->fetchAll();
    return $app['twig']->render('index.twig', ['jobs' => $jobs]);
});

$app->run();
